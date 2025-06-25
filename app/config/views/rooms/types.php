<h1 class="mb-4">Gestión de Tipos de Habitación</h1>

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
    <a href="/hotel_completo/public/rooms/create_type" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Tipo de Habitación</a>
    <a href="/hotel_completo/public/rooms" class="btn btn-secondary"><i class="fas fa-bed"></i> Volver a Habitaciones</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Tipos de Habitación</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="roomTypesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Capacidad</th>
                        <th>Precio Base (S/)</th>
                        <th>Descripción</th>
                        <th>Comodidades</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roomTypes)): ?>
                        <?php foreach ($roomTypes as $type): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['nombre_tipo']); ?></td>
                                <td><?php echo htmlspecialchars($type['capacidad']); ?></td>
                                <td><?php echo number_format(htmlspecialchars($type['precio_base']), 2, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars(substr($type['descripcion'], 0, 50)); ?>...</td>
                                <td><?php echo htmlspecialchars(substr($type['comodidades'], 0, 50)); ?>...</td>
                                <td>
                                    <a href="/hotel_completo/public/rooms/edit_type/<?php echo htmlspecialchars($type['id_tipo_habitacion']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/rooms/delete_type/<?php echo htmlspecialchars($type['id_tipo_habitacion']); ?>" class="btn btn-sm btn-danger delete-type-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay tipos de habitación registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmación para eliminar tipo de habitación
    const deleteTypeButtons = document.querySelectorAll('.delete-type-btn');
    deleteTypeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este tipo de habitación? Esto puede afectar a las habitaciones asociadas.')) {
                e.preventDefault();
            }
        });
    });
});
</script>