<?php
// hotel_completo/app/models/RoomType.php

class RoomType {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los tipos de habitación.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM tipos_habitacion ORDER BY nombre_tipo ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un tipo de habitación por su ID.
     * @param int $id_tipo_habitacion
     * @return array|false
     */
    public function getById($id_tipo_habitacion) {
        $stmt = $this->pdo->prepare("SELECT * FROM tipos_habitacion WHERE id_tipo_habitacion = ?");
        $stmt->execute([$id_tipo_habitacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo tipo de habitación.
     * @param array $data Los datos del tipo de habitación.
     * @return bool True en éxito, False en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO tipos_habitacion (nombre_tipo, capacidad, precio_base, descripcion, comodidades, foto_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre_tipo'],
            $data['capacidad'],
            $data['precio_base'],
            $data['descripcion'],
            $data['comodidades'],
            $data['foto_url']
        ]);
    }

    /**
     * Actualiza un tipo de habitación existente.
     * @param int $id_tipo_habitacion El ID del tipo de habitación a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_tipo_habitacion, $data) {
        $sql = "UPDATE tipos_habitacion SET nombre_tipo = ?, capacidad = ?, precio_base = ?, descripcion = ?, comodidades = ?, foto_url = ? WHERE id_tipo_habitacion = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nombre_tipo'],
            $data['capacidad'],
            $data['precio_base'],
            $data['descripcion'],
            $data['comodidades'],
            $data['foto_url'],
            $id_tipo_habitacion
        ]);
    }

    /**
     * Elimina un tipo de habitación.
     * @param int $id_tipo_habitacion El ID del tipo de habitación a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_tipo_habitacion) {
        // Antes de eliminar un tipo de habitación, considera si hay habitaciones asociadas.
        // Si la restricción de clave foránea en la tabla 'habitaciones' es RESTRICT,
        // esto fallará si hay habitaciones de este tipo.
        // Podrías añadir una lógica para verificar esto o cambiar el estado a "inactivo".
        $stmt = $this->pdo->prepare("DELETE FROM tipos_habitacion WHERE id_tipo_habitacion = ?");
        return $stmt->execute([$id_tipo_habitacion]);
    }
}