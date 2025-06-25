<h1 class="mb-4">Gestión de Caja</h1>

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

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Estado Actual de la Caja</h6>
    </div>
    <div class="card-body">
        <?php if ($openRegister): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <strong>Caja Abierta:</strong> Turno #<?php echo htmlspecialchars($openRegister['id_movimiento_caja']); ?><br>
                    <strong>Abierto por:</strong> <?php echo htmlspecialchars($openRegister['apertura_nombre'] . ' ' . $openRegister['apertura_apellido']); ?> el <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($openRegister['fecha_apertura']))); ?><br>
                    <strong>Saldo Inicial:</strong> S/ <?php echo number_format(htmlspecialchars($openRegister['saldo_inicial']), 2, '.', ','); ?><br>
                    <strong>Ingresos Acumulados:</strong> S/ <?php echo number_format(htmlspecialchars($registerSummary['total_ingresos']), 2, '.', ','); ?><br>
                    <strong>Egresos Acumulados:</strong> S/ <?php echo number_format(htmlspecialchars($registerSummary['total_egresos']), 2, '.', ','); ?><br>
                    <strong>Saldo Calculado:</strong> S/ <?php echo number_format(htmlspecialchars($openRegister['saldo_inicial'] + $registerSummary['total_ingresos'] - $registerSummary['total_egresos']), 2, '.', ','); ?>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <!-- <a href="/hotel_completo/public/cash_register/add_transaction" class="btn btn-primary me-md-2"><i class="fas fa-plus"></i> Registrar Transacción</a> -->
                <a href="/hotel_completo/public/cash_register/close/<?php echo htmlspecialchars($openRegister['id_movimiento_caja']); ?>" class="btn btn-danger"><i class="fas fa-money-bill-alt"></i> Cerrar Caja</a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Caja Cerrada:</strong> No hay ningún turno de caja activo en este momento.
                </div>
            </div>
            <div class="d-grid justify-content-md-end">
                <a href="/hotel_completo/public/cash_register/open" class="btn btn-success"><i class="fas fa-plus"></i> Abrir Caja</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-grid justify-content-md-end">
    <a href="/hotel_completo/public/cash_register/history" class="btn btn-info"><i class="fas fa-history"></i> Ver Historial de Cierres de Caja</a>
</div>