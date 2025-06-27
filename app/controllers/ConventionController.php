<?php
// hotel_completo/app/controllers/ConventionController.php

require_once __DIR__ . '/../models/ConventionRoom.php';
require_once __DIR__ . '/../models/ConventionBooking.php';
require_once __DIR__ . '/../models/Guest.php'; // Para buscar huéspedes

class ConventionController {
    private $conventionRoomModel;
    private $conventionBookingModel;
    private $guestModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->conventionRoomModel = new ConventionRoom($pdo);
        $this->conventionBookingModel = new ConventionBooking($pdo);
        $this->guestModel = new Guest($pdo);
    }

    /**
     * Displays the list of all convention bookings (events), with filters and search.
     */
    public function index() {
        $title = "Gestión de Eventos y Convenciones";
        
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $filters = [];
        // Obtener los parámetros de filtro de $_GET
        $search_query = trim($_GET['search_query'] ?? '');
        $status_filter = $_GET['status_filter'] ?? [];
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $show_all = isset($_GET['show_all']);

        // Lógica de filtrado:
        if (empty($search_query) && empty($status_filter) && empty($start_date) && empty($end_date) && !$show_all) {
            $filters['status'] = ['confirmada', 'pendiente']; // Por defecto
        } else {
            if (!empty($search_query)) {
                $filters['client_name'] = $search_query;
            }
            if (!empty($status_filter)) {
                if (!$show_all && is_array($status_filter)) {
                    $filters['status'] = $status_filter;
                }
            }
            if (!empty($start_date)) {
                $filters['start_date'] = $start_date;
            }
            if (!empty($end_date)) {
                $filters['end_date'] = $end_date;
            }
            if ($show_all) {
                $filters['show_all'] = true;
            }
        }
        
        $bookings = $this->conventionBookingModel->searchBookings($filters);

        $all_booking_statuses = [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'realizada' => 'Realizada',
            'cancelada' => 'Cancelada'
        ];

        $content_view = VIEW_PATH . 'convention/index.php';
        extract([
            'bookings' => $bookings,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'all_booking_statuses' => $all_booking_statuses,
            'current_search_query' => $search_query,
            'current_status_filter' => $status_filter,
            'current_start_date' => $start_date,
            'current_end_date' => $end_date,
            'current_show_all' => $show_all
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new convention booking or processes its creation.
     */
    public function create() {
        $title = "Crear Nueva Reserva de Convención";
        $error_message = '';
        $success_message = '';

        $conventionRooms = $this->conventionRoomModel->getAll(); // Obtener todas las salas
        $guests = $this->guestModel->getAll(); // Obtener todos los huéspedes

        // Lógica para pre-seleccionar sala si viene en la URL o si hay una por defecto
        $preselected_room_id = $_GET['room_id'] ?? null;
        $preselected_date = $_GET['date'] ?? date('Y-m-d');
        $preselected_start_time = $_GET['start_time'] ?? '09:00';
        $preselected_end_time = $_GET['end_time'] ?? '17:00';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_guest_from_list = isset($_POST['id_huesped_existente']) && !empty($_POST['id_huesped_existente']);
            $id_huesped = $is_guest_from_list ? $_POST['id_huesped_existente'] : null;

            $booking_data = [
                'id_sala' => $_POST['id_sala'] ?? null,
                'id_huesped' => $id_huesped,
                'nombre_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['nombre'] . ' ' . $this->guestModel->getById($id_huesped)['apellido']) : trim($_POST['nombre_contacto'] ?? ''),
                'telefono_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['telefono'] ?? null) : trim($_POST['telefono_contacto'] ?? ''),
                'email_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['email'] ?? null) : trim($_POST['email_contacto'] ?? ''),
                'nombre_evento' => trim($_POST['nombre_evento'] ?? ''),
                'fecha_evento' => trim($_POST['fecha_evento'] ?? ''),
                'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
                'hora_fin' => trim($_POST['hora_fin'] ?? ''),
                'num_asistentes' => (int)($_POST['num_asistentes'] ?? 0),
                'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                'estado' => trim($_POST['estado'] ?? 'pendiente'),
                'comentarios' => trim($_POST['comentarios'] ?? null)
            ];

            $services_json = $_POST['event_services_json'] ?? '[]';
            $services_data = json_decode($services_json, true);
            if (!is_array($services_data)) $services_data = [];

            // Validaciones
            if (empty($booking_data['id_sala']) || empty($booking_data['nombre_evento']) || empty($booking_data['fecha_evento']) || empty($booking_data['hora_inicio']) || empty($booking_data['hora_fin'])) {
                $error_message = 'La sala, nombre del evento, fecha y horas son obligatorios.';
            } elseif ($booking_data['hora_inicio'] >= $booking_data['hora_fin']) {
                $error_message = 'La hora de fin debe ser posterior a la hora de inicio.';
            } elseif (empty($booking_data['nombre_contacto']) || (empty($booking_data['telefono_contacto']) && empty($booking_data['email_contacto']))) {
                $error_message = 'Debe especificar el nombre del contacto y al menos un teléfono o email de contacto.';
            } else {
                // Verificar disponibilidad de la sala
                $overlapping = $this->conventionRoomModel->getAvailableRooms(
                    $booking_data['fecha_evento'],
                    $booking_data['hora_inicio'],
                    $booking_data['hora_fin'],
                    null // No excluir ninguna reserva para la creación
                );
                // Si la sala seleccionada NO está en la lista de disponibles, significa que se solapa
                $selected_room_is_available = false;
                foreach ($overlapping as $room_available) {
                    if ($room_available['id_sala'] == $booking_data['id_sala']) {
                        $selected_room_is_available = true;
                        break;
                    }
                }
                if (!$selected_room_is_available) {
                    $error_message = 'La sala seleccionada no está disponible en el horario especificado o ya tiene una reserva que se solapa.';
                } else {
                    try {
                        $id_reserva = $this->conventionBookingModel->create($booking_data, $services_data);
                        if ($id_reserva) {
                            $_SESSION['success_message'] = 'Reserva de convención creada exitosamente con ID: ' . $id_reserva;
                            header('Location: /hotel_completo/public/convention');
                            exit();
                        } else {
                            $error_message = 'Error al crear la reserva de convención.';
                        }
                    } catch (Exception $e) {
                        $error_message = 'Error de base de datos al crear reserva de convención: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'convention/create.php';
        extract([
            'conventionRooms' => $conventionRooms,
            'guests' => $guests,
            'error_message' => $error_message,
            'success_message' => $success_message,
            'preselected_room_id' => $preselected_room_id,
            'preselected_date' => $preselected_date,
            'preselected_start_time' => $preselected_start_time,
            'preselected_end_time' => $preselected_end_time
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a convention booking or processes its update.
     * @param int $id_reserva_convencion
     */
    public function edit($id_reserva_convencion) {
        $title = "Editar Reserva de Convención";
        $booking = $this->conventionBookingModel->getById($id_reserva_convencion);
        $conventionRooms = $this->conventionRoomModel->getAll();
        $guests = $this->guestModel->getAll();

        $error_message = '';
        $success_message = '';

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva de convención no encontrada.';
            header('Location: /hotel_completo/public/convention');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_guest_from_list = isset($_POST['id_huesped_existente']) && !empty($_POST['id_huesped_existente']);
            $id_huesped = $is_guest_from_list ? $_POST['id_huesped_existente'] : null;

            $booking_data = [
                'id_sala' => $_POST['id_sala'] ?? null,
                'id_huesped' => $id_huesped,
                'nombre_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['nombre'] . ' ' . $this->guestModel->getById($id_huesped)['apellido']) : trim($_POST['nombre_contacto'] ?? ''),
                'telefono_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['telefono'] ?? null) : trim($_POST['telefono_contacto'] ?? ''),
                'email_contacto' => $is_guest_from_list ? ($this->guestModel->getById($id_huesped)['email'] ?? null) : trim($_POST['email_contacto'] ?? ''),
                'nombre_evento' => trim($_POST['nombre_evento'] ?? ''),
                'fecha_evento' => trim($_POST['fecha_evento'] ?? ''),
                'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
                'hora_fin' => trim($_POST['hora_fin'] ?? ''),
                'num_asistentes' => (int)($_POST['num_asistentes'] ?? 0),
                'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                'estado' => trim($_POST['estado'] ?? 'pendiente'),
                'comentarios' => trim($_POST['comentarios'] ?? null)
            ];

            $services_json = $_POST['event_services_json'] ?? '[]';
            $services_data = json_decode($services_json, true);
            if (!is_array($services_data)) $services_data = [];

            // Validaciones
            if (empty($booking_data['id_sala']) || empty($booking_data['nombre_evento']) || empty($booking_data['fecha_evento']) || empty($booking_data['hora_inicio']) || empty($booking_data['hora_fin'])) {
                $error_message = 'La sala, nombre del evento, fecha y horas son obligatorios.';
            } elseif ($booking_data['hora_inicio'] >= $booking_data['hora_fin']) {
                $error_message = 'La hora de fin debe ser posterior a la hora de inicio.';
            } elseif (empty($booking_data['nombre_contacto']) || (empty($booking_data['telefono_contacto']) && empty($booking_data['email_contacto']))) {
                $error_message = 'Debe especificar el nombre del contacto y al menos un teléfono o email de contacto.';
            } else {
                // Verificar disponibilidad de la sala, excluyendo la reserva actual
                $overlapping = $this->conventionRoomModel->getAvailableRooms(
                    $booking_data['fecha_evento'],
                    $booking_data['hora_inicio'],
                    $booking_data['hora_fin'],
                    $id_reserva_convencion // Excluir esta reserva al verificar solapamientos
                );
                $selected_room_is_available = false;
                foreach ($overlapping as $room_available) {
                    if ($room_available['id_sala'] == $booking_data['id_sala']) {
                        $selected_room_is_available = true;
                        break;
                    }
                }
                // Si la sala seleccionada NO está en la lista de disponibles, significa que se solapa
                if (!$selected_room_is_available) {
                    $error_message = 'La sala seleccionada no está disponible en el horario especificado o ya tiene una reserva que se solapa.';
                } else {
                    try {
                        if ($this->conventionBookingModel->update($id_reserva_convencion, $booking_data, $services_data)) {
                            $_SESSION['success_message'] = 'Reserva de convención actualizada exitosamente.';
                            header('Location: /hotel_completo/public/convention');
                            exit();
                        } else {
                            $error_message = 'Error al actualizar la reserva de convención.';
                        }
                    } catch (Exception $e) {
                        $error_message = 'Error de base de datos al actualizar reserva de convención: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'convention/edit.php';
        extract([
            'booking' => $booking,
            'conventionRooms' => $conventionRooms,
            'guests' => $guests,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a convention booking.
     * @param int $id_reserva_convencion
     */
    public function delete($id_reserva_convencion) {
        try {
            if ($this->conventionBookingModel->delete($id_reserva_convencion)) {
                $_SESSION['success_message'] = 'Reserva de convención eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la reserva de convención.';
            }
        } catch (PDOException | Exception $e) {
            $_SESSION['error_message'] = 'Error desconocido al eliminar la reserva de convención: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/convention');
        exit();
    }

    /**
     * Displays the list of all convention rooms, with options to manage them.
     */
    public function rooms() {
        $title = "Gestión de Salas de Convenciones";
        $rooms = $this->conventionRoomModel->getAll();

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'convention/rooms/index.php';
        extract([
            'rooms' => $rooms,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new convention room or processes its creation.
     */
    public function createRoom() {
        $title = "Crear Nueva Sala de Convenciones";
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_sala' => trim($_POST['nombre_sala'] ?? ''),
                'capacidad_max' => (int)($_POST['capacidad_max'] ?? 0),
                'precio_hora_base' => (float)($_POST['precio_hora_base'] ?? 0.00),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible'),
                'ubicacion' => trim($_POST['ubicacion'] ?? '')
            ];

            if (empty($data['nombre_sala']) || $data['capacidad_max'] <= 0 || $data['precio_hora_base'] <= 0) {
                $error_message = 'Nombre, capacidad y precio base son obligatorios y deben ser valores positivos.';
            } else {
                try {
                    $id_sala = $this->conventionRoomModel->create($data);
                    if ($id_sala) {
                        $_SESSION['success_message'] = 'Sala de convenciones creada exitosamente con ID: ' . $id_sala;
                        header('Location: /hotel_completo/public/convention/rooms');
                        exit();
                    } else {
                        $error_message = 'Error al crear la sala de convenciones. El nombre podría ya existir.';
                    }
                } catch (PDOException | Exception $e) {
                    $error_message = 'Error de base de datos al crear sala de convenciones: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'convention/rooms/create.php';
        extract(['error_message' => $error_message, 'success_message' => $success_message]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a convention room or processes its update.
     * @param int $id_sala
     */
    public function editRoom($id_sala) {
        $title = "Editar Sala de Convenciones";
        $room = $this->conventionRoomModel->getById($id_sala);
        $error_message = '';
        $success_message = '';

        if (!$room) {
            $_SESSION['error_message'] = 'Sala de convenciones no encontrada.';
            header('Location: /hotel_completo/public/convention/rooms');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_sala' => trim($_POST['nombre_sala'] ?? ''),
                'capacidad_max' => (int)($_POST['capacidad_max'] ?? 0),
                'precio_hora_base' => (float)($_POST['precio_hora_base'] ?? 0.00),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => trim($_POST['estado'] ?? 'disponible'),
                'ubicacion' => trim($_POST['ubicacion'] ?? '')
            ];

            if (empty($data['nombre_sala']) || $data['capacidad_max'] <= 0 || $data['precio_hora_base'] <= 0) {
                $error_message = 'Nombre, capacidad y precio base son obligatorios y deben ser valores positivos.';
            } else {
                try {
                    if ($this->conventionRoomModel->update($id_sala, $data)) {
                        $_SESSION['success_message'] = 'Sala de convenciones actualizada exitosamente.';
                        header('Location: /hotel_completo/public/convention/rooms');
                        exit();
                    } else {
                        $error_message = 'Error al actualizar la sala de convenciones.';
                    }
                } catch (PDOException | Exception $e) {
                    $error_message = 'Error de base de datos al actualizar sala de convenciones: ' . $e->getMessage();
                }
            }
        }

        $content_view = VIEW_PATH . 'convention/rooms/edit.php';
        extract([
            'room' => $room,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a convention room.
     * @param int $id_sala
     */
    public function deleteRoom($id_sala) {
        try {
            if ($this->conventionRoomModel->delete($id_sala)) {
                $_SESSION['success_message'] = 'Sala de convenciones eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la sala de convenciones.';
            }
        } catch (PDOException | Exception $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la sala de convenciones porque tiene reservas asociadas.';
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar la sala de convenciones: ' . $e->getMessage();
            }
        }
        header('Location: /hotel_completo/public/convention/rooms');
        exit();
    }

    /**
     * AJAX endpoint to search available convention rooms.
     */
    public function searchAvailableRoomsAjax() {
        header('Content-Type: application/json');
        $fecha_evento = $_GET['fecha_evento'] ?? null;
        $hora_inicio = $_GET['hora_inicio'] ?? null;
        $hora_fin = $_GET['hora_fin'] ?? null;
        $exclude_id_reserva_convencion = $_GET['exclude_id'] ?? null; // Para edición

        if ($fecha_evento && $hora_inicio && $hora_fin && $hora_inicio < $hora_fin) {
            $availableRooms = $this->conventionRoomModel->getAvailableRooms($fecha_evento, $hora_inicio, $hora_fin, $exclude_id_reserva_convencion);
            echo json_encode($availableRooms);
        } else {
            echo json_encode([]);
        }
        exit();
    }

    /**
     * AJAX endpoint to search guests. (Re-using GuestModel method)
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
