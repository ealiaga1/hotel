<?php
// hotel_completo/app/models/MenuCategory.php

class MenuCategory {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las categorías del menú.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM categorias_menu ORDER BY nombre_categoria ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una categoría de menú por su ID.
     * @param int $id_categoria_menu
     * @return array|false
     */
    public function getById($id_categoria_menu) {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias_menu WHERE id_categoria_menu = ?");
        $stmt->execute([$id_categoria_menu]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva categoría de menú.
     * @param array $data Los datos de la categoría.
     * @return int|false El ID de la nueva categoría o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO categorias_menu (nombre_categoria, descripcion) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['nombre_categoria'],
            $data['descripcion'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza una categoría de menú existente.
     * @param int $id_categoria_menu El ID de la categoría a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_categoria_menu, $data) {
        $sql = "UPDATE categorias_menu SET nombre_categoria = ?, descripcion = ? WHERE id_categoria_menu = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre_categoria'],
            $data['descripcion'] ?? null,
            $id_categoria_menu
        ]);
    }

    /**
     * Elimina una categoría de menú.
     * @param int $id_categoria_menu El ID de la categoría a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_categoria_menu) {
        // Considerar restricciones de clave foránea con platos_menu
        $stmt = $this->pdo->prepare("DELETE FROM categorias_menu WHERE id_categoria_menu = ?");
        return $stmt->execute([$id_categoria_menu]);
    }
}