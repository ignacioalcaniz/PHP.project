<?php include 'includes/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="form-container" style="max-width: 700px;">
            <h2>Contacto</h2>
            <p>Envianos tu consulta sobre productos, pedidos o recomendaciones de vinos.</p>

            <form action="procesar-contacto.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Asunto</label>
                    <input type="text" name="asunto" required>
                </div>

                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="mensaje" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Enviar consulta</button>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>