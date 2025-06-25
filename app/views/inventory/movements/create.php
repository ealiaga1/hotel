<h1 class="mb-4">Registrar Nuevo Movimiento de Inventario</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Movimiento</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/inventory/movements/create" method="POST">
            <div class="mb-3">
                <label for="id_producto" class="form-label">Producto <span class="text-danger">*</span></label>
                <select class="form-select" id="id_producto" name="id_producto" required>
                    <option value="">Seleccione un producto</option>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo htmlspecialchars($product['id_producto']); ?>" data-unidad="<?php echo htmlspecialchars($product['unidad_medida']); ?>" data-stock="<?php echo htmlspecialchars($product['stock_actual']); ?>">
                                <?php echo htmlspecialchars($product['nombre_producto']); ?> (Stock: <?php echo htmlspecialchars(number_format($product['stock_actual'], 2, '.', ',')) . ' ' . htmlspecialchars($product['unidad_medida']); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay productos de inventario registrados.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_tipo_movimiento" class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                <select class="form-select" id="id_tipo_movimiento" name="id_tipo_movimiento" required>
                    <option value="">Seleccione un tipo</option>
                    <?php if (!empty($movementTypes)): ?>
                        <?php foreach ($movementTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['id_tipo_movimiento']); ?>" data-nombre="<?php echo htmlspecialchars($type['nombre_movimiento']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type['nombre_movimiento']))); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay tipos de movimiento registrados.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-control" id="cantidad" name="cantidad" step="0.01" min="0.01" required>
                    <span class="input-group-text" id="unidadMedidaDisplay">Unidad</span>
                </div>
                <small class="form-text text-muted" id="currentStockDisplay">Stock actual: 0 Unidad</small>
            </div>
            <div class="mb-3">
                <label for="referencia" class="form-label">Referencia (Opcional)</label>
                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Ej: Factura #123, Venta diaria, Descarte">
            </div>
            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/inventory/movements" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const idProductoSelect = document.getElementById('id_producto');
    const unidadMedidaDisplay = document.getElementById('unidadMedidaDisplay');
    const currentStockDisplay = document.getElementById('currentStockDisplay');
    const cantidadInput = document.getElementById('cantidad');
    const idTipoMovimientoSelect = document.getElementById('id_tipo_movimiento');

    function updateProductInfo() {
        const selectedOption = idProductoSelect.options[idProductoSelect.selectedIndex];
        if (selectedOption.value) {
            const unidad = selectedOption.dataset.unidad || 'Unidad';
            const stock = selectedOption.dataset.stock || '0';
            unidadMedidaDisplay.textContent = unidad;
            currentStockDisplay.textContent = `Stock actual: ${parseFloat(stock).toFixed(2)} ${unidad}`;
        } else {
            unidadMedidaDisplay.textContent = 'Unidad';
            currentStockDisplay.textContent = 'Stock actual: 0 Unidad';
        }
        validateQuantity(); // Re-validate quantity on product change
    }

    function validateQuantity() {
        const selectedProductOption = idProductoSelect.options[idProductoSelect.selectedIndex];
        const currentStock = parseFloat(selectedProductOption.dataset.stock || '0');
        const quantityToMove = parseFloat(cantidadInput.value);
        const selectedMovementTypeOption = idTipoMovimientoSelect.options[idTipoMovimientoSelect.selectedIndex];
        const movementTypeName = selectedMovementTypeOption.dataset.nombre || '';

        // Only validate for 'salida', 'descarte', 'lavanderia_envio'
        if (['salida', 'descarte', 'lavanderia_envio'].includes(movementTypeName)) {
            if (quantityToMove > currentStock) {
                cantidadInput.setCustomValidity('La cantidad excede el stock disponible.');
                cantidadInput.classList.add('is-invalid');
            } else {
                cantidadInput.setCustomValidity('');
                cantidadInput.classList.remove('is-invalid');
            }
        } else {
            // For other types (like 'entrada'), no stock limit check
            cantidadInput.setCustomValidity('');
            cantidadInput.classList.remove('is-invalid');
        }
    }

    idProductoSelect.addEventListener('change', updateProductInfo);
    cantidadInput.addEventListener('input', validateQuantity);
    idTipoMovimientoSelect.addEventListener('change', validateQuantity); // Re-validate on type change

    updateProductInfo(); // Initial call
    validateQuantity(); // Initial call
});
</script>