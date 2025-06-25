<?php
// hotel_completo/app/models/Table.php

class Table {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all tables.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM mesas ORDER BY numero_mesa ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a table by its ID.
     * @param int $id_mesa
     * @return array|false
     */
    public function getById($id_mesa) {
        $stmt = $this->pdo->prepare("SELECT * FROM mesas WHERE id_mesa = ?");
        $stmt->execute([$id_mesa]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new table.
     * @param array $data The table data.
     * @return int|false The ID of the new table or false on failure.
     */
    public function create($data) {
        $sql = "INSERT INTO mesas (numero_mesa, capacidad, ubicacion, estado) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['numero_mesa'],
            $data['capacidad'],
            $data['ubicacion'] ?? null,
            $data['estado'] ?? 'disponible'
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates an existing table.
     * @param int $id_mesa The ID of the table to update.
     * @param array $data The new data.
     * @return bool True on success, False on failure.
     */
    public function update($id_mesa, $data) {
        $sql = "UPDATE mesas SET numero_mesa = ?, capacidad = ?, ubicacion = ?, estado = ? WHERE id_mesa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['numero_mesa'],
            $data['capacidad'],
            $data['ubicacion'] ?? null,
            $data['estado'],
            $id_mesa
        ]);
    }

    /**
     * Deletes a table.
     * @param int $id_mesa The ID of the table to delete.
     * @return bool True on success, False on failure.
     */
    public function delete($id_mesa) {
        $stmt = $this->pdo->prepare("DELETE FROM mesas WHERE id_mesa = ?");
        return $stmt->execute([$id_mesa]);
    }

    /**
     * Updates the status of a table.
     * @param int $id_mesa The ID of the table.
     * @param string $new_status The new status ('disponible', 'ocupada', 'reservada', 'en_limpieza').
     * @return bool True on success, False on failure.
     */
    public function updateStatus($id_mesa, $new_status) {
        $stmt = $this->pdo->prepare("UPDATE mesas SET estado = ? WHERE id_mesa = ?");
        return $stmt->execute([$new_status, $id_mesa]);
    }

    // --- NUEVO MÉTODO PARA EL DASHBOARD ---
    public function getOccupiedTablesCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mesas WHERE estado = 'ocupada'");
        return $stmt->fetchColumn();
    }
}