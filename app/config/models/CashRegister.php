<?php
// hotel_completo/app/models/CashRegister.php

class CashRegister {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets the currently open cash register.
     * @return array|false The open register details or false if none is open.
     */
    public function getOpenRegister() {
        $sql = "SELECT cr.*, u.nombre_usuario AS apertura_nombre, u.apellido_usuario AS apertura_apellido
                FROM movimientos_caja cr
                JOIN usuarios u ON cr.id_usuario_apertura = u.id_usuario
                WHERE cr.estado = 'abierta'
                LIMIT 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a specific cash register shift by its ID.
     * @param int $register_id
     * @return array|false
     */
    public function getRegisterById($register_id) {
        $sql = "SELECT cr.*,
                       u_apertura.nombre_usuario AS apertura_nombre, u_apertura.apellido_usuario AS apertura_apellido,
                       u_cierre.nombre_usuario AS cierre_nombre, u_cierre.apellido_usuario AS cierre_apellido
                FROM movimientos_caja cr
                JOIN usuarios u_apertura ON cr.id_usuario_apertura = u_apertura.id_usuario
                LEFT JOIN usuarios u_cierre ON cr.id_usuario_cierre = u_cierre.id_usuario
                WHERE cr.id_movimiento_caja = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$register_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Opens a new cash register shift.
     * @param float $initial_balance The starting balance.
     * @param int $user_id The ID of the user opening the register.
     * @return int|false The ID of the new cash register shift or false on failure.
     */
    public function openRegister($initial_balance, $user_id) {
        $sql = "INSERT INTO movimientos_caja (id_usuario_apertura, fecha_apertura, saldo_inicial, estado)
                VALUES (?, NOW(), ?, 'abierta')";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([$user_id, $initial_balance]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Closes an active cash register shift and updates its totals.
     * @param int $register_id The ID of the cash register shift to close.
     * @param float $final_balance The ending balance.
     * @param float $total_ingresos The sum of all income transactions during the shift.
     * @param float $total_egresos The sum of all expense transactions during the shift.
     * @param int $user_id The ID of the user closing the register.
     * @return bool True on success, False on failure.
     */
    public function closeRegister($register_id, $final_balance, $total_ingresos, $total_egresos, $user_id) {
        $sql = "UPDATE movimientos_caja SET
                id_usuario_cierre = ?, fecha_cierre = NOW(), saldo_final = ?,
                total_ingresos = ?, total_egresos = ?, estado = 'cerrada'
                WHERE id_movimiento_caja = ? AND estado = 'abierta'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$user_id, $final_balance, $total_ingresos, $total_egresos, $register_id]);
    }

    /**
     * Gets summary data for a specific cash register shift (total income/expenses).
     * @param int $register_id The ID of the cash register shift.
     * @return array Associative array with 'total_ingresos' and 'total_egresos'.
     */
    public function getRegisterSummary($register_id) {
        $sql = "SELECT
                    SUM(CASE WHEN tipo_transaccion = 'ingreso' THEN monto ELSE 0 END) AS total_ingresos,
                    SUM(CASE WHEN tipo_transaccion = 'egreso' THEN monto ELSE 0 END) AS total_egresos
                FROM transacciones_caja
                WHERE id_movimiento_caja = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$register_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_ingresos' => (float)($summary['total_ingresos'] ?? 0),
            'total_egresos' => (float)($summary['total_egresos'] ?? 0)
        ];
    }

    /**
     * Gets all closed cash register shifts for history.
     * @return array
     */
    public function getAllClosedRegisters() {
        $sql = "SELECT cr.*,
                       u_apertura.nombre_usuario AS apertura_nombre, u_apertura.apellido_usuario AS apertura_apellido,
                       u_cierre.nombre_usuario AS cierre_nombre, u_cierre.apellido_usuario AS cierre_apellido
                FROM movimientos_caja cr
                JOIN usuarios u_apertura ON cr.id_usuario_apertura = u_apertura.id_usuario
                LEFT JOIN usuarios u_cierre ON cr.id_usuario_cierre = u_cierre.id_usuario
                WHERE cr.estado = 'cerrada'
                ORDER BY cr.fecha_cierre DESC, cr.fecha_apertura DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets income and expense totals grouped by payment method for a given cash register shift.
     * @param int $register_id
     * @return array Associative array where keys are payment methods.
     */
    public function getMethodTotalsForRegister($register_id) {
        $sql = "SELECT
                    metodo_pago,
                    SUM(CASE WHEN tipo_transaccion = 'ingreso' THEN monto ELSE 0 END) AS total_ingresos,
                    SUM(CASE WHEN tipo_transaccion = 'egreso' THEN monto ELSE 0 END) AS total_egresos
                FROM transacciones_caja
                WHERE id_movimiento_caja = ?
                GROUP BY metodo_pago";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$register_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted_results = [];
        foreach ($results as $row) {
            $method = $row['metodo_pago'] ?? 'Desconocido';
            $formatted_results[$method] = [
                'ingresos' => (float)($row['total_ingresos'] ?? 0.00),
                'egresos' => (float)($row['total_egresos'] ?? 0.00) // CORRECCIÓN AQUÍ: Se cambió $row['egresos'] a $row['total_egresos']
            ];
        }
        return $formatted_results;
    }

    /**
     * Gets income totals by document type (Factura/Boleta) if applicable for a given cash register shift.
     * This relies on transacciones_caja having id_factura linked to the facturas table.
     * @param int $register_id
     * @return array Associative array where keys are document types.
     */
    public function getDocumentTypeTotalsForRegister($register_id) {
        $sql = "SELECT
                    f.tipo_documento,
                    SUM(tc.monto) AS total_monto
                FROM transacciones_caja tc
                JOIN facturas f ON tc.id_factura = f.id_factura
                WHERE tc.id_movimiento_caja = ? AND tc.tipo_transaccion = 'ingreso'
                GROUP BY f.tipo_documento";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$register_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted_results = [];
        foreach ($results as $row) {
            $doc_type = $row['tipo_documento'] ?? 'Desconocido';
            $formatted_results[$doc_type] = (float)($row['total_monto'] ?? 0.00);
        }
        return $formatted_results;
    }

    /**
     * Gets the total amount of pending charges (Por Cobrar) for all guests.
     * @return float
     */
    public function getTotalPendingCharges() {
        $stmt = $this->pdo->query("SELECT SUM(monto) FROM cargos_huesped WHERE estado = 'pendiente'");
        return (float) $stmt->fetchColumn();
    }
}

