<?php
// hotel_completo/app/models/Product.php

class Product {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets products with stock equal to or less than minimum stock.
     * Used for Dashboard alerts.
     * @param int $limit Limit of products to return.
     * @return array
     */
    public function getProductsWithLowStock($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT
                id_producto,
                nombre_producto,
                stock_actual,
                stock_minimo,
                unidad_medida
            FROM productos_inventario
            WHERE stock_actual <= stock_minimo AND stock_actual > 0
            ORDER BY stock_actual ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all inventory products with their category.
     * @return array
     */
    public function getAllProducts() {
        $sql = "SELECT pi.*, ci.nombre_categoria
                FROM productos_inventario pi
                JOIN categorias_inventario ci ON pi.id_categoria = ci.id_categoria
                ORDER BY ci.nombre_categoria ASC, pi.nombre_producto ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a product by its ID.
     * @param int $id_producto
     * @return array|false
     */
    public function getById($id_producto) {
        $sql = "SELECT pi.*, ci.nombre_categoria
                FROM productos_inventario pi
                JOIN categorias_inventario ci ON pi.id_categoria = ci.id_categoria
                WHERE pi.id_producto = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new product.
     * @param array $data Product data.
     * @return int|false The ID of the new product or false on failure.
     */
    public function create($data) {
        $sql = "INSERT INTO productos_inventario (id_categoria, nombre_producto, descripcion, unidad_medida, stock_actual, stock_minimo, precio_compra, es_insumo_restaurante, es_lenceria)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_categoria'],
            $data['nombre_producto'],
            $data['descripcion'] ?? null,
            $data['unidad_medida'],
            $data['stock_actual'] ?? 0,
            $data['stock_minimo'] ?? 0,
            $data['precio_compra'] ?? null,
            $data['es_insumo_restaurante'] ?? 0,
            $data['es_lenceria'] ?? 0
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates an existing product.
     * @param int $id_producto The ID of the product to update.
     * @param array $data The new product data.
     * @return bool True on success, False on failure.
     */
    public function update($id_producto, $data) {
        $sql = "UPDATE productos_inventario SET
                id_categoria = ?, nombre_producto = ?, descripcion = ?, unidad_medida = ?,
                stock_actual = ?, stock_minimo = ?, precio_compra = ?, es_insumo_restaurante = ?, es_lenceria = ?
                WHERE id_producto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['id_categoria'],
            $data['nombre_producto'],
            $data['descripcion'] ?? null,
            $data['unidad_medida'],
            $data['stock_actual'],
            $data['stock_minimo'],
            $data['precio_compra'] ?? null,
            $data['es_insumo_restaurante'] ?? 0,
            $data['es_lenceria'] ?? 0,
            $id_producto
        ]);
    }

    /**
     * Deletes a product.
     * @param int $id_producto The ID of the product to delete.
     * @return bool True on success, False on failure.
     */
    public function delete($id_producto) {
        $stmt = $this->pdo->prepare("DELETE FROM productos_inventario WHERE id_producto = ?");
        return $stmt->execute([$id_producto]);
    }

    /**
     * Updates the stock of a product.
     * @param int $id_producto
     * @param float $quantity_change Amount to add (positive) or subtract (negative).
     * @return bool True on success, False on failure.
     */
    public function updateStock($id_producto, $quantity_change) {
        $sql = "UPDATE productos_inventario SET stock_actual = stock_actual + ? WHERE id_producto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity_change, $id_producto]);
    }

    // --- NUEVOS MÉTODOS PARA EL DASHBOARD ---
    public function getTotalProductsCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos_inventario");
        return $stmt->fetchColumn();
    }

    public function getProductsWithLowStockCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos_inventario WHERE stock_actual <= stock_minimo AND stock_actual > 0");
        return $stmt->fetchColumn();
    }
}

