<h1 class="mb-4">Crear Nuevo Tipo de Habitación</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Tipo de Habitación</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/rooms/create_type" method="POST">
            <div class="mb-3">
                <label for="nombre_tipo" class="form-label">Nombre del Tipo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_tipo" name="nombre_tipo" required>
            </div>
            <div class="mb-3">
                <label for="capacidad" class="form-label">Capacidad <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="capacidad" name="capacidad" required min="1">
            </div>
            <div class="mb-3">
                <label for="precio_base" class="form-label">Precio Base (S/) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="precio_base" name="precio_base" step="0.01" required min="0.01">
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="comodidades" class="form-label">Comodidades</label>
                <textarea class="form-control" id="comodidades" name="comodidades" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="foto_url" class="form-label">URL de la Foto</label>
                <input type="url" class="form-control" id="foto_url" name="foto_url" placeholder="https://ejemplo.com/imagen.jpg">
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/rooms/types" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Tipo</button>
            </div>
        </form>
    </div>
</div>