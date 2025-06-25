<h1 class="mb-4">Crear Nuevo Producto de Inventario</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Producto</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/inventory/products/create" method="POST">
            <div class="mb-3">
                <label for="nombre_producto" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
            </div>
            <div class="mb-3">
                <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">Seleccione una categoría</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>">
                                <?php echo htmlspecialchars($category['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay categorías de inventario registradas. Cree una primero.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="unidad_medida" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="unidad_medida" name="unidad_medida" placeholder="Ej: kg, litro, unidad, caja" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="stock_actual" class="form-label">Stock Actual</label>
                    <input type="number" class="form-control" id="stock_actual" name="stock_actual" step="0.01" min="0" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="0.01" min="0" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="precio_compra" class="form-label">Precio de Compra (S/)</label>
                    <input type="number" class="form-control" id="precio_compra" name="precio_compra" step="0.01" min="0" value="0.00">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="es_insumo_restaurante" name="es_insumo_restaurante">
                        <label class="form-check-label" for="es_insumo_restaurante">Es insumo de Restaurante</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="es_lenceria" name="es_lenceria">
                        <label class="form-check-label" for="es_lenceria">Es Lencería / Ropa de Cama</label>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/inventory" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Producto</button>
            </div>
        </form>
    </div>
</div>