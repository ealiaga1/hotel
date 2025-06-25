<?php
// hotel_completo/app/controllers/RestaurantController.php

// Required models
require_once __DIR__ . '/../models/MenuCategory.php';
require_once __DIR__ . '/../models/MenuItem.php';
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../models/RestaurantOrder.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/CashTransaction.php';


class RestaurantController {
    private $menuCategoryModel;
    private $menuItemModel;
    private $tableModel;
    private $restaurantOrderModel;
    private $guestModel;
    private $cashRegisterModel;
    private $cashTransactionModel;
    private $pdo;


    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->menuCategoryModel = new MenuCategory($this->pdo);
        $this->menuItemModel = new MenuItem($this->pdo);
        $this->tableModel = new Table($this->pdo);
        $this->restaurantOrderModel = new RestaurantOrder($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->cashRegisterModel = new CashRegister($this->pdo);
        $this->cashTransactionModel = new CashTransaction($this->pdo);
    }

    /**
     * Displays the main restaurant dashboard or menu items list.
     */
    public function index() {
        $title = "Gestión de Platos del Menú";
        $menuItems = $this->menuItemModel->getAllMenuItems();
        $categories = $this->menuCategoryModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'restaurant/menu_items/index.php';
        extract([
            'menuItems' => $menuItems,
            'categories' => $categories,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function createMenuItem() {
        $title = "Crear Nuevo Plato del Menú";
        $categories = $this->menuCategoryModel->getAll();

        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_categoria_menu' => $_POST['id_categoria_menu'] ?? '',
                'nombre_plato' => trim($_POST['nombre_plato'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'precio' => $_POST['precio'] ?? '',
                'foto_url' => trim($_POST['foto_url'] ?? ''),
                'disponible' => isset($_POST['disponible']) ? 1 : 0
            ];

            if (empty($data['nombre_plato']) || empty($data['precio']) || empty($data['id_categoria_menu'])) {
                $error_message = 'Nombre del plato, Precio y Categoría son obligatorios.';
            } elseif (!is_numeric($data['precio']) || $data['precio'] <= 0) {
                $error_message = 'El precio debe ser un número positivo.';
            } else {
                try {
                    $id_plato = $this->menuItemModel->create($data);
                    if ($id_plato) {
                        $_SESSION['success_message'] = 'Plato del menú creado exitosamente.';
                        header('Location: /hotel_completo/public/restaurant');
                        exit();
                    } else {
                        $error_message = 'Error al crear el plato del menú.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al crear plato: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/menu_items/create.php';
        extract(['categories' => $categories, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function editMenuItem($id_plato) {
        $title = "Editar Plato del Menú";
        $menuItem = $this->menuItemModel->getById($id_plato);
        $categories = $this->menuCategoryModel->getAll();

        $error_message = '';
        $success_message = '';

        if (!$menuItem) {
            $_SESSION['error_message'] = 'Plato del menú no encontrado.';
            header('Location: /hotel_completo/public/restaurant');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_categoria_menu' => $_POST['id_categoria_menu'] ?? '',
                'nombre_plato' => trim($_POST['nombre_plato'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'precio' => $_POST['precio'] ?? '',
                'foto_url' => trim($_POST['foto_url'] ?? ''),
                'disponible' => isset($_POST['disponible']) ? 1 : 0
            ];

            if (empty($data['nombre_plato']) || empty($data['precio']) || empty($data['id_categoria_menu'])) {
                $error_message = 'Nombre del plato, Precio y Categoría son obligatorios.';
            } elseif (!is_numeric($data['precio']) || $data['precio'] <= 0) {
                $error_message = 'El precio debe ser un número positivo.';
            } else {
                try {
                    if ($this->menuItemModel->update($id_plato, $data)) {
                        $_SESSION['success_message'] = 'Plato del menú actualizado exitosamente.';
                        $menuItem = $this->menuItemModel->getById($id_plato); // Reload
                    } else {
                        $error_message = 'Error al actualizar el plato del menú.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al actualizar plato: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/menu_items/edit.php';
        extract(['menuItem' => $menuItem, 'categories' => $categories, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function deleteMenuItem($id_plato) {
        try {
            if ($this->menuItemModel->delete($id_plato)) {
                $_SESSION['success_message'] = 'Plato del menú eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el plato del menú.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar el plato porque tiene pedidos asociados.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el plato: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/restaurant');
        exit();
    }

    // --- Menu Categories Management (existing methods) ---
    public function categories() {
        $title = "Gestión de Categorías del Menú";
        $categories = $this->menuCategoryModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'restaurant/categories/index.php';
        extract(['categories' => $categories, 'success_message' => $success_message, 'error_message' => $error_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function createCategory() {
        $title = "Crear Nueva Categoría de Menú";
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
                    $id_categoria = $this->menuCategoryModel->create($data);
                    if ($id_categoria) {
                        $_SESSION['success_message'] = 'Categoría de menú creada exitosamente.';
                        header('Location: /hotel_completo/public/restaurant/categories');
                        exit();
                    } else {
                        $error_message = 'Error al crear la categoría. El nombre podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe una categoría con ese nombre.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar categoría: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/categories/create.php';
        extract(['error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function editCategory($id_categoria_menu) {
        $title = "Editar Categoría de Menú";
        $category = $this->menuCategoryModel->getById($id_categoria_menu);
        $error_message = '';
        $success_message = '';

        if (!$category) {
            $_SESSION['error_message'] = 'Categoría no encontrada.';
            header('Location: /hotel_completo/public/restaurant/categories');
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
                    if ($this->menuCategoryModel->update($id_categoria_menu, $data)) {
                        $_SESSION['success_message'] = 'Categoría actualizada exitosamente.';
                        $category = $this->menuCategoryModel->getById($id_categoria_menu); // Reload
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

        $content_view = VIEW_PATH . 'restaurant/categories/edit.php';
        extract(['category' => $category, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function deleteCategory($id_categoria_menu) {
        try {
            if ($this->menuCategoryModel->delete($id_categoria_menu)) {
                $_SESSION['success_message'] = 'Categoría eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la categoría.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la categoría porque tiene platos asociados.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar la categoría: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/restaurant/categories');
        exit();
    }

    // --- Tables Management (UPDATED) ---
    /**
     * Displays the restaurant tables dashboard.
     */
    public function tables() {
        $title = "Mesas del Restaurante";
        
        // Obtener todas las mesas
        $tables = $this->tableModel->getAll();

        // Para cada mesa, si está ocupada, obtener la orden activa
        foreach ($tables as &$table) {
            if ($table['estado'] === 'ocupada') {
                $activeOrder = $this->restaurantOrderModel->getActiveOrderByTableId($table['id_mesa']);
                $table['active_order'] = $activeOrder;
                // Si la orden tiene un huésped (del hotel)
                if ($activeOrder && !empty($activeOrder['huesped_nombre'])) {
                    $table['cliente_info'] = htmlspecialchars($activeOrder['huesped_nombre'] . ' ' . $activeOrder['huesped_apellido']);
                } elseif ($activeOrder && !empty($activeOrder['nombre_cliente'])) { // nombre_cliente es para cliente externo
                    $table['cliente_info'] = htmlspecialchars($activeOrder['nombre_cliente']);
                } else {
                    $table['cliente_info'] = 'N/A';
                }
            } else {
                $table['active_order'] = null;
                $table['cliente_info'] = 'N/A';
            }
        }
        unset($table);

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'restaurant/tables/index.php'; // Apunta a la vista del dashboard visual
        extract([
            'tables' => $tables,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new table or processes its creation.
     */
    public function createTable() {
        $title = "Crear Nueva Mesa";
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_mesa' => trim($_POST['numero_mesa'] ?? ''),
                'capacidad' => $_POST['capacidad'] ?? '',
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible')
            ];

            if (empty($data['numero_mesa']) || empty($data['capacidad'])) {
                $error_message = 'Número de mesa y Capacidad son obligatorios.';
            } elseif (!is_numeric($data['capacidad']) || $data['capacidad'] <= 0) {
                $error_message = 'La capacidad debe ser un número positivo.';
            } else {
                try {
                    $id_mesa = $this->tableModel->create($data);
                    if ($id_mesa) {
                        $_SESSION['success_message'] = 'Mesa creada exitosamente.';
                        header('Location: /hotel_completo/public/restaurant/tables');
                        exit();
                    } else {
                        $error_message = 'Error al crear la mesa. El número de mesa podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe una mesa con ese número.';
                    } else {
                        $error_message = 'Error de base de datos al crear mesa: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/tables/create.php';
        extract(['error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a table or processes its update.
     * @param int $id_mesa
     */
    public function editTable($id_mesa) {
        $title = "Editar Mesa";
        $table = $this->tableModel->getById($id_mesa);
        $error_message = '';
        $success_message = '';

        if (!$table) {
            $_SESSION['error_message'] = 'Mesa no encontrada.';
            header('Location: /hotel_completo/public/restaurant/tables');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_mesa' => trim($_POST['numero_mesa'] ?? ''),
                'capacidad' => $_POST['capacidad'] ?? '',
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible')
            ];

            if (empty($data['numero_mesa']) || empty($data['capacidad'])) {
                $error_message = 'Número de mesa y Capacidad son obligatorios.';
            } elseif (!is_numeric($data['capacidad']) || $data['capacidad'] <= 0) {
                $error_message = 'La capacidad debe ser un número positivo.';
            } else {
                try {
                    if ($this->tableModel->update($id_mesa, $data)) {
                        $_SESSION['success_message'] = 'Mesa actualizada exitosamente.';
                        $table = $this->tableModel->getById($id_mesa); // Reload
                    } else {
                        $error_message = 'Error al actualizar la mesa. El número de mesa podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe otra mesa con ese número.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar mesa: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/tables/edit.php';
        extract(['table' => $table, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function deleteTable($id_mesa) {
        try {
            if ($this->tableModel->delete($id_mesa)) {
                $_SESSION['success_message'] = 'Mesa eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la mesa.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la mesa porque tiene pedidos asociados.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar la mesa: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/restaurant/tables');
        exit();
    }

    /**
     * Updates the status of a table directly from an action.
     * @param int $id_mesa The ID of the table to update.
     * @param string $new_status The new status for the table (e.g., 'disponible', 'en_limpieza').
     */
    public function updateTableStatus($id_mesa, $new_status) {
        // Asegurarse de que solo se pueda llamar via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Acceso no permitido. La actualización de estado debe ser vía POST.';
            header('Location: /hotel_completo/public/restaurant/tables');
            exit();
        }

        try {
            $allowed_statuses = ['disponible', 'ocupada', 'reservada', 'en_limpieza'];
            if (!in_array($new_status, $allowed_statuses)) {
                $_SESSION['error_message'] = 'Estado de mesa no válido: ' . htmlspecialchars($new_status);
                header('Location: /hotel_completo/public/restaurant/tables');
                exit();
            }

            $table = $this->tableModel->getById($id_mesa);
            if (!$table) {
                $_SESSION['error_message'] = 'Mesa no encontrada.';
                header('Location: /hotel_completo/public/restaurant/tables');
                exit();
            }

            if ($this->tableModel->updateStatus($id_mesa, $new_status)) {
                $_SESSION['success_message'] = 'Estado de la mesa ' . htmlspecialchars($table['numero_mesa']) . ' actualizado a "' . ucfirst(str_replace('_', ' ', $new_status)) . '" exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al actualizar el estado de la mesa.';
            }
        } catch (PDOException | Exception $e) {
            $_SESSION['error_message'] = 'Error de base de datos al actualizar el estado de la mesa: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/restaurant/tables');
        exit();
    }

    // --- Orders Management (existing methods) ---

    /**
     * Displays the list of all restaurant orders.
     */
    public function orders() {
        $title = "Gestión de Pedidos del Restaurante";
        $orders = $this->restaurantOrderModel->getAllOrders();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'restaurant/orders/index.php';
        extract(['orders' => $orders, 'success_message' => $success_message, 'error_message' => $error_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new order or processes its creation.
     */
    public function createOrder() {
        $title = "Crear Nuevo Pedido";
        $tables = $this->tableModel->getAll();
        $menuItems = $this->menuItemModel->getAllMenuItems();
        $huespedes = $this->guestModel->getAll();

        $error_message = '';
        $success_message = '';

        // --- INICIO: Lógica para pre-seleccionar mesa y orden si vienen de la URL ---
        $preselected_table_id = $_GET['table_id'] ?? null;
        $preselected_order_id = $_GET['add_to_order'] ?? null;
        $preselected_order = null;
        $preloaded_items = [];
        $preselected_client_type = $_GET['client_type'] ?? 'mesa'; // Leer preselected_client_type de la URL

        if ($preselected_order_id) {
            $preselected_order = $this->restaurantOrderModel->getOrderById($preselected_order_id);
            if ($preselected_order) {
                $preloaded_items = $preselected_order['items'];
                $preselected_table_id = $preselected_order['id_mesa'];
                $preselected_client_type = $preselected_order['tipo_pedido'];
            } else {
                $error_message = 'El pedido al que intenta añadir no fue encontrado.';
                $preselected_order_id = null;
            }
        } else if ($preselected_table_id) {
            $preselected_client_type = 'mesa';
        }
        // --- FIN: Lógica para pre-seleccionar ---

        // Debugging variables passed to view
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: Extract variables for view:");
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: preselected_table_id: " . ($preselected_table_id ?? 'null'));
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: preselected_order_id: " . ($preselected_order_id ?? 'null'));
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: preloaded_items count: " . count($preloaded_items));
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: preselected_client_type: " . ($preselected_client_type ?? 'null'));
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: error_message: " . ($error_message ?? 'null'));
        error_log("DEBUG-RESTAURANT-CONTROLLER-CREATE: success_message: " . ($success_message ?? 'null'));


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("DEBUG-RESTAURANT-ORDER: POST data for create order: " . print_r($_POST, true));

            $tipo_pedido = trim($_POST['tipo_pedido'] ?? 'mesa');
            $id_mesa = ($tipo_pedido === 'mesa') ? ($_POST['id_mesa'] ?? null) : null;
            $id_huesped = ($tipo_pedido === 'habitacion') ? ($_POST['id_huesped'] ?? null) : null;
            $nombre_cliente_externo = ($tipo_pedido === 'externo') ? trim($_POST['nombre_cliente_externo'] ?? '') : null;
            $telefono_cliente_externo = ($tipo_pedido === 'externo') ? trim($_POST['telefono_cliente_externo'] ?? '') : null;
            
            $items_json = $_POST['order_items_json'] ?? '[]';
            $order_items = json_decode($items_json, true);

            $total_pedido = 0;
            foreach ($order_items as $item) {
                $total_pedido += (float)($item['cantidad'] ?? 0) * (float)($item['precio_unitario'] ?? 0);
            }

            // Basic validation
            if (empty($order_items)) {
                $error_message = 'El pedido debe contener al menos un plato.';
            } elseif ($tipo_pedido === 'mesa' && empty($id_mesa)) {
                $error_message = 'Debe seleccionar una mesa para pedidos de mesa.';
            } elseif ($tipo_pedido === 'habitacion' && empty($id_huesped)) {
                $error_message = 'Debe seleccionar un huésped para pedidos a la habitación.';
            } elseif ($tipo_pedido === 'externo' && empty($nombre_cliente_externo)) {
                $error_message = 'Debe ingresar el nombre del cliente para pedidos externos.';
            }
            else {
                $transaction_started_here = false;
                if (!$this->pdo->inTransaction()) {
                    $this->pdo->beginTransaction();
                    $transaction_started_here = true;
                }

                try {
                    $order_data = [
                        'id_mesa' => $id_mesa,
                        'id_huesped' => $id_huesped,
                        'nombre_cliente' => $nombre_cliente_externo,
                        'telefono_cliente' => $telefono_cliente_externo,
                        'id_usuario_toma_pedido' => $_SESSION['user_id'] ?? null,
                        'estado' => 'pendiente',
                        'total_pedido' => $total_pedido,
                        'tipo_pedido' => $tipo_pedido,
                        'comentarios' => trim($_POST['comentarios'] ?? null)
                    ];

                    $id_pedido = $this->restaurantOrderModel->createOrder($order_data, $order_items);
                    if (!$id_pedido) {
                        throw new Exception('Error al crear el pedido en el modelo.');
                    }

                    // --- Lógica de CARGO A HABITACIÓN vs PAGO DIRECTO ---
                    // IMPORTANTE: Los campos de pago (payment_type, payment_method) ahora se envían en el POST
                    $payment_type_form = trim($_POST['payment_type'] ?? 'immediate');
                    $payment_method_form = trim($_POST['payment_method'] ?? 'Efectivo');
                    $id_huesped_charge_form = ($payment_type_form === 'charge_to_room') ? ($_POST['id_huesped_charge'] ?? null) : null;

                    if ($payment_type_form === 'charge_to_room') {
                        $guest_charge_data = [
                            'id_huesped' => $id_huesped_charge_form,
                            'id_reserva' => null,
                            'id_pedido_restaurante' => $id_pedido,
                            'descripcion' => 'Consumo Restaurante (Pedido #' . $id_pedido . ')',
                            'monto' => $total_pedido,
                            'estado' => 'pendiente',
                            'id_usuario_registro' => $_SESSION['user_id'] ?? null
                        ];
                        if (!$this->guestModel->addGuestCharge($guest_charge_data)) {
                            throw new Exception('Error al registrar el cargo del pedido en la cuenta del huésped.');
                        }
                    } elseif ($payment_type_form === 'immediate') {
                        $openRegister = $this->cashRegisterModel->getOpenRegister();
                        if (!$openRegister) {
                             throw new Exception('No hay un turno de caja abierto para registrar el pago inmediato de este pedido de restaurante. Abra la caja.');
                        }
                        $transaction_description = 'Venta Restaurante (Pedido #' . $id_pedido . ')';
                        if ($tipo_pedido === 'mesa' && !empty($id_mesa)) {
                            $mesa_info = $this->tableModel->getById($id_mesa);
                            $transaction_description .= ' (Mesa ' . ($mesa_info['numero_mesa'] ?? $id_mesa) . ')';
                        } elseif ($tipo_pedido === 'habitacion' && !empty($id_huesped)) {
                            $huesped_info = $this->guestModel->getById($id_huesped);
                            $transaction_description .= ' (Huésped ' . ($huesped_info['nombre'] ?? '') . ' ' . ($huesped_info['apellido'] ?? '') . ')';
                        } elseif ($tipo_pedido === 'externo' && !empty($nombre_cliente_externo)) {
                            $transaction_description .= ' (Cliente Externo: ' . $nombre_cliente_externo . ')';
                        }

                        $cash_transaction_data = [
                            'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                            'descripcion' => $transaction_description,
                            'monto' => $total_pedido,
                            'tipo_transaccion' => 'ingreso',
                            'metodo_pago' => $payment_method_form,
                            'id_usuario' => $_SESSION['user_id'] ?? null
                        ];
                        if (!$this->cashTransactionModel->create($cash_transaction_data)) {
                            throw new Exception('Error al registrar la transacción de caja para el pedido de restaurante.');
                        }
                        $this->restaurantOrderModel->updateOrderStatus($id_pedido, 'pagado');
                    }
                    // --- FIN Lógica de CARGO A HABITACIÓN vs PAGO DIRECTO ---

                    if ($tipo_pedido === 'mesa' && !empty($id_mesa)) {
                         if (!$this->tableModel->updateStatus($id_mesa, 'ocupada')) {
                             throw new Exception('Error al actualizar el estado de la mesa a ocupada.');
                         }
                    }

                    if ($transaction_started_here) {
                        $this->pdo->commit();
                    }
                    $_SESSION['success_message'] = 'Pedido creado exitosamente con ID: ' . $id_pedido;
                    header('Location: /hotel_completo/public/restaurant/orders');
                    exit();

                } catch (Exception $e) {
                    if ($transaction_started_here && $this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    $error_message = 'Error al crear el pedido: ' . $e->getMessage();
                    error_log("DEBUG-RESTAURANT-ORDER-CREATE ERROR CATCH: " . $e->getMessage());
                    echo "Error al crear el pedido (DETALLE): " . htmlspecialchars($e->getMessage());
                    exit();
                }
            }
        }

        $content_view = VIEW_PATH . 'restaurant/orders/create.php';
        extract([
            'tables' => $tables,
            'menuItems' => $menuItems,
            'huespedes' => $huespedes,
            'error_message' => $error_message,
            'success_message' => $success_message,
            'preselected_table_id' => $preselected_table_id,
            'preselected_order_id' => $preselected_order_id,
            'preselected_order' => $preselected_order,
            'preloaded_items' => $preloaded_items,
            'preselected_client_type' => $preselected_client_type
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays details of an order (for viewing or editing items).
     * @param int $id_pedido
     */
    public function viewOrder($id_pedido) {
        $title = "Detalle del Pedido #" . $id_pedido;
        $order = $this->restaurantOrderModel->getOrderById($id_pedido);

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        if (!$order) {
            $_SESSION['error_message'] = 'Pedido no encontrado.';
            header('Location: /hotel_completo/public/restaurant/orders');
            exit();
        }

        $content_view = VIEW_PATH . 'restaurant/orders/view.php';
        extract(['order' => $order, 'success_message' => $success_message, 'error_message' => $error_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }


    /**
     * Updates order status (e.g., to "ready", "delivered", "paid").
     * This is typically an action from the orders list/view.
     * @param int $id_pedido
     * @param string $new_status
     */
    public function updateOrderStatus($id_pedido, $new_status) {
        $order = $this->restaurantOrderModel->getOrderById($id_pedido);

        if (!$order) {
            $_SESSION['error_message'] = 'Pedido no encontrado para actualizar estado.';
            header('Location: /hotel_completo/public/restaurant/orders');
            exit();
        }

        $id_mesa_to_update = null;
        $new_table_status = null;
            
        $transaction_started_here = false;

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transaction_started_here = true;
            }

            if ($new_status === 'pagado' && $order['estado'] !== 'pagado') {
                $openRegister = $this->cashRegisterModel->getOpenRegister();
                if (!$openRegister) {
                    throw new Exception('No hay un turno de caja abierto para registrar el pago de este pedido de restaurante. Abra la caja.');
                }

                if ($order['tipo_pedido'] === 'habitacion' && !empty($order['id_huesped'])) {
                    $pending_charges = $this->guestModel->getPendingChargesForGuest($order['id_huesped'], null, $id_pedido);
                    if (!empty($pending_charges)) {
                        $charge_id_to_mark_paid = $pending_charges[0]['id_cargo'];
                        if (!$this->guestModel->updateGuestChargesStatus([$charge_id_to_mark_paid], 'pagado')) {
                            throw new Exception('Error al marcar el cargo del huésped por pedido de restaurante como pagado.');
                        }
                    } else {
                        $guest_charge_data = [
                            'id_huesped' => $order['id_huesped'],
                            'id_reserva' => null,
                            'id_pedido_restaurante' => $id_pedido,
                            'descripcion' => 'Consumo Restaurante (Pedido #' . $id_pedido . ') - Pagado Directo',
                            'monto' => $order['total_pedido'],
                            'estado' => 'pagado',
                            'id_usuario_registro' => $_SESSION['user_id'] ?? null
                        ];
                        if (!$this->guestModel->addGuestCharge($guest_charge_data)) {
                             throw new Exception('Error al registrar nuevo cargo PAGADO para el pedido de restaurante.');
                        }
                    }
                }

                $transaction_description = 'Venta de Restaurante - Pedido #' . $id_pedido;
                if ($order['tipo_pedido'] === 'mesa' && !empty($order['numero_mesa'])) {
                    $transaction_description .= ' (Mesa ' . $order['numero_mesa'] . ')';
                } elseif ($order['tipo_pedido'] === 'habitacion' && !empty($order['huesped_nombre'])) {
                    $transaction_description .= ' (Huésped ' . $order['huesped_nombre'] . ' ' . $order['huesped_apellido'] . ')';
                } elseif ($order['tipo_pedido'] === 'externo' && !empty($order['nombre_cliente'])) {
                     $transaction_description .= ' (Cliente Externo: ' . $order['nombre_cliente'] . ')';
                }

                $cash_transaction_data = [
                    'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                    'descripcion' => $transaction_description,
                    'monto' => $order['total_pedido'],
                    'tipo_transaccion' => 'ingreso',
                    'metodo_pago' => 'Efectivo',
                    'id_usuario' => $_SESSION['user_id'] ?? null
                ];
                if (!$this->cashTransactionModel->create($cash_transaction_data)) {
                    throw new Exception('Error al registrar la transacción de caja para el pago del pedido.');
                }
            }

            if (!empty($order['id_mesa'])) {
                $id_mesa_to_update = $order['id_mesa'];
                if ($new_status === 'pagado' || $new_status === 'cancelado') {
                    $new_table_status = 'disponible';
                } else if ($new_status === 'en_preparacion' || $new_status === 'listo' || $new_status === 'entregado') {
                    $new_table_status = 'ocupada';
                }
            }
            
            if ($this->restaurantOrderModel->updateOrderStatus($id_pedido, $new_status)) {
                if ($id_mesa_to_update !== null && $new_table_status !== null) {
                     if (!$this->tableModel->updateStatus($id_mesa_to_update, $new_table_status)) {
                         throw new Exception('Error al actualizar el estado de la mesa asociada al pedido.');
                     }
                }
                
                if ($transaction_started_here) {
                    $this->pdo->commit();
                }
                $_SESSION['success_message'] = 'Estado del pedido #' . $id_pedido . ' actualizado a "' . ucfirst(str_replace('_', ' ', $new_status)) . '".';
            } else {
                if ($transaction_started_here) {
                    $this->pdo->rollBack();
                }
                $_SESSION['error_message'] = 'Error al actualizar el estado del pedido #' . $id_pedido . '.';
            }
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['error_message'] = 'Error al actualizar pedido: ' . htmlspecialchars($e->getMessage());
            echo "Error al actualizar pedido (DETALLE): " . htmlspecialchars($e->getMessage());
            exit();
        }
        header('Location: /hotel_completo/public/restaurant/orders');
        exit();
    }


    /**
     * Deletes an order.
     * @param int $id_pedido
     */
    public function deleteOrder($id_pedido) {
        try {
            if ($this->restaurantOrderModel->deleteOrder($id_pedido)) {
                $_SESSION['success_message'] = 'Pedido #' . $id_pedido . ' eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el pedido #' . $id_pedido . '.';
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error al eliminar pedido: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/restaurant/orders');
        exit();
    }


    /**
     * AJAX endpoint to search guests.
     */
    public function searchGuestsAjax() {
        if (isset($_GET['query'])) {
            $query = $_GET['query'];
            $guests = $this->guestModel->searchGuests($query);
            header('Content-Type: application/json');
            echo json_encode($guests);
        }
        exit();
    }
}