<?php
// hotel_completo/app/models/RestaurantOrder.php

class RestaurantOrder {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllOrders($filtros = []) {
    $sql = "SELECT * FROM pedidos_restaurante WHERE 1";
    $params = [];

    if (!empty($filtros['estado'])) {
        $sql .= " AND estado = ?";
        $params[] = $filtros['estado'];
    }

    if (!empty($filtros['exclude_estado'])) {
        $sql .= " AND estado <> ?";
        $params[] = $filtros['exclude_estado'];
    }

    if (!empty($filtros['q'])) {
        $sql .= " AND (nombre_cliente LIKE ? OR id_mesa IN (SELECT id_mesa FROM mesas WHERE numero_mesa LIKE ?))";
        $params[] = '%' . $filtros['q'] . '%';
        $params[] = '%' . $filtros['q'] . '%';
    }

    $sql .= " ORDER BY fecha_pedido DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getOrderById($id_pedido) {
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
                WHERE ro.id_pedido = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_pedido]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return false;
        }

        $items_sql = "SELECT dp.*, pm.nombre_plato, pm.descripcion AS plato_descripcion
                      FROM detalle_pedido dp
                      JOIN platos_menu pm ON dp.id_plato = pm.id_plato
                      WHERE dp.id_pedido = ?";
        $items_stmt = $this->pdo->prepare($items_sql);
        $items_stmt->execute([$id_pedido]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }

    public function createOrder($order_data, $items_data) {
    try {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
            $sql_order = "INSERT INTO pedidos_restaurante (id_mesa, id_huesped, id_usuario_toma_pedido, fecha_pedido, estado, total_pedido, tipo_pedido, nombre_cliente, telefono_cliente)
                          VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
            $stmt_order = $this->pdo->prepare($sql_order);
            $order_result = $stmt_order->execute([
                $order_data['id_mesa'] ?? null,
                $order_data['id_huesped'] ?? null,
                $order_data['id_usuario_toma_pedido'] ?? ($_SESSION['user_id'] ?? null),
                $order_data['estado'] ?? 'pendiente',
                $order_data['total_pedido'],
                $order_data['tipo_pedido'] ?? 'mesa',
                $order_data['nombre_cliente'] ?? null,
                $order_data['telefono_cliente'] ?? null
            ]);

            if (!$order_result) {
                $error = $stmt_order->errorInfo();
                error_log("DEBUG-RESTAURANT-ORDER ERROR: createOrder failed (INSERT pedido): " . print_r($error, true));
                throw new Exception("Error al crear el pedido principal.");
            }
            $id_pedido = $this->pdo->lastInsertId();

            $sql_item = "INSERT INTO detalle_pedido (id_pedido, id_plato, cantidad, precio_unitario, subtotal, comentarios)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $this->pdo->prepare($sql_item);

            foreach ($items_data as $item) {
    $subtotal = $item['cantidad'] * $item['precio_unitario'];
    $item_result = $stmt_item->execute([
        $id_pedido,
        $item['id_plato'],
        $item['cantidad'],
        $item['precio_unitario'],
        $subtotal,
        $item['comentarios'] ?? null
    ]);
    if (!$item_result) {
        $error = $stmt_item->errorInfo();
        error_log("DEBUG-RESTAURANT-ORDER ERROR: createOrder failed (INSERT item): " . print_r($error, true));
        throw new Exception("Error al insertar un item del pedido. Error SQL: " . print_r($error, true));
    }
}

            if (!empty($order_data['id_mesa']) && $order_data['tipo_pedido'] == 'mesa') {
                $table_model = new Table($this->pdo);
                if (!$table_model->updateStatus($order_data['id_mesa'], 'ocupada')) {
                    throw new Exception("Error al actualizar el estado de la mesa.");
                }
            }

            $this->pdo->commit();
            return $id_pedido;

        } catch (Exception $e) {
    if ($this->pdo->inTransaction()) {
        $this->pdo->rollBack();
    }
    error_log("DEBUG-RESTAURANT-ORDER ERROR: createOrder failed: " . $e->getMessage());
    // Devuelve el mensaje de error para mostrarlo en pantalla
    return $e->getMessage();
}
    }

    public function updateOrderStatus($id_pedido, $new_order_status, $id_mesa = null, $new_table_status = null) {
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            $sql_order = "UPDATE pedidos_restaurante SET estado = ? WHERE id_pedido = ?";
            $stmt_order = $this->pdo->prepare($sql_order);
            $order_result = $stmt_order->execute([$new_order_status, $id_pedido]);

            if (!$order_result) {
                throw new Exception("Error al actualizar el estado del pedido en la base de datos.");
            }

            if ($id_mesa !== null && $new_table_status !== null) {
                $table_model = new Table($this->pdo);
                if (!$table_model->updateStatus($id_mesa, $new_table_status)) {
                    throw new Exception("Error al actualizar el estado de la mesa.");
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER ERROR: updateOrderStatus failed in model: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteOrder($id_pedido) {
        try {
            $this->pdo->beginTransaction();

            $order = $this->getOrderById($id_pedido);
            if (!$order) {
                throw new Exception("Pedido no encontrado para eliminar.");
            }

            $stmt_items = $this->pdo->prepare("DELETE FROM detalle_pedido WHERE id_pedido = ?");
            $stmt_items->execute([$id_pedido]);

            $stmt_order = $this->pdo->prepare("DELETE FROM pedidos_restaurante WHERE id_pedido = ?");
            $order_delete_result = $stmt_order->execute([$id_pedido]);

            if (!$order_delete_result) {
                throw new Exception("Error al eliminar el pedido principal.");
            }

            if (!empty($order['id_mesa']) && $order['estado'] != 'pagado' && $order['estado'] != 'cancelado') {
                $table_model = new Table($this->pdo);
                if (!$table_model->updateStatus($order['id_mesa'], 'disponible')) {
                    error_log("DEBUG-RESTAURANT-ORDER: Could not set table " . $order['id_mesa'] . " to 'disponible' on order delete.");
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("DEBUG-RESTAURANT-ORDER ERROR: deleteOrder failed: " . $e->getMessage());
            return false;
        }
    }

    public function getPendingOrdersCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM pedidos_restaurante WHERE estado IN ('pendiente', 'en_preparacion', 'listo', 'entregado')");
        return $stmt->fetchColumn();
    }

    public function getActiveOrderByTableId($id_mesa) {
        $sql = "SELECT ro.id_pedido, ro.fecha_pedido, ro.total_pedido, ro.estado AS estado_pedido,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido,
                       ro.nombre_cliente AS cliente_externo_nombre, ro.telefono_cliente AS cliente_externo_telefono
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