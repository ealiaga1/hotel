<h1 class="mb-4">Editar Cotización: <?php echo htmlspecialchars($quotation['nro_cotizacion'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($quotation) || !$quotation): ?>
    <div class="alert alert-warning" role="alert">Cotización no encontrada.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos de la Cotización</h6>
    </div>
    <div class="card-body">
        <form id="quotationForm" action="/hotel_completo/public/quotations/edit/<?php echo htmlspecialchars($quotation['id_cotizacion']); ?>" method="POST">
            <!-- Sección de Encabezado de Cotización -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Información General</legend>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nro_cotizacion" class="form-label">Nro. Cotización (*)</label>
                        <input type="text" class="form-control" id="nro_cotizacion" name="nro_cotizacion" value="<?php echo htmlspecialchars($quotation['nro_cotizacion']); ?>" readonly required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_cotizacion" class="form-label">Fecha (*)</label>
                        <input type="date" class="form-control" id="fecha_cotizacion" name="fecha_cotizacion" value="<?php echo htmlspecialchars($quotation['fecha_cotizacion'] ? date('Y-m-d', strtotime($quotation['fecha_cotizacion'])) : ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="oferta_valido_dias" class="form-label">Oferta Válida (Días)</label>
                        <input type="number" class="form-control" id="oferta_valido_dias" name="oferta_valido_dias" min="0" value="<?php echo htmlspecialchars($quotation['oferta_valido_dias'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tiempo_entrega_dias" class="form-label">Tiempo de Entrega (Días)</label>
                        <input type="number" class="form-control" id="tiempo_entrega_dias" name="tiempo_entrega_dias" min="0" value="<?php echo htmlspecialchars($quotation['tiempo_entrega_dias'] ?? ''); ?>">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="garantia" class="form-label">Garantía</label>
                        <input type="text" class="form-control" id="garantia" name="garantia" value="<?php echo htmlspecialchars($quotation['garantia'] ?? ''); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="incluido_igv" class="form-label">IGV (*)</label>
                        <select class="form-select" id="incluido_igv" name="incluido_igv" required>
                            <option value="Mas IGV" <?php echo ($quotation['incluido_igv'] == 'Mas IGV') ? 'selected' : ''; ?>>Más IGV (18%)</option>
                            <option value="Incluido IGV" <?php echo ($quotation['incluido_igv'] == 'Incluido IGV') ? 'selected' : ''; ?>>Incluido IGV</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="moneda" class="form-label">Moneda (*)</label>
                        <select class="form-select" id="moneda" name="moneda" required>
                            <option value="Soles" <?php echo ($quotation['moneda'] == 'Soles') ? 'selected' : ''; ?>>Soles (S/)</option>
                            <option value="Dolares" <?php echo ($quotation['moneda'] == 'Dolares') ? 'selected' : ''; ?>>Dólares ($)</option>
                            <option value="Euro" <?php echo ($quotation['moneda'] == 'Euro') ? 'selected' : ''; ?>>Euro (€)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tipo_cambio" class="form-label">Tipo de Cambio</label>
                        <input type="number" class="form-control" id="tipo_cambio" name="tipo_cambio" step="0.0001" min="0.0001" value="<?php echo htmlspecialchars($quotation['tipo_cambio'] ?? '1.0000'); ?>" <?php echo ($quotation['moneda'] == 'Soles') ? 'readonly' : ''; ?>>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_vendedor" class="form-label">Vendedor (*)</label>
                        <select class="form-select" id="id_vendedor" name="id_vendedor" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id_usuario']); ?>" <?php echo ($quotation['id_vendedor'] == $user['id_usuario']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nombre_usuario'] . ' ' . $user['apellido_usuario']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="condicion" class="form-label">Condición (*)</label>
                        <select class="form-select" id="condicion" name="condicion" required>
                            <option value="Contado" <?php echo ($quotation['condicion'] == 'Contado') ? 'selected' : ''; ?>>Contado</option>
                            <option value="Credito" <?php echo ($quotation['condicion'] == 'Credito') ? 'selected' : ''; ?>>Crédito</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="atencion" class="form-label">Atención (Contacto)</label>
                    <input type="text" class="form-control" id="atencion" name="atencion" value="<?php echo htmlspecialchars($quotation['atencion'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="2"><?php echo htmlspecialchars($quotation['comentario'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado de Cotización (*)</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="Pendiente" <?php echo ($quotation['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Aceptada" <?php echo ($quotation['estado'] == 'Aceptada') ? 'selected' : ''; ?>>Aceptada</option>
                        <option value="Rechazada" <?php echo ($quotation['estado'] == 'Rechazada') ? 'selected' : ''; ?>>Rechazada</option>
                        <option value="Anulada" <?php echo ($quotation['estado'] == 'Anulada') ? 'selected' : ''; ?>>Anulada</option>
                    </select>
                </div>
            </fieldset>

            <!-- Sección de Cliente -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Datos del Cliente</legend>
                <?php $isExistingClient = !empty($quotation['id_cliente']); ?>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleExistingClient" <?php echo !$isExistingClient ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="toggleExistingClient">Cliente Nuevo/Externo</label>
                </div>

                <!-- Campos para Cliente Nuevo/Externo -->
                <div id="newClientFields" style="<?php echo !$isExistingClient ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="cliente_razon_social" class="form-label">Razón Social / Nombre Cliente (*)</label>
                        <input type="text" class="form-control" id="cliente_razon_social" name="cliente_razon_social" value="<?php echo htmlspecialchars($quotation['cliente_razon_social'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cliente_ruc_dni" class="form-label">RUC/DNI Cliente</label>
                        <input type="text" class="form-control" id="cliente_ruc_dni" name="cliente_ruc_dni" value="<?php echo htmlspecialchars($quotation['cliente_ruc_dni'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="cliente_direccion" class="form-label">Dirección Cliente</label>
                        <input type="text" class="form-control" id="cliente_direccion" name="cliente_direccion" value="<?php echo htmlspecialchars($quotation['cliente_direccion'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="cliente_email" class="form-label">Email Cliente</label>
                        <input type="email" class="form-control" id="cliente_email" name="cliente_email" value="<?php echo htmlspecialchars($quotation['cliente_email'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Campos para Cliente Existente (Huésped) -->
                <div id="existingClientFields" style="<?php echo $isExistingClient ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="searchGuestQuotation" class="form-label">Buscar Huésped Existente (*)</label>
                        <input type="text" class="form-control" id="searchGuestQuotation" placeholder="Nombre, Apellido, Email o DNI" value="<?php echo htmlspecialchars(($quotation['huesped_nombre'] ?? '') . ' ' . ($quotation['huesped_apellido'] ?? '') . (!empty($quotation['huesped_nombre']) ? ' (' . ($quotation['cliente_ruc_dni'] ?? '') . ')' : '')); ?>">
                        <div id="guestSearchResultsQuotation" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedGuestQuotationDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestQuotationDisplay" readonly value="<?php echo htmlspecialchars(($quotation['huesped_nombre'] ?? '') . ' ' . ($quotation['huesped_apellido'] ?? '') . (!empty($quotation['huesped_nombre']) ? ' (' . ($quotation['cliente_ruc_dni'] ?? '') . ')' : '')); ?>">
                        <input type="hidden" name="id_cliente_existente" id="idClientExisting" value="<?php echo htmlspecialchars($quotation['id_cliente'] ?? ''); ?>">
                        <!-- Campos ocultos para precargar datos si se selecciona un huésped -->
                        <input type="hidden" name="precargado_cliente_razon_social" id="precargadoClienteRazonSocial" value="<?php echo htmlspecialchars($quotation['cliente_razon_social'] ?? ''); ?>">
                        <input type="hidden" name="precargado_cliente_ruc_dni" id="precargadoClienteRucDni" value="<?php echo htmlspecialchars($quotation['cliente_ruc_dni'] ?? ''); ?>">
                        <input type="hidden" name="precargado_cliente_direccion" id="precargadoClienteDireccion" value="<?php echo htmlspecialchars($quotation['cliente_direccion'] ?? ''); ?>">
                        <input type="hidden" name="precargado_cliente_email" id="precargadoClienteEmail" value="<?php echo htmlspecialchars($quotation['cliente_email'] ?? ''); ?>">
                    </div>
                </div
            </fieldset>

            <!-- Sección de Ítems de Cotización -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Ítems de la Cotización (*)</legend>
                <div class="mb-3">
                    <label for="selectProductForQuotation" class="form-label">Añadir Producto de Inventario</label>
                    <select class="form-select" id="selectProductForQuotation">
                        <option value="">Buscar y Seleccionar Producto</option>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['id_producto']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($product['nombre_producto']); ?>"
                                        data-precio="<?php echo htmlspecialchars($product['precio_compra']); ?>"
                                        data-unidad="<?php echo htmlspecialchars($product['unidad_medida']); ?>">
                                    <?php echo htmlspecialchars($product['nombre_producto']); ?> (S/<?php echo number_format(htmlspecialchars($product['precio_compra']), 2); ?> / <?php echo htmlspecialchars($product['unidad_medida']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">Seleccione un producto de inventario para añadir o describa uno nuevo.</small>
                </div>
                <div class="mb-3">
                    <button type="button" id="addItemButton" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Añadir Ítem</button>
                    <button type="button" id="addNewItemButton" class="btn btn-outline-success btn-sm"><i class="fas fa-plus-circle"></i> Añadir Ítem Nuevo</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cant.</th>
                                <th>P. Unit.</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="quotationItemsTableBody">
                            <!-- Items se añadirán aquí con JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <th id="subtotalDisplay" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">IGV (18%):</th>
                                <th id="igvDisplay" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th id="totalDisplay" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="quotation_items_json" id="quotationItemsJson">
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/quotations" class="btn btn-secondary me-2">Regresar</a>
                <button type="submit" class="btn btn-primary">Actualizar Cotización</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo $base_url_for_assets; ?>';
    const IGV_RATE = 0.18;

    // --- Elementos del Encabezado ---
    const monedaSelect = document.getElementById('moneda');
    const tipoCambioInput = document.getElementById('tipo_cambio');
    const incluidoIgvSelect = document.getElementById('incluido_igv');

    monedaSelect.addEventListener('change', function() {
        if (this.value === 'Soles') {
            tipoCambioInput.value = '1.0000';
            tipoCambioInput.setAttribute('readonly', 'readonly');
        } else {
            tipoCambioInput.removeAttribute('readonly');
        }
        updateTotals();
    });

    incluidoIgvSelect.addEventListener('change', updateTotals);

    // --- Elementos de Cliente ---
    const toggleExistingClient = document.getElementById('toggleExistingClient');
    const newClientFields = document.getElementById('newClientFields');
    const existingClientFields = document.getElementById('existingClientFields');

    const clienteRazonSocialInput = document.getElementById('cliente_razon_social');
    const clienteRucDniInput = document.getElementById('cliente_ruc_dni');
    const clienteDireccionInput = document.getElementById('cliente_direccion');
    const clienteEmailInput = document.getElementById('cliente_email');

    const searchGuestQuotationInput = document.getElementById('searchGuestQuotation');
    const guestSearchResultsQuotation = document.getElementById('guestSearchResultsQuotation');
    const idClientExistingInput = document.getElementById('idClientExisting');
    const selectedGuestQuotationDisplay = document.getElementById('selectedGuestQuotationDisplay');

    const precargadoClienteRazonSocial = document.getElementById('precargadoClienteRazonSocial');
    const precargadoClienteRucDni = document.getElementById('precargadoClienteRucDni');
    const precargadoClienteDireccion = document.getElementById('precargadoClienteDireccion');
    const precargadoClienteEmail = document.getElementById('precargadoClienteEmail');


    function toggleClientFields() {
        if (toggleExistingClient.checked) { // Cliente Nuevo/Externo
            newClientFields.style.display = 'block';
            existingClientFields.style.display = 'none';
            clienteRazonSocialInput.setAttribute('required', 'required');
            searchGuestQuotationInput.removeAttribute('required');
            idClientExistingInput.removeAttribute('required');

            idClientExistingInput.value = '';
            selectedGuestQuotationDisplay.value = '';
            guestSearchResultsQuotation.innerHTML = '';

            // Limpiar precarga
            precargadoClienteRazonSocial.value = '';
            precargadoClienteRucDni.value = '';
            precargadoClienteDireccion.value = '';
            precargadoClienteEmail.value = '';

            // Limpiar campos de cliente nuevo si se estaban usando para un existente
            clienteRazonSocialInput.value = '';
            clienteRucDniInput.value = '';
            clienteDireccionInput.value = '';
            clienteEmailInput.value = '';


        } else { // Cliente Existente (Huésped)
            newClientFields.style.display = 'none';
            existingClientFields.style.display = 'block';
            clienteRazonSocialInput.removeAttribute('required');
            
            searchGuestQuotationInput.setAttribute('required', 'required');
            idClientExistingInput.setAttribute('required', 'required');

            // Cargar datos del huésped seleccionado si ya existe un ID
            if (idClientExistingInput.value && precargadoClienteRazonSocial.value) {
                clienteRazonSocialInput.value = precargadoClienteRazonSocial.value;
                clienteRucDniInput.value = precargadoClienteRucDni.value;
                clienteDireccionInput.value = precargadoClienteDireccion.value;
                clienteEmailInput.value = precargadoClienteEmail.value;
            } else { // Si no hay huésped existente precargado, limpiar los campos de cliente_
                clienteRazonSocialInput.value = '';
                clienteRucDniInput.value = '';
                clienteDireccionInput.value = '';
                clienteEmailInput.value = '';
            }
        }
    }
    toggleExistingClient.addEventListener('change', toggleClientFields);
    // Para edición, si ya hay un id_cliente, iniciar en el modo "Existente"
    if (idClientExistingInput.value) {
        toggleExistingClient.checked = false; // Desmarcar "Cliente Nuevo/Externo"
    }
    toggleClientFields(); // Set initial state


    // Búsqueda de Huéspedes para Cotización (AJAX)
    let searchGuestQuotationTimeout;
    searchGuestQuotationInput.addEventListener('input', function() {
        clearTimeout(searchGuestQuotationTimeout);
        const query = this.value;
        if (query.length < 3) {
            guestSearchResultsQuotation.innerHTML = '';
            return;
        }
        searchGuestQuotationTimeout = setTimeout(() => {
            fetch(`${baseUrl}quotations/search_guests_ajax?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    guestSearchResultsQuotation.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(guest => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.textContent = `${guest.nombre} ${guest.apellido} (${guest.numero_documento || guest.email || guest.telefono})`;
                            item.dataset.id = guest.id_huesped;
                            item.dataset.razonSocial = `${guest.nombre} ${guest.apellido}`;
                            item.dataset.rucDni = guest.numero_documento || '';
                            item.dataset.direccion = guest.direccion || '';
                            item.dataset.email = guest.email || '';

                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                idClientExistingInput.value = this.dataset.id;
                                selectedGuestQuotationDisplay.value = this.textContent;
                                guestSearchResultsQuotation.innerHTML = '';

                                // Precargar datos en campos ocultos para el POST
                                precargadoClienteRazonSocial.value = this.dataset.razonSocial;
                                precargadoClienteRucDni.value = this.dataset.rucDni;
                                precargadoClienteDireccion.value = this.dataset.direccion;
                                precargadoClienteEmail.value = this.dataset.email;

                                // Asegurar que los campos visibles tomen los valores del existente si se vuelven visibles
                                clienteRazonSocialInput.value = this.dataset.razonSocial;
                                clienteRucDniInput.value = this.dataset.rucDni;
                                clienteDireccionInput.value = this.dataset.direccion;
                                clienteEmailInput.value = this.dataset.email;
                            });
                            guestSearchResultsQuotation.appendChild(item);
                        });
                    } else {
                        guestSearchResultsQuotation.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                    }
                })
                .catch(error => console.error('Error buscando huéspedes:', error));
        }, 300);
    });


    // --- Ítems de Cotización ---
    const selectProductForQuotation = document.getElementById('selectProductForQuotation');
    const addItemButton = document.getElementById('addItemButton');
    const addNewItemButton = document.getElementById('addNewItemButton');
    const quotationItemsTableBody = document.getElementById('quotationItemsTableBody');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const igvDisplay = document.getElementById('igvDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    const quotationItemsJsonInput = document.getElementById('quotationItemsJson');

    let quotationItems = <?php echo json_encode($quotation['items'] ?? []); ?>; // Cargar ítems existentes para edición

    function updateTotals() {
        let currentSubtotal = 0;
        quotationItems.forEach(item => {
            currentSubtotal += (parseFloat(item.cantidad) * parseFloat(item.precio_unitario));
        });
        currentSubtotal = parseFloat(currentSubtotal.toFixed(2));

        let currentIgv = 0;
        if (incluidoIgvSelect.value === 'Mas IGV') {
            currentIgv = parseFloat((currentSubtotal * IGV_RATE).toFixed(2));
        }
        
        let currentTotal = parseFloat((currentSubtotal + currentIgv).toFixed(2));

        subtotalDisplay.textContent = `S/ ${currentSubtotal.toFixed(2)}`;
        igvDisplay.textContent = `S/ ${currentIgv.toFixed(2)}`;
        totalDisplay.textContent = `S/ ${currentTotal.toFixed(2)}`;

        quotationItemsJsonInput.value = JSON.stringify(quotationItems);
    }

    function renderQuotationItemsTable() {
        quotationItemsTableBody.innerHTML = '';
        if (quotationItems.length === 0) {
            quotationItemsTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Añada ítems a la cotización.</td></tr>';
        } else {
            quotationItems.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${htmlspecialchars(item.descripcion_item)}</td>
                    <td><input type="number" class="form-control form-control-sm item-quantity" data-index="${index}" value="${htmlspecialchars(item.cantidad)}" min="0.01" step="0.01" style="width: 80px;"></td>
                    <td>S/ <input type="number" class="form-control form-control-sm item-price" data-index="${index}" value="${htmlspecialchars(item.precio_unitario)}" min="0.00" step="0.01" style="width: 100px;"></td>
                    <td class="item-subtotal text-end">S/ ${parseFloat(item.cantidad * item.precio_unitario).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item-btn" data-index="${index}"><i class="fas fa-trash-alt"></i></button></td>
                `;
                quotationItemsTableBody.appendChild(row);
            });
        }
        updateTotals();
    }

    addItemButton.addEventListener('click', function() {
        const selectedProductOption = selectProductForQuotation.options[selectProductForQuotation.selectedIndex];
        if (selectedProductOption.value) {
            const productId = selectedProductOption.value;
            const productName = selectedProductOption.dataset.nombre;
            const productPrice = parseFloat(selectedProductOption.dataset.precio);
            const productUnit = selectedProductOption.dataset.unidad;

            const existingItemIndex = quotationItems.findIndex(item => item.id_producto === productId);
            if (existingItemIndex > -1) {
                quotationItems[existingItemIndex].cantidad++;
            } else {
                quotationItems.push({
                    id_producto: productId,
                    descripcion_item: `${productName} (${productUnit})`,
                    cantidad: 1,
                    precio_unitario: productPrice,
                    subtotal: productPrice
                });
            }
            renderQuotationItemsTable();
            selectProductForQuotation.value = "";
        } else {
            alert('Por favor, seleccione un producto del inventario o use "Añadir Ítem Nuevo".');
        }
    });

    addNewItemButton.addEventListener('click', function() {
        const newItemDescription = prompt('Ingrese la descripción del nuevo ítem:');
        if (newItemDescription) {
            const newItemQuantity = parseFloat(prompt('Ingrese la cantidad:')) || 1;
            const newItemPrice = parseFloat(prompt('Ingrese el precio unitario:')) || 0;

            if (newItemQuantity > 0 && newItemPrice >= 0) {
                quotationItems.push({
                    id_producto: null,
                    descripcion_item: newItemDescription,
                    cantidad: newItemQuantity,
                    precio_unitario: newItemPrice,
                    subtotal: newItemQuantity * newItemPrice
                });
                renderQuotationItemsTable();
            } else {
                alert('Cantidad debe ser positiva y Precio unitario no negativo.');
            }
        }
    });

    quotationItemsTableBody.addEventListener('change', function(e) {
        const target = e.target;
        if (target.classList.contains('item-quantity') || target.classList.contains('item-price')) {
            const index = target.dataset.index;
            const item = quotationItems[index];

            if (target.classList.contains('item-quantity')) {
                item.cantidad = parseFloat(target.value) || 0;
                if (item.cantidad <= 0) {
                    alert('La cantidad debe ser mayor a 0.');
                    item.cantidad = 1;
                    target.value = 1;
                }
            } else if (target.classList.contains('item-price')) {
                item.precio_unitario = parseFloat(target.value) || 0;
                if (item.precio_unitario < 0) {
                    alert('El precio no puede ser negativo.');
                    item.precio_unitario = 0;
                    target.value = 0;
                }
            }
            item.subtotal = parseFloat((item.cantidad * item.precio_unitario).toFixed(2));
            updateTotals(); // Actualizar totales después de un cambio en la cantidad/precio
            renderQuotationItemsTable(); // Re-render para actualizar subtotal en la fila
        }
    });

    quotationItemsTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn') || e.target.closest('.remove-item-btn')) {
            const button = e.target.closest('.remove-item-btn');
            const index = button.dataset.index;
            quotationItems.splice(index, 1);
            renderQuotationItemsTable();
        }
    });

    // Función para escapar HTML, como htmlspecialchars en PHP
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // Inicializar la tabla de ítems y totales
    renderQuotationItemsTable();
    updateTotals(); // Llamada inicial para asegurar que los totales se calculen al cargar la página.
});
</script>
