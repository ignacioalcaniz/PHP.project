<?php include '../includes/header.php'; ?>

<main class="section">
    <div class="container">
        <div class="form-container" style="max-width: 800px;">
            <h2>Agregar vino</h2>
            <p>Cargá un nuevo producto al catálogo.</p>

            <form action="/proyecto_cava_Noble/admin/procesar-crear-producto.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" required>
                </div>

                <div class="form-group">
                    <label>Precio</label>
                    <input type="number" name="precio" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>País</label>
                    <input type="text" name="pais" required>
                </div>

                <div class="form-group">
                    <label>Región</label>
                    <input type="text" name="region" required>
                </div>

                <div class="form-group">
                    <label>Bodega</label>
                    <input type="text" name="bodega" required>
                </div>

                <div class="form-group">
                    <label>Cepa</label>
                    <input type="text" name="cepa" required>
                </div>

                <div class="form-group">
                    <label>Añada</label>
                    <input type="number" name="anada" min="1900" max="2030" required>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" min="0" required>
                </div>

                <div class="form-group">
                    <label>Imagen URL</label>
                    <input type="text" name="imagen" required>
                </div>

                <div class="form-group">
                    <label>Destacado</label>
                    <select name="destacado" required>
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Guardar producto</button>
                <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>