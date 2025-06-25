<?php
// hotel_completo/app/models/CompanySetting.php

class CompanySetting {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene la configuración actual de la empresa.
     * Asume que solo hay una fila de configuración.
     * @return array|false
     */
    public function getSettings() {
        error_log("DEBUG-COMPANY-SETTING: getSettings called.");
        $sql = "SELECT * FROM configuracion_empresa LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log("DEBUG-COMPANY-SETTING: SQL executed: " . $sql);
        error_log("DEBUG-COMPANY-SETTING: Result fetched: " . print_r($settings, true));

        // Si no hay configuración, se devuelve un array con valores por defecto
        if (!$settings) {
            error_log("DEBUG-COMPANY-SETTING: No settings found in DB, returning defaults.");
            return [
                'nombre_empresa' => 'HOTEL GESTIÓN',
                'ruc' => 'N/A',
                'direccion' => 'N/A',
                'telefono' => 'N/A',
                'email' => 'N/A',
                'logo_url' => null
            ];
        }
        return $settings;
    }

    /**
     * Actualiza la configuración de la empresa.
     * Si no existe una fila, la crea.
     * @param array $data Los datos de configuración a guardar.
     * @return bool True en éxito, False en fallo.
     */
    public function updateSettings($data) {
        error_log("DEBUG-COMPANY-SETTING: updateSettings called with data: " . print_r($data, true));
        $settings = $this->getSettings(); // Intenta obtener la configuración existente

        try {
            if (isset($settings['id_config'])) { // Si ya existe, actualiza
                $sql = "UPDATE configuracion_empresa SET
                        nombre_empresa = ?, ruc = ?, direccion = ?, telefono = ?, email = ?, logo_url = ?
                        WHERE id_config = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([
                    $data['nombre_empresa'],
                    $data['ruc'] ?? null,
                    $data['direccion'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    $data['logo_url'] ?? null,
                    $settings['id_config']
                ]);
                error_log("DEBUG-COMPANY-SETTING: UPDATE SQL: " . $sql . " | Result: " . ($result ? 'TRUE' : 'FALSE'));
                return $result;
            } else { // Si no existe, inserta una nueva fila (debe ser la primera y única)
                $sql = "INSERT INTO configuracion_empresa (nombre_empresa, ruc, direccion, telefono, email, logo_url)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute([
                    $data['nombre_empresa'],
                    $data['ruc'] ?? null,
                    $data['direccion'] ?? null,
                    $data['telefono'] ?? null,
                    $data['email'] ?? null,
                    $data['logo_url'] ?? null
                ]);
                error_log("DEBUG-COMPANY-SETTING: INSERT SQL: " . $sql . " | Result: " . ($result ? 'TRUE' : 'FALSE'));
                return $result ? $this->pdo->lastInsertId() : false;
            }
        } catch (PDOException $e) {
            error_log("DEBUG-COMPANY-SETTING ERROR: PDOException in updateSettings: " . $e->getMessage());
            throw $e; // Re-lanzar la excepción para que el controlador la capture
        }
    }
}
