<h1 class="mb-4">Registrar Transacción Manual de Caja</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Transacción</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/cash_register/add_transaction" method="POST">
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                <small class="form-text text-muted">Ej: Venta de minibar, Compra de suministros, Cambio de efectivo.</small>
            </div>
            <div class="mb-3">
                <label for="monto" class="form-label">Monto (S/) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required>
            </div>
            <div class="mb-3">
                <label for="tipo_transaccion" class="form-label">Tipo de Transacción <span class="text-danger">*</span></label>
                <select class="form-select" id="tipo_transaccion" name="tipo_transaccion" required>
                    <option value="">Seleccione el tipo</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/cash_register" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Transacción</button>
            </div>
        </form>
    </div>
</div>