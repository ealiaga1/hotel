<h1 class="mb-4">Editar Huésped: <?php echo htmlspecialchars($guest['nombre'] ?? '') . ' ' . htmlspecialchars($guest['apellido'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($guest) || !$guest): ?>
    <div class="alert alert-warning" role="alert">Huésped no encontrado.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Huésped</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/guests/edit/<?php echo htmlspecialchars($guest['id_huesped']); ?>" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($guest['nombre'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($guest['apellido'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo_documento" class="form-label">Tipo Documento</label>
                    <select class="form-select" id="tipo_documento" name="tipo_documento">
                        <option value="">Selecciona</option>
                        <option value="DNI" <?php echo ($guest['tipo_documento'] == 'DNI') ? 'selected' : ''; ?>>DNI</option>
                        <option value="Pasaporte" <?php echo ($guest['tipo_documento'] == 'Pasaporte') ? 'selected' : ''; ?>>Pasaporte</option>
                        <option value="Carnet de Extranjería" <?php echo ($guest['tipo_documento'] == 'Carnet de Extranjería') ? 'selected' : ''; ?>>Carnet de Extranjería</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="numero_documento" class="form-label">Número Documento</label>
                    <input type="text" class="form-control" id="numero_documento" name="numero_documento" value="<?php echo htmlspecialchars($guest['numero_documento'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($guest['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($guest['telefono'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($guest['fecha_nacimiento'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="pais" class="form-label">País</label>
                    <input type="text" class="form-control" id="pais" name="pais" value="<?php echo htmlspecialchars($guest['pais'] ?? 'Perú'); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($guest['direccion'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($guest['ciudad'] ?? ''); ?>">
            </div>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/guests" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Huésped</button>
            </div>
        </form>
    </div>
</div>