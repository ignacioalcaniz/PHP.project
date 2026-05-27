<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cantidadCarrito = 0;

if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadCarrito += $item['cantidad'];
    }
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';
$inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cava Noble</title>
    <link rel="stylesheet" href="/proyecto_cava_Noble/assets/css/styles.css">
</head>

<body>
<header class="site-header">
    <div class="container header-container">

        <a href="/proyecto_cava_Noble/index.php" class="brand">
            <span class="brand-icon">🍷</span>
            <span class="brand-text">
                <strong>Cava Noble</strong>
                <small>Premium Wines</small>
            </span>
        </a>

        <nav class="navbar">
            <ul class="nav-list">
                <li><a href="/proyecto_cava_Noble/index.php">Inicio</a></li>
                <li><a href="/proyecto_cava_Noble/pages/catalogo.php">Catálogo</a></li>
                <li><a href="/proyecto_cava_Noble/pages/contacto.php">Contacto</a></li>

                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <li><a class="nav-admin" href="/proyecto_cava_Noble/admin/index.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="nav-actions">
            <a class="cart-link" href="/proyecto_cava_Noble/carrito.php">
                <span class="cart-icon">🛒</span>
                <span>Carrito</span>
                <strong><?php echo $cantidadCarrito; ?></strong>
            </a>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo htmlspecialchars($inicialUsuario); ?>
                    </div>

                    <div class="user-info">
                        <span>Hola, <?php echo htmlspecialchars($nombreUsuario); ?></span>
                        <small>Cliente</small>
                    </div>

                    <a class="logout-link" href="/proyecto_cava_Noble/Login/logout.php">Salir</a>
                </div>
            <?php else: ?>
                <a class="login-link" href="/proyecto_cava_Noble/Login/login.php">Ingresar</a>
                <a class="register-link" href="/proyecto_cava_Noble/Registro/registro.php">Registrarse</a>
            <?php endif; ?>
        </div>

    </div>
</header>