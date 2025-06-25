<?php
// hotel_completo/app/lib/Database.php

// Incluye el archivo de configuración de la base de datos
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null; // Instancia única de la clase Database
    private $pdo; // Objeto PDO para la conexión

    // Constructor privado para aplicar el patrón Singleton
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva la emulación de sentencias preparadas para mayor seguridad
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En un entorno de producción, NO muestres el mensaje de error directamente.
            // En su lugar, registra el error y muestra un mensaje amigable.
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            die('Error de conexión a la base de datos. Por favor, inténtelo más tarde.');
        }
    }

    // Método estático para obtener la única instancia de la conexión
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método para obtener el objeto PDO
    public function getConnection() {
        return $this->pdo;
    }

    // Evita la clonación de la instancia
    private function __clone() {}

    // Evita la deserialización de la instancia
    public function __wakeup() {
        throw new Exception("No se puede deserializar una instancia de singleton.");
    }
}