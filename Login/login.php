<?php
require_once '../includes/session.php';
require_once '../includes/security.php';

startSecureSession();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="form-container">

            <h2>Ingresar</h2>
            <p>Accedé a tu cuenta para continuar en Cava Noble.</p>

            <?php if (isset($_GET['error'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Email o contraseña incorrectos.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['blocked'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Demasiados intentos fallidos. Esperá 15 minutos antes de volver a intentar.
                </p>
            <?php endif; ?>

            <?php if (isset($_GET['expired'])): ?>
                <p style="color:#8b0000; font-weight:700;">
                    Tu sesión expiró por inactividad. Volvé a iniciar sesión.
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
                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label>Contraseña</label>

                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <label style="display:flex;align-items:center;gap:10px;font-weight:700;">
                    <input
                        type="checkbox"
                        name="remember_me"
                        value="1"
                    >
                    Recordarme por 30 días
                </label>

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