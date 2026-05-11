<?php include '../includes/header.php'; ?>

<main class="section">

    <div class="container">

        <div class="form-container">

            <h2>Ingresar</h2>

            <form action="/Login/procesar-login.php" method="POST" class="auth-form">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Ingresar
                </button>

            </form>

            <br>

            <p>
                ¿No tenés cuenta?
                <a href="/Registro/registro.php">
                    Registrate
                </a>
            </p>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>