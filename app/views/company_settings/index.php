<h1 class="mb-4">Configuración de la Empresa</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos Generales de la Empresa</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/company_settings" method="POST">
            <div class="mb-3">
                <label for="nombre_empresa" class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($settings['nombre_empresa'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="ruc" class="form-label">RUC</label>
                <input type="text" class="form-control" id="ruc" name="ruc" value="<?php echo htmlspecialchars($settings['ruc'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($settings['direccion'] ?? ''); ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($settings['telefono'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="logo_url" class="form-label">URL del Logo (Opcional)</label>
                <input type="url" class="form-control" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>" placeholder="Ej: http://tuempresa.com/logo.png">
                <?php if (!empty($settings['logo_url'])): ?>
                    <small class="form-text text-muted mt-2">Logo actual: <img src="<?php echo htmlspecialchars($settings['logo_url']); ?>" alt="Logo de la empresa" style="max-height: 50px; vertical-align: middle;"></small>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </div>
        </form>
    </div>
</div>
