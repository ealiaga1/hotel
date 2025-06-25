<?php
// hotel_completo/app/models/RestaurantOrder.php

class RestaurantOrder {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all restaurant orders with details.
     * @return array
     */
    public function getAllOrders() {
        $sql = "SELECT ro.*,
                       m.numero_mesa,
                       u.nombre_usuario AS mesero_nombre,
                       u.apellido_usuario AS mesero_apellido,
                       h.nombre AS huesped_nombre,
                       h.apellido AS huesped_apellido
                FROM pedidos_restaurante ro
                LEFT JOIN mesas m ON ro.id_mesa = m.id_mesa
                LEFT JOIN usuarios u ON ro.id_usuario_toma_pedido = u.id_usuario
                LEFT JOIN huespedes h ON ro.id_huesped = h.id_huesped
                ORDER BY ro.fecha_pedido DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a specific order by ID with its items.
     * @param int $id_pedido
     * @return array|false Order details with an array of items, or false if not found.
     */
    public function getOrderById($id_pedido) {
        $sql = "SELECT ro.id_pedido, ro.fecha_pedido, ro.total_pedido, ro.estado AS estado_pedido, ro.tipo_pedido,
                       ro.comentarios, ro.fecha_registro, ro.nombre_cliente, ro.telefono_cliente,
                       m.numero_mesa, m.ubicacion AS mesa_ubicacion, m.estado AS mesa_estado,
                       u.nombre_usuario AS mesero_nombre, u.apellido_usuario AS mesero_apellido,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido, h.numero_documento AS huesped_documento
                FROM pedidos_restaurante ro
                LEFT JOIN mesas m ON ro.id_mesa = m.id_mesa
                LEFT JOIN usuarios u ON ro.id_usuario_toma_pedido = u.id_usuario
                LEFT JOIN huespedes h ON ro.id_huesped = h.id_huesped
                WHERE ro.id_pedido = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_pedido]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        // Get order items
        $items_sql = "SELECT dp.*, pm.nombre_plato, pm.descripcion AS plato_descripcion
                      FROM detalle_pedido dp
                      LEFT JOIN platos_menu pm ON dp.id_plato = pm.id_plato
                      WHERE dp.id_pedido = ?";
        $items_stmt = $this->pdo->prepare($items_sql);
        $items_stmt->execute([$id_pedido]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    /**
     * Creates a new restaurant order with its items.
     * @param array $order_data Main order details.
     * @param array $items_data Array of order items [{id_plato, descripcion_item, cantidad, precio_unitario}].
     * @return int|false The ID of the new order or false on failure.
     */
    public function createOrder($order_data, $items_data) {
        error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: createOrder called.");
        error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Order data received: " . print_r($order_data, true));
        error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Items data received: " . print_r($items_data, true));

        $transaction_started_here = false; 

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transaction_started_here = true;
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Transaction started by model.");
            } else {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Transaction already active, continuing within existing one.");
            }

            // 1. Insert main order
            $sql_order = "INSERT INTO pedidos_restaurante (id_mesa, id_huesped, nombre_cliente, telefono_cliente, id_usuario_toma_pedido, fecha_pedido, estado, total_pedido, tipo_pedido, comentarios)
                          VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)"; 
            $stmt_order = $this->pdo->prepare($sql_order);

            $order_params = [
                $order_data['id_mesa'] ?? null,
                $order_data['id_huesped'] ?? null,
                $order_data['nombre_cliente'] ?? null,
                $order_data['telefono_cliente'] ?? null,
                $order_data['id_usuario_toma_pedido'] ?? $_SESSION['user_id'] ?? null,
                $order_data['estado'] ?? 'pendiente',
                $order_data['total_pedido'],
                $order_data['tipo_pedido'] ?? 'mesa',
                $order_data['comentarios'] ?? null
            ];
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Main order SQL: " . $sql_order);
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Main order params count: " . count($order_params)); // DEBUG
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Main order params: " . print_r($order_params, true));

            $order_result = $stmt_order->execute($order_params);

            if (!$order_result) {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE ERROR: Failed to execute main order insert (execute returned false).");
                throw new Exception("Error al crear el pedido principal (ejecución SQL).");
            }
            $id_pedido = $this->pdo->lastInsertId();
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Main order created with ID: " . $id_pedido);

            // 2. Insert order items
            $sql_item = "INSERT INTO detalle_pedido (id_pedido, id_plato, descripcion_item, cantidad, precio_unitario, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $this->pdo->prepare($sql_item);

            foreach ($items_data as $item) {
                $subtotal = (float)($item['cantidad'] ?? 0) * (float)($item['precio_unitario'] ?? 0);
                $item_params = [
                    $id_pedido,
                    $item['id_plato'] ?? null,
                    $item['descripcion_item'] ?? ($item['nombre_plato'] ?? 'Descripción no disponible'),
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $subtotal
                ];
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Item SQL: " . $sql_item);
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Item params: " . print_r($item_params, true));

                $item_result = $stmt_item->execute($item_params);
                if (!$item_result) {
                    error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE ERROR: Failed to insert an order item (execute returned false).");
                    throw new Exception("Error al insertar un ítem del pedido.");
                }
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: All items inserted successfully.");

            if ($transaction_started_here) {
                $this->pdo->commit();
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Transaction committed successfully by model.");
            } else {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE: Transaction not started by model, not committing here.");
            }
            return $id_pedido;

        } catch (PDOException $e) {
            if ($transaction_started_here && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE FATAL ERROR: PDOException in createOrder: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Query: " . ($stmt_order->queryString ?? '') . " | Params: " . print_r($order_params ?? [], true) . " | Trace: " . $e->getTraceAsString());
            throw new Exception("Error de base de datos en createOrder: " . $e->getMessage() . " (SQLSTATE: " . $e->getCode() . ")");
        } catch (Exception $e) {
            if ($transaction_started_here && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-CREATE FATAL ERROR: General Exception in createOrder: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Updates an existing order's main details and status.
     * @param int $id_pedido
     * @param string $new_order_status
     * @return bool
     */
    public function updateOrderStatus($id_pedido, $new_order_status) {
        error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: updateOrderStatus called for Order ID: " . $id_pedido . ", New Status: " . $new_order_status);
        $transaction_started_here = false; 
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transaction_started_here = true; 
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Transaction started by model.");
            } else {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Transaction already active, continuing within existing one.");
            }
            
            $sql_order = "UPDATE pedidos_restaurante SET estado = ? WHERE id_pedido = ?";
            $stmt_order = $this->pdo->prepare($sql_order);
            $order_update_params = [$new_order_status, $id_pedido];
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Order status update SQL: " . $sql_order);
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Order status update params: " . print_r($order_update_params, true));
            $order_result = $stmt_order->execute($order_update_params);

            if (!$order_result) {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE ERROR: Failed to execute order status update.");
                throw new Exception("Error al actualizar el estado del pedido en la base de datos.");
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Order status updated to " . $new_order_status);

            if ($transaction_started_here) {
                $this->pdo->commit();
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Transaction committed successfully by model.");
            } else {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE: Transaction not started by model, not committing here.");
            }
            return true;
        } catch (PDOException $e) {
            if ($transaction_started_here && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE FATAL ERROR: PDOException in updateOrderStatus: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Exception $e) {
            if ($transaction_started_here && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-UPDATE FATAL ERROR: General Exception in updateOrderStatus: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Deletes an order and its items.
     * @param int $id_pedido
     * @return bool
     */
    public function deleteOrder($id_pedido) {
        error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: deleteOrder called for ID: " . $id_pedido);
        try {
            $this->pdo->beginTransaction();
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Transaction started for delete.");

            // Get order details to update table status if needed
            $order = $this->getOrderById($id_pedido);
            if (!$order) {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE ERROR: Order not found for deletion.");
                throw new Exception("Pedido no encontrado para eliminar.");
            }

            // Delete order items first (due to foreign key constraint)
            $stmt_items = $this->pdo->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ?");
            $items_delete_params = [$id_pedido];
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Deleting items SQL: " . $stmt_items->queryString);
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Deleting items params: " . print_r($items_delete_params, true));
            $stmt_items->execute($items_delete_params);
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Items deleted for order " . $id_pedido);

            // Delete the main order
            $sql_order_delete = "DELETE FROM pedidos_restaurante WHERE id_pedido = ?";
            $stmt_order = $this->pdo->prepare($sql_order_delete);
            $order_delete_params = [$id_pedido];
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Deleting order SQL: " . $sql_order_delete);
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Deleting order params: " . print_r($order_delete_params, true));
            $order_delete_result = $stmt_order->execute($order_delete_params);

            if (!$order_delete_result) {
                error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE ERROR: Failed to delete main order.");
                throw new Exception("Error al eliminar el pedido principal.");
            }
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Main order deleted successfully.");

            $this->pdo->commit();
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE: Transaction committed successfully for delete.");
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE FATAL ERROR: PDOException in deleteOrder: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Trace: " . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-RESTAURANT-ORDER-MODEL-DELETE FATAL ERROR: General Exception in deleteOrder: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Gets the count of pending/active orders for the dashboard.
     * @return int
     */
    public function getPendingOrdersCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM pedidos_restaurante WHERE estado IN ('pendiente', 'en_preparacion', 'listo', 'entregado')");
        return $stmt->fetchColumn();
    }

    /**
     * Obtiene la orden activa (no pagada ni cancelada) para una mesa específica.
     * Incluye detalles del cliente/huésped.
     * @param int $id_mesa
     * @return array|false Detalles de la orden activa o false si no hay.
     */
    public function getActiveOrderByTableId($id_mesa) {
        $sql = "SELECT ro.id_pedido, ro.fecha_pedido, ro.total_pedido, ro.estado AS estado_pedido,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido,
                       ro.nombre_cliente, ro.telefono_cliente
                FROM pedidos_restaurante ro
                LEFT JOIN huespedes h ON ro.id_huesped = h.id_huesped
                WHERE ro.id_mesa = ? AND ro.estado NOT IN ('pagado', 'cancelado')
                ORDER BY ro.fecha_pedido DESC, ro.id_pedido DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_mesa]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}