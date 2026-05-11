<?php include '../includes/header.php'; ?>

<main class="section">

    <div class="container">

        <div class="form-container">

            <h2>Crear cuenta</h2>

            <form action="/Registro/procesar-registro.php" method="POST" class="auth-form">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Registrarme
                </button>

            </form>

            <br>

            <p>
                ¿Ya tenés cuenta?
                <a href="/Login/login.php">
                    Ingresá
                </a>
            </p>

        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>