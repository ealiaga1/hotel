<?php
if (!empty($success_message)) {
    echo "<div class='alert alert-success'>{$success_message}</div>";
}
if (!empty($error_message)) {
    echo "<div class='alert alert-danger'>{$error_message}</div>";
}
?>

<h2 class="mb-4 d-flex align-items-center justify-content-between">
    Gestión de Pedidos del Restaurante
    <a href="/hotel_completo/public/restaurant/orders/create" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Crear Pedido
    </a>
</h2>

<!-- Buscador y Filtros Mejorados -->
<form method="get" class="row align-items-end g-2 mb-4" style="background: #f9f9f9; padding: 16px; border-radius: 6px;">
    <div class="col-md-4">
        <label for="search" class="form-label mb-1"><strong>Buscar</strong></label>
        <input type="text" id="search" name="q" class="form-control"
               placeholder="Nombre, mesa o huésped"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label for="estado" class="form-label mb-1"><strong>Estado</strong></label>
        <select id="estado" name="estado" class="form-select">
            <option value="">Todos</option>
            <option value="activo" <?= ($_GET['estado'] ?? '') === "activo" ? "selected" : "" ?>>Activos</option>
            <option value="reservado" <?= ($_GET['estado'] ?? '') === "reservado" ? "selected" : "" ?>>Reservas</option>
            <option value="pagado" <?= ($_GET['estado'] ?? '') === "pagado" ? "selected" : "" ?>>Pagados</option>
            <option value="pendiente" <?= ($_GET['estado'] ?? '') === "pendiente" ? "selected" : "" ?>>Pendientes</option>
            <option value="listo" <?= ($_GET['estado'] ?? '') === "listo" ? "selected" : "" ?>>Listos</option>
            <option value="cancelado" <?= ($_GET['estado'] ?? '') === "cancelado" ? "selected" : "" ?>>Cancelados</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
    <div class="col-md-2 d-grid">
        <a href="/hotel_completo/public/restaurant/orders" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<!-- Tabla de Pedidos Mejorada -->
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID Pedido</th>
                <th>Tipo</th>
                <th>Mesa / Huésped</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha Pedido</th>
                <th>Mesero</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($orders)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No se encontraron pedidos.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id_pedido']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($order['tipo_pedido'] ?? 'N/A')) ?></td>
                    <td>
                        <?php
                            if (!empty($order['id_mesa'])) {
                                echo "Mesa #" . htmlspecialchars($order['id_mesa']);
                            } elseif (!empty($order['id_huesped'])) {
                                echo "Huésped #" . htmlspecialchars($order['id_huesped']);
                            } elseif (!empty($order['nombre_cliente'])) {
                                echo htmlspecialchars($order['nombre_cliente']);
                            } else {
                                echo "N/A";
                            }
                        ?>
                    </td>
                    <td>S/ <?= number_format($order['total_pedido'], 2) ?></td>
                    <td>
                        <span class="badge
                            <?php
                                $estado = strtolower($order['estado']);
                                if ($estado === 'pagado')      echo 'bg-success';
                                elseif ($estado === 'pendiente') echo 'bg-warning text-dark';
                                elseif ($estado === 'cancelado') echo 'bg-danger';
                                elseif ($estado === 'listo')     echo 'bg-info text-dark';
                                elseif ($estado === 'activo')    echo 'bg-primary';
                                elseif ($estado === 'reservado') echo 'bg-secondary';
                                else echo 'bg-secondary';
                            ?>">
                            <?= ucfirst(htmlspecialchars($order['estado'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $fecha = $order['fecha_pedido'] ?? $order['fecha'] ?? '';
                        echo $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '';
                        ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(trim(($order['mesero_nombre'] ?? '') . ' ' . ($order['mesero_apellido'] ?? ''))) ?>
                    </td>
                    <td>
                        <a href="/hotel_completo/public/restaurant/orders/view/<?= $order['id_pedido'] ?>"
                           class="btn btn-sm btn-outline-info">Ver</a>
                        <!-- Puedes agregar más botones aquí -->
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este pedido? Esta acción no se puede deshacer y puede fallar si tiene registros asociados.')) {
                e.preventDefault();
            }
        });
    });

    const cancelOrderButtons = document.querySelectorAll('.cancel-order-btn');
    cancelOrderButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres CANCELAR este pedido?')) {
                e.preventDefault();
            }
        });
    });
});
</script>