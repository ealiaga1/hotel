<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte POS - Caja #<?php echo htmlspecialchars($register_id ?? ''); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            padding: 0;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }
        .report-container {
            width: 210mm; /* A4 width */
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            margin: 0;
            color: #007bff;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #666;
        }
        .section {
            margin-bottom: 15px;
        }
        .section h2 {
            font-size: 14pt;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #007bff;
        }
        .report-info, .operation-amounts {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Espacio entre los items */
        }
        .report-info div, .operation-amounts div {
            flex: 1 1 48%; /* Dos columnas aproximadamente */
        }
        .report-info p, .operation-amounts p {
            margin: 0;
            padding: 2px 0;
        }
        .total-summary {
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        .total-summary p {
            font-size: 11pt;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .total-summary p span:last-child {
            color: #007bff;
        }
        .note {
            margin-top: 30px;
            font-size: 9pt;
            color: #555;
            text-align: center;
        }

        /* Estilos para impresión */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 9pt;
            }
            .report-container {
                width: 100%;
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .header, .footer {
                margin-bottom: 10px;
            }
            .section {
                margin-bottom: 10px;
            }
            .section h2 {
                font-size: 12pt;
                padding-bottom: 3px;
                margin-bottom: 5px;
            }
            .report-info div, .operation-amounts div {
                flex: 1 1 45%; /* Ajuste para impresión */
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="report-container">
        <div class="header">
            <?php if (!empty($companyLogoUrl)): ?>
                <img src="<?php echo htmlspecialchars($companyLogoUrl); ?>" alt="Logo Empresa" style="max-height: 80px; margin-bottom: 10px;"><br>
            <?php endif; ?>
            <h1>Reporte Punto de Venta</h1>
            <p>Empresa: <?php echo htmlspecialchars($companyName ?? 'N/A'); ?></p>
            <p>RUC: <?php echo htmlspecialchars($companyRuc ?? 'N/A'); ?> Establecimiento: <?php echo htmlspecialchars($companyAddress ?? 'N/A'); ?></p>
            <p>Teléfono: <?php echo htmlspecialchars($companyPhone ?? 'N/A'); ?> | Email: <?php echo htmlspecialchars($companyEmail ?? 'N/A'); ?></p>
            <p>Fecha reporte: <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($fechaReporte))); ?></p>
        </div>

        <hr>

        <div class="section">
            <h2>Información del Turno de Caja</h2>
            <div class="report-info">
                <div><p><strong>Vendedor:</strong> <?php echo htmlspecialchars($sellerName); ?></p></div>
                <div><p><strong>Estado de caja:</strong> <?php echo htmlspecialchars(ucfirst($estadoCaja)); ?></p></div>
                <div><p><strong>Fecha y hora apertura:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($fechaApertura))); ?></p></div>
                <div><p><strong>Fecha y hora cierre:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($fechaCierre))); ?></p></div>
            </div>
        </div>

        <div class="section">
            <h2>Montos de Operación</h2>
            <div class="operation-amounts">
                <div><p><strong>Saldo inicial:</strong> S/ <?php echo number_format(htmlspecialchars($saldoInicial), 2, '.', ','); ?></p></div>
                <div><p><strong>Ingreso efectivo:</strong> S/ <?php echo number_format(htmlspecialchars($ingresoEfectivo), 2, '.', ','); ?></p></div>
                <div><p><strong>Egreso efectivo:</strong> S/ <?php echo number_format(htmlspecialchars($egresoEfectivo), 2, '.', ','); ?></p></div>
                <div><p><strong>Total efectivo (Calculado):</strong> S/ <?php echo number_format(htmlspecialchars($totalEfectivoCalculado), 2, '.', ','); ?></p></div>
                
                <div><p><strong>Ingreso Billeteras/Tarjetas:</strong> S/ <?php echo number_format(htmlspecialchars($billeterasDigitalIngreso), 2, '.', ','); ?></p></div>
                <div><p><strong>Egreso Billeteras/Tarjetas:</strong> S/ <?php echo number_format(htmlspecialchars($billeterasDigitalEgreso), 2, '.', ','); ?></p></div>
                
                <div><p><strong>Ingreso Transferencia Bancaria:</strong> S/ <?php echo number_format(htmlspecialchars($transferenciasBancariasIngreso), 2, '.', ','); ?></p></div>
                <div><p><strong>Egreso Transferencia Bancaria:</strong> S/ <?php echo number_format(htmlspecialchars($transferenciasBancariasEgreso), 2, '.', ','); ?></p></div>
            </div>
        </div>

        <div class="section">
            <h2>Resumen de Comprobantes</h2>
            <div class="operation-amounts">
                <div><p><strong>Total Facturas:</strong> S/ <?php echo number_format(htmlspecialchars($totalCPE), 2, '.', ','); ?></p></div>
                <div><p><strong>Total Boletas:</strong> S/ <?php echo number_format(htmlspecialchars($totalNotaVenta), 2, '.', ','); ?></p></div>
            </div>
        </div>
        
        <div class="section">
            <h2>Otros Totales</h2>
            <div class="operation-amounts">
                <div><p><strong>Por cobrar (Cargos a Huésped):</strong> S/ <?php echo number_format(htmlspecialchars($porCobrar), 2, '.', ','); ?></p></div>
                <div><p><strong>Total Propinas:</strong> S/ <?php echo number_format(htmlspecialchars($totalPropinas), 2, '.', ','); ?></p></div>
            </div>
        </div>

        <div class="total-summary">
            <p><strong>Total de Caja (Neto):</strong> <span>S/ <?php echo number_format(htmlspecialchars($totalCajaCalculado), 2, '.', ','); ?></span></p>
        </div>

        <div class="note">
            <p>Este reporte es para uso interno y refleja los movimientos registrados en el turno de caja.</p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo htmlspecialchars($companySettings['nombre_empresa'] ?? 'Hotel Gestión'); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
