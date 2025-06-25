<h1 class="mb-4">Editar Reserva: #<?php echo htmlspecialchars($booking['id_reserva'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($booking) || !$booking): ?>
    <div class="alert alert-warning" role="alert">Reserva no encontrada.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalles de la Reserva</h6>
    </div>
    <div class="card-body">
        <form id="bookingEditForm" action="/hotel_completo/public/bookings/edit/<?php echo htmlspecialchars($booking['id_reserva']); ?>" method="POST">

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Datos del Huésped</legend>
                <div class="mb-3">
                    <label for="huesped_actual" class="form-label">Huésped Actual:</label>
                    <input type="text" class="form-control" id="huesped_actual"
                           value="<?php echo htmlspecialchars($booking['huesped_nombre'] . ' ' . $booking['huesped_apellido']); ?> (DNI: <?php echo htmlspecialchars($booking['numero_documento'] ?? 'N/A'); ?>)"
                           readonly>
                    <input type="hidden" name="id_huesped" value="<?php echo htmlspecialchars($booking['id_huesped']); ?>">
                </div>
                </fieldset>

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Fechas y Ocupación</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_entrada" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada"
                               value="<?php echo htmlspecialchars($booking['fecha_entrada']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_salida" class="form-label">Fecha de Salida <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_salida" name="fecha_salida"
                               value="<?php echo htmlspecialchars($booking['fecha_salida']); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="adultos" class="form-label">Adultos <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="adultos" name="adultos"
                               value="<?php echo htmlspecialchars($booking['adultos']); ?>" min="1" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ninos" class="form-label">Niños</label>
                        <input type="number" class="form-control" id="ninos" name="ninos"
                               value="<?php echo htmlspecialchars($booking['ninos']); ?>" min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="id_tipo_habitacion_deseado" class="form-label">Tipo de Habitación Deseado</label>
                        <select class="form-select" id="id_tipo_habitacion_deseado" name="id_tipo_habitacion_deseado">
                            <option value="">Cualquier tipo</option>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['id_tipo_habitacion']); ?>" data-precio="<?php echo htmlspecialchars($type['precio_base']); ?>"
                                    <?php echo ($type['id_tipo_habitacion'] == ($booking['id_habitacion'] ? $booking['id_tipo_habitacion'] : '')) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['nombre_tipo']); ?> (Cap: <?php echo htmlspecialchars($type['capacidad']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="button" id="searchRoomsBtn" class="btn btn-outline-primary"><i class="fas fa-search"></i> Buscar Habitaciones Disponibles</button>
                </div>

                <div id="availableRoomsSection">
                    <div class="mb-3">
                        <label for="id_habitacion" class="form-label">Habitación Asignada (Opcional)</label>
                        <select class="form-select" id="id_habitacion" name="id_habitacion">
                            <option value="">Sin asignar (Reserva pendiente)</option>
                            <?php if ($booking['id_habitacion']): // Si ya hay una habitación asignada, mostrarla como opción seleccionada ?>
                                <option value="<?php echo htmlspecialchars($booking['id_habitacion']); ?>"
                                        data-precioBase="<?php echo htmlspecialchars($booking['tipo_habitacion_precio_base']); ?>"
                                        selected>
                                    Hab. <?php echo htmlspecialchars($booking['numero_habitacion']); ?> (Actual)
                                </option>
                            <?php endif; ?>
                            </select>
                        <small class="form-text text-muted">Solo se muestran habitaciones disponibles para las fechas y capacidad seleccionadas.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado de la Reserva</label>
                    <select class="form-select" id="estado" name="estado" required>
                        <option value="pendiente" <?php echo ($booking['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmada" <?php echo ($booking['estado'] == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="check_in" <?php echo ($booking['estado'] == 'check_in') ? 'selected' : ''; ?>>Check-in</option>
                        <option value="check_out" <?php echo ($booking['estado'] == 'check_out') ? 'selected' : ''; ?>>Check-out</option>
                        <option value="cancelada" <?php echo ($booking['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        <option value="no_show" <?php echo ($booking['estado'] == 'no_show') ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
            </fieldset>

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">Resumen de Reserva</legend>
                <div class="mb-3">
                    <label for="precio_total" class="form-label">Precio Total (S/) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="precio_total" name="precio_total" step="0.01" min="0.01"
                           value="<?php echo htmlspecialchars($booking['precio_total'] ?? '0.00'); ?>" required>
                    <small class="form-text text-muted">Este precio se puede recalcular al cambiar la habitación o fechas.</small>
                </div>
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios</label>
                    <textarea class="form-control" id="comentarios" name="comentarios" rows="3"><?php echo htmlspecialchars($booking['comentarios'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/bookings" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Reserva</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const baseUrl = '<?php echo $base_url_for_assets; ?>';

        // --- Búsqueda de Habitaciones Disponibles con AJAX ---
        const fechaEntradaInput = document.getElementById('fecha_entrada');
        const fechaSalidaInput = document.getElementById('fecha_salida');
        const adultosInput = document.getElementById('adultos');
        const ninosInput = document.getElementById('ninos');
        const tipoHabitacionDeseadoSelect = document.getElementById('id_tipo_habitacion_deseado');
        const searchRoomsBtn = document.getElementById('searchRoomsBtn');
        const idHabitacionSelect = document.getElementById('id_habitacion');
        const precioTotalInput = document.getElementById('precio_total');

        // Initial search for available rooms when page loads (useful for editing dates)
        searchAvailableRooms(); // Call once on load

        searchRoomsBtn.addEventListener('click', searchAvailableRooms);

        fechaEntradaInput.addEventListener('change', searchAvailableRooms);
        fechaSalidaInput.addEventListener('change', searchAvailableRooms);
        adultosInput.addEventListener('change', searchAvailableRooms);
        ninosInput.addEventListener('change', searchAvailableRooms); // Add listener for children too
        tipoHabitacionDeseadoSelect.addEventListener('change', searchAvailableRooms);

        idHabitacionSelect.addEventListener('change', updateTotalPrice);

        function searchAvailableRooms() {
            const fechaEntrada = fechaEntradaInput.value;
            const fechaSalida = fechaSalidaInput.value;
            const capacidad = parseInt(adultosInput.value) + parseInt(ninosInput.value);
            const idTipoHabitacion = tipoHabitacionDeseadoSelect.value;
            const currentRoomId = '<?php echo htmlspecialchars($booking['id_habitacion'] ?? ''); ?>'; // Habitación actual de esta reserva

            if (!fechaEntrada || !fechaSalida || fechaEntrada >= fechaSalida) {
                // alert('Por favor, selecciona fechas de entrada y salida válidas. La fecha de salida debe ser posterior a la de entrada.');
                idHabitacionSelect.innerHTML = '<option value="">Selecciona fechas válidas primero</option>';
                updateTotalPrice();
                return;
            }

            idHabitacionSelect.innerHTML = '<option value="">Cargando habitaciones...</option>';

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
                    let currentRoomAlreadyAdded = false;

                    // Si la habitación actual de la reserva está entre las disponibles, la agregamos
                    // y la seleccionamos
                    if (currentRoomId && data.some(room => room.id_habitacion == currentRoomId)) {
                        const room = data.find(r => r.id_habitacion == currentRoomId);
                        const option = document.createElement('option');
                        option.value = room.id_habitacion;
                        option.dataset.precioBase = room.precio_base;
                        option.textContent = `Hab. ${room.numero_habitacion} (${room.nombre_tipo}, S/${parseFloat(room.precio_base).toFixed(2)}/noche) - Actual`;
                        option.selected = true; // Seleccionarla por defecto
                        idHabitacionSelect.appendChild(option);
                        currentRoomAlreadyAdded = true;
                    }
                    // Si la habitación actual no estaba en la lista (ej. porque ya está ocupada por esta misma reserva)
                    // la añadimos para que siga seleccionada
                    else if (currentRoomId) {
                         const currentRoomOption = document.createElement('option');
                         currentRoomOption.value = currentRoomId;
                         currentRoomOption.textContent = `Hab. <?php echo htmlspecialchars($booking['numero_habitacion'] ?? 'N/A'); ?> (Asignada actualmente)`;
                         currentRoomOption.dataset.precioBase = '<?php echo htmlspecialchars($booking['tipo_habitacion_precio_base'] ?? 0); ?>';
                         currentRoomOption.selected = true;
                         idHabitacionSelect.appendChild(currentRoomOption);
                         currentRoomAlreadyAdded = true;
                    }


                    data.forEach(room => {
                        // Evitar duplicados si la habitación actual ya fue agregada
                        if (room.id_habitacion == currentRoomId && currentRoomAlreadyAdded) {
                            return;
                        }
                        const option = document.createElement('option');
                        option.value = room.id_habitacion;
                        option.dataset.precioBase = room.precio_base;
                        option.textContent = `Hab. ${room.numero_habitacion} (${room.nombre_tipo}, S/${parseFloat(room.precio_base).toFixed(2)}/noche)`;
                        idHabitacionSelect.appendChild(option);
                    });

                    if (data.length === 0 && !currentRoomAlreadyAdded) {
                        idHabitacionSelect.innerHTML = '<option value="">No se encontraron habitaciones disponibles</option>';
                    }
                    updateTotalPrice();
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
            let selectedRoomPrice = selectedRoomOption ? parseFloat(selectedRoomOption.dataset.precioBase || 0) : 0;

            const selectedRoomTypeId = tipoHabitacionDeseadoSelect.value;
            const selectedRoomTypeOption = tipoHabitacionDeseadoSelect.options[tipoHabitacionDeseadoSelect.selectedIndex];
            let selectedRoomTypePrice = selectedRoomTypeOption ? parseFloat(selectedRoomTypeOption.dataset.precio || 0) : 0;

            // Si hay una habitación asignada y seleccionada, su precio prevalece
            let effectivePricePerNight = 0;
            if (selectedRoomPrice > 0) {
                effectivePricePerNight = selectedRoomPrice;
            } else if (selectedRoomTypePrice > 0) {
                effectivePricePerNight = selectedRoomTypePrice;
            } else if ('<?php echo htmlspecialchars($booking['tipo_habitacion_precio_base'] ?? 0); ?>' > 0 && !selectedRoomOption.value) {
                // Si no se seleccionó una nueva habitación y no se seleccionó un tipo deseado,
                // pero la reserva ya tenía un precio base de tipo de habitación
                effectivePricePerNight = parseFloat('<?php echo htmlspecialchars($booking['tipo_habitacion_precio_base'] ?? 0); ?>');
            }


            if (fechaEntrada && fechaSalida && fechaEntrada < fechaSalida) {
                const date1 = new Date(fechaEntrada);
                const date2 = new Date(fechaSalida);
                const diffTime = Math.abs(date2 - date1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let finalPrice = effectivePricePerNight * diffDays;
                precioTotalInput.value = finalPrice.toFixed(2);
            } else {
                precioTotalInput.value = '0.00';
            }
        }

        // Inicializar el precio total al cargar la página y cuando cambian fechas/adultos/niños
        updateTotalPrice();
        // searchAvailableRooms(); // Ya se llama una vez al cargar la página
    });
</script>