<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$stmtCategorias = $pdo->prepare("
    SELECT id, nombre
    FROM categorias
    ORDER BY nombre ASC
");

$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll();

$stmtBodegas = $pdo->prepare("
    SELECT id, nombre
    FROM bodegas
    ORDER BY nombre ASC
");

$stmtBodegas->execute();
$bodegas = $stmtBodegas->fetchAll();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">

        <div class="section-header">
            <h2>Crear producto</h2>
            <p>Agregar un nuevo vino al catálogo.</p>
        </div>

        <div class="form-container" style="max-width:800px;">

            <form
                action="/proyecto_cava_Noble/admin/procesar-crear-producto.php"
                method="POST"
                class="auth-form"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo $csrfToken; ?>"
                >

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label>Precio</label>
                    <input type="number" name="precio" min="0" required>
                </div>

                <div class="form-group">
                    <label>Categoría</label>

                    <select name="categoria_id" required>
                        <option value="">Seleccionar</option>

                        <?php foreach ($categorias as $categoria): ?>

                            <option value="<?php echo (int)$categoria['id']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bodega</label>

                    <select name="bodega_id" required>
                        <option value="">Seleccionar</option>

                        <?php foreach ($bodegas as $bodega): ?>

                            <option value="<?php echo (int)$bodega['id']; ?>">
                                <?php echo htmlspecialchars($bodega['nombre']); ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cepa</label>
                    <input type="text" name="cepa" required>
                </div>

                <div class="form-group">
                    <label>Añada</label>
                    <input type="number" name="anada" required>
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
                    <label>Producto destacado</label>

                    <select name="destacado">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Crear producto
                </button>

            </form>

        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>