<?php
// hotel_completo/app/models/InventoryCategory.php

class InventoryCategory {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all inventory categories.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM categorias_inventario ORDER BY nombre_categoria ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets an inventory category by its ID.
     * @param int $id_categoria
     * @return array|false
     */
    public function getById($id_categoria) {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias_inventario WHERE id_categoria = ?");
        $stmt->execute([$id_categoria]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new inventory category.
     * @param array $data The category data.
     * @return int|false The ID of the new category or false on failure.
     */
    public function create($data) {
        $sql = "INSERT INTO categorias_inventario (nombre_categoria, descripcion) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nombre_categoria'],
            $data['descripcion'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates an existing inventory category.
     * @param int $id_categoria The ID of the category to update.
     * @param array $data The new data.
     * @return bool True on success, False on failure.
     */
    public function update($id_categoria, $data) {
        $sql = "UPDATE categorias_inventario SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre_categoria'],
            $data['descripcion'] ?? null,
            $id_categoria
        ]);
    }

    /**
     * Deletes an inventory category.
     * @param int $id_categoria The ID of the category to delete.
     * @return bool True on success, False on failure.
     */
    public function delete($id_categoria) {
        // Consider foreign key constraints with productos_inventario
        $stmt = $this->pdo->prepare("DELETE FROM categorias_inventario WHERE id_categoria = ?");
        return $stmt->execute([$id_categoria]);
    }
}