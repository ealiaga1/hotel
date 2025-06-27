<h1 class="mb-4 mesa-header mesa-fadein"><i class="fas fa-utensils me-2"></i>Mesas del Restaurante</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show mesa-fadein" role="alert">
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
    <div class="alert alert-danger mesa-fadein" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end align-items-center mb-3 mesa-fadein">
    <a href="/hotel_completo/public/restaurant/tables/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Añadir Nueva Mesa
    </a>
</div>

<style>
/* Encabezado atractivo */
.mesa-header {
    background: linear-gradient(90deg, #2196f3 0%, #64b5f6 100%);
    color: #fff;
    padding: 16px 40px 14px 28px;
    border-radius: 18px;
    box-shadow: 0 4px 18px #2196f322;
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 2.3rem !important;
    letter-spacing: 1px;
    display: inline-block;
}

/* Animación de entrada suave */
.mesa-fadein {
    opacity: 0;
    transform: translateY(40px);
    animation: mesaFadein 0.8s ease-out forwards;
}
@keyframes mesaFadein {
    to {
        opacity: 1;
        transform: none;
    }
}

.mesas-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 30px 36px;
    justify-content: center;
    align-items: flex-start;
    margin-top: 24px;
    margin-bottom: 24px;
}

.restaurant-table-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    background: none;
    transition: box-shadow .17s, transform .17s;
    border-radius: 22px;
    padding: 12px 0 0 0;
    position: relative;
}

.restaurant-table-card:hover .circle-table {
    box-shadow: 0 8px 28px #1976d255, 0 2px 8px #2196f366;
    transform: scale(1.09);
}

.circle-table {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    background: linear-gradient(135deg, #e3f2fd 70%, #bbdefb 100%);
    color: #1565c0;
    border: 6px solid #1976d2;
    box-shadow: 0 2px 10px #1976d233;
    margin-bottom: 7px;
    position: relative;
    transition: box-shadow .17s, transform .17s;
}

.mesa-disponible .circle-table {
    background: linear-gradient(135deg, #e3fcec 70%, #bbffcc 100%);
    border-color: #43a047;
    color: #1b5e20;
}
.mesa-ocupada .circle-table {
    background: linear-gradient(135deg, #ffdde1 60%, #fbc2eb 100%);
    border-color: #d32f2f;
    color: #b71c1c;
}
.mesa-reservada .circle-table {
    background: linear-gradient(135deg, #fff9e5 70%, #ffe082 100%);
    border-color: #ffb300;
    color: #bf360c;
}
.mesa-en_limpieza .circle-table {
    background: linear-gradient(135deg, #e1f7fa 80%, #bbdefb 100%);
    border-color: #17a2b8;
    color: #0d7682;
}

.table-number {
    font-size: 2.2rem;
    font-weight: bold;
    margin-top: 1px;
    margin-bottom: 1px;
    text-shadow: 0 1px 8px #fff8, 0 1px 1px #0001;
}
.estado-mesa {
    font-size: 1.01rem;
    font-weight: 600;
    color: #1976d2;
    text-align: center;
    margin-bottom: 2px;
    text-shadow: 0 1px 4px #fff, 0 1px 1px #0002;
    letter-spacing: .2px;
}
.restaurant-table-card .fa-utensils {
    font-size: 2.1rem;
    margin-bottom: 3px;
}
.mesa-info-pedido {
    font-size: 0.89rem;
    color: #333;
    text-align: center;
    margin-top: 6px;
    min-height: 36px;
    font-weight: 400;
    background: #f5faff;
    border-radius: 10px;
    padding: 3px 8px 2px 8px;
    box-shadow: 0 1px 6px #1976d222;
}

/* Responsive */
@media (max-width: 767.98px) {
    .mesas-grid {
        gap: 22px;
    }
    .circle-table {
        width: 68px;
        height: 68px;
        font-size: 1.2rem;
    }
    .table-number {
        font-size: 1.18rem;
    }
    .restaurant-table-card .fa-utensils {
        font-size: 1.35rem;
    }
}
</style>

<?php if (!empty($tables)): ?>
    <div class="mesas-grid">
        <?php foreach ($tables as $mesa): ?>
            <?php
                // Estado para clases de color
                $clase_estado = 'mesa-' . htmlspecialchars($mesa['estado']);
                $info_pedido = '';
                // Mostrar info si está ocupada y tiene pedido activo
                if ($mesa['estado'] === 'ocupada' && !empty($mesa['active_order'])) {
                    $info_pedido = 'Pedido: #' . htmlspecialchars($mesa['active_order']['id_pedido']);
                    if (!empty($mesa['cliente_info'])) {
                        $info_pedido .= '<br>Cliente: ' . htmlspecialchars($mesa['cliente_info']);
                    }
                    $info_pedido .= '<br>Total: S/ ' . number_format($mesa['active_order']['total_pedido'], 2, '.', ',');
                }
            ?>
            <div class="restaurant-table-card <?= $clase_estado ?>"
                 title="Mesa #<?= htmlspecialchars($mesa['numero_mesa']) ?>"
                 data-table-id="<?= htmlspecialchars($mesa['id_mesa']) ?>"
                 data-table-number="<?= htmlspecialchars($mesa['numero_mesa']) ?>"
                 data-table-status="<?= htmlspecialchars($mesa['estado']) ?>"
                 data-order-id="<?= !empty($mesa['active_order']) ? htmlspecialchars($mesa['active_order']['id_pedido']) : '' ?>">
                <div class="circle-table">
                    <i class="fas fa-utensils mb-1"></i>
                    <span class="table-number"><?= htmlspecialchars($mesa['numero_mesa']) ?></span>
                </div>
                <div class="estado-mesa"><?= ucfirst(str_replace('_', ' ', $mesa['estado'])) ?></div>
                <?php if ($info_pedido): ?>
                    <div class="mesa-info-pedido"><?= $info_pedido ?></div>
                <?php else: ?>
                    <div class="mesa-info-pedido" style="background:transparent;box-shadow:none;">&nbsp;</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center mesa-fadein" role="alert">
        No hay mesas registradas en el sistema.
    </div>
<?php endif; ?>

<!-- Modal moderno para acciones en mesa -->
<div class="modal fade" id="tableActionModal" tabindex="-1" aria-labelledby="tableActionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content clean-modal">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="tableActionModalLabel">
            <i class="fas fa-utensils text-primary me-2"></i>
            Acciones para Mesa <span id="modalTableNumber"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-1">
        <div class="mb-3">
          <div class="fw-semibold small text-secondary">ID Mesa:</div>
          <div class="mb-2" id="modalTableId"></div>
          <div class="fw-semibold small text-secondary">Estado Actual:</div>
          <div class="mb-2" id="modalTableStatus"></div>
          <div class="fw-semibold small text-secondary">Orden Activa:</div>
          <div class="mb-2" id="modalOrderInfo"></div>
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

<style>
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
    min-width: 160px;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableCards = document.querySelectorAll('.restaurant-table-card');
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

            // Botones principales alineados en grid
            const actions = [];
            actions.push(
              `<div class="col-12 col-sm-6">
                <a href="${baseUrl}restaurant/tables/edit/${tableId}" class="btn btn-info w-100 mb-1">
                  <i class="fas fa-edit"></i> Editar Mesa
                </a>
              </div>`
            );

            switch (tableStatus) {
                case 'disponible':
                    actions.push(
                        `<div class="col-12 col-sm-6">
                            <a href="${baseUrl}restaurant/orders/create?table_id=${tableId}" class="btn btn-success w-100 mb-1">
                              <i class="fas fa-plus"></i> Tomar Pedido
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-warning w-100 mb-1" onclick="sendTableStatusUpdate(${tableId}, 'en_limpieza', '${tableNumber}')">
                              <i class="fas fa-broom"></i> En Limpieza
                            </button>
                        </div>`
                    );
                    break;
                case 'ocupada':
                    actions.push(
                        `<div class="col-12 col-sm-6">
                            <a href="${baseUrl}restaurant/orders/view/${orderId}" class="btn btn-primary w-100 mb-1">
                              <i class="fas fa-eye"></i> Ver Pedido
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <a href="${baseUrl}restaurant/orders/create?table_id=${tableId}&add_to_order=${orderId}" class="btn btn-success w-100 mb-1">
                              <i class="fas fa-cart-plus"></i> Añadir a Pedido
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-danger w-100 mb-1" onclick="sendTableStatusUpdate(${tableId}, 'en_limpieza', '${tableNumber}')" title="Marcar como 'En Limpieza' y liberar para nuevo pedido">
                              <i class="fas fa-times-circle"></i> Finalizar Pedido
                            </button>
                        </div>`
                    );
                    break;
                case 'en_limpieza':
                    actions.push(
                        `<div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-success w-100 mb-1" onclick="sendTableStatusUpdate(${tableId}, 'disponible', '${tableNumber}')">
                              <i class="fas fa-check"></i> Marcar Disponible
                            </button>
                        </div>`
                    );
                    break;
                case 'reservada':
                    actions.push(
                        `<div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-success w-100 mb-1" onclick="sendTableStatusUpdate(${tableId}, 'ocupada', '${tableNumber}')">
                              <i class="fas fa-user-check"></i> Marcar Ocupada
                            </button>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button type="button" class="btn btn-secondary w-100 mb-1" onclick="sendTableStatusUpdate(${tableId}, 'disponible', '${tableNumber}')">
                              <i class="fas fa-undo"></i> Cancelar Reserva
                            </button>
                        </div>`
                    );
                    break;
            }

            modalActions.innerHTML = actions.join('');
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
