<h1 class="mb-4">Editar Mesa: <?php echo htmlspecialchars($table['numero_mesa'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($table) || !$table): ?>
    <div class="alert alert-warning" role="alert">Mesa no encontrada.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Mesa</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/restaurant/tables/edit/<?php echo htmlspecialchars($table['id_mesa']); ?>" method="POST">
            <div class="mb-3">
                <label for="numero_mesa" class="form-label">Número de Mesa <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="numero_mesa" name="numero_mesa" value="<?php echo htmlspecialchars($table['numero_mesa'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" min="1" value="<?php echo htmlspecialchars($table['capacidad'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($table['ubicacion'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="disponible" <?php echo ($table['estado'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="ocupada" <?php echo ($table['estado'] == 'ocupada') ? 'selected' : ''; ?>>Ocupada</option>
                    <option value="reservada" <?php echo ($table['estado'] == 'reservada') ? 'selected' : ''; ?>>Reservada</option>
                    <option value="en_limpieza" <?php echo ($table['estado'] == 'en_limpieza') ? 'selected' : ''; ?>>En Limpieza</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/restaurant/tables" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Mesa</button>
            </div>
        </form>
    </div>
</div>