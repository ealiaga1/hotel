<?php
// hotel_completo/app/models/PoolReservation.php

class PoolReservation {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all pool reservations with guest details.
     * @return array
     */
    public function getAll() {
        $sql = "SELECT pr.*, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido
                FROM reservas_piscina pr
                LEFT JOIN huespedes h ON pr.id_huesped = h.id_huesped
                ORDER BY pr.fecha_reserva DESC, pr.hora_inicio DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a pool reservation by its ID.
     * @param int $id_reserva_piscina
     * @return array|false
     */
    public function getById($id_reserva_piscina) {
        $sql = "SELECT pr.*, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido,
                       h.numero_documento AS huesped_documento
                FROM reservas_piscina pr
                LEFT JOIN huespedes h ON pr.id_huesped = h.id_huesped
                WHERE pr.id_reserva_piscina = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_reserva_piscina]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new pool reservation.
     * @param array $data The reservation data.
     * @return int|false The ID of the new reservation or false on failure.
     */
    public function create($data) {
        $sql = "INSERT INTO reservas_piscina (id_huesped, nombre_cliente, telefono_cliente, fecha_reserva, hora_inicio, hora_fin, cantidad_personas, precio_total, estado, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_huesped'] ?? null,
            $data['nombre_cliente'] ?? null,
            $data['telefono_cliente'] ?? null,
            $data['fecha_reserva'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['cantidad_personas'],
            $data['precio_total'],
            $data['estado'] ?? 'confirmada'
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates an existing pool reservation.
     * @param int $id_reserva_piscina The ID of the reservation to update.
     * @param array $data The new data.
     * @return bool True on success, False on failure.
     */
    public function update($id_reserva_piscina, $data) {
        $sql = "UPDATE reservas_piscina SET
                id_huesped = ?, nombre_cliente = ?, telefono_cliente = ?, fecha_reserva = ?,
                hora_inicio = ?, hora_fin = ?, cantidad_personas = ?, precio_total = ?, estado = ?
                WHERE id_reserva_piscina = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['id_huesped'] ?? null,
            $data['nombre_cliente'] ?? null,
            $data['telefono_cliente'] ?? null,
            $data['fecha_reserva'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['cantidad_personas'],
            $data['precio_total'],
            $data['estado'],
            $id_reserva_piscina
        ]);
    }

    /**
     * Updates the status of a pool reservation.
     * @param int $id_reserva_piscina The ID of the reservation.
     * @param string $new_status The new status.
     * @return bool
     */
    public function updateStatus($id_reserva_piscina, $new_status) {
        $stmt = $this->pdo->prepare("UPDATE reservas_piscina SET estado = ? WHERE id_reserva_piscina = ?");
        return $stmt->execute([$new_status, $id_reserva_piscina]);
    }

    /**
     * Deletes a pool reservation.
     * @param int $id_reserva_piscina The ID of the reservation to delete.
     * @return bool True on success, False on failure.
     */
    public function delete($id_reserva_piscina) {
        $stmt = $this->pdo->prepare("DELETE FROM reservas_piscina WHERE id_reserva_piscina = ?");
        return $stmt->execute([$id_reserva_piscina]);
    }

    /**
     * Checks for overlapping pool reservations for a given date and time range.
     * Excludes a specific reservation ID (for editing).
     * @param string $fecha_reserva
     * @param string $hora_inicio
     * @param string $hora_fin
     * @param int|null $exclude_id_reserva_piscina ID of reservation to exclude from overlap check.
     * @return int Count of overlapping reservations.
     */
    public function countOverlappingReservations($fecha_reserva, $hora_inicio, $hora_fin, $exclude_id_reserva_piscina = null) {
        $sql = "SELECT COUNT(*) FROM reservas_piscina
                WHERE fecha_reserva = ?
                AND estado IN ('confirmada')
                AND (
                    (hora_inicio < ? AND hora_fin > ?)
                    OR (hora_inicio >= ? AND hora_inicio < ?)
                    OR (hora_fin > ? AND hora_fin <= ?)
                    OR (? <= hora_inicio AND ? >= hora_fin)
                )";

        $params = [
            $fecha_reserva,
            $hora_fin, $hora_inicio,
            $hora_inicio, $hora_fin,
            $hora_inicio, $hora_fin,
            $hora_inicio, $hora_fin
        ];

        if ($exclude_id_reserva_piscina !== null) {
            $sql .= " AND id_reserva_piscina != ?";
            $params[] = $exclude_id_reserva_piscina;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Gets the count of pending/active reservations for the dashboard.
     * @param string $today Current date for filtering.
     * @return int
     */
    public function getTodayReservationsCount($today) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservas_piscina WHERE fecha_reserva = ? AND estado IN ('confirmada', 'completada')");
        $stmt->execute([$today]);
        return $stmt->fetchColumn();
    }

    /**
     * Searches and filters pool reservations based on provided criteria.
     * @param array $filters An associative array of filters (e.g., 'client_name', 'status', 'start_date', 'end_date', 'show_all').
     * @return array List of filtered pool reservations.
     */
    public function searchReservations($filters = []) {
        $sql = "SELECT pr.*, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido
                FROM reservas_piscina pr
                LEFT JOIN huespedes h ON pr.id_huesped = h.id_huesped
                WHERE 1=1"; // Start with a true condition to easily append AND clauses

        $params = [];

        // Filter by client name (huesped o cliente externo)
        if (!empty($filters['client_name'])) {
            $client_search_query = '%' . $filters['client_name'] . '%';
            $sql .= " AND (h.nombre LIKE ? OR h.apellido LIKE ? OR pr.nombre_cliente LIKE ?)";
            $params[] = $client_search_query;
            $params[] = $client_search_query;
            $params[] = $client_search_query;
        }

        // Filter by status
        // Si $filters['status'] está vacío y $filters['show_all'] es falso, aplicar filtro por defecto
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $status_placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $sql .= " AND pr.estado IN (" . $status_placeholders . ")";
            $params = array_merge($params, $filters['status']);
        } elseif (!($filters['show_all'] ?? false)) { // Solo aplicar filtro por defecto si NO se pidió 'show_all'
            $sql .= " AND pr.estado IN (?, ?)"; // confirmada, pendiente
            $params = array_merge($params, ['confirmada', 'pendiente']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $sql .= " AND pr.fecha_reserva >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND pr.fecha_reserva <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY pr.fecha_reserva DESC, pr.hora_inicio DESC";

        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("DEBUG-POOL-RESERVATION-MODEL ERROR: PDOException in searchReservations: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . print_r($params, true));
            return [];
        }
    }
}