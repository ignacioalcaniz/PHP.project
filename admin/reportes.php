<?php
require_once '../includes/auth.php';
require_once '../includes/security.php';
require_once '../config/database.php';

requireAdmin();

$pdo = conectarDB();

$sqlKpis = "
    SELECT
        (SELECT COUNT(*) FROM productos) AS total_productos,
        (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
        (SELECT COUNT(*) FROM pedidos) AS total_pedidos,
        (SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente') AS pedidos_pendientes,
        (SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado') AS pedidos_entregados,
        (SELECT COALESCE(SUM(total), 0) FROM pedidos) AS facturacion_total,
        (SELECT COALESCE(AVG(total), 0) FROM pedidos) AS ticket_promedio,
        (SELECT COALESCE(SUM(cantidad), 0) FROM pedido_items) AS unidades_vendidas,
        (SELECT COALESCE(AVG(precio), 0) FROM productos) AS precio_promedio,
        (SELECT COALESCE(AVG(stock), 0) FROM productos) AS stock_promedio
";

$stmtKpis = $pdo->prepare($sqlKpis);
$stmtKpis->execute();
$kpis = $stmtKpis->fetch();

$sqlUltimosPedidos = "
    SELECT id, nombre_cliente, email_cliente, estado, metodo_pago, total, fecha_pedido
    FROM pedidos
    ORDER BY fecha_pedido DESC
    LIMIT 5
";
$stmtUltimosPedidos = $pdo->prepare($sqlUltimosPedidos);
$stmtUltimosPedidos->execute();
$ultimosPedidos = $stmtUltimosPedidos->fetchAll();

$sqlProductosMasVendidos = "
    SELECT
        pi.producto_id,
        pi.nombre_producto,
        SUM(pi.cantidad) AS unidades_vendidas,
        SUM(pi.subtotal) AS total_generado,
        p.stock,
        c.nombre AS categoria,
        b.nombre AS bodega
    FROM pedido_items pi
    LEFT JOIN productos p ON pi.producto_id = p.id
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    GROUP BY pi.producto_id, pi.nombre_producto, p.stock, c.nombre, b.nombre
    ORDER BY unidades_vendidas DESC
    LIMIT 5
";
$stmtProductosMasVendidos = $pdo->prepare($sqlProductosMasVendidos);
$stmtProductosMasVendidos->execute();
$productosMasVendidos = $stmtProductosMasVendidos->fetchAll();

$sqlVentasPorCategoria = "
    SELECT
        c.nombre AS categoria,
        COUNT(DISTINCT p.id) AS productos_distintos,
        COALESCE(SUM(pi.cantidad), 0) AS unidades_vendidas,
        COALESCE(SUM(pi.subtotal), 0) AS total_generado
    FROM categorias c
    LEFT JOIN productos p ON p.categoria_id = c.id
    LEFT JOIN pedido_items pi ON pi.producto_id = p.id
    GROUP BY c.id, c.nombre
    ORDER BY total_generado DESC
";
$stmtVentasCategoria = $pdo->prepare($sqlVentasPorCategoria);
$stmtVentasCategoria->execute();
$ventasPorCategoria = $stmtVentasCategoria->fetchAll();

$sqlVentasPorBodega = "
    SELECT
        b.nombre AS bodega,
        b.pais,
        b.region,
        COUNT(DISTINCT p.id) AS productos_distintos,
        COALESCE(SUM(pi.cantidad), 0) AS unidades_vendidas,
        COALESCE(SUM(pi.subtotal), 0) AS total_generado
    FROM bodegas b
    LEFT JOIN productos p ON p.bodega_id = b.id
    LEFT JOIN pedido_items pi ON pi.producto_id = p.id
    GROUP BY b.id, b.nombre, b.pais, b.region
    ORDER BY total_generado DESC, b.nombre ASC
    LIMIT 8
";
$stmtVentasBodega = $pdo->prepare($sqlVentasPorBodega);
$stmtVentasBodega->execute();
$ventasPorBodega = $stmtVentasBodega->fetchAll();

$sqlStockBajo = "
    SELECT p.id, p.nombre, p.stock, p.precio, c.nombre AS categoria, b.nombre AS bodega
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    WHERE p.stock <= 5
    ORDER BY p.stock ASC, p.nombre ASC
";
$stmtStockBajo = $pdo->prepare($sqlStockBajo);
$stmtStockBajo->execute();
$productosStockBajo = $stmtStockBajo->fetchAll();

$sqlPremium = "
    SELECT p.id, p.nombre, p.precio, p.stock, c.nombre AS categoria, b.nombre AS bodega
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN bodegas b ON p.bodega_id = b.id
    WHERE p.precio > (SELECT AVG(precio) FROM productos)
    ORDER BY p.precio DESC
    LIMIT 8
";
$stmtPremium = $pdo->prepare($sqlPremium);
$stmtPremium->execute();
$productosPremium = $stmtPremium->fetchAll();

$sqlPedidosPorEstado = "
    SELECT estado, COUNT(*) AS total_pedidos, COALESCE(SUM(total), 0) AS total_generado
    FROM pedidos
    GROUP BY estado
    ORDER BY total_pedidos DESC
";
$stmtEstados = $pdo->prepare($sqlPedidosPorEstado);
$stmtEstados->execute();
$pedidosPorEstado = $stmtEstados->fetchAll();

include '../includes/header.php';
?>

<main class="section admin-shell">
    <div class="container">

        <div class="section-header">
            <span class="section-kicker">Analytics</span>
            <h2>Dashboard y reportes</h2>
            <p>Métricas comerciales con PHP + MySQL, JOINs, GROUP BY, SUM, COUNT y subconsultas.</p>
        </div>

        <div class="admin-kpi-grid">
            <div class="admin-kpi-card">
                <span>Facturación</span>
                <strong>$<?php echo number_format($kpis['facturacion_total'], 0, ',', '.'); ?></strong>
                <small>Ingresos totales registrados</small>
            </div>

            <div class="admin-kpi-card">
                <span>Pedidos</span>
                <strong><?php echo (int)$kpis['total_pedidos']; ?></strong>
                <small><?php echo (int)$kpis['pedidos_pendientes']; ?> pendientes</small>
            </div>

            <div class="admin-kpi-card">
                <span>Ticket promedio</span>
                <strong>$<?php echo number_format($kpis['ticket_promedio'], 0, ',', '.'); ?></strong>
                <small>Promedio por pedido</small>
            </div>

            <div class="admin-kpi-card">
                <span>Productos</span>
                <strong><?php echo (int)$kpis['total_productos']; ?></strong>
                <small>Catálogo activo</small>
            </div>

            <div class="admin-kpi-card">
                <span>Usuarios</span>
                <strong><?php echo (int)$kpis['total_usuarios']; ?></strong>
                <small>Registrados</small>
            </div>

            <div class="admin-kpi-card">
                <span>Unidades vendidas</span>
                <strong><?php echo (int)$kpis['unidades_vendidas']; ?></strong>
                <small>Items vendidos</small>
            </div>
        </div>

        <br><br>

        <div class="admin-report-grid">

            <section class="admin-report-panel">
                <div class="admin-panel-header">
                    <div>
                        <h2>Últimos pedidos</h2>
                        <p>Compras recientes generadas desde el checkout.</p>
                    </div>
                </div>

                <?php if (empty($ultimosPedidos)): ?>
                    <div class="admin-empty-state">
                        <h3>Sin pedidos</h3>
                        <p>Todavía no hay pedidos registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($ultimosPedidos as $pedido): ?>
                            <article class="admin-list-row">
                                <div>
                                    <h3>Pedido #<?php echo (int)$pedido['id']; ?></h3>
                                    <p><?php echo e($pedido['nombre_cliente']); ?> · <?php echo e($pedido['email_cliente']); ?></p>
                                    <p><?php echo e($pedido['fecha_pedido']); ?></p>
                                </div>

                                <div class="admin-list-actions">
                                    <span class="admin-badge"><?php echo e($pedido['estado']); ?></span>
                                    <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>
                                    <a href="/proyecto_cava_Noble/admin/detalle-pedido.php?id=<?php echo (int)$pedido['id']; ?>" class="btn-card">Ver</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="admin-report-panel">
                <div class="admin-panel-header">
                    <div>
                        <h2>Productos más vendidos</h2>
                        <p>Ranking por unidades vendidas.</p>
                    </div>
                </div>

                <?php if (empty($productosMasVendidos)): ?>
                    <div class="admin-empty-state">
                        <h3>Sin ventas</h3>
                        <p>Todavía no hay ventas registradas.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($productosMasVendidos as $producto): ?>
                            <article class="admin-list-row">
                                <div>
                                    <h3><?php echo e($producto['nombre_producto']); ?></h3>
                                    <p><?php echo e($producto['bodega'] ?? 'Sin bodega'); ?> · <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?></p>
                                    <p><strong>Stock:</strong> <?php echo (int)$producto['stock']; ?></p>
                                </div>

                                <div class="admin-list-actions">
                                    <span class="admin-badge"><?php echo (int)$producto['unidades_vendidas']; ?> unidades</span>
                                    <strong>$<?php echo number_format($producto['total_generado'], 0, ',', '.'); ?></strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>

        <br><br>

        <div class="admin-report-grid">

            <section class="admin-report-panel">
                <h2>Ventas por categoría</h2>

                <div class="admin-list">
                    <?php foreach ($ventasPorCategoria as $item): ?>
                        <article class="admin-list-row">
                            <div>
                                <h3><?php echo e($item['categoria']); ?></h3>
                                <p>Productos: <?php echo (int)$item['productos_distintos']; ?> · Unidades: <?php echo (int)$item['unidades_vendidas']; ?></p>
                            </div>
                            <strong>$<?php echo number_format($item['total_generado'], 0, ',', '.'); ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-report-panel">
                <h2>Ventas por bodega</h2>

                <div class="admin-list">
                    <?php foreach ($ventasPorBodega as $item): ?>
                        <article class="admin-list-row">
                            <div>
                                <h3><?php echo e($item['bodega']); ?></h3>
                                <p><?php echo e($item['pais']); ?> · <?php echo e($item['region']); ?></p>
                                <p>Productos: <?php echo (int)$item['productos_distintos']; ?> · Unidades: <?php echo (int)$item['unidades_vendidas']; ?></p>
                            </div>
                            <strong>$<?php echo number_format($item['total_generado'], 0, ',', '.'); ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>

        <br><br>

        <div class="admin-report-grid">

            <section class="admin-report-panel">
                <h2>Pedidos por estado</h2>

                <?php if (empty($pedidosPorEstado)): ?>
                    <p>No hay estados para mostrar.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($pedidosPorEstado as $item): ?>
                            <article class="admin-list-row">
                                <div>
                                    <h3><?php echo e(ucfirst($item['estado'])); ?></h3>
                                    <p>Pedidos: <?php echo (int)$item['total_pedidos']; ?></p>
                                </div>
                                <strong>$<?php echo number_format($item['total_generado'], 0, ',', '.'); ?></strong>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="admin-report-panel">
                <h2>Alertas de stock bajo</h2>

                <?php if (empty($productosStockBajo)): ?>
                    <p>No hay productos con stock bajo.</p>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($productosStockBajo as $producto): ?>
                            <article class="admin-list-row">
                                <div>
                                    <h3><?php echo e($producto['nombre']); ?></h3>
                                    <p><?php echo e($producto['bodega'] ?? 'Sin bodega'); ?> · <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?></p>
                                    <p>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></p>
                                </div>
                                <span class="admin-badge"><?php echo (int)$producto['stock']; ?> unidades</span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>

        <br><br>

        <section class="admin-report-panel">
            <h2>Productos premium</h2>
            <p>Productos con precio superior al promedio del catálogo.</p>

            <?php if (empty($productosPremium)): ?>
                <div class="admin-empty-state">
                    <h3>Sin productos premium</h3>
                    <p>No hay productos premium detectados.</p>
                </div>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($productosPremium as $producto): ?>
                        <article class="admin-list-row">
                            <div>
                                <h3><?php echo e($producto['nombre']); ?></h3>
                                <p><?php echo e($producto['bodega'] ?? 'Sin bodega'); ?> · <?php echo e($producto['categoria'] ?? 'Sin categoría'); ?></p>
                                <p>Stock: <?php echo (int)$producto['stock']; ?></p>
                            </div>

                            <strong>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <br>

        <a href="/proyecto_cava_Noble/admin/index.php" class="btn btn-secondary">
            Volver al panel
        </a>

    </div>
</main>

<?php include '../includes/footer.php'; ?>