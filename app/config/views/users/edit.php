<h1 class="mb-4">Editar Empleado: <?php echo htmlspecialchars($user['nombre_usuario'] ?? '') . ' ' . htmlspecialchars($user['apellido_usuario'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($user) || !$user): ?>
    <div class="alert alert-warning" role="alert">Empleado no encontrado.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Empleado</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/users/edit/<?php echo htmlspecialchars($user['id_usuario']); ?>" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($user['nombre_usuario'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido_usuario" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" value="<?php echo htmlspecialchars($user['apellido_usuario'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Ingresa una nueva contraseña solo si deseas cambiarla.</small>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_rol" name="id_rol" required>
                        <option value="">Seleccione un rol</option>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['id_rol']); ?>"
                                    <?php echo ($role['id_rol'] == $user['id_rol']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['nombre_rol']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No hay roles registrados.</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control" id="dni" name="dni" value="<?php echo htmlspecialchars($user['dni'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="activo" <?php echo ($user['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo ($user['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($user['direccion'] ?? ''); ?>">
            </div>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/users" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Empleado</button>
            </div>
        </form>
    </div>
</div>