<?php
// hotel_completo/app/models/InventoryMovementType.php

class InventoryMovementType {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all inventory movement types.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM tipos_movimiento_inventario ORDER BY nombre_movimiento ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a movement type by its ID.
     * @param int $id_tipo_movimiento
     * @return array|false
     */
    public function getById($id_tipo_movimiento) {
        $stmt = $this->pdo->prepare("SELECT * FROM tipos_movimiento_inventario WHERE id_tipo_movimiento = ?");
        $stmt->execute([$id_tipo_movimiento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // You can add create, update, delete methods if these types are user-manageable
    // For now, we assume these are mostly fixed values (e.g., 'entrada', 'salida', 'lavanderia_envio')
}