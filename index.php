<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pdo = conectarDB();

/**
 * Convierte la ruta almacenada de una imagen en una URL válida
 * tanto para desarrollo local como para producción.
 */
function productImageUrl(?string $imagePath): string
{
    $imagePath = trim((string)$imagePath);

    if ($imagePath === '') {
        return '';
    }

    /*
     * Si la imagen ya es una URL externa completa,
     * no se modifica.
     */
    if (
        str_starts_with($imagePath, 'http://')
        || str_starts_with($imagePath, 'https://')
        || str_starts_with($imagePath, 'data:')
    ) {
        return $imagePath;
    }

    /*
     * Las imágenes antiguas fueron guardadas así:
     *
     * /proyecto_cava_Noble/assets/uploads/...
     *
     * Eliminamos solamente el prefijo del proyecto.
     */
    $imagePath = preg_replace(
        '#^/?proyecto_cava_Noble/#',
        '',
        $imagePath
    );

    return url(ltrim($imagePath, '/'));
}

$tituloSitio = 'Cava Noble';
$subtituloSitio = 'Vinos argentinos y del exterior en una experiencia única';

/*
|--------------------------------------------------------------------------
| Productos destacados
|--------------------------------------------------------------------------
*/

$sqlDestacados = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre
    FROM productos p
    LEFT JOIN categorias c
        ON p.categoria_id = c.id
    LEFT JOIN bodegas b
        ON p.bodega_id = b.id
    WHERE p.destacado = 1
    ORDER BY p.id DESC
    LIMIT 3
";

$stmtDestacados = $pdo->prepare($sqlDestacados);
$stmtDestacados->execute();

$destacados = $stmtDestacados->fetchAll();

/*
|--------------------------------------------------------------------------
| Productos premium
|--------------------------------------------------------------------------
*/

$sqlPremium = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre
    FROM productos p
    LEFT JOIN categorias c
        ON p.categoria_id = c.id
    LEFT JOIN bodegas b
        ON p.bodega_id = b.id
    WHERE p.precio >= (
        SELECT AVG(precio)
        FROM productos
    )
    ORDER BY p.precio DESC
    LIMIT 3
";

$stmtPremium = $pdo->prepare($sqlPremium);
$stmtPremium->execute();

$premium = $stmtPremium->fetchAll();

/*
|--------------------------------------------------------------------------
| Productos argentinos
|--------------------------------------------------------------------------
*/

$sqlArgentinos = "
    SELECT
        p.*,
        c.nombre AS categoria,
        b.nombre AS bodega_nombre
    FROM productos p
    LEFT JOIN categorias c
        ON p.categoria_id = c.id
    LEFT JOIN bodegas b
        ON p.bodega_id = b.id
    WHERE p.pais = 'Argentina'
    ORDER BY p.id DESC
    LIMIT 3
";

$stmtArgentinos = $pdo->prepare($sqlArgentinos);
$stmtArgentinos->execute();

$argentinos = $stmtArgentinos->fetchAll();

/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

$sqlStats = "
    SELECT
        (SELECT COUNT(*) FROM productos) AS total_productos,
        (SELECT COUNT(*) FROM bodegas) AS total_bodegas,
        (SELECT COUNT(*) FROM categorias) AS total_categorias
";

$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute();

$stats = $stmtStats->fetch();

/*
|--------------------------------------------------------------------------
| Secciones de productos
|--------------------------------------------------------------------------
*/

$secciones = [
    [
        'id' => 'destacados',
        'clase' => 'section',
        'kicker' => 'Curaduría Cava Noble',
        'titulo' => 'Vinos destacados',
        'descripcion' => 'Nuestra selección recomendada para esta temporada.',
        'productos' => $destacados,
    ],
    [
        'id' => '',
        'clase' => 'section section-alt',
        'kicker' => 'Alta gama',
        'titulo' => 'Selección premium',
        'descripcion' => 'Etiquetas con precio superior al promedio del catálogo.',
        'productos' => $premium,
    ],
    [
        'id' => '',
        'clase' => 'section',
        'kicker' => 'Origen nacional',
        'titulo' => 'Vinos argentinos',
        'descripcion' => 'Etiquetas nacionales con identidad, carácter y expresión regional.',
        'productos' => $argentinos,
    ],
];

include __DIR__ . '/includes/header.php';

?>

<main>

    <section class="hero">
        <div class="container hero-content">

            <div class="hero-text">

                <span class="hero-badge">
                    Selección premium
                </span>

                <h1>
                    <?php echo e($subtituloSitio); ?>
                </h1>

                <p>
                    Descubrí etiquetas seleccionadas de bodegas nacionales e
                    internacionales. Una tienda pensada para quienes valoran
                    el buen vino, la trazabilidad y una experiencia de compra
                    cuidada.
                </p>

                <div class="hero-actions">

                    <a
                        href="<?php echo e(url('pages/catalogo.php')); ?>"
                        class="btn btn-primary"
                    >
                        Ver catálogo
                    </a>

                    <a
                        href="#destacados"
                        class="btn btn-secondary"
                    >
                        Explorar destacados
                    </a>

                </div>
            </div>

            <div class="hero-card">

                <h2>
                    <?php echo e($tituloSitio); ?>
                </h2>

                <p>
                    Malbec, Cabernet Sauvignon, Pinot Noir, Chardonnay
                    y etiquetas internacionales.
                </p>

                <div class="home-stats">

                    <div>
                        <strong>
                            <?php echo (int)($stats['total_productos'] ?? 0); ?>
                        </strong>

                        <span>Vinos</span>
                    </div>

                    <div>
                        <strong>
                            <?php echo (int)($stats['total_bodegas'] ?? 0); ?>
                        </strong>

                        <span>Bodegas</span>
                    </div>

                    <div>
                        <strong>
                            <?php echo (int)($stats['total_categorias'] ?? 0); ?>
                        </strong>

                        <span>Categorías</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <?php foreach ($secciones as $seccion): ?>

        <section
            <?php if ($seccion['id'] !== ''): ?>
                id="<?php echo e($seccion['id']); ?>"
            <?php endif; ?>
            class="<?php echo e($seccion['clase']); ?>"
        >

            <div class="container">

                <div class="section-header">

                    <span class="section-kicker">
                        <?php echo e($seccion['kicker']); ?>
                    </span>

                    <h2>
                        <?php echo e($seccion['titulo']); ?>
                    </h2>

                    <p>
                        <?php echo e($seccion['descripcion']); ?>
                    </p>

                </div>

                <div class="products-grid">

                    <?php if (empty($seccion['productos'])): ?>

                        <p>
                            No hay productos disponibles en esta sección.
                        </p>

                    <?php else: ?>

                        <?php foreach ($seccion['productos'] as $producto): ?>

                            <article class="product-card">

                                <img
                                    src="<?php echo e(
                                        productImageUrl($producto['imagen'] ?? '')
                                    ); ?>"
                                    alt="<?php echo e(
                                        $producto['nombre'] ?? 'Producto'
                                    ); ?>"
                                    loading="lazy"
                                >

                                <div class="product-info">

                                    <span class="product-category">
                                        <?php echo e(
                                            $producto['categoria']
                                            ?? $producto['pais']
                                            ?? 'Vino'
                                        ); ?>
                                    </span>

                                    <h3>
                                        <?php echo e(
                                            $producto['nombre'] ?? ''
                                        ); ?>
                                    </h3>

                                    <p>
                                        <?php echo e(
                                            $producto['bodega_nombre']
                                            ?? $producto['bodega']
                                            ?? ''
                                        ); ?>
                                    </p>

                                    <div class="product-footer">

                                        <span class="price">
                                            <?php echo formatPrice(
                                                $producto['precio'] ?? 0
                                            ); ?>
                                        </span>

                                        <a
                                            href="<?php echo e(
                                                url('pages/producto.php')
                                            ); ?>?id=<?php echo (int)$producto['id']; ?>"
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

        </section>

    <?php endforeach; ?>

    <section class="section section-alt">

        <div class="container">

            <div class="section-header">

                <span class="section-kicker">
                    Experiencia de compra
                </span>

                <h2>
                    Una cava digital completa
                </h2>

                <p>
                    Catálogo dinámico, stock real, checkout,
                    pedidos y administración interna.
                </p>

            </div>

            <div class="home-feature-grid">

                <div class="home-feature-card">
                    <span>🍇</span>

                    <h3>Catálogo curado</h3>

                    <p>
                        Vinos organizados por categoría, bodega,
                        país, cepa y rango de precio.
                    </p>
                </div>

                <div class="home-feature-card">
                    <span>🏛️</span>

                    <h3>Bodegas seleccionadas</h3>

                    <p>
                        Etiquetas nacionales e internacionales
                        con información de origen y región.
                    </p>
                </div>

                <div class="home-feature-card">
                    <span>🛒</span>

                    <h3>Compra simple</h3>

                    <p>
                        Carrito, checkout y generación de pedidos
                        integrados con MySQL.
                    </p>
                </div>

            </div>
        </div>

    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>