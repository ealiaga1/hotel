<h1 class="mb-4">Calendario de Reservas</h1>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <a href="/hotel_completo/public/calendar/<?php echo htmlspecialchars($prevMonthYear); ?>/<?php echo htmlspecialchars($prevMonthNum); ?>" class="btn btn-secondary"><i class="fas fa-chevron-left"></i> Anterior</a>
        <h6 class="m-0 font-weight-bold text-primary fs-4"><?php echo (new DateTime("$currentYear-$currentMonth-01"))->format('F Y'); ?></h6>
        <a href="/hotel_completo/public/calendar/<?php echo htmlspecialchars($nextMonthYear); ?>/<?php echo htmlspecialchars($nextMonthNum); ?>" class="btn btn-secondary">Siguiente <i class="fas fa-chevron-right"></i></a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-fixed-header" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 150px;" class="text-center"><i class="fas fa-bed me-2"></i>Habitación</th>
                        <?php
                        $daysInMonth = (int)$firstDayOfMonth->format('t');
                        for ($i = 1; $i <= $daysInMonth; $i++) {
                            echo '<th class="text-center" style="width: 40px;">' . $i . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td class="sticky-column"><strong><?php echo htmlspecialchars($room['numero_habitacion']); ?></strong><br><small><?php echo htmlspecialchars($room['nombre_tipo']); ?></small></td>
                                <?php
                                for ($i = 1; $i <= $daysInMonth; $i++) {
                                    $currentDay = new DateTime($firstDayOfMonth->format('Y-m-' . str_pad($i, 2, '0', STR_PAD_LEFT)));
                                    $dayKey = $currentDay->format('Y-m-d');
                                    $cellClass = 'calendar-cell';
                                    $cellContent = '';
                                    
                                    // Comprobar si hay reservas para esta habitación en este día
                                    if (isset($bookingsByDayAndRoom[$dayKey][$room['numero_habitacion']])) {
                                        $bookingsOnThisDay = $bookingsByDayAndRoom[$dayKey][$room['numero_habitacion']];
                                        
                                        foreach ($bookingsOnThisDay as $booking) {
                                            $segmentClass = 'booking-segment';
                                            $displayText = '';
                                            $tooltipTitle = 'Reserva #' . htmlspecialchars($booking['id_reserva']) . ' | Huésped: ' . htmlspecialchars($booking['huesped_nombre'] . ' ' . $booking['huesped_apellido']) . ' | Estado: ' . htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['estado'])));

                                            // Definir clase de color basada en el estado
                                            switch ($booking['estado']) {
                                                case 'confirmada':  $segmentClass .= ' booking-status-confirmada'; break;
                                                case 'check_in':    $segmentClass .= ' booking-status-check_in'; break;
                                                case 'pendiente':   $segmentClass .= ' booking-status-pendiente'; break;
                                                case 'check_out':   $segmentClass .= ' booking-status-check_out'; break;
                                                case 'cancelada':   $segmentClass .= ' booking-status-cancelada'; break;
                                                default:            $segmentClass .= ' booking-status-default'; break;
                                            }

                                            // Información compacta para mostrar en la celda
                                            $guestInitials = '';
                                            if (!empty($booking['huesped_nombre'])) {
                                                $guestInitials .= strtoupper(substr($booking['huesped_nombre'], 0, 1));
                                            }
                                            if (!empty($booking['huesped_apellido'])) {
                                                $guestInitials .= strtoupper(substr($booking['huesped_apellido'], 0, 1));
                                            }
                                            
                                            // Si la celda es muy pequeña, solo mostrar ID o iniciales
                                            $displayText = 'ID#' . htmlspecialchars($booking['id_reserva']);
                                            if (!empty($guestInitials)) {
                                                $displayText .= ' (' . $guestInitials . ')';
                                            }
                                            
                                            // Agregar un enlace a la reserva para ver/editar detalles
                                            $cellContent .= '<a href="/hotel_completo/public/bookings/edit/' . htmlspecialchars($booking['id_reserva']) . '" class="' . htmlspecialchars($segmentClass) . '" title="' . htmlspecialchars($tooltipTitle) . '">' . htmlspecialchars($displayText) . '</a>';
                                        }
                                    } else {
                                        // Si no hay reserva, la celda está vacía
                                        $cellContent = '';
                                    }
                                    echo '<td class="' . htmlspecialchars($cellClass) . '">' . $cellContent . '</td>';
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $daysInMonth + 1; ?>" class="text-center">No hay habitaciones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Estilos para el calendario */
.table-fixed-header thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa; /* Color de fondo para el encabezado fijo */
    z-index: 2;
    border-bottom: 2px solid #dee2e6;
    vertical-align: middle; /* Centrar verticalmente el contenido del th */
}
.table-fixed-header .sticky-column {
    position: sticky;
    left: 0;
    background: #f8f9fa; /* Color de fondo para la columna fija */
    z-index: 3; /* Más alto que el thead para que siempre esté al frente */
    border-right: 2px solid #dee2e6;
    vertical-align: middle;
}
.table-fixed-header tbody td {
    vertical-align: top; /* Alinear el contenido al top para que el texto se vea bien */
    height: 40px; /* Altura fija para las celdas de los días */
    padding: 2px; /* Reducir padding */
    overflow: hidden; /* Ocultar contenido extra */
    white-space: normal; /* Permitir salto de línea si es necesario */
    word-wrap: break-word; /* Romper palabras largas */
}
.calendar-cell {
    position: relative;
    overflow: hidden;
    background-color: #f0f0f0; /* Color de fondo base para celdas de día */
}
/* Estilo para los segmentos de reserva dentro de la celda */
.calendar-cell .booking-segment {
    display: block;
    height: 100%; /* Ocupa toda la altura de la celda */
    width: 100%;
    text-align: center;
    line-height: 1; /* Reducir line-height para más texto */
    font-size: 7pt; /* Tamaño de letra pequeño para que quepa */
    color: #fff;
    font-weight: bold;
    padding-top: 5px; /* Pequeño padding superior */
    text-overflow: ellipsis; /* Añadir puntos suspensivos si el texto es muy largo */
    white-space: nowrap; /* Evitar que el texto salte de línea dentro del span */
    overflow: hidden; /* Ocultar el texto que excede */
    border-radius: 3px; /* Bordes ligeramente redondeados */
}
/* Colores específicos para estados de reserva */
.booking-status-confirmada { background-color: #007bff; }    /* Azul para confirmada (color del sistema) */
.booking-status-check_in { background-color: #dc3545; }    /* Rojo para check-in (ocupada) */
.booking-status-pendiente { background-color: #6aabff; }   /* Azul claro para pendiente */
.booking-status-check_out { background-color: #6c757d; }   /* Gris para check-out */
.booking-status-cancelada { background-color: #e9ecef; color: #aaa; border: 1px dashed #ccc; } /* Muy claro con borde para cancelada */
.booking-status-default { background-color: #28a745; } /* Verde por defecto si no coincide */


/* Tooltip básico para las reservas */
.booking-segment[title]:hover::after {
    content: attr(title);
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 8pt;
    white-space: nowrap;
    z-index: 10;
    bottom: 100%; /* Arriba del elemento */
    left: 50%;
    transform: translateX(-50%);
    margin-bottom: 5px; /* Espacio entre el tooltip y el elemento */
}

/* Opcional: para scroll horizontal si hay muchos días */
.table-responsive {
    overflow-x: auto;
}
</style>
