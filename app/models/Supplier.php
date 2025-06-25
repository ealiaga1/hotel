<?php
// hotel_completo/app/models/Supplier.php

// Asegúrate de que no haya llamadas a informe_de_errores() aquí.

class Supplier {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los proveedores.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM proveedores ORDER BY razon_social ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un proveedor por su ID.
     * @param int $id_proveedor
     * @return array|false
     */
    public function getById($id_proveedor) {
        $stmt = $this->pdo->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
        $stmt->execute([$id_proveedor]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo proveedor.
     * @param array $data Los datos del proveedor.
     * @return int|false El ID del nuevo proveedor o false en fallo.
     */
    public function create($data) {
        $sql = "INSERT INTO proveedores (tipo, razon_social, ruc_dni, departamento, provincia, distrito, direccion, telefono_fijo, telefono_celular, telefono_otro, email, contacto, nro_cta_detraccion, modo_pago, observaciones, fecha_registro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['tipo'],
            $data['razon_social'],
            $data['ruc_dni'] ?? null,
            $data['departamento'] ?? null,
            $data['provincia'] ?? null,
            $data['distrito'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono_fijo'] ?? null,
            $data['telefono_celular'] ?? null,
            $data['telefono_otro'] ?? null,
            $data['email'] ?? null,
            $data['contacto'] ?? null,
            $data['nro_cta_detraccion'] ?? null,
            $data['modo_pago'],
            $data['observaciones'] ?? null
        ]);
        return $result ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Actualiza un proveedor existente.
     * @param int $id_proveedor El ID del proveedor a actualizar.
     * @param array $data Los nuevos datos.
     * @return bool True en éxito, False en fallo.
     */
    public function update($id_proveedor, $data) {
        $sql = "UPDATE proveedores SET
                tipo = ?, razon_social = ?, ruc_dni = ?, departamento = ?, provincia = ?, distrito = ?, direccion = ?,
                telefono_fijo = ?, telefono_celular = ?, telefono_otro = ?, email = ?, contacto = ?, nro_cta_detraccion = ?,
                modo_pago = ?, observaciones = ?
                WHERE id_proveedor = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['tipo'],
            $data['razon_social'],
            $data['ruc_dni'] ?? null,
            $data['departamento'] ?? null,
            $data['provincia'] ?? null,
            $data['distrito'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono_fijo'] ?? null,
            $data['telefono_celular'] ?? null,
            $data['telefono_otro'] ?? null,
            $data['email'] ?? null,
            $data['contacto'] ?? null,
            $data['nro_cta_detraccion'] ?? null,
            $data['modo_pago'],
            $data['observaciones'] ?? null,
            $id_proveedor
        ]);
    }

    /**
     * Elimina un proveedor.
     * @param int $id_proveedor El ID del proveedor a eliminar.
     * @return bool True en éxito, False en fallo.
     */
    public function delete($id_proveedor) {
        $stmt = $this->pdo->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
        return $stmt->execute([$id_proveedor]);
    }
}
