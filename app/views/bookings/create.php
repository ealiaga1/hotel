<h1 class="mb-4">Crear Nueva Reserva</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Reserva</h6>
    </div>
    <div class="card-body">
        <form id="bookingForm" action="/hotel_completo/public/bookings/create" method="POST">

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Datos del Huésped</legend>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleNewGuest" checked>
                    <label class="form-check-label" for="toggleNewGuest">Es un Huésped Nuevo</label>
                </div>

                <div id="existingGuestFields" style="display: none;">
                    <div class="mb-3">
                        <label for="searchGuest" class="form-label">Buscar Huésped Existente</label>
                        <input type="text" class="form-control" id="searchGuest" placeholder="Nombre, Apellido, Email o DNI/Pasaporte">
                        <div id="guestSearchResults" class="list-group position-absolute w-75" style="z-index: 1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedGuestDisplay" class="form-label">Huésped Seleccionado:</label>
                        <input type="text" class="form-control" id="selectedGuestDisplay" readonly>
                        <input type="hidden" name="id_huesped_existente" id="idHuespedExistente">
                    </div>
                </div>

                <div id="newGuestFields">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_huesped" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_huesped" name="nombre_huesped">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido_huesped" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido_huesped" name="apellido_huesped">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo Documento</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento">
                                <option value="">Selecciona</option>
                                <option value="DNI">DNI</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Carnet de Extranjería">Carnet de Extranjería</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="numero_documento" class="form-label">Número Documento</label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email_huesped" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_huesped" name="email_huesped">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono_huesped" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_huesped" name="telefono_huesped">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento_huesped" class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento_huesped" name="fecha_nacimiento_huesped">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pais_huesped" class="form-label">País</label>
                            <input type="text" class="form-control" id="pais_huesped" name="pais_huesped" value="Perú">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="direccion_huesped" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion_huesped" name="direccion_huesped">
                    </div>
                    <div class="mb-3">
                        <label for="ciudad_huesped" class="form-label">Ciudad</label>
                        <input type="text" class="form-control" id="ciudad_huesped" name="ciudad_huesped">
                    </div>
                </div>
                <input type="hidden" name="is_new_guest" id="isNewGuestInput" value="true">
            </fieldset>

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Fechas y Ocupación</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_entrada" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_salida" class="form-label">Fecha de Salida <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="adultos" class="form-label">Adultos <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="adultos" name="adultos" value="1" min="1" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ninos" class="form-label">Niños</label>
                        <input type="number" class="form-control" id="ninos" name="ninos" value="0" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                         <label for="id_tipo_habitacion_deseado" class="form-label">Tipo de Habitación Deseado</label>
                         <select class="form-select" id="id_tipo_habitacion_deseado" name="id_tipo_habitacion_deseado">
                             <option value="">Cualquier tipo</option>
                             <?php foreach ($roomTypes as $type): ?>
                                 <option value="<?php echo htmlspecialchars($type['id_tipo_habitacion']); ?>" data-precio="<?php echo htmlspecialchars($type['precio_base']); ?>">
                                     <?php echo htmlspecialchars($type['nombre_tipo']); ?> (Cap: <?php echo htmlspecialchars($type['capacidad']); ?>)
                                 </option>
                             <?php endforeach; ?>
                         </select>
                     </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="button" id="searchRoomsBtn" class="btn btn-outline-primary"><i class="fas fa-search"></i> Buscar Habitaciones Disponibles</button>
                </div>

                <div id="availableRoomsSection" style="display: none;">
                    <div class="mb-3">
                        <label for="id_habitacion" class="form-label">Habitación Asignada (Opcional)</label>
                        <select class="form-select" id="id_habitacion" name="id_habitacion">
                            <option value="">Sin asignar (Reserva pendiente)</option>
                            </select>
                        <small class="form-text text-muted">Solo se muestran habitaciones disponibles para las fechas y capacidad seleccionadas.</small>
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Resumen de Reserva</legend>
                <div class="mb-3">
                    <label for="precio_total" class="form-label">Precio Total (S/) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="precio_total" name="precio_total" step="0.01" min="0.01" required>
                    <small class="form-text text-muted">Este precio se puede calcular automáticamente al seleccionar una habitación y fechas.</small>
                </div>
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios</label>
                    <textarea class="form-control" id="comentarios" name="comentarios" rows="3"></textarea>
                </div>
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/bookings" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Reserva</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?php echo $base_url_for_assets; ?>'; // Definido en main_layout.php

        // --- Toggle Huésped Nuevo/Existente ---
        const toggleNewGuest = document.getElementById('toggleNewGuest');
        const newGuestFields = document.getElementById('newGuestFields');
        const existingGuestFields = document.getElementById('existingGuestFields');
        const isNewGuestInput = document.getElementById('isNewGuestInput');

        const nombreHuesped = document.getElementById('nombre_huesped');
        const apellidoHuesped = document.getElementById('apellido_huesped');
        const emailHuesped = document.getElementById('email_huesped');
        const telefonoHuesped = document.getElementById('telefono_huesped');
        const numeroDocumentoHuesped = document.getElementById('numero_documento');
        const tipoDocumentoHuesped = document.getElementById('tipo_documento');
        const direccionHuesped = document.getElementById('direccion_huesped');
        const ciudadHuesped = document.getElementById('ciudad_huesped');
        const paisHuesped = document.getElementById('pais_huesped');
        const fechaNacimientoHuesped = document.getElementById('fecha_nacimiento_huesped');

        const selectedGuestDisplay = document.getElementById('selectedGuestDisplay');
        const idHuespedExistente = document.getElementById('idHuespedExistente');

        function toggleGuestFields() {
            if (toggleNewGuest.checked) {
                newGuestFields.style.display = 'block';
                existingGuestFields.style.display = 'none';
                isNewGuestInput.value = 'true';
                // Hacer campos de huésped nuevo requeridos
                nombreHuesped.setAttribute('required', 'required');
                apellidoHuesped.setAttribute('required', 'required');
                idHuespedExistente.removeAttribute('required');
                idHuespedExistente.value = ''; // Limpiar si se cambia a nuevo
                selectedGuestDisplay.value = '';
                nombreHuesped.focus();
            } else {
                newGuestFields.style.display = 'none';
                existingGuestFields.style.display = 'block';
                isNewGuestInput.value = 'false';
                // Hacer campos de huésped existente requeridos
                idHuespedExistente.setAttribute('required', 'required');
                nombreHuesped.removeAttribute('required');
                apellidoHuesped.removeAttribute('required');
                // Limpiar campos de huésped nuevo
                nombreHuesped.value = ''; apellidoHuesped.value = ''; emailHuesped.value = '';
                telefonoHuesped.value = ''; numeroDocumentoHuesped.value = ''; tipoDocumentoHuesped.value = '';
                direccionHuesped.value = ''; ciudadHuesped.value = ''; paisHuesped.value = 'Perú'; fechaNacimientoHuesped.value = '';
                searchGuest.focus();
            }
        }
        toggleNewGuest.addEventListener('change', toggleGuestFields);
        toggleGuestFields(); // Ejecutar al cargar la página para establecer el estado inicial

        // --- Búsqueda de Huéspedes con AJAX ---
        const searchGuestInput = document.getElementById('searchGuest');
        const guestSearchResults = document.getElementById('guestSearchResults');
        let searchGuestTimeout;

        searchGuestInput.addEventListener('input', function() {
            clearTimeout(searchGuestTimeout);
            const query = this.value;
            if (query.length < 3) { // Mínimo 3 caracteres para buscar
                guestSearchResults.innerHTML = '';
                return;
            }

            searchGuestTimeout = setTimeout(() => {
                fetch(`${baseUrl}bookings/search_guests_ajax?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        guestSearchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(guest => {
                                const item = document.createElement('a');
                                item.href = '#';
                                item.classList.add('list-group-item', 'list-group-item-action');
                                item.textContent = `${guest.nombre} ${guest.apellido} (${guest.numero_documento || guest.email || guest.telefono})`;
                                item.dataset.id = guest.id_huesped;
                                item.dataset.nombre = guest.nombre;
                                item.dataset.apellido = guest.apellido;
                                item.dataset.documentoTipo = guest.tipo_documento;
                                item.dataset.documentoNumero = guest.numero_documento;
                                item.dataset.email = guest.email;
                                item.dataset.telefono = guest.telefono;
                                // Puedes añadir más datos aquí si los necesitas para precargar el formulario de edición
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    idHuespedExistente.value = this.dataset.id;
                                    selectedGuestDisplay.value = `${this.dataset.nombre} ${this.dataset.apellido} (${this.dataset.documentoNumero || this.dataset.email})`;
                                    guestSearchResults.innerHTML = ''; // Limpiar resultados
                                });
                                guestSearchResults.appendChild(item);
                            });
                        } else {
                            guestSearchResults.innerHTML = '<div class="list-group-item">No se encontraron huéspedes.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error buscando huéspedes:', error);
                        guestSearchResults.innerHTML = '<div class="list-group-item text-danger">Error al buscar huéspedes.</div>';
                    });
            }, 300); // Pequeño delay para no saturar el servidor
        });

        // --- Búsqueda de Habitaciones Disponibles con AJAX ---
        const fechaEntradaInput = document.getElementById('fecha_entrada');
        const fechaSalidaInput = document.getElementById('fecha_salida');
        const adultosInput = document.getElementById('adultos');
        const ninosInput = document.getElementById('ninos');
        const tipoHabitacionDeseadoSelect = document.getElementById('id_tipo_habitacion_deseado');
        const searchRoomsBtn = document.getElementById('searchRoomsBtn');
        const availableRoomsSection = document.getElementById('availableRoomsSection');
        const idHabitacionSelect = document.getElementById('id_habitacion');
        const precioTotalInput = document.getElementById('precio_total');

        searchRoomsBtn.addEventListener('click', searchAvailableRooms);

        // También buscar habitaciones cuando cambien las fechas o el tipo de habitación deseado
        fechaEntradaInput.addEventListener('change', searchAvailableRooms);
        fechaSalidaInput.addEventListener('change', searchAvailableRooms);
        adultosInput.addEventListener('change', searchAvailableRooms);
        tipoHabitacionDeseadoSelect.addEventListener('change', searchAvailableRooms);


        // Escuchar cambios en la habitación asignada para actualizar el precio
        idHabitacionSelect.addEventListener('change', function() {
            updateTotalPrice();
        });

        function searchAvailableRooms() {
            const fechaEntrada = fechaEntradaInput.value;
            const fechaSalida = fechaSalidaInput.value;
            const capacidad = parseInt(adultosInput.value) + parseInt(ninosInput.value); // Capacidad total
            const idTipoHabitacion = tipoHabitacionDeseadoSelect.value;

            if (!fechaEntrada || !fechaSalida || fechaEntrada >= fechaSalida) {
                alert('Por favor, selecciona fechas de entrada y salida válidas. La fecha de salida debe ser posterior a la de entrada.');
                availableRoomsSection.style.display = 'none';
                idHabitacionSelect.innerHTML = '<option value="">Sin asignar (Reserva pendiente)</option>';
                updateTotalPrice(); // Limpiar precio si las fechas son inválidas
                return;
            }

            // Mostrar el spinner o indicador de carga (opcional)
            idHabitacionSelect.innerHTML = '<option value="">Cargando habitaciones...</option>';
            availableRoomsSection.style.display = 'block';

            const queryParams = new URLSearchParams({
                action: 'search_available_rooms',
                fecha_entrada: fechaEntrada,
                fecha_salida: fechaSalida,
                capacidad: capacidad,
                id_tipo_habitacion: idTipoHabitacion
            }).toString();

            fetch(`${baseUrl}bookings/search_available_rooms_ajax?${queryParams}`)
                .then(response => response.json())
                .then(data => {
                    idHabitacionSelect.innerHTML = '<option value="">Sin asignar (Reserva pendiente)</option>';
                    if (data.length > 0) {
                        data.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id_habitacion;
                            option.dataset.precioBase = room.precio_base;
                            option.textContent = `Hab. ${room.numero_habitacion} (${room.nombre_tipo}, S/${parseFloat(room.precio_base).toFixed(2)}/noche)`;
                            idHabitacionSelect.appendChild(option);
                        });
                    } else {
                        idHabitacionSelect.innerHTML = '<option value="">No se encontraron habitaciones disponibles</option>';
                    }
                    updateTotalPrice(); // Recalcular precio cuando las habitaciones se cargan
                })
                .catch(error => {
                    console.error('Error buscando habitaciones:', error);
                    idHabitacionSelect.innerHTML = '<option value="">Error al cargar habitaciones</option>';
                    updateTotalPrice();
                });
        }

        function updateTotalPrice() {
            const fechaEntrada = fechaEntradaInput.value;
            const fechaSalida = fechaSalidaInput.value;
            const selectedRoomOption = idHabitacionSelect.options[idHabitacionSelect.selectedIndex];
            const selectedRoomPrice = selectedRoomOption ? parseFloat(selectedRoomOption.dataset.precioBase || 0) : 0;
            const selectedRoomTypeId = tipoHabitacionDeseadoSelect.value;
            const selectedRoomTypeOption = tipoHabitacionDeseadoSelect.options[tipoHabitacionDeseadoSelect.selectedIndex];
            const selectedRoomTypePrice = selectedRoomTypeOption ? parseFloat(selectedRoomTypeOption.dataset.precio || 0) : 0;


            if (fechaEntrada && fechaSalida && fechaEntrada < fechaSalida) {
                const date1 = new Date(fechaEntrada);
                const date2 = new Date(fechaSalida);
                const diffTime = Math.abs(date2 - date1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Número de noches

                let finalPrice = 0;
                if (selectedRoomPrice > 0) {
                    finalPrice = selectedRoomPrice * diffDays;
                } else if (selectedRoomTypePrice > 0) {
                    // Si no se ha asignado una habitación, usar el precio del tipo de habitación deseado
                    finalPrice = selectedRoomTypePrice * diffDays;
                }
                precioTotalInput.value = finalPrice.toFixed(2);
            } else {
                precioTotalInput.value = '0.00';
            }
        }

        // Inicializar el precio total al cargar la página
        updateTotalPrice();
        fechaEntradaInput.addEventListener('change', updateTotalPrice);
        fechaSalidaInput.addEventListener('change', updateTotalPrice);
    });
</script>