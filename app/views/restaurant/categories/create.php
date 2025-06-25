<h1 class="mb-4">Crear Nueva Categoría de Menú</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Categoría</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/restaurant/categories/create" method="POST">
            <div class="mb-3">
                <label for="nombre_categoria" class="form-label">Nombre de Categoría <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/restaurant/categories" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Categoría</button>
            </div>
        </form>
    </div>
</div>