<?php
require_once '../config/database.php';

define('MONEDA', '$');

$pdo = conectarDB();

$pais = trim($_GET['pais'] ?? '');
$categoriaId = trim($_GET['categoria_id'] ?? '');
$bodegaId = trim($_GET['bodega_id'] ?? '');
$cepa = trim($_GET['cepa'] ?? '');
$precioMax = trim($_GET['precio_max'] ?? '');

$stmtCategorias = $pdo->prepare("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll();

$stmtBodegas = $pdo->prepare("SELECT id, nombre FROM bodegas ORDER BY nombre ASC");
$stmtBodegas->execute();
$bodegas = $stmtBodegas->fetchAll();

$stmtPaises = $pdo->prepare("
    SELECT DISTINCT pais
    FROM bodegas
    WHERE pais IS NOT NULL AND pais <> ''
    ORDER BY pais ASC
");
$stmtPaises->execute();
$paises = $stmtPaises->fetchAll();

$sql = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre,
        b.pais AS bodega_pais,
        b.region AS bodega_region
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    WHERE 1=1
";

$params = [];

if ($pais !== '') {
    $sql .= " AND b.pais = :pais";
    $params[':pais'] = $pais;
}

if ($categoriaId !== '') {
    $sql .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoriaId;
}

if ($bodegaId !== '') {
    $sql .= " AND p.bodega_id = :bodega_id";
    $params[':bodega_id'] = $bodegaId;
}

if ($cepa !== '') {
    $sql .= " AND p.cepa LIKE :cepa";
    $params[':cepa'] = "%$cepa%";
}

if ($precioMax !== '') {
    $sql .= " AND p.precio <= :precio_max";
    $params[':precio_max'] = $precioMax;
}

$sql .= " ORDER BY p.destacado DESC, p.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Catálogo</h2>
            <p>Explorá vinos argentinos e internacionales con filtros dinámicos desde MySQL.</p>
        </div>

        <div class="form-container" style="max-width: 1100px; margin-bottom: 40px;">
            <h2>Buscar vinos</h2>
            <p>Filtrá por país, categoría, bodega, cepa o presupuesto máximo.</p>

            <form method="GET" action="/proyecto_cava_Noble/pages/catalogo.php" class="auth-form">
                <div class="form-group">
                    <label>País</label>
                    <select name="pais">
                        <option value="">Todos</option>
                        <?php foreach ($paises as $item): ?>
                            <option value="<?php echo htmlspecialchars($item['pais']); ?>" <?php if ($pais === $item['pais']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($item['pais']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo (int)$categoria['id']; ?>" <?php if ($categoriaId == $categoria['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bodega</label>
                    <select name="bodega_id">
                        <option value="">Todas</option>
                        <?php foreach ($bodegas as $bodega): ?>
                            <option value="<?php echo (int)$bodega['id']; ?>" <?php if ($bodegaId == $bodega['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($bodega['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cepa</label>
                    <input
                        type="text"
                        name="cepa"
                        value="<?php echo htmlspecialchars($cepa); ?>"
                        placeholder="Malbec, Chardonnay, Blend..."
                    >
                </div>

                <div class="form-group">
                    <label>Precio máximo</label>
                    <input
                        type="number"
                        name="precio_max"
                        value="<?php echo htmlspecialchars($precioMax); ?>"
                        placeholder="Ej: 25000"
                    >
                </div>

                <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                <a href="/proyecto_cava_Noble/pages/catalogo.php" class="btn btn-secondary">Limpiar filtros</a>
            </form>

            <?php if ($pais !== '' || $categoriaId !== '' || $bodegaId !== '' || $cepa !== '' || $precioMax !== ''): ?>
                <br>
                <div class="product-data">
                    <p><strong>Filtros aplicados:</strong></p>
                    <p><strong>País:</strong> <?php echo $pais !== '' ? htmlspecialchars($pais) : 'Todos'; ?></p>
                    <p><strong>Categoría:</strong>
                        <?php
                            $categoriaSeleccionada = 'Todas';
                            foreach ($categorias as $categoria) {
                                if ($categoriaId == $categoria['id']) {
                                    $categoriaSeleccionada = $categoria['nombre'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($categoriaSeleccionada);
                        ?>
                    </p>
                    <p><strong>Bodega:</strong>
                        <?php
                            $bodegaSeleccionada = 'Todas';
                            foreach ($bodegas as $bodega) {
                                if ($bodegaId == $bodega['id']) {
                                    $bodegaSeleccionada = $bodega['nombre'];
                                    break;
                                }
                            }
                            echo htmlspecialchars($bodegaSeleccionada);
                        ?>
                    </p>
                    <p><strong>Cepa:</strong> <?php echo $cepa !== '' ? htmlspecialchars($cepa) : 'Todas'; ?></p>
                    <p><strong>Precio máximo:</strong>
                        <?php echo $precioMax !== '' ? '$' . number_format((float)$precioMax, 0, ',', '.') : 'Sin límite'; ?>
                    </p>
                </div>
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
                        <img
                            src="<?php echo htmlspecialchars($producto['imagen']); ?>"
                            alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                        >

                        <div class="product-info">
                            <span class="product-category">
                                <?php echo htmlspecialchars($producto['categoria'] ?? 'Sin categoría'); ?>
                            </span>

                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>

                            <p>
                                <?php echo htmlspecialchars($producto['bodega_nombre'] ?? $producto['bodega']); ?>
                                ·
                                <?php echo htmlspecialchars($producto['bodega_region'] ?? $producto['region']); ?>
                            </p>

                            <p>
                                <strong>País:</strong>
                                <?php echo htmlspecialchars($producto['bodega_pais'] ?? $producto['pais']); ?>
                            </p>

                            <p>
                                <strong>Cepa:</strong>
                                <?php echo htmlspecialchars($producto['cepa']); ?>
                            </p>

                            <?php if ((int)$producto['stock'] > 0): ?>
                                <p><strong>Stock:</strong> disponible</p>
                            <?php else: ?>
                                <p><strong>Stock:</strong> agotado</p>
                            <?php endif; ?>

                            <div class="product-footer">
                                <span class="price">
                                    <?php echo MONEDA . number_format($producto['precio'], 0, ',', '.'); ?>
                                </span>

                                <a
                                    href="/proyecto_cava_Noble/pages/producto.php?id=<?php echo $producto['id']; ?>"
                                    class="btn-card"
                                >
                                    Ver más
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>