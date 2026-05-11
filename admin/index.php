<?php include '../includes/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Panel administrador</h2>
            <p>Gestión interna de Cava Noble.</p>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <span class="admin-badge">Disponible</span>
                <h3>Productos</h3>
                <p>Gestioná vinos, precios, stock, imágenes y productos destacados.</p>
                <br>
                <a href="/admin/productos.php" class="btn btn-primary">Administrar productos</a>
            </div>

            <div class="info-card">
                <span class="admin-badge">Próximamente</span>
                <h3>Pedidos</h3>
                <p>En futuras versiones se podrán visualizar compras, estados y detalles de pedidos.</p>
                <br>
                <button class="btn btn-secondary" disabled>No disponible</button>
            </div>

            <div class="info-card">
                <span class="admin-badge">Próximamente</span>
                <h3>Usuarios</h3>
                <p>En futuras versiones se podrán administrar clientes registrados y roles del sistema.</p>
                <br>
                <button class="btn btn-secondary" disabled>No disponible</button>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>