<h1 class="mb-4">Mesas del Restaurante</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end align-items-center mb-3">
    <a href="/hotel_completo/public/restaurant/tables/create" class="btn btn-primary"><i class="fas fa-plus"></i> Añadir Nueva Mesa</a>
</div>

<div class="row">
    <?php if (!empty($tables)): ?>
        <?php foreach ($tables as $table): ?>
            <?php
            $cardClass = 'bg-light'; // Default
            $textColor = 'text-white'; // Default to white for colored cards
            $statusText = '';
            $statusIconClass = '';
            $orderInfo = '';
            $orderId = ''; // Para pasar el ID de la orden si existe

            switch ($table['estado']) {
                case 'disponible':
                    $cardClass = 'bg-success';
                    $statusText = 'Disponible';
                    $statusIconClass = 'fas fa-chair'; // Icono para mesa disponible
                    $textColor = 'text-white';
                    break;
                case 'ocupada':
                    $cardClass = 'bg-danger';
                    $statusText = 'Ocupada';
                    $statusIconClass = 'fas fa-users'; // Icono para mesa ocupada
                    $textColor = 'text-white';
                    if ($table['active_order']) {
                        $orderInfo = 'Pedido: #' . htmlspecialchars($table['active_order']['id_pedido']);
                        if (!empty($table['cliente_info'])) {
                            $orderInfo .= '<br>Cliente: ' . htmlspecialchars($table['cliente_info']);
                        }
                        $orderInfo .= '<br>Total: S/ ' . number_format(htmlspecialchars($table['active_order']['total_pedido']), 2, '.', ',');
                        $orderId = htmlspecialchars($table['active_order']['id_pedido']);
                    }
                    break;
                case 'en_limpieza':
                    $cardClass = 'bg-warning text-dark'; // Texto oscuro para fondo claro
                    $statusText = 'En Limpieza';
                    $statusIconClass = 'fas fa-broom'; // Icono para limpieza
                    $textColor = 'text-dark';
                    break;
                case 'reservada': // Si tienes este estado
                    $cardClass = 'bg-info';
                    $statusText = 'Reservada';
                    $statusIconClass = 'fas fa-bookmark';
                    $textColor = 'text-white';
                    break;
                default:
                    $cardClass = 'bg-secondary';
                    $statusText = ucfirst($table['estado']);
                    $statusIconClass = 'fas fa-question';
                    $textColor = 'text-white';
                    break;
            }
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card <?php echo $cardClass; ?> shadow h-100 table-card" style="cursor: pointer;" 
                     data-table-id="<?php echo htmlspecialchars($table['id_mesa']); ?>"
                     data-table-number="<?php echo htmlspecialchars($table['numero_mesa']); ?>"
                     data-table-status="<?php echo htmlspecialchars($table['estado']); ?>"
                     data-order-id="<?php echo $orderId; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 <?php echo $textColor; ?>">Mesa <?php echo htmlspecialchars($table['numero_mesa']); ?></h5>
                            <i class="<?php echo $statusIconClass; ?> fa-2x <?php echo $textColor; ?>"></i> <!-- Icono de estado -->
                        </div>
                        <p class="card-text <?php echo $textColor; ?>"><small>Capacidad: <?php echo htmlspecialchars($table['capacidad']); ?></small></p>
                        <hr class="my-2 border-white opacity-50">
                        <p class="card-text <?php echo $textColor; ?>"><strong>Estado:</strong> <?php echo $statusText; ?></p>
                        <?php if ($orderInfo): ?>
                            <p class="card-text <?php echo $textColor; ?>"><small><?php echo $orderInfo; ?></small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                No hay mesas registradas en el sistema.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para acciones de mesa -->
<div class="modal fade" id="tableActionModal" tabindex="-1" aria-labelledby="tableActionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tableActionModalLabel">Acciones para Mesa <span id="modalTableNumber"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>ID Mesa:</strong> <span id="modalTableId"></span></p>
        <p><strong>Estado Actual:</strong> <span id="modalTableStatus"></span></p>
        <p><strong>Orden Activa:</strong> <span id="modalOrderInfo"></span></p>
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
    const tableCards = document.querySelectorAll('.table-card');
    const tableActionModal = new bootstrap.Modal(document.getElementById('tableActionModal'));
    
    const modalTableNumber = document.getElementById('modalTableNumber');
    const modalTableId = document.getElementById('modalTableId');
    const modalTableStatus = document.getElementById('modalTableStatus');
    const modalOrderInfo = document.getElementById('modalOrderInfo');
    const modalActions = document.getElementById('modalActions');

    tableCards.forEach(card => {
        card.addEventListener('click', function() {
            const tableId = this.dataset.tableId;
            const tableNumber = this.dataset.tableNumber;
            const tableStatus = this.dataset.tableStatus;
            const orderId = this.dataset.orderId; // ID de la orden activa, si existe
            const orderInfoHtml = this.querySelector('.card-text:last-of-type small') ? this.querySelector('.card-text:last-of-type small').innerHTML : 'N/A';

            modalTableNumber.textContent = tableNumber;
            modalTableId.textContent = tableId;
            modalTableStatus.textContent = tableStatus.charAt(0).toUpperCase() + tableStatus.slice(1).replace('_', ' ');
            modalOrderInfo.innerHTML = (tableStatus === 'ocupada' && orderId) ? orderInfoHtml : 'N/A';

            modalActions.innerHTML = ''; // Limpiar acciones previas

            const baseUrl = '<?php echo $base_url_for_assets; ?>';

            // Siempre incluir el botón de Editar Mesa
            modalActions.innerHTML += `<a href="${baseUrl}restaurant/tables/edit/${tableId}" class="btn btn-info me-2"><i class="fas fa-edit"></i> Editar Mesa</a>`;

            // Acciones específicas por estado de mesa
            switch (tableStatus) {
                case 'disponible':
                    modalActions.innerHTML += `
                        <a href="${baseUrl}restaurant/orders/create?table_id=${tableId}" class="btn btn-success me-2"><i class="fas fa-plus"></i> Tomar Pedido</a>
                        <button type="button" class="btn btn-warning" onclick="sendTableStatusUpdate(${tableId}, 'en_limpieza', '${tableNumber}')"><i class="fas fa-broom"></i> En Limpieza</button>
                    `;
                    break;
                case 'ocupada':
                    modalActions.innerHTML += `
                        <a href="${baseUrl}restaurant/orders/view/${orderId}" class="btn btn-primary me-2"><i class="fas fa-eye"></i> Ver Pedido</a>
                        <a href="${baseUrl}restaurant/orders/create?table_id=${tableId}&add_to_order=${orderId}" class="btn btn-success me-2"><i class="fas fa-cart-plus"></i> Añadir a Pedido</a>
                        <button type="button" class="btn btn-danger" onclick="sendTableStatusUpdate(${tableId}, 'en_limpieza', '${tableNumber}')" title="Marcar como 'En Limpieza' y liberar para nuevo pedido"><i class="fas fa-times-circle"></i> Finalizar Pedido</button>
                    `;
                    break;
                case 'en_limpieza':
                    modalActions.innerHTML += `
                        <button type="button" class="btn btn-success me-2" onclick="sendTableStatusUpdate(${tableId}, 'disponible', '${tableNumber}')"><i class="fas fa-check"></i> Marcar Disponible</button>
                    `;
                    break;
                case 'reservada': // Si usas este estado, puedes añadir acciones aquí
                    modalActions.innerHTML += `
                        <button type="button" class="btn btn-success me-2" onclick="sendTableStatusUpdate(${tableId}, 'ocupada', '${tableNumber}')"><i class="fas fa-user-check"></i> Marcar Ocupada</button>
                        <button type="button" class="btn btn-secondary" onclick="sendTableStatusUpdate(${tableId}, 'disponible', '${tableNumber}')"><i class="fas fa-undo"></i> Cancelar Reserva</button>
                    `;
                    break;
            }

            tableActionModal.show();
        });
    });

    // Función global para enviar el formulario POST para actualizar el estado de la mesa
    window.sendTableStatusUpdate = function(tableId, newStatus, tableNumber) {
        if (confirm(`¿Estás seguro de cambiar el estado de la mesa ${tableNumber} a "${newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ')}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${baseUrl}restaurant/tables/update_status/${tableId}/${newStatus}`;
            
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>
