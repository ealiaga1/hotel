<?php
// hotel_completo/app/controllers/PoolController.php

// Required models
require_once __DIR__ . '/../models/PoolReservation.php';
require_once __DIR__ . '/../models/Guest.php';
require_once __DIR__ . '/../models/CashRegister.php';
require_once __DIR__ . '/../models/CashTransaction.php';


class PoolController {
    private $poolReservationModel;
    private $guestModel;
    private $cashRegisterModel;
    private $cashTransactionModel;
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->poolReservationModel = new PoolReservation($this->pdo);
        $this->guestModel = new Guest($this->pdo);
        $this->cashRegisterModel = new CashRegister($this->pdo);
        $this->cashTransactionModel = new CashTransaction($this->pdo);
    }

    /**
     * Displays the list of all pool reservations, with filters and search.
     */
    public function index() {
        $title = "Gestión de Reservas de Piscina";
        
        $success_message = $_SESSION['success_message'] ?? null;
        $error_message = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);

        $filters = [];
        // Obtener los parámetros de filtro de $_GET
        $search_query = trim($_GET['search_query'] ?? '');
        $status_filter = $_GET['status_filter'] ?? []; // Puede ser un array si es selección múltiple
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $show_all = isset($_GET['show_all']); // Si el checkbox "Mostrar todas" está marcado

        // Lógica de filtrado:
        // Si no hay filtros explícitos ni búsqueda, mostrar solo activas/pendientes por defecto
        if (empty($search_query) && empty($status_filter) && empty($start_date) && empty($end_date) && !$show_all) {
            $filters['status'] = ['confirmada', 'pendiente'];
        } else {
            // Aplicar filtros si se proporcionaron en la URL o se activó "mostrar_todas"
            if (!empty($search_query)) {
                $filters['client_name'] = $search_query;
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
        
        $reservations = $this->poolReservationModel->searchReservations($filters);

        // Definir todos los estados posibles para el dropdown de filtro
        $all_pool_statuses = [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada'
        ];

        $content_view = VIEW_PATH . 'pool/index.php';
        extract([
            'reservations' => $reservations,
            'success_message' => $success_message,
            'error_message' => $error_message,
            'all_pool_statuses' => $all_pool_statuses, // Pasar todos los estados para el filtro
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
     * Displays the form to create a new pool reservation or processes its creation.
     */
    public function create() {
        $title = "Crear Nueva Reserva de Piscina";
        $error_message = '';
        $success_message = '';

        $guests = $this->guestModel->getAll();
        $openRegister = $this->cashRegisterModel->getOpenRegister();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_guest_from_list = isset($_POST['id_huesped_existente']) && !empty($_POST['id_huesped_existente']);
            $id_huesped = $is_guest_from_list ? $_POST['id_huesped_existente'] : null;

            $data = [
                'id_huesped' => $id_huesped,
                'nombre_cliente' => $is_guest_from_list ? null : trim($_POST['nombre_cliente'] ?? ''),
                'telefono_cliente' => $is_guest_from_list ? null : trim($_POST['telefono_cliente'] ?? ''),
                'fecha_reserva' => trim($_POST['fecha_reserva'] ?? ''),
                'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
                'hora_fin' => trim($_POST['hora_fin'] ?? ''),
                'cantidad_personas' => (int)($_POST['cantidad_personas'] ?? 1),
                'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                'estado' => trim($_POST['estado'] ?? 'confirmada'),
                'payment_type' => trim($_POST['payment_type'] ?? 'immediate'),
                'payment_method' => trim($_POST['payment_method'] ?? null)
            ];

            if (empty($data['fecha_reserva']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || $data['cantidad_personas'] <= 0 || $data['precio_total'] <= 0) {
                $error_message = 'Todos los campos obligatorios (Fecha, Horas, Cantidad, Precio) deben ser llenados.';
            } elseif ($data['hora_inicio'] >= $data['hora_fin']) {
                $error_message = 'La hora de fin debe ser posterior a la hora de inicio.';
            } elseif (!$is_guest_from_list && (empty($data['nombre_cliente']) || empty($data['telefono_cliente']))) {
                 $error_message = 'Para clientes externos, Nombre y Teléfono son obligatorios.';
            } elseif ($data['payment_type'] === 'immediate' && empty($data['payment_method'])) {
                $error_message = 'Debe seleccionar un método de pago para reservas con pago inmediato.';
            } elseif ($data['payment_type'] === 'charge_to_room' && empty($id_huesped)) {
                $error_message = 'Debe seleccionar un huésped para cargar a la habitación.';
            } else {
                $overlapping = $this->poolReservationModel->countOverlappingReservations(
                    $data['fecha_reserva'],
                    $data['hora_inicio'],
                    $data['hora_fin']
                );
                if ($overlapping > 0) {
                    $error_message = 'Ya existe una reserva de piscina que se solapa con el horario seleccionado. Por favor, elija otro horario.';
                } else {
                    $transaction_started_here = false;

                    try {
                        $is_charge_transaction_needed = (
                            ($data['estado'] === 'completada' && ($reservation['estado'] ?? '') !== 'completada' && $data['precio_total'] > 0) ||
                            ($data['payment_type'] === 'immediate' && $data['precio_total'] > 0 && $data['estado'] !== 'completada') // Considerar si no es completada pero es pago inmediato
                        );
                        
                        if ($is_charge_transaction_needed) {
                             if (!$openRegister) {
                                throw new Exception('No hay un turno de caja abierto para registrar el pago o cargo de esta reserva de piscina. Abra la caja.');
                            }
                            if (!$this->pdo->inTransaction()) {
                                $this->pdo->beginTransaction();
                                $transaction_started_here = true;
                            }
                        }
                        
                        $id_reserva_piscina = $this->poolReservationModel->create($data);
                        if ($id_reserva_piscina) {
                            if ($data['precio_total'] > 0 && ($data['payment_type'] === 'immediate' || $data['payment_type'] === 'charge_to_room')) {
                                $transaction_description = 'Reserva Piscina #' . $id_reserva_piscina;
                                if (!empty($id_huesped)) {
                                    $guest_info = $this->guestModel->getById($id_huesped);
                                    $transaction_description .= ' (Huésped: ' . ($guest_info['nombre'] ?? '') . ' ' . ($guest_info['apellido'] ?? '') . ')';
                                } elseif (!empty($data['nombre_cliente'])) {
                                    $transaction_description .= ' (Cliente Externo: ' . $data['nombre_cliente'] . ')';
                                }

                                if ($data['payment_type'] === 'immediate') {
                                    $cash_transaction_data = [
                                        'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                                        'descripcion' => 'Pago ' . $transaction_description . ' - ' . $data['payment_method'],
                                        'monto' => $data['precio_total'],
                                        'tipo_transaccion' => 'ingreso',
                                        'metodo_pago' => $data['payment_method'],
                                        'id_usuario' => $_SESSION['user_id'] ?? null
                                    ];
                                    $id_cash_transaction = $this->cashTransactionModel->create($cash_transaction_data);
                                    if (!$id_cash_transaction) {
                                        throw new Exception('Error al registrar la transacción de caja para la reserva de piscina (pago inmediato).');
                                    }
                                } elseif ($data['payment_type'] === 'charge_to_room') {
                                    // Los cargos a habitación son pendientes hasta el checkout
                                    $guest_charge_data = [
                                        'id_huesped' => $id_huesped,
                                        'id_reserva' => null,
                                        'descripcion' => 'Cargo por Reserva de Piscina #' . $id_reserva_piscina,
                                        'monto' => $data['precio_total'],
                                        'estado' => 'pendiente',
                                        'id_usuario_registro' => $_SESSION['user_id'] ?? null
                                    ];
                                    $id_guest_charge = $this->guestModel->addGuestCharge($guest_charge_data);
                                    if (!$id_guest_charge) {
                                        throw new Exception('Error al registrar el cargo a la habitación del huésped por reserva de piscina.');
                                    }
                                    // No se genera transacción de caja directa aquí, se hace al consolidar en checkout de huésped
                                }
                            }
                            
                            if ($transaction_started_here) {
                                $this->pdo->commit();
                            }
                            $_SESSION['success_message'] = 'Reserva de piscina creada exitosamente con ID: ' . $id_reserva_piscina . '.';
                            header('Location: /hotel_completo/public/pool');
                            exit();
                        } else {
                            if ($transaction_started_here) {
                                $this->pdo->rollBack();
                            }
                            $error_message = 'Error al crear la reserva de piscina.';
                        }
                    } catch (Exception $e) {
                        if ($transaction_started_here && $this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $error_message = 'Error de base de datos al crear reserva de piscina: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'pool/create.php';
        extract([
            'openRegister' => $openRegister,
            'guests' => $guests,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Displays the form to edit a pool reservation or processes its update.
     * @param int $id_reserva_piscina
     */
    public function edit($id_reserva_piscina) {
        $title = "Editar Reserva de Piscina";
        $reservation = $this->poolReservationModel->getById($id_reserva_piscina);
        $error_message = '';
        $success_message = '';

        $guests = $this->guestModel->getAll();
        $openRegister = $this->cashRegisterModel->getOpenRegister();

        if (!$reservation) {
            $_SESSION['error_message'] = 'Reserva de piscina no encontrada.';
            header('Location: /hotel_completo/public/pool');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $is_guest_from_list = isset($_POST['id_huesped_existente']) && !empty($_POST['id_huesped_existente']);
            $id_huesped = $is_guest_from_list ? $_POST['id_huesped_existente'] : null;

            $data = [
                'id_huesped' => $id_huesped,
                'nombre_cliente' => $is_guest_from_list ? null : trim($_POST['nombre_cliente'] ?? ''),
                'telefono_cliente' => $is_guest_from_list ? null : trim($_POST['telefono_cliente'] ?? ''),
                'fecha_reserva' => trim($_POST['fecha_reserva'] ?? ''),
                'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
                'hora_fin' => trim($_POST['hora_fin'] ?? ''),
                'cantidad_personas' => (int)($_POST['cantidad_personas'] ?? 1),
                'precio_total' => (float)($_POST['precio_total'] ?? 0.00),
                'estado' => trim($_POST['estado'] ?? 'confirmada'),
                'payment_type' => trim($_POST['payment_type'] ?? 'immediate'),
                'payment_method' => trim($_POST['payment_method'] ?? null)
            ];

            $old_status = $reservation['estado'];
            $new_status = $data['estado'];
            $old_precio_total = $reservation['precio_total'];

            if (empty($data['fecha_reserva']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || $data['cantidad_personas'] <= 0 || $data['precio_total'] <= 0) {
                $error_message = 'Todos los campos obligatorios (Fecha, Horas, Cantidad, Precio) deben ser llenados.';
            } elseif ($data['hora_inicio'] >= $data['hora_fin']) {
                $error_message = 'La hora de fin debe ser posterior a la hora de inicio.';
            } elseif (!$is_guest_from_list && (empty($data['nombre_cliente']) || empty($data['telefono_cliente']))) {
                 $error_message = 'Para clientes externos, Nombre y Teléfono son obligatorios.';
            } elseif ($data['payment_type'] === 'immediate' && empty($data['payment_method'])) {
                $error_message = 'Debe seleccionar un método de pago para reservas con pago inmediato.';
            } elseif ($data['payment_type'] === 'charge_to_room' && empty($id_huesped)) {
                $error_message = 'Debe seleccionar un huésped para cargar a la habitación.';
            } else {
                $overlapping = $this->poolReservationModel->countOverlappingReservations(
                    $data['fecha_reserva'],
                    $data['hora_inicio'],
                    $data['hora_fin'],
                    $id_reserva_piscina
                );
                if ($overlapping > 0) {
                    $error_message = 'Ya existe una reserva de piscina que se solapa con el horario seleccionado.';
                } else {
                    $transaction_started_here = false;

                    try {
                        $is_charge_transaction_needed = (
                            ($new_status === 'completada' && $old_status !== 'completada' && $data['precio_total'] > 0) ||
                            ($data['payment_type'] === 'immediate' && $data['precio_total'] > 0 && $new_status !== 'completada' && $old_precio_total != $data['precio_total']) // Si cambia precio y no es completada
                        );

                        if ($is_charge_transaction_needed) {
                             if (!$openRegister) {
                                throw new Exception('No hay un turno de caja abierto para registrar el pago o cargo de esta reserva de piscina. Abra la caja.');
                            }
                            if (!$this->pdo->inTransaction()) {
                                $this->pdo->beginTransaction();
                                $transaction_started_here = true;
                            }
                        }
                        
                        if ($this->poolReservationModel->update($id_reserva_piscina, $data)) {
                            $_SESSION['success_message'] = 'Reserva de piscina actualizada exitosamente.';
                            
                            if ($is_charge_transaction_needed) {
                                $transaction_description = 'Reserva Piscina #' . $id_reserva_piscina;
                                if (!empty($data['id_huesped'])) {
                                    $guest_info = $this->guestModel->getById($data['id_huesped']);
                                    $transaction_description .= ' (Huésped: ' . ($guest_info['nombre'] ?? '') . ' ' . ($guest_info['apellido'] ?? '') . ')';
                                } elseif (!empty($data['nombre_cliente'])) {
                                    $transaction_description .= ' (Cliente Externo: ' . $data['nombre_cliente'] . ')';
                                }

                                if ($data['payment_type'] === 'immediate') {
                                    $cash_transaction_data = [
                                        'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                                        'descripcion' => 'Pago ' . $transaction_description . ' - ' . $data['payment_method'],
                                        'monto' => $data['precio_total'],
                                        'tipo_transaccion' => 'ingreso',
                                        'metodo_pago' => $data['payment_method'],
                                        'id_usuario' => $_SESSION['user_id'] ?? null
                                    ];
                                    $id_cash_transaction = $this->cashTransactionModel->create($cash_transaction_data);
                                    if (!$id_cash_transaction) {
                                        throw new Exception('Error al registrar la transacción de caja para la reserva de piscina (pago inmediato).');
                                    }
                                } elseif ($data['payment_type'] === 'charge_to_room') {
                                    // Si el estado pasa a completada y es cargo a habitación, marcar el cargo como pagado.
                                    // Si cambia el precio, crear un nuevo cargo o ajustar el existente (más complejo, por ahora crearemos nuevo si no hay previo)
                                    $existing_charges = $this->guestModel->getPendingChargesForGuest($data['id_huesped'], null);
                                    $charge_to_update_id = null;
                                    foreach ($existing_charges as $charge) {
                                        if (strpos($charge['descripcion'], 'Reserva de Piscina #' . $id_reserva_piscina) !== false) {
                                            $charge_to_update_id = $charge['id_cargo'];
                                            break;
                                        }
                                    }

                                    if ($charge_to_update_id) {
                                        // Actualizar cargo existente
                                        if (!$this->guestModel->updateGuestChargesAmount($charge_to_update_id, $data['precio_total'])) {
                                            throw new Exception('Error al actualizar el monto del cargo existente por reserva de piscina.');
                                        }
                                    } else {
                                        // Crear nuevo cargo si no existía uno específico de piscina para esta reserva
                                        $guest_charge_data = [
                                            'id_huesped' => $id_huesped,
                                            'id_reserva' => null,
                                            'descripcion' => 'Cargo por Reserva de Piscina #' . $id_reserva_piscina,
                                            'monto' => $data['precio_total'],
                                            'estado' => 'pendiente',
                                            'id_usuario_registro' => $_SESSION['user_id'] ?? null
                                        ];
                                        if (!$this->guestModel->addGuestCharge($guest_charge_data)) {
                                            throw new Exception('Error al registrar nuevo cargo a la habitación del huésped por reserva de piscina.');
                                        }
                                    }
                                    // Si el pago es un cargo a habitación y la reserva se marca como completada, se registra en caja
                                    if ($new_status === 'completada' && $old_status !== 'completada') {
                                         $cash_transaction_data = [
                                            'id_movimiento_caja' => $openRegister['id_movimiento_caja'],
                                            'descripcion' => 'Pago Consolidado Reserva Piscina #' . $id_reserva_piscina . ' (Cargo a Habitación)',
                                            'monto' => $data['precio_total'],
                                            'tipo_transaccion' => 'ingreso',
                                            'metodo_pago' => 'Cargo a Habitación (Pagado)', // Método de pago del cargo
                                            'id_usuario' => $_SESSION['user_id'] ?? null
                                        ];
                                        if (!$this->cashTransactionModel->create($cash_transaction_data)) {
                                            throw new Exception('Error al registrar transacción de caja para cargo a habitación (Piscina).');
                                        }
                                    }
                                }
                            }
                            
                            if ($transaction_started_here) {
                                $this->pdo->commit();
                            }
                            $reservation = $this->poolReservationModel->getById($id_reserva_piscina);
                        } else {
                            if ($transaction_started_here) {
                                $this->pdo->rollBack();
                            }
                            $error_message = 'Error al actualizar la reserva de piscina.';
                        }
                    } catch (Exception $e) {
                        if ($transaction_started_here && $this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $error_message = 'Error de base de datos al actualizar reserva de piscina: ' . $e->getMessage();
                    }
                }
            }
        }

        $content_view = VIEW_PATH . 'pool/edit.php';
        extract([
            'reservation' => $reservation,
            'openRegister' => $openRegister,
            'guests' => $guests,
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        include VIEW_PATH . 'layouts/main_layout.php';
    }

    /**
     * Processes the deletion of a pool reservation.
     * @param int $id_reserva_piscina
     */
    public function delete($id_reserva_piscina) {
        try {
            if ($this->poolReservationModel->delete($id_reserva_piscina)) {
                $_SESSION['success_message'] = 'Reserva de piscina eliminada exitosamente.';
            } else {
                $_SESSION['error_message'] = 'Error al eliminar la reserva de piscina.';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error de base de datos al eliminar reserva de piscina: ' . $e->getMessage();
        }
        header('Location: /hotel_completo/public/pool');
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