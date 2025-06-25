<h1 class="mb-4">Gestión de Personal</h1>

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

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/users/create" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Nuevo Empleado</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Empleados</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>DNI</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($user['nombre_usuario'] . ' ' . $user['apellido_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['nombre_rol']); ?></td>
                                <td><?php echo htmlspecialchars($user['dni']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefono']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($user['estado']) {
                                        case 'activo':   $badgeClass = 'bg-success'; break;
                                        case 'inactivo': $badgeClass = 'bg-danger'; break;
                                        default:         $badgeClass = 'bg-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($user['estado'])); ?></span>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/users/edit/<?php echo htmlspecialchars($user['id_usuario']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id_usuario']): // Can't delete self ?>
                                    <a href="/hotel_completo/public/users/delete/<?php echo htmlspecialchars($user['id_usuario']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay empleados registrados.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar este empleado? Esta acción no se puede deshacer y puede fallar si tiene registros asociados.')) {
                e.preventDefault();
            }
        });
    });
});
</script>