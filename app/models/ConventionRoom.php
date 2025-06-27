<?php
// hotel_completo/app/models/ConventionRoom.php

class ConventionRoom {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las salas de convenciones.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM salas_convenciones ORDER BY nombre_sala ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una sala de convenciones por su ID.
     * @param int $id_sala
     * @return array|false
     */
    public function getById($id_sala) {
        $stmt = $this->pdo->prepare("SELECT * FROM salas_convenciones WHERE id_sala = ?");
        $stmt->execute([$id_sala]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva sala de convenciones.
     * @param array $data Los datos de la sala.
     * @return int|false El ID de la nueva sala o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO salas_convenciones (nombre_sala, capacidad_max, precio_hora_base, descripcion, estado, ubicacion)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nombre_sala'],
            $data['capacidad_max'],
            $data['precio_hora_base'],
            $data['descripcion'] ?? null,
            $data['estado'] ?? 'disponible',
            $data['ubicacion'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza una sala de convenciones existente.
     * @param int $id_sala El ID de la sala a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_sala, $data) {
        $sql = "UPDATE salas_convenciones SET
                nombre_sala = ?, capacidad_max = ?, precio_hora_base = ?, descripcion = ?, estado = ?, ubicacion = ?
                WHERE id_sala = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre_sala'],
            $data['capacidad_max'],
            $data['precio_hora_base'],
            $data['descripcion'] ?? null,
            $data['estado'],
            $data['ubicacion'] ?? null,
            $id_sala
        ]);
    }

    /**
     * Elimina una sala de convenciones.
     * @param int $id_sala El ID de la sala a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_sala) {
        $stmt = $this->pdo->prepare("DELETE FROM salas_convenciones WHERE id_sala = ?");
        return $stmt->execute([$id_sala]);
    }

    /**
     * Busca salas disponibles en un rango de fecha y hora.
     * @param string $fecha_evento
     * @param string $hora_inicio
     * @param string $hora_fin
     * @param int|null $exclude_id_reserva_convencion Para excluir una reserva al editar.
     * @return array
     */
    public function getAvailableRooms($fecha_evento, $hora_inicio, $hora_fin, $exclude_id_reserva_convencion = null) {
        $sql = "
            SELECT sc.id_sala, sc.nombre_sala, sc.capacidad_max, sc.precio_hora_base
            FROM salas_convenciones sc
            WHERE sc.estado = 'disponible'
            AND sc.id_sala NOT IN (
                SELECT rcv.id_sala
                FROM reservas_convenciones rcv
                WHERE rcv.fecha_evento = ?
                AND rcv.estado IN ('pendiente', 'confirmada')
                AND (
                    (rcv.hora_inicio < ? AND rcv.hora_fin > ?) -- Solapamiento completo
                    OR (rcv.hora_inicio >= ? AND rcv.hora_inicio < ?)      -- Inicio de reserva existente dentro del rango buscado
                    OR (rcv.hora_fin > ? AND rcv.hora_fin <= ?)         -- Fin de reserva existente dentro del rango buscado
                    OR (? <= rcv.hora_inicio AND ? >= rcv.hora_fin) -- Rango buscado envuelve reserva existente
                )
        ";
        
        $params = [$fecha_evento, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin];

        if ($exclude_id_reserva_convencion !== null) {
            $sql .= " AND rcv.id_reserva_convencion != ?";
            $params[] = $exclude_id_reserva_convencion;
        }
        $sql .= ")"; // Cierra el NOT IN

        $sql .= " ORDER BY sc.nombre_sala ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
