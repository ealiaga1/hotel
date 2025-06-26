<?php
// hotel_completo/app/models/Guest.php

class Guest {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los huéspedes.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM huespedes ORDER BY apellido ASC, nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un huésped por su ID.
     * @param int $id_huesped
     * @return array|false
     */
    public function getById($id_huesped) {
        $stmt = $this->pdo->prepare("SELECT * FROM huespedes WHERE id_huesped = ?");
        $stmt->execute([$id_huesped]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca huéspedes por nombre, apellido, email o DNI/Pasaporte.
     * @param string $query Término de búsqueda.
     * @return array
     */
    public function searchGuests($query) {
        error_log("DEBUG-GUEST-MODEL: searchGuests called with query: " . $query);
        $query = '%' . $query . '%';
        $sql = "SELECT id_huesped, nombre, apellido, tipo_documento, numero_documento, email, telefono
                FROM huespedes
                WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR numero_documento LIKE ?
                ORDER BY apellido, nombre
                LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$query, $query, $query, $query]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG-GUEST-MODEL: SQL executed: " . $sql);
            error_log("DEBUG-GUEST-MODEL: SQL params: " . print_r([$query, $query, $query, $query], true));
            error_log("DEBUG-GUEST-MODEL: Search results count: " . count($results));
            error_log("DEBUG-GUEST-MODEL: Search results: " . print_r($results, true));
            return $results;
        } catch (PDOException $e) {
            error_log("DEBUG-GUEST-MODEL ERROR: PDOException in searchGuests: " . $e->getMessage() . " | SQL: " . $sql);
            return []; // Return empty array on error
        }
    }

    /**
     * Creates a new guest.
     * @param array $data The guest data.
     * @return int|false El ID del nuevo huésped o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO huespedes (nombre, apellido, tipo_documento, numero_documento, email, telefono, direccion, ciudad, pais, fecha_nacimiento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['tipo_documento'] ?? null,
            $data['numero_documento'] ?? null,
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['pais'] ?? null,
            $data['fecha_nacimiento'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza un huésped existente.
     * @param int $id_huesped El ID del huésped a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_huesped, $data) {
        $sql = "UPDATE huespedes SET
                nombre = ?, apellido = ?, tipo_documento = ?, numero_documento = ?,
                email = ?, telefono = ?, direccion = ?, ciudad = ?, pais = ?, fecha_nacimiento = ?
                WHERE id_huesped = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['tipo_documento'] ?? null,
            $data['numero_documento'] ?? null,
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['ciudad'] ?? null,
            $data['pais'] ?? null,
            $data['fecha_nacimiento'] ?? null,
            $id_huesped
        ]);
    }

    /**
     * Elimina un huésped.
     * @param int $id_huesped El ID del huésped a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_huesped) {
        $stmt = $this->pdo->prepare("DELETE FROM huespedes WHERE id_huesped = ?");
        return $stmt->execute([$id_huesped]);
    }

    /**
     * Adds a new charge to a guest's account.
     * @param array $data Charge data.
     * @return int|false The ID of the new charge or false on failure.
     */
    public function addGuestCharge($data) {
        $sql = "INSERT INTO cargos_huesped (id_huesped, id_reserva, descripcion, monto, fecha_cargo, estado, id_usuario_registro)
                VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_huesped'],
            $data['id_reserva'] ?? null,
            $data['descripcion'],
            $data['monto'],
            $data['estado'] ?? 'pendiente',
            $data['id_usuario_registro'] ?? $_SESSION['user_id'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Gets all pending (unpaid) charges for a specific guest, optionally linked to a booking.
     * @param int $id_huesped
     * @param int|null $id_reserva Optional booking ID to *filter* charges. If null, gets all for guest.
     * @return array
     */
    public function getPendingChargesForGuest($id_huesped, $id_reserva = null) {
        $sql = "SELECT id_cargo, descripcion, monto, fecha_cargo FROM cargos_huesped
                WHERE id_huesped = ? AND estado = 'pendiente'";
        $params = [$id_huesped];

        if ($id_reserva !== null) { // Si id_reserva es null, este filtro NO se aplica, y se traen todos los pendientes para el huésped.
            $sql .= " AND id_reserva = ?";
            $params[] = $id_reserva;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marks charges as paid or voided.
     * @param array $charge_ids Array of charge IDs to update.
     * @param string $new_status 'pagado' or 'anulado'.
     * @return bool
     */
    public function updateGuestChargesStatus($charge_ids, $new_status) {
        if (empty($charge_ids)) return true; // Nothing to update
        $placeholders = implode(',', array_fill(0, count($charge_ids), '?'));
        $sql = "UPDATE cargos_huesped SET estado = ? WHERE id_cargo IN (" . $placeholders . ")";
        $params = array_merge([$new_status], $charge_ids);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

public function updateGuestChargesAmount($id_cargo, $new_amount) {
        $sql = "UPDATE cargos_huesped SET monto = ? WHERE id_cargo = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$new_amount, $id_cargo]);
    }
    

    // --- NUEVO MÉTODO PARA EL DASHBOARD ---
    public function getTotalGuestsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM huespedes");
        return $stmt->fetchColumn();
    }
}
