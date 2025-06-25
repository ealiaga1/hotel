<?php
// hotel_completo/app/models/InventoryMovement.php

class InventoryMovement {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all inventory movements with product and type details.
     * @return array
     */
    public function getAll() {
        // MODIFICACIÓN: Añadir pi.unidad_medida a la selección y LEFT JOIN con productos_inventario
        $sql = "SELECT im.*, pi.nombre_producto, pi.unidad_medida, pit.nombre_movimiento, u.nombre_usuario AS usuario_nombre, u.apellido_usuario AS usuario_apellido
                FROM movimientos_inventario im
                JOIN productos_inventario pi ON im.id_producto = pi.id_producto
                JOIN tipos_movimiento_inventario pit ON im.id_tipo_movimiento = pit.id_tipo_movimiento
                LEFT JOIN usuarios u ON im.id_usuario = u.id_usuario
                ORDER BY im.fecha_movimiento DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a movement by its ID.
     * @param int $id_movimiento
     * @return array|false
     */
    public function getById($id_movimiento) {
        // MODIFICACIÓN: Añadir pi.unidad_medida a la selección
        $sql = "SELECT im.*, pi.nombre_producto, pi.unidad_medida, pit.nombre_movimiento, u.nombre_usuario AS usuario_nombre, u.apellido_usuario AS usuario_apellido
                FROM movimientos_inventario im
                JOIN productos_inventario pi ON im.id_producto = pi.id_producto
                JOIN tipos_movimiento_inventario pit ON im.id_tipo_movimiento = pit.id_tipo_movimiento
                LEFT JOIN usuarios u ON im.id_usuario = u.id_usuario
                WHERE im.id_movimiento = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_movimiento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new inventory movement and updates product stock.
     * @param array $data Movement data including product ID, type ID, and quantity.
     * @return int|false The ID of the new movement or false on failure.
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insert the movement record
            $sql_movement = "INSERT INTO movimientos_inventario (id_producto, id_tipo_movimiento, cantidad, fecha_movimiento, id_usuario, referencia)
                             VALUES (?, ?, ?, NOW(), ?, ?)";
            $stmt_movement = $this->pdo->prepare($sql_movement);
            $movement_result = $stmt_movement->execute([
                $data['id_producto'],
                $data['id_tipo_movimiento'],
                $data['cantidad'],
                $data['id_usuario'] ?? $_SESSION['user_id'] ?? null, // Default to logged-in user
                $data['referencia'] ?? null
            ]);

            if (!$movement_result) {
                throw new Exception("Error al registrar el movimiento de inventario.");
            }
            $id_movimiento = $this->pdo->lastInsertId();

            // 2. Update product stock
            // Determine stock change: positive for 'entrada', negative for 'salida'/'descarte'
            $quantity_change = $data['cantidad']; // Default to positive
            $movement_type = $this->pdo->prepare("SELECT nombre_movimiento FROM tipos_movimiento_inventario WHERE id_tipo_movimiento = ?");
            $movement_type->execute([$data['id_tipo_movimiento']]);
            $type_name = $movement_type->fetchColumn();

            if (in_array($type_name, ['salida', 'descarte', 'lavanderia_envio'])) { // Adjust as per your movement types that reduce stock
                $quantity_change = -$quantity_change;
            }

            // Call Product model to update stock
            $product_model = new Product($this->pdo); // Re-use PDO instance, but need Product model
            if (!$product_model->updateStock($data['id_producto'], $quantity_change)) {
                throw new Exception("Error al actualizar el stock del producto.");
            }

            $this->pdo->commit();
            return $id_movimiento;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-INVENTORY-MOVEMENT ERROR: create movement failed: " . $e->getMessage());
            return false;
        }
    }

    // You can add update, delete methods for movements, though usually movements are appended and not edited/deleted easily
}
