<h1 class="mb-4">Gestión de Huéspedes</h1>

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
    <a href="/hotel_completo/public/guests/create" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Nuevo Huésped</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Huéspedes</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="guestsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Tipo Doc.</th>
                        <th>Nº Documento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($guests)): ?>
                        <?php foreach ($guests as $guest): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($guest['id_huesped']); ?></td>
                                <td><?php echo htmlspecialchars($guest['nombre'] . ' ' . $guest['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($guest['tipo_documento']); ?></td>
                                <td><?php echo htmlspecialchars($guest['numero_documento']); ?></td>
                                <td><?php echo htmlspecialchars($guest['email']); ?></td>
                                <td><?php echo htmlspecialchars($guest['telefono']); ?></td>
                                <td>
                                    <a href="/hotel_completo/public/guests/edit/<?php echo htmlspecialchars($guest['id_huesped']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <!-- Aquí podrías añadir un botón para ver historial de reservas -->
                                    <a href="/hotel_completo/public/guests/delete/<?php echo htmlspecialchars($guest['id_huesped']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay huéspedes registrados.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar este huésped? Si tiene reservas o facturas asociadas, la eliminación podría fallar.')) {
                e.preventDefault(); // Evita la navegación si el usuario cancela
            }
        });
    });
});
</script>