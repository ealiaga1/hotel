<?php
// hotel_completo/app/controllers/UserController.php

// Required models
require_once __DIR__ . '/../models/User.php'; // User model

class UserController {
    private $userModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->userModel = new User($pdo);
    }

    /**
     * Displays the list of all staff members.
     */
    public function index() {
        $title = "Gestión de Personal";
        $users = $this->userModel->getAllUsers(); // Get all users

        // Session messages (success/error)
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']); // Clear messages

        $content_view = VIEW_PATH . 'users/index.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new staff member or processes its creation.
     */
    public function create() {
        $title = "Registrar Nuevo Empleado";
        $roles = $this->userModel->getAllRoles(); // Get all roles for the dropdown

        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_rol' => $_POST['id_rol'] ?? '',
                'nombre_usuario' => trim($_POST['nombre_usuario'] ?? ''),
                'apellido_usuario' => trim($_POST['apellido_usuario'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '', // Password in plain text from form
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'dni' => trim($_POST['dni'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'activo')
            ];

            // Basic validation
            if (empty($data['nombre_usuario']) || empty($data['email']) || empty($data['password']) || empty($data['id_rol'])) {
                $error_message = 'Nombre, Correo Electrónico, Contraseña y Rol son obligatorios.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Formato de correo electrónico inválido.';
            } else {
                try {
                    $id_usuario = $this->userModel->createUser($data);
                    if ($id_usuario) {
                        $_SESSION['success_message'] = 'Empleado registrado exitosamente con ID: ' . $id_usuario;
                        header('Location: /hotel_completo/public/users'); // Redirect to user list
                        exit();
                    } else {
                        $error_message = 'Error al registrar el empleado. El DNI o Correo Electrónico podrían ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe un empleado con el mismo DNI o Correo Electrónico.';
                    } else {
                        $error_message = 'Error de base de datos al registrar empleado: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'users/create.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a staff member or processes its update.
     * @param int $id_usuario
     */
    public function edit($id_usuario) {
        $title = "Editar Empleado";
        $user = $this->userModel->getUserById($id_usuario);
        $roles = $this->userModel->getAllRoles(); // Get all roles for the dropdown

        $error_message = '';
        $success_message = '';

        if (!$user) {
            $_SESSION['error_message'] = 'Empleado no encontrado.';
            header('Location: /hotel_completo/public/users');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_rol' => $_POST['id_rol'] ?? '',
                'nombre_usuario' => trim($_POST['nombre_usuario'] ?? ''),
                'apellido_usuario' => trim($_POST['apellido_usuario'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '', // Optional new password
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'dni' => trim($_POST['dni'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'activo')
            ];

            // Basic validation
            if (empty($data['nombre_usuario']) || empty($data['email']) || empty($data['id_rol'])) {
                $error_message = 'Nombre, Correo Electrónico y Rol son obligatorios.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Formato de correo electrónico inválido.';
            } else {
                try {
                    if ($this->userModel->updateUser($id_usuario, $data)) {
                        $_SESSION['success_message'] = 'Empleado actualizado exitosamente.';
                        // Reload user data to reflect changes in the form
                        $user = $this->userModel->getUserById($id_usuario);
                    } else {
                        $error_message = 'Error al actualizar el empleado.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe otro empleado con el mismo DNI o Correo Electrónico.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar empleado: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'users/edit.php';
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a staff member.
     * @param int $id_usuario
     */
    public function delete($id_usuario) {
        // Prevent deletion of the currently logged-in user
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id_usuario) {
            $_SESSION['error_message'] = 'No puedes eliminar tu propia cuenta mientras estás logueado.';
            header('Location: /hotel_completo/public/users');
            exit();
        }

        try {
            if ($this->userModel->deleteUser($id_usuario)) {
                $_SESSION['success_message'] = 'Empleado eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el empleado.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Foreign key constraint violation
                $_SESSION['error_message'] = 'No se puede eliminar el empleado porque tiene registros asociados (ej. logs, transacciones).';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el empleado: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/users');
        exit();
    }
}