<?php
// hotel_completo/app/controllers/ReceptionController.php

require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Booking.php'; // Para obtener información de las reservas asociadas

class ReceptionController {
    private $roomModel;
    private $bookingModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->roomModel = new Room($pdo);
        $this->bookingModel = new Booking($pdo);
    }

    /**
     * Displays the main reception dashboard with room statuses.
     */
    public function index() {
        $title = "Tablero de Recepción";

        // Obtener todas las habitaciones con su tipo y estado actual
        $rooms = $this->roomModel->getAllRoomsWithDetails(); // Necesitarás este método en RoomModel

        // Para cada habitación, obtener información de la reserva activa si está ocupada
        foreach ($rooms as &$room) {
            if ($room['estado'] === 'ocupada') {
                $activeBooking = $this->bookingModel->getActiveBookingByRoomId($room['id_habitacion']); // Necesitarás este método en BookingModel
                $room['active_booking'] = $activeBooking;
            } else {
                $room['active_booking'] = null;
            }
        }
        unset($room); // Romper la referencia al último elemento

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $content_view = VIEW_PATH . 'reception/index.php';
        // Pasa los datos de las habitaciones a la vista
        extract([
            'rooms' => $rooms,
            'success_message' => $success_message,
            'error_message' => $error_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    // Puedes añadir otros métodos aquí para acciones rápidas desde recepción
    // como check-in/out rápido, cambiar estado a mantenimiento, etc.
}
