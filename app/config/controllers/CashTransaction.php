<?php
// hotel_completo/app/models/CashTransaction.php

class CashTransaction {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all transactions for a specific cash register shift.
     * @param int $id_movimiento_caja The ID of the cash register shift.
     * @return array
     */
    public function getTransactionsByRegisterId($id_movimiento_caja) {
        $sql = "SELECT ct.*, u.nombre_usuario AS usuario_nombre, u.apellido_usuario AS usuario_apellido
                FROM transacciones_caja ct
                LEFT JOIN usuarios u ON ct.id_usuario = u.id_usuario
                WHERE ct.id_movimiento_caja = ?
                ORDER BY ct.fecha_transaccion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_movimiento_caja]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new cash transaction.
     * This method can be used for manual income/expense records.
     * @param array $data Transaction data including id_movimiento_caja, description, amount, type, and method.
     * @return int|false The ID of the new transaction or false on failure.
     */
    public function create($data) {
        // MODIFICACIÓN: Se añadió 'metodo_pago' a la lista de columnas y al array de parámetros
        $sql = "INSERT INTO transacciones_caja (id_movimiento_caja, id_pago, id_factura, descripcion, monto, tipo_transaccion, metodo_pago, fecha_transaccion, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $params = [
            $data['id_movimiento_caja'],
            $data['id_pago'] ?? null,
            $data['id_factura'] ?? null,
            $data['descripcion'],
            $data['monto'],
            $data['tipo_transaccion'],
            $data['metodo_pago'] ?? null, // Nuevo parámetro
            $data['id_usuario'] ?? $_SESSION['user_id'] ?? null
        ];

        error_log("DEBUG-CASH-TRANSACTION: SQL for Cash Transaction Create: " . $sql);
        error_log("DEBUG-CASH-TRANSACTION: Params for Cash Transaction Create (" . count($params) . " total): " . print_r($params, true));

        $stmt = $this->pdo->prepare($sql);
        try {
            $result = $stmt->execute($params);
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("DEBUG-CASH-TRANSACTION ERROR: PDOException on Cash Transaction create: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . print_r($params, true));
            throw $e;
        }
    }

    // You can add methods to update/delete specific transactions if needed.
}
