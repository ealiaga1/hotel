<h1 class="mb-4">Gestión de Inventario (Productos)</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ACCESOS DIRECTOS INVENTARIO -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-white">Registrar Movimiento</h5>
                        <p class="card-text">Entrada, salida, descarte de productos.</p>
                    </div>
                    <i class="fas fa-exchange-alt fa-3x"></i>
                </div>
                <a href="/hotel_completo/public/inventory/movements/create" class="btn btn-light btn-sm mt-3 w-100">Registrar Movimiento <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card bg-success text-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-white">Historial de Movimientos</h5>
                        <p class="card-text">Ver todas las transacciones de stock.</p>
                    </div>
                    <i class="fas fa-history fa-3x"></i>
                </div>
                <a href="/hotel_completo/public/inventory/movements" class="btn btn-light btn-sm mt-3 w-100">Ver Historial <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card bg-info text-white shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-white">Gestionar Categorías</h5>
                        <p class="card-text">Administrar tipos de productos (limpieza, lencería).</p>
                    </div>
                    <i class="fas fa-tags fa-3x"></i>
                </div>
                <a href="/hotel_completo/public/inventory/categories" class="btn btn-light btn-sm mt-3 w-100">Ir a Categorías <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/inventory/products/create" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Nuevo Producto</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Productos de Inventario</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="productsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Unidad</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Precio Compra (S/)</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id_producto']); ?></td>
                                <td><?php echo htmlspecialchars($product['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($product['nombre_categoria']); ?></td>
                                <td><?php echo htmlspecialchars($product['unidad_medida']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(number_format($product['stock_actual'], 2, '.', ',')); ?>
                                    <?php if ($product['stock_actual'] <= $product['stock_minimo'] && $product['stock_actual'] > 0): ?>
                                        <span class="badge bg-warning text-dark ms-2">Bajo</span>
                                    <?php elseif ($product['stock_actual'] == 0): ?>
                                        <span class="badge bg-danger ms-2">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(number_format($product['stock_minimo'], 2, '.', ',')); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($product['precio_compra']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $types = [];
                                    if ($product['es_insumo_restaurante']) $types[] = 'Insumo Restaurante';
                                    if ($product['es_lenceria']) $types[] = 'Lencería';
                                    echo empty($types) ? 'General' : implode(', ', $types);
                                    ?>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/inventory/products/edit/<?php echo htmlspecialchars($product['id_producto']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/inventory/products/delete/<?php echo htmlspecialchars($product['id_producto']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay productos de inventario registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este producto? Si tiene movimientos de inventario o está asociado a platos de menú, la eliminación podría fallar.')) {
                e.preventDefault();
            }
        });
    });
});
</script>