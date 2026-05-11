<?php
require_once '../config/database.php';

$pdo = conectarDB();

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = (float) ($_POST['precio'] ?? 0);
$pais = trim($_POST['pais'] ?? '');
$region = trim($_POST['region'] ?? '');
$bodega = trim($_POST['bodega'] ?? '');
$cepa = trim($_POST['cepa'] ?? '');
$anada = (int) ($_POST['anada'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$imagen = trim($_POST['imagen'] ?? '');
$destacado = (int) ($_POST['destacado'] ?? 0);

$errores = [];

if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if ($descripcion === '') $errores[] = 'La descripción es obligatoria.';
if ($precio <= 0) $errores[] = 'El precio debe ser mayor a cero.';
if ($pais === '') $errores[] = 'El país es obligatorio.';
if ($region === '') $errores[] = 'La región es obligatoria.';
if ($bodega === '') $errores[] = 'La bodega es obligatoria.';
if ($cepa === '') $errores[] = 'La cepa es obligatoria.';
if ($anada < 1900 || $anada > 2030) $errores[] = 'La añada no es válida.';
if ($stock < 0) $errores[] = 'El stock no puede ser negativo.';
if ($imagen === '') $errores[] = 'La imagen es obligatoria.';

if (!empty($errores)) {
    include '../includes/header.php';
    ?>

    <main class="section">
        <div class="container">
            <div class="form-container">
                <h2>Error al cargar producto</h2>

                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>

                <br>
                <a href="/proyecto_cava_Noble/admin/crear-producto.php" class="btn btn-primary">Volver</a>
            </div>
        </div>
    </main>

    <?php
    include '../includes/footer.php';
    exit;
}

$sql = "INSERT INTO productos
(nombre, descripcion, precio, pais, region, bodega, cepa, anada, stock, imagen, destacado)
VALUES
(:nombre, :descripcion, :precio, :pais, :region, :bodega, :cepa, :anada, :stock, :imagen, :destacado)";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':precio', $precio);
$stmt->bindParam(':pais', $pais);
$stmt->bindParam(':region', $region);
$stmt->bindParam(':bodega', $bodega);
$stmt->bindParam(':cepa', $cepa);
$stmt->bindParam(':anada', $anada, PDO::PARAM_INT);
$stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
$stmt->bindParam(':imagen', $imagen);
$stmt->bindParam(':destacado', $destacado, PDO::PARAM_INT);

$stmt->execute();

header('Location: /proyecto_cava_Noble/admin/productos.php');
exit;