<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($invoice['serie_documento'] ?? ''); ?>-<?php echo htmlspecialchars($invoice['numero_documento'] ?? ''); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10pt;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact; /* Para navegadores basados en Webkit */
            print-color-adjust: exact; /* Estándar */
        }
        .invoice-container {
            width: 210mm; /* Ancho de una hoja A4 */
            min-height: 297mm; /* Alto de una hoja A4 */
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20mm;
            box-sizing: border-box;
            background-color: #fff;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #666;
        }
        .invoice-details, .customer-details {
            width: 100%;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .invoice-details div, .customer-details div {
            width: 48%;
            border: 1px solid #eee;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
        }
        table th {
            background-color: #e9e9e9;
            color: #333;
            font-weight: bold;
        }
        .total-section {
            width: 40%;
            margin-left: auto;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f0f0f0;
        }
        .total-section p {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
        }
        .total-section .grand-total {
            font-size: 12pt;
            font-weight: bold;
            color: #007bff;
        }
        .notes {
            margin-top: 30px;
            font-size: 9pt;
            color: #555;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 8pt;
            color: #888;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 10pt;
            }
            .invoice-container {
                width: 100%;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="invoice-container">
        <div class="header">
            <?php if (!empty($companySettings['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($companySettings['logo_url']); ?>" alt="Logo Empresa" style="max-height: 80px; margin-bottom: 10px;"><br>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($companySettings['nombre_empresa'] ?? 'HOTEL GESTIÓN'); ?></h1>
            <p>RUC: <?php echo htmlspecialchars($companySettings['ruc'] ?? 'N/A'); ?></p>
            <p>Dirección: <?php echo htmlspecialchars($companySettings['direccion'] ?? 'N/A'); ?></p>
            <p>Teléfono: <?php echo htmlspecialchars($companySettings['telefono'] ?? 'N/A'); ?> | Email: <?php echo htmlspecialchars($companySettings['email'] ?? 'N/A'); ?></p>
        </div>

        <hr>

        <div class="section-title">FACTURA DE VENTA</div>
        <div class="invoice-details">
            <div>
                <p><strong>Número de Documento:</strong> <?php echo htmlspecialchars($invoice['tipo_documento'] ?? 'N/A'); ?> <?php echo htmlspecialchars($invoice['serie_documento'] ?? ''); ?>-<?php echo htmlspecialchars($invoice['numero_documento'] ?? ''); ?></p>
                <p><strong>Fecha de Emisión:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($invoice['fecha_emision'] ?? ''))); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars(ucfirst($invoice['estado'] ?? '')); ?></p>
            </div>
            <div>
                <p><strong>Factura ID:</strong> <?php echo htmlspecialchars($invoice['id_factura'] ?? ''); ?></p>
                <?php if (!empty($invoice['booking_id'])): ?>
                    <p><strong>Reserva ID:</strong> <?php echo htmlspecialchars($invoice['booking_id']); ?></p>
                    <p><strong>Habitación:</strong> <?php echo htmlspecialchars($invoice['numero_habitacion'] ?? 'N/A'); ?></p>
                <?php endif; ?>
                <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($invoice['metodo_pago_principal'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="section-title">Datos del Huésped</div>
        <div class="customer-details">
            <div>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($invoice['huesped_nombre'] ?? '') . ' ' . htmlspecialchars($invoice['huesped_apellido'] ?? ''); ?></p>
                <p><strong>Doc. Identidad:</strong> <?php echo htmlspecialchars($invoice['huesped_documento'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($invoice['huesped_email'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="section-title">Detalles del Cargo</div>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($invoice['booking_id'])): // Si está asociada a una reserva de alojamiento
                    $num_nights = 0;
                    if (!empty($invoice['booking_fecha_entrada']) && !empty($invoice['booking_fecha_salida'])) {
                        try {
                            $diff = date_diff(date_create($invoice['booking_fecha_entrada']), date_create($invoice['booking_fecha_salida']));
                            $num_nights = $diff->days;
                        } catch (Exception $e) {
                            $num_nights = 0; // En caso de error, 0 noches
                        }
                    }
                    $precio_base_noche = 0;
                    if ($num_nights > 0) {
                         $precio_base_noche = ($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']) / $num_nights;
                    }
                ?>
                    <tr>
                        <td>Alojamiento (Reserva #<?php echo htmlspecialchars($invoice['booking_id']); ?>)</td>
                        <td><?php echo htmlspecialchars($num_nights); ?> Noches</td>
                        <td>S/ <?php echo number_format(htmlspecialchars($precio_base_noche), 2, '.', ','); ?></td>
                        <td>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                    </tr>
                <?php else: // Si no está asociada a una reserva de alojamiento (ej. venta directa consolidada, pero sin detalle de ítems) ?>
                     <tr>
                        <td>Servicios Varios / Venta Directa</td>
                        <td>1</td>
                        <td>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                        <td>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></td>
                    </tr>
                <?php endif; ?>
                <!-- Si se implementaran ítems detallados en la factura (ej. de cargos_huesped), irían aquí -->
            </tbody>
        </table>

        <div class="total-section">
            <p>Subtotal: <span>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total'] - $invoice['impuestos'] + $invoice['descuentos']), 2, '.', ','); ?></span></p>
            <p>Impuestos (<?php echo (0.18 * 100); ?>%): <span>S/ <?php echo number_format(htmlspecialchars($invoice['impuestos']), 2, '.', ','); ?></span></p>
            <p>Descuentos: <span>S/ <?php echo number_format(htmlspecialchars($invoice['descuentos']), 2, '.', ','); ?></span></p>
            <p class="grand-total">Total: <span>S/ <?php echo number_format(htmlspecialchars($invoice['monto_total']), 2, '.', ','); ?></span></p>
        </div>

        <div class="notes">
            <p>Gracias por su preferencia.</p>
        </div>
    </div>
</body>
</html>

