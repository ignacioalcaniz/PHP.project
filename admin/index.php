<?php

require_once '../includes/auth.php';

requireAdmin();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Administración</span>

            <h2>Panel administrador</h2>

            <p>
                Centro de gestión de Cava Noble: catálogo, ventas, clientes,
                reportes, seguridad, auditoría y operaciones internas del e-commerce.
            </p>
        </div>

        <div class="admin-grid">

            <!-- PRODUCTOS -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">🍷</div>
                        <span class="admin-badge">Catálogo</span>
                    </div>

                    <h3>Productos</h3>

                    <p>
                        Gestioná vinos, precios, stock, imágenes,
                        productos destacados y disponibilidad del catálogo.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/productos.php"
                        class="btn btn-primary"
                    >
                        Administrar productos
                    </a>
                </div>
            </article>

            <!-- CATEGORÍAS -->

            <article class="admin-card">
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
                    <a
                        href="/proyecto_cava_Noble/admin/categorias.php"
                        class="btn btn-primary"
                    >
                        Administrar categorías
                    </a>
                </div>
            </article>

            <!-- BODEGAS -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">🏛️</div>
                        <span class="admin-badge">Origen</span>
                    </div>

                    <h3>Bodegas</h3>

                    <p>
                        Gestioná bodegas nacionales e internacionales,
                        países, regiones y descripciones comerciales.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/bodegas.php"
                        class="btn btn-primary"
                    >
                        Administrar bodegas
                    </a>
                </div>
            </article>

            <!-- GESTIÓN PROFESIONAL DE PEDIDOS -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">📦</div>
                        <span class="admin-badge">Ventas</span>
                    </div>

                    <h3>Gestión de pedidos</h3>

                    <p>
                        Administrá el flujo completo de compra:
                        generación del pedido, seguimiento operativo,
                        actualización de estados y pedidos finalizados.
                    </p>
                </div>

                <div class="admin-card-actions">

                    <a
                        href="/proyecto_cava_Noble/pages/catalogo.php"
                        class="btn btn-primary"
                    >
                        Realizar pedido
                    </a>

                    <a
                        href="/proyecto_cava_Noble/admin/pedidos.php?vista=activos"
                        class="btn btn-secondary"
                    >
                        Ver pedidos activos
                    </a>

                    <a
                        href="/proyecto_cava_Noble/admin/pedidos.php?vista=finalizados"
                        class="btn btn-secondary"
                    >
                        Pedidos finalizados
                    </a>

                </div>
            </article>

            <!-- USUARIOS -->

            <article class="admin-card">
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
                    <a
                        href="/proyecto_cava_Noble/admin/usuarios.php"
                        class="btn btn-primary"
                    >
                        Administrar usuarios
                    </a>
                </div>
            </article>

            <!-- REPORTES -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">📊</div>
                        <span class="admin-badge">Analytics</span>
                    </div>

                    <h3>Reportes</h3>

                    <p>
                        Consultá métricas comerciales, ventas,
                        stock, productos destacados y rendimiento general.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/reportes.php"
                        class="btn btn-primary"
                    >
                        Ver reportes
                    </a>
                </div>
            </article>

            <!-- AUDITORÍA -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">📜</div>
                        <span class="admin-badge">Auditoría</span>
                    </div>

                    <h3>Registro de actividad</h3>

                    <p>
                        Consultá las acciones administrativas realizadas
                        sobre productos, categorías, bodegas, pedidos y usuarios.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/logs.php"
                        class="btn btn-primary"
                    >
                        Ver registros
                    </a>
                </div>
            </article>

            <!-- CENTRO DE SEGURIDAD -->

            <article class="admin-card">
                <div class="admin-card-top">

                    <div class="admin-card-header">
                        <div class="admin-icon">🛡️</div>
                        <span class="admin-badge">Seguridad</span>
                    </div>

                    <h3>Centro de seguridad</h3>

                    <p>
                        Monitoreá intentos de acceso, IP sospechosas,
                        bloqueos temporales y actividad de autenticación.
                    </p>
                </div>

                <div class="admin-card-actions">
                    <a
                        href="/proyecto_cava_Noble/admin/seguridad.php"
                        class="btn btn-primary"
                    >
                        Ver seguridad
                    </a>
                </div>
            </article>

        </div>

        <!-- ESTADO DEL SISTEMA -->

        <section class="admin-status-panel">

            <div class="admin-status-header">
                <div>
                    <h2>Estado actual del sistema</h2>

                    <p>
                        Resumen técnico y funcional de la plataforma.
                    </p>
                </div>
            </div>

            <div class="admin-status-grid">

                <article class="admin-status-card">
                    <h3>Catálogo relacional</h3>

                    <p>
                        Productos relacionados con categorías y bodegas
                        mediante claves foráneas y consultas preparadas en MySQL.
                    </p>
                </article>

                <article class="admin-status-card">
                    <h3>Pedidos transaccionales</h3>

                    <p>
                        El checkout valida stock, genera pedidos e ítems,
                        descuenta existencias y confirma todo dentro de una transacción.
                    </p>
                </article>

                <article class="admin-status-card">
                    <h3>Flujo operativo</h3>

                    <p>
                        Los pedidos avanzan desde procesando hasta entregado,
                        con vistas separadas para activos, finalizados y cancelados.
                    </p>
                </article>

                <article class="admin-status-card">
                    <h3>Seguridad aplicada</h3>

                    <p>
                        Roles, sesiones seguras, CSRF, cookies HttpOnly,
                        Turnstile, rate limiting, bloqueo de IP y auditoría.
                    </p>
                </article>

            </div>
        </section>

    </div>
</main>

<?php include '../includes/footer.php'; ?>