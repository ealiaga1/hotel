<?php
// hotel_completo/app/controllers/InventoryController.php

// Required models
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/InventoryCategory.php';
require_once __DIR__ . '/../models/InventoryMovementType.php';
require_once __DIR__ . '/../models/InventoryMovement.php';
require_once __DIR__ . '/../models/User.php'; // For getting user (staff) details for movements

class InventoryController {
    private $productModel;
    private $inventoryCategoryModel;
    private $inventoryMovementTypeModel;
    private $inventoryMovementModel;
    private $userModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->productModel = new Product($pdo);
        $this->inventoryCategoryModel = new InventoryCategory($pdo);
        $this->inventoryMovementTypeModel = new InventoryMovementType($pdo);
        $this->inventoryMovementModel = new InventoryMovement($pdo);
        $this->userModel = new User($pdo); // For staff info in movements
    }

    /**
     * Displays the main inventory dashboard or product list.
     */
    public function index() {
        $title = "Gestión de Inventario";
        $products = $this->productModel->getAllProducts();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'inventory/products/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    // --- Inventory Categories Management ---

    /**
     * Displays the list of inventory categories.
     */
    public function categories() {
        $title = "Gestión de Categorías de Inventario";
        $categories = $this->inventoryCategoryModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'inventory/categories/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new inventory category or processes its creation.
     */
    public function createCategory() {
        $title = "Crear Nueva Categoría de Inventario";
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_categoria' => trim($_POST['nombre_categoria'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            if (empty($data['nombre_categoria'])) {
                $error_message = 'El nombre de la categoría es obligatorio.';
            } else {
                try {
                    $id_categoria = $this->inventoryCategoryModel->create($data);
                    if ($id_categoria) {
                        $_SESSION['success_message'] = 'Categoría de inventario creada exitosamente.';
                        header('Location: /hotel_completo/public/inventory/categories');
                        exit();
                    } else {
                        $error_message = 'Error al crear la categoría. El nombre podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe una categoría con ese nombre.';
                    } else {
                        $error_message = 'Error de base de datos al crear categoría: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'inventory/categories/create.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit an inventory category or processes its update.
     * @param int $id_categoria
     */
    public function editCategory($id_categoria) {
        $title = "Editar Categoría de Inventario";
        $category = $this->inventoryCategoryModel->getById($id_categoria);
        $error_message = '';
        $success_message = '';

        if (!$category) {
            $_SESSION['error_message'] = 'Categoría no encontrada.';
            header('Location: /hotel_completo/public/inventory/categories');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_categoria' => trim($_POST['nombre_categoria'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            if (empty($data['nombre_categoria'])) {
                $error_message = 'El nombre de la categoría es obligatorio.';
            } else {
                try {
                    if ($this->inventoryCategoryModel->update($id_categoria, $data)) {
                        $_SESSION['success_message'] = 'Categoría actualizada exitosamente.';
                        $category = $this->inventoryCategoryModel->getById($id_categoria); // Reload
                    } else {
                        $error_message = 'Error al actualizar la categoría. El nombre podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe otra categoría con ese nombre.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar categoría: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'inventory/categories/edit.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of an inventory category.
     * @param int $id_categoria
     */
    public function deleteCategory($id_categoria) {
        try {
            if ($this->inventoryCategoryModel->delete($id_categoria)) {
                $_SESSION['success_message'] = 'Categoría eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la categoría.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la categoría porque tiene productos de inventario asociados.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar la categoría: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/inventory/categories');
        exit();
    }

    // --- Inventory Products Management ---

    /**
     * Displays the form to create a new inventory product or processes its creation.
     */
    public function createProduct() {
        $title = "Crear Nuevo Producto de Inventario";
        $categories = $this->inventoryCategoryModel->getAll(); // For category dropdown

        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_categoria' => $_POST['id_categoria'] ?? '',
                'nombre_producto' => trim($_POST['nombre_producto'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'unidad_medida' => trim($_POST['unidad_medida'] ?? ''),
                'stock_actual' => (float)($_POST['stock_actual'] ?? 0),
                'stock_minimo' => (float)($_POST['stock_minimo'] ?? 0),
                'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
                'es_insumo_restaurante' => isset($_POST['es_insumo_restaurante']) ? 1 : 0,
                'es_lenceria' => isset($_POST['es_lenceria']) ? 1 : 0
            ];

            // Validation
            if (empty($data['nombre_producto']) || empty($data['id_categoria']) || empty($data['unidad_medida'])) {
                $error_message = 'Nombre del producto, Categoría y Unidad de Medida son obligatorios.';
            } elseif (!is_numeric($data['stock_actual']) || $data['stock_actual'] < 0 || !is_numeric($data['stock_minimo']) || $data['stock_minimo'] < 0) {
                $error_message = 'El stock actual y mínimo deben ser números positivos o cero.';
            } elseif (!is_numeric($data['precio_compra']) || $data['precio_compra'] < 0) {
                 $error_message = 'El precio de compra debe ser un número positivo o cero.';
            } else {
                try {
                    $id_producto = $this->productModel->create($data);
                    if ($id_producto) {
                        $_SESSION['success_message'] = 'Producto de inventario creado exitosamente.';
                        header('Location: /hotel_completo/public/inventory'); // Redirect to products list
                        exit();
                    } else {
                        $error_message = 'Error al crear el producto. El nombre podría ya existir.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al crear producto: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'inventory/products/create.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit an inventory product or processes its update.
     * @param int $id_producto
     */
    public function editProduct($id_producto) {
        $title = "Editar Producto de Inventario";
        $product = $this->productModel->getById($id_producto);
        $categories = $this->inventoryCategoryModel->getAll();

        $error_message = '';
        $success_message = '';

        if (!$product) {
            $_SESSION['error_message'] = 'Producto de inventario no encontrado.';
            header('Location: /hotel_completo/public/inventory');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_categoria' => $_POST['id_categoria'] ?? '',
                'nombre_producto' => trim($_POST['nombre_producto'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'unidad_medida' => trim($_POST['unidad_medida'] ?? ''),
                'stock_actual' => (float)($_POST['stock_actual'] ?? 0),
                'stock_minimo' => (float)($_POST['stock_minimo'] ?? 0),
                'precio_compra' => (float)($_POST['precio_compra'] ?? 0),
                'es_insumo_restaurante' => isset($_POST['es_insumo_restaurante']) ? 1 : 0,
                'es_lenceria' => isset($_POST['es_lenceria']) ? 1 : 0
            ];

            // Validation
            if (empty($data['nombre_producto']) || empty($data['id_categoria']) || empty($data['unidad_medida'])) {
                $error_message = 'Nombre del producto, Categoría y Unidad de Medida son obligatorios.';
            } elseif (!is_numeric($data['stock_actual']) || $data['stock_actual'] < 0 || !is_numeric($data['stock_minimo']) || $data['stock_minimo'] < 0) {
                $error_message = 'El stock actual y mínimo deben ser números positivos o cero.';
            } elseif (!is_numeric($data['precio_compra']) || $data['precio_compra'] < 0) {
                 $error_message = 'El precio de compra debe ser un número positivo o cero.';
            } else {
                try {
                    if ($this->productModel->update($id_producto, $data)) {
                        $_SESSION['success_message'] = 'Producto de inventario actualizado exitosamente.';
                        $product = $this->productModel->getById($id_producto); // Reload
                    } else {
                        $error_message = 'Error al actualizar el producto.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al actualizar producto: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'inventory/products/edit.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of an inventory product.
     * @param int $id_producto
     */
    public function deleteProduct($id_producto) {
        try {
            if ($this->productModel->delete($id_producto)) {
                $_SESSION['success_message'] = 'Producto de inventario eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el producto.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar el producto porque tiene movimientos de inventario o está asociado a platos de menú.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el producto: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/inventory');
        exit();
    }

    // --- Inventory Movements Management ---

    /**
     * Displays the list of all inventory movements.
     */
    public function movements() {
        $title = "Historial de Movimientos de Inventario";
        $movements = $this->inventoryMovementModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'inventory/movements/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new inventory movement (entrada/salida) or processes it.
     */
    public function createMovement() {
        $title = "Registrar Nuevo Movimiento de Inventario";
        $products = $this->productModel->getAllProducts(); // All products for selection
        $movementTypes = $this->inventoryMovementTypeModel->getAll(); // All movement types (entrada, salida, etc.)

        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_producto' => $_POST['id_producto'] ?? '',
                'id_tipo_movimiento' => $_POST['id_tipo_movimiento'] ?? '',
                'cantidad' => (float)($_POST['cantidad'] ?? 0),
                'referencia' => trim($_POST['referencia'] ?? ''),
                'id_usuario' => $_SESSION['user_id'] ?? null // The logged-in user
            ];

            if (empty($data['id_producto']) || empty($data['id_tipo_movimiento']) || $data['cantidad'] <= 0) {
                $error_message = 'Producto, Tipo de Movimiento y Cantidad son obligatorios y la cantidad debe ser positiva.';
            } else {
                // Additional validation: if it's a 'salida' or 'descarte', check if enough stock exists
                $movement_type_info = $this->inventoryMovementTypeModel->getById($data['id_tipo_movimiento']);
                $product_info = $this->productModel->getById($data['id_producto']);

                if (!$movement_type_info || !$product_info) {
                    $error_message = 'Producto o Tipo de Movimiento no válidos.';
                } elseif (in_array($movement_type_info['nombre_movimiento'], ['salida', 'descarte', 'lavanderia_envio'])) {
                    if ($product_info['stock_actual'] < $data['cantidad']) {
                        $error_message = 'Stock insuficiente para esta salida. Stock actual: ' . $product_info['stock_actual'] . ' ' . $product_info['unidad_medida'];
                    }
                }

                if (!$error_message) {
                    try {
                        $id_movimiento = $this->inventoryMovementModel->create($data);
                        if ($id_movimiento) {
                            $_SESSION['success_message'] = 'Movimiento de inventario registrado exitosamente.';
                            header('Location: /hotel_completo/public/inventory/movements');
                            exit();
                        } else {
                            $error_message = 'Error al registrar el movimiento de inventario.';
                        }
                    } catch (Exception $e) { // Catch Exception from model's create (transaction errors)
                        $_SESSION['error_message'] = 'Error de base de datos al registrar movimiento: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'inventory/movements/create.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }
}