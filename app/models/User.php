<?php
// hotel_completo/app/models/User.php

class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Finds a user by their email for authentication.
     * @param string $email The user's email.
     * @return array|false User data as an associative array or false if not found.
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id_usuario, id_rol, nombre_usuario, email, password_hash FROM usuarios WHERE email = ? AND estado = 'activo'");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- NEW METHODS FOR STAFF MANAGEMENT ---

    /**
     * Gets all users (staff members) with their role names.
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT u.*, r.nombre_rol
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.apellido_usuario ASC, u.nombre_usuario ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a user (staff member) by their ID.
     * @param int $id_usuario
     * @return array|false
     */
    public function getUserById($id_usuario) {
        $sql = "SELECT u.*, r.nombre_rol
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new user (staff member).
     * @param array $data User data including password (will be hashed).
     * @return int|false The ID of the new user or false on failure.
     */
    public function createUser($data) {
        $sql = "INSERT INTO usuarios (id_rol, nombre_usuario, apellido_usuario, email, password_hash, telefono, direccion, dni, estado, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // NOW() for fecha_creacion

        // Hash the password before storing it
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['id_rol'],
            $data['nombre_usuario'],
            $data['apellido_usuario'] ?? null,
            $data['email'],
            $hashed_password, // Store the hash
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['dni'] ?? null,
            $data['estado'] ?? 'activo'
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Updates an existing user (staff member).
     * @param int $id_usuario The ID of the user to update.
     * @param array $data The new user data.
     * @return bool True on success, false on failure.
     */
    public function updateUser($id_usuario, $data) {
        $sql = "UPDATE usuarios SET
                id_rol = ?, nombre_usuario = ?, apellido_usuario = ?, email = ?,
                telefono = ?, direccion = ?, dni = ?, estado = ? ";
        $params = [
            $data['id_rol'],
            $data['nombre_usuario'],
            $data['apellido_usuario'] ?? null,
            $data['email'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['dni'] ?? null,
            $data['estado']
        ];

        // Only update password if a new one is provided
        if (!empty($data['password'])) {
            $sql .= ", password_hash = ? ";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id_usuario = ?";
        $params[] = $id_usuario;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Deletes a user (staff member).
     * @param int $id_usuario The ID of the user to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteUser($id_usuario) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        return $stmt->execute([$id_usuario]);
    }

    /**
     * Gets all roles (for dropdowns).
     * @return array
     */
    public function getAllRoles() {
        $stmt = $this->pdo->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NUEVO MÉTODO PARA EL DASHBOARD ---
    public function getTotalUsersCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'"); // Count only active users
        return $stmt->fetchColumn();
    }
}
