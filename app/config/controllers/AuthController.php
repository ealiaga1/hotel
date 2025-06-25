<?php
// hotel_completo/app/controllers/AuthController.php

class AuthController {
    private $userModel;

    public function __construct() {
        // Obtener la instancia de PDO a través de la clase Database
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $this->userModel = new User($pdo); // Instanciar el modelo de usuario
    }

    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm() {
        $title = "Iniciar Sesión";
        $content_view = __DIR__ . '/../views/auth/login.php'; // Ruta a la vista del formulario
        include __DIR__ . '/../views/layouts/auth_layout.php'; // Incluye el layout base
    }

    /**
     * Procesa el intento de login.
     */
    public function login() {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error_message = '';

        if (empty($email) || empty($password)) {
            $error_message = 'Por favor, introduce tu correo y contraseña.';
        } else {
            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Autenticación exitosa
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre_usuario'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role_id'] = $user['id_rol']; // Guarda el ID del rol
                // Opcional: Podrías guardar el nombre del rol si lo obtuvieras con un JOIN

                // Redirigir al dashboard u otra página inicial del sistema
                header('Location: /hotel_completo/public/dashboard.php');
                exit();
            } else {
                $error_message = 'Credenciales incorrectas o usuario inactivo.';
            }
        }

        // Si hay un error, vuelve a mostrar el formulario con el mensaje
        $title = "Iniciar Sesión";
        $content_view = __DIR__ . '/../views/auth/login.php';
        include __DIR__ . '/../views/layouts/auth_layout.php';
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout() {
        session_start(); // Asegura que la sesión esté iniciada para destruirla
        session_unset(); // Elimina todas las variables de sesión
        session_destroy(); // Destruye la sesión
        header('Location: /hotel_completo/public/login.php'); // Redirige al formulario de login
        exit();
    }
}