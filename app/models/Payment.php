<?php
// hotel_completo/app/models/Payment.php

class Payment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo registro de pago.
     * @param array $data Los datos del pago.
     * @return int|false El ID del nuevo pago o false en fallo.
     */
    public function create($data) {
        // Columnas a insertar: id_reserva, id_factura, monto, metodo_pago, referencia_transaccion, fecha_pago
        // fecha_pago usa NOW() directamente en SQL, no un placeholder.
        $sql = "INSERT INTO pagos (id_reserva, id_factura, monto, metodo_pago, referencia_transaccion, fecha_pago)
                VALUES (?, ?, ?, ?, ?, NOW())"; // ¡5 placeholders '?'

        // Prepara los datos para la ejecución
        $params = [
            $data['id_reserva'] ?? null,       // 1er parámetro (para id_reserva)
            $data['id_factura'] ?? null,       // 2do parámetro (para id_factura)
            $data['monto'],                    // 3er parámetro (para monto)
            $data['metodo_pago'],              // 4to parámetro (para metodo_pago)
            $data['referencia_transaccion'] ?? null // 5to parámetro (para referencia_transaccion)
            // NOW() se maneja directamente en SQL, no necesita un placeholder aquí.
        ];

        // --- DEPURACIÓN: Registra la SQL y los parámetros en el log de errores ---
        error_log("DEBUG-PAYMENT: SQL for Payment Create: " . $sql);
        error_log("DEBUG-PAYMENT: Params for Payment Create: " . print_r($params, true));
        // --- FIN DEPURACIÓN ---

        $stmt = $this->pdo->prepare($sql);
        try {
            $result = $stmt->execute($params);
            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            // Registra el error PDO detallado si la ejecución falla
            error_log("DEBUG-PAYMENT ERROR: PDOException on Payment create: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . print_r($params, true));
            throw $e; // Vuelve a lanzar la excepción para que el controlador la capture
        }
    }

    // Puedes añadir métodos para obtener pagos por reserva, por factura, etc.
}