<h1 class="mb-4">Historial de Movimientos de Inventario</h1>

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
    <a href="/hotel_completo/public/inventory/movements/create" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Nuevo Movimiento</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Movimientos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="movementsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha y Hora</th>
                        <th>Producto</th>
                        <th>Tipo de Movimiento</th>
                        <th>Cantidad</th>
                        <th>Referencia</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movements)): ?>
                        <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($movement['id_movimiento']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($movement['fecha_movimiento']))); ?></td>
                                <td><?php echo htmlspecialchars($movement['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $movement['nombre_movimiento']))); ?></td>
                                <td><?php echo htmlspecialchars(number_format($movement['cantidad'], 2, '.', ',')) . ' ' . htmlspecialchars($movement['unidad_medida']); ?></td>
                                <td><?php echo htmlspecialchars($movement['referencia'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($movement['usuario_nombre'] . ' ' . $movement['usuario_apellido']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay movimientos de inventario registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>