<h1 class="mb-4">Crear Nueva Habitación</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Habitación</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/rooms/create" method="POST">
            <div class="mb-3">
                <label for="numero_habitacion" class="form-label">Número de Habitación <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="numero_habitacion" name="numero_habitacion" required>
            </div>
            <div class="mb-3">
                <label for="id_tipo_habitacion" class="form-label">Tipo de Habitación <span class="text-danger">*</span></label>
                <select class="form-select" id="id_tipo_habitacion" name="id_tipo_habitacion" required>
                    <option value="">Selecciona un tipo</option>
                    <?php if (!empty($roomTypes)): ?>
                        <?php foreach ($roomTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['id_tipo_habitacion']); ?>">
                                <?php echo htmlspecialchars($type['nombre_tipo']); ?> (Cap: <?php echo htmlspecialchars($type['capacidad']); ?>, Precio: S/<?php echo number_format(htmlspecialchars($type['precio_base']), 2, '.', ','); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay tipos de habitación registrados. Crea uno primero.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="piso" class="form-label">Piso <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="piso" name="piso" required min="1">
            </div>
            <div class="mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion">
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/rooms" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Habitación</button>
            </div>
        </form>
    </div>
</div>