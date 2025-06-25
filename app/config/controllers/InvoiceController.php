<?php
// hotel_completo/app/controllers/InvoiceController.php

// Required models
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';

class InvoiceController {
    private $invoiceModel;
    private $paymentModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->invoiceModel = new Invoice($pdo);
        $this->paymentModel = new Payment($pdo);
    }

    /**
     * Displays the list of all invoices.
     */
    public function index() {
        $title = "Gestión de Facturación";
        $invoices = $this->invoiceModel->getAllInvoices();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'invoicing/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the details of a specific invoice.
     * @param int $id_factura
     */
    public function view($id_factura) {
        $title = "Detalle de Factura #" . $id_factura;
        $invoice = $this->invoiceModel->getInvoiceById($id_factura);
        
        if (!$invoice) {
            $_SESSION['error_message'] = 'Factura no encontrada.';
            header('Location: /hotel_completo/public/invoicing');
            exit();
        }

        $content_view = VIEW_PATH . 'invoicing/view.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Voids (anulaciones) an invoice.
     * @param int $id_factura
     */
    public function void($id_factura) {
        $invoice = $this->invoiceModel->getInvoiceById($id_factura);

        if (!$invoice) {
            $_SESSION['error_message'] = 'Factura no encontrada para anular.';
            header('Location: /hotel_completo/public/invoicing');
            exit();
        }

        if ($invoice['estado'] === 'anulada') {
            $_SESSION['error_message'] = 'La factura ya está anulada.';
            header('Location: /hotel_completo/public/invoicing');
            exit();
        }

        try {
            if ($this->invoiceModel->updateStatus($id_factura, 'anulada')) {
                $_SESSION['success_message'] = 'Factura #' . $id_factura . ' anulada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al anular la factura.';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error de base de datos al anular factura: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/invoicing');
        exit();
    }

    /**
     * Displays a print-friendly view of the invoice in A4 format.
     * @param int $id_factura
     */
    public function printA4($id_factura) {
        $invoice = $this->invoiceModel->getInvoiceById($id_factura);

        if (!$invoice) {
            // Handle error: invoice not found
            echo "Factura no encontrada.";
            exit();
        }

        // Load a specific view for printing (without main layout)
        include VIEW_PATH . 'invoicing/print_a4.php';
        exit(); // Stop further execution
    }

    /**
     * Displays a print-friendly view of the invoice in Ticket format.
     * @param int $id_factura
     */
    public function printTicket($id_factura) {
        $invoice = $this->invoiceModel->getInvoiceById($id_factura);

        if (!$invoice) {
            // Handle error: invoice not found
            echo "Factura no encontrada.";
            exit();
        }

        // Load a specific view for printing (without main layout)
        include VIEW_PATH . 'invoicing/print_ticket.php';
        exit(); // Stop further execution
    }
}