<h1 class="mb-4">Crear Nueva Mesa</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Mesa</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/restaurant/tables/create" method="POST">
            <div class="mb-3">
                <label for="numero_mesa" class="form-label">Número de Mesa <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="numero_mesa" name="numero_mesa" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" required>
            </div>
            <div class="mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion">
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado Inicial</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="disponible">Disponible</option>
                    <option value="ocupada">Ocupada</option>
                    <option value="reservada">Reservada</option>
                    <option value="en_limpieza">En Limpieza</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/restaurant/tables" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Mesa</button>
            </div>
        </form>
    </div>
</div>