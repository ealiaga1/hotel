<h1 class="mb-4">Historial de Cierres de Caja</h1>

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

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Registros de Cajas Cerradas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="cashHistoryTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Turno</th>
                        <th>Abierto por</th>
                        <th>Fecha Apertura</th>
                        <th>Cerrado por</th>
                        <th>Fecha Cierre</th>
                        <th>Saldo Inicial</th>
                        <th>Ingresos</th>
                        <th>Egresos</th>
                        <th>Saldo Final Contado</th>
                        <th>Diferencia</th>
                        <th>Acciones</th> <!-- Nueva columna para el botón de reporte -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($closedRegisters)): ?>
                        <?php foreach ($closedRegisters as $register): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($register['id_movimiento_caja']); ?></td>
                                <td><?php echo htmlspecialchars($register['apertura_nombre'] . ' ' . $register['apertura_apellido']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($register['fecha_apertura']))); ?></td>
                                <td><?php echo htmlspecialchars($register['cierre_nombre'] . ' ' . $register['cierre_apellido'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($register['fecha_cierre']))); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($register['saldo_inicial']), 2, '.', ','); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($register['total_ingresos']), 2, '.', ','); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($register['total_egresos']), 2, '.', ','); ?></td>
                                <td>S/ <?php echo number_format(htmlspecialchars($register['saldo_final']), 2, '.', ','); ?></td>
                                <td>
                                    <?php
                                    $calculated_balance = $register['saldo_inicial'] + $register['total_ingresos'] - $register['total_egresos'];
                                    $difference = $register['saldo_final'] - $calculated_balance;
                                    $diffClass = ($difference == 0) ? 'text-success' : (($difference > 0) ? 'text-primary' : 'text-danger');
                                    ?>
                                    <span class="<?php echo $diffClass; ?>">S/ <?php echo number_format(htmlspecialchars($difference), 2, '.', ','); ?></span>
                                </td>
                                <td>
                                    <!-- Botón para ver Reporte POS de este turno -->
                                    <a href="/hotel_completo/public/cash_register/pos_report/<?php echo htmlspecialchars($register['id_movimiento_caja']); ?>" target="_blank" class="btn btn-sm btn-info" title="Ver Reporte POS">
                                        <i class="fas fa-file-alt"></i> POS
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">No hay registros de cierres de caja.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
