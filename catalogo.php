<?php
require_once 'config/database.php';

define('MONEDA', '$');

$pdo = conectarDB();

$pais = trim($_GET['pais'] ?? '');
$cepa = trim($_GET['cepa'] ?? '');
$precioMax = trim($_GET['precio_max'] ?? '');

$sql = "SELECT * FROM productos WHERE 1=1";
$params = [];

if ($pais !== '') {
    $sql .= " AND pais = :pais";
    $params[':pais'] = $pais;
}

if ($cepa !== '') {
    $sql .= " AND cepa LIKE :cepa";
    $params[':cepa'] = "%$cepa%";
}

if ($precioMax !== '') {
    $sql .= " AND precio <= :precio_max";
    $params[':precio_max'] = $precioMax;
}

$sql .= " ORDER BY destacado DESC, nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

include 'includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Catálogo</h2>
            <p>Explorá vinos argentinos e internacionales.</p>
        </div>

        <div class="form-container" style="max-width: 1000px; margin-bottom: 40px;">
            <h2>Buscar vinos</h2>
            <p>Filtrá por país, cepa o presupuesto máximo.</p>

            <form method="GET" action="catalogo.php" class="auth-form">
                <div class="form-group">
                    <label>País</label>
                    <select name="pais">
                        <option value="">Todos</option>
                        <option value="Argentina" <?php if ($pais === 'Argentina') echo 'selected'; ?>>Argentina</option>
                        <option value="Chile" <?php if ($pais === 'Chile') echo 'selected'; ?>>Chile</option>
                        <option value="Francia" <?php if ($pais === 'Francia') echo 'selected'; ?>>Francia</option>
                        <option value="Italia" <?php if ($pais === 'Italia') echo 'selected'; ?>>Italia</option>
                        <option value="España" <?php if ($pais === 'España') echo 'selected'; ?>>España</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cepa</label>
                    <input type="text" name="cepa" value="<?php echo htmlspecialchars($cepa); ?>" placeholder="Malbec, Chardonnay, Blend...">
                </div>

                <div class="form-group">
                    <label>Precio máximo</label>
                    <input type="number" name="precio_max" value="<?php echo htmlspecialchars($precioMax); ?>" placeholder="Ej: 25000">
                </div>

                <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                <a href="catalogo.php" class="btn btn-secondary">Limpiar filtros</a>
            </form>

            <?php if ($pais !== '' || $cepa !== '' || $precioMax !== ''): ?>
                <br>
                <p><strong>Filtros aplicados:</strong></p>
                <p>País: <?php echo $pais !== '' ? htmlspecialchars($pais) : 'Todos'; ?></p>
                <p>Cepa: <?php echo $cepa !== '' ? htmlspecialchars($cepa) : 'Todas'; ?></p>
                <p>Precio máximo: <?php echo $precioMax !== '' ? '$' . number_format($precioMax, 0, ',', '.') : 'Sin límite'; ?></p>
            <?php endif; ?>
        </div>

        <div class="section-header">
            <p>Productos encontrados: <strong><?php echo count($productos); ?></strong></p>
        </div>

        <div class="products-grid">
            <?php if (empty($productos)): ?>
                <p>No se encontraron vinos con esos filtros.</p>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <article class="product-card">
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">

                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($producto['pais']); ?></span>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($producto['bodega']); ?> · <?php echo htmlspecialchars($producto['region']); ?></p>

                            <?php if ((int)$producto['stock'] > 0): ?>
                                <p><strong>Stock:</strong> disponible</p>
                            <?php else: ?>
                                <p><strong>Stock:</strong> agotado</p>
                            <?php endif; ?>

                            <div class="product-footer">
                                <span class="price"><?php echo MONEDA . number_format($producto['precio'], 0, ',', '.'); ?></span>
                                <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn-card">Ver más</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>