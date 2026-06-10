<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    redirect('/proyecto_cava_Noble/admin/productos.php');
}

$sqlProducto = "SELECT * FROM productos WHERE id = :id LIMIT 1";
$stmtProducto = $pdo->prepare($sqlProducto);
$stmtProducto->bindParam(':id', $id, PDO::PARAM_INT);
$stmtProducto->execute();

$producto = $stmtProducto->fetch();

if (!$producto) {
    redirect('/proyecto_cava_Noble/admin/productos.php');
}

$stmtCategorias = $pdo->prepare("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll();

$stmtBodegas = $pdo->prepare("SELECT id, nombre FROM bodegas ORDER BY nombre ASC");
$stmtBodegas->execute();
$bodegas = $stmtBodegas->fetchAll();

$csrfToken = generateCsrfToken();

include '../includes/header.php';
?>

<main class="section">
    <div class="container">
        <div class="section-header">
            <h2>Editar producto</h2>
            <p>Modificá la información del vino seleccionado.</p>
        </div>

        <div class="form-container" style="max-width:800px;">
            <form
                action="/proyecto_cava_Noble/admin/procesar-editar-producto.php"
                method="POST"
                enctype="multipart/form-data"
                class="auth-form"
            >
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                <input type="hidden" name="id" value="<?php echo (int)$producto['id']; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo e($producto['imagen']); ?>">

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo e($producto['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="5" required><?php echo e($producto['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Precio</label>
                    <input type="number" name="precio" min="0" step="0.01" value="<?php echo e($producto['precio']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo (int)$categoria['id']; ?>" <?php if ((int)$producto['categoria_id'] === (int)$categoria['id']) echo 'selected'; ?>>
                                <?php echo e($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bodega</label>
                    <select name="bodega_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($bodegas as $bodega): ?>
                            <option value="<?php echo (int)$bodega['id']; ?>" <?php if ((int)$producto['bodega_id'] === (int)$bodega['id']) echo 'selected'; ?>>
                                <?php echo e($bodega['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cepa</label>
                    <input type="text" name="cepa" value="<?php echo e($producto['cepa']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Añada</label>
                    <input type="number" name="anada" min="1900" max="2100" value="<?php echo e($producto['anada']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" min="0" value="<?php echo e($producto['stock']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Imagen actual</label>
                    <img src="<?php echo e($producto['imagen']); ?>" alt="<?php echo e($producto['nombre']); ?>" style="max-width:180px; border-radius:16px; background:#fff; padding:10px;">
                </div>

                <div class="form-group">
                    <label>Nueva imagen opcional</label>
                    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                    <small>Si no seleccionás una nueva imagen, se mantiene la actual.</small>
                </div>

                <div class="form-group">
                    <label>Producto destacado</label>
                    <select name="destacado" required>
                        <option value="0" <?php if ((int)$producto['destacado'] === 0) echo 'selected'; ?>>No</option>
                        <option value="1" <?php if ((int)$producto['destacado'] === 1) echo 'selected'; ?>>Sí</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Guardar cambios</button>

                <a href="/proyecto_cava_Noble/admin/productos.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>