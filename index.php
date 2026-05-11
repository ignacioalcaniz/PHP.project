<?php
require_once 'config/database.php';

$pdo = conectarDB();

$tituloSitio = "Cava Noble";
$subtituloSitio = "Vinos argentinos y del exterior en una experiencia única";

$sqlDestacados = "SELECT * FROM productos WHERE destacado = 1 ORDER BY id DESC LIMIT 3";
$stmtDestacados = $pdo->prepare($sqlDestacados);
$stmtDestacados->execute();
$destacados = $stmtDestacados->fetchAll();

include 'includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <span class="hero-badge">Selección premium</span>
                <h1><?php echo $subtituloSitio; ?></h1>
                <p>
                    Descubrí etiquetas seleccionadas de bodegas nacionales e internacionales.
                    Una tienda pensada para quienes valoran el buen vino.
                </p>
                <div class="hero-actions">
                    <a href="catalogo.php" class="btn btn-primary">Ver catálogo</a>
                    <a href="#destacados" class="btn btn-secondary">Explorar destacados</a>
                </div>
            </div>

            <div class="hero-card">
                <h2><?php echo $tituloSitio; ?></h2>
                <p>Malbec, Cabernet Sauvignon, Pinot Noir, Chardonnay y más.</p>
            </div>
        </div>
    </section>

    <section id="destacados" class="section">
        <div class="container">
            <div class="section-header">
                <h2>Vinos destacados</h2>
                <p>Nuestra selección recomendada para esta temporada.</p>
            </div>

            <div class="products-grid">
                <?php foreach ($destacados as $producto): ?>
                    <article class="product-card">
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">

                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($producto['pais']); ?></span>
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($producto['bodega']); ?></p>

                            <div class="product-footer">
                                <span class="price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                                <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn-card">Ver más</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2>Vinos argentinos</h2>
                <p>Selección de etiquetas nacionales con identidad y carácter.</p>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <h3>Mendoza</h3>
                    <p>Malbecs intensos, elegantes y con gran estructura.</p>
                </div>
                <div class="info-card">
                    <h3>Salta</h3>
                    <p>Altura, frescura y vinos con un perfil distintivo.</p>
                </div>
                <div class="info-card">
                    <h3>Patagonia</h3>
                    <p>Pinot Noir y blancos con expresión delicada y moderna.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>