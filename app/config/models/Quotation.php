<?php
// hotel_completo/app/models/Quotation.php

class Quotation {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Genera un nuevo número de cotización consecutivo.
     * Formato: COT-YYYYMMDD-N (COT-20240701-001)
     * @return string
     */
    public function generateNewQuotationNumber() {
        $today = date('Ymd');
        $sql = "SELECT COUNT(*) FROM cotizaciones WHERE nro_cotizacion LIKE 'COT-" . $today . "-%'";
        $stmt = $this->pdo->query($sql);
        $count = $stmt->fetchColumn();
        $nextNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT); // Rellenar con ceros a la izquierda
        return "COT-" . $today . "-" . $nextNumber;
    }

    /**
     * Obtiene todas las cotizaciones con detalles de vendedor y cliente.
     * @return array
     */
    public function getAll() {
        $sql = "SELECT c.*, u.nombre_usuario AS vendedor_nombre, u.apellido_usuario AS vendedor_apellido,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido
                FROM cotizaciones c
                JOIN usuarios u ON c.id_vendedor = u.id_usuario
                LEFT JOIN huespedes h ON c.id_cliente = h.id_huesped
                ORDER BY c.fecha_cotizacion DESC, c.nro_cotizacion DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una cotización por su ID con todos sus detalles y ítems.
     * @param int $id_cotizacion
     * @return array|false
     */
    public function getById($id_cotizacion) {
        // Obtener detalles de la cotización
        $sql = "SELECT c.*, u.nombre_usuario AS vendedor_nombre, u.apellido_usuario AS vendedor_apellido,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido
                FROM cotizaciones c
                JOIN usuarios u ON c.id_vendedor = u.id_usuario
                LEFT JOIN huespedes h ON c.id_cliente = h.id_huesped
                WHERE c.id_cotizacion = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_cotizacion]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quotation) {
            return false;
        }

        // Obtener ítems de la cotización
        $items_sql = "SELECT dc.*, pi.nombre_producto AS producto_inventario_nombre, pi.precio_compra AS producto_inventario_precio, pi.unidad_medida AS producto_inventario_unidad
                      FROM detalle_cotizacion dc
                      LEFT JOIN productos_inventario pi ON dc.id_producto = pi.id_producto
                      WHERE dc.id_cotizacion = ?";
        $items_stmt = $this->pdo->prepare($items_sql);
        $items_stmt->execute([$id_cotizacion]);
        $quotation['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        return $quotation;
    }

    /**
     * Crea una nueva cotización con sus ítems.
     * @param array $quotation_data Datos principales de la cotización.
     * @param array $items_data Array de ítems [{id_producto, descripcion_item, cantidad, precio_unitario}].
     * @return int|false El ID de la nueva cotización o false en fallo.
     */
    public function create($quotation_data, $items_data) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar el encabezado de la cotización
            $sql_quotation = "INSERT INTO cotizaciones (nro_cotizacion, fecha_cotizacion, oferta_valido_dias, tiempo_entrega_dias, garantia, incluido_igv, moneda, tipo_cambio, id_vendedor, condicion, atencion, comentario, id_cliente, cliente_razon_social, cliente_ruc_dni, cliente_direccion, cliente_email, subtotal, impuestos, total, estado, fecha_registro)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_quotation = $this->pdo->prepare($sql_quotation);

            $quotation_result = $stmt_quotation->execute([
                $quotation_data['nro_cotizacion'],
                $quotation_data['fecha_cotizacion'],
                $quotation_data['oferta_valido_dias'] ?? null,
                $quotation_data['tiempo_entrega_dias'] ?? null,
                $quotation_data['garantia'] ?? null,
                $quotation_data['incluido_igv'],
                $quotation_data['moneda'],
                $quotation_data['tipo_cambio'] ?? null,
                $quotation_data['id_vendedor'] ?? $_SESSION['user_id'] ?? null,
                $quotation_data['condicion'],
                $quotation_data['atencion'] ?? null,
                $quotation_data['comentario'] ?? null,
                $quotation_data['id_cliente'] ?? null,
                $quotation_data['cliente_razon_social'],
                $quotation_data['cliente_ruc_dni'] ?? null,
                $quotation_data['cliente_direccion'] ?? null,
                $quotation_data['cliente_email'] ?? null,
                $quotation_data['subtotal'],
                $quotation_data['impuestos'],
                $quotation_data['total'],
                $quotation_data['estado'] ?? 'Pendiente'
            ]);

            if (!$quotation_result) {
                throw new Exception("Error al crear el encabezado de la cotización.");
            }
            $id_cotizacion = $this->pdo->lastInsertId();

            // 2. Insertar los ítems de la cotización
            $sql_item = "INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, descripcion_item, cantidad, precio_unitario, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $this->pdo->prepare($sql_item);

            foreach ($items_data as $item) {
                $item_result = $stmt_item->execute([
                    $id_cotizacion,
                    $item['id_producto'] ?? null,
                    $item['descripcion_item'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                ]);
                if (!$item_result) {
                    throw new Exception("Error al insertar un ítem de la cotización.");
                }
            }

            $this->pdo->commit();
            return $id_cotizacion;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-QUOTATION ERROR: create failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una cotización existente con sus ítems.
     * @param int $id_cotizacion ID de la cotización a actualizar.
     * @param array $quotation_data Datos principales de la cotización.
     * @param array $items_data Array de ítems [{id_producto, descripcion_item, cantidad, precio_unitario}].
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_cotizacion, $quotation_data, $items_data) {
        try {
            $this->pdo->beginTransaction();

            // 1. Actualizar el encabezado de la cotización
            $sql_quotation = "UPDATE cotizaciones SET
                              fecha_cotizacion = ?, oferta_valido_dias = ?, tiempo_entrega_dias = ?, garantia = ?,
                              incluido_igv = ?, moneda = ?, tipo_cambio = ?, id_vendedor = ?, condicion = ?,
                              atencion = ?, comentario = ?, id_cliente = ?, cliente_razon_social = ?,
                              cliente_ruc_dni = ?, cliente_direccion = ?, cliente_email = ?,
                              subtotal = ?, impuestos = ?, total = ?, estado = ?
                              WHERE id_cotizacion = ?";
            $stmt_quotation = $this->pdo->prepare($sql_quotation);

            $quotation_result = $stmt_quotation->execute([
                $quotation_data['fecha_cotizacion'],
                $quotation_data['oferta_valido_dias'] ?? null,
                $quotation_data['tiempo_entrega_dias'] ?? null,
                $quotation_data['garantia'] ?? null,
                $quotation_data['incluido_igv'],
                $quotation_data['moneda'],
                $quotation_data['tipo_cambio'] ?? null,
                $quotation_data['id_vendedor'] ?? $_SESSION['user_id'] ?? null,
                $quotation_data['condicion'],
                $quotation_data['atencion'] ?? null,
                $quotation_data['comentario'] ?? null,
                $quotation_data['id_cliente'] ?? null,
                $quotation_data['cliente_razon_social'],
                $quotation_data['cliente_ruc_dni'] ?? null,
                $quotation_data['cliente_direccion'] ?? null,
                $quotation_data['cliente_email'] ?? null,
                $quotation_data['subtotal'],
                $quotation_data['impuestos'],
                $quotation_data['total'],
                $quotation_data['estado'] ?? 'Pendiente',
                $id_cotizacion
            ]);

            if (!$quotation_result) {
                throw new Exception("Error al actualizar el encabezado de la cotización.");
            }

            // 2. Eliminar ítems antiguos y insertar nuevos (método común para ítems variables)
            $stmt_delete_items = $this->pdo->prepare("DELETE FROM detalle_cotizacion WHERE id_cotizacion = ?");
            $stmt_delete_items->execute([$id_cotizacion]);

            $sql_item = "INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, descripcion_item, cantidad, precio_unitario, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $this->pdo->prepare($sql_item);

            foreach ($items_data as $item) {
                $item_result = $stmt_item->execute([
                    $id_cotizacion,
                    $item['id_producto'] ?? null,
                    $item['descripcion_item'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                ]);
                if (!$item_result) {
                    throw new Exception("Error al insertar un ítem de la cotización durante la actualización.");
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-QUOTATION ERROR: update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una cotización y todos sus ítems.
     * @param int $id_cotizacion ID de la cotización a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_cotizacion) {
        try {
            $this->pdo->beginTransaction();

            // Los ítems se borrarán automáticamente gracias a ON DELETE CASCADE en detalle_cotizacion

            $sql = "DELETE FROM cotizaciones WHERE id_cotizacion = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id_cotizacion]);

            if (!$result) {
                throw new Exception("Error al eliminar la cotización.");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-QUOTATION ERROR: delete failed: " . $e->getMessage());
            return false;
        }
    }
}
