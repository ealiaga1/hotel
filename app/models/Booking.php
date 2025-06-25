<?php
// hotel_completo/app/models/Booking.php

class Booking {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene el número de reservas pendientes/confirmadas para el dashboard.
     * @return int
     */
    public function getPendingBookingsCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservas WHERE estado IN ('pendiente', 'confirmada') AND fecha_entrada >= CURDATE()");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Obtiene los huéspedes actualmente en el hotel (reservas en estado 'check_in').
     * @return int
     */
    public function getCurrentGuestsInHouseCount() {
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT id_huesped) FROM reservas WHERE estado = 'check_in'");
        return $stmt->fetchColumn();
    }

    /**
     * Obtiene las últimas reservas para el dashboard.
     * @param int $limit
     * @return array
     */
    public function getLatestBookings($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT
                r.id_reserva,
                h.nombre AS huesped_nombre,
                h.apellido AS huesped_apellido,
                CASE
                    WHEN hab.numero_habitacion IS NOT NULL THEN hab.numero_habitacion
                    ELSE 'Por asignar'
                END AS numero_habitacion,
                r.fecha_entrada,
                r.fecha_salida,
                r.estado
            FROM reservas r
            JOIN huespedes h ON r.id_huesped = h.id_huesped
            LEFT JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
            ORDER BY r.fecha_reserva DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las reservas con su información detallada.
     * @return array
     */
    public function getAllBookings() {
        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_entrada,
                    r.fecha_salida,
                    r.adultos,
                    r.ninos,
                    r.precio_total,
                    r.estado,
                    h.nombre AS huesped_nombre,
                    h.apellido AS huesped_apellido,
                    h.telefono AS huesped_telefono,
                    h.email AS huesped_email,
                    th.nombre_tipo AS tipo_habitacion_nombre,
                    hab.numero_habitacion
                FROM reservas r
                JOIN huespedes h ON r.id_huesped = h.id_huesped
                LEFT JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                LEFT JOIN tipos_habitacion th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                ORDER BY r.fecha_entrada DESC, r.fecha_reserva DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una reserva por su ID con detalles completos.
     * @param int $id_reserva
     * @return array|false
     */
    public function getBookingById($id_reserva) {
        $sql = "SELECT
                    r.*,
                    h.nombre AS huesped_nombre,
                    h.apellido AS huesped_apellido,
                    h.tipo_documento,
                    h.numero_documento,
                    h.email AS huesped_email,
                    h.telefono AS huesped_telefono,
                    h.direccion,
                    h.ciudad,
                    h.pais,
                    h.fecha_nacimiento,
                    th.nombre_tipo AS tipo_habitacion_nombre,
                    th.precio_base AS tipo_habitacion_precio_base,
                    hab.numero_habitacion,
                    hab.estado AS habitacion_estado
                FROM reservas r
                JOIN huespedes h ON r.id_huesped = h.id_huesped
                LEFT JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                LEFT JOIN tipos_habitacion th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                WHERE r.id_reserva = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_reserva]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva reserva.
     * @param array $data Los datos de la reserva.
     * @return int|false El ID de la nueva reserva o false en fallo.
     */
    public function createBooking($data) {
        $sql = "INSERT INTO reservas (id_huesped, id_habitacion, fecha_entrada, fecha_salida, adultos, ninos, precio_total, estado, comentarios, fecha_reserva)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_huesped'],
            $data['id_habitacion'] ?? null,
            $data['fecha_entrada'],
            $data['fecha_salida'],
            $data['adultos'],
            $data['ninos'] ?? 0,
            $data['precio_total'],
            $data['estado'] ?? 'pendiente',
            $data['comentarios'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza una reserva existente.
     * @param int $id_reserva El ID de la reserva a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function updateBooking($id_reserva, $data) {
        $sql = "UPDATE reservas SET
                id_huesped = ?, id_habitacion = ?, fecha_entrada = ?, fecha_salida = ?,
                adultos = ?, ninos = ?, precio_total = ?, estado = ?, comentarios = ?
                WHERE id_reserva = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['id_huesped'],
            $data['id_habitacion'] ?? null,
            $data['fecha_entrada'],
            $data['fecha_salida'],
            $data['adultos'],
            $data['ninos'] ?? 0,
            $data['precio_total'],
            $data['estado'],
            $data['comentarios'] ?? null,
            $id_reserva
        ]);
    }

    /**
     * Elimina una reserva.
     * @param int $id_reserva El ID de la reserva a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function deleteBooking($id_reserva) {
        $stmt = $this->pdo->prepare("DELETE FROM reservas WHERE id_reserva = ?");
        return $stmt->execute([$id_reserva]);
    }

    /**
     * Updates the status of a booking and the associated room.
     * @param int $id_reserva The booking ID.
     * @param string $new_booking_status The new status for the booking (e.g., 'check_in', 'check_out', 'cancelled').
     * @param int|null $id_habitacion The room ID, if assigning or freeing.
     * @param string|null $new_room_status The new status for the room (e.g., 'occupied', 'dirty', 'available').
     * @return bool True on success, False on failure.
     */
    public function updateBookingStatusAndRoom($id_reserva, $new_booking_status, $id_habitacion = null, $new_room_status = null) {
        try {
            error_log("DEBUG-BOOKING-MODEL: updateBookingStatusAndRoom called. Booking ID: " . $id_reserva . ", New Booking Status: " . $new_booking_status . ", Room ID: " . ($id_habitacion ?? 'NULL') . ", New Room Status: " . ($new_room_status ?? 'NULL'));

            // 1. Update booking status
            $sql_booking = "UPDATE reservas SET estado = ? WHERE id_reserva = ?";
            $stmt_booking = $this->pdo->prepare($sql_booking);
            $booking_update_result = $stmt_booking->execute([$new_booking_status, $id_reserva]);
            error_log("DEBUG-BOOKING-MODEL: Booking update SQL: " . $sql_booking . " | Params: " . print_r([$new_booking_status, $id_reserva], true) . " | Result: " . ($booking_update_result ? 'TRUE' : 'FALSE'));

            if (!$booking_update_result) {
                error_log("DEBUG-BOOKING-MODEL ERROR: Failed to update booking status for ID: " . $id_reserva);
                return false;
            }

            // 2. Update room status if room ID and new status are provided
            if ($id_habitacion !== null && $new_room_status !== null) {
                $sql_room = "UPDATE habitaciones SET estado = ? WHERE id_habitacion = ?";
                $stmt_room = $this->pdo->prepare($sql_room);
                $room_update_result = $stmt_room->execute([$new_room_status, $id_habitacion]);
                error_log("DEBUG-BOOKING-MODEL: Room update SQL: " . $sql_room . " | Params: " . print_r([$new_room_status, $id_habitacion], true) . " | Result: " . ($room_update_result ? 'TRUE' : 'FALSE'));

                if (!$room_update_result) {
                    error_log("DEBUG-BOOKING-MODEL ERROR: Failed to update room status for ID: " . $id_habitacion);
                    return false;
                }
            }

            return true;

        } catch (PDOException $e) {
            error_log("DEBUG-BOOKING-MODEL ERROR: PDOException in updateBookingStatusAndRoom: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode());
            return false;
        }
    }


    /**
     * Gets available rooms for a given date range.
     * @param string $fecha_entrada // Format: YYYY-MM-DD
     * @param string $fecha_salida  // Format: YYYY-MM-DD
     * @param int $capacidad_minima Minimum required capacity.
     * @param int|null $id_tipo_habitacion_preferido Optional: Preferred room type ID.
     * @return array
     */
    public function getAvailableRooms($fecha_entrada, $fecha_salida, $capacidad_minima = 1, $id_tipo_habitacion_preferido = null) {
        $sql = "
            SELECT
                h.id_habitacion,
                h.numero_habitacion,
                h.estado,
                h.piso,
                th.id_tipo_habitacion,
                th.nombre_tipo,
                th.capacidad,
                th.precio_base
            FROM habitaciones h
            JOIN tipos_habitacion th ON h.id_tipo_habitacion = th.id_tipo_habitacion
            WHERE h.estado IN ('disponible', 'sucia')
              AND th.capacidad >= ?
              AND h.id_habitacion NOT IN (
                  SELECT r.id_habitacion
                  FROM reservas r
                  WHERE r.id_habitacion IS NOT NULL
                    AND r.estado IN ('confirmada', 'check_in')
                    AND (
                        (r.fecha_entrada < ? AND r.fecha_salida > ?)
                        OR (r.fecha_entrada BETWEEN ? AND ?)
                        OR (r.fecha_salida BETWEEN ? AND ?)
                        OR (? BETWEEN r.fecha_entrada AND r.fecha_salida AND ? BETWEEN r.fecha_entrada AND r.fecha_salida)
                    )
              )
        ";

        $params = [
            $capacidad_minima,
            $fecha_salida, $fecha_entrada,
            $fecha_entrada, $fecha_salida,
            $fecha_entrada, $fecha_salida,
            $fecha_entrada, $fecha_salida
        ];

        if ($id_tipo_habitacion_preferido !== null && $id_tipo_habitacion_preferido != '') {
            $sql .= " AND th.id_tipo_habitacion = ?";
            $params[] = $id_tipo_habitacion_preferido;
        }

        $sql .= " ORDER BY th.precio_base ASC, h.numero_habitacion ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la reserva activa (estado 'check_in') para una habitación específica.
     * Incluye datos básicos del huésped.
     * @param int $id_habitacion
     * @return array|false Detalles de la reserva activa o false si no hay.
     */
    public function getActiveBookingByRoomId($id_habitacion) {
        $sql = "SELECT r.id_reserva, r.fecha_entrada, r.fecha_salida, r.adultos, r.ninos,
                       h.id_huesped, h.nombre AS huesped_nombre, h.apellido AS huesped_apellido, h.telefono
                FROM reservas r
                JOIN huespedes h ON r.id_huesped = h.id_huesped
                WHERE r.id_habitacion = ? AND r.estado = 'check_in'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_habitacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las reservas que se solapan con un rango de fechas.
     * Utilizado para el calendario.
     * @param string $start_date Fecha de inicio del rango (ej. 'YYYY-MM-01').
     * @param string $end_date Fecha de fin del rango (ej. 'YYYY-MM-31').
     * @return array Lista de reservas con detalles de habitación y huésped.
     */
    public function getBookingsByDateRange($start_date, $end_date) {
        error_log("DEBUG-BOOKING-MODEL: getBookingsByDateRange called.");
        error_log("DEBUG-BOOKING-MODEL: Start Date: " . $start_date . ", End Date: " . $end_date);

        $sql = "SELECT r.id_reserva, r.fecha_entrada, r.fecha_salida, r.estado,
                       hab.numero_habitacion, hab.id_habitacion,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido
                FROM reservas r
                LEFT JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                LEFT JOIN huespedes h ON r.id_huesped = h.id_huesped
                WHERE r.fecha_entrada <= ? AND r.fecha_salida >= ?
                  AND r.estado IN ('confirmada', 'check_in', 'pendiente')
                ORDER BY r.fecha_entrada ASC";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$end_date, $start_date]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG-BOOKING-MODEL: SQL executed for getBookingsByDateRange: " . $sql);
            error_log("DEBUG-BOOKING-MODEL: Params: " . print_r([$end_date, $start_date], true));
            error_log("DEBUG-BOOKING-MODEL: Bookings found for calendar: " . count($results));
            error_log("DEBUG-BOOKING-MODEL: Bookings Data: " . print_r($results, true));
            return $results;
        } catch (PDOException $e) {
            error_log("DEBUG-BOOKING-MODEL ERROR: PDOException in getBookingsByDateRange: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca reservas por diferentes filtros.
     * @param array $filters Opciones de filtro: 'status', 'start_date', 'end_date', 'guest_name'.
     * @return array
     */
    public function searchBookings($filters = []) {
        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_entrada,
                    r.fecha_salida,
                    r.adultos,
                    r.ninos,
                    r.precio_total,
                    r.estado,
                    h.nombre AS huesped_nombre,
                    h.apellido AS huesped_apellido,
                    h.telefono AS huesped_telefono,
                    h.email AS huesped_email,
                    th.nombre_tipo AS tipo_habitacion_nombre,
                    hab.numero_habitacion
                FROM reservas r
                JOIN huespedes h ON r.id_huesped = h.id_huesped
                LEFT JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                LEFT JOIN tipos_habitacion th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                WHERE 1=1"; // Start with a true condition to easily append AND clauses

        $params = [];

        // Filter by status (default to active ones if no status filter provided and show_all is false)
        // Si $filters['status'] está vacío y $filters['show_all'] es falso, aplicar filtro por defecto
        if (!empty($filters['status'])) {
            $status_placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $sql .= " AND r.estado IN (" . $status_placeholders . ")";
            $params = array_merge($params, $filters['status']);
        } elseif (!($filters['show_all'] ?? false)) { // Solo aplicar filtro por defecto si NO se pidió 'show_all'
            $sql .= " AND r.estado IN (?, ?, ?)";
            $params = array_merge($params, ['check_in', 'confirmada', 'pendiente']);
        }


        // Filter by date range (fecha_entrada or fecha_salida)
        if (!empty($filters['start_date'])) {
            $sql .= " AND r.fecha_entrada >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND r.fecha_salida <= ?";
            $params[] = $filters['end_date'];
        }

        // Filter by guest name
        if (!empty($filters['guest_name'])) {
            $guest_search_query = '%' . $filters['guest_name'] . '%';
            $sql .= " AND (h.nombre LIKE ? OR h.apellido LIKE ?)";
            $params[] = $guest_search_query;
            $params[] = $guest_search_query;
        }

        $sql .= " ORDER BY r.fecha_entrada DESC, r.fecha_reserva DESC";

        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG-BOOKING-MODEL: searchBookings SQL: " . $sql);
            error_log("DEBUG-BOOKING-MODEL: searchBookings Params: " . print_r($params, true));
            error_log("DEBUG-BOOKING-MODEL: searchBookings Results count: " . count($results));
            return $results;
        } catch (PDOException $e) {
            error_log("DEBUG-BOOKING-MODEL ERROR: PDOException in searchBookings: " . $e->getMessage());
            return [];
        }
    }
}
