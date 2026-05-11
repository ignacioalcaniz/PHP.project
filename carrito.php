<?php include 'includes/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Tu carrito</h2>
            <p>Revisá los productos seleccionados antes de continuar.</p>
        </div>

        <div class="cart-box">
            <?php if (empty($_SESSION['carrito'])): ?>
                <p>Tu carrito está vacío.</p>
                <br>
                <a href="/catalogo.php" class="btn btn-primary">Ir al catálogo</a>

            <?php else: ?>
                <?php $totalGeneral = 0; ?>

                <?php foreach ($_SESSION['carrito'] as $item): ?>
                    <?php
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $totalGeneral += $subtotal;
                    ?>

                    <div class="cart-item">
                        <div>
                            <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <p>Cantidad: <?php echo (int)$item['cantidad']; ?></p>
                            <p>Precio unitario: $<?php echo number_format($item['precio'], 0, ',', '.'); ?></p>
                            <p>Subtotal: $<?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:end;">
                            <img
                                src="<?php echo htmlspecialchars($item['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($item['nombre']); ?>"
                                style="width:90px; border-radius:10px;"
                            >

                            <a
                                href="/carrito/eliminar.php?id=<?php echo $item['id']; ?>"
                                class="btn-card"
                            >
                                Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <h3>Total</h3>
                    <span>$<?php echo number_format($totalGeneral, 0, ',', '.'); ?></span>
                </div>

                <div style="display:flex; gap:15px; flex-wrap:wrap;">
                    <a href="/carrito/vaciar.php" class="btn btn-secondary">Vaciar carrito</a>
                    <a href="/catalogo.php" class="btn btn-secondary">Seguir comprando</a>
                    <a href="/checkout/checkout.php" class="btn btn-primary">Ir al checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>