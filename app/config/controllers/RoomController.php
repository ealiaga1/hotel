<?php
// hotel_completo/app/controllers/RoomController.php

// Las inclusiones de modelos Room.php y RoomType.php son gestionadas por public/index.php

class RoomController {
    private $roomModel;
    private $roomTypeModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->roomModel = new Room($pdo);
        $this->roomTypeModel = new RoomType($pdo);
    }

    /**
     * Displays the list of all rooms.
     */
    public function index() {
        $title = "Gestión de Habitaciones";
        $rooms = $this->roomModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'rooms/index.php';
        extract(['rooms' => $rooms, 'success_message' => $success_message, 'error_message' => $error_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new room or processes its creation.
     */
    public function create() {
        $title = "Crear Nueva Habitación";
        $roomTypes = $this->roomTypeModel->getAll(); // Get all room types for dropdown

        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_tipo_habitacion' => $_POST['id_tipo_habitacion'] ?? '',
                'numero_habitacion' => trim($_POST['numero_habitacion'] ?? ''),
                'piso' => trim($_POST['piso'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible')
            ];

            // Basic validation
            if (empty($data['id_tipo_habitacion']) || empty($data['numero_habitacion'])) {
                $error_message = 'El tipo y número de habitación son obligatorios.';
            } else {
                try {
                    $id_habitacion = $this->roomModel->create($data);
                    if ($id_habitacion) {
                        $_SESSION['success_message'] = 'Habitación creada exitosamente con ID: ' . $id_habitacion;
                        header('Location: /hotel_completo/public/rooms');
                        exit();
                    } else {
                        $error_message = 'Error al crear la habitación. El número de habitación podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') { // Integrity constraint violation: duplicate entry
                        $error_message = 'Error: Ya existe una habitación con ese número.';
                    } else {
                        $error_message = 'Error de base de datos al crear habitación: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'rooms/create.php';
        extract(['roomTypes' => $roomTypes, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a room or processes its update.
     * @param int $id_habitacion
     */
    public function edit($id_habitacion) {
        $title = "Editar Habitación";
        // Eliminamos las líneas de depuración temporalmente añadidas
        // ini_set('display_errors', 1);
        // error_reporting(E_ALL);
        // echo "DEBUGGING RoomController::edit() <br>";
        // echo "Attempting to retrieve Room with ID: " . htmlspecialchars($id_habitacion) . "<br>";

        $room = $this->roomModel->getRoomById($id_habitacion);
        
        // Eliminamos las líneas de depuración temporalmente añadidas
        // echo "Result of getRoomById for ID " . htmlspecialchars($id_habitacion) . ": <br><pre>";
        // var_dump($room);
        // echo "</pre><br>";

        $roomTypes = $this->roomTypeModel->getAll();

        $error_message = '';
        $success_message = '';

        if (!$room) {
            $_SESSION['error_message'] = 'Habitación no encontrada.';
            header('Location: /hotel_completo/public/rooms');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_tipo_habitacion' => $_POST['id_tipo_habitacion'] ?? '',
                'numero_habitacion' => trim($_POST['numero_habitacion'] ?? ''),
                'piso' => trim($_POST['piso'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible')
            ];

            if (empty($data['id_tipo_habitacion']) || empty($data['numero_habitacion'])) {
                $error_message = 'El tipo y número de habitación son obligatorios.';
            } else {
                try {
                    if ($this->roomModel->update($id_habitacion, $data)) {
                        $_SESSION['success_message'] = 'Habitación actualizada exitosamente.';
                        $room = $this->roomModel->getRoomById($id_habitacion); // Reload room data to reflect changes
                    } else {
                        $error_message = 'Error al actualizar la habitación.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe otra habitación con ese número.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar habitación: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'rooms/edit.php';
        extract(['room' => $room, 'roomTypes' => $roomTypes, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a room.
     * @param int $id_habitacion
     */
    public function delete($id_habitacion) {
        try {
            if ($this->roomModel->delete($id_habitacion)) {
                $_SESSION['success_message'] = 'Habitación eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la habitación.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la habitación porque tiene reservas asociadas.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar la habitación: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/rooms');
        exit();
    }

    /**
     * Updates the status of a room directly from an action (e.g., from Reception Dashboard).
     * @param int $id_habitacion The ID of the room to update.
     * @param string $new_status The new status for the room (e.g., 'disponible', 'sucia', 'mantenimiento').
     */
    public function updateStatus($id_habitacion, $new_status) {
        // Eliminamos las líneas de depuración temporalmente añadidas
        ini_set('display_errors', 0);
        // error_reporting(E_ALL); // Mantener para el log del servidor
        // error_log("DEBUG-ROOM-STATUS: updateStatus method called. Room ID: " . $id_habitacion . ", New Status: " . $new_status);
        // error_log("DEBUG-ROOM-STATUS: Request Method: " . $_SERVER['REQUEST_METHOD']);
        // error_log("DEBUG-ROOM-STATUS: POST data: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Acceso no permitido. La actualización de estado debe ser vía POST.';
            header('Location: /hotel_completo/public/reception');
            exit();
        }

        try {
            $allowed_statuses = ['disponible', 'ocupada', 'sucia', 'mantenimiento'];
            if (!in_array($new_status, $allowed_statuses)) {
                $_SESSION['error_message'] = 'Estado de habitación no válido: ' . htmlspecialchars($new_status);
                // error_log("DEBUG-ROOM-STATUS: Invalid new status provided: " . $new_status);
                header('Location: /hotel_completo/public/reception');
                exit();
            }

            $room = $this->roomModel->getRoomById($id_habitacion);
            if (!$room) {
                $_SESSION['error_message'] = 'Habitación no encontrada.';
                // error_log("DEBUG-ROOM-STATUS: Room not found for ID: " . $id_habitacion);
                header('Location: /hotel_completo/public/reception');
                exit();
            }

            if ($room['estado'] === 'ocupada' && !($new_status === 'sucia' || $new_status === 'mantenimiento')) {
                 $_SESSION['error_message'] = 'No se puede cambiar directamente una habitación ocupada a "' . ucfirst($new_status) . '". Debe pasar por el proceso de Checkout o poner en mantenimiento.';
                 // error_log("DEBUG-ROOM-STATUS: Attempted to change occupied room to " . $new_status . " directly.");
                 header('Location: /hotel_completo/public/reception');
                 exit();
            }

            // error_log("DEBUG-ROOM-STATUS: Attempting to update room status in model.");
            if ($this->roomModel->updateRoomStatus($id_habitacion, $new_status)) {
                // error_log("DEBUG-ROOM-STATUS: Room status updated successfully.");
                $_SESSION['success_message'] = 'Estado de la habitación ' . htmlspecialchars($room['numero_habitacion']) . ' actualizado a "' . ucfirst($new_status) . '" exitosamente.';
            } else {
                // error_log("DEBUG-ROOM-STATUS: Failed to update room status in model.");
                $_SESSION['error_message'] = 'Error al actualizar el estado de la habitación (updateRoomStatus regresó false).';
            }
        } catch (PDOException $e) {
            // error_log("DEBUG-ROOM-STATUS ERROR: PDOException: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error de base de datos al actualizar el estado de la habitación: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/reception');
        exit();
    }


    // --- MÉTODOS DE GESTIÓN DE TIPOS DE HABITACIÓN ---
    public function types() {
        $title = "Gestión de Tipos de Habitación";
        $roomTypes = $this->roomTypeModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'rooms/types.php';
        extract(['roomTypes' => $roomTypes, 'success_message' => $success_message, 'error_message' => $error_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function createType() {
        $title = "Crear Nuevo Tipo de Habitación";
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_tipo' => trim($_POST['nombre_tipo'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'capacidad' => $_POST['capacidad'] ?? '',
                'precio_base' => $_POST['precio_base'] ?? '',
                'comodidades' => trim($_POST['comodidades'] ?? ''),
                'foto_url' => trim($_POST['foto_url'] ?? '')
            ];

            if (empty($data['nombre_tipo']) || empty($data['capacidad']) || empty($data['precio_base'])) {
                $error_message = 'Nombre, Capacidad y Precio Base son obligatorios.';
            } elseif (!is_numeric($data['capacidad']) || $data['capacidad'] <= 0) {
                $error_message = 'La capacidad debe ser un número positivo.';
            } elseif (!is_numeric($data['precio_base']) || $data['precio_base'] <= 0) {
                $error_message = 'El precio base debe ser un número positivo.';
            } else {
                try {
                    $id_tipo = $this->roomTypeModel->create($data);
                    if ($id_tipo) {
                        $_SESSION['success_message'] = 'Tipo de habitación creado exitosamente.';
                        header('Location: /hotel_completo/public/rooms/types');
                        exit();
                    } else {
                        $error_message = 'Error al crear el tipo de habitación. El nombre podría ya existir.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe un tipo de habitación con ese nombre.';
                    } else {
                        $error_message = 'Error de base de datos al crear tipo de habitación: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'rooms/create_type.php';
        extract(['error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function editType($id_tipo_habitacion) {
        $title = "Editar Tipo de Habitación";
        $roomType = $this->roomTypeModel->getById($id_tipo_habitacion);
        $error_message = '';
        $success_message = '';

        if (!$roomType) {
            $_SESSION['error_message'] = 'Tipo de habitación no encontrado.';
            header('Location: /hotel_completo/public/rooms/types');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_tipo' => trim($_POST['nombre_tipo'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'capacidad' => $_POST['capacidad'] ?? '',
                'precio_base' => $_POST['precio_base'] ?? '',
                'comodidades' => trim($_POST['comodidades'] ?? ''),
                'foto_url' => trim($_POST['foto_url'] ?? '')
            ];

            if (empty($data['nombre_tipo']) || empty($data['capacidad']) || empty($data['precio_base'])) {
                $error_message = 'Nombre, Capacidad y Precio Base son obligatorios.';
            } elseif (!is_numeric($data['capacidad']) || $data['capacidad'] <= 0) {
                $error_message = 'La capacidad debe ser un número positivo.';
            } elseif (!is_numeric($data['precio_base']) || $data['precio_base'] <= 0) {
                $error_message = 'El precio base debe ser un número positivo.';
            } else {
                try {
                    if ($this->roomTypeModel->update($id_tipo_habitacion, $data)) {
                        $_SESSION['success_message'] = 'Tipo de habitación actualizado exitosamente.';
                        $roomType = $this->roomTypeModel->getById($id_tipo_habitacion); // Reload
                    } else {
                        $error_message = 'Error al actualizar el tipo de habitación.';
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000') {
                        $error_message = 'Error: Ya existe otro tipo de habitación con ese nombre.';
                    } else {
                        $error_message = 'Error de base de datos al actualizar tipo de habitación: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'rooms/edit_type.php';
        extract(['roomType' => $roomType, 'error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    public function deleteType($id_tipo_habitacion) {
        try {
            if ($this->roomTypeModel->delete($id_tipo_habitacion)) {
                $_SESSION['success_message'] = 'Tipo de habitación eliminado exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar el tipo de habitación.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar el tipo de habitación porque tiene habitaciones asociadas.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el tipo de habitación: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/rooms/types');
        exit();
    }
}