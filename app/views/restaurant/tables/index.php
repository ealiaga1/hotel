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

<style>
.mesas-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
}
.mesa-circulo {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    margin: 0 0 15px 0;
    box-shadow: 0 0 8px rgba(0,0,0,0.08);
    transition: box-shadow 0.2s, transform 0.2s;
    border: 3px solid #eee;
    cursor: pointer;
    background: #f8f9fa;
    position: relative;
    min-width: 110px;
}
.mesa-circulo:hover {
    box-shadow: 0 0 20px 2px #007bff55;
    transform: scale(1.08);
}
.mesa-circulo.disponible { border-color: #28a745; background: #eafaea; }
.mesa-circulo.ocupada { border-color: #dc3545; background: #ffeaea; }
.mesa-circulo.reservada { border-color: #ffc107; background: #fff8e1; }
.mesa-circulo.en_limpieza { border-color: #17a2b8; background: #e1f7fa; }
.mesa-numero {
    font-size: 2.1rem;
    font-weight: bold;
}
.mesa-estado {
    font-size: 0.93rem;
    margin-top: 6px;
    text-transform: capitalize;
}
.mesa-info-pedido {
    font-size: 0.85rem;
    color: #333;
    text-align: center;
    margin-top: 7px;
}
</style>

<?php if (!empty($tables)): ?>
    <div class="mesas-flex">
        <?php foreach ($tables as $mesa): ?>
            <?php
                $clase_estado = htmlspecialchars($mesa['estado']);
                $info_pedido = '';
                // Si la mesa está ocupada y tiene pedido activo, mostrar info
                if ($mesa['estado'] === 'ocupada' && !empty($mesa['active_order'])) {
                    $info_pedido = 'Pedido: #' . htmlspecialchars($mesa['active_order']['id_pedido']);
                    if (!empty($mesa['cliente_info'])) {
                        $info_pedido .= '<br>Cliente: ' . htmlspecialchars($mesa['cliente_info']);
                    }
                    $info_pedido .= '<br>Total: S/ ' . number_format($mesa['active_order']['total_pedido'], 2, '.', ',');
                }
            ?>
            <div class="mesa-circulo <?= $clase_estado ?>"
                 title="Mesa #<?= htmlspecialchars($mesa['numero_mesa']) ?>"
                 data-table-id="<?= htmlspecialchars($mesa['id_mesa']) ?>"
                 data-table-number="<?= htmlspecialchars($mesa['numero_mesa']) ?>"
                 data-table-status="<?= htmlspecialchars($mesa['estado']) ?>"
                 data-order-id="<?= !empty($mesa['active_order']) ? htmlspecialchars($mesa['active_order']['id_pedido']) : '' ?>">
                <div class="mesa-numero"><?= htmlspecialchars($mesa['numero_mesa']) ?></div>
                <div class="mesa-estado"><?= ucfirst(str_replace('_', ' ', $mesa['estado'])) ?></div>
                <?php if ($info_pedido): ?>
                    <div class="mesa-info-pedido"><?= $info_pedido ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center" role="alert">
        No hay mesas registradas en el sistema.
    </div>
<?php endif; ?>

<!-- Modal y JS (igual que antes, solo adaptando selectores si es necesario) -->
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
    const tableCards = document.querySelectorAll('.mesa-circulo');
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
            const orderId = this.dataset.orderId;
            const orderInfoHtml = this.querySelector('.mesa-info-pedido') ? this.querySelector('.mesa-info-pedido').innerHTML : 'N/A';

            modalTableNumber.textContent = tableNumber;
            modalTableId.textContent = tableId;
            modalTableStatus.textContent = tableStatus.charAt(0).toUpperCase() + tableStatus.slice(1).replace('_', ' ');
            modalOrderInfo.innerHTML = (tableStatus === 'ocupada' && orderId) ? orderInfoHtml : 'N/A';

            modalActions.innerHTML = '';

            const baseUrl = '<?php echo $base_url_for_assets ?? "/hotel_completo/public/"; ?>';

            // Editar Mesa
            modalActions.innerHTML += `<a href="${baseUrl}restaurant/tables/edit/${tableId}" class="btn btn-info me-2"><i class="fas fa-edit"></i> Editar Mesa</a>`;

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
                case 'reservada':
                    modalActions.innerHTML += `
                        <button type="button" class="btn btn-success me-2" onclick="sendTableStatusUpdate(${tableId}, 'ocupada', '${tableNumber}')"><i class="fas fa-user-check"></i> Marcar Ocupada</button>
                        <button type="button" class="btn btn-secondary" onclick="sendTableStatusUpdate(${tableId}, 'disponible', '${tableNumber}')"><i class="fas fa-undo"></i> Cancelar Reserva</button>
                    `;
                    break;
            }

            tableActionModal.show();
        });
    });

    window.sendTableStatusUpdate = function(tableId, newStatus, tableNumber) {
        if (confirm(`¿Estás seguro de cambiar el estado de la mesa ${tableNumber} a "${newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ')}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo $base_url_for_assets ?? "/hotel_completo/public/"; ?>restaurant/tables/update_status/' + tableId + '/' + newStatus;
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>
