<?php
// hotel_completo/app/controllers/QuotationController.php

require_once __DIR__ . '/../models/Quotation.php';
require_once __DIR__ . '/../models/User.php';    // Para el vendedor
require_once __DIR__ . '/../models/Guest.php';    // Para seleccionar cliente
require_once __DIR__ . '/../models/Product.php';  // Para seleccionar productos de inventario

class QuotationController {
    private $quotationModel;
    private $userModel;
    private $guestModel;
    private $productModel;
    private $pdo; // Para transacciones si se necesitan a nivel de controlador

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->quotationModel = new Quotation($this->pdo);
        $this->userModel = new User($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->productModel = new Product($this->pdo);
    }

    /**
     * Displays the list of all quotations.
     */
    public function index() {
        $title = "Gestión de Cotizaciones";
        $quotations = $this->quotationModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'quotations/index.php';
        // Pasar la lista de cotizaciones a la vista
        extract([
            'quotations' => $quotations,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new quotation or processes its creation.
     */
    public function create() {
        $title = "Crear Nueva Cotización";
        $error_message = '';
        $success_message = '';

        $new_quotation_number = $this->quotationModel->generateNewQuotationNumber();
        $users = $this->userModel->getAllUsers(); // Para el selector de vendedor
        $products = $this->productModel->getAllProducts(); // Para el selector de productos

        // Datos para los desplegables de Ubigeo (Perú)
        $departamentos = [
            'Amazonas', 'Ancash', 'Apurimac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco',
            'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto',
            'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente_tipo = $_POST['cliente_tipo'] ?? 'nuevo'; // 'existente' o 'nuevo'
            $id_cliente_existente = ($cliente_tipo === 'existente') ? ($_POST['id_cliente_existente'] ?? null) : null;

            // Datos de la cotización
            $quotation_data = [
                'nro_cotizacion' => trim($_POST['nro_cotizacion'] ?? ''),
                'fecha_cotizacion' => trim($_POST['fecha_cotizacion'] ?? ''),
                'oferta_valido_dias' => (int)($_POST['oferta_valido_dias'] ?? 0),
                'tiempo_entrega_dias' => (int)($_POST['tiempo_entrega_dias'] ?? 0),
                'garantia' => trim($_POST['garantia'] ?? ''),
                'incluido_igv' => trim($_POST['incluido_igv'] ?? 'Mas IGV'),
                'moneda' => trim($_POST['moneda'] ?? 'Soles'),
                'tipo_cambio' => (float)($_POST['tipo_cambio'] ?? 1.00),
                'id_vendedor' => $_POST['id_vendedor'] ?? $_SESSION['user_id'] ?? null,
                'condicion' => trim($_POST['condicion'] ?? 'Contado'),
                'atencion' => trim($_POST['atencion'] ?? ''),
                'comentario' => trim($_POST['comentario'] ?? ''),
                'id_cliente' => $id_cliente_existente, // Puede ser NULL si es cliente nuevo/externo
                'cliente_razon_social' => trim($_POST['cliente_razon_social'] ?? ''),
                'cliente_ruc_dni' => trim($_POST['cliente_ruc_dni'] ?? ''),
                'cliente_direccion' => trim($_POST['cliente_direccion'] ?? ''),
                'cliente_email' => trim($_POST['cliente_email'] ?? ''),
                'estado' => 'Pendiente', // Estado inicial al crear
            ];

            // Detalles de los ítems de la cotización (viene como JSON del JS)
            $items_json = $_POST['quotation_items_json'] ?? '[]';
            $items_data = json_decode($items_json, true);

            // Calcular subtotal, impuestos y total
            $subtotal = 0;
            foreach ($items_data as $item) {
                $subtotal += ((float)$item['cantidad'] * (float)$item['precio_unitario']);
            }
            $impuestos = ($quotation_data['incluido_igv'] === 'Mas IGV') ? $subtotal * 0.18 : 0; // Ejemplo 18% IGV
            $total = $subtotal + $impuestos;

            $quotation_data['subtotal'] = $subtotal;
            $quotation_data['impuestos'] = $impuestos;
            $quotation_data['total'] = $total;

            // Validación
            if (empty($quotation_data['nro_cotizacion']) || empty($quotation_data['fecha_cotizacion']) || empty($quotation_data['moneda']) || empty($quotation_data['condicion']) || empty($quotation_data['cliente_razon_social']) || empty($items_data)) {
                $error_message = 'Los campos obligatorios del encabezado y al menos un ítem son necesarios.';
            } else {
                try {
                    $id_cotizacion = $this->quotationModel->create($quotation_data, $items_data);
                    if ($id_cotizacion) {
                        $_SESSION['success_message'] = 'Cotización creada exitosamente con ID: ' . $id_cotizacion;
                        header('Location: /hotel_completo/public/quotations');
                        exit();
                    } else {
                        $error_message = 'Error al crear la cotización.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Posible duplicado de nro_cotizacion
                        $error_message = 'Error: Ya existe una cotización con ese número de cotización.';
                    } else {
                        $error_message = 'Error de base de datos al crear cotización: ' . $e->getMessage();
                    }
                } catch (Exception $e) {
                    $error_message = 'Error: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'quotations/create.php';
        extract([
            'new_quotation_number' => $new_quotation_number,
            'users' => $users,
            'products' => $products,
            'departamentos' => $departamentos,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a quotation or processes its update.
     * @param int $id_cotizacion
     */
    public function edit($id_cotizacion) {
        $title = "Editar Cotización";
        $quotation = $this->quotationModel->getById($id_cotizacion);
        $users = $this->userModel->getAllUsers();
        $products = $this->productModel->getAllProducts();

        $departamentos = [
            'Amazonas', 'Ancash', 'Apurimac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco',
            'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto',
            'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali'
        ];

        $error_message = '';
        $success_message = '';

        if (!$quotation) {
            $_SESSION['error_message'] = 'Cotización no encontrada.';
            header('Location: /hotel_completo/public/quotations');
            exit();
        }

        // Si se envió el formulario (POST request)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente_tipo = $_POST['cliente_tipo'] ?? 'nuevo';
            $id_cliente_existente = ($cliente_tipo === 'existente') ? ($_POST['id_cliente_existente'] ?? null) : null;

            // Datos de la cotización
            $quotation_data = [
                'nro_cotizacion' => trim($_POST['nro_cotizacion'] ?? ''), // No se edita normalmente
                'fecha_cotizacion' => trim($_POST['fecha_cotizacion'] ?? ''),
                'oferta_valido_dias' => (int)($_POST['oferta_valido_dias'] ?? 0),
                'tiempo_entrega_dias' => (int)($_POST['tiempo_entrega_dias'] ?? 0),
                'garantia' => trim($_POST['garantia'] ?? ''),
                'incluido_igv' => trim($_POST['incluido_igv'] ?? 'Mas IGV'),
                'moneda' => trim($_POST['moneda'] ?? 'Soles'),
                'tipo_cambio' => (float)($_POST['tipo_cambio'] ?? 1.00),
                'id_vendedor' => $_POST['id_vendedor'] ?? $_SESSION['user_id'] ?? null,
                'condicion' => trim($_POST['condicion'] ?? 'Contado'),
                'atencion' => trim($_POST['atencion'] ?? ''),
                'comentario' => trim($_POST['comentario'] ?? ''),
                'id_cliente' => $id_cliente_existente,
                'cliente_razon_social' => trim($_POST['cliente_razon_social'] ?? ''),
                'cliente_ruc_dni' => trim($_POST['cliente_ruc_dni'] ?? ''),
                'cliente_direccion' => trim($_POST['cliente_direccion'] ?? ''),
                'cliente_email' => trim($_POST['cliente_email'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'Pendiente'), // Se puede cambiar el estado
            ];

            // Detalles de los ítems de la cotización (viene como JSON del JS)
            $items_json = $_POST['quotation_items_json'] ?? '[]';
            $items_data = json_decode($items_json, true);

            // Calcular subtotal, impuestos y total (recalcular al actualizar)
            $subtotal = 0;
            foreach ($items_data as $item) {
                $subtotal += ((float)$item['cantidad'] * (float)$item['precio_unitario']);
            }
            $impuestos = ($quotation_data['incluido_igv'] === 'Mas IGV') ? $subtotal * 0.18 : 0;
            $total = $subtotal + $impuestos;

            $quotation_data['subtotal'] = $subtotal;
            $quotation_data['impuestos'] = $impuestos;
            $quotation_data['total'] = $total;

            // Validación
            if (empty($quotation_data['fecha_cotizacion']) || empty($quotation_data['moneda']) || empty($quotation_data['condicion']) || empty($quotation_data['cliente_razon_social']) || empty($items_data)) {
                $error_message = 'Los campos obligatorios del encabezado y al menos un ítem son necesarios.';
            } else {
                try {
                    if ($this->quotationModel->update($id_cotizacion, $quotation_data, $items_data)) {
                        $_SESSION['success_message'] = 'Cotización actualizada exitosamente.';
                        $quotation = $this->quotationModel->getById($id_cotizacion); // Recargar datos
                    } else {
                        $error_message = 'Error al actualizar la cotización.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al actualizar cotización: ' . $e->getMessage();
                } catch (Exception $e) {
                    $error_message = 'Error: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'quotations/edit.php';
        extract([
            'quotation' => $quotation,
            'users' => $users,
            'products' => $products,
            'departamentos' => $departamentos,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a quotation.
     * @param int $id_cotizacion
     */
    public function delete($id_cotizacion) {
        try {
            if ($this->quotationModel->delete($id_cotizacion)) {
                $_SESSION['success_message'] = 'Cotización eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la cotización.';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error de base de datos al eliminar cotización: ' . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/quotations');
        exit();
    }

    // --- Métodos AJAX para la selección dinámica de ítems y clientes ---

    /**
     * AJAX endpoint to search inventory products.
     */
    public function searchProductsAjax() {
        if (isset($_GET['query'])) {
            $query = trim($_GET['query']);
            $products = $this->productModel->searchProducts($query); // Necesitas este método en ProductModel
            header('Content-Type: application/json');
            echo json_encode($products);
        }
        exit();
    }

    /**
     * AJAX endpoint to get product details by ID.
     */
    public function getProductDetailsAjax() {
        if (isset($_GET['id_producto'])) {
            $id_producto = (int)$_GET['id_producto'];
            $product = $this->productModel->getById($id_producto); // Reusa el getById existente
            if ($product) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'nombre_producto' => $product['nombre_producto'],
                    'precio_compra' => $product['precio_compra'],
                    'unidad_medida' => $product['unidad_medida'],
                    'stock_actual' => $product['stock_actual']
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
            }
        }
        exit();
    }

    /**
     * AJAX endpoint to search guests. (for client selection)
     */
    public function searchGuestsAjax() {
        if (isset($_GET['query'])) {
            $query = $_GET['query'];
            $guests = $this->guestModel->searchGuests($query); // Reusa el searchGuests existente
            header('Content-Type: application/json');
            echo json_encode($guests);
        }
        exit();
    }
}
