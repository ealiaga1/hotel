<h1 class="mb-4">Tablero de Recepción</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="row">
    <?php if (!empty($rooms)): ?>
        <?php foreach ($rooms as $room): ?>
            <?php
            $cardClass = 'bg-light'; // Default
            $textColor = 'text-white'; // Default to white for colored cards
            $statusText = '';
            $bookingInfo = '';
            $statusIconClass = ''; // Para el icono de estado

            switch ($room['estado']) {
                case 'disponible':
                    $cardClass = 'bg-success';
                    $statusText = 'Disponible';
                    $statusIconClass = 'fas fa-door-open'; // Icono para disponible
                    break;
                case 'ocupada':
                    $cardClass = 'bg-danger';
                    $statusText = 'Ocupada';
                    $statusIconClass = 'fas fa-bed'; // Icono para ocupada
                    if ($room['active_booking']) {
                        $bookingInfo = 'Huésped: ' . htmlspecialchars($room['active_booking']['huesped_nombre'] . ' ' . $room['active_booking']['huesped_apellido']) . '<br>';
                        $bookingInfo .= 'Salida: ' . htmlspecialchars($room['active_booking']['fecha_salida']);
                    }
                    break;
                case 'sucia':
                    $cardClass = 'bg-warning text-dark'; // Texto oscuro para fondo claro
                    $textColor = 'text-dark';
                    $statusText = 'Sucia';
                    $statusIconClass = 'fas fa-broom'; // Icono para sucia
                    break;
                case 'mantenimiento':
                    $cardClass = 'bg-secondary';
                    $statusText = 'Mantenimiento';
                    $statusIconClass = 'fas fa-tools'; // Icono para mantenimiento
                    break;
                default:
                    $cardClass = 'bg-info';
                    $statusText = ucfirst($room['estado']);
                    $statusIconClass = 'fas fa-question-circle'; // Icono por defecto
                    break;
            }
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card <?php echo $cardClass; ?> shadow h-100 room-card" style="cursor: pointer;" 
                     data-room-id="<?php echo htmlspecialchars($room['id_habitacion']); ?>"
                     data-room-number="<?php echo htmlspecialchars($room['numero_habitacion']); ?>"
                     data-room-status="<?php echo htmlspecialchars($room['estado']); ?>"
                     data-booking-id="<?php echo htmlspecialchars($room['active_booking']['id_reserva'] ?? ''); ?>"
                     data-guest-id="<?php echo htmlspecialchars($room['active_booking']['id_huesped'] ?? ''); ?>"
                     data-guest-name="<?php echo htmlspecialchars($room['active_booking']['huesped_nombre'] . ' ' . $room['active_booking']['huesped_apellido'] ?? ''); ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 <?php echo $textColor; ?>">Hab. <?php echo htmlspecialchars($room['numero_habitacion']); ?></h5>
                            <i class="<?php echo $statusIconClass; ?> fa-2x <?php echo $textColor; ?>"></i> <!-- Icono de estado -->
                        </div>
                        <p class="card-text <?php echo $textColor; ?>"><small><?php echo htmlspecialchars($room['nombre_tipo']); ?> (Piso: <?php echo htmlspecialchars($room['piso']); ?>)</small></p>
                        <hr class="my-2 border-white opacity-50"> <!-- Línea divisoria más sutil -->
                        <p class="card-text <?php echo $textColor; ?>"><strong>Estado:</strong> <?php echo $statusText; ?></p>
                        <?php if ($bookingInfo): ?>
                            <p class="card-text <?php echo $textColor; ?>"><small><?php echo $bookingInfo; ?></small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                No hay habitaciones registradas en el sistema.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para acciones de habitación -->
<div class="modal fade" id="roomActionModal" tabindex="-1" aria-labelledby="roomActionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roomActionModalLabel">Acciones para Habitación <span id="modalRoomNumber"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>ID Habitación:</strong> <span id="modalRoomId"></span></p>
        <p><strong>Estado Actual:</strong> <span id="modalRoomStatus"></span></p>
        <p><strong>Huésped:</strong> <span id="modalGuestInfo"></span></p>
        <p><strong>Reserva Activa:</strong> <span id="modalBookingId"></span></p>
        <hr>
        <div id="modalActions">
          <!-- Botones de acción se insertarán aquí con JS -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomCards = document.querySelectorAll('.room-card');
    const roomActionModal = new bootstrap.Modal(document.getElementById('roomActionModal'));
    
    const modalRoomNumber = document.getElementById('modalRoomNumber');
    const modalRoomId = document.getElementById('modalRoomId');
    const modalRoomStatus = document.getElementById('modalRoomStatus');
    const modalGuestInfo = document.getElementById('modalGuestInfo');
    const modalBookingId = document.getElementById('modalBookingId');
    const modalActions = document.getElementById('modalActions');

    roomCards.forEach(card => {
        card.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            const roomNumber = this.dataset.roomNumber;
            const roomStatus = this.dataset.roomStatus;
            const bookingId = this.dataset.bookingId;
            const guestId = this.dataset.guestId; // ID del huésped para precarga en Venta Directa
            const guestName = this.dataset.guestName; // Nombre del huésped para precarga en Venta Directa
            const guestInfoHtml = this.querySelector('.card-text:last-of-type small') ? this.querySelector('.card-text:last-of-type small').innerHTML : 'N/A';

            modalRoomNumber.textContent = roomNumber;
            modalRoomId.textContent = roomId;
            modalRoomStatus.textContent = roomStatus.charAt(0).toUpperCase() + roomStatus.slice(1);
            modalGuestInfo.innerHTML = (roomStatus === 'ocupada' && bookingId) ? guestInfoHtml : 'N/A';
            modalBookingId.textContent = (roomStatus === 'ocupada' && bookingId) ? bookingId : 'N/A';

            modalActions.innerHTML = ''; // Limpiar acciones previas

            const baseUrl = '<?php echo $base_url_for_assets; ?>';

            // Botón de Editar Habitación (siempre visible para todos los estados)
            modalActions.innerHTML += `<a href="${baseUrl}rooms/edit/${roomId}" class="btn btn-info me-2"><i class="fas fa-edit"></i> Editar Habitación</a>`;

            // Acciones específicas por estado
            switch (roomStatus) {
                case 'disponible':
                    modalActions.innerHTML += `
                        <a href="${baseUrl}bookings/create?room_id=${roomId}" class="btn btn-success me-2"><i class="fas fa-plus"></i> Asignar Reserva</a>
                    `;
                    break;
                case 'ocupada':
                    modalActions.innerHTML += `
                        <a href="${baseUrl}bookings/checkout/${bookingId}" class="btn btn-danger me-2"><i class="fas fa-sign-out-alt"></i> Checkout</a>
                        <a href="${baseUrl}bookings/edit/${bookingId}" class="btn btn-secondary me-2"><i class="fas fa-eye"></i> Ver Reserva</a>
                    `;
                    break;
                case 'sucia':
                    // Acciones para habitaciones sucias (sin botón "Marcar Limpia" directo aquí)
                    break;
                case 'mantenimiento':
                    // Acciones para habitaciones en mantenimiento (sin botón "Marcar Disponible" directo aquí)
                    break;
            }

            // Añadir el botón de Venta Directa
            let saleUrl = `${baseUrl}cash_register/sell_product?room_id=${roomId}`;
            // Si la habitación está ocupada y hay un huésped asociado, precargar su ID y nombre
            if (roomStatus === 'ocupada' && guestId) {
                saleUrl += `&guest_id=${guestId}&guest_name=${encodeURIComponent(guestName)}`;
            }
            modalActions.innerHTML += `
                <a href="${saleUrl}" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Venta Directa</a>
            `;

            roomActionModal.show();
        });
    });

    // La función window.sendRoomStatusUpdate fue eliminada en la revisión anterior
    // ya que ahora la edición de estado de habitación se hace vía la página de edición de habitación.
});
</script>
