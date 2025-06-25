<h1 class="mb-4">Editar Reserva de Piscina: #<?php echo htmlspecialchars($reservation['id_reserva_piscina'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($reservation) || !$reservation): ?>
    <div class="alert alert-warning" role="alert">Reserva de piscina no encontrada.</div>
<?php return; endif; ?>

<?php if (!$openRegister): // Validacion si la caja esta abierta ?>
    <div class="alert alert-warning" role="alert">No hay un turno de caja abierto. Los cambios de estado de reserva de piscina a 'Completada' con pago inmediato no se reflejarán en caja.</div>
<?php endif; ?>


<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Reserva de Piscina</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/pool/edit/<?php echo htmlspecialchars($reservation['id_reserva_piscina']); ?>" method="POST">
            <!-- Sección de Cliente: Huésped del Hotel o Cliente Externo -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Datos del Cliente</legend>
                <?php $isExternal = empty($reservation['id_huesped']); ?>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleGuestType" <?php echo $isExternal ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="toggleGuestType">Cliente Externo (No es huésped del hotel)</label>
                </div>

                <!-- Campos para Cliente Externo -->
                <div id="externalClientFields" style="<?php echo $isExternal ? 'display: block;' : 'display: none;'; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_cliente" class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($reservation['nombre_cliente'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_cliente" class="form-label">Teléfono del Cliente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" value="<?php echo htmlspecialchars($reservation['telefono_cliente'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Campos para Huésped del Hotel -->
                <div id="hotelGuestFields" style="<?php echo $isExternal ? 'display: none;' : 'display: block;'; ?>">
                    <div class="mb-3">
                        <label for="searchGuestPool" class="form-label">Buscar Huésped <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="searchGuestPool" placeholder="Nombre, Apellido, Email o DNI" value="<?php echo htmlspecialchars($reservation['huesped_nombre'] . ' ' . $reservation['huesped_apellido'] . ' (' . ($reservation['huesped_documento'] ?? '') . ')'); ?>">
                        <div id="guestSearchResultsPool" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedGuestPoolDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestPoolDisplay" value="<?php echo htmlspecialchars($reservation['huesped_nombre'] . ' ' . $reservation['huesped_apellido'] . ' (' . ($reservation['huesped_documento'] ?? '') . ')'); ?>" readonly>
                        <input type="hidden" name="id_huesped_existente" id="idHuespedPool" value="<?php echo htmlspecialchars($reservation['id_huesped'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <!-- Sección de Fechas, Horas y Detalles -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Horario y Ocupación</legend>
                <div class="mb-3">
                    <label for="fecha_reserva" class="form-label">Fecha de Reserva <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" value="<?php echo htmlspecialchars($reservation['fecha_reserva'] ?? ''); ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="hora_inicio" class="form-label">Hora de Inicio <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo htmlspecialchars($reservation['hora_inicio'] ? substr($reservation['hora_inicio'], 0, 5) : ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="hora_fin" class="form-label">Hora de Fin <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo htmlspecialchars($reservation['hora_fin'] ? substr($reservation['hora_fin'], 0, 5) : ''); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="cantidad_personas" class="form-label">Cantidad de Personas <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cantidad_personas" name="cantidad_personas" min="1" value="<?php echo htmlspecialchars($reservation['cantidad_personas'] ?? '1'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="precio_total" class="form-label">Precio Total (S/) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="precio_total" name="precio_total" step="0.01" min="0.01" value="<?php echo htmlspecialchars($reservation['precio_total'] ?? '0.00'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado de la Reserva</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="confirmada" <?php echo ($reservation['estado'] == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="cancelada" <?php echo ($reservation['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        <option value="completada" <?php echo ($reservation['estado'] == 'completada') ? 'selected' : ''; ?>>Completada</option>
                    </select>
                </div>
            </fieldset>

            <!-- Nuevo: Sección de Pago -->
            <fieldset class="mb-4 p-3 border rounded" id="paymentSection">
                <legend class="float-none w-auto px-2 fs-5">Información de Pago</legend>
                <?php
                // Determinar el tipo de pago actual (si se guardó en algún momento)
                // Para simplificar, asumimos que si el estado es 'completada', el tipo de pago ya se definió.
                // Si la reserva viene con un método de pago, lo usamos. De lo contrario, por defecto inmediato.
                $current_payment_type = 'immediate'; // Valor por defecto
                if ($reservation['estado'] === 'completada' || !empty($reservation['metodo_pago_principal'])) { // Assuming 'metodo_pago_principal' reflects if it was paid
                    // Esta lógica puede ser más compleja si tienes un campo 'tipo_pago' en reservas_piscina
                    // Para este ejemplo, si no hay 'metodo_pago_principal', asumimos que es 'charge_to_room' si no fue pago inmediato
                    // Si tienes un campo en DB para 'tipo_pago_reserva_piscina', úsalo aquí.
                    if (strpos($reservation['metodo_pago_principal'] ?? '', 'Cargo a Habitación') !== false) {
                         $current_payment_type = 'charge_to_room';
                    } else if (!empty($reservation['metodo_pago_principal'])) {
                         $current_payment_type = 'immediate';
                    }
                }
                ?>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" id="poolPaymentTypeImmediate" value="immediate" <?php echo ($current_payment_type === 'immediate') ? 'checked' : ''; ?> <?php echo ($reservation['estado'] === 'completada' || $reservation['estado'] === 'cancelada') ? 'disabled' : ''; ?>>
                        <label class="form-check-label" for="poolPaymentTypeImmediate">Pago Inmediato</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_type" id="poolPaymentTypeChargeToRoom" value="charge_to_room" <?php echo ($current_payment_type === 'charge_to_room') ? 'checked' : ''; ?> <?php echo ($reservation['estado'] === 'completada' || $reservation['estado'] === 'cancelada') ? 'disabled' : ''; ?>>
                        <label class="form-check-label" for="poolPaymentTypeChargeToRoom">Cargar a Habitación</label>
                    </div>
                </div>

                <div id="poolImmediatePaymentFields" style="<?php echo ($current_payment_type === 'immediate') ? 'display: block;' : 'display: none;'; ?>">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" <?php echo ($reservation['estado'] === 'completada' || $reservation['estado'] === 'cancelada' || $current_payment_type === 'charge_to_room') ? 'disabled' : ''; ?>>
                            <option value="">Seleccione un método</option>
                            <option value="Efectivo" <?php echo ($reservation['metodo_pago_principal'] ?? '') === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                            <option value="Tarjeta de Crédito" <?php echo ($reservation['metodo_pago_principal'] ?? '') === 'Tarjeta de Crédito' ? 'selected' : ''; ?>>Tarjeta de Crédito</option>
                            <option value="Yape/Plin" <?php echo ($reservation['metodo_pago_principal'] ?? '') === 'Yape/Plin' ? 'selected' : ''; ?>>Yape/Plin</option>
                            <option value="Transferencia Bancaria" <?php echo ($reservation['metodo_pago_principal'] ?? '') === 'Transferencia Bancaria' ? 'selected' : ''; ?>>Transferencia Bancaria</option>
                        </select>
                    </div>
                </div>
                <div id="poolChargeToRoomFields" style="<?php echo ($current_payment_type === 'charge_to_room') ? 'display: block;' : 'display: none;'; ?>">
                    <div class="alert alert-info" role="alert">
                        Esta reserva se registró para cargar a la cuenta del huésped y se sumará al total a pagar en su Check-out.
                    </div>
                </div>
            </fieldset>


            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/pool" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary" <?php echo ($reservation['estado'] === 'completada' || $reservation['estado'] === 'cancelada') ? 'disabled' : ''; ?>>Actualizar Reserva de Piscina</button>
                <!--<button type="submit" class="btn btn-primary">Actualizar Reserva de Piscina</button>-->
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo $base_url_for_assets; ?>';

    // --- Cliente: Huésped vs Externo ---
    const toggleGuestType = document.getElementById('toggleGuestType');
    const externalClientFields = document.getElementById('externalClientFields');
    const hotelGuestFields = document.getElementById('hotelGuestFields');
    const nombreClienteInput = document.getElementById('nombre_cliente');
    const telefonoClienteInput = document.getElementById('telefono_cliente');
    const searchGuestPoolInput = document.getElementById('searchGuestPool');
    const idHuespedPoolInput = document.getElementById('idHuespedPool');
    const selectedGuestPoolDisplay = document.getElementById('selectedGuestPoolDisplay');

    // --- Pago: Inmediato vs Cargar a Habitación ---
    const poolPaymentTypeImmediate = document.getElementById('poolPaymentTypeImmediate');
    const poolPaymentTypeChargeToRoom = document.getElementById('poolPaymentTypeChargeToRoom');
    const poolImmediatePaymentFields = document.getElementById('poolImmediatePaymentFields');
    const poolChargeToRoomFields = document.getElementById('poolChargeToRoomFields');
    const paymentMethodSelect = document.getElementById('payment_method');
    const poolReservationStatusSelect = document.getElementById('estado');

    // Estado inicial de la reserva desde PHP
    const initialReservationStatus = '<?php echo htmlspecialchars($reservation['estado'] ?? 'confirmada'); ?>';


    // Función para alternar los campos de tipo de cliente y sus requerimientos
    function toggleClientTypeFields() {
        if (toggleGuestType.checked) { // Cliente Externo
            externalClientFields.style.display = 'block';
            hotelGuestFields.style.display = 'none';
            nombreClienteInput.setAttribute('required', 'required');
            telefonoClienteInput.setAttribute('required', 'required');
            searchGuestPoolInput.removeAttribute('required');
            idHuespedPoolInput.removeAttribute('required');
            idHuespedPoolInput.value = '';
            selectedGuestPoolDisplay.value = '';
            // No limpiar nombreClienteInput/telefonoClienteInput aquí para edición, solo en 'create'
        } else { // Huésped del Hotel
            externalClientFields.style.display = 'none';
            hotelGuestFields.style.display = 'block';
            nombreClienteInput.removeAttribute('required');
            telefonoClienteInput.removeAttribute('required');
            
            searchGuestPoolInput.setAttribute('required', 'required');
            idHuespedPoolInput.setAttribute('required', 'required');
        }
        togglePaymentFields(); // Re-evaluar campos de pago al cambiar tipo de cliente
    }

    // Función para alternar los campos de tipo de pago y sus requerimientos
    function togglePaymentFields() {
        if (initialReservationStatus === 'completada' || initialReservationStatus === 'cancelada') {
            // Si la reserva ya está completada o cancelada, no permitir cambios en el tipo de pago
            poolPaymentTypeImmediate.disabled = true;
            poolPaymentTypeChargeToRoom.disabled = true;
            paymentMethodSelect.disabled = true;
            return; // Salir de la función, no se necesita más lógica
        }

        if (poolPaymentTypeImmediate.checked) {
            poolImmediatePaymentFields.style.display = 'block';
            poolChargeToRoomFields.style.display = 'none';
            paymentMethodSelect.setAttribute('required', 'required');

            // Si es pago inmediato, el estado de la reserva NO puede ser 'pendiente' o 'confirmada' al crear
            // Solo ajustar si el estado es compatible con un pago inmediato (ej. no cancelada)
            if (poolReservationStatusSelect.value === 'pendiente' || poolReservationStatusSelect.value === 'confirmada') {
                 poolReservationStatusSelect.value = 'completada'; // Sugerir completada si es pago inmediato
            }

        } else { // Cargar a Habitación
            poolImmediatePaymentFields.style.display = 'none';
            poolChargeToRoomFields.style.display = 'block';
            paymentMethodSelect.removeAttribute('required');
            paymentMethodSelect.value = ''; // Limpiar el valor seleccionado

            // Si se carga a habitación, forzar tipo de cliente a Huésped
            toggleGuestType.checked = false; // Desmarcar "Cliente Externo"
            toggleClientTypeFields(); // Ejecutar para forzar el cambio visual y de requeridos

            // Si se carga a habitación, el estado de la reserva NO puede ser 'completada' (aún no se paga)
            if (poolReservationStatusSelect.value === 'completada') {
                 poolReservationStatusSelect.value = 'confirmada'; // Sugerir confirmada si se carga a habitación
            }
        }
        updateRequiredAttributes(); // Llamada final para ajustar todos los requeridos
    }

    // Función central para ajustar todos los atributos 'required' basado en el estado actual de los radios
    function updateRequiredAttributes() {
        // Campos de Cliente Externo
        if (customerTypeExternal.checked) {
            nombreClienteInput.setAttribute('required', 'required');
            telefonoClienteInput.setAttribute('required', 'required');
        } else {
            nombreClienteInput.removeAttribute('required');
            telefonoClienteInput.removeAttribute('required');
        }

        // Campos de Huésped del Hotel
        if (customerTypeGuest.checked) {
            searchGuestPoolInput.setAttribute('required', 'required');
            idHuespedPoolInput.setAttribute('required', 'required');
        } else {
            searchGuestPoolInput.removeAttribute('required');
            idHuespedPoolInput.removeAttribute('required');
        }

        // Método de Pago
        if (poolPaymentTypeImmediate.checked && (initialReservationStatus !== 'completada' && initialReservationStatus !== 'cancelada')) {
            paymentMethodSelect.setAttribute('required', 'required');
        } else {
            paymentMethodSelect.removeAttribute('required');
        }
    }


    // --- Event Listeners ---
    toggleGuestType.addEventListener('change', toggleClientTypeFields);
    poolPaymentTypeImmediate.addEventListener('change', togglePaymentFields);
    poolPaymentTypeChargeToRoom.addEventListener('change', togglePaymentFields);
    poolReservationStatusSelect.addEventListener('change', togglePaymentFields); // Re-evaluar si cambia el estado manualmente


    // Búsqueda de Huéspedes para Piscina (AJAX) - Idéntico a otros módulos
    const guestSearchResultsPool = document.getElementById('guestSearchResultsPool');
    let searchGuestPoolTimeout;

    searchGuestPoolInput.addEventListener('input', function() {
        clearTimeout(searchGuestPoolTimeout);
        const query = this.value;
        if (query.length < 3) {
            guestSearchResultsPool.innerHTML = '';
            return;
        }
        searchGuestPoolTimeout = setTimeout(() => {
            fetch(`${baseUrl}pool/search_guests_ajax?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) { return response.text().then(text => { throw new Error('Server response: ' + text); }); }
                    return response.json();
                })
                .then(data => {
                    guestSearchResultsPool.innerHTML = '';
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
                                idHuespedPoolInput.value = this.dataset.id;
                                selectedGuestPoolDisplay.value = `${this.dataset.nombre} ${this.dataset.apellido}`;
                                guestSearchResultsPool.innerHTML = '';
                            });
                            guestSearchResultsPool.appendChild(item);
                        });
                    } else {
                        guestSearchResultsPool.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error buscando huéspedes para piscina:', error);
                    guestSearchResultsPool.innerHTML = `<div class="list-group-item text-danger">Error al buscar huéspedes: ${error.message}.</div>`;
                });
        }, 300);
    });

    // Llamadas iniciales para configurar el estado de los campos al cargar la página
    toggleClientTypeFields(); // Esto a su vez llama a togglePaymentFields() y updateRequiredAttributes()
});
</script>