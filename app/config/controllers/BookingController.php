<?php
// hotel_completo/app/controllers/BookingController.php

// Required models
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/RoomType.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/CashTransaction.php';
require_once __DIR__ . '/../models/CompanySetting.php';

class BookingController {
    private $pdo;
    private $bookingModel;
    private $guestModel;
    private $roomTypeModel;
    private $roomModel;
    private $paymentModel;
    private $invoiceModel;
    private $cashRegisterModel;
    private $cashTransactionModel;
    private $companySettingModel;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->bookingModel = new Booking($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->roomTypeModel = new RoomType($this->pdo);
        $this->roomModel = new Room($this->pdo);
        $this->paymentModel = new Payment($this->pdo);
        $this->invoiceModel = new Invoice($this->pdo);
        $this->cashRegisterModel = new CashRegister($this->pdo);
        $this->cashTransactionModel = new CashTransaction($this->pdo);
        $this->companySettingModel = new CompanySetting($this->pdo);
    }

    /**
     * Displays the list of all bookings, with filters and search.
     */
    public function index() {
        $title = "Gestión de Reservas";

        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $filters = [];
        // Determinar si se aplica un filtro por defecto (reservas activas) o se usa la búsqueda
        // Los filtros se obtienen de $_GET
        $search_query = trim($_GET['search_query'] ?? '');
        $status_filter = $_GET['status_filter'] ?? [];
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $show_all = isset($_GET['show_all']); // Si el checkbox "Mostrar todas" está marcado

        // Si no hay filtros explícitos ni búsqueda, mostrar solo activas por defecto
        if (empty($search_query) && empty($status_filter) && empty($start_date) && empty($end_date) && !$show_all) {
            $filters['status'] = ['check_in', 'confirmada', 'pendiente'];
        } else {
            // Aplicar filtros si se proporcionaron en la URL o se activó "mostrar_todas"
            if (!empty($search_query)) {
                $filters['guest_name'] = $search_query;
                // También buscar por ID de reserva o número de habitación si es numérico
                if (is_numeric($search_query)) {
                    $filters['booking_id_or_room'] = $search_query;
                }
            }
            if (!empty($status_filter)) {
                // Si 'show_all' está marcado, ignorar cualquier otro filtro de estado específico para mostrar todo
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
        
        $bookings = $this->bookingModel->searchBookings($filters);

        // Definir todos los estados posibles para el dropdown de filtro
        $all_booking_statuses = [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'check_in' => 'Check-in',
            'check_out' => 'Check-out',
            'cancelada' => 'Cancelada'
        ];

        $content_view = VIEW_PATH . 'bookings/index.php';
        extract([
            'bookings' => $bookings,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'all_booking_statuses' => $all_booking_statuses, // Pasar todos los estados
            // Pasar los valores de los filtros para que el formulario los retenga
            'current_search_query' => $search_query,
            'current_status_filter' => $status_filter,
            'current_start_date' => $start_date,
            'current_end_date' => $end_date,
            'current_show_all' => $show_all
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to create a new booking or processes its creation.
     */
    public function create() {
        $title = "Crear Nueva Reserva";
        $roomTypes = $this->roomTypeModel->getAll();
        $availableRooms = [];

        $error_message = '';
        $success_message = '';
        $guest_data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("DEBUG-BOOKING: POST request received in create method.");
            error_log("DEBUG-BOOKING: POST data: " . print_r($_POST, true));

            $is_new_guest = isset($_POST['is_new_guest']) && $_POST['is_new_guest'] === 'true';
            $id_huesped = null;

            if ($is_new_guest) {
                $guest_data_form = [
                    'nombre' => trim($_POST['nombre_huesped'] ?? ''),
                    'apellido' => trim($_POST['apellido_huesped'] ?? ''),
                    'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
                    'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                    'email' => trim($_POST['email_huesped'] ?? ''),
                    'telefono' => trim($_POST['telefono_huesped'] ?? ''),
                    'direccion' => trim($_POST['direccion_huesped'] ?? ''),
                    'ciudad' => trim($_POST['ciudad_huesped'] ?? ''),
                    'pais' => trim($_POST['pais_huesped'] ?? ''),
                    'fecha_nacimiento' => trim($_POST['fecha_nacimiento_huesped'] ?? '')
                ];
                error_log("DEBUG-BOOKING: New guest data form: " . print_r($guest_data_form, true));

                if (empty($guest_data_form['nombre']) || empty($guest_data_form['apellido'])) {
                    $error_message = 'Nombre y Apellido del huésped son obligatorios.';
                    error_log("DEBUG-BOOKING: Validation error - Guest name/surname missing.");
                } elseif (!empty($guest_data_form['email']) && !filter_var($guest_data_form['email'], FILTER_VALIDATE_EMAIL)) {
                    $error_message = 'Formato de correo electrónico inválido.';
                    error_log("DEBUG-BOOKING: Validation error - Invalid guest email format.");
                } else {
                    try {
                        $id_huesped = $this->guestModel->create($guest_data_form);
                        if (!$id_huesped) {
                            $error_message = 'Error al crear el nuevo huésped. Verifique si el DNI/Email ya existe.';
                            error_log("DEBUG-BOOKING: Guest model returned false on create. Likely duplicate DNI/Email.");
                        } else {
                            error_log("DEBUG-BOOKING: New guest created with ID: " . $id_huesped);
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Error de base de datos al crear huésped: ' . $e->getMessage();
                        error_log("DEBUG-BOOKING: PDOException on guest create: " . $e->getMessage());
                    }
                }
            } else {
                $id_huesped = $_POST['id_huesped_existente'] ?? null;
                error_log("DEBUG-BOOKING: Existing guest ID received: " . $id_huesped);
                if (empty($id_huesped)) {
                    $error_message = 'Debe seleccionar un huésped existente.';
                    error_log("DEBUG-BOOKING: Validation error - No existing guest selected.");
                }
            }

            if (!$error_message) {
                $data = [
                    'id_huesped' => $id_huesped,
                    'id_habitacion' => $_POST['id_habitacion'] ?? null,
                    'fecha_entrada' => trim($_POST['fecha_entrada'] ?? ''),
                    'fecha_salida' => trim($_POST['fecha_salida'] ?? ''),
                    'adultos' => (int)($_POST['adultos'] ?? 1),
                    'ninos' => (int)($_POST['ninos'] ?? 0),
                    'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                    'estado' => 'pendiente',
                    'comentarios' => trim($_POST['comentarios'] ?? '')
                ];
                error_log("DEBUG-BOOKING: Booking data array: " . print_r($data, true));

                if (empty($data['fecha_entrada']) || empty($data['fecha_salida']) || $data['adultos'] <= 0) {
                    $error_message = 'Las fechas y el número de adultos son obligatorios.';
                    error_log("DEBUG-BOOKING: Validation error - Dates or adults missing/invalid.");
                } elseif ($data['fecha_entrada'] >= $data['fecha_salida']) {
                    $error_message = 'La fecha de salida debe ser posterior a la fecha de entrada.';
                    error_log("DEBUG-BOOKING: Validation error - Invalid date range.");
                } else {
                    $calculated_price = 0;
                    $num_nights = 0;
                    try {
                        $diff = date_diff(date_create($data['fecha_entrada']), date_create($data['fecha_salida']));
                        $num_nights = $diff->days;
                    } catch (Exception $e) {
                        error_log("DEBUG-BOOKING: Error calculating date diff: " . $e->getMessage());
                        $error_message = 'Error en el cálculo de fechas.';
                    }

                    if (!$error_message && $num_nights > 0) {
                        if (!empty($data['id_habitacion'])) {
                            $room_info = $this->roomModel->getRoomById($data['id_habitacion']);
                            if ($room_info) {
                                $calculated_price = $room_info['precio_base'] * $num_nights;
                                error_log("DEBUG-BOOKING: Price calculated from assigned room: " . $calculated_price);
                            }
                        } else {
                            $id_tipo_habitacion_deseado = $_POST['id_tipo_habitacion_deseado'] ?? null;
                            if ($id_tipo_habitacion_deseado) {
                                $type_info = $this->roomTypeModel->getById($id_tipo_habitacion_deseado);
                                if ($type_info) {
                                    $calculated_price = $type_info['precio_base'] * $num_nights;
                                    error_log("DEBUG-BOOKING: Price calculated from desired room type: " . $calculated_price);
                                }
                            }
                        }
                    } else if (!$error_message) {
                         error_log("DEBUG-BOOKING: num_nights is 0 or less. Cannot calculate price.");
                    }

                    $data['precio_total'] = ($calculated_price > 0 && !$error_message) ? $calculated_price : ($data['precio_total'] > 0 ? $data['precio_total'] : 0.00);

                    if ($data['precio_total'] <= 0) {
                        $error_message = 'El precio total de la reserva debe ser mayor a cero. Por favor, asigne una habitación o tipo de habitación válido.';
                        error_log("DEBUG-BOOKING: Validation error - Price total is zero or less.");
                    }

                    if (!$error_message) {
                        try {
                            $id_reserva = $this->bookingModel->createBooking($data);
                            if ($id_reserva) {
                                error_log("DEBUG-BOOKING: Booking model returned ID: " . $id_reserva);
                                $_SESSION['success_message'] = 'Reserva creada exitosamente con ID: ' . $id_reserva . '. El estado de la habitación se actualizará al hacer Check-in.';
                                header('Location: /hotel_completo/public/bookings');
                                exit();
                            } else {
                                $error_message = 'Error al crear la reserva en el modelo. El modelo devolvió false. Revise logs del modelo Booking.';
                                error_log("DEBUG-BOOKING: Booking model returned false. Check Booking.php createBooking method logs.");
                            }
                        } catch (PDOException $e) {
                            $error_message = 'Error de base de datos al crear reserva: ' . $e->getMessage();
                            error_log("DEBUG-BOOKING: PDOException on booking create: " . $e->getMessage());
                        }
                    }
                }
            }
        } else {
            if (isset($_GET['action']) && $_GET['action'] == 'search_guests_ajax' && isset($_GET['query'])) {
                error_log("DEBUG-BOOKING: AJAX search_guests_ajax received.");
                $query = $_GET['query'];
                $guests = $this->guestModel->searchGuests($query);
                header('Content-Type: application/json');
                echo json_encode($guests);
                exit();
            }

            if (isset($_GET['action']) && $_GET['action'] == 'search_available_rooms_ajax') {
                error_log("DEBUG-BOOKING: AJAX search_available_rooms_ajax received.");
                $fecha_entrada = $_GET['fecha_entrada'] ?? null;
                $fecha_salida = $_GET['fecha_salida'] ?? null;
                $capacidad = $_GET['capacidad'] ?? 1;
                $id_tipo_habitacion = $_GET['id_tipo_habitacion'] ?? null;

                error_log("DEBUG-BOOKING: search_available_rooms_ajax params: Entrada=" . $fecha_entrada . ", Salida=" . $fecha_salida . ", Capacidad=" . $capacidad . ", TipoHab=" . $id_tipo_habitacion);

                if ($fecha_entrada && $fecha_salida && $fecha_entrada < $fecha_salida) {
                    $availableRooms = $this->bookingModel->getAvailableRooms($fecha_entrada, $fecha_salida, $capacidad, $id_tipo_habitacion);
                    error_log("DEBUG-BOOKING: Available rooms found: " . print_r($availableRooms, true));
                } else {
                    $availableRooms = [];
                    error_log("DEBUG-BOOKING: Invalid dates for search_available_rooms_ajax (Fecha Entrada: " . $fecha_entrada . ", Fecha Salida: " . $fecha_salida . ").");
                }
                header('Content-Type: application/json');
                echo json_encode($availableRooms);
                exit();
            }

            if (isset($_GET['action']) && $_GET['action'] == 'get_room_type_price_ajax') {
                error_log("DEBUG-BOOKING: AJAX get_room_type_price_ajax received.");
                if (isset($_GET['id_tipo_habitacion'])) {
                    $id_tipo_habitacion = $_GET['id_tipo_habitacion'];
                    $roomType = $this->roomTypeModel->getById($id_tipo_habitacion);
                    if ($roomType) {
                        header('Content-Type: application/json');
                        echo json_encode(['precio_base' => $roomType['precio_base']]);
                        error_log("DEBUG-BOOKING: Price for type " . $id_tipo_habitacion . ": " . $roomType['precio_base']);
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['error' => 'Tipo de habitación no encontrado']);
                        error_log("DEBUG-BOOKING: Room type " . $id_tipo_habitacion . " not found for price.");
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'ID de tipo de habitación no proporcionado']);
                    error_log("DEBUG-BOOKING: No room type ID provided for price AJAX.");
                }
                exit();
            }
        }

        $content_view = VIEW_PATH . 'bookings/create.php';
        extract([
            'roomTypes' => $roomTypes,
            'availableRooms' => $availableRooms,
            'error_message' => $error_message,
            'success_message' => $success_message,
            'guest_data' => $guest_data
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a booking or processes the update.
     * @param int $id_reserva
     */
    public function edit($id_reserva) {
        $title = "Editar Reserva";
        $booking = $this->bookingModel->getBookingById($id_reserva);
        $roomTypes = $this->roomTypeModel->getAll();
        $availableRooms = [];

        $error_message = '';
        $success_message = '';

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada.';
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if (!empty($booking['id_habitacion'])) {
            $room_info = $this->roomModel->getRoomById($booking['id_habitacion']);
            if ($room_info) {
                $booking['tipo_habitacion_precio_base'] = $room_info['precio_base'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("DEBUG-BOOKING-EDIT: POST request received for booking ID: " . $id_reserva);
            error_log("DEBUG-BOOKING-EDIT: POST data: " . print_r($_POST, true));

            $data = [
                'id_huesped' => $_POST['id_huesped'] ?? $booking['id_huesped'],
                'id_habitacion' => $_POST['id_habitacion'] ?? null,
                'fecha_entrada' => trim($_POST['fecha_entrada'] ?? ''),
                'fecha_salida' => trim($_POST['fecha_salida'] ?? ''),
                'adultos' => (int)($_POST['adultos'] ?? 1),
                'ninos' => (int)($_POST['ninos'] ?? 0),
                'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                'estado' => trim($_POST['estado'] ?? 'pendiente'),
                'comentarios' => trim($_POST['comentarios'] ?? '')
            ];
            error_log("DEBUG-BOOKING-EDIT: Data array for update: " . print_r($data, true));

            if (empty($data['fecha_entrada']) || empty($data['fecha_salida']) || $data['adultos'] <= 0) {
                $error_message = 'Las fechas y el número de adultos son obligatorios.';
                error_log("DEBUG-BOOKING-EDIT: Validation error - Dates or adults missing/invalid.");
            } elseif ($data['fecha_entrada'] >= $data['fecha_salida']) {
                $error_message = 'La fecha de salida debe ser posterior a la fecha de entrada.';
                error_log("DEBUG-BOOKING-EDIT: Validation error - Invalid date range.");
            } else {
                $calculated_price = 0;
                $num_nights = 0;
                try {
                    $diff = date_diff(date_create($data['fecha_entrada']), date_create($data['fecha_salida']));
                    $num_nights = $diff->days;
                } catch (Exception $e) {
                    error_log("DEBUG-BOOKING-EDIT: Error calculating date diff: " . $e->getMessage());
                    $error_message = 'Error en el cálculo de fechas.';
                }

                if (!$error_message && $num_nights > 0) {
                    if (!empty($data['id_habitacion'])) {
                        $room_info_for_price = $this->roomModel->getRoomById($data['id_habitacion']);
                        if ($room_info_for_price) {
                            $calculated_price = $room_info_for_price['precio_base'] * $num_nights;
                            error_log("DEBUG-BOOKING-EDIT: Price calculated from assigned room: " . $calculated_price);
                        }
                    } else {
                        $id_tipo_habitacion_deseado = $_POST['id_tipo_habitacion_deseado'] ?? null;
                        if ($id_tipo_habitacion_deseado) {
                             $type_info_for_price = $this->roomTypeModel->getById($id_tipo_habitacion_deseado);
                             if ($type_info_for_price) {
                                $calculated_price = $type_info_for_price['precio_base'] * $num_nights;
                                error_log("DEBUG-BOOKING-EDIT: Price calculated from desired room type: " . $calculated_price);
                             }
                        }
                    }
                } else if (!$error_message) {
                     error_log("DEBUG-BOOKING-EDIT: num_nights is 0 or less. Cannot calculate price.");
                }

                $data['precio_total'] = ($calculated_price > 0 && !$error_message) ? $calculated_price : ($data['precio_total'] > 0 ? $data['precio_total'] : 0.00);

                if ($data['precio_total'] <= 0) {
                    $error_message = 'El precio total de la reserva debe ser mayor a cero. Por favor, asigne una habitación o tipo de habitación válido.';
                    error_log("DEBUG-BOOKING-EDIT: Validation error - Price total is zero or less.");
                }

                $old_room_id = $booking['id_habitacion'];
                $new_room_id = $data['id_habitacion'];
                $old_status = $booking['estado'];
                $new_status = $data['estado'];

                if (!$error_message) {
                    try {
                        if ($this->bookingModel->updateBooking($id_reserva, $data)) {
                            $success_message = 'Reserva actualizada exitosamente.';
                            error_log("DEBUG-BOOKING-EDIT: Booking updated successfully in model.");

                            if ($old_room_id !== $new_room_id) {
                                error_log("DEBUG-BOOKING-EDIT: Room ID changed from " . $old_room_id . " to " . $new_room_id);
                                if (!empty($old_room_id)) {
                                    if ($old_status != 'check_in' && $old_status != 'check_out') {
                                        $this->roomModel->updateRoomStatus($old_room_id, 'disponible');
                                        error_log("DEBUG-BOOKING-EDIT: Old room " . $old_room_id . " status set to 'disponible'.");
                                    }
                                }
                                if (!empty($new_room_id)) {
                                    $this->roomModel->updateRoomStatus($new_room_id, 'ocupada');
                                    error_log("DEBUG-BOOKING-EDIT: New room " . $new_room_id . " status set to 'ocupada'.");
                                }
                            } else if ($old_status != $new_status) {
                                error_log("DEBUG-BOOKING-EDIT: Booking status changed from " . $old_status . " to " . $new_status);
                                if ($new_status == 'check_in' && !empty($new_room_id)) {
                                    $this->roomModel->updateRoomStatus($new_room_id, 'ocupada');
                                    error_log("DEBUG-BOOKING-EDIT: Room " . $new_room_id . " status set to 'ocupada' (check-in).");
                                } elseif ($new_status == 'check_out' && !empty($new_room_id)) {
                                    $this->roomModel->updateRoomStatus($new_room_id, 'sucia');
                                    error_log("DEBUG-BOOKING-EDIT: Room " . $new_room_id . " status set to 'sucia' (check-out).");
                                } elseif ($new_status == 'cancelada' && !empty($new_room_id)) {
                                    if ($old_status == 'check_in' || ($assigned_room = $this->roomModel->getRoomById($new_room_id)) && $assigned_room['estado'] == 'ocupada') {
                                        $this->roomModel->updateRoomStatus($new_room_id, 'disponible');
                                        error_log("DEBUG-BOOKING-EDIT: Room " . $new_room_id . " status set to 'disponible' (cancelled from occupied).");
                                    }
                                }
                            }

                            $booking = $this->bookingModel->getBookingById($id_reserva);
                            if (!empty($booking['id_habitacion'])) {
                                $room_info = $this->roomModel->getRoomById($booking['id_habitacion']);
                                if ($room_info) {
                                    $booking['tipo_habitacion_precio_base'] = $room_info['precio_base'];
                                }
                            }

                        } else {
                            $error_message = 'Error al actualizar la reserva en el modelo. El modelo devolvió false. Revise logs del modelo Booking.';
                            error_log("DEBUG-BOOKING-EDIT: Booking model returned false on update. Check Booking.php updateBooking method logs.");
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Error de base de datos al actualizar reserva: ' . $e->getMessage();
                        error_log("DEBUG-BOOKING-EDIT: PDOException on booking update: " . $e->getMessage());
                    }
                }
            }
        }

        if (!empty($booking['fecha_entrada']) && !empty($booking['fecha_salida'])) {
            $capacidad_huespedes = $booking['adultos'] + $booking['ninos'];

            $id_tipo_habitacion_preferido_para_busqueda = null;
            if (!empty($booking['id_habitacion'])) {
                $room_details_for_type = $this->roomModel->getRoomById($booking['id_habitacion']);
                if ($room_details_for_type && isset($room_details_for_type['id_tipo_habitacion'])) {
                    $id_tipo_habitacion_preferido_para_busqueda = $room_details_for_type['id_tipo_habitacion'];
                }
            }

            $availableRooms = $this->bookingModel->getAvailableRooms(
                $booking['fecha_entrada'],
                $booking['fecha_salida'],
                $capacidad_huespedes,
                $id_tipo_habitacion_preferido_para_busqueda
            );
            if ($booking['id_habitacion'] && !in_array($booking['id_habitacion'], array_column($availableRooms, 'id_habitacion'))) {
                $current_room_details = $this->roomModel->getRoomById($booking['id_habitacion']);
                if ($current_room_details) {
                    array_unshift($availableRooms, $current_room_details);
                }
            }
        }

        $content_view = VIEW_PATH . 'bookings/edit.php';
        extract([
            'booking' => $booking,
            'roomTypes' => $roomTypes,
            'availableRooms' => $availableRooms,
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null
        ]);
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        include VIEW_PATH . 'layouts/main_layout.php';
    }


    /**
     * Processes booking deletion.
     * @param int $id_reserva
     */
    public function delete($id_reserva) {
        error_log("DEBUG-BOOKING: Delete request for booking ID: " . $id_reserva);
        $booking = $this->bookingModel->getBookingById($id_reserva);

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada para eliminar.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not found for deletion.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        try {
            if ($this->bookingModel->deleteBooking($id_reserva)) {
                $_SESSION['success_message'] = 'Reserva eliminada exitosamente.';
                error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " deleted successfully.");
                if ($booking['estado'] === 'check_in' && !empty($booking['id_habitacion'])) {
                    $this->roomModel->updateRoomStatus($booking['id_habitacion'], 'disponible');
                    error_log("DEBUG-BOOKING: Room " . $booking['id_habitacion'] . " status updated to 'disponible' after booking deletion.");
                }
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la reserva. Podría tener dependencias (ej. pagos/facturas).';
                error_log("DEBUG-BOOKING: Booking model returned false on delete for ID " . $id_reserva . ".");
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = 'No se puede eliminar la reserva porque tiene pagos o facturas asociadas.';
                error_log("DEBUG-BOOKING: PDOException (23000) on booking delete: " . $e->getMessage());
            } else {
                $_SESSION['error_message'] = 'Error desconocido al eliminar el huésped: ' . $e->getMessage();
                error_log("DEBUG-BOOKING: PDOException (unknown) on booking delete: " . $e->getMessage());
            }
        }
        header('Location: /hotel_completo/public/bookings');
        exit();
    }


    /**
     * Processes booking check-in.
     * @param int $id_reserva
     */
    public function checkIn($id_reserva) {
        error_log("DEBUG-BOOKING: Check-in request for booking ID: " . $id_reserva);
        $booking = $this->bookingModel->getBookingById($id_reserva);

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada para Check-in.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not found for check-in.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if ($booking['estado'] === 'check_in') {
            $_SESSION['error_message'] = 'La reserva ya tiene Check-in.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " already checked in.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }
        if ($booking['estado'] === 'check_out') {
            $_SESSION['error_message'] = 'La reserva ya tiene Check-out.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " already checked out.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }
        if ($booking['estado'] === 'cancelada') {
            $_SESSION['error_message'] = 'La reserva ha sido cancelada.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " is cancelled.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if (empty($booking['id_habitacion'])) {
            $_SESSION['error_message'] = 'Para realizar el Check-in, la reserva debe tener una habitación asignada.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " has no room assigned for check-in.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        $assigned_room = $this->roomModel->getRoomById($booking['id_habitacion']);
        if ($assigned_room['estado'] === 'ocupada' && $booking['estado'] !== 'check_in') {
             $_SESSION['error_message'] = 'La habitación ' . $assigned_room['numero_habitacion'] . ' ya está ocupada por otra reserva. Por favor, asigne otra.';
             error_log("DEBUG-BOOKING: Room " . $assigned_room['numero_habitacion'] . " is already occupied by another booking.");
             header('Location: /hotel_completo/public/bookings/edit/' . $id_reserva);
             exit();
        }
        if ($assigned_room['estado'] === 'mantenimiento') {
            $_SESSION['error_message'] = 'La habitación ' . $assigned_room['numero_habitacion'] . ' está en mantenimiento y no puede ser ocupada.';
            error_log("DEBUG-BOOKING: Room " . $assigned_room['numero_habitacion'] . " is in maintenance.");
            header('Location: /hotel_completo/public/bookings/edit/' . $id_reserva);
            exit();
        }

        try {
            if ($this->bookingModel->updateBookingStatusAndRoom($id_reserva, 'check_in', $booking['id_habitacion'], 'ocupada')) {
                $_SESSION['success_message'] = 'Check-in realizado exitosamente para la reserva ' . $id_reserva . '.';
                error_log("DEBUG-BOOKING: Check-in successful for booking ID " . $id_reserva . ", room " . $booking['id_habitacion'] . " to 'ocupada'.");
            } else {
                $_SESSION['error_message'] = 'Error al realizar el Check-in.';
                error_log("DEBUG-BOOKING: Booking model returned false on check-in for ID " . $id_reserva . ".");
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error de base de datos al realizar Check-in: ' . $e->getMessage();
            error_log("DEBUG-BOOKING: PDOException on check-in: " . $e->getMessage());
        }
        header('Location: /hotel_completo/public/bookings');
        exit();
    }


    /**
     * Processes booking check-out.
     * @param int $id_reserva
     */
    public function checkOut($id_reserva) {
        error_log("DEBUG-BOOKING: Check-out request for booking ID: " . $id_reserva);
        $booking = $this->bookingModel->getBookingById($id_reserva);

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada para Check-out.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not found for check-out.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if ($booking['estado'] !== 'check_in') {
            $_SESSION['error_message'] = 'La reserva no está en estado "Check-in" para realizar el Check-out.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not in check_in state for check-out (current state: " . $booking['estado'] . ").");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        // --- ACCOUNT SUMMARY CALCULATION ---
        $accommodation_cost = $booking['precio_total']; 
        
        // MODIFICACIÓN CLAVE: Obtener TODOS los cargos pendientes para el huésped, NO FILTRAR POR RESERVA
        // Esto recuperará los cargos de venta directa y otros que no tienen id_reserva.
        $additional_charges_list = $this->guestModel->getPendingChargesForGuest($booking['id_huesped'], null);
        $additional_charges = 0;
        foreach ($additional_charges_list as $charge) {
            $additional_charges += $charge['monto'];
        }

        $total_due = $accommodation_cost + $additional_charges;

        // --- Display the Check-out summary screen ---
        $title = "Resumen de Check-out para Reserva #" . $id_reserva;
        $content_view = VIEW_PATH . 'bookings/checkout_summary.php';

        extract([
            'booking' => $booking,
            'accommodation_cost' => $accommodation_cost,
            'additional_charges' => $additional_charges,
            'total_due' => $total_due,
            'charges_list' => $additional_charges_list,
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null,
        ]);
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        include VIEW_PATH . 'layouts/main_layout.php';
        exit();
    }

    /**
     * Processes the final payment and completes the Check-out.
     * This method will be called from the checkout_summary.php form.
     * @param int $id_reserva
     */
    public function finalizeCheckout($id_reserva) {
        error_log("DEBUG-BOOKING: Finalize Checkout request for booking ID: " . $id_reserva);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        $monto_pagado = (float)($_POST['monto_pagado'] ?? 0.00);
        $metodo_pago = trim($_POST['metodo_pago'] ?? '');
        $tipo_documento = trim($_POST['tipo_documento_factura'] ?? 'Boleta');
        $serie_documento = trim($_POST['serie_documento'] ?? 'F001');
        $numero_documento = trim($_POST['numero_documento'] ?? '');

        $booking = $this->bookingModel->getBookingById($id_reserva);

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada para finalizar Check-out.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not found for finalize checkout.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if ($booking['estado'] !== 'check_in') {
            $_SESSION['error_message'] = 'La reserva no está en estado "Check-in" para finalizar el Check-out.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not in check_in state for finalize checkout.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        // ¡NUEVO! Validar que el monto pagado cubra al menos el total_due calculado
        $accommodation_cost = $booking['precio_total'];
        $additional_charges_list = $this->guestModel->getPendingChargesForGuest($booking['id_huesped'], null);
        $additional_charges = 0;
        foreach ($additional_charges_list as $charge) {
            $additional_charges += $charge['monto'];
        }
        $total_due_calculated = $accommodation_cost + $additional_charges;

        if ($monto_pagado < $total_due_calculated && $monto_pagado > 0) {
            $_SESSION['error_message'] = 'El monto pagado (S/ ' . number_format($monto_pagado, 2) . ') es menor al total adeudado (S/ ' . number_format($total_due_calculated, 2) . ').';
            header('Location: /hotel_completo/public/bookings/checkout_summary/' . $id_reserva);
            exit();
        }


        if ($monto_pagado <= 0 || empty($metodo_pago) || empty($numero_documento)) {
            $_SESSION['error_message'] = 'Por favor, ingrese un monto pagado válido, método de pago y número de documento para la factura.';
            header('Location: /hotel_completo/public/bookings/checkout_summary/' . $id_reserva);
            exit();
        }

        $transaction_started = false;

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $transaction_started = true;
                error_log("DEBUG-BOOKING: Transaction started successfully.");
            } else {
                error_log("DEBUG-BOOKING: Transaction was already active, skipping beginTransaction().");
            }


            // 1. Create the invoice
            $invoice_data = [
                'id_reserva' => $id_reserva,
                'id_huesped' => $booking['id_huesped'],
                'monto_total' => $monto_pagado,
                'impuestos' => 0.18 * $monto_pagado,
                'descuentos' => 0,
                'estado' => 'pagada',
                'tipo_documento' => $tipo_documento,
                'serie_documento' => $serie_documento,
                'numero_documento' => $numero_documento,
                'fecha_vencimiento' => null
            ];
            $id_factura = $this->invoiceModel->create($invoice_data);
            if (!$id_factura) {
                throw new Exception('Error al crear la factura.');
            }
            error_log("DEBUG-BOOKING: Invoice created with ID: " . $id_factura);

            // 2. Register the payment
            $payment_data = [
                'id_reserva' => $id_reserva,
                'id_factura' => $id_factura,
                'monto' => $monto_pagado,
                'metodo_pago' => $metodo_pago,
                'referencia_transaccion' => 'Pago por Reserva #' . $id_reserva . ' Doc: ' . $numero_documento,
            ];
            $id_pago = $this->paymentModel->create($payment_data);
            if (!$id_pago) {
                throw new Exception('Error al registrar el pago.');
            }
            error_log("DEBUG-BOOKING: Payment created with ID: " . $id_pago);

            // 3. Register Cash Transaction (Income) for the actual payment received
            $openRegister = $this->cashRegisterModel->getOpenRegister();
            if (!$openRegister) {
                throw new Exception('No hay un turno de caja abierto para registrar la transacción de pago. Abra la caja.');
            }
            $cash_transaction_data = [
                'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                'id_pago' => $id_pago,
                'id_factura' => $id_factura,
                'descripcion' => 'Pago de reserva #' . $id_reserva . ' - ' . $metodo_pago,
                'monto' => $monto_pagado,
                'tipo_transaccion' => 'ingreso',
                'metodo_pago' => $metodo_pago, // Pasa el método de pago a la transacción de caja
                'id_usuario' => $_SESSION['user_id'] ?? null
            ];
            $id_cash_transaction = $this->cashTransactionModel->create($cash_transaction_data);
            if (!$id_cash_transaction) {
                throw new Exception('Error al registrar la transacción de caja para el pago.');
            }
            error_log("DEBUG-BOOKING: Cash transaction created with ID: " . $id_cash_transaction);


            // 4. Update the Booking status and Room status
            if (!$this->bookingModel->updateBookingStatusAndRoom($id_reserva, 'check_out', $booking['id_habitacion'], 'sucia')) {
                throw new Exception('Error al finalizar el Check-out (actualizar estados).');
            }
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " and Room " . $booking['id_habitacion'] . " status updated to 'check_out' and 'sucia'.");

            // 5. Mark associated charges as 'pagado'
            $charge_ids_to_mark_paid = array_column($additional_charges_list, 'id_cargo');
            if (!empty($charge_ids_to_mark_paid)) {
                if (!$this->guestModel->updateGuestChargesStatus($charge_ids_to_mark_paid, 'pagado')) {
                    throw new Exception('Error al marcar los cargos adicionales del huésped como pagados.');
                }
                error_log("DEBUG-BOOKING: Guest charges " . implode(',', $charge_ids_to_mark_paid) . " marked as 'pagado'.");
            }


            if ($transaction_started) {
                $this->pdo->commit();
                error_log("DEBUG-BOOKING: Transaction committed.");
            } else {
                error_log("DEBUG-BOOKING: Transaction was already active, skipping commit().");
            }

            $_SESSION['success_message'] = 'Check-out finalizado exitosamente para la reserva ' . $id_reserva . '. Factura #' . $numero_documento . ' generada.';

        } catch (Exception $e) {
            if ($transaction_started && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                error_log("DEBUG-BOOKING: Transaction rolled back.");
            } else if ($transaction_started) {
                 error_log("DEBUG-BOOKING: Transaction started but no longer active. Skipping rollback().");
            } else {
                 error_log("DEBUG-BOOKING: Transaction was not started by this method. Skipping rollback().");
            }

            $_SESSION['error_message'] = 'Error al finalizar Check-out: ' . $e->getMessage();
            error_log("DEBUG-BOOKING: FinalizeCheckout failed: " . $e->getMessage());
        }

        header('Location: /hotel_completo/public/bookings');
        exit();
    }


    /**
     * Cancels a booking.
     * @param int $id_reserva
     */
    public function cancel($id_reserva) {
        error_log("DEBUG-BOOKING: Cancel request for booking ID: " . $id_reserva);
        $booking = $this->bookingModel->getBookingById($id_reserva);

        if (!$booking) {
            $_SESSION['error_message'] = 'Reserva no encontrada para cancelar.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " not found for cancel.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        if ($booking['estado'] === 'cancelada') {
            $_SESSION['error_message'] = 'La reserva ya está cancelada.';
            error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " already cancelled.");
            header('Location: /hotel_completo/public/bookings');
            exit();
        }

        $id_habitacion_to_free = ($booking['estado'] === 'check_in') ? $booking['id_habitacion'] : null;

        try {
            if ($this->bookingModel->updateBookingStatusAndRoom($id_reserva, 'cancelada', $id_habitacion_to_free, 'disponible')) {
                $_SESSION['success_message'] = 'Reserva cancelada exitosamente para la reserva ' . $id_reserva . '.';
                error_log("DEBUG-BOOKING: Booking ID " . $id_reserva . " cancelled successfully. Room " . $id_habitacion_to_free . " set to 'disponible'.");
            } else {
                $_SESSION['error_message'] = 'Error al cancelar la reserva.';
                error_log("DEBUG-BOOKING: Booking model returned false on cancel for ID " . $id_reserva . ".");
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error de base de datos al cancelar reserva: ' . $e->getMessage();
            error_log("DEBUG-BOOKING: PDOException on cancel: " . $e->getMessage());
        }
        header('Location: /hotel_completo/public/bookings');
        exit();
    }


    /**
     * AJAX endpoint to search guests.
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

    /**
     * AJAX endpoint to search for available rooms.
     */
    public function searchAvailableRoomsAjax() {
        error_log("DEBUG-BOOKING: AJAX search_available_rooms_ajax received.");
        $fecha_entrada = $_GET['fecha_entrada'] ?? null;
        $fecha_salida = $_GET['fecha_salida'] ?? null;
        $capacidad = $_GET['capacidad'] ?? 1;
        $id_tipo_habitacion = $_GET['id_tipo_habitacion'] ?? null;

        error_log("DEBUG-BOOKING: search_available_rooms_ajax params: Entrada=" . $fecha_entrada . ", Salida=" . $fecha_salida . ", Capacidad=" . $capacidad . ", TipoHab=" . $id_tipo_habitacion);

        if ($fecha_entrada && $fecha_salida && $fecha_entrada < $fecha_salida) {
            $availableRooms = $this->bookingModel->getAvailableRooms($fecha_entrada, $fecha_salida, $capacidad, $id_tipo_habitacion);
            error_log("DEBUG-BOOKING: Available rooms found: " . print_r($availableRooms, true));
        } else {
            $availableRooms = [];
            error_log("DEBUG-BOOKING: Invalid dates for search_available_rooms_ajax (Fecha Entrada: " . $fecha_entrada . ", Fecha Salida: " . $fecha_salida . ").");
        }
        header('Content-Type: application/json');
        echo json_encode($availableRooms);
        exit();
    }

    /**
     * AJAX endpoint to get room type base price.
     */
    public function getRoomTypePriceAjax() {
        error_log("DEBUG-BOOKING: AJAX get_room_type_price_ajax received.");
        if (isset($_GET['id_tipo_habitacion'])) {
            $id_tipo_habitacion = $_GET['id_tipo_habitacion'];
            $roomType = $this->roomTypeModel->getById($id_tipo_habitacion);
            if ($roomType) {
                header('Content-Type: application/json');
                echo json_encode(['precio_base' => $roomType['precio_base']]);
                error_log("DEBUG-BOOKING: Price for type " . $id_tipo_habitacion . ": " . $roomType['precio_base']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Tipo de habitación no encontrado']);
                error_log("DEBUG-BOOKING: Room type " . $id_tipo_habitacion . " not found for price.");
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de tipo de habitación no proporcionado']);
            error_log("DEBUG-BOOKING: No room type ID provided for price AJAX.");
        }
        exit();
    }
}