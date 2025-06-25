<?php
// hotel_completo/app/models/Invoice.php

class Invoice {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene el total de ingresos por facturas pagadas en una fecha específica.
     * @param string $date La fecha en formato 'YYYY-MM-DD'.
     * @return float
     */
    public function getDailyRevenue($date) {
        $stmt = $this->pdo->prepare("SELECT SUM(monto_total) FROM facturas WHERE DATE(fecha_emision) = ? AND estado = 'pagada'");
        $stmt->execute([$date]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Crea una nueva factura.
     * @param array $data Los datos de la factura.
     * @return int|false El ID de la nueva factura o false en fallo.
     */
    public function create($data) {
        // CORRECCIÓN: Se eliminó 'fecha_emision' de la lista de columnas
        // ya que la base de datos usa DEFAULT CURRENT_TIMESTAMP.
        $sql = "INSERT INTO facturas (id_reserva, id_huesped, fecha_vencimiento, monto_total, impuestos, descuentos, estado, tipo_documento, serie_documento, numero_documento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // ¡Ahora son 10 placeholders '?'

        // Prepara los datos para la ejecución
        $params = [
            $data['id_reserva'] ?? null,       // 1er parámetro (para id_reserva)
            $data['id_huesped'],               // 2do parámetro (para id_huesped)
            // Ya no hay placeholder para fecha_emision
            $data['fecha_vencimiento'] ?? null, // 3er parámetro (para fecha_vencimiento)
            $data['monto_total'],              // 4to parámetro (para monto_total)
            $data['impuestos'] ?? 0.00,        // 5to parámetro (para impuestos)
            $data['descuentos'] ?? 0.00,       // 6to parámetro (para descuentos)
            $data['estado'] ?? 'pendiente',    // 7mo parámetro (para estado)
            $data['tipo_documento'] ?? 'Boleta',// 8vo parámetro (para tipo_documento)
            $data['serie_documento'],          // 9no parámetro (para serie_documento)
            $data['numero_documento']          // 10mo parámetro (para numero_documento)
        ];

        error_log("DEBUG-INVOICE: SQL for Invoice Create: " . $sql);
        error_log("DEBUG-INVOICE: Params for Invoice Create (" . count($params) . " total): " . print_r($params, true));

        $stmt = $this->pdo->prepare($sql);
        try {
            $result = $stmt->execute($params);
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("DEBUG-INVOICE ERROR: PDOException on Invoice create: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . print_r($params, true));
            throw $e;
        }
    }

    /**
     * Obtiene todas las facturas con detalles de huésped y reserva.
     * @return array
     */
    public function getAllInvoices() {
        $sql = "SELECT i.*, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido,
                       b.id_reserva AS booking_id, b.fecha_entrada AS booking_fecha_entrada, b.fecha_salida AS booking_fecha_salida
                FROM facturas i
                JOIN huespedes h ON i.id_huesped = h.id_huesped
                LEFT JOIN reservas b ON i.id_reserva = b.id_reserva
                ORDER BY i.fecha_emision DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una factura por su ID con detalles completos.
     * @param int $id_factura
     * @return array|false
     */
    public function getInvoiceById($id_factura) {
        $sql = "SELECT i.*, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido,
                       h.numero_documento AS huesped_documento, h.email AS huesped_email,
                       b.id_reserva AS booking_id, b.fecha_entrada AS booking_fecha_entrada, b.fecha_salida AS booking_fecha_salida,
                       b.adultos AS booking_adultos, b.ninos AS booking_ninos,
                       hab.numero_habitacion, pm.monto AS monto_pago, pm.metodo_pago AS metodo_pago_principal
                FROM facturas i
                JOIN huespedes h ON i.id_huesped = h.id_huesped
                LEFT JOIN reservas b ON i.id_reserva = b.id_reserva
                LEFT JOIN habitaciones hab ON b.id_habitacion = hab.id_habitacion
                LEFT JOIN pagos pm ON i.id_factura = pm.id_factura AND pm.id_reserva = i.id_reserva -- Intenta vincular el pago principal
                WHERE i.id_factura = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_factura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates the status of an invoice.
     * @param int $id_factura
     * @param string $new_status The new status ('pendiente', 'pagada', 'anulada').
     * @return bool True on success, False on failure.
     */
    public function updateStatus($id_factura, $new_status) {
        $sql = "UPDATE facturas SET estado = ? WHERE id_factura = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$new_status, $id_factura]);
    }
}