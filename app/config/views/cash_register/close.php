<h1 class="mb-4">Cerrar Caja: Turno #<?php echo htmlspecialchars($openRegister['id_movimiento_caja'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($openRegister) || !$openRegister): ?>
    <div class="alert alert-warning" role="alert">No se encontró un turno de caja abierto para cerrar.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Resumen del Turno de Caja</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Turno Abierto por:</strong> <?php echo htmlspecialchars($openRegister['apertura_nombre'] . ' ' . $openRegister['apertura_apellido']); ?><br>
                <strong>Fecha y Hora de Apertura:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($openRegister['fecha_apertura']))); ?><br>
                <strong>Saldo Inicial:</strong> S/ <?php echo number_format(htmlspecialchars($openRegister['saldo_inicial']), 2, '.', ','); ?>
            </div>
            <div class="col-md-6">
                <strong>Ingresos Registrados:</strong> <span class="text-success">S/ <?php echo number_format(htmlspecialchars($registerSummary['total_ingresos']), 2, '.', ','); ?></span><br>
                <strong>Egresos Registrados:</strong> <span class="text-danger">S/ <?php echo number_format(htmlspecialchars($registerSummary['total_egresos']), 2, '.', ','); ?></span><br>
                <strong>Saldo Final Calculado:</strong> <span class="text-primary fs-5">S/ <?php echo number_format(htmlspecialchars($calculated_final_balance), 2, '.', ','); ?></span>
            </div>
        </div>

        <hr>

        <form action="/hotel_completo/public/cash_register/close/<?php echo htmlspecialchars($openRegister['id_movimiento_caja']); ?>" method="POST">
            <div class="mb-3">
                <label for="saldo_final_input" class="form-label">Saldo Final Contado (S/) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="saldo_final_input" name="saldo_final_input" step="0.01" min="0" value="<?php echo htmlspecialchars(number_format($calculated_final_balance, 2, '.', '')); ?>" required>
                <small class="form-text text-muted">Ingrese el monto físico de efectivo al cerrar la caja.</small>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/cash_register" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-danger">Confirmar Cierre de Caja</button>
            </div>
        </form>
    </div>
</div>