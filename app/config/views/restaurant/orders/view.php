<h1 class="mb-4">Detalle del Pedido #<?php echo htmlspecialchars($order['id_pedido'] ?? ''); ?></h1>

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

<?php if (!isset($order) || !$order): ?>
    <div class="alert alert-warning" role="alert">Pedido no encontrado.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Información General del Pedido</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>ID Pedido:</strong> <?php echo htmlspecialchars($order['id_pedido']); ?><br>
                <strong>Tipo de Pedido:</strong> <?php echo htmlspecialchars(ucfirst($order['tipo_pedido'])); ?><br>
                <?php if ($order['tipo_pedido'] === 'mesa'): ?>
                    <strong>Mesa:</strong> <?php echo htmlspecialchars($order['numero_mesa'] ?? 'N/A'); ?><br>
                <?php elseif ($order['tipo_pedido'] === 'habitacion'): ?>
                    <strong>Huésped:</strong> <?php echo htmlspecialchars($order['huesped_nombre'] . ' ' . $order['huesped_apellido']); ?><br>
                <?php endif; ?>
                <strong>Estado:</strong>
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
            </div>
            <div class="col-md-6 mb-3">
                <strong>Fecha y Hora:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['fecha_pedido']))); ?><br>
                <strong>Tomado por:</strong> <?php echo htmlspecialchars($order['mesero_nombre'] . ' ' . $order['mesero_apellido']); ?><br>
                <strong>Comentarios:</strong> <?php echo htmlspecialchars($order['comentarios'] ?? 'Ninguno'); ?>
            </div>
        </div>

        <hr>

        <h5>Platos del Pedido:</h5>
        <div class="table-responsive mb-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Plato</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Comentarios Item</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($order['items'])): ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nombre_plato']); ?></td>
                                <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($item['precio_unitario']), 2, '.', ','); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($item['subtotal']), 2, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars($item['comentarios'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay platos en este pedido.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total del Pedido:</th>
                        <th class="text-end">S/ <?php echo number_format(htmlspecialchars($order['total_pedido']), 2, '.', ','); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <a href="/hotel_completo/public/restaurant/orders" class="btn btn-secondary me-2">Volver al Listado</a>
            <?php if ($order['estado'] !== 'pagado' && $order['estado'] !== 'cancelado'): ?>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="updateStatusBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        Cambiar Estado
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="updateStatusBtn">
                        <?php if ($order['estado'] === 'pendiente'): ?>
                            <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/en_preparacion">En Preparación</a></li>
                        <?php elseif ($order['estado'] === 'en_preparacion'): ?>
                            <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/listo">Listo para Entregar</a></li>
                        <?php elseif ($order['estado'] === 'listo'): ?>
                            <li><a class="dropdown-item" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/entregado">Marcar como Entregado</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-success" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/pagado">Marcar como Pagado</a></li>
                        <li><a class="dropdown-item text-danger" href="/hotel_completo/public/restaurant/orders/update_status/<?php echo htmlspecialchars($order['id_pedido']); ?>/cancelado">Cancelar Pedido</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>