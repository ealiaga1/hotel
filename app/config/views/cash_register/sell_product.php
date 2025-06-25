<h1 class="mb-4">Venta Directa de Productos</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!$openRegister): ?>
    <div class="alert alert-warning" role="alert">No hay un turno de caja abierto para registrar ventas. Abra la caja primero.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Venta</h6>
    </div>
    <div class="card-body">
        <form id="sellProductForm" action="/hotel_completo/public/cash_register/sell_product" method="POST">

            <!-- Tipo de Cliente: Huésped o Externo -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Cliente de la Venta</legend>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="customer_type" id="customerTypeExternal" value="external" checked>
                        <label class="form-check-label" for="customerTypeExternal">Cliente Externo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="customer_type" id="customerTypeGuest" value="guest">
                        <label class="form-check-label" for="customerTypeGuest">Huésped del Hotel</label>
                    </div>
                </div>

                <div id="externalCustomerFields" class="mb-3">
                    <label for="customer_description" class="form-label">Nombre / Descripción Cliente Externo</label>
                    <input type="text" class="form-control" id="customer_description" name="customer_description" value="Cliente Externo">
                </div>

                <div id="guestCustomerFields" class="mb-3" style="display: none;">
                    <label for="searchGuestSale" class="form-label">Buscar Huésped <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="searchGuestSale" placeholder="Nombre, Apellido, Email o DNI">
                    <div id="guestSearchResultsSale" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    <div class="mt-2">
                        <label for="selectedGuestSaleDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestSaleDisplay" readonly>
                        <input type="hidden" name="id_huesped_selected" id="idHuespedSale">
                    </div>
                </div>
            </fieldset>

            <!-- Selección de Productos -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Productos Vendidos</legend>
                <div class="mb-3">
                    <label for="selectProductItem" class="form-label">Añadir Producto</label>
                    <select class="form-select" id="selectProductItem">
                        <option value="">Seleccione un producto</option>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <?php if ($product['stock_actual'] > 0): // Only show products with stock ?>
                                    <option value="<?php echo htmlspecialchars($product['id_producto']); ?>"
                                            data-nombre="<?php echo htmlspecialchars($product['nombre_producto']); ?>"
                                            data-precio="<?php echo htmlspecialchars($product['precio_compra']); ?>"
                                            data-stock="<?php echo htmlspecialchars($product['stock_actual']); ?>"
                                            data-unidad="<?php echo htmlspecialchars($product['unidad_medida']); ?>">
                                        <?php echo htmlspecialchars($product['nombre_producto']); ?> (Stock: <?php echo htmlspecialchars(number_format($product['stock_actual'], 2)) . ' ' . htmlspecialchars($product['unidad_medida']); ?>, Precio: S/<?php echo number_format(htmlspecialchars($product['precio_compra']), 2); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No hay productos en inventario con stock disponible.</option>
                        <?php endif; ?>
                    </select>
                    <button type="button" id="addProductToSaleBtn" class="btn btn-success btn-sm mt-2"><i class="fas fa-plus"></i> Añadir Producto</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Cantidad</th>
                                <th>Stock Disp.</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="soldItemsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Añada productos a la venta.</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total de la Venta:</th>
                                <th id="saleTotal" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="sold_items_json" id="soldItemsJson">
                <input type="hidden" name="total_sale_amount" id="totalSaleAmount">
            </fieldset>

            <!-- Método de Pago / Carga a Habitación -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Información de Pago</legend>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" id="paymentTypeImmediate" value="immediate" checked>
                        <label class="form-check-label" for="paymentTypeImmediate">Pago Inmediato</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" id="paymentTypeChargeToRoom" value="charge_to_room">
                        <label class="form-check-label" for="paymentTypeChargeToRoom">Cargar a Habitación</label>
                    </div>
                </div>
                
                <div id="immediatePaymentFields">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method"> <!-- REQUIRED attribute managed by JS -->
                            <option value="">Seleccione un método</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                            <option value="Yape/Plin">Yape/Plin</option>
                            <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                        </select>
                    </div>
                </div>

                <div id="chargeToRoomFields" style="display: none;">
                    <div class="alert alert-info" role="alert">
                        Esta venta se registrará como un cargo pendiente en la cuenta del huésped y se sumará al total a pagar en su Check-out.
                    </div>
                </div>
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/cash_register" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar Venta</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo $base_url_for_assets; ?>';
    console.log("DEBUG-JS: baseUrl is: " + baseUrl);

    // --- Cliente: Huésped vs Externo ---
    const customerTypeExternal = document.getElementById('customerTypeExternal');
    const customerTypeGuest = document.getElementById('customerTypeGuest');
    const externalCustomerFields = document.getElementById('externalCustomerFields');
    const guestCustomerFields = document.getElementById('guestCustomerFields');
    const customerDescriptionInput = document.getElementById('customer_description');
    const searchGuestSaleInput = document.getElementById('searchGuestSale'); 
    const idHuespedSaleInput = document.getElementById('idHuespedSale');
    const selectedGuestSaleDisplay = document.getElementById('selectedGuestSaleDisplay');

    // --- Toggle Payment Type ---
    const paymentTypeImmediate = document.getElementById('paymentTypeImmediate');
    const paymentTypeChargeToRoom = document.getElementById('paymentTypeChargeToRoom');
    const immediatePaymentFields = document.getElementById('immediatePaymentFields');
    const chargeToRoomFields = document.getElementById('chargeToRoomFields');
    const paymentMethodSelect = document.getElementById('payment_method');

    // Function to handle showing/hiding payment fields and setting required attributes
    function togglePaymentType() {
        console.log("DEBUG-JS: togglePaymentType called.");
        if (paymentTypeImmediate.checked) {
            console.log("DEBUG-JS: Payment type: Immediate. Showing immediate fields, hiding charge fields.");
            immediatePaymentFields.style.display = 'block';
            chargeToRoomFields.style.display = 'none';
            paymentMethodSelect.setAttribute('required', 'required'); // Method required for immediate payment
            
            // Adjust required fields for customer type based on their current selection
            if (customerTypeExternal.checked) {
                customerDescriptionInput.setAttribute('required', 'required');
                searchGuestSaleInput.removeAttribute('required');
                idHuespedSaleInput.removeAttribute('required');
            } else { // customerTypeGuest.checked
                searchGuestSaleInput.setAttribute('required', 'required');
                idHuespedSaleInput.setAttribute('required', 'required');
            }
            
        } else { // Cargar a Habitación (paymentTypeChargeToRoom.checked is true)
            console.log("DEBUG-JS: Payment type: Charge to Room. Hiding immediate fields, showing charge fields.");
            immediatePaymentFields.style.display = 'none';
            chargeToRoomFields.style.display = 'block';
            paymentMethodSelect.removeAttribute('required'); // Method NOT required for charge to room
            paymentMethodSelect.value = ''; // Clear selected payment method
            
            // When switching to charge to room, customer MUST be a guest, so force guest selection fields
            customerTypeGuest.checked = true; // Automatically select 'Huésped del Hotel' radio button
            console.log("DEBUG-JS: Forced customer type to Guest for Charge to Room.");
            
            externalCustomerFields.style.display = 'none';
            customerDescriptionInput.removeAttribute('required'); 
            customerDescriptionInput.value = '';
            
            guestCustomerFields.style.display = 'block'; 
            searchGuestSaleInput.setAttribute('required', 'required');
            idHuespedSaleInput.setAttribute('required', 'required');
        }
        console.log("DEBUG-JS: paymentMethodSelect required status:", paymentMethodSelect.hasAttribute('required'));
        console.log("DEBUG-JS: customerDescriptionInput required status:", customerDescriptionInput.hasAttribute('required'));
        console.log("DEBUG-JS: searchGuestSaleInput required status:", searchGuestSaleInput.hasAttribute('required'));
        console.log("DEBUG-JS: idHuespedSaleInput required status:", idHuespedSaleInput.hasAttribute('required'));
    }

    // Function to handle showing/hiding customer type fields and setting required attributes
    function toggleCustomerTypeFields() {
        console.log("DEBUG-JS: toggleCustomerTypeFields called.");
        if (customerTypeExternal.checked) {
            externalCustomerFields.style.display = 'block';
            guestCustomerFields.style.display = 'none';
            customerDescriptionInput.setAttribute('required', 'required');
            searchGuestSaleInput.removeAttribute('required');
            idHuespedSaleInput.removeAttribute('required');
            idHuespedSaleInput.value = '';
            selectedGuestSaleDisplay.value = '';
            customerDescriptionInput.value = 'Cliente Externo';
        } else { // customerTypeGuest.checked
            externalCustomerFields.style.display = 'none';
            guestCustomerFields.style.display = 'block';
            customerDescriptionInput.removeAttribute('required');
            customerDescriptionInput.value = '';

            searchGuestSaleInput.setAttribute('required', 'required');
            idHuespedSaleInput.setAttribute('required', 'required');
        }
        // Always re-evaluate payment fields whenever customer type changes
        togglePaymentType(); 
    }


    // --- Event Listeners for radio buttons ---
    customerTypeExternal.addEventListener('change', toggleCustomerTypeFields);
    customerTypeGuest.addEventListener('change', toggleCustomerTypeFields);
    paymentTypeImmediate.addEventListener('change', togglePaymentType);
    paymentTypeChargeToRoom.addEventListener('change', togglePaymentType);


    // Initial state setup (important: call after all elements are referenced)
    // Make sure all functions are defined before their initial call
    toggleCustomerTypeFields(); // Call once at the end of DOMContentLoaded to set initial states correctly


    // --- Búsqueda de Huéspedes para Venta (AJAX) ---
    const guestSearchResultsSale = document.getElementById('guestSearchResultsSale');
    let searchGuestSaleTimeout;

    searchGuestSaleInput.addEventListener('input', function() {
        clearTimeout(searchGuestSaleTimeout);
        const query = this.value;
        if (query.length < 3) { // Minimum 3 characters to search
            guestSearchResultsSale.innerHTML = '';
            return;
        }
        console.log("DEBUG-JS: Searching guest with query: " + query);

        searchGuestSaleTimeout = setTimeout(() => {
            fetch(`${baseUrl}cash_register/search_guests_ajax?query=${encodeURIComponent(query)}`)
                .then(response => {
                    console.log("DEBUG-JS: AJAX response received. Status:", response.status);
                    if (!response.ok) { 
                        return response.text().then(text => { throw new Error('Server response: ' + text); });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("DEBUG-JS: Guest search data:", data);
                    guestSearchResultsSale.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(guest => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.textContent = `${guest.nombre} ${guest.apellido} (${guest.numero_documento || guest.email || guest.telefono})`;
                            item.dataset.id = guest.id_huesped;
                            item.dataset.nombre = guest.nombre;
                            item.dataset.apellido = guest.apellido;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                idHuespedSaleInput.value = this.dataset.id;
                                selectedGuestSaleDisplay.value = `${this.dataset.nombre} ${this.dataset.apellido}`;
                                guestSearchResultsSale.innerHTML = ''; 
                            });
                            guestSearchResultsSale.appendChild(item);
                        });
                    } else {
                        guestSearchResultsSale.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error buscando huéspedes para venta:', error);
                    guestSearchResultsSale.innerHTML = `<div class="list-group-item text-danger">Error al buscar huéspedes: ${error.message}.</div>`;
                });
        }, 300);
    });

    // --- Selección y Adición de Productos a la Venta ---
    const selectProductItem = document.getElementById('selectProductItem');
    const addProductToSaleBtn = document.getElementById('addProductToSaleBtn');
    const soldItemsTableBody = document.getElementById('soldItemsTableBody');
    const saleTotalElement = document.getElementById('saleTotal');
    const soldItemsJsonInput = document.getElementById('soldItemsJson');
    const totalSaleAmountInput = document.getElementById('totalSaleAmount');

    let soldItems = []; 

    function updateSoldItemsTable() {
        console.log("DEBUG-JS: updateSoldItemsTable called. Current soldItems:", soldItems);
        soldItemsTableBody.innerHTML = '';
        let total = 0;

        if (soldItems.length === 0) {
            soldItemsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Añada productos a la venta.</td></tr>';
        } else {
            soldItems.forEach((item, index) => {
                const row = document.createElement('tr');
                const subtotal = item.cantidad * item.precio_unitario;
                total += subtotal;

                row.innerHTML = `
                    <td>${item.nombre_producto}</td>
                    <td><input type="number" class="form-control form-control-sm item-quantity-sale" data-index="${index}" value="${item.cantidad}" min="1" max="${item.stock_disponible}" style="width: 70px;"></td>
                    <td>${item.stock_disponible} ${item.unidad_medida}</td>
                    <td>S/ ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                    <td>S/ ${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item-btn-sale" data-index="${index}"><i class="fas fa-trash-alt"></i></button></td>
                `;
                soldItemsTableBody.appendChild(row);
            });
        }

        saleTotalElement.textContent = `S/ ${total.toFixed(2)}`;
        totalSaleAmountInput.value = total.toFixed(2);
        soldItemsJsonInput.value = JSON.stringify(soldItems); 
    }

    addProductToSaleBtn.addEventListener('click', function() {
        const selectedOption = selectProductItem.options[selectProductItem.selectedIndex];
        if (selectedOption.value) {
            const id_producto = selectedOption.value;
            const nombre_plato = selectedOption.dataset.nombre;
            const precio_unitario = parseFloat(selectedOption.dataset.precio);
            const stock_disponible = parseFloat(selectedOption.dataset.stock);
            const unidad_medida = selectedOption.dataset.unidad;

            const existingItemIndex = soldItems.findIndex(item => item.id_producto === id_producto);
            if (existingItemIndex > -1) {
                if (soldItems[existingItemIndex].cantidad + 1 <= stock_disponible) {
                    soldItems[existingItemIndex].cantidad++;
                } else {
                    alert(`No hay suficiente stock para añadir más de "${nombre_plato}". Stock disponible: ${stock_disponible}`);
                }
            } else {
                if (stock_disponible > 0) {
                    soldItems.push({
                        id_producto: id_producto,
                        nombre_producto: nombre_plato,
                        cantidad: 1,
                        precio_unitario: precio_unitario,
                        stock_disponible: stock_disponible,
                        unidad_medida: unidad_medida
                    });
                } else {
                    alert(`"${nombre_plato}" está agotado y no se puede añadir.`);
                }
            }
            updateSoldItemsTable();
        } else {
            alert('Por favor, seleccione un producto para añadir.');
        }
    });

    soldItemsTableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-quantity-sale')) {
            const index = e.target.dataset.index;
            const newQuantity = parseInt(e.target.value);
            const maxQuantity = parseInt(e.target.max);

            console.log(`DEBUG-JS: Quantity changed. Index: ${index}, New: ${newQuantity}, Max: ${maxQuantity}`);

            if (!isNaN(newQuantity) && newQuantity > 0 && newQuantity <= maxQuantity) {
                soldItems[index].cantidad = newQuantity;
                updateSoldItemsTable();
            } else if (newQuantity > maxQuantity) {
                alert(`No puedes seleccionar más de ${maxQuantity} unidades para este producto.`);
                e.target.value = maxQuantity;
                soldItems[index].cantidad = maxQuantity;
                updateSoldItemsTable();
            } else if (newQuantity <= 0) {
                alert('La cantidad debe ser al menos 1.');
                e.target.value = 1;
                soldItems[index].cantidad = 1;
                updateSoldItemsTable();
            }
        }
    });

    soldItemsTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn-sale') || e.target.closest('.remove-item-btn-sale')) {
            const button = e.target.closest('.remove-item-btn-sale');
            const index = button.dataset.index;
            console.log("DEBUG-JS: Removing item at index:", index);
            soldItems.splice(index, 1);
            updateSoldItemsTable();
        }
    });

    updateSoldItemsTable(); 

});
</script>