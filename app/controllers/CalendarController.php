<?php
// hotel_completo/app/controllers/CalendarController.php

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';

class CalendarController {
    private $bookingModel;
    private $roomModel;

    public function __construct() {
        $pdo = Database::getInstance()->getConnection();
        $this->bookingModel = new Booking($pdo);
        $this->roomModel = new Room($pdo);
    }

    /**
     * Muestra el calendario de reservas para un mes y año dados.
     * Por defecto, muestra el mes actual.
     * @param int|null $year Año a mostrar.
     * @param int|null $month Mes a mostrar.
     */
    public function index($year = null, $month = null) {
        $title = "Calendario de Reservas";

        // Determinar el año y mes actual o el solicitado
        $currentYear = $year ?? date('Y');
        $currentMonth = $month ?? date('n'); // n = mes sin ceros iniciales (1-12)

        // Obtener el primer y último día del mes
        $firstDayOfMonth = new DateTime("$currentYear-$currentMonth-01");
        $lastDayOfMonth = new DateTime($firstDayOfMonth->format('Y-m-t')); // 't' para el último día del mes

        // Obtener las reservas para el rango de este mes
        $bookings = $this->bookingModel->getBookingsByDateRange(
            $firstDayOfMonth->format('Y-m-d'),
            $lastDayOfMonth->format('Y-m-d')
        );

        // Obtener todas las habitaciones para mostrar en el calendario
        $rooms = $this->roomModel->getAll(); // Usamos getAll() que es más ligero para este listado

        // Agrupar reservas por día y por habitación para facilitar la visualización en el calendario
        $bookingsByDayAndRoom = [];
        foreach ($bookings as $booking) {
            $currentDate = new DateTime($booking['fecha_entrada']);
            $endDate = new DateTime($booking['fecha_salida']);

            // Iterar sobre cada día que la reserva ocupa
            while ($currentDate < $endDate) {
                $dayKey = $currentDate->format('Y-m-d');
                $roomNumber = $booking['numero_habitacion'];

                if (!isset($bookingsByDayAndRoom[$dayKey])) {
                    $bookingsByDayAndRoom[$dayKey] = [];
                }
                if (!isset($bookingsByDayAndRoom[$dayKey][$roomNumber])) {
                    $bookingsByDayAndRoom[$dayKey][$roomNumber] = [];
                }
                $bookingsByDayAndRoom[$dayKey][$roomNumber][] = $booking;
                $currentDate->modify('+1 day');
            }
        }

        // Para la navegación de meses
        $prevMonth = (new DateTime("$currentYear-$currentMonth-01"))->modify('-1 month');
        $nextMonth = (new DateTime("$currentYear-$currentMonth-01"))->modify('+1 month');

        $content_view = VIEW_PATH . 'calendar/index.php';
        extract([
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'firstDayOfMonth' => $firstDayOfMonth,
            'prevMonthYear' => $prevMonth->format('Y'),
            'prevMonthNum' => $prevMonth->format('n'),
            'nextMonthYear' => $nextMonth->format('Y'),
            'nextMonthNum' => $nextMonth->format('n'),
            'bookingsByDayAndRoom' => $bookingsByDayAndRoom,
            'rooms' => $rooms,
            'title' => $title
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }
}
