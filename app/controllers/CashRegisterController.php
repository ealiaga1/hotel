<?php
// hotel_completo/app/controllers/CashRegisterController.php

// Required models
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/CashTransaction.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/CompanySetting.php';

class CashRegisterController {
    private $pdo;
    private $cashRegisterModel;
    private $cashTransactionModel;
    private $userModel;
    private $productModel;
    private $guestModel;
    private $invoiceModel;
    private $companySettingModel;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->cashRegisterModel = new CashRegister($this->pdo);
        $this->cashTransactionModel = new CashTransaction($this->pdo);
        $this->userModel = new User($this->pdo);
        $this->productModel = new Product($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->invoiceModel = new Invoice($this->pdo);
        $this->companySettingModel = new CompanySetting($this->pdo);
    }

    /**
     * Displays the cash register status (open/closed) and options.
     */
    public function index() {
        $title = "Gestión de Caja";
        $openRegister = $this->cashRegisterModel->getOpenRegister();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // If a register is open, get its summary
        $registerSummary = [];
        if ($openRegister) {
            $registerSummary = $this->cashRegisterModel->getRegisterSummary($openRegister['id_movimiento_caja']);
        }

        $content_view = VIEW_PATH . 'cash_register/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to open a cash register shift or processes its opening.
     */
    public function open() {
        $title = "Abrir Caja";
        $error_message = '';
        $success_message = '';

        // Check if a register is already open
        if ($this->cashRegisterModel->getOpenRegister()) {
            $_SESSION['error_message'] = 'Ya existe un turno de caja abierto. Ciérrelo antes de abrir uno nuevo.';
            header('Location: /hotel_completo/public/cash_register');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $initial_balance = (float)($_POST['saldo_inicial'] ?? 0.00);
            $user_id = $_SESSION['user_id'] ?? null;

            if ($initial_balance < 0 || empty($user_id)) {
                $error_message = 'El saldo inicial debe ser un valor positivo y el usuario debe estar logueado.';
            } else {
                try {
                    $id_movimiento_caja = $this->cashRegisterModel->openRegister($initial_balance, $user_id);
                    if ($id_movimiento_caja) {
                        $_SESSION['success_message'] = 'Caja abierta exitosamente. ID de Turno: ' . $id_movimiento_caja;
                        header('Location: /hotel_completo/public/cash_register');
                        exit();
                    } else {
                        $error_message = 'Error al abrir la caja.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al abrir caja: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'cash_register/open.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the summary before closing a cash register shift or processes its closing.
     * @param int $register_id The ID of the cash register shift to close.
     */
    public function close($register_id) {
        $title = "Cerrar Caja";
        $openRegister = $this->cashRegisterModel->getOpenRegister();
        $error_message = '';
        $success_message = '';

        if (!$openRegister || $openRegister['id_movimiento_caja'] != $register_id) {
            $_SESSION['error_message'] = 'No se encontró un turno de caja abierto válido para cerrar.';
            header('Location: /hotel_completo/public/cash_register');
            exit();
        }

        $registerSummary = $this->cashRegisterModel->getRegisterSummary($openRegister['id_movimiento_caja']);
        $calculated_final_balance = $openRegister['saldo_inicial'] + $registerSummary['total_ingresos'] - $registerSummary['total_egresos'];


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $final_balance_input = (float)($_POST['saldo_final_input'] ?? 0.00); // Balance physically counted
            $user_id = $_SESSION['user_id'] ?? null;

            if ($final_balance_input < 0 || empty($user_id)) {
                $error_message = 'El saldo final debe ser un valor positivo y el usuario debe estar logueado.';
            } else {
                try {
                    if ($this->cashRegisterModel->closeRegister(
                        $register_id,
                        $final_balance_input, // The actual final balance from the user
                        $registerSummary['total_ingresos'],
                        $registerSummary['total_egresos'],
                        $user_id
                    )) {
                        $_SESSION['success_message'] = 'Caja cerrada exitosamente. ID de Turno: ' . $register_id;
                        $_SESSION['last_closed_register_id'] = $register_id; // Guardar ID para el enlace del reporte
                        header('Location: /hotel_completo/public/cash_register');
                        exit();
                    } else {
                        $error_message = 'Error al cerrar la caja. Asegúrese de que el turno aún esté abierto.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al cerrar caja: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'cash_register/close.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the history of closed cash register shifts.
     */
    public function history() {
        $title = "Historial de Cierres de Caja";
        $closedRegisters = $this->cashRegisterModel->getAllClosedRegisters();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'cash_register/history.php';
        extract([
            'closedRegisters' => $closedRegisters,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to add a manual cash transaction (income/expense) or processes it.
     */
    public function addTransaction() {
        $title = "Registrar Transacción Manual";
        $error_message = '';
        $success_message = '';

        $openRegister = $this->cashRegisterModel->getOpenRegister();

        if (!$openRegister) {
            $_SESSION['error_message'] = 'No hay un turno de caja abierto para registrar transacciones.';
            header('Location: /hotel_completo/public/cash_register');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'monto' => (float)($_POST['monto'] ?? 0.00),
                'tipo_transaccion' => trim($_POST['tipo_transaccion'] ?? ''),
                'metodo_pago' => trim($_POST['metodo_pago'] ?? 'Efectivo'),
                'id_usuario' => $_SESSION['user_id'] ?? null
            ];

            if (empty($data['descripcion']) || $data['monto'] <= 0 || empty($data['tipo_transaccion'])) {
                $error_message = 'Descripción, Monto y Tipo de Transacción son obligatorios y el monto debe ser positivo.';
            } else {
                try {
                    $id_transaction = $this->cashTransactionModel->create($data);
                    if ($id_transaction) {
                        $_SESSION['success_message'] = 'Transacción registrada exitosamente. ID: ' . $id_transaction;
                        header('Location: /hotel_completo/public/cash_register/transactions');
                        exit();
                    } else {
                        $error_message = 'Error al registrar la transacción.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al registrar transacción: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'cash_register/add_transaction.php';
        extract(['openRegister' => $openRegister, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the list of all transactions for the currently open cash register.
     * Could be extended to view transactions for closed registers via history.
     */
    public function viewOpenRegisterTransactions() {
        $title = "Transacciones del Turno de Caja Actual";
        $openRegister = $this->cashRegisterModel->getOpenRegister();
        $transactions = [];

        if (!$openRegister) {
            $_SESSION['error_message'] = 'No hay un turno de caja abierto para ver transacciones.';
            header('Location: /hotel_completo/public/cash_register');
            exit();
        }

        $transactions = $this->cashTransactionModel->getTransactionsByRegisterId($openRegister['id_movimiento_caja']);

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'cash_register/transactions.php';
        extract(['openRegister' => $openRegister, 'transactions' => $transactions, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to sell a product directly or processes the sale.
     */
    public function sellProduct() {
        $title = "Venta Directa de Productos";
        $products = $this->productModel->getAllProducts();
        $guests = $this->guestModel->getAll();

        $error_message = '';
        $success_message = '';

        $openRegister = $this->cashRegisterModel->getOpenRegister();

        if (!$openRegister) {
            $_SESSION['error_message'] = 'No hay un turno de caja abierto para registrar ventas. Abra la caja primero.';
            header('Location: /hotel_completo/public/cash_register');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("DEBUG-CASH-REGISTER: sellProduct POST data: " . print_r($_POST, true));

            $items_json = $_POST['sold_items_json'] ?? '[]';
            $sold_items = json_decode($items_json, true);
            $total_sale_amount = (float)($_POST['total_sale_amount'] ?? 0.00);
            $payment_type = trim($_POST['payment_type'] ?? 'immediate');
            $payment_method = trim($_POST['payment_method'] ?? '');
            $id_huesped_selected = ($payment_type === 'charge_to_room') ? ($_POST['id_huesped_selected'] ?? null) : null;
            $customer_description = trim($_POST['customer_description'] ?? 'Cliente Externo');

            if (empty($sold_items)) {
                $error_message = 'Debe añadir al menos un producto para vender.';
            } elseif ($total_sale_amount <= 0) {
                $error_message = 'El monto total de la venta debe ser mayor a cero.';
            } elseif ($payment_type === 'immediate' && empty($payment_method)) {
                $error_message = 'Debe seleccionar un método de pago para ventas inmediatas.';
            } elseif ($payment_type === 'charge_to_room' && empty($id_huesped_selected)) {
                $error_message = 'Debe seleccionar un huésped para cargar a la habitación.';
            } else {
                try {
                    $this->pdo->beginTransaction();

                    if ($payment_type === 'immediate') {
                        $transaction_description = 'Venta Directa - ' . $customer_description;
                        $cash_transaction_data = [
                            'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                            'descripcion' => $transaction_description,
                            'monto' => $total_sale_amount,
                            'tipo_transaccion' => 'ingreso',
                            'metodo_pago' => $payment_method,
                            'id_usuario' => $_SESSION['user_id'] ?? null
                        ];
                        $id_cash_transaction = $this->cashTransactionModel->create($cash_transaction_data);
                        if (!$id_cash_transaction) {
                            throw new Exception('Error al registrar la transacción de caja para la venta.');
                        }
                        error_log("DEBUG-CASH-REGISTER: Cash transaction created for immediate sale ID: " . $id_cash_transaction);

                    } elseif ($payment_type === 'charge_to_room') {
                        $charge_description = 'Venta Directa de productos (Cargada a Habitación)';
                        $guest_charge_data = [
                            'id_huesped' => $id_huesped_selected,
                            'id_reserva' => null,
                            'descripcion' => $charge_description,
                            'monto' => $total_sale_amount,
                            'estado' => 'pendiente',
                            'id_usuario_registro' => $_SESSION['user_id'] ?? null
                        ];
                        $id_guest_charge = $this->guestModel->addGuestCharge($guest_charge_data);
                        if (!$id_guest_charge) {
                            throw new Exception('Error al registrar el cargo a la habitación del huésped.');
                        }
                        error_log("DEBUG-CASH-REGISTER: Guest charge created for sale ID: " . $id_guest_charge);
                    }


                    foreach ($sold_items as $item) {
                        $product_info = $this->productModel->getById($item['id_producto']);
                        if (!$product_info || $product_info['stock_actual'] < $item['cantidad']) {
                            throw new Exception('Stock insuficiente para el producto: ' . ($product_info['nombre_producto'] ?? 'ID ' . $item['id_producto']) . '. Stock disponible: ' . ($product_info['stock_actual'] ?? '0') . ' ' . ($product_info['unidad_medida'] ?? ''));
                        }
                        if (!$this->productModel->updateStock($item['id_producto'], -$item['cantidad'])) {
                            throw new Exception('Error al descontar stock del producto: ' . ($product_info['nombre_producto'] ?? 'ID ' . $item['id_producto']));
                        }
                    }

                    $this->pdo->commit();
                    $_SESSION['success_message'] = 'Venta de productos registrada exitosamente. Total: S/ ' . number_format($total_sale_amount, 2) . ($payment_type === 'charge_to_room' ? ' (Cargado a Habitación)' : '');
                    header('Location: /hotel_completo/public/cash_register');
                    exit();

                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $_SESSION['error_message'] = 'Error al registrar la venta de productos: ' . $e->getMessage();
                    error_log("DEBUG-CASH-REGISTER: sellProduct failed: " . $e->getMessage());
                }
            }
        }

        $content_view = VIEW_PATH . 'cash_register/sell_product.php';
        extract([
            'openRegister' => $openRegister,
            'products' => $products,
            'guests' => $guests,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
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

    /**
     * Generates and displays the Point of Sale (POS) report for a specific cash register movement.
     * @param int $register_id The ID of the cash register movement (shift).
     */
    public function posReport($register_id) {
        $title = "Reporte de Punto de Venta (POS)";

        // Obtener el registro de caja específico
        $registerData = $this->cashRegisterModel->getRegisterById($register_id);
        
        if (!$registerData || $registerData['estado'] !== 'cerrada') {
            $_SESSION['error_message'] = 'Reporte POS solo disponible para cajas cerradas o turno no encontrado.';
            header('Location: /hotel_completo/public/cash_register/history');
            exit();
        }

        // Obtener resúmenes de transacciones
        $summary = $this->cashRegisterModel->getRegisterSummary($register_id);
        $methodTotals = $this->cashRegisterModel->getMethodTotalsForRegister($register_id);
        $documentTypeTotals = $this->cashRegisterModel->getDocumentTypeTotalsForRegister($register_id);

        $companySettings = $this->companySettingModel->getSettings(); // Obtener configuración de la empresa
        // Pasar la configuración de la empresa a las variables individuales para la vista
        $companyName = $companySettings['nombre_empresa'] ?? "Nombre de la Empresa";
        $companyRuc = $companySettings['ruc'] ?? "N/A";
        $companyAddress = $companySettings['direccion'] ?? "Dirección de la Empresa";
        $companyPhone = $companySettings['telefono'] ?? "N/A";
        $companyEmail = $companySettings['email'] ?? "N/A";
        $companyLogoUrl = $companySettings['logo_url'] ?? null;
        
        // Determinar el nombre del vendedor/usuario de cierre
        $sellerName = ($registerData['cierre_nombre'] ?? 'N/A') . ' ' . ($registerData['cierre_apellido'] ?? '');
        if (empty($sellerName) || $sellerName === ' ') {
            $sellerName = ($registerData['apertura_nombre'] ?? 'N/A') . ' ' . ($registerData['apertura_apellido'] ?? '');
        }
        if (empty($sellerName) || $sellerName === ' ') { $sellerName = "Sistema"; }

        // Inicializar estadoCaja para evitar Undefined variable
        $estadoCaja = $registerData['estado'] ?? 'Desconocido'; 

        // Calcular montos de operación detallados
        $saldoInicial = (float)$registerData['saldo_inicial'];
        $ingresoEfectivo = $methodTotals['Efectivo']['ingresos'] ?? 0.00;
        $egresoEfectivo = $methodTotals['Efectivo']['egresos'] ?? 0.00;
        $totalEfectivoCalculado = $saldoInicial + $ingresoEfectivo - $egresoEfectivo; 
        
        $billeterasDigitalIngreso = ($methodTotals['Tarjeta de Crédito']['ingresos'] ?? 0.00) + ($methodTotals['Yape/Plin']['ingresos'] ?? 0.00);
        $billeterasDigitalEgreso = ($methodTotals['Tarjeta de Crédito']['egresos'] ?? 0.00) + ($methodTotals['Yape/Plin']['egresos'] ?? 0.00);
        
        $transferenciasBancariasIngreso = ($methodTotals['Transferencia Bancaria']['ingresos'] ?? 0.00);
        $transferenciasBancariasEgreso = ($methodTotals['Transferencia Bancaria']['egresos'] ?? 0.00);

        $totalCajaCalculado = $saldoInicial + ($summary['total_ingresos'] ?? 0.00) - ($summary['total_egresos'] ?? 0.00);
        
        $totalCPE = $documentTypeTotals['Factura'] ?? 0.00;
        $totalNotaVenta = $documentTypeTotals['Boleta'] ?? 0.00;

        $porCobrar = $this->cashRegisterModel->getTotalPendingCharges();
        $totalPropinas = 0.00;


        $content_view = VIEW_PATH . 'cash_register/pos_report.php';
        extract([
            'companyName' => $companyName,
            'companyRuc' => $companyRuc,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone, // Pasar a la vista
            'companyEmail' => $companyEmail, // Pasar a la vista
            'companyLogoUrl' => $companyLogoUrl, // Pasar a la vista
            'sellerName' => $sellerName,
            'fechaReporte' => date('Y-m-d H:i:s'),
            'fechaApertura' => $registerData['fecha_apertura'],
            'fechaCierre' => $registerData['fecha_cierre'],
            'estadoCaja' => $estadoCaja, // Pasa la variable de estado
            'saldoInicial' => $saldoInicial,
            'ingresoEfectivo' => $ingresoEfectivo,
            'egresoEfectivo' => $egresoEfectivo,
            'totalEfectivoCalculado' => $totalEfectivoCalculado,
            'billeterasDigitalIngreso' => $billeterasDigitalIngreso,
            'billeterasDigitalEgreso' => $billeterasDigitalEgreso,
            'transferenciasBancariasIngreso' => $transferenciasBancariasIngreso,
            'transferenciasBancariasEgreso' => $transferenciasBancariasEgreso,
            'totalCajaCalculado' => $totalCajaCalculado,
            'totalCPE' => $totalCPE,
            'totalNotaVenta' => $totalNotaVenta,
            'porCobrar' => $porCobrar,
            'totalPropinas' => $totalPropinas
        ]);
        include $content_view;
        exit();
    }
}