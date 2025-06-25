<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo <?php echo htmlspecialchars($invoice['serie_documento'] ?? ''); ?>-<?php echo htmlspecialchars($invoice['numero_documento'] ?? ''); ?></title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace; /* Fuente monoespaciada para estilo de ticket */
            margin: 0;
            padding: 10px;
            font-size: 9pt; /* Tamaño de letra más pequeño para ticket */
            width: 80mm; /* Ancho típico de impresora térmica de recibos (puede necesitar ajuste) */
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            line-height: 1.4;
            color: #333;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 12pt;
            margin: 0;
            color: #000;
        }
        .header p {
            margin: 2px 0;
            font-size: 8pt;
        }
        .divider {
            border-top: 1px dashed #aaa;
            margin: 10px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .info-row strong {
            flex-shrink: 0;
            margin-right: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .items-table th, .items-table td {
            padding: 5px 0;
            text-align: left;
            font-size: 9pt;
            border-bottom: 1px dotted #ccc;
        }
        .items-table th {
            font-weight: bold;
        }
        .items-table td:nth-child(2) { text-align: center; } /* Cantidad */
        .items-table td:nth-child(3) { text-align: right; } /* Precio unitario */
        .items-table td:nth-child(4) { text-align: right; } /* Subtotal */
        
        .totals-summary {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #aaa;
        }
        .totals-summary p {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 10pt;
        }
        .totals-summary .grand-total {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }
        .notes {
            margin-top: 15px;
            font-size: 8pt;
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-ticket-container">
        <div class="header">
            <?php if (!empty($companySettings['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($companySettings['logo_url']); ?>" alt="Logo Empresa" style="max-height: 50px; margin-bottom: 5px;"><br>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($companySettings['nombre_empresa'] ?? 'HOTEL GESTIÓN'); ?></h1>
            <p>RUC: <?php echo htmlspecialchars($companySettings['ruc'] ?? 'N/A'); ?></p>
            <p><?php echo htmlspecialchars($companySettings['direccion'] ?? 'N/A'); ?></p>
            <p>Tel: <?php echo htmlspecialchars($companySettings['telefono'] ?? 'N/A'); ?></p>
        </div>

        <div class="divider"></div>

        <div class="info-row">
            <span><strong>Doc:</strong> <?php echo htmlspecialchars($invoice['tipo_documento'] ?? 'N/A'); ?> <?php echo htmlspecialchars($invoice['serie_documento'] ?? ''); ?>-<?php echo htmlspecialchars($invoice['numero_documento'] ?? ''); ?></span>
        </div>
        <div class="info-row">
            <span><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['fecha_emision'] ?? ''))); ?></span>
        </div>
        <div class="info-row">
            <span><strong>Huésped:</strong> <?php echo htmlspecialchars($invoice['huesped_nombre'] ?? '') . ' ' . htmlspecialchars($invoice['huesped_apellido'] ?? ''); ?></span>
        </div>
        <?php if (!empty($invoice['booking_id'])): ?>
        <div class="info-row">
            <span><strong>Reserva:</strong> #<?php echo htmlspecialchars($invoice['booking_id']); ?></span>
        </div>
        <div class="info-row">
            <span><strong>Hab.:</strong> <?php echo htmlspecialchars($invoice['numero_habitacion'] ?? 'N/A'); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span><strong>Pago:</strong> <?php echo htmlspecialchars($invoice['metodo_pago_principal'] ?? 'N/A'); ?></span>
        </div>

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Desc.</th>
                    <th>Cant.</th>
                    <th>P.Unit.</th>
                    <th>Subt.</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($invoice['booking_id'])):
                    $num_nights = 0;
                    if (!empty($invoice['booking_fecha_entrada']) && !empty($invoice['booking_fecha_salida'])) {
                        try {
                            $diff = date_diff(date_create($invoice['booking_fecha_entrada']), date_create($invoice['booking_fecha_salida']));
                            $num_nights = $diff->days;
                        } catch (Exception $e) {
                            $num_nights = 0;
                        }
                    }
                    $precio_base_noche = 0;
                    if ($num_nights > 0) {
                         $precio_base_noche = ($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']) / $num_nights;
                    }
                ?>
                    <tr>
                        <td>Alojamiento (Res. #<?php echo htmlspecialchars($invoice['booking_id']); ?>)</td>
                        <td><?php echo htmlspecialchars($num_nights); ?></td>
                        <td>S/ <?php echo number_format(htmlspecialchars($precio_base_noche), 2, '.', ','); ?></td>
                        <td>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td>Servicios Varios / Venta Directa</td>
                        <td>1</td>
                        <td><?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                        <td><?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="totals-summary">
            <p>Subtotal: <span>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></span></p>
            <p>IGV: <span>S/ <?php echo number_format(htmlspecialchars($invoice['impuestos']), 2, '.', ','); ?></span></p>
            <p>TOTAL: <span>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total']), 2, '.', ','); ?></span></p>
        </div>

        <div class="divider"></div>
        <div class="notes">
            <p>¡Gracias por su visita!</p>
            <p>Vuelva Pronto.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo htmlspecialchars($companySettings['nombre_empresa'] ?? 'Hotel Gestión'); ?></p>
        </div>
    </div>
</body>
</html>

