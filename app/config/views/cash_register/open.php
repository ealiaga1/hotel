<h1 class="mb-4">Abrir Caja</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de Apertura de Caja</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/cash_register/open" method="POST">
            <div class="mb-3">
                <label for="saldo_inicial" class="form-label">Saldo Inicial (S/) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="saldo_inicial" name="saldo_inicial" step="0.01" min="0" value="0.00" required>
                <small class="form-text text-muted">Monto de efectivo con el que se inicia el turno de caja.</small>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/cash_register" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-success">Abrir Caja</button>
            </div>
        </form>
    </div>
</div>