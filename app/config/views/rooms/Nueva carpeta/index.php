<h1 class="mb-4">Gestión de Habitaciones</h1>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/hotel_completo/public/rooms/create" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Habitación</a>
    <a href="/hotel_completo/public/rooms/types" class="btn btn-info"><i class="fas fa-list"></i> Gestionar Tipos de Habitación</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Habitaciones</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="roomsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nº Habitación</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Precio Base</th>
                        <th>Piso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['numero_habitacion']); ?></td>
                                <td><?php echo htmlspecialchars($room['nombre_tipo']); ?></td>
                                <td><?php echo htmlspecialchars($room['capacidad']); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($room['precio_base']), 2, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars($room['piso']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($room['estado']) {
                                        case 'disponible':    $badgeClass = 'bg-success'; break;
                                        case 'ocupada':       $badgeClass = 'bg-warning text-dark'; break;
                                        case 'sucia':         $badgeClass = 'bg-danger'; break;
                                        case 'mantenimiento': $badgeClass = 'bg-secondary'; break;
                                        default:              $badgeClass = 'bg-primary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($room['estado'])); ?></span>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/rooms/edit/<?php echo htmlspecialchars($room['id_habitacion']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/rooms/delete/<?php echo htmlspecialchars($room['id_habitacion']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay habitaciones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmación para eliminar
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta habitación?')) {
                e.preventDefault(); // Evita la navegación si el usuario cancela
            }
        });
    });
});
</script>