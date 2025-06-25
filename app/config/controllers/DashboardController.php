<?php
// hotel_completo/app/controllers/DashboardController.php

// Required models
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/RestaurantOrder.php';
require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../models/PoolReservation.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/CompanySetting.php'; // ¡NUEVO!

class DashboardController {
    private $bookingModel;
    private $roomModel;
    private $guestModel;
    private $userModel;
    private $restaurantOrderModel;
    private $tableModel;
    private $poolReservationModel;
    private $productModel;
    private $cashRegisterModel;
    private $companySettingModel; // ¡NUEVO!
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->bookingModel = new Booking($this->pdo);
        $this->roomModel = new Room($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->userModel = new User($this->pdo);
        $this->restaurantOrderModel = new RestaurantOrder($this->pdo);
        $this->tableModel = new Table($this->pdo);
        $this->poolReservationModel = new PoolReservation($this->pdo);
        $this->productModel = new Product($this->pdo);
        $this->cashRegisterModel = new CashRegister($this->pdo);
        $this->companySettingModel = new CompanySetting($this->pdo); // ¡NUEVO!
    }

    public function index() {
        $title = "Dashboard del Hotel";

        // --- Datos existentes para el Dashboard ---
        $pendingBookingsCount = $this->bookingModel->getPendingBookingsCount();
        $latestBookings = $this->bookingModel->getLatestBookings(5);
        $today = date('Y-m-d');


        // --- Nuevos datos de resumen por módulo ---
        $roomsTotal = $this->roomModel->getTotalRoomsCount();
        $roomsAvailable = $this->roomModel->getAvailableRoomsCount();
        $roomsOccupied = $this->roomModel->getOccupiedRoomsCount();
        $roomsMaintenance = $this->roomModel->getRoomsInMaintenanceCount();

        $totalGuests = $this->guestModel->getTotalGuestsCount();
        $currentGuestsInHouse = $this->bookingModel->getCurrentGuestsInHouseCount();

        $totalStaff = $this->userModel->getTotalUsersCount();

        $pendingRestaurantOrders = $this->restaurantOrderModel->getPendingOrdersCount();
        $occupiedTables = $this->tableModel->getOccupiedTablesCount();

        $todayPoolReservations = $this->poolReservationModel->getTodayReservationsCount($today);

        $totalInventoryProducts = $this->productModel->getTotalProductsCount();
        $lowStockProducts = $this->productModel->getProductsWithLowStockCount();

        $openRegister = $this->cashRegisterModel->getOpenRegister();
        $cashBalance = 0;
        $cashRegisterStatus = 'Cerrada';
        if ($openRegister) {
            $cashRegisterStatus = 'Abierta (ID: ' . $openRegister['id_movimiento_caja'] . ')';
            $summary = $this->cashRegisterModel->getRegisterSummary($openRegister['id_movimiento_caja']);
            $cashBalance = $openRegister['saldo_inicial'] + $summary['total_ingresos'] - $summary['total_egresos'];
        }

        $companySettings = $this->companySettingModel->getSettings(); // ¡NUEVO! Obtener configuración de la empresa

        // --- Pasar todos los datos a la vista ---
        $content_view = VIEW_PATH . 'dashboard/index.php';
        extract([
            'pendingBookingsCount' => $pendingBookingsCount,
            'latestBookings' => $latestBookings,
            'roomsTotal' => $roomsTotal,
            'roomsAvailable' => $roomsAvailable,
            'roomsOccupied' => $roomsOccupied,
            'roomsMaintenance' => $roomsMaintenance,
            'totalGuests' => $totalGuests,
            'currentGuestsInHouse' => $currentGuestsInHouse,
            'totalStaff' => $totalStaff,
            'pendingRestaurantOrders' => $pendingRestaurantOrders,
            'occupiedTables' => $occupiedTables,
            'todayPoolReservations' => $todayPoolReservations,
            'totalInventoryProducts' => $totalInventoryProducts,
            'lowStockProducts' => $lowStockProducts,
            'cashRegisterStatus' => $cashRegisterStatus,
            'cashBalance' => $cashBalance,
            'companySettings' => $companySettings // ¡NUEVO!
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }
}
