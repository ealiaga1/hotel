<?php
// hotel_completo/public/index.php (Front Controller)

// --- Configuracion de Depuracion (Mantener activada durante el desarrollo!) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- Fin Configuracion de Depuracion ---

session_start(); // Inicia la sesion PHP para todo el sistema

// Establecer la zona horaria por defecto a America/Lima (Perú)
date_default_timezone_set('America/Lima');

// Incluye las configuraciones y librerias base
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/config/paths.php';
require_once __DIR__ . '/../app/lib/Database.php';

// Incluye todos los modelos que puedan ser necesarios globalmente o por los controladores
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Room.php';
require_once __DIR__ . '/../app/models/RoomType.php';
require_once __DIR__ . '/../app/models/Booking.php';
require_once __DIR__ . '/../app/models/Guest.php';
require_once __DIR__ . '/../app/models/Payment.php';
require_once __DIR__ . '/../app/models/Invoice.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/MenuCategory.php';
require_once __DIR__ . '/../app/models/MenuItem.php';
require_once __DIR__ . '/../app/models/Table.php';
require_once __DIR__ . '/../app/models/RestaurantOrder.php';
require_once __DIR__ . '/../app/models/PoolReservation.php';
require_once __DIR__ . '/../app/models/InventoryCategory.php';
require_once __DIR__ . '/../app/models/InventoryMovementType.php';
require_once __DIR__ . '/../app/models/InventoryMovement.php';
require_once __DIR__ . '/../app/models/CashRegister.php';
require_once __DIR__ . '/../app/models/CashTransaction.php';
require_once __DIR__ . '/../app/models/CompanySetting.php';
require_once __DIR__ . '/../app/models/Supplier.php'; // Incluir modelo de Proveedor
require_once __DIR__ . '/../app/models/Quotation.php'; // Incluir modelo de Cotización


// Incluye todos los controladores que puedan ser necesarios
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/RoomController.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';
require_once __DIR__ . '/../app/controllers/GuestController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/RestaurantController.php';
require_once __DIR__ . '/../app/controllers/PoolController.php';
require_once __DIR__ . '/../app/controllers/InventoryController.php';
require_once __DIR__ . '/../app/controllers/CashRegisterController.php';
require_once __DIR__ . '/../app/controllers/InvoiceController.php';
require_once __DIR__ . '/../app/controllers/CompanySettingsController.php';
require_once __DIR__ . '/../app/controllers/ReceptionController.php';
require_once __DIR__ . '/../app/controllers/SupplierController.php'; // Incluir controlador de Proveedor
require_once __DIR__ . '/../app/controllers/QuotationController.php'; // Incluir controlador de Cotización


// Obtener el ID del rol de Super Admin para control de visibilidad del menu
$pdo = Database::getInstance()->getConnection();
try {
    $stmt_super_admin_role = $pdo->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Super Admin'");
    $stmt_super_admin_role->execute();
    $super_admin_role_id = $stmt_super_admin_role->fetchColumn();
} catch (PDOException $e) {
    error_log("Error al obtener el ID del rol 'Super Admin' en index.php: " . $e->getMessage());
    $super_admin_role_id = null;
}


// Obten la URL solicitada y limpia el prefijo de tu aplicacion
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/hotel_completo/public/'; // Asegurate de que esta sea la ruta correcta para tu servidor
$route = ltrim(str_replace($base_path, '', $request_uri), '/');

// --- DEPURACION DE RUTA ---
error_log("DEBUG-ROUTE (index.php): Request URI original: " . $_SERVER['REQUEST_URI']);
error_log("DEBUG-ROUTE (index.php): Ruta parseada (PHP_URL_PATH): " . $request_uri);
error_log("DEBUG-ROUTE (index.php): Base Path configurada (\$base_path): " . $base_path);
error_log("DEBUG-ROUTE (index.php): Ruta final limpia para el switch (\$route): " . $route);
// --- FIN DEPURACION DE RUTA ---


// --- Logica de Enrutamiento y Autenticacion ---

if ($route === 'login.php' || $route === 'login') {
    if (isset($_SESSION['user_id'])) {
        header('Location: ' . $base_path . 'dashboard.php');
        exit();
    }
    require_once __DIR__ . '/login.php';
    exit();
}

if ($route === 'logout.php' || $route === 'logout') {
    $authController = new AuthController();
    $authController->logout();
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_path . 'login.php');
    exit();
}

// --- Rutas protegidas (solo para usuarios autenticados) ---

// Instanciar controladores ANTES del switch, para que estén disponibles
// en todos los casos que los usen.
$authController = new AuthController();
$dashboardController = new DashboardController();
$roomController = new RoomController();
$bookingController = new BookingController();
$guestController = new GuestController();
$userController = new UserController();
$restaurantController = new RestaurantController();
$poolController = new PoolController();
$inventoryController = new InventoryController();
$cashRegisterController = new CashRegisterController();
$invoiceController = new InvoiceController();
$companySettingsController = new CompanySettingsController();
$receptionController = new ReceptionController();
$supplierController = new SupplierController(); // Instanciar controlador de Proveedor
$quotationController = new QuotationController(); // Instanciar controlador de Cotización


switch ($route) {
    case 'dashboard.php':
    case 'dashboard':
    case '':
        $dashboardController->index();
        break;

    // --- Rutas del Módulo de Recepción ---
    case 'reception.php':
    case 'reception':
        $receptionController->index();
        break;

    // --- Rutas del Módulo de Calendario ---
    case 'calendar.php':
    case 'calendar':
        $calendarController->index();
        break;
    case (preg_match('/^calendar\/(\d{4})\/(\d{1,2})$/', $route, $matches) ? true : false):
        $calendarController->index($matches[1], $matches[2]);
        break;

    // --- Rutas para el Modulo de Habitaciones ---
    case 'rooms.php':
    case 'rooms':
        $roomController->index();
        break;
    case 'rooms/create':
        $roomController->create();
        break;
    // IMPORTANTE: Colocar rutas más específicas ANTES de las más generales.
    case (preg_match('/^rooms\/edit_type\/(\d+)$/', $route, $matches) ? true : false): // Ruta para editar TIPO de habitación
        $roomController->editType($matches[1]);
        break;
    case (preg_match('/^rooms\/delete_type\/(\d+)$/', $route, $matches) ? true : false): // Ruta para eliminar TIPO de habitación
        $roomController->deleteType($matches[1]);
        break;
    case 'rooms/create_type': // Ruta para crear TIPO de habitación
        $roomController->createType();
        break;
    case 'rooms/types': // Ruta para listar TIPOS de habitación
        $roomController->types();
        break;
    case (preg_match('/^rooms\/edit\/(\d+)$/', $route, $matches) ? true : false): // Ruta para editar HABITACIÓN
        $roomController->edit($matches[1]);
        break;
    case (preg_match('/^rooms\/delete\/(\d+)$/', $route, $matches) ? true : false): // Ruta para eliminar HABITACIÓN
        $roomController->delete($matches[1]);
        break;
    case (preg_match('/^rooms\/update_status\/(\d+)\/([a-z_]+)$/', $route, $matches) ? true : false): // Ruta para actualizar estado desde Recepción
        $roomController->updateStatus($matches[1], $matches[2]);
        break;


    // --- Rutas para el Modulo de Reservas ---
    case (preg_match('/^bookings\/checkin\/(\d+)$/', $route, $matches) ? true : false):
        error_log("DEBUG-ROUTE: Coincidencia con bookings/checkin/ - ID: " . $matches[1]);
        $bookingController->checkIn($matches[1]);
        break;
    case (preg_match('/^bookings\/checkout\/(\d+)$/', $route, $matches) ? true : false):
        error_log("DEBUG-ROUTE: Coincidencia con bookings/checkout/ - ID: " . $matches[1]);
        $bookingController->checkOut($matches[1]);
        break;
    case (preg_match('/^bookings\/finalize_checkout\/(\d+)$/', $route, $matches) ? true : false):
        error_log("DEBUG-ROUTE: Coincidencia con bookings/finalize_checkout/ - ID: " . $matches[1]);
        $bookingController->finalizeCheckout($matches[1]);
        break;
    case (preg_match('/^bookings\/cancel\/(\d+)$/', $route, $matches) ? true : false):
        error_log("DEBUG-ROUTE: Coincidencia con bookings/cancel/ - ID: " . $matches[1]);
        $bookingController->cancel($matches[1]);
        break;
    case (preg_match('/^bookings\/delete\/(\d+)$/', $route, $matches) ? true : false):
        error_log("DEBUG-ROUTE: Coincidencia con bookings/delete/ - ID: " . $matches[1]);
        $bookingController->delete($matches[1]);
        break;
    case (preg_match('/^bookings\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $bookingController->edit($matches[1]);
        break;
    case 'bookings.php':
    case 'bookings':
        $bookingController->index();
        break;
    case 'bookings/create':
        $bookingController->create();
        break;

    // --- Rutas AJAX para Reservas ---
    case 'bookings/search_guests_ajax':
        $bookingController->searchGuestsAjax();
        break;
    case 'bookings/search_available_rooms_ajax':
        $bookingController->searchAvailableRoomsAjax();
        break;
    case 'bookings/get_room_type_price_ajax':
        $bookingController->getRoomTypePriceAjax();
        break;

    // --- Rutas para el Modulo de Huespedes ---
    case 'guests.php':
    case 'guests':
        $guestController->index();
        break;
    case 'guests/create':
        $guestController->create();
        break;
    case (preg_match('/^guests\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $guestController->edit($matches[1]);
        break;
    case (preg_match('/^guests\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $guestController->delete($matches[1]);
        break;

    // --- Rutas para el Modulo de Personal ---
    case 'users.php':
    case 'users':
        $userController->index();
        break;
    case 'users/create':
        $userController->create();
        break;
    case (preg_match('/^users\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $userController->edit($matches[1]);
        break;
    case (preg_match('/^users\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $userController->delete($matches[1]);
        break;

    // --- Rutas para el Modulo de Restaurante (MENU & MESAS & PEDIDOS) ---
    // Menu Items
    case 'restaurant.php':
    case 'restaurant':
        $restaurantController->index();
        break;
    case 'restaurant/menu_items/create':
        $restaurantController->createMenuItem();
        break;
    case (preg_match('/^restaurant\/menu_items\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->editMenuItem($matches[1]);
        break;
    case (preg_match('/^restaurant\/menu_items\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->deleteMenuItem($matches[1]);
        break;
    // Menu Categories
    case 'restaurant/categories':
        $restaurantController->categories();
        break;
    case 'restaurant/categories/create':
        $restaurantController->createCategory();
        break;
    case (preg_match('/^restaurant\/categories\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->editCategory($matches[1]);
        break;
    case (preg_match('/^restaurant\/categories\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->deleteCategory($matches[1]);
        break;
    // Tables
    case 'restaurant/tables':
        $restaurantController->tables();
        break;
    case 'restaurant/tables/create':
        $restaurantController->createTable();
        break;
    case (preg_match('/^restaurant\/tables\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->editTable($matches[1]);
        break;
    case (preg_match('/^restaurant\/tables\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->deleteTable($matches[1]);
        break;
    // Orders
    case 'restaurant/orders':
        $restaurantController->orders();
        break;
    case 'restaurant/orders/create':
        $restaurantController->createOrder();
        break;
    case (preg_match('/^restaurant\/orders\/view\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->viewOrder($matches[1]);
        break;
    case (preg_match('/^restaurant\/orders\/update_status\/(\d+)\/([a-z_]+)$/', $route, $matches) ? true : false):
        $restaurantController->updateOrderStatus($matches[1], $matches[2]);
        break;
    case (preg_match('/^restaurant\/orders\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $restaurantController->deleteOrder($matches[1]);
        break;

    // --- Rutas AJAX específicas del Restaurante ---
    case 'restaurant/search_guests_ajax':
        $restaurantController->searchGuestsAjax();
        break;

    // --- Rutas para el Modulo de Piscina ---
    case 'pool.php':
    case 'pool':
        $poolController->index();
        break;
    case 'pool/create':
        $poolController->create();
        break;
    case (preg_match('/^pool\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $poolController->edit($matches[1]);
        break;
    case (preg_match('/^pool\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $poolController->delete($matches[1]);
        break;
    case 'pool/search_guests_ajax':
        $poolController->searchGuestsAjax();
        break;

    // --- Rutas para el Modulo de Inventario ---
    case 'inventory.php':
    case 'inventory':
        $inventoryController->index();
        break;
    case 'inventory/products/create':
        $inventoryController->createProduct();
        break;
    case (preg_match('/^inventory\/products\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $inventoryController->editProduct($matches[1]);
        break;
    case (preg_match('/^inventory\/products\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $inventoryController->deleteProduct($matches[1]);
        break;
    case 'inventory/categories':
        $inventoryController->categories();
        break;
    case 'inventory/categories/create':
        $inventoryController->createCategory();
        break;
    case (preg_match('/^inventory\/categories\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $inventoryController->editCategory($matches[1]);
        break;
    case (preg_match('/^inventory\/categories\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $inventoryController->deleteCategory($matches[1]);
        break;
    case 'inventory/movements':
        $inventoryController->movements();
        break;
    case 'inventory/movements/create':
        $inventoryController->createMovement();
        break;

    // --- Rutas para el Modulo de Caja ---
    case 'cash_register.php':
    case 'cash_register':
        $cashRegisterController->index();
        break;
    case 'cash_register/open':
        $cashRegisterController->open();
        break;
    case (preg_match('/^cash_register\/close\/(\d+)$/', $route, $matches) ? true : false):
        $cashRegisterController->close($matches[1]);
        break;
    case 'cash_register/history':
        $cashRegisterController->history();
        break;
    case 'cash_register/add_transaction':
        $cashRegisterController->addTransaction();
        break;
    case 'cash_register/transactions':
        $cashRegisterController->viewOpenRegisterTransactions();
        break;
    case 'cash_register/sell_product':
        $cashRegisterController->sellProduct();
        break;
    case 'cash_register/search_guests_ajax':
        $cashRegisterController->searchGuestsAjax();
        break;
    case (preg_match('/^cash_register\/pos_report\/(\d+)$/', $route, $matches) ? true : false):
        $cashRegisterController->posReport($matches[1]);
        break;

    // --- Rutas para el Modulo de Facturación ---
    case 'invoicing.php':
    case 'invoicing':
        $invoiceController->index();
        break;
    case (preg_match('/^invoicing\/view\/(\d+)$/', $route, $matches) ? true : false):
        $invoiceController->view($matches[1]);
        break;
    case (preg_match('/^invoicing\/void\/(\d+)$/', $route, $matches) ? true : false):
        $invoiceController->void($matches[1]);
        break;
    case (preg_match('/^invoicing\/print_a4\/(\d+)$/', $route, $matches) ? true : false):
        $invoiceController->printA4($matches[1]);
        break;
    case (preg_match('/^invoicing\/print_ticket\/(\d+)$/', $route, $matches) ? true : false):
        $invoiceController->printTicket($matches[1]);
        break;

    // --- Ruta para Configuración de Empresa ---
    case 'company_settings.php':
    case 'company_settings':
        $companySettingsController->index();
        break;

    // --- Rutas para el Modulo de Proveedores ---
    case 'suppliers.php':
    case 'suppliers':
        $supplierController->index();
        break;
    case 'suppliers/create':
        $supplierController->create();
        break;
    case (preg_match('/^suppliers\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $supplierController->edit($matches[1]);
        break;
    case (preg_match('/^suppliers\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $supplierController->delete($matches[1]);
        break;

    // --- Rutas para el Modulo de Cotizaciones ---
    case 'quotations.php':
    case 'quotations':
        $quotationController->index();
        break;
    case 'quotations/create':
        $quotationController->create();
        break;
    case (preg_match('/^quotations\/edit\/(\d+)$/', $route, $matches) ? true : false):
        $quotationController->edit($matches[1]);
        break;
    case (preg_match('/^quotations\/delete\/(\d+)$/', $route, $matches) ? true : false):
        $quotationController->delete($matches[1]);
        break;
    // Rutas AJAX para cotizaciones (usarán métodos en QuotationController)
    case 'quotations/search_products_ajax':
        $quotationController->searchProductsAjax();
        break;
    case 'quotations/get_product_details_ajax':
        $quotationController->getProductDetailsAjax();
        break;
    case 'quotations/search_guests_ajax':
        $quotationController->searchGuestsAjax();
        break;

    default:
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>La pagina solicitada no existe.</p><p><a href='" . $base_path . "dashboard.php'>Volver al Dashboard</a></p>";
        break;
}
