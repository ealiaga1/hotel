<h1 class="mb-4">Dashboard General</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Resumen General del Hotel -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-primary shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Habitaciones Disponibles
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($roomsAvailable); ?> / <?php echo htmlspecialchars($roomsTotal); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bed fa-2x text-white-50"></i>
                    </div>
                </div>
                <small class="text-white-50">Ocupadas: <?php echo htmlspecialchars($roomsOccupied); ?>, Mantenimiento: <?php echo htmlspecialchars($roomsMaintenance); ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-success shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Reservas Pendientes (Hoy+)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($pendingBookingsCount); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-white-50"></i>
                    </div>
                </div>
                <small class="text-white-50">Huéspedes en Hotel: <?php echo htmlspecialchars($currentGuestsInHouse); ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-info shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Estado de Caja
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-white">S/ <?php echo number_format(htmlspecialchars($cashBalance), 2, '.', ','); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wallet fa-2x text-white-50"></i>
                    </div>
                </div>
                <small class="text-white-50">Estado: <?php echo htmlspecialchars($cashRegisterStatus); ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card text-white bg-warning shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Inventario (Stock Bajo)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($lowStockProducts); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-white-50"></i>
                    </div>
                </div>
                <small class="text-white-50">Total Productos: <?php echo htmlspecialchars($totalInventoryProducts); ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Módulos Específicos -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-concierge-bell me-2"></i> Restaurante</h6>
            </div>
            <div class="card-body">
                <p><strong>Pedidos Pendientes:</strong> <?php echo htmlspecialchars($pendingRestaurantOrders); ?></p>
                <p><strong>Mesas Ocupadas:</strong> <?php echo htmlspecialchars($occupiedTables); ?></p>
                <a href="/hotel_completo/public/restaurant/orders" class="btn btn-sm btn-outline-primary">Ver Pedidos</a>
                <a href="/hotel_completo/public/restaurant/tables" class="btn btn-sm btn-outline-info">Ver Mesas</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-swimming-pool me-2"></i> Piscina</h6>
            </div>
            <div class="card-body">
                <p><strong>Reservas Confirmadas Hoy:</strong> <?php echo htmlspecialchars($todayPoolReservations); ?></p>
                <p><strong>Total Huéspedes Registrados:</strong> <?php echo htmlspecialchars($totalGuests); ?></p>
                <a href="/hotel_completo/public/pool" class="btn btn-sm btn-outline-primary">Ver Reservas Piscina</a>
                <a href="/hotel_completo/public/guests" class="btn btn-sm btn-outline-info">Gestionar Huéspedes</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog me-2"></i> Personal</h6>
            </div>
            <div class="card-body">
                <p><strong>Total Empleados Activos:</strong> <?php echo htmlspecialchars($totalStaff); ?></p>
                <p class="text-muted">Gestión de usuarios y roles del sistema.</p>
                <a href="/hotel_completo/public/users" class="btn btn-sm btn-outline-primary">Gestionar Personal</a>
            </div>
        </div>
    </div>
</div>


<!-- Últimas 5 Reservas -->
<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Últimas Reservas Registradas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="latestBookingsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Reserva</th>
                        <th>Huésped</th>
                        <th>Habitación</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($latestBookings)): ?>
                        <?php foreach ($latestBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['id_reserva']); ?></td>
                                <td><?php echo htmlspecialchars($booking['huesped_nombre'] . ' ' . $booking['huesped_apellido']); ?></td>
                                <td><?php echo htmlspecialchars($booking['numero_habitacion']); ?></td>
                                <td><?php echo htmlspecialchars($booking['fecha_entrada']); ?></td>
                                <td><?php echo htmlspecialchars($booking['fecha_salida']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($booking['estado']) {
                                        case 'pendiente':   $badgeClass = 'bg-info'; break;
                                        case 'confirmada':  $badgeClass = 'bg-warning text-dark'; break;
                                        case 'check_in':    $badgeClass = 'bg-success'; break;
                                        case 'check_out':   $badgeClass = 'bg-secondary'; break;
                                        case 'cancelada':   $badgeClass = 'bg-danger'; break;
                                        default:            $badgeClass = 'bg-primary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['estado']))); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay reservas recientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
