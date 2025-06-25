<h1 class="mb-4">Gestión de Pedidos del Restaurante</h1>

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

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/restaurant/orders/create" class="btn btn-primary"><i class="fas fa-plus"></i> Crear Nuevo Pedido</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Pedidos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Tipo</th>
                        <th>Mesa / Huésped</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha Pedido</th>
                        <th>Mesero</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id_pedido']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($order['tipo_pedido'])); ?></td>
                                <td>
                                    <?php
                                    if ($order['tipo_pedido'] === 'mesa' && !empty($order['numero_mesa'])) {
                                        echo 'Mesa ' . htmlspecialchars($order['numero_mesa']);
                                    } elseif ($order['tipo_pedido'] === 'habitacion' && !empty($order['huesped_nombre'])) {
                                        echo htmlspecialchars($order['huesped_nombre'] . ' ' . $order['huesped_apellido']);
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>S/ <?php echo number_format(htmlspecialchars($order['total_pedido']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($order['estado']) {
                                        case 'pendiente':       $badgeClass = 'bg-info'; break;
                                        case 'en_preparacion':  $badgeClass = 'bg-warning text-dark'; break;
                                        case 'listo':           $badgeClass = 'bg-primary'; break;
                                        case 'entregado':       $badgeClass = 'bg-success'; break;
                                        case 'pagado':          $badgeClass = 'bg-secondary'; break;
                                        case 'cancelado':       $badgeClass = 'bg-danger'; break;
                                        default:                $badgeClass = 'bg-light text-dark'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['estado']))); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['fecha_pedido']))); ?></td>
                                <td><?php echo htmlspecialchars($order['mesero_nombre'] . ' ' . $order['mesero_apellido']); ?></td>
                                <td>
                                    <a href="/hotel_completo/public/restaurant/orders/view/<?php echo htmlspecialchars($order['id_pedido']); ?>" class="btn btn-sm btn-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                    <?php if ($order['estado'] !== 'pagado' && $order['estado'] !== 'cancelado'): ?>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $order['id_pedido']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $order['id_pedido']; ?>">
                                                <?php if ($order['estado'] === 'pendiente'): ?>
                                                    <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/en_preparacion">En Preparación</a></li>
                                                <?php elseif ($order['estado'] === 'en_preparacion'): ?>
                                                    <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/listo">Listo</a></li>
                                                <?php elseif ($order['estado'] === 'listo'): ?>
                                                    <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/entregado">Entregado</a></li>
                                                <?php endif; ?>
                                                <?php if ($order['estado'] !== 'pagado'): ?>
                                                    <li><a class="dropdown-item text-success" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/pagado">Marcar Pagado</a></li>
                                                <?php endif; ?>
                                                <?php if ($order['estado'] !== 'cancelado'): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger cancel-order-btn" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/cancelado">Cancelar Pedido</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <a href="/hotel_completo/public/restaurant/orders/delete/<?php echo htmlspecialchars($order['id_pedido']); ?>" class="btn btn-sm btn-outline-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay pedidos registrados.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar este pedido? Esta acción no se puede deshacer y puede fallar si tiene registros asociados.')) {
                e.preventDefault();
            }
        });
    });

    const cancelOrderButtons = document.querySelectorAll('.cancel-order-btn');
    cancelOrderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres CANCELAR este pedido?')) {
                e.preventDefault();
            }
        });
    });
});
</script>