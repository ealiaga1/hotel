<h1 class="mb-4">Gestión de Proveedores</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/suppliers/create" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Nuevo Proveedor</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Proveedores</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="suppliersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Razón Social/Nombre</th>
                        <th>RUC/DNI</th>
                        <th>Modo de Pago</th>
                        <th>Teléfono Fijo</th>
                        <th>Email</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['id_proveedor']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['razon_social']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['ruc_dni'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['modo_pago']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['telefono_fijo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contacto'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="/hotel_completo/public/suppliers/edit/<?php echo htmlspecialchars($supplier['id_proveedor']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/suppliers/delete/<?php echo htmlspecialchars($supplier['id_proveedor']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No hay proveedores registrados.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar este proveedor? Si tiene registros asociados (ej. compras), la eliminación podría fallar.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
