<h1 class="mb-4">Editar Reserva de Evento</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($booking) || !$booking): ?>
    <div class="alert alert-warning" role="alert">Reserva de convención no encontrada.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Reserva del Evento</h6>
    </div>
    <div class="card-body">
        <form id="conventionBookingForm" action="/hotel_completo/public/convention/edit/<?php echo htmlspecialchars($booking['id_reserva_convencion']); ?>" method="POST">
            <!-- Sección de Contacto/Cliente -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Datos del Contacto (*)</legend>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="client_type_radio" id="clientTypeGuest" value="guest" <?php echo empty($booking['nombre_contacto']) || !empty($booking['id_huesped']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="clientTypeGuest">Huésped Existente</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="client_type_radio" id="clientTypeExternal" value="external" <?php echo !empty($booking['nombre_contacto']) && empty($booking['id_huesped']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="clientTypeExternal">Cliente Externo</label>
                    </div>
                </div>

                <!-- Campos para Huésped Existente -->
                <div id="guestFields">
                    <div class="mb-3">
                        <label for="searchGuest" class="form-label">Buscar Huésped</label>
                        <input type="text" class="form-control" id="searchGuest" placeholder="Nombre, Apellido, Email o DNI" value="<?php echo htmlspecialchars(($booking['huesped_nombre'] ?? '') . ' ' . ($booking['huesped_apellido'] ?? '')); ?>">
                        <div id="guestSearchResults" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedGuestDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestDisplay" readonly value="<?php echo htmlspecialchars(($booking['huesped_nombre'] ?? '') . ' ' . ($booking['huesped_apellido'] ?? '')); ?>">
                        <input type="hidden" name="id_huesped_existente" id="idHuespedExistente" value="<?php echo htmlspecialchars($booking['id_huesped'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Campos para Cliente Externo -->
                <div id="externalClientFields" style="display: none;">
                    <div class="mb-3">
                        <label for="nombre_contacto" class="form-label">Nombre del Contacto</label>
                        <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto" value="<?php echo htmlspecialchars($booking['nombre_contacto'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telefono_contacto" class="form-label">Teléfono del Contacto</label>
                        <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" value="<?php echo htmlspecialchars($booking['telefono_contacto'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email_contacto" class="form-label">Email del Contacto</label>
                        <input type="email" class="form-control" id="email_contacto" name="email_contacto" value="<?php echo htmlspecialchars($booking['email_contacto'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>

            <!-- Sección de Detalles del Evento -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Detalles del Evento (*)</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre_evento" class="form-label">Nombre del Evento</label>
                        <input type="text" class="form-control" id="nombre_evento" name="nombre_evento" value="<?php echo htmlspecialchars($booking['nombre_evento'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_sala" class="form-label">Sala de Convenciones</label>
                        <select class="form-select" id="id_sala" name="id_sala" required>
                            <option value="">Seleccione una sala</option>
                            <?php foreach ($conventionRooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room['id_sala']); ?>"
                                    data-precio-hora="<?php echo htmlspecialchars($room['precio_hora_base']); ?>"
                                    <?php echo (isset($booking['id_sala']) && $booking['id_sala'] == $room['id_sala']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['nombre_sala']); ?> (Cap: <?php echo htmlspecialchars($room['capacidad_max']); ?>, S/<?php echo number_format($room['precio_hora_base'], 2); ?>/hora)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fecha_evento" class="form-label">Fecha del Evento</label>
                        <input type="date" class="form-control" id="fecha_evento" name="fecha_evento" value="<?php echo htmlspecialchars($booking['fecha_evento'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hora_inicio" class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo htmlspecialchars($booking['hora_inicio'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="hora_fin" class="form-label">Hora Fin</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo htmlspecialchars($booking['hora_fin'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="num_asistentes" class="form-label">Número de Asistentes</label>
                        <input type="number" class="form-control" id="num_asistentes" name="num_asistentes" min="1" value="<?php echo htmlspecialchars($booking['num_asistentes'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="pendiente" <?php echo (($booking['estado'] ?? '') === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="confirmada" <?php echo (($booking['estado'] ?? '') === 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                            <option value="realizada" <?php echo (($booking['estado'] ?? '') === 'realizada') ? 'selected' : ''; ?>>Realizada</option>
                            <option value="cancelada" <?php echo (($booking['estado'] ?? '') === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios</label>
                    <textarea class="form-control" id="comentarios" name="comentarios" rows="2"><?php echo htmlspecialchars($booking['comentarios'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="precio_total" class="form-label">Precio Total (S/) (Calculado automáticamente o Manual)</label>
                    <input type="number" class="form-control" id="precio_total" name="precio_total" step="0.01" min="0" value="<?php echo htmlspecialchars($booking['precio_total'] ?? '0.00'); ?>">
                    <button type="button" id="calculatePriceBtn" class="btn btn-info btn-sm mt-2"><i class="fas fa-calculator"></i> Calcular Precio</button>
                </div>
            </fieldset>

            <!-- Sección de Servicios/Equipos Adicionales -->
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Servicios/Equipos Adicionales</legend>
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="service_description" class="form-label">Descripción del Servicio/Equipo</label>
                        <input type="text" class="form-control" id="service_description" placeholder="Ej: Proyector, Catering para 20 personas">
                    </div>
                    <div class="col-md-2">
                        <label for="service_quantity" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="service_quantity" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label for="service_price_unit" class="form-label">Precio Unit. (S/)</label>
                        <input type="number" class="form-control" id="service_price_unit" step="0.01" min="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="addServiceBtn" class="btn btn-success"><i class="fas fa-plus"></i> Añadir</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody">
                            <!-- Servicios añadidos dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Servicios:</th>
                                <th id="totalServices" class="text-end">S/ 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="event_services_json" id="eventServicesJson" value="<?php echo htmlspecialchars(json_encode($booking['services'] ?? [])); ?>">
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/convention" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Reserva</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?php echo $base_url_for_assets; ?>';

    // --- Elementos de Cliente (Huésped/Externo) ---
    const clientTypeGuestRadio = document.getElementById('clientTypeGuest');
    const clientTypeExternalRadio = document.getElementById('clientTypeExternal');
    const guestFieldsDiv = document.getElementById('guestFields');
    const externalClientFieldsDiv = document.getElementById('externalClientFields');
    const searchGuestInput = document.getElementById('searchGuest');
    const guestSearchResultsDiv = document.getElementById('guestSearchResults');
    const idHuespedExistenteInput = document.getElementById('idHuespedExistente');
    const selectedGuestDisplay = document.getElementById('selectedGuestDisplay');
    const nombreContactoInput = document.getElementById('nombre_contacto');
    const telefonoContactoInput = document.getElementById('telefono_contacto');
    const emailContactoInput = document.getElementById('email_contacto');

    // --- Elementos de Evento ---
    const idSalaSelect = document.getElementById('id_sala');
    const fechaEventoInput = document.getElementById('fecha_evento');
    const horaInicioInput = document.getElementById('hora_inicio');
    const horaFinInput = document.getElementById('hora_fin');
    const precioTotalInput = document.getElementById('precio_total');
    const calculatePriceBtn = document.getElementById('calculatePriceBtn');

    // --- Elementos de Servicios/Equipos ---
    const serviceDescriptionInput = document.getElementById('service_description');
    const serviceQuantityInput = document.getElementById('service_quantity');
    const servicePriceUnitInput = document.getElementById('service_price_unit');
    const addServiceBtn = document.getElementById('addServiceBtn');
    const servicesTableBody = document.getElementById('servicesTableBody');
    const totalServicesDisplay = document.getElementById('totalServices');
    const eventServicesJsonInput = document.getElementById('eventServicesJson');

    let eventServices = <?php echo json_encode($booking['services'] ?? []); ?>; // Array para almacenar los servicios del evento, precargado

    // --- Funciones de Visibilidad y Requeridos ---
    function toggleClientTypeFields() {
        if (clientTypeGuestRadio.checked) {
            guestFieldsDiv.style.display = 'block';
            externalClientFieldsDiv.style.display = 'none';
            idHuespedExistenteInput.setAttribute('required', 'required');
            nombreContactoInput.removeAttribute('required');
            telefonoContactoInput.removeAttribute('required');
            emailContactoInput.removeAttribute('required');
            // No limpiar valores si el huésped ya está precargado
            if (!idHuespedExistenteInput.value) { // Solo limpiar si no hay huésped ya seleccionado por PHP
                nombreContactoInput.value = '';
                telefonoContactoInput.value = '';
                emailContactoInput.value = '';
            }
        } else {
            guestFieldsDiv.style.display = 'none';
            externalClientFieldsDiv.style.display = 'block';
            idHuespedExistenteInput.removeAttribute('required');
            selectedGuestDisplay.value = '';
            idHuespedExistenteInput.value = '';
            searchGuestInput.value = '';
            guestSearchResultsDiv.innerHTML = '';
            nombreContactoInput.setAttribute('required', 'required');
            // Al menos uno de teléfono o email debe ser requerido para cliente externo
            // Simplificamos: solo nombre requerido, teléfono/email opcionales por ahora
            // Si quieres que al menos uno de teléfono o email sea requerido, necesitarás JS adicional para validación en el submit
        }
    }

    // Funciones para calcular el precio total del evento
    function calculateTotalPrice() {
        const selectedRoom = idSalaSelect.options[idSalaSelect.selectedIndex];
        let salaPricePerHour = 0;
        if (selectedRoom && selectedRoom.value) {
            salaPricePerHour = parseFloat(selectedRoom.dataset.precioHora);
        }

        const startDate = fechaEventoInput.value;
        const startTime = horaInicioInput.value;
        const endTime = horaFinInput.value;

        let totalHours = 0;
        if (startDate && startTime && endTime && startTime < endTime) {
            const startDateTime = new Date(`${startDate}T${startTime}`);
            const endDateTime = new Date(`${startDate}T${endTime}`);
            const diffMs = endDateTime - startDateTime;
            totalHours = diffMs / (1000 * 60 * 60); // Diferencia en horas
        }

        const roomCost = salaPricePerHour * totalHours;
        let servicesTotal = 0;
        eventServices.forEach(service => {
            servicesTotal += (service.cantidad * service.precio_unitario);
        });

        const finalTotal = roomCost + servicesTotal;
        precioTotalInput.value = finalTotal.toFixed(2);
        totalServicesDisplay.textContent = `S/ ${servicesTotal.toFixed(2)}`;
    }

    function renderServicesTable() {
        servicesTableBody.innerHTML = '';
        if (eventServices.length === 0) {
            servicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay servicios/equipos añadidos.</td></tr>';
        } else {
            eventServices.forEach((service, index) => {
                const row = document.createElement('tr');
                const subtotal = service.cantidad * service.precio_unitario; // Usar precio_unitario, no service_price_unit
                row.innerHTML = `
                    <td>${htmlspecialchars(service.descripcion_servicio)}</td>
                    <td><input type="number" class="form-control form-control-sm service-quantity" data-index="${index}" value="${htmlspecialchars(service.cantidad)}" min="1" step="1" style="width: 70px;"></td>
                    <td>S/ <input type="number" class="form-control form-control-sm service-price" data-index="${index}" value="${htmlspecialchars(service.precio_unitario)}" min="0.00" step="0.01" style="width: 90px;"></td>
                    <td class="service-subtotal text-end">S/ ${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-service-btn" data-index="${index}"><i class="fas fa-trash-alt"></i></button></td>
                `;
                servicesTableBody.appendChild(row);
            });
        }
        calculateTotalPrice();
        eventServicesJsonInput.value = JSON.stringify(eventServices);
    }

    function htmlspecialchars(str) {
        if (typeof str !== 'string' && typeof str !== 'number') return str;
        return String(str).replace(/&/g, '&amp;')
                          .replace(/</g, '&lt;')
                          .replace(/>/g, '&gt;')
                          .replace(/"/g, '&quot;')
                          .replace(/'/g, '&#039;');
    }

    // --- Event Listeners ---
    clientTypeGuestRadio.addEventListener('change', toggleClientTypeFields);
    clientTypeExternalRadio.addEventListener('change', toggleClientTypeFields);

    idSalaSelect.addEventListener('change', calculateTotalPrice);
    fechaEventoInput.addEventListener('change', calculateTotalPrice);
    horaInicioInput.addEventListener('change', calculateTotalPrice);
    horaFinInput.addEventListener('change', calculateTotalPrice);
    calculatePriceBtn.addEventListener('click', calculateTotalPrice);

    addServiceBtn.addEventListener('click', function() {
        const description = serviceDescriptionInput.value.trim();
        const quantity = parseInt(serviceQuantityInput.value);
        const priceUnit = parseFloat(servicePriceUnitInput.value);

        if (description && quantity > 0 && priceUnit >= 0) {
            eventServices.push({
                descripcion_servicio: description,
                cantidad: quantity,
                precio_unitario: priceUnit,
                subtotal: quantity * priceUnit
            });
            serviceDescriptionInput.value = '';
            serviceQuantityInput.value = '1';
            servicePriceUnitInput.value = '0.00';
            renderServicesTable();
        } else {
            alert('Por favor, complete todos los campos del servicio (descripción, cantidad > 0, precio unitario >= 0).');
        }
    });

    servicesTableBody.addEventListener('change', function(e) {
        const target = e.target;
        if (target.classList.contains('service-quantity') || target.classList.contains('service-price')) {
            const index = target.dataset.index;
            const service = eventServices[index];

            if (target.classList.contains('service-quantity')) {
                service.cantidad = parseInt(target.value) || 0;
                if (service.cantidad <= 0) { alert('La cantidad debe ser mayor a 0.'); service.cantidad = 1; target.value = 1; }
            } else if (target.classList.contains('service-price')) {
                service.precio_unitario = parseFloat(target.value) || 0;
                if (service.precio_unitario < 0) { alert('El precio no puede ser negativo.'); service.precio_unitario = 0; target.value = 0; }
            }
            renderServicesTable();
        }
    });

    servicesTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-service-btn') || e.target.closest('.remove-service-btn')) {
            const button = e.target.closest('.remove-service-btn');
            const index = button.dataset.index;
            eventServices.splice(index, 1);
            renderServicesTable();
        }
    });

    // Búsqueda de Huéspedes (AJAX)
    let searchGuestTimeout;
    searchGuestInput.addEventListener('input', function() {
        clearTimeout(searchGuestTimeout);
        const query = this.value;
        if (query.length < 3) {
            guestSearchResultsDiv.innerHTML = '';
            return;
        }
        searchGuestTimeout = setTimeout(() => {
            fetch(`${baseUrl}convention/search_guests_ajax?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    guestSearchResultsDiv.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(guest => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.textContent = `${guest.nombre} ${guest.apellido} (${guest.numero_documento || guest.email || guest.telefono})`;
                            item.dataset.id = guest.id_huesped;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                idHuespedExistenteInput.value = this.dataset.id;
                                selectedGuestDisplay.value = this.textContent;
                                nombreContactoInput.value = `${guest.nombre} ${guest.apellido}`; // Auto-fill contact name
                                telefonoContactoInput.value = guest.telefono || ''; // Auto-fill contact phone
                                emailContactoInput.value = guest.email || ''; // Auto-fill contact email
                                guestSearchResultsDiv.innerHTML = '';
                            });
                            guestSearchResultsDiv.appendChild(item);
                        });
                    } else {
                        guestSearchResultsDiv.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                    }
                })
                .catch(error => console.error('Error buscando huéspedes:', error));
        }, 300);
    });

    // --- Inicialización al cargar la página ---
    toggleClientTypeFields(); // Configura la visibilidad inicial de los campos de cliente
    renderServicesTable(); // Renderiza la tabla de servicios inicialmente
});
</script>
