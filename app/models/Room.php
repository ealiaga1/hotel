<?php
// hotel_completo/app/models/Room.php

class Room {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las habitaciones con su tipo, capacidad y precio base.
     * Es la utilizada para el listado general de habitaciones.
     * @return array
     */
    public function getAll() {
        // CORRECCIÓN: Incluir th.capacidad y th.precio_base
        $sql = "SELECT h.*, th.nombre_tipo, th.capacidad, th.precio_base
                FROM habitaciones h
                JOIN tipos_habitacion th ON h.id_tipo_habitacion = th.id_tipo_habitacion
                ORDER BY h.numero_habitacion ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una habitación por su ID.
     * @param int $id_habitacion
     * @return array|false
     */
    public function getRoomById($id_habitacion) {
        $stmt = $this->pdo->prepare("SELECT h.*, th.nombre_tipo, th.precio_base FROM habitaciones h JOIN tipos_habitacion th ON h.id_tipo_habitacion = th.id_tipo_habitacion WHERE h.id_habitacion = ?");
        $stmt->execute([$id_habitacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva habitación.
     * @param array $data Los datos de la habitación.
     * @return int|false El ID de la nueva habitación o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO habitaciones (id_tipo_habitacion, numero_habitacion, piso, estado) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_tipo_habitacion'],
            $data['numero_habitacion'],
            $data['piso'] ?? null,
            $data['estado'] ?? 'disponible'
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza una habitación existente.
     * @param int $id_habitacion El ID de la habitación a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_habitacion, $data) {
        $sql = "UPDATE habitaciones SET id_tipo_habitacion = ?, numero_habitacion = ?, piso = ?, estado = ? WHERE id_habitacion = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['id_tipo_habitacion'],
            $data['numero_habitacion'],
            $data['piso'] ?? null,
            $data['estado'],
            $id_habitacion
        ]);
    }

    /**
     * Actualiza solo el estado de una habitación.
     * @param int $id_habitacion
     * @param string $estado
     * @return bool
     */
    public function updateRoomStatus($id_habitacion, $estado) {
        $stmt = $this->pdo->prepare("UPDATE habitaciones SET estado = ? WHERE id_habitacion = ?");
        return $stmt->execute([$estado, $id_habitacion]);
    }

    /**
     * Elimina una habitación.
     * @param int $id_habitacion El ID de la habitación a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_habitacion) {
        $stmt = $this->pdo->prepare("DELETE FROM habitaciones WHERE id_habitacion = ?");
        return $stmt->execute([$id_habitacion]);
    }

    // --- MÉTODOS PARA EL DASHBOARD ---
    public function getTotalRoomsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM habitaciones");
        return $stmt->fetchColumn();
    }

    public function getAvailableRoomsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM habitaciones WHERE estado = 'disponible'");
        return $stmt->fetchColumn();
    }

    public function getOccupiedRoomsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM habitaciones WHERE estado = 'ocupada'");
        return $stmt->fetchColumn();
    }

    public function getRoomsInMaintenanceCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM habitaciones WHERE estado = 'mantenimiento'");
        return $stmt->fetchColumn();
    }

    // --- MÉTODOS PARA RECEPCIÓN ---
    /**
     * Obtiene todas las habitaciones con detalles de su tipo.
     * Útil para el tablero de recepción.
     * @return array
     */
    public function getAllRoomsWithDetails() {
        $sql = "SELECT h.*, th.nombre_tipo, th.capacidad
                FROM habitaciones h
                JOIN tipos_habitacion th ON h.id_tipo_habitacion = th.id_tipo_habitacion
                ORDER BY h.numero_habitacion ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}