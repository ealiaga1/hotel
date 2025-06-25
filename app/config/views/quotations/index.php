<h1 class="mb-4">Gestión de Cotizaciones</h1>

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
    <a href="/hotel_completo/public/quotations/create" class="btn btn-primary"><i class="fas fa-plus"></i> Crear Nueva Cotización</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Cotizaciones Emitidas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="quotationsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Cotiz.</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Moneda</th>
                        <th>Total (S/)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($quotations)): ?>
                        <?php foreach ($quotations as $quotation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quotation['nro_cotizacion']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($quotation['fecha_cotizacion']))); ?></td>
                                <td><?php echo htmlspecialchars($quotation['cliente_razon_social']); ?></td>
                                <td><?php echo htmlspecialchars($quotation['vendedor_nombre'] . ' ' . $quotation['vendedor_apellido']); ?></td>
                                <td><?php echo htmlspecialchars($quotation['moneda']); ?></td>
                                <td><?php echo number_format(htmlspecialchars($quotation['total']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($quotation['estado']) {
                                        case 'Pendiente':   $badgeClass = 'bg-info text-white'; break;
                                        case 'Aceptada':    $badgeClass = 'bg-success text-white'; break;
                                        case 'Rechazada':   $badgeClass = 'bg-danger text-white'; break;
                                        case 'Anulada':     $badgeClass = 'bg-secondary text-white'; break;
                                        default:            $badgeClass = 'bg-primary text-white'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($quotation['estado'])); ?></span>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/quotations/edit/<?php echo htmlspecialchars($quotation['id_cotizacion']); ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                    <a href="/hotel_completo/public/quotations/delete/<?php echo htmlspecialchars($quotation['id_cotizacion']); ?>" class="btn btn-sm btn-danger delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                    <!-- Puedes añadir aquí un botón para "Imprimir Cotización" -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay cotizaciones registradas.</td>
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
            if (!confirm('¿Estás seguro de que quieres eliminar esta cotización? Esta acción es irreversible.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
