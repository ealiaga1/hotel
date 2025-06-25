<h1 class="mb-4">Gestión de Categorías del Menú</h1>

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
    <a href="/hotel_completo/public/restaurant/categories/create" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Nueva Categoría</a>
    <a href="/hotel_completo/public/restaurant" class="btn btn-info"><i class="fas fa-utensils"></i> Volver al Menú de Platos</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Categorías del Menú</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="menuCategoriesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Categoría</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['id_categoria_menu']); ?></td>
                                <td><?php echo htmlspecialchars($category['nombre_categoria']); ?></td>
                                <td><?php echo htmlspecialchars($category['descripcion']); ?></td>
                                <td>
                                    <a href="/hotel_completo/public/restaurant/categories/edit/<?php echo htmlspecialchars($category['id_categoria_menu']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/restaurant/categories/delete/<?php echo htmlspecialchars($category['id_categoria_menu']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay categorías de menú registradas.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar esta categoría? Si tiene platos asociados, la eliminación podría fallar.')) {
                e.preventDefault();
            }
        });
    });
});
</script>