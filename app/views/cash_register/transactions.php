<h1 class="mb-4">Transacciones del Turno de Caja Actual</h1>

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

<?php if (!isset($openRegister) || !$openRegister): ?>
    <div class="alert alert-warning" role="alert">No hay un turno de caja abierto para ver sus transacciones.</div>
<?php return; endif; ?>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/cash_register/add_transaction" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Nueva Transacción</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Transacciones (Turno #<?php echo htmlspecialchars($openRegister['id_movimiento_caja']); ?>)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="cashTransactionsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Transacción</th>
                        <th>Fecha y Hora</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Monto (S/)</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['id_transaccion_caja']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($transaction['fecha_transaccion']))); ?></td>
                                <td><?php echo htmlspecialchars($transaction['descripcion']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = ($transaction['tipo_transaccion'] === 'ingreso') ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($transaction['tipo_transaccion'])); ?></span>
                                </td>
                                <td>S/ <?php echo number_format(htmlspecialchars($transaction['monto']), 2, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars($transaction['usuario_nombre'] . ' ' . $transaction['usuario_apellido'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay transacciones registradas para este turno de caja.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>