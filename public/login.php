<?php
// hotel_completo/public/login.php

// Inicia la sesión PHP al comienzo de cada script que la necesite
session_start();

// Si el usuario ya está logueado, redirigirlo al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /hotel_completo/public/dashboard.php'); // Redirigir al dashboard
    exit();
}

// Incluye los archivos necesarios
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/lib/Database.php';
require_once __DIR__ . '/../app/models/User.php'; // Se requiere el modelo User
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Crear una instancia del controlador de autenticación
$authController = new AuthController();

// Si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController->login();
} else {
    // Si no es un POST, simplemente muestra el formulario de login
    $authController->showLoginForm();
}