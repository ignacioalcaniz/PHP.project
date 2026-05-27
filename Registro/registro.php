<?php
require_once '../includes/security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="form-container">
            <h2>Crear cuenta</h2>
            <p>Registrate para comprar y administrar tu experiencia en Cava Noble.</p>

            <?php if (isset($_GET['error'])): ?>
                <p style="color:#8b0000;">
                    No se pudo completar el registro. Revisá los datos ingresados.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['duplicado'])): ?>
                <p style="color:#8b0000;">
                    Ya existe una cuenta registrada con ese email.
                </p>
            <?php endif; ?>

            <form action="/proyecto_cava_Noble/Registro/procesar-registro.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required autocomplete="given-name">
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" required autocomplete="family-name">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        required
                        minlength="6"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Registrarme
                </button>
            </form>

            <br>

            <p>
                ¿Ya tenés cuenta?
                <a href="/proyecto_cava_Noble/Login/login.php">
                    Ingresá
                </a>
            </p>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>