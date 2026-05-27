<?php
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

$errores = [];

if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if ($email === '') $errores[] = 'El email es obligatorio.';
if ($asunto === '') $errores[] = 'El asunto es obligatorio.';
if ($mensaje === '') $errores[] = 'El mensaje es obligatorio.';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email no tiene un formato válido.';
}

$enviado = false;

if (empty($errores)) {
    $destino = "contacto@cavanoble.com";
    $asuntoEmail = "Consulta Cava Noble: " . $asunto;
    $contenido = "Nombre: $nombre\n";
    $contenido .= "Email: $email\n";
    $contenido .= "Mensaje:\n$mensaje";

    $headers = "From: $email";

    $enviado = mail($destino, $asuntoEmail, $contenido, $headers);
}

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="form-container">
            <?php if (!empty($errores)): ?>
                <h2>Error en el formulario</h2>

                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>

                <br>
                <a href="/proyecto_cava_Noble/pages/contacto.php" class="btn btn-primary">Volver</a>

            <?php else: ?>
                <h2>Consulta recibida</h2>
                <p>Gracias, <?php echo htmlspecialchars($nombre); ?>. Tu consulta fue procesada correctamente.</p>

                <?php if (!$enviado): ?>
                    <p><small>Nota: en servidor local es normal que la función mail() no envíe realmente el correo.</small></p>
                <?php endif; ?>

                <br>
                <a href="/proyecto_cava_Noble/index.php" class="btn btn-primary">Volver al inicio</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>