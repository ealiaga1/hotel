<h1 class="mb-4">Crear Nuevo Pedido</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles del Pedido</h6>
    </div>
    <div class="card-body">
        <form id="orderForm" action="/hotel_completo/public/restaurant/orders/create" method="POST">
            <!-- Tipo de Pedido: Mesa, Habitación, Externo -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Tipo de Pedido</legend>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_pedido" id="tipoPedidoMesa" value="mesa" <?php echo (($preselected_client_type ?? 'mesa') === 'mesa') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="tipoPedidoMesa">En Mesa</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_pedido" id="tipoPedidoHabitacion" value="habitacion" <?php echo (($preselected_client_type ?? '') === 'habitacion') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="tipoPedidoHabitacion">A Habitación (Huésped)</label>
                    </div>
                     <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tipo_pedido" id="tipoPedidoExterno" value="externo" <?php echo (($preselected_client_type ?? '') === 'externo') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="tipoPedidoExterno">Cliente Externo</label>
                    </div>
                </div>

                <!-- Campos para Pedido en Mesa -->
                <div id="fieldsTipoMesa" style="<?php echo (($preselected_client_type ?? 'mesa') === 'mesa') ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="id_mesa" class="form-label">Mesa (*)</label>
                        <select class="form-select" id="id_mesa" name="id_mesa">
                            <option value="">Seleccione una mesa</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?php echo htmlspecialchars($table['id_mesa']); ?>" <?php echo (isset($preselected_table_id) && $preselected_table_id == $table['id_mesa']) ? 'selected' : ''; ?>>
                                    Mesa <?php echo htmlspecialchars($table['numero_mesa']); ?> (Cap: <?php echo htmlspecialchars($table['capacidad']); ?>, Estado: <?php echo htmlspecialchars(ucfirst($table['estado'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Campos para Pedido a Habitación (Huésped) -->
                <div id="fieldsTipoHabitacion" style="<?php echo (($preselected_client_type ?? '') === 'habitacion') ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="searchGuestOrder" class="form-label">Buscar Huésped (*)</label>
                        <input type="text" class="form-control" id="searchGuestOrder" placeholder="Nombre, Apellido, Email o DNI">
                        <div id="guestSearchResultsOrder" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedGuestOrderDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestOrderDisplay" readonly>
                        <input type="hidden" name="id_huesped" id="idHuespedOrder">
                    </div>
                </div>

                <!-- Campos para Cliente Externo -->
                 <div id="fieldsTipoExterno" style="<?php echo (($preselected_client_type ?? '') === 'externo') ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="nombre_cliente_externo" class="form-label">Nombre Cliente Externo (*)</label>
                        <input type="text" class="form-control" id="nombre_cliente_externo" name="nombre_cliente_externo">
                    </div>
                     <div class="mb-3">
                        <label for="telefono_cliente_externo" class="form-label">Teléfono Cliente Externo</label>
                        <input type="text" class="form-control" id="telefono_cliente_externo" name="telefono_cliente_externo">
                    </div>
                </div>
            </fieldset>

            <!-- Sección de Ítems del Pedido -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Platos del Pedido (*)</legend>
                <div class="mb-3">
                    <label for="selectMenuItem" class="form-label">Añadir Plato del Menú</label>
                    <select class="form-select" id="selectMenuItem">
                        <option value="">Seleccione un plato</option>
                        <?php foreach ($menuItems as $item): ?>
                            <option value="<?php echo htmlspecialchars($item['id_plato']); ?>" 
                                    data-nombre="<?php echo htmlspecialchars($item['nombre_plato']); ?>"
                                    data-precio="<?php echo htmlspecialchars($item['precio']); ?>">
                                <?php echo htmlspecialchars($item['nombre_plato']); ?> (S/<?php echo number_format(htmlspecialchars($item['precio']), 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <button type="button" id="addDishToOrderBtn" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Añadir Plato</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsTableBody">
                            <!-- Los ítems del pedido se añadirán aquí dinámicamente con JavaScript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total del Pedido:</th>
                                <th id="orderTotal" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="order_items_json" id="orderItemsJson">
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/restaurant/orders" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Pedido</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo $base_url_for_assets; ?>';

    // --- Elementos de Tipo de Pedido ---
    const tipoPedidoMesa = document.getElementById('tipoPedidoMesa');
    const tipoPedidoHabitacion = document.getElementById('tipoPedidoHabitacion');
    const tipoPedidoExterno = document.getElementById('tipoPedidoExterno');
    const fieldsTipoMesa = document.getElementById('fieldsTipoMesa');
    const fieldsTipoHabitacion = document.getElementById('fieldsTipoHabitacion');
    const fieldsTipoExterno = document.getElementById('fieldsTipoExterno');

    // --- Elementos de Mesa ---
    const idMesaSelect = document.getElementById('id_mesa');
    const preselectedTableId = '<?php echo htmlspecialchars($preselected_table_id ?? ''); ?>';

    // --- Elementos de Huésped (para tipo 'Habitación') ---
    const searchGuestOrderInput = document.getElementById('searchGuestOrder');
    const guestSearchResultsOrder = document.getElementById('guestSearchResultsOrder');
    const idHuespedOrderInput = document.getElementById('idHuespedOrder');
    const selectedGuestOrderDisplay = document.getElementById('selectedGuestOrderDisplay');

    // --- Elementos de Ítems del Pedido ---
    const selectMenuItem = document.getElementById('selectMenuItem');
    const addDishToOrderBtn = document.getElementById('addDishToOrderBtn');
    const orderItemsTableBody = document.getElementById('orderItemsTableBody');
    const orderTotalDisplay = document.getElementById('orderTotal');
    const orderItemsJsonInput = document.getElementById('orderItemsJson');

    let orderItems = []; // Array para almacenar los ítems del pedido

    // --- Lógica para precargar ítems si es un "Añadir a Pedido" ---
    const preloadedItems = <?php echo json_encode($preloaded_items ?? []); ?>;
    if (preloadedItems.length > 0) {
        orderItems = preloadedItems; // Cargar ítems existentes
    }

    // --- Funciones ---
    function togglePedidoTypeFields() {
        tipoPedidoMesa.removeAttribute('required');
        idMesaSelect.removeAttribute('required');
        searchGuestOrderInput.removeAttribute('required');
        idHuespedOrderInput.removeAttribute('required');
        document.getElementById('nombre_cliente_externo').removeAttribute('required');

        if (tipoPedidoMesa.checked) {
            fieldsTipoMesa.style.display = 'block';
            fieldsTipoHabitacion.style.display = 'none';
            fieldsTipoExterno.style.display = 'none';
            idMesaSelect.setAttribute('required', 'required');
        } else if (tipoPedidoHabitacion.checked) {
            fieldsTipoMesa.style.display = 'none';
            fieldsTipoHabitacion.style.display = 'block';
            fieldsTipoExterno.style.display = 'none';
            searchGuestOrderInput.setAttribute('required', 'required');
            idHuespedOrderInput.setAttribute('required', 'required');
        } else if (tipoPedidoExterno.checked) {
            fieldsTipoMesa.style.display = 'none';
            fieldsTipoHabitacion.style.display = 'none';
            fieldsTipoExterno.style.display = 'block';
            document.getElementById('nombre_cliente_externo').setAttribute('required', 'required');
        }
    }

    function updateOrderTotals() {
        let total = 0;
        orderItems.forEach(item => {
            total += (parseFloat(item.cantidad) * parseFloat(item.precio_unitario));
        });
        orderTotalDisplay.textContent = `S/ ${total.toFixed(2)}`;
        orderItemsJsonInput.value = JSON.stringify(orderItems);
    }

    function renderOrderItemsTable() {
        orderItemsTableBody.innerHTML = '';
        if (orderItems.length === 0) {
            orderItemsTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Añada platos al pedido.</td></tr>';
        } else {
            orderItems.forEach((item, index) => {
                const row = document.createElement('tr');
                const subtotal = parseFloat(item.cantidad) * parseFloat(item.precio_unitario);
                row.innerHTML = `
                    <td>${htmlspecialchars(item.nombre_plato || item.descripcion_item)}</td>
                    <td><input type="number" class="form-control form-control-sm item-quantity" data-index="${index}" value="${htmlspecialchars(item.cantidad)}" min="0.01" step="0.01" style="width: 70px;"></td>
                    <td>S/ <input type="number" class="form-control form-control-sm item-price" data-index="${index}" value="${htmlspecialchars(item.precio_unitario)}" min="0.00" step="0.01" style="width: 90px;"></td>
                    <td class="item-subtotal text-end">S/ ${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item-btn" data-index="${index}"><i class="fas fa-trash-alt"></i></button></td>
                `;
                orderItemsTableBody.appendChild(row);
            });
        }
        updateOrderTotals();
    }

    // Función para escapar HTML
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }

    // --- Event Listeners ---
    tipoPedidoMesa.addEventListener('change', togglePedidoTypeFields);
    tipoPedidoHabitacion.addEventListener('change', togglePedidoTypeFields);
    tipoPedidoExterno.addEventListener('change', togglePedidoTypeFields);

    addDishToOrderBtn.addEventListener('click', function() {
        const selectedOption = selectMenuItem.options[selectMenuItem.selectedIndex];
        if (selectedOption.value) {
            const id_plato = selectedOption.value;
            const nombre_plato = selectedOption.dataset.nombre;
            const precio = parseFloat(selectedOption.dataset.precio);

            const existingItemIndex = orderItems.findIndex(item => item.id_plato === id_plato);
            if (existingItemIndex > -1) {
                orderItems[existingItemIndex].cantidad++;
            } else {
                orderItems.push({
                    id_plato: id_plato,
                    nombre_plato: nombre_plato, // Para mostrar en la tabla
                    descripcion_item: nombre_plato, // Se usa si id_plato es null (item nuevo)
                    cantidad: 1,
                    precio_unitario: precio
                });
            }
            renderOrderItemsTable();
            selectMenuItem.value = ""; // Limpiar selección
        } else {
            alert('Por favor, seleccione un plato del menú.');
        }
    });

    orderItemsTableBody.addEventListener('change', function(e) {
        const target = e.target;
        if (target.classList.contains('item-quantity') || target.classList.contains('item-price')) {
            const index = target.dataset.index;
            const item = orderItems[index];

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
            item.subtotal = parseFloat((item.cantidad * item.precio_unitario).toFixed(2)); // Recalcular subtotal de la fila
            renderOrderItemsTable(); // Re-render para actualizar el subtotal en la fila y el total
        }
    });

    orderItemsTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn') || e.target.closest('.remove-item-btn')) {
            const button = e.target.closest('.remove-item-btn');
            const index = button.dataset.index;
            orderItems.splice(index, 1);
            renderOrderItemsTable();
        }
    });

    // Búsqueda de Huéspedes para Pedido (AJAX) - Igual que en otros módulos
    let searchGuestOrderTimeout;
    searchGuestOrderInput.addEventListener('input', function() {
        clearTimeout(searchGuestOrderTimeout);
        const query = this.value;
        if (query.length < 3) {
            guestSearchResultsOrder.innerHTML = '';
            return;
        }
        searchGuestOrderTimeout = setTimeout(() => {
            fetch(`${baseUrl}restaurant/search_guests_ajax?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    guestSearchResultsOrder.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(guest => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.textContent = `${guest.nombre} ${guest.apellido} (${guest.numero_documento || guest.email || guest.telefono})`;
                            item.dataset.id = guest.id_huesped;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                idHuespedOrderInput.value = this.dataset.id;
                                selectedGuestOrderDisplay.value = this.textContent;
                                guestSearchResultsOrder.innerHTML = '';
                            });
                            guestSearchResultsOrder.appendChild(item);
                        });
                    } else {
                        guestSearchResultsOrder.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                    }
                })
                .catch(error => console.error('Error buscando huéspedes:', error));
        }, 300);
    });

    // --- Inicialización al cargar la página ---
    // Pre-seleccionar la mesa si viene en la URL
    if (preselectedTableId) {
        idMesaSelect.value = preselectedTableId;
        tipoPedidoMesa.checked = true;
    }
    // Si es "Añadir a Pedido"
    if ('<?php echo htmlspecialchars($preselected_order_id ?? ''); ?>' !== '') {
        // Asegurarse de que el tipo de pedido sea el de la orden existente
        const orderType = '<?php echo htmlspecialchars($preselected_client_type ?? ''); ?>';
        if (orderType === 'habitacion') {
            tipoPedidoHabitacion.checked = true;
            // Si el pedido existente es de habitación, cargar el huésped
            const preloadedGuestId = '<?php echo htmlspecialchars($preselected_order['id_huesped'] ?? ''); ?>';
            const preloadedGuestName = '<?php echo htmlspecialchars(($preselected_order['huesped_nombre'] ?? '') . ' ' . ($preselected_order['huesped_apellido'] ?? '')); ?>';
            if (preloadedGuestId) {
                idHuespedOrderInput.value = preloadedGuestId;
                selectedGuestOrderDisplay.value = preloadedGuestName;
            }
        } else if (orderType === 'externo') {
            tipoPedidoExterno.checked = true;
            // Cargar el nombre y teléfono del cliente externo si es el caso
            document.getElementById('nombre_cliente_externo').value = '<?php echo htmlspecialchars($preselected_order['cliente_externo_nombre'] ?? ''); ?>';
            document.getElementById('telefono_cliente_externo').value = '<?php echo htmlspecialchars($preselected_order['cliente_externo_telefono'] ?? ''); ?>';
        } else { // Asumir mesa si no es habitación ni externo
            tipoPedidoMesa.checked = true;
        }
        // Deshabilitar los radios de tipo de pedido si estamos añadiendo a un pedido existente
        tipoPedidoMesa.disabled = true;
        tipoPedidoHabitacion.disabled = true;
        tipoPedidoExterno.disabled = true;
    }

    togglePedidoTypeFields(); // Llamada inicial para configurar la visibilidad y requeridos
    renderOrderItemsTable(); // Renderizar ítems pre-cargados al inicio
});
</script>
