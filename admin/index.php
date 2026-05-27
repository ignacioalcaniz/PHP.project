<?php
require_once '../includes/auth.php';
requireAdmin();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Panel administrador</h2>
            <p>
                Gestión interna de Cava Noble.
                Administración de productos, pedidos, categorías, bodegas, métricas y operaciones del e-commerce.
            </p>
        </div>

        <div class="info-cards">

            <div class="info-card">
                <span class="admin-badge">Disponible</span>

                <h3>Productos</h3>

                <p>
                    Gestioná vinos, precios, stock, imágenes,
                    etiquetas destacadas y catálogo general.
                </p>

                <br>

                <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-primary">
                    Administrar productos
                </a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Disponible</span>

                <h3>Categorías</h3>

                <p>
                    Organizá el catálogo por tipos de vino
                    y administrá categorías dinámicas.
                </p>

                <br>

                <a href="/proyecto_cava_Noble/admin/categorias.php" class="btn btn-primary">
                    Administrar categorías
                </a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Disponible</span>

                <h3>Bodegas</h3>

                <p>
                    Gestioná bodegas nacionales e internacionales,
                    regiones y descripciones comerciales.
                </p>

                <br>

                <a href="/proyecto_cava_Noble/admin/bodegas.php" class="btn btn-primary">
                    Administrar bodegas
                </a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Disponible</span>

                <h3>Pedidos</h3>

                <p>
                    Visualizá compras, clientes, productos vendidos
                    y actualizá estados de pedidos.
                </p>

                <br>

                <a href="/proyecto_cava_Noble/admin/pedidos.php" class="btn btn-primary">
                    Administrar pedidos
                </a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Disponible</span>

                <h3>Reportes</h3>

                <p>
                    Dashboard comercial con métricas,
                    JOINs, GROUP BY y reportes avanzados.
                </p>

                <br>

                <a href="/proyecto_cava_Noble/admin/reportes.php" class="btn btn-primary">
                    Ver reportes
                </a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Próximamente</span>

                <h3>Usuarios</h3>

                <p>
                    Gestión de clientes registrados,
                    perfiles, roles y actividad del sistema.
                </p>

                <br>

                <button class="btn btn-secondary" disabled>
                    No disponible
                </button>
            </div>

        </div>

        <br><br>

        <div class="cart-box" style="max-width:100%;">
            <h2>Estado actual del sistema</h2>

            <br>

            <div class="info-cards">

                <div class="info-card">
                    <h3>Catálogo relacional</h3>
                    <p>
                        Productos relacionados con categorías
                        y bodegas mediante claves foráneas MySQL.
                    </p>
                </div>

                <div class="info-card">
                    <h3>Pedidos reales</h3>
                    <p>
                        El checkout genera pedidos, guarda items,
                        calcula totales y administra estados.
                    </p>
                </div>

                <div class="info-card">
                    <h3>Dashboard comercial</h3>
                    <p>
                        Reportes dinámicos con métricas,
                        ventas, stock y análisis comerciales.
                    </p>
                </div>

                <div class="info-card">
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