<?php
// hotel_completo/app/models/ConventionBooking.php

class ConventionBooking {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las reservas de convenciones con detalles de sala y cliente.
     * @param array $filters Filtros opcionales (estado, fecha, nombre_cliente).
     * @return array
     */
    public function searchBookings($filters = []) {
        $sql = "SELECT rcv.*,
                       sc.nombre_sala, sc.capacidad_max AS sala_capacidad, sc.precio_hora_base AS sala_precio_hora,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido, h.numero_documento AS huesped_documento
                FROM reservas_convenciones rcv
                JOIN salas_convenciones sc ON rcv.id_sala = sc.id_sala
                LEFT JOIN huespedes h ON rcv.id_huesped = h.id_huesped
                WHERE 1=1"; // Condición base para añadir filtros

        $params = [];

        if (!empty($filters['status']) && is_array($filters['status'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $sql .= " AND rcv.estado IN (" . $placeholders . ")";
            $params = array_merge($params, $filters['status']);
        } elseif (!($filters['show_all'] ?? false)) {
            // Filtro por defecto: Mostrar pendientes y confirmadas si no hay otro filtro de estado o 'show_all'
            $sql .= " AND rcv.estado IN (?, ?)";
            $params = array_merge($params, ['pendiente', 'confirmada']);
        }

        if (!empty($filters['client_name'])) {
            $search_term = '%' . $filters['client_name'] . '%';
            $sql .= " AND (rcv.nombre_contacto LIKE ? OR h.nombre LIKE ? OR h.apellido LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND rcv.fecha_evento >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND rcv.fecha_evento <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY rcv.fecha_evento DESC, rcv.hora_inicio DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una reserva de convenciones por su ID con todos sus detalles.
     * @param int $id_reserva_convencion
     * @return array|false
     */
    public function getById($id_reserva_convencion) {
        $sql = "SELECT rcv.*,
                       sc.nombre_sala, sc.capacidad_max AS sala_capacidad, sc.precio_hora_base AS sala_precio_hora,
                       h.nombre AS huesped_nombre, h.apellido AS huesped_apellido, h.numero_documento AS huesped_documento
                FROM reservas_convenciones rcv
                JOIN salas_convenciones sc ON rcv.id_sala = sc.id_sala
                LEFT JOIN huespedes h ON rcv.id_huesped = h.id_huesped
                WHERE rcv.id_reserva_convencion = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_reserva_convencion]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return false;
        }

        // Obtener detalles de servicios/equipos
        $items_sql = "SELECT * FROM detalle_evento_servicios WHERE id_reserva_convencion = ?";
        $items_stmt = $this->pdo->prepare($items_sql);
        $items_stmt->execute([$id_reserva_convencion]);
        $booking['services'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

        return $booking;
    }

    /**
     * Crea una nueva reserva de convenciones con sus servicios.
     * @param array $booking_data Datos principales de la reserva.
     * @param array $services_data Array de servicios/equipos.
     * @return int|false El ID de la nueva reserva o false en fallo.
     */
    public function create($booking_data, $services_data) {
        try {
            $this->pdo->beginTransaction();

            $sql_booking = "INSERT INTO reservas_convenciones (id_sala, id_huesped, nombre_contacto, telefono_contacto, email_contacto, nombre_evento, fecha_evento, hora_inicio, hora_fin, num_asistentes, precio_total, estado, comentarios, fecha_registro)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_booking = $this->pdo->prepare($sql_booking);
            $booking_result = $stmt_booking->execute([
                $booking_data['id_sala'],
                $booking_data['id_huesped'] ?? null,
                $booking_data['nombre_contacto'],
                $booking_data['telefono_contacto'] ?? null,
                $booking_data['email_contacto'] ?? null,
                $booking_data['nombre_evento'],
                $booking_data['fecha_evento'],
                $booking_data['hora_inicio'],
                $booking_data['hora_fin'],
                $booking_data['num_asistentes'] ?? null,
                $booking_data['precio_total'],
                $booking_data['estado'] ?? 'pendiente',
                $booking_data['comentarios'] ?? null
            ]);

            if (!$booking_result) {
                throw new Exception("Error al crear la reserva de convenciones.");
            }
            $id_reserva_convencion = $this->pdo->lastInsertId();

            // Insertar servicios/equipos
            $sql_service = "INSERT INTO detalle_evento_servicios (id_reserva_convencion, descripcion_servicio, cantidad, precio_unitario, subtotal, comentarios_item)
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_service = $this->pdo->prepare($sql_service);

            foreach ($services_data as $service) {
                $subtotal = $service['cantidad'] * $service['precio_unitario'];
                $service_result = $stmt_service->execute([
                    $id_reserva_convencion,
                    $service['descripcion_servicio'],
                    $service['cantidad'],
                    $service['precio_unitario'],
                    $subtotal,
                    $service['comentarios_item'] ?? null
                ]);
                if (!$service_result) {
                    throw new Exception("Error al insertar un servicio del evento.");
                }
            }

            $this->pdo->commit();
            return $id_reserva_convencion;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-CONVENTION-BOOKING ERROR: create failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una reserva de convenciones existente con sus servicios.
     * @param int $id_reserva_convencion El ID de la reserva a actualizar.
     * @param array $booking_data Los nuevos datos de la reserva.
     * @param array $services_data Array de servicios/equipos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_reserva_convencion, $booking_data, $services_data) {
        try {
            $this->pdo->beginTransaction();

            $sql_booking = "UPDATE reservas_convenciones SET
                            id_sala = ?, id_huesped = ?, nombre_contacto = ?, telefono_contacto = ?, email_contacto = ?,
                            nombre_evento = ?, fecha_evento = ?, hora_inicio = ?, hora_fin = ?, num_asistentes = ?,
                            precio_total = ?, estado = ?, comentarios = ?
                            WHERE id_reserva_convencion = ?";
            $stmt_booking = $this->pdo->prepare($sql_booking);
            $booking_result = $stmt_booking->execute([
                $booking_data['id_sala'],
                $booking_data['id_huesped'] ?? null,
                $booking_data['nombre_contacto'],
                $booking_data['telefono_contacto'] ?? null,
                $booking_data['email_contacto'] ?? null,
                $booking_data['nombre_evento'],
                $booking_data['fecha_evento'],
                $booking_data['hora_inicio'],
                $booking_data['hora_fin'],
                $booking_data['num_asistentes'] ?? null,
                $booking_data['precio_total'],
                $booking_data['estado'],
                $booking_data['comentarios'] ?? null,
                $id_reserva_convencion
            ]);

            if (!$booking_result) {
                throw new Exception("Error al actualizar la reserva de convenciones.");
            }

            // Eliminar servicios antiguos y insertar nuevos
            $stmt_delete_services = $this->pdo->prepare("DELETE FROM detalle_evento_servicios WHERE id_reserva_convencion = ?");
            $stmt_delete_services->execute([$id_reserva_convencion]);

            $sql_service = "INSERT INTO detalle_evento_servicios (id_reserva_convencion, descripcion_servicio, cantidad, precio_unitario, subtotal, comentarios_item)
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_service = $this->pdo->prepare($sql_service);

            foreach ($services_data as $service) {
                $subtotal = $service['cantidad'] * $service['precio_unitario'];
                $service_result = $stmt_service->execute([
                    $id_reserva_convencion,
                    $service['descripcion_servicio'],
                    $service['cantidad'],
                    $service['precio_unitario'],
                    $subtotal,
                    $service['comentarios_item'] ?? null
                ]);
                if (!$service_result) {
                    throw new Exception("Error al insertar un servicio del evento durante la actualización.");
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-CONVENTION-BOOKING ERROR: update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de una reserva de convenciones.
     * @param int $id_reserva_convencion
     * @param string $new_status
     * @return bool
     */
    public function updateStatus($id_reserva_convencion, $new_status) {
        $stmt = $this->pdo->prepare("UPDATE reservas_convenciones SET estado = ? WHERE id_reserva_convencion = ?");
        return $stmt->execute([$new_status, $id_reserva_convencion]);
    }

    /**
     * Elimina una reserva de convenciones y sus servicios.
     * @param int $id_reserva_convencion
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_reserva_convencion) {
        try {
            $this->pdo->beginTransaction();

            // Los servicios se borrarán automáticamente gracias a ON DELETE CASCADE
            $sql = "DELETE FROM reservas_convenciones WHERE id_reserva_convencion = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id_reserva_convencion]);

            if (!$result) {
                throw new Exception("Error al eliminar la reserva de convenciones.");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("DEBUG-CONVENTION-BOOKING ERROR: delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta el número de reservas de convenciones activas para el dashboard.
     * @return int
     */
    public function getPendingBookingsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM reservas_convenciones WHERE estado IN ('pendiente', 'confirmada')");
        return $stmt->fetchColumn();
    }
}
