<?php
// hotel_completo/app/controllers/GuestController.php

require_once __DIR__ . '/../models/Guest.php';

class GuestController {
    private $guestModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->guestModel = new Guest($pdo);
    }

    /**
     * Displays the list of all guests, with search functionality.
     */
    public function index() {
        $title = "Gestión de Huéspedes";
        
        $search_query = trim($_GET['search_query'] ?? ''); // Obtener la consulta de búsqueda

        if (!empty($search_query)) {
            $guests = $this->guestModel->searchGuests($search_query); // Usar el método de búsqueda
        } else {
            $guests = $this->guestModel->getAll(); // Si no hay búsqueda, obtener todos
        }

        // Session messages (success/error)
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'guests/index.php';
        // Pasar la lista de huéspedes y la consulta de búsqueda actual a la vista
        extract([
            'guests' => $guests,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'current_search_query' => $search_query // Para mantener el valor en el input del buscador
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new guest or processes its creation.
     */
    public function create() {
        $title = "Registrar Nuevo Huésped";
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'ciudad' => trim($_POST['ciudad'] ?? ''),
                'pais' => trim($_POST['pais'] ?? ''),
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? '')
            ];

            // Basic validation
            if (empty($data['nombre']) || empty($data['apellido'])) {
                $error_message = 'Nombre y Apellido son obligatorios.';
            } elseif (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Formato de correo electrónico inválido.';
            } else {
                try {
                    $id_huesped = $this->guestModel->create($data);
                    if ($id_huesped) {
                        $_SESSION['success_message'] = 'Huésped registrado exitosamente con ID: ' . $id_huesped;
                        header('Location: /hotel_completo/public/guests'); // Redirect to guest list
                        exit();
                    } else {
                        $error_message = 'Error al registrar el huésped. El número de documento o correo electrónico podrían ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe un huésped con el mismo Número de Documento o Correo Electrónico.';
                    } else {
                        $error_message = 'Error de base de datos al registrar huésped: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'guests/create.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a guest or processes its update.
     * @param int $id_huesped
     */
    public function edit($id_huesped) {
        $title = "Editar Huésped";
        $guest = $this->guestModel->getById($id_huesped);
        $error_message = '';
        $success_message = '';

        if (!$guest) {
            $_SESSION['error_message'] = 'Huésped no encontrado.';
            header('Location: /hotel_completo/public/guests');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'ciudad' => trim($_POST['ciudad'] ?? ''),
                'pais' => trim($_POST['pais'] ?? ''),
                'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? '')
            ];

            // Basic validation
            if (empty($data['nombre']) || empty($data['apellido'])) {
                $error_message = 'Nombre y Apellido son obligatorios.';
            } elseif (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Formato de correo electrónico inválido.';
            } else {
                try {
                    if ($this->guestModel->update($id_huesped, $data)) {
                        $_SESSION['success_message'] = 'Huésped actualizado exitosamente.';
                        $guest = $this->guestModel->getById($id_huesped); // Reload guest data to reflect changes
                    } else {
                        $error_message = 'Error al actualizar el huésped.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe otro huésped con el mismo Número de Documento o Correo Electrónico.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar huésped: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'guests/edit.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a guest.
     * @param int $id_huesped
     */
    public function delete($id_huesped) {
        try {
            if ($this->guestModel->delete($id_huesped)) {
                $_SESSION['success_message'] = 'Huésped eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el huésped.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Foreign key constraint violation
                $_SESSION['error_message'] = 'No se puede eliminar el huésped porque tiene reservas o cargos asociados.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el huésped: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/guests');
        exit();
    }

    /**
     * AJAX endpoint to search guests.
     * Reusable for other modules (e.g., Bookings, CashRegister, Quotations).
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
