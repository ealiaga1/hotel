<h1 class="mb-4">Gestión de Platos del Menú</h1>

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

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/hotel_completo/public/restaurant/menu_items/create" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Nuevo Plato</a>
    <a href="/hotel_completo/public/restaurant/categories" class="btn btn-info"><i class="fas fa-list"></i> Gestionar Categorías</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Platos del Menú</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="menuItemsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plato</th>
                        <th>Categoría</th>
                        <th>Precio (S/)</th>
                        <th>Descripción</th>
                        <th>Foto</th>
                        <th>Disponible</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($menuItems)): ?>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['id_plato']); ?></td>
                                <td><?php echo htmlspecialchars($item['nombre_plato']); ?></td>
                                <td><?php echo htmlspecialchars($item['nombre_categoria']); ?></td>
                                <td><?php echo number_format(htmlspecialchars($item['precio']), 2, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['descripcion'], 0, 50)); ?>...</td>
                                <td>
                                    <?php if (!empty($item['foto_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['foto_url']); ?>" alt="Foto" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" onerror="this.onerror=null;this.src='https://placehold.co/50x50/cccccc/ffffff?text=No+Img';">
                                    <?php else: ?>
                                        No img
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['disponible']): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/restaurant/menu_items/edit/<?php echo htmlspecialchars($item['id_plato']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/restaurant/menu_items/delete/<?php echo htmlspecialchars($item['id_plato']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay platos de menú registrados.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar este plato? Si tiene pedidos asociados, la eliminación podría fallar.')) {
                e.preventDefault();
            }
        });
    });
});
</script>