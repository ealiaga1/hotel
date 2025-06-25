<h1 class="mb-4">Crear Nuevo Plato del Menú</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Plato</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/restaurant/menu_items/create" method="POST">
            <div class="mb-3">
                <label for="nombre_plato" class="form-label">Nombre del Plato <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_plato" name="nombre_plato" required>
            </div>
            <div class="mb-3">
                <label for="id_categoria_menu" class="form-label">Categoría <span class="text-danger">*</span></label>
                <select class="form-select" id="id_categoria_menu" name="id_categoria_menu" required>
                    <option value="">Seleccione una categoría</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id_categoria_menu']); ?>">
                                <?php echo htmlspecialchars($category['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay categorías registradas. Cree una primero.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio (S/) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0.01" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="foto_url" class="form-label">URL de la Foto</label>
                <input type="url" class="form-control" id="foto_url" name="foto_url" placeholder="https://ejemplo.com/imagen.jpg">
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="disponible" name="disponible" checked>
                <label class="form-check-label" for="disponible">Plato Disponible</label>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/restaurant" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Plato</button>
            </div>
        </form>
    </div>
</div>