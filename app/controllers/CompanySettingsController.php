<?php
// hotel_completo/app/controllers/CompanySettingsController.php

require_once __DIR__ . '/../models/CompanySetting.php';

class CompanySettingsController {
    private $companySettingModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->companySettingModel = new CompanySetting($pdo);
    }

    /**
     * Displays the company settings form or processes its submission.
     */
    public function index() {
        $title = "Configuración de la Empresa";
        $settings = $this->companySettingModel->getSettings(); // Obtener la configuración actual

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        // Si se envió el formulario (POST request)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_empresa' => trim($_POST['nombre_empresa'] ?? ''),
                'ruc' => trim($_POST['ruc'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'logo_url' => trim($_POST['logo_url'] ?? null) // Por ahora, solo un URL o nulo
            ];

            // Validación básica
            if (empty($data['nombre_empresa'])) {
                $error_message = 'El nombre de la empresa es obligatorio.';
            } else {
                try {
                    if ($this->companySettingModel->updateSettings($data)) {
                        $_SESSION['success_message'] = 'Configuración de la empresa actualizada exitosamente.';
                        // Recargar la configuración para mostrar los cambios
                        $settings = $this->companySettingModel->getSettings();
                    } else {
                        $error_message = 'Error al actualizar la configuración de la empresa.';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Error de base de datos al guardar configuración: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'company_settings/index.php';
        // Pasar la configuración y los mensajes a la vista
        extract([
            'settings' => $settings,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }
}
