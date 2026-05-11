<?php
require_once '../config/database.php';
$pdo = conectarDB();

$sql = "SELECT * FROM productos ORDER BY creado_en DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Administrar productos</h2>
            <p>Listado de vinos cargados en la tienda.</p>
            <br>
            <a href="/admin/crear-producto.php" class="btn btn-primary">Agregar vino</a>
        </div>

        <div class="cart-box" style="max-width: 100%;">
            <?php if (empty($productos)): ?>
                <p>No hay productos cargados.</p>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($producto['pais']); ?> · <?php echo htmlspecialchars($producto['region']); ?></p>
                            <p><strong>Bodega:</strong> <?php echo htmlspecialchars($producto['bodega']); ?></p>
                            <p><strong>Cepa:</strong> <?php echo htmlspecialchars($producto['cepa']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                            <p><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?></p>
                            <p><strong>Destacado:</strong> <?php echo ((int)$producto['destacado'] === 1) ? 'Sí' : 'No'; ?></p>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px; align-items:end;">
                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="width:90px;">
                            <a href="#" class="btn-card">Editar</a>
                            <a href="#" class="btn-card">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>