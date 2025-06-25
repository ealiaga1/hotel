<h1 class="mb-4">Detalle de Factura #<?php echo htmlspecialchars($invoice['id_factura'] ?? ''); ?></h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if (!isset($invoice) || !$invoice): ?>
    <div class="alert alert-warning" role="alert">Factura no encontrada.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Información General de la Factura</h6>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Tipo Documento:</strong> <?php echo htmlspecialchars($invoice['tipo_documento']); ?><br>
                <strong>Serie y Nº:</strong> <?php echo htmlspecialchars($invoice['serie_documento']); ?>-<?php echo htmlspecialchars($invoice['numero_documento']); ?><br>
                <strong>Fecha Emisión:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['fecha_emision']))); ?><br>
                <strong>Fecha Vencimiento:</strong> <?php echo htmlspecialchars($invoice['fecha_vencimiento'] ?? 'N/A'); ?>
            </div>
            <div class="col-md-6">
                <strong>Estado:</strong>
                <?php
                $badgeClass = '';
                switch ($invoice['estado']) {
                    case 'pendiente':   $badgeClass = 'bg-warning text-dark'; break;
                    case 'pagada':      $badgeClass = 'bg-success'; break;
                    case 'anulada':     $badgeClass = 'bg-danger'; break;
                    default:            $badgeClass = 'bg-secondary'; break;
                }
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($invoice['estado'])); ?></span><br>
                <strong>Huésped:</strong> <?php echo htmlspecialchars($invoice['huesped_nombre'] . ' ' . $invoice['huesped_apellido']); ?><br>
                <strong>Documento Huésped:</strong> <?php echo htmlspecialchars($invoice['huesped_documento'] ?? 'N/A'); ?><br>
                <strong>Email Huésped:</strong> <?php echo htmlspecialchars($invoice['huesped_email'] ?? 'N/A'); ?>
            </div>
        </div>

        <hr>

        <h5>Cargos y Detalles Asociados:</h5>
        <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Monto Base:
                <span>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Impuestos (IGV):
                <span>S/ <?php echo number_format(htmlspecialchars($invoice['impuestos']), 2, '.', ','); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Descuentos:
                <span>S/ <?php echo number_format(htmlspecialchars($invoice['descuentos']), 2, '.', ','); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center bg-light font-weight-bold">
                <strong>Total Facturado:</strong>
                <span class="fs-4 text-primary">S/ <?php echo number_format(htmlspecialchars($invoice['monto_total']), 2, '.', ','); ?></span>
            </li>
        </ul>

        <?php if (!empty($invoice['booking_id'])): ?>
        <h5 class="mt-4">Detalles de Reserva Asociada:</h5>
        <ul class="list-group mb-3">
            <li class="list-group-item">
                <strong>Reserva ID:</strong> <a href="/hotel_completo/public/bookings/edit/<?php echo htmlspecialchars($invoice['booking_id']); ?>"><?php echo htmlspecialchars($invoice['booking_id']); ?></a><br>
                <strong>Fechas:</strong> <?php echo htmlspecialchars($invoice['booking_fecha_entrada']); ?> al <?php echo htmlspecialchars($invoice['booking_fecha_salida']); ?><br>
                <strong>Habitación:</strong> <?php echo htmlspecialchars($invoice['numero_habitacion'] ?? 'N/A'); ?><br>
                <strong>Adultos/Niños:</strong> <?php echo htmlspecialchars($invoice['booking_adultos']); ?>/<?php echo htmlspecialchars($invoice['booking_ninos']); ?>
            </li>
        </ul>
        <?php endif; ?>

        <?php if (!empty($invoice['monto_pago'])): ?>
        <h5 class="mt-4">Detalles de Pago:</h5>
        <ul class="list-group mb-3">
            <li class="list-group-item">
                <strong>Monto Pagado:</strong> S/ <?php echo number_format(htmlspecialchars($invoice['monto_pago']), 2, '.', ','); ?><br>
                <strong>Método de Pago:</strong> <?php echo htmlspecialchars($invoice['metodo_pago_principal'] ?? 'N/A'); ?>
            </li>
        </ul>
        <?php endif; ?>


        <div class="d-flex justify-content-end mt-4">
            <a href="/hotel_completo/public/invoicing" class="btn btn-secondary me-2">Volver al Listado</a>
            <?php if ($invoice['estado'] !== 'anulada'): ?>
                <div class="dropdown d-inline-block me-2">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="printDropdownDetail" data-bs-toggle="dropdown" aria-expanded="false" title="Imprimir">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="printDropdownDetail">
                        <li><a class="dropdown-item" href="/hotel_completo/public/invoicing/print_a4/<?php echo htmlspecialchars($invoice['id_factura']); ?>" target="_blank">Imprimir A4</a></li>
                        <li><a class="dropdown-item" href="/hotel_completo/public/invoicing/print_ticket/<?php echo htmlspecialchars($invoice['id_factura']); ?>" target="_blank">Imprimir Ticket</a></li>
                    </ul>
                </div>
                <a href="/hotel_completo/public/invoicing/void/<?php echo htmlspecialchars($invoice['id_factura']); ?>" class="btn btn-danger void-invoice-btn" title="Anular Factura"><i class="fas fa-ban"></i> Anular Factura</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voidButton = document.querySelector('.void-invoice-btn');
    if (voidButton) {
        voidButton.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres ANULAR esta factura? Esta acción es irreversible y puede requerir ajustes manuales en la contabilidad.')) {
                e.preventDefault();
            }
        });
    }
});
</script>