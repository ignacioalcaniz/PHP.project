<?php
require_once '../includes/auth.php';
requireAdmin();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <h2>Panel administrador</h2>
            <p>
                Centro de gestión de Cava Noble: catálogo, ventas, clientes,
                reportes y operaciones internas del e-commerce.
            </p>
        </div>

        <div class="admin-grid">

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">🍷</div>
                        <span class="admin-badge">Catálogo</span>
                    </div>

                    <h3>Productos</h3>

                    <p>
                        Gestioná vinos, precios, stock, imágenes,
                        destacados y catálogo general.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-primary">
                        Administrar productos
                    </a>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">🏷️</div>
                        <span class="admin-badge">Organización</span>
                    </div>

                    <h3>Categorías</h3>

                    <p>
                        Organizá el catálogo por tipos de vino
                        y administrá categorías dinámicas.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/categorias.php" class="btn btn-primary">
                        Administrar categorías
                    </a>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">🏛️</div>
                        <span class="admin-badge">Origen</span>
                    </div>

                    <h3>Bodegas</h3>

                    <p>
                        Gestioná bodegas nacionales e internacionales,
                        regiones y descripciones comerciales.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/bodegas.php" class="btn btn-primary">
                        Administrar bodegas
                    </a>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">📦</div>
                        <span class="admin-badge">Ventas</span>
                    </div>

                    <h3>Pedidos</h3>

                    <p>
                        Visualizá compras, clientes, productos vendidos
                        y actualizá estados de pedidos.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/pedidos.php" class="btn btn-primary">
                        Administrar pedidos
                    </a>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">👥</div>
                        <span class="admin-badge">Clientes</span>
                    </div>

                    <h3>Usuarios</h3>

                    <p>
                        Gestioná clientes registrados, roles de acceso,
                        historial de compras y actividad comercial.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/usuarios.php" class="btn btn-primary">
                        Administrar usuarios
                    </a>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-top">
                    <div class="admin-card-header">
                        <div class="admin-icon">📊</div>
                        <span class="admin-badge">Analytics</span>
                    </div>

                    <h3>Reportes</h3>

                    <p>
                        Dashboard comercial con métricas,
                        JOINs, GROUP BY y reportes avanzados.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a href="/proyecto_cava_Noble/admin/reportes.php" class="btn btn-primary">
                        Ver reportes
                    </a>
                </div>
            </div>

        </div>

        <div class="admin-status-panel">
            <div class="admin-status-header">
                <div>
                    <h2>Estado actual del sistema</h2>
                    <p>Resumen técnico y funcional del e-commerce.</p>
                </div>
            </div>

            <div class="admin-status-grid">

                <div class="admin-status-card">
                    <h3>Catálogo relacional</h3>
                    <p>
                        Productos relacionados con categorías
                        y bodegas mediante claves foráneas MySQL.
                    </p>
                </div>

                <div class="admin-status-card">
                    <h3>Pedidos reales</h3>
                    <p>
                        El checkout genera pedidos, guarda items,
                        calcula totales y administra estados.
                    </p>
                </div>

                <div class="admin-status-card">
                    <h3>Usuarios y roles</h3>
                    <p>
                        Administración de clientes y permisos
                        mediante roles de usuario.
                    </p>
                </div>

                <div class="admin-status-card">
                    <h3>Seguridad aplicada</h3>
                    <p>
                        Sistema protegido con roles,
                        sesiones, CSRF y consultas preparadas.
                    </p>
                </div>

            </div>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>