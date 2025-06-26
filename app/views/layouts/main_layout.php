<?php
// hotel_completo/app/views/layouts/main_layout.php

// Asegurar que la clase Database y las constantes de PATHS esten disponibles
if (!class_exists('Database')) {
    require_once __DIR__ . '/../../lib/Database.php';
}
if (!defined('VIEW_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

// DEFINICION DE LA RUTA BASE PARA LOS RECURSOS ESTATICOS
$base_url_for_assets = '/hotel_completo/public/';


// Obtener el ID del rol de Super Admin para control de visibilidad del menu
$super_admin_role_id_for_layout = null;
try {
    $pdo_layout = Database::getInstance()->getConnection();
    $stmt_super_admin_role_layout = $pdo_layout->prepare("SELECT id_rol FROM roles WHERE nombre_rol = 'Super Admin'");
    $stmt_super_admin_role_layout->execute();
    $super_admin_role_id_for_layout = $stmt_super_admin_role_layout->fetchColumn();
} catch (PDOException | Exception $e) { // Captura PDOException o Exception general (ej. si Database::getInstance() falla)
    error_log("Error al obtener el ID del rol 'Super Admin' en el layout: " . $e->getMessage());
    $super_admin_role_id_for_layout = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Sistema de Gestion Hotelera'; ?></title>

    <!-- Favicon - Edificio Azul con 'H' -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect x='3' y='2' width='18' height='20' rx='2' ry='2' fill='%23007bff'/%3E%3Ctext x='50%25' y='60%25' font-family='Arial, sans-serif' font-size='14' fill='%23ffffff' text-anchor='middle' alignment-baseline='middle'%3EH%3C/text%3E%3C/svg%3E">
    <!-- Bootstrap CSS - Usando CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome - Usando CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url_for_assets; ?>css/custom.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        #sidebar-wrapper {
            min-width: 250px;
            max-width: 250px;
            background-color: linear-gradient(160deg, #23395d 0%, #1565c0 90%);
            color: #f8f9fa;;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            box-shadow: 2px 0 6px rgba(35,57,93,0.13);
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            color: #ffffff;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            margin-bottom: 15px;
            text-shadow: 1px 1px 8px #1565c0;
            letter-spacing: 2px;
        }
        #sidebar-wrapper .list-group {
            width: 100%;
        }
        /* Estilos para los items principales del menú (padres de submenús) */
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #b3c3e6;
            border: none;
            padding: 12px 1.25rem;
            display: flex;
            align-items: center;
            border-radius: 0;
            font-size: 0.97rem;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, border-left 0.2s;
        }
        #sidebar-wrapper .list-group-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(90deg, #1976d2 0%, #64b5f6 100%);
            color: #fff;
            text-decoration: none;
            border-left: 4px solid #29b6f6;
            padding-left: calc(1.25rem - 4px);
        }
        /* Estilos para los submenús */
        .sidebar-submenu {
            background-color: #23395d;
            padding-left: 20px;
            border-left: 4px solid #1976d2;
            margin-left: 10px;
        }
        .sidebar-submenu .list-group-item {
            padding-top: 8px;
            padding-bottom: 8px;
            font-size: 0.92rem;
            color: #b3c3e6;
        }
        .sidebar-submenu .list-group-item:hover,
        .sidebar-submenu .list-group-item.active {
            border-left: 4px solid #29b6f6;
            background: #1976d2;
            color: #fff;
        }
        /* Ajuste para el icono de flecha del desplegable */
        .dropdown-toggle-sidebar::after {
            display: inline-block;
            margin-left: auto; /* Empuja la flecha a la derecha */
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
            transform: rotate(-90deg); /* Flecha hacia la izquierda por defecto */
            transition: transform 0.2s ease-in-out;
        }
        .dropdown-toggle-sidebar.collapsed::after {
            transform: rotate(0deg); /* Flecha hacia abajo cuando está colapsado */
        }


        #page-content-wrapper {
            flex-grow: 1;
            padding: 20px;
            background-color: #f0f2f5;
        }
        #navbar-top {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-radius: 0.25rem;
        }
        .user-info {
            font-weight: bold;
            color: #23395d;
            font-size: 1.05rem;
        }
        .dropdown-menu {
            border-radius: 0.25rem;
            box-shadow: 0 4px 10px rgba(33,150,243,0.09);
            font-size: 0.97rem;;
        }
        .text-primary-light { color: #64b5f6 !important; }
        .text-warning-light { color: #ffe08a !important; }
        .text-success-light { color: #87e087 !important; }
        .text-info-light { color: #8ad8ff !important; }

        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed;
                left: -250px;
                height: 100vh;
                z-index: 1030;
            }
            #sidebar-wrapper.toggled {
                left: 0;
            }
            #page-content-wrapper {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div id="sidebar-wrapper" class="d-none d-lg-flex">
        <div class="sidebar-heading">Hotel Manager</div>
        <div class="list-group list-group-flush">
            <?php
            $current_route = ltrim(str_replace($base_url_for_assets, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''), '/');
            
            // Lógica para determinar qué menú principal está activo o si alguno de sus sub-elementos lo está
            $is_restaurant_active = str_starts_with($current_route, 'restaurant');
            $is_inventory_active = str_starts_with($current_route, 'inventory');
            $is_pool_active = str_starts_with($current_route, 'pool');
            $is_cash_register_active = str_starts_with($current_route, 'cash_register');
            $is_invoicing_active = str_starts_with($current_route, 'invoicing');
            $is_company_settings_active = str_starts_with($current_route, 'company_settings'); // NUEVO
            $is_reception_active = str_starts_with($current_route, 'reception'); // NUEVO
            ?>

            <a href="<?php echo $base_url_for_assets; ?>dashboard.php" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'dashboard') === 0 || $current_route == '') ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
            </a>
                         <!-- Nuevo enlace de Recepción -->
            <a href="<?php echo $base_url_for_assets; ?>reception" class="list-group-item list-group-item-action <?php echo $is_reception_active ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-concierge-bell"></i> Recepción
            </a>

            <a href="<?php echo $base_url_for_assets; ?>rooms" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'rooms') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-bed"></i> Habitaciones
            </a>
            <a href="<?php echo $base_url_for_assets; ?>bookings" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'bookings') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-book"></i> Reservas
            </a>
            <a href="<?php echo $base_url_for_assets; ?>guests" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'guests') === 0) ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-users"></i> Huéspedes
            </a>
            
            <!-- Restaurante con Submenú Desplegable -->
            <a class="list-group-item list-group-item-action dropdown-toggle-sidebar <?php echo $is_restaurant_active ? 'active' : 'collapsed'; ?>" 
               data-bs-toggle="collapse" href="#submenuRestaurant" role="button" aria-expanded="<?php echo $is_restaurant_active ? 'true' : 'false'; ?>" 
               aria-controls="submenuRestaurant">
                <i class="fas fa-fw fa-concierge-bell"></i> Restaurante
            </a>
            <div class="collapse <?php echo $is_restaurant_active ? 'show' : ''; ?>" id="submenuRestaurant">
                <div class="list-group list-group-flush sidebar-submenu">
                    <a href="<?php echo $base_url_for_assets; ?>restaurant/orders" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'restaurant/orders') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-cash-register"></i> Pedidos
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>restaurant/tables" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'restaurant/tables') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-chair"></i> Mesas
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>restaurant" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'restaurant') === 0 && !str_starts_with($current_route, 'restaurant/orders') && !str_starts_with($current_route, 'restaurant/tables') && !str_starts_with($current_route, 'restaurant/categories')) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-utensils"></i> Platos del Menú
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>restaurant/categories" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'restaurant/categories') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-tags"></i> Categorías del Menú
                    </a>
                </div>
            </div>

            <a href="<?php echo $base_url_for_assets; ?>pool" class="list-group-item list-group-item-action <?php echo $is_pool_active ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-swimming-pool"></i> Piscina
            </a>

            <!-- Inventario con Submenú Desplegable -->
            <a class="list-group-item list-group-item-action dropdown-toggle-sidebar <?php echo $is_inventory_active ? 'active' : 'collapsed'; ?>" 
               data-bs-toggle="collapse" href="#submenuInventory" role="button" aria-expanded="<?php echo $is_inventory_active ? 'true' : 'false'; ?>" 
               aria-controls="submenuInventory">
                <i class="fas fa-fw fa-boxes"></i> Inventario
            </a>
            <div class="collapse <?php echo $is_inventory_active ? 'show' : ''; ?>" id="submenuInventory">
                <div class="list-group list-group-flush sidebar-submenu">
                    <a href="<?php echo $base_url_for_assets; ?>inventory/products/create" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'inventory/products/create') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-plus-circle"></i> Nuevo Producto
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>inventory/movements/create" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'inventory/movements/create') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-exchange-alt"></i> Registrar Movimiento
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>inventory/movements" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'inventory/movements') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-history"></i> Historial de Movimientos
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>inventory/categories" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'inventory/categories') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-tags"></i> Categorías Inventario
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>inventory" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'inventory') === 0 && !str_starts_with($current_route, 'inventory/products') && !str_starts_with($current_route, 'inventory/movements') && !str_starts_with($current_route, 'inventory/categories')) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-boxes"></i> Ver Productos
                    </a>
                </div>
            </div>

            <!-- Caja con Submenú Desplegable -->
            <a class="list-group-item list-group-item-action dropdown-toggle-sidebar <?php echo $is_cash_register_active ? 'active' : 'collapsed'; ?>" 
               data-bs-toggle="collapse" href="#submenuCashRegister" role="button" aria-expanded="<?php echo $is_cash_register_active ? 'true' : 'false'; ?>" 
               aria-controls="submenuCashRegister">
                <i class="fas fa-fw fa-cash-register"></i> Caja
            </a>
            <div class="collapse <?php echo $is_cash_register_active ? 'show' : ''; ?>" id="submenuCashRegister">
                <div class="list-group list-group-flush sidebar-submenu">
                    <a href="<?php echo $base_url_for_assets; ?>cash_register" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register') === 0 && !str_starts_with($current_route, 'cash_register/history') && !str_starts_with($current_route, 'cash_register/open') && !str_starts_with($current_route, 'cash_register/add_transaction') && !str_starts_with($current_route, 'cash_register/transactions') && !str_starts_with($current_route, 'cash_register/sell_product') && !str_starts_with($current_route, 'cash_register/pos_report')) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-hand-holding-usd"></i> Estado de Caja
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>cash_register/open" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register/open') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-money-check-alt"></i> Abrir Caja
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>cash_register/history" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register/history') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-file-invoice-dollar"></i> Historial de Cierres
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>cash_register/add_transaction" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register/add_transaction') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-cash-register"></i> Registrar Transacción
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>cash_register/transactions" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register/transactions') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-clipboard-list"></i> Ver Transacciones
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>cash_register/sell_product" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'cash_register/sell_product') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-shopping-cart"></i> Venta Directa
                    </a>
                    <!-- El enlace a Reporte POS se mueve dentro de Historial de Cierres -->
                </div>
            </div>
            
            <!-- Facturación - Ahora un enlace principal directo -->
            <a href="<?php echo $base_url_for_assets; ?>invoicing" class="list-group-item list-group-item-action <?php echo $is_invoicing_active ? 'active' : ''; ?>">
                <i class="fas fa-fw fa-file-invoice"></i> Facturación
            </a>
            
            <?php
            // Solo el Super Admin puede acceder a la configuración de la empresa y gestión de usuarios
            if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == $super_admin_role_id_for_layout) :
            ?>
            <!-- Grupo de Configuración y Administración -->
            <a class="list-group-item list-group-item-action dropdown-toggle-sidebar <?php echo ($is_company_settings_active || str_starts_with($current_route, 'users')) ? 'active' : 'collapsed'; ?>"
               data-bs-toggle="collapse" href="#submenuAdmin" role="button" aria-expanded="<?php echo ($is_company_settings_active || str_starts_with($current_route, 'users')) ? 'true' : 'false'; ?>"
               aria-controls="submenuAdmin">
                <i class="fas fa-fw fa-tools"></i> Administración
            </a>
            <div class="collapse <?php echo ($is_company_settings_active || str_starts_with($current_route, 'users')) ? 'show' : ''; ?>" id="submenuAdmin">
                <div class="list-group list-group-flush sidebar-submenu">
                    <a href="<?php echo $base_url_for_assets; ?>users" class="list-group-item list-group-item-action <?php echo (strpos($current_route, 'users') === 0) ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-user-cog"></i> Gestión de Usuarios
                    </a>
                    <a href="<?php echo $base_url_for_assets; ?>company_settings" class="list-group-item list-group-item-action <?php echo $is_company_settings_active ? 'active' : ''; ?>">
                        <i class="fas fa-fw fa-building"></i> Datos de Empresa
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom" id="navbar-top">
            <div class="container-fluid">
                <button class="btn btn-primary d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <h3 class="ms-3 d-none d-lg-block mb-0">Dashboard</h3>
                <div class="ms-auto d-flex align-items-center">
                    <span class="user-info me-3">
                        Bienvenido, **<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?>**!
                    </span>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-2x"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_url_for_assets; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <?php
            error_log("DEBUG-LAYOUT: Attempting to include \$content_view: " . ($content_view ?? 'NOT SET'));
            if (isset($content_view) && !file_exists($content_view)) {
                error_log("DEBUG-LAYOUT: File DOES NOT EXIST at path: " . $content_view);
                echo '<div class="alert alert-danger" role="alert">Error: La vista solicitada no se encontró: <strong>' . htmlspecialchars($content_view) . '</strong>. Verifique la ruta en el controlador.</div>';
            }
            ?>
            <?php include $content_view; ?>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) - Usando CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Custom JS - Usando la ruta base definida con PHP -->
    <script src="<?php echo $base_url_for_assets; ?>js/custom.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebarToggle = document.getElementById('sidebarToggle');
            var sidebar = document.getElementById('sidebar-wrapper');

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('d-none');
                    // For a more "push" effect, you could adjust margin-left or transform
                    // pageContent.classList.toggle('expanded'); // Or a class to expand/shrink
                });
            }
        });
    </script>
</body>
</html>
