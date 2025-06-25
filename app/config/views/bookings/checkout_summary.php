<h1 class="mb-4">Resumen de Check-out para Reserva #<?php echo htmlspecialchars($booking['id_reserva'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($booking) || !$booking): ?>
    <div class="alert alert-warning" role="alert">Reserva no encontrada o no válida para Check-out.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Check-out</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Información de la Reserva</h5>
                <p>
                    <strong>ID Reserva:</strong> <?php echo htmlspecialchars($booking['id_reserva']); ?><br>
                    <strong>Huésped:</strong> <?php echo htmlspecialchars($booking['huesped_nombre'] . ' ' . $booking['huesped_apellido']); ?><br>
                    <strong>Habitación:</strong> <?php echo htmlspecialchars($booking['numero_habitacion'] ?? 'No asignada'); ?><br>
                    <strong>Fechas:</strong> <?php echo htmlspecialchars($booking['fecha_entrada']); ?> al <?php echo htmlspecialchars($booking['fecha_salida']); ?><br>
                    <strong>Adultos/Niños:</strong> <?php echo htmlspecialchars($booking['adultos']); ?>/<?php echo htmlspecialchars($booking['ninos']); ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h5>Estado Actual</h5>
                <p>
                    <?php
                    $badgeClass = '';
                    switch ($booking['estado']) {
                        case 'check_in': $badgeClass = 'bg-success'; break;
                        default:         $badgeClass = 'bg-secondary'; break;
                    }
                    ?>
                    <span class="badge <?php echo $badgeClass; ?> fs-5"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $booking['estado']))); ?></span>
                </p>
            </div>
        </div>

        <hr>

        <h5>Resumen de Cuenta:</h5>
        <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Costo de Alojamiento:
                <span>S/ <?php echo number_format(htmlspecialchars($accommodation_cost), 2, '.', ','); ?></span>
            </li>
            
            <?php if (!empty($charges_list)): ?>
                <li class="list-group-item list-group-item-secondary">
                    <strong>Otros Cargos / Servicios Pendientes:</strong>
                </li>
                <?php foreach ($charges_list as $charge): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center ps-4">
                        - <?php echo htmlspecialchars($charge['descripcion']); ?> (<?php echo htmlspecialchars(date('d/m/Y', strtotime($charge['fecha_cargo']))); ?>):
                        <span>S/ <?php echo number_format(htmlspecialchars($charge['monto']), 2, '.', ','); ?></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                 <li class="list-group-item d-flex justify-content-between align-items-center">
                    Otros Cargos / Servicios:
                    <span>S/ 0.00</span>
                </li>
            <?php endif; ?>

            <li class="list-group-item d-flex justify-content-between align-items-center bg-light font-weight-bold">
                <strong>Total Adeudado:</strong>
                <span class="fs-4 text-primary">S/ <?php echo number_format(htmlspecialchars($total_due), 2, '.', ','); ?></span>
            </li>
        </ul>

        <hr>

        <h5>Información de Pago para Factura:</h5>
        <form action="/hotel_completo/public/bookings/finalize_checkout/<?php echo htmlspecialchars($booking['id_reserva']); ?>" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="monto_pagado" class="form-label">Monto Pagado (S/) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="monto_pagado" name="monto_pagado" step="0.01" min="0.01" value="<?php echo htmlspecialchars(number_format($total_due, 2, '.', '')); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                        <option value="">Seleccione un método</option>
                        <option value="Efectivo" selected>Efectivo</option>
                        <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                        <option value="Yape/Plin">Yape/Plin</option>
                        <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tipo_documento_factura" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_documento_factura" name="tipo_documento_factura" required>
                        <option value="Boleta">Boleta</option>
                        <option value="Factura">Factura</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="serie_documento" class="form-label">Serie Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="serie_documento" name="serie_documento" value="B001" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" value="<?php echo date('YmdHis'); ?>" required>
                    <small class="form-text text-muted">Se autogenera, modificar si es necesario.</small>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/bookings" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Finalizar Check-out y Pagar</button>
            </div>
        </form>
    </div>
</div>