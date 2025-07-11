<h1 class="mb-4 recepcion-header recepcion-fadein"><i class="fas fa-concierge-bell me-2"></i>Tablero de Recepción</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show recepcion-fadein" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger recepcion-fadein" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<style>
/* Encabezado atractivo */
.recepcion-header {
    background: linear-gradient(90deg, #2196f3 0%, #64b5f6 100%);
    color: #fff;
    padding: 18px 40px 14px 28px;
    border-radius: 18px;
    box-shadow: 0 4px 20px #2196f322;
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 2.3rem !important;
    letter-spacing: 1px;
    display: inline-block;
}

/* Animación de entrada suave */
.recepcion-fadein {
    opacity: 0;
    transform: translateY(40px);
    animation: recepcionFadein 0.8s ease-out forwards;
}
@keyframes recepcionFadein {
    to {
        opacity: 1;
        transform: none;
    }
}

/* Tarjetas modernas para habitaciones */
.room-card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 4px 24px #1976d233;
    transition: box-shadow 0.25s, transform 0.23s;
    background: #f8f9fa;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    animation: recepcionFadein 1.1s cubic-bezier(.19,1,.22,1) backwards;
}
.room-card:hover {
    box-shadow: 0 8px 32px rgba(33,150,243,0.18), 0 2px 16px #42a5f540;
    transform: translateY(-8px) scale(1.025);
    border: 1.5px solid #1976d2;
    z-index: 2;
}
.room-card.bg-success { background: linear-gradient(120deg, #e3fcec 70%, #bbdefb 100%); }
.room-card.bg-danger { background: linear-gradient(120deg, #ffdde1 60%, #2196f3 100%); }
.room-card.bg-warning { background: linear-gradient(120deg, #fff9e5 70%, #64b5f6 100%); }
.room-card.bg-secondary { background: linear-gradient(120deg, #e3e8ef 70%, #90caf9 100%); }
.room-card.bg-info { background: linear-gradient(120deg, #e3f2fd 70%, #64b5f6 100%); }

/* Colores de textos y sombras para mejor visibilidad */
.room-card .card-title,
.room-card .card-text,
.room-card .card-text small {
    color: #17325c !important;
    text-shadow: 0 1px 8px #fff8, 0 1px 1px #0002;
    font-weight: 600;
}
.room-card .card-title {
    font-size: 1.3rem;
    font-weight: bold;
    letter-spacing: 1px;
}

/* Estado destacado */
.room-card .estado-label {
    color: #17325c !important;
    font-weight: 700;
    font-size: 1rem;
}

/* Subtítulo/small */
.room-card .card-text small {
    font-weight: 500;
    opacity: 0.88;
}

/* Iconos grandes y visibles en cada estado */
.room-card .fa, .room-card .fas, .room-card .far {
    color: #1565c0 !important;
    text-shadow: 0 1px 4px #fff, 0 2px 8px #1976d288;
    font-size: 2.1rem;
}
.room-card.bg-success .fa, .room-card.bg-success .fas { color: #1b5e20 !important; }
.room-card.bg-danger .fa, .room-card.bg-danger .fas { color: #b71c1c !important; }
.room-card.bg-warning .fa, .room-card.bg-warning .fas { color: #ff8f00 !important; }
.room-card.bg-secondary .fa, .room-card.bg-secondary .fas { color: #37474f !important; }
.room-card.bg-info .fa, .room-card.bg-info .fas { color: #1976d2 !important; }

/* Linea divisoria sutil */
.room-card hr {
    background: #1976d2;
    opacity: .18;
    height: 2px;
    margin: 6px 0;
}

/* MODAL limpio y profesional */
.clean-modal .modal-content {
    border-radius: 22px;
    box-shadow: 0 6px 32px #1976d266;
    padding: 0 8px;
}
.clean-modal .modal-header {
    background: linear-gradient(90deg, #e3f2fd 0%, #bbdefb 100%);
    border-radius: 22px 22px 0 0;
    box-shadow: 0 2px 12px #2196f312;
}
.clean-modal .modal-title {
    font-size: 1.25rem;
}
#modalActions .btn {
    min-width: 170px;
    margin: 0;
    padding: 0.7rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    border-radius: 14px;
    box-shadow: 0 2px 8px #42a5f622;
    transition: transform 0.09s;
    display: flex;
    align-items: center;
    justify-content: center;
}
#modalActions .btn i {
    margin-right: 7px;
    font-size: 1.2em;
}
#modalActions .btn:hover {
    transform: translateY(-2px) scale(1.035);
}
#modalActions .row {
    --bs-gutter-x: 0.5rem;
}
@media (max-width: 575.98px) {
    #modalActions .btn {
        min-width: 100%;
        font-size: 0.97rem;
        margin-bottom: 4px;
    }
}
</style>

<div class="row">
    <?php if (!empty($rooms)): ?>
        <?php foreach ($rooms as $room): ?>
            <?php
            $cardClass = 'bg-light';
            $statusText = '';
            $bookingInfo = '';
            $statusIconClass = '';

            switch ($room['estado']) {
                case 'disponible':
                    $cardClass = 'bg-success';
                    $statusText = 'Disponible';
                    $statusIconClass = 'fas fa-door-open';
                    break;
                case 'ocupada':
                    $cardClass = 'bg-danger';
                    $statusText = 'Ocupada';
                    $statusIconClass = 'fas fa-bed';
                    if ($room['active_booking']) {
                        $bookingInfo = 'Huésped: ' . htmlspecialchars($room['active_booking']['huesped_nombre'] . ' ' . $room['active_booking']['huesped_apellido']) . '<br>';
                        $bookingInfo .= 'Salida: ' . htmlspecialchars($room['active_booking']['fecha_salida']);
                    }
                    break;
                case 'sucia':
                    $cardClass = 'bg-warning';
                    $statusText = 'Sucia';
                    $statusIconClass = 'fas fa-broom';
                    break;
                case 'mantenimiento':
                    $cardClass = 'bg-secondary';
                    $statusText = 'Mantenimiento';
                    $statusIconClass = 'fas fa-tools';
                    break;
                default:
                    $cardClass = 'bg-info';
                    $statusText = ucfirst($room['estado']);
                    $statusIconClass = 'fas fa-question-circle';
                    break;
            }
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4 recepcion-fadein">
                <div class="card <?php echo $cardClass; ?> shadow h-100 room-card"
                     data-room-id="<?php echo htmlspecialchars($room['id_habitacion']); ?>"
                     data-room-number="<?php echo htmlspecialchars($room['numero_habitacion']); ?>"
                     data-room-status="<?php echo htmlspecialchars($room['estado']); ?>"
                     data-booking-id="<?php echo htmlspecialchars($room['active_booking']['id_reserva'] ?? ''); ?>"
                     data-guest-id="<?php echo htmlspecialchars($room['active_booking']['id_huesped'] ?? ''); ?>"
                     data-guest-name="<?php echo htmlspecialchars($room['active_booking']['huesped_nombre'] . ' ' . $room['active_booking']['huesped_apellido'] ?? ''); ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Hab. <?php echo htmlspecialchars($room['numero_habitacion']); ?></h5>
                            <i class="<?php echo $statusIconClass; ?> fa-2x"></i>
                        </div>
                        <p class="card-text"><small><?php echo htmlspecialchars($room['nombre_tipo']); ?> (Piso: <?php echo htmlspecialchars($room['piso']); ?>)</small></p>
                        <hr>
                        <p class="card-text estado-label"><strong>Estado:</strong> <?php echo $statusText; ?></p>
                        <?php if ($bookingInfo): ?>
                            <p class="card-text"><small><?php echo $bookingInfo; ?></small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 recepcion-fadein">
            <div class="alert alert-info text-center" role="alert">
                No hay habitaciones registradas en el sistema.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL para acciones de habitación -->
<div class="modal fade" id="roomActionModal" tabindex="-1" aria-labelledby="roomActionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content clean-modal">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="roomActionModalLabel">
          <i class="fas fa-door-open text-primary me-2"></i>
          Acciones para Habitación <span id="modalRoomNumber"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-1">
        <div class="mb-3">
          <div class="fw-semibold small text-secondary">ID Habitación:</div>
          <div class="mb-2" id="modalRoomId"></div>
          <div class="fw-semibold small text-secondary">Estado Actual:</div>
          <div class="mb-2" id="modalRoomStatus"></div>
          <div class="fw-semibold small text-secondary">Huésped:</div>
          <div class="mb-2" id="modalGuestInfo"></div>
          <div class="fw-semibold small text-secondary">Reserva Activa:</div>
          <div class="mb-2" id="modalBookingId"></div>
        </div>
        <hr class="my-3">
        <div id="modalActions" class="row g-2 justify-content-center">
          <!-- Botones de acción se insertarán aquí con JS -->
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
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
            const guestId = this.dataset.guestId;
            const guestName = this.dataset.guestName;
            const guestInfoHtml = this.querySelector('.card-text:last-of-type small')
                ? this.querySelector('.card-text:last-of-type small').innerHTML
                : 'N/A';

            modalRoomNumber.textContent = roomNumber;
            modalRoomId.textContent = roomId;
            modalRoomStatus.textContent = roomStatus.charAt(0).toUpperCase() + roomStatus.slice(1);
            modalGuestInfo.innerHTML = (roomStatus === 'ocupada' && bookingId) ? guestInfoHtml : 'N/A';
            modalBookingId.textContent = (roomStatus === 'ocupada' && bookingId) ? bookingId : 'N/A';

            modalActions.innerHTML = '';

            const baseUrl = '<?php echo $base_url_for_assets; ?>';
            const actions = [];

            actions.push(
              `<div class="col-12 col-sm-6">
                <a href="${baseUrl}rooms/edit/${roomId}" class="btn btn-info w-100 mb-1">
                  <i class="fas fa-edit"></i> Editar Habitación
                </a>
              </div>`
            );

            if (roomStatus === 'disponible') {
              actions.push(
                `<div class="col-12 col-sm-6">
                  <a href="${baseUrl}bookings/create?room_id=${roomId}" class="btn btn-success w-100 mb-1">
                    <i class="fas fa-plus"></i> Asignar Reserva
                  </a>
                </div>`
              );
            } else if (roomStatus === 'ocupada') {
              actions.push(
                `<div class="col-12 col-sm-6">
                  <a href="${baseUrl}bookings/checkout/${bookingId}" class="btn btn-danger w-100 mb-1">
                    <i class="fas fa-sign-out-alt"></i> Checkout
                  </a>
                </div>
                <div class="col-12 col-sm-6">
                  <a href="${baseUrl}bookings/edit/${bookingId}" class="btn btn-secondary w-100 mb-1">
                    <i class="fas fa-eye"></i> Ver Reserva
                  </a>
                </div>`
              );
            }

            let saleUrl = `${baseUrl}cash_register/sell_product?room_id=${roomId}`;
            if (roomStatus === 'ocupada' && guestId) {
              saleUrl += `&guest_id=${guestId}&guest_name=${encodeURIComponent(guestName)}`;
            }
            actions.push(
              `<div class="col-12 col-sm-6">
                <a href="${saleUrl}" class="btn btn-primary w-100 mb-1">
                  <i class="fas fa-shopping-cart"></i> Venta Directa
                </a>
              </div>`
            );

            modalActions.innerHTML = actions.join('');
            roomActionModal.show();
        });
    });
});
</script>
