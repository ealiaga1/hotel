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
     * @param array $data Transaction data.
     * @return int|false The ID of the new transaction or false on failure.
     */
    public function create($data) {
        // CORRECCIÓN: Se añadió 'id_usuario' a la lista de columnas.
        // La fecha_transaccion usa NOW() directamente en SQL.
        $sql = "INSERT INTO transacciones_caja (id_movimiento_caja, id_pago, id_factura, descripcion, monto, tipo_transaccion, fecha_transaccion, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)"; // ¡7 placeholders '?'

        $params = [
            $data['id_movimiento_caja'],        // 1
            $data['id_pago'] ?? null,           // 2
            $data['id_factura'] ?? null,        // 3
            $data['descripcion'],               // 4
            $data['monto'],                     // 5
            $data['tipo_transaccion'],          // 6
            // NOW() no necesita un placeholder aquí.
            $data['id_usuario'] ?? $_SESSION['user_id'] ?? null // 7
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