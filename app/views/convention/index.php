<h1 class="mb-4">Gestión de Eventos y Convenciones</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/convention/create" class="btn btn-primary"><i class="fas fa-plus"></i> Crear Nueva Reserva de Evento</a>
</div>

<!-- Formulario de Filtro y Búsqueda -->
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Buscar y Filtrar Reservas de Eventos</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/convention" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search_query" class="form-label">Buscar por Contacto/Evento</label>
                <input type="text" class="form-control" id="search_query" name="search_query" placeholder="Nombre Contacto, Nombre Evento" value="<?php echo htmlspecialchars($current_search_query ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label for="status_filter" class="form-label">Filtrar por Estado</label>
                <select class="form-select" id="status_filter" name="status_filter[]" multiple size="3">
                    <?php foreach ($all_booking_statuses as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>" 
                            <?php echo in_array($value, $current_status_filter ?? []) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Mantén Ctrl/Cmd para seleccionar múltiples.</small>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Fecha Evento Desde</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($current_start_date ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Fecha Evento Hasta</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($current_end_date ?? ''); ?>">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <div class="form-check form-switch mt-auto">
                    <input class="form-check-input" type="checkbox" id="show_all" name="show_all" <?php echo ($current_show_all ?? false) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="show_all">Todas</label>
                </div>
            </div>
            <div class="col-md-auto mt-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                <a href="/hotel_completo/public/convention" class="btn btn-secondary"><i class="fas fa-redo"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Reservas de Eventos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="conventionBookingsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sala</th>
                        <th>Evento</th>
                        <th>Contacto</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Asistentes</th>
                        <th>Precio (S/)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['id_reserva_convencion']); ?></td>
                                <td><?php echo htmlspecialchars($booking['nombre_sala'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($booking['nombre_evento']); ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($booking['nombre_contacto']);
                                    if (!empty($booking['huesped_nombre'])) {
                                        echo '<br><small>(Huésped: ' . htmlspecialchars($booking['huesped_nombre'] . ' ' . $booking['huesped_apellido']) . ')</small>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($booking['fecha_evento']))); ?></td>
                                <td><?php echo htmlspecialchars(substr($booking['hora_inicio'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($booking['hora_fin'], 0, 5)); ?></td>
                                <td><?php echo htmlspecialchars($booking['num_asistentes'] ?? 'N/A'); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($booking['precio_total']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($booking['estado']) {
                                        case 'pendiente':   $badgeClass = 'bg-info'; break;
                                        case 'confirmada':  $badgeClass = 'bg-warning text-dark'; break;
                                        case 'realizada':   $badgeClass = 'bg-success'; break;
                                        case 'cancelada':   $badgeClass = 'bg-danger'; break;
                                        default:            $badgeClass = 'bg-primary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['estado']))); ?></span>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/convention/edit/<?php echo htmlspecialchars($booking['id_reserva_convencion']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/convention/delete/<?php echo htmlspecialchars($booking['id_reserva_convencion']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                    <?php if ($booking['estado'] === 'pendiente' || $booking['estado'] === 'realizada'): ?>
                                    <a href="/hotel_completo/public/convention/update_status/<?php echo htmlspecialchars($booking['id_reserva_convencion']); ?>/confirmada" class="btn btn-sm btn-success" title="Confirmar"><i class="fas fa-check-circle"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No hay reservas de eventos que coincidan con los criterios de búsqueda/filtro.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta reserva de evento? Esta acción es irreversible y podría dejar inconsistencias si ya hubo pagos o servicios asociados.')) {
                e.preventDefault();
            }
        });
    });

    // Mantener la selección múltiple del filtro de estado
    const statusFilterSelect = document.getElementById('status_filter');
    const currentStatusFilter = <?php echo json_encode($current_status_filter ?? []); ?>;
    Array.from(statusFilterSelect.options).forEach(option => {
        if (currentStatusFilter.includes(option.value)) {
            option.selected = true;
        } else {
            option.selected = false;
        }
    });
});
</script>
