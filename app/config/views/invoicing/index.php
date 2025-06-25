<h1 class="mb-4">Gestión de Facturación</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Listado de Facturas Emitidas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="invoicesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Factura</th>
                        <th>Tipo / Nº Documento</th>
                        <th>Huésped</th>
                        <th>Reserva ID</th>
                        <th>Fecha Emisión</th>
                        <th>Monto Total (S/)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['id_factura']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['tipo_documento']); ?> / <?php echo htmlspecialchars($invoice['serie_documento']); ?>-<?php echo htmlspecialchars($invoice['numero_documento']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['huesped_nombre'] . ' ' . $invoice['huesped_apellido']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['id_reserva'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['fecha_emision']))); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch ($invoice['estado']) {
                                        case 'pendiente':   $badgeClass = 'bg-warning text-dark'; break;
                                        case 'pagada':      $badgeClass = 'bg-success'; break;
                                        case 'anulada':     $badgeClass = 'bg-danger'; break;
                                        default:            $badgeClass = 'bg-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($invoice['estado'])); ?></span>
                                </td>
                                <td>
                                    <a href="/hotel_completo/public/invoicing/view/<?php echo htmlspecialchars($invoice['id_factura']); ?>" class="btn btn-sm btn-info" title="Ver Detalle"><i class="fas fa-eye"></i></a>
                                    <?php if ($invoice['estado'] !== 'anulada'): ?>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="printDropdown<?php echo $invoice['id_factura']; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="printDropdown<?php echo $invoice['id_factura']; ?>">
                                                <li><a class="dropdown-item" href="/hotel_completo/public/invoicing/print_a4/<?php echo htmlspecialchars($invoice['id_factura']); ?>" target="_blank">Imprimir A4</a></li>
                                                <li><a class="dropdown-item" href="/hotel_completo/public/invoicing/print_ticket/<?php echo htmlspecialchars($invoice['id_factura']); ?>" target="_blank">Imprimir Ticket</a></li>
                                            </ul>
                                        </div>
                                        <a href="/hotel_completo/public/invoicing/void/<?php echo htmlspecialchars($invoice['id_factura']); ?>" class="btn btn-sm btn-danger void-invoice-btn" title="Anular Factura"><i class="fas fa-ban"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay facturas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voidButtons = document.querySelectorAll('.void-invoice-btn');
    voidButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres ANULAR esta factura? Esta acción es irreversible y puede requerir ajustes manuales en la contabilidad.')) {
                e.preventDefault();
            }
        });
    });
});
</script>