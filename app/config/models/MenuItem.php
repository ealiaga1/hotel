<?php
// hotel_completo/app/models/MenuItem.php

class MenuItem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los platos del menú con su categoría.
     * @return array
     */
    public function getAllMenuItems() {
        $sql = "SELECT mi.*, mc.nombre_categoria
                FROM platos_menu mi
                JOIN categorias_menu mc ON mi.id_categoria_menu = mc.id_categoria_menu
                ORDER BY mc.nombre_categoria ASC, mi.nombre_plato ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un plato del menú por su ID.
     * @param int $id_plato
     * @return array|false
     */
    public function getById($id_plato) {
        $sql = "SELECT mi.*, mc.nombre_categoria
                FROM platos_menu mi
                JOIN categorias_menu mc ON mi.id_categoria_menu = mc.id_categoria_menu
                WHERE mi.id_plato = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_plato]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo plato del menú.
     * @param array $data Los datos del plato.
     * @return int|false El ID del nuevo plato o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO platos_menu (id_categoria_menu, nombre_plato, descripcion, precio, foto_url, disponible) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_categoria_menu'],
            $data['nombre_plato'],
            $data['descripcion'] ?? null,
            $data['precio'],
            $data['foto_url'] ?? null,
            $data['disponible'] ?? 1
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza un plato del menú existente.
     * @param int $id_plato El ID del plato a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_plato, $data) {
        $sql = "UPDATE platos_menu SET id_categoria_menu = ?, nombre_plato = ?, descripcion = ?, precio = ?, foto_url = ?, disponible = ? WHERE id_plato = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['id_categoria_menu'],
            $data['nombre_plato'],
            $data['descripcion'] ?? null,
            $data['precio'],
            $data['foto_url'] ?? null,
            $data['disponible'] ?? 1,
            $id_plato
        ]);
    }

    /**
     * Elimina un plato del menú.
     * @param int $id_plato El ID del plato a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_plato) {
        // Considerar restricciones de clave foránea con detalle_pedido
        $stmt = $this->pdo->prepare("DELETE FROM platos_menu WHERE id_plato = ?");
        return $stmt->execute([$id_plato]);
    }
}