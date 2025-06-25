<?php
// hotel_completo/app/controllers/SupplierController.php

require_once __DIR__ . '/../models/Supplier.php'; // Incluir el modelo de proveedor

class SupplierController {
    private $supplierModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->supplierModel = new Supplier($pdo);
    }

    /**
     * Displays the list of all suppliers.
     */
    public function index() {
        $title = "Gestión de Proveedores";
        $suppliers = $this->supplierModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'suppliers/index.php';
        // Pasar la lista de proveedores a la vista
        extract([
            'suppliers' => $suppliers,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new supplier or processes its creation.
     */
    public function create() {
        $title = "Registrar Nuevo Proveedor";
        $error_message = '';
        $success_message = '';

        // Listas de opciones para los desplegables (Perú)
        $departamentos = [
            'Amazonas', 'Ancash', 'Apurimac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco',
            'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto',
            'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali'
        ];
        // Las provincias y distritos se manejarán como campos de texto libre por la complejidad del ubigeo completo.

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tipo' => trim($_POST['tipo'] ?? ''),
                'razon_social' => trim($_POST['razon_social'] ?? ''),
                'ruc_dni' => trim($_POST['ruc_dni'] ?? ''),
                'departamento' => trim($_POST['departamento'] ?? ''),
                'provincia' => trim($_POST['provincia'] ?? ''),
                'distrito' => trim($_POST['distrito'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono_fijo' => trim($_POST['telefono_fijo'] ?? ''),
                'telefono_celular' => trim($_POST['telefono_celular'] ?? ''),
                'telefono_otro' => trim($_POST['telefono_otro'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'contacto' => trim($_POST['contacto'] ?? ''),
                'nro_cta_detraccion' => trim($_POST['nro_cta_detraccion'] ?? ''),
                'modo_pago' => trim($_POST['modo_pago'] ?? '')
            ];

            // Basic validation
            if (empty($data['tipo']) || empty($data['razon_social']) || empty($data['ruc_dni']) || empty($data['modo_pago'])) {
                $error_message = 'Los campos Tipo, Razón Social, RUC/DNI y Modo de Pago son obligatorios.';
            } else {
                try {
                    $id_proveedor = $this->supplierModel->create($data);
                    if ($id_proveedor) {
                        $_SESSION['success_message'] = 'Proveedor registrado exitosamente.';
                        header('Location: /hotel_completo/public/suppliers'); // Redirect to supplier list
                        exit();
                    } else {
                        $error_message = 'Error al registrar el proveedor.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe un proveedor con el mismo RUC/DNI.';
                    } else {
                        $error_message = 'Error de base de datos al registrar proveedor: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'suppliers/create.php';
        extract([
            'departamentos' => $departamentos,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a supplier or processes its update.
     * @param int $id_proveedor
     */
    public function edit($id_proveedor) {
        $title = "Editar Proveedor";
        $supplier = $this->supplierModel->getById($id_proveedor);
        $error_message = '';
        $success_message = '';

        $departamentos = [
            'Amazonas', 'Ancash', 'Apurimac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco',
            'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto',
            'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali'
        ];

        if (!$supplier) {
            $_SESSION['error_message'] = 'Proveedor no encontrado.';
            header('Location: /hotel_completo/public/suppliers');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tipo' => trim($_POST['tipo'] ?? ''),
                'razon_social' => trim($_POST['razon_social'] ?? ''),
                'ruc_dni' => trim($_POST['ruc_dni'] ?? ''),
                'departamento' => trim($_POST['departamento'] ?? ''),
                'provincia' => trim($_POST['provincia'] ?? ''),
                'distrito' => trim($_POST['distrito'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'telefono_fijo' => trim($_POST['telefono_fijo'] ?? ''),
                'telefono_celular' => trim($_POST['telefono_celular'] ?? ''),
                'telefono_otro' => trim($_POST['telefono_otro'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'contacto' => trim($_POST['contacto'] ?? ''),
                'nro_cta_detraccion' => trim($_POST['nro_cta_detraccion'] ?? ''),
                'modo_pago' => trim($_POST['modo_pago'] ?? '')
            ];

            // Basic validation
            if (empty($data['tipo']) || empty($data['razon_social']) || empty($data['ruc_dni']) || empty($data['modo_pago'])) {
                $error_message = 'Los campos Tipo, Razón Social, RUC/DNI y Modo de Pago son obligatorios.';
            } else {
                try {
                    if ($this->supplierModel->update($id_proveedor, $data)) {
                        $_SESSION['success_message'] = 'Proveedor actualizado exitosamente.';
                        $supplier = $this->supplierModel->getById($id_proveedor); // Reload data
                    } else {
                        $error_message = 'Error al actualizar el proveedor.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe otro proveedor con el mismo RUC/DNI.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar proveedor: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'suppliers/edit.php';
        extract([
            'supplier' => $supplier,
            'departamentos' => $departamentos,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a supplier.
     * @param int $id_proveedor
     */
    public function delete($id_proveedor) {
        try {
            if ($this->supplierModel->delete($id_proveedor)) {
                $_SESSION['success_message'] = 'Proveedor eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el proveedor.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar el proveedor porque tiene registros asociados (ej. compras).';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el proveedor: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/suppliers');
        exit();
    }
}
