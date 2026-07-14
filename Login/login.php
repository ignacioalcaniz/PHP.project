<?php

require_once '../includes/session.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';

startSecureSession();

if (isset($_SESSION['usuario_id'])) {
    redirect('/proyecto_cava_Noble/index.php');
}

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="form-container">

            <h2>Ingresar</h2>

            <p>
                Accedé con tu email, nombre de usuario o DNI
                para continuar en Cava Noble.
            </p>

            <?php if (isset($_GET['error'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Los datos de acceso no son correctos.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['blocked'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Demasiados intentos fallidos.
                    Esperá 15 minutos antes de volver a intentar.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['expired'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Tu sesión expiró por inactividad.
                    Volvé a iniciar sesión.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['captcha'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    La validación de seguridad falló.
                    Intentá nuevamente.
                </p>
            <?php endif; ?>

            <form
                action="/proyecto_cava_Noble/Login/procesar-login.php"
                method="POST"
                class="auth-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e($csrfToken); ?>"
                >

                <div class="form-group">
                    <label for="identificador">
                        Email, usuario o DNI
                    </label>

                    <input
                        type="text"
                        id="identificador"
                        name="identificador"
                        maxlength="190"
                        required
                        autocomplete="username"
                        inputmode="text"
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <label
                    style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        font-weight:700;
                    "
                >
                    <input
                        type="checkbox"
                        name="remember_me"
                        value="1"
                    >

                    Recordarme por 30 días
                </label>

                <?php renderTurnstileWidget(); ?>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Ingresar
                </button>
            </form>

            <br>

            <p>
                ¿No tenés cuenta?

                <a href="/proyecto_cava_Noble/Registro/registro.php">
                    Registrate
                </a>
            </p>

        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>