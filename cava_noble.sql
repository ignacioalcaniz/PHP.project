-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-07-2026 a las 05:11:32
-- Versión del servidor: 12.2.2-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cava_noble`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `entidad` varchar(100) NOT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `accion`, `entidad`, `entidad_id`, `descripcion`, `ip_address`, `created_at`) VALUES
(1, 1, 'ELIMINAR', 'CATEGORIA', 7, 'Categoría eliminada: Pinot Nior', '::1', '2026-06-24 18:09:22'),
(2, 2, 'FINALIZAR_PEDIDO', 'PEDIDO', 11, 'Pedido #11 del cliente \"Ignacio Alcañiz\" finalizado. Estado modificado de \"procesando\" a \"entregado\".', '::1', '2026-07-16 03:01:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodegas`
--

CREATE TABLE `bodegas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bodegas`
--

INSERT INTO `bodegas` (`id`, `nombre`, `pais`, `region`, `descripcion`, `creado_en`) VALUES
(1, 'Catena Zapata', 'Argentina', 'Mendoza', 'Bodega argentina reconocida internacionalmente por sus vinos de alta gama.', '2026-06-10 18:57:21'),
(2, 'Norton', 'Argentina', 'Mendoza', 'Bodega tradicional mendocina con amplia trayectoria.', '2026-06-10 18:57:21'),
(3, 'El Esteco', 'Argentina', 'Salta', 'Bodega del norte argentino, reconocida por vinos de altura.', '2026-06-10 18:57:21'),
(4, 'Bodega Chacra', 'Argentina', 'Patagonia', 'Bodega patagónica especializada en Pinot Noir.', '2026-06-10 18:57:21'),
(5, 'Luigi Bosca', 'Argentina', 'Mendoza', 'Bodega familiar argentina con gran presencia internacional.', '2026-06-10 18:57:21'),
(6, 'Zuccardi', 'Argentina', 'Mendoza', 'Bodega argentina moderna, reconocida por vinos de terroir.', '2026-06-10 18:57:21'),
(7, 'Trapiche', 'Argentina', 'Mendoza', 'Una de las bodegas argentinas más reconocidas y exportadas.', '2026-06-10 18:57:21'),
(8, 'Rutini Wines', 'Argentina', 'Mendoza', 'Bodega histórica de vinos argentinos de alta calidad.', '2026-06-10 18:57:21'),
(9, 'Concha y Toro', 'Chile', 'Valle Central', 'Bodega chilena con gran presencia internacional.', '2026-06-10 18:57:21'),
(10, 'Montes', 'Chile', 'Colchagua', 'Bodega chilena premium, reconocida por Carménère y blends.', '2026-06-10 18:57:21'),
(11, 'Casillero del Diablo', 'Chile', 'Valle Central', 'Línea reconocida internacionalmente de vinos chilenos.', '2026-06-10 18:57:21'),
(12, 'Louis Jadot', 'Francia', 'Borgoña', 'Casa francesa tradicional especializada en vinos de Borgoña.', '2026-06-10 18:57:21'),
(13, 'Maison Bordeaux', 'Francia', 'Bordeaux', 'Casa francesa de vinos tintos clásicos de Bordeaux.', '2026-06-10 18:57:21'),
(14, 'Moët & Chandon', 'Francia', 'Champagne', 'Casa francesa reconocida mundialmente por sus espumantes.', '2026-06-10 18:57:21'),
(15, 'Ruffino', 'Italia', 'Toscana', 'Bodega italiana tradicional reconocida por Chianti.', '2026-06-10 18:57:21'),
(16, 'Antinori', 'Italia', 'Toscana', 'Bodega histórica italiana de vinos premium.', '2026-06-10 18:57:21'),
(17, 'Masi', 'Italia', 'Veneto', 'Bodega italiana especializada en Amarone y vinos del Veneto.', '2026-06-10 18:57:21'),
(18, 'Marqués de Riscal', 'España', 'Rioja', 'Bodega española tradicional con vinos de Rioja.', '2026-06-10 18:57:21'),
(19, 'Torres', 'España', 'Cataluña', 'Bodega española reconocida internacionalmente por vinos mediterráneos.', '2026-06-10 18:57:21'),
(20, 'Freixenet', 'España', 'Cataluña', 'Casa española reconocida por sus cavas y espumantes.', '2026-06-10 18:57:21'),
(21, 'Achaval Ferrer', 'Argentina', 'Mendoza', 'Bodega mendocina reconocida por Malbecs de alta gama y vinos de terroir.', '2026-06-10 19:10:11'),
(22, 'Viña Cobos', 'Argentina', 'Mendoza', 'Bodega fundada por Paul Hobbs, reconocida por vinos premium de gran concentración.', '2026-06-10 19:10:11'),
(23, 'Terrazas de los Andes', 'Argentina', 'Mendoza', 'Bodega mendocina enfocada en vinos de altura y expresión varietal.', '2026-06-10 19:10:11'),
(24, 'Alta Vista', 'Argentina', 'Mendoza', 'Bodega argentina reconocida por Malbecs elegantes y vinos de viñedo único.', '2026-06-10 19:10:11'),
(25, 'Kaiken', 'Argentina', 'Mendoza', 'Bodega argentina vinculada a Montes, con vinos expresivos y modernos.', '2026-06-10 19:10:11'),
(26, 'Doña Paula', 'Argentina', 'Mendoza', 'Bodega de Mendoza especializada en vinos varietales de buena relación precio-calidad.', '2026-06-10 19:10:11'),
(27, 'Andeluna', 'Argentina', 'Mendoza', 'Bodega ubicada en Tupungato, Valle de Uco, reconocida por vinos de altura.', '2026-06-10 19:10:11'),
(28, 'Salentein', 'Argentina', 'Mendoza', 'Bodega del Valle de Uco con arquitectura icónica y vinos de alta gama.', '2026-06-10 19:10:11'),
(29, 'Pulenta Estate', 'Argentina', 'Mendoza', 'Bodega familiar mendocina con vinos elegantes y modernos.', '2026-06-10 19:10:11'),
(30, 'Nieto Senetiner', 'Argentina', 'Mendoza', 'Bodega tradicional de Mendoza con amplia trayectoria en vinos argentinos.', '2026-06-10 19:10:11'),
(31, 'Colomé', 'Argentina', 'Salta', 'Bodega salteña de altura, reconocida por Malbecs intensos y Torrontés expresivos.', '2026-06-10 19:10:11'),
(32, 'Amalaya', 'Argentina', 'Salta', 'Bodega del Valle Calchaquí con vinos frescos, aromáticos y de altura.', '2026-06-10 19:10:11'),
(33, 'Domingo Molina', 'Argentina', 'Salta', 'Bodega familiar salteña especializada en vinos de altura y gran carácter.', '2026-06-10 19:10:11'),
(34, 'Piattelli Vineyards Cafayate', 'Argentina', 'Salta', 'Bodega ubicada en Cafayate, reconocida por vinos de altura y estilo moderno.', '2026-06-10 19:10:11'),
(35, 'La Caroyense', 'Argentina', 'Córdoba', 'Bodega histórica de Colonia Caroya, referente vitivinícola de Córdoba.', '2026-06-10 19:10:11'),
(36, 'Terra Camiare', 'Argentina', 'Córdoba', 'Bodega cordobesa ubicada en Colonia Caroya, reconocida por vinos regionales renovados.', '2026-06-10 19:10:11'),
(37, 'Bodega Jairala Oller', 'Argentina', 'Córdoba', 'Bodega cordobesa con tradición familiar y producción de vinos regionales.', '2026-06-10 19:10:11'),
(38, 'Finca Atos', 'Argentina', 'Córdoba', 'Proyecto vitivinícola cordobés enfocado en vinos de identidad local.', '2026-06-10 19:10:11'),
(39, 'Humberto Canale', 'Argentina', 'Patagonia', 'Bodega histórica de Río Negro, referente en vinos patagónicos.', '2026-06-10 19:10:11'),
(40, 'Noemía', 'Argentina', 'Patagonia', 'Bodega patagónica de alta gama reconocida por Malbecs finos y elegantes.', '2026-06-10 19:10:11'),
(41, 'Familia Schroeder', 'Argentina', 'Patagonia', 'Bodega de Neuquén con vinos modernos y expresivos.', '2026-06-10 19:10:11'),
(42, 'Fin del Mundo', 'Argentina', 'Patagonia', 'Bodega neuquina reconocida por vinos patagónicos de amplia distribución.', '2026-06-10 19:10:11'),
(43, 'Santa Rita', 'Chile', 'Valle Central', 'Bodega chilena tradicional con gran presencia internacional.', '2026-06-10 19:10:11'),
(44, 'Errázuriz', 'Chile', 'Aconcagua', 'Bodega chilena reconocida por vinos premium y expresión de terroir.', '2026-06-10 19:10:11'),
(45, 'Viña Seña', 'Chile', 'Aconcagua', 'Proyecto chileno de alta gama reconocido internacionalmente.', '2026-06-10 19:10:11'),
(46, 'Lapostolle', 'Chile', 'Colchagua', 'Bodega chilena premium reconocida por Clos Apalta y vinos de gran elegancia.', '2026-06-10 19:10:11'),
(47, 'Undurraga', 'Chile', 'Valle Central', 'Bodega chilena histórica con vinos accesibles y exportación internacional.', '2026-06-10 19:10:11'),
(48, 'Château Margaux', 'Francia', 'Bordeaux', 'Prestigioso château francés de Bordeaux, reconocido por vinos de colección.', '2026-06-10 19:10:11'),
(49, 'Château Lafite Rothschild', 'Francia', 'Bordeaux', 'Casa francesa icónica de Pauillac, referente mundial de vinos premium.', '2026-06-10 19:10:11'),
(50, 'Château Mouton Rothschild', 'Francia', 'Bordeaux', 'Château histórico de Bordeaux, reconocido por etiquetas artísticas y vinos excepcionales.', '2026-06-10 19:10:11'),
(51, 'Dom Pérignon', 'Francia', 'Champagne', 'Casa francesa de champagne premium, símbolo de lujo y celebración.', '2026-06-10 19:10:11'),
(52, 'Veuve Clicquot', 'Francia', 'Champagne', 'Casa de champagne reconocida por su estilo elegante y su etiqueta amarilla.', '2026-06-10 19:10:11'),
(53, 'Tenuta San Guido', 'Italia', 'Toscana', 'Bodega italiana reconocida por Sassicaia, uno de los vinos más importantes de Italia.', '2026-06-10 19:10:11'),
(54, 'Gaja', 'Italia', 'Piamonte', 'Bodega italiana de alta gama, referente en Barbaresco y vinos premium.', '2026-06-10 19:10:11'),
(55, 'Marchesi di Barolo', 'Italia', 'Piamonte', 'Bodega histórica italiana especializada en Barolo y vinos del Piamonte.', '2026-06-10 19:10:11'),
(56, 'Biondi-Santi', 'Italia', 'Toscana', 'Bodega histórica de Montalcino, referente del Brunello.', '2026-06-10 19:10:11'),
(57, 'Frescobaldi', 'Italia', 'Toscana', 'Familia vitivinícola italiana con larga tradición y vinos de prestigio.', '2026-06-10 19:10:11'),
(58, 'Vega Sicilia', 'España', 'Ribera del Duero', 'Bodega española legendaria, reconocida por vinos de alta gama y larga guarda.', '2026-06-10 19:10:11'),
(59, 'Pingus', 'España', 'Ribera del Duero', 'Proyecto español premium reconocido por vinos exclusivos y de colección.', '2026-06-10 19:10:11'),
(60, 'Protos', 'España', 'Ribera del Duero', 'Bodega española tradicional de Ribera del Duero con gran reconocimiento.', '2026-06-10 19:10:11'),
(61, 'La Rioja Alta', 'España', 'Rioja', 'Bodega histórica riojana reconocida por reservas clásicos y elegantes.', '2026-06-10 19:10:11'),
(62, 'Codorníu', 'España', 'Cataluña', 'Casa española tradicional reconocida por sus cavas y espumantes.', '2026-06-10 19:10:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bodegas_backup`
--

CREATE TABLE `bodegas_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nombre` varchar(120) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bodegas_backup`
--

INSERT INTO `bodegas_backup` (`id`, `nombre`, `pais`, `region`, `descripcion`, `creado_en`) VALUES
(1, 'Catena Zapata', 'Argentina', 'Mendoza', 'Bodega premium argentina', '2026-05-20 17:07:44'),
(2, 'Norton', 'Argentina', 'Mendoza', 'Bodega tradicional mendocina', '2026-05-20 17:07:44'),
(3, 'Concha y Toro', 'Chile', 'Valle Central', 'Bodega chilena internacional', '2026-05-20 17:07:44'),
(4, 'Louis Jadot', 'Francia', 'Borgoña', 'Casa tradicional francesa', '2026-05-20 17:07:44'),
(5, 'Luigi Bosca', 'Argentina', 'Mendoza', 'Bodega argentina tradicional', '2026-05-20 18:10:54'),
(6, 'Zuccardi', 'Argentina', 'Mendoza', 'Bodega argentina reconocida por vinos de calidad', '2026-05-20 18:10:54'),
(7, 'Bodega Chacra', 'Argentina', 'Patagonia', 'Bodega patagónica especializada en Pinot Noir', '2026-05-20 18:10:54'),
(8, 'Montes', 'Chile', 'Colchagua', 'Bodega chilena premium', '2026-05-20 18:10:54'),
(9, 'Maison Bordeaux', 'Francia', 'Bordeaux', 'Casa francesa de vinos tintos', '2026-05-20 18:10:54'),
(10, 'Antinori', 'Italia', 'Toscana', 'Bodega italiana histórica', '2026-05-20 18:10:54'),
(11, 'Marqués de Riscal', 'España', 'Rioja', 'Bodega española tradicional', '2026-05-20 18:10:54'),
(12, 'Torres', 'España', 'Cataluña', 'Bodega española reconocida internacionalmente', '2026-05-20 18:10:54'),
(13, 'Catena Zapata', 'Argentina', 'Mendoza', 'Bodega premium argentina', '2026-05-20 18:19:48'),
(14, 'Norton', 'Argentina', 'Mendoza', 'Bodega tradicional mendocina', '2026-05-20 18:19:48'),
(15, 'El Esteco', 'Argentina', 'Salta', 'Bodega del norte argentino', '2026-05-20 18:19:48'),
(16, 'Bodega Chacra', 'Argentina', 'Patagonia', 'Bodega patagónica', '2026-05-20 18:19:48'),
(17, 'Luigi Bosca', 'Argentina', 'Mendoza', 'Bodega argentina tradicional', '2026-05-20 18:19:48'),
(18, 'Zuccardi', 'Argentina', 'Mendoza', 'Bodega argentina reconocida', '2026-05-20 18:19:48'),
(19, 'Concha y Toro', 'Chile', 'Valle Central', 'Bodega chilena internacional', '2026-05-20 18:19:48'),
(20, 'Montes', 'Chile', 'Colchagua', 'Bodega chilena premium', '2026-05-20 18:19:48'),
(21, 'Louis Jadot', 'Francia', 'Borgoña', 'Casa francesa tradicional', '2026-05-20 18:19:48'),
(22, 'Maison Bordeaux', 'Francia', 'Bordeaux', 'Casa francesa de Bordeaux', '2026-05-20 18:19:48'),
(23, 'Ruffino', 'Italia', 'Toscana', 'Bodega italiana tradicional', '2026-05-20 18:19:48'),
(24, 'Antinori', 'Italia', 'Toscana', 'Bodega italiana histórica', '2026-05-20 18:19:48'),
(25, 'Marqués de Riscal', 'España', 'Rioja', 'Bodega española tradicional', '2026-05-20 18:19:48'),
(26, 'Torres', 'España', 'Cataluña', 'Bodega española internacional', '2026-05-20 18:19:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `creado_en`) VALUES
(1, 'Tintos', 'Vinos tintos nacionales e importados', '2026-06-10 18:57:21'),
(2, 'Blancos', 'Vinos blancos frescos y aromáticos', '2026-06-10 18:57:21'),
(3, 'Espumantes', 'Vinos espumantes para celebraciones', '2026-06-10 18:57:21'),
(4, 'Rosados', 'Vinos rosados frescos y versátiles', '2026-06-10 18:57:21'),
(5, 'Importados', 'Selección internacional de vinos', '2026-06-10 18:57:21'),
(6, 'Premium', 'Etiquetas de alta gama y selección especial', '2026-06-10 18:57:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `attempted_at`) VALUES
(21, 'ignaalcaniz@gmail.com', '::1', 0, '2026-07-14 22:55:37'),
(22, 'ignaalcaniz@gmail.com', '::1', 0, '2026-07-14 22:56:00'),
(23, 'ignaalcaniz@gmail.com', '::1', 0, '2026-07-15 23:20:25'),
(24, 'ignaalcaniz@gmail.com', '::1', 1, '2026-07-15 23:20:40'),
(25, 'ignaalcaniz@gmail.com', '::1', 1, '2026-07-15 23:45:41'),
(26, 'ignaalcaniz@gmail.com', '::1', 1, '2026-07-16 00:15:15'),
(27, 'ignaalcaniz@gmail.com', '::1', 1, '2026-07-16 01:58:05'),
(28, 'ignaalcaniz@gmail.com', '::1', 1, '2026-07-16 02:51:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(150) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'procesando',
  `total` decimal(10,2) NOT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `nombre_cliente`, `email_cliente`, `telefono`, `direccion`, `ciudad`, `provincia`, `metodo_pago`, `estado`, `total`, `fecha_pedido`) VALUES
(11, 2, 'Ignacio Alcañiz', 'ignaalcaniz@gmail.com', '+543515143521', 'de los belgas 6355 barrio boulevares', 'Cordoba', 'Córdoba', 'efectivo', 'entregado', 76000.00, '2026-07-15 23:52:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `nombre_producto`, `precio_unitario`, `cantidad`, `subtotal`) VALUES
(6, 11, 18, 'Masi Costasera Amarone', 76000.00, 1, 76000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `bodega` varchar(120) NOT NULL,
  `cepa` varchar(100) NOT NULL,
  `anada` year(4) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) NOT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL,
  `bodega_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `pais`, `region`, `bodega`, `cepa`, `anada`, `stock`, `imagen`, `destacado`, `creado_en`, `categoria_id`, `bodega_id`) VALUES
(1, 'Catena Zapata Malbec Argentino', 'Malbec argentino premium de alta gama, complejo, elegante y persistente.', 95000.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 5, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29bac550c7a4.95354544.jpg', 1, '2026-06-10 18:57:21', 6, 1),
(2, 'Catena Malbec', 'Malbec elegante con notas de frutos negros y final persistente.', 18500.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 15, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29bab6b2ae88.43365994.jpg', 1, '2026-06-10 18:57:21', 1, 1),
(3, 'Norton Reserva Cabernet', 'Cabernet Sauvignon intenso y estructurado.', 16900.00, 'Argentina', 'Mendoza', 'Norton', 'Cabernet Sauvignon', '2021', 12, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29ba954b6149.04252537.jpg', 0, '2026-06-10 18:57:21', 1, 2),
(4, 'El Esteco Torrontés', 'Blanco aromático típico del norte argentino.', 15200.00, 'Argentina', 'Salta', 'El Esteco', 'Torrontés', '2023', 10, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29ba700858d0.91560751.jpg', 1, '2026-06-10 18:57:21', 2, 3),
(5, 'Bodega Chacra Pinot Noir', 'Pinot Noir patagónico fino, elegante y expresivo.', 32500.00, 'Argentina', 'Patagonia', 'Bodega Chacra', 'Pinot Noir', '2021', 6, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29ba47521b87.98488085.jpg', 1, '2026-06-10 18:57:21', 1, 4),
(6, 'Luigi Bosca Malbec', 'Malbec clásico argentino con gran balance y expresión varietal.', 19800.00, 'Argentina', 'Mendoza', 'Luigi Bosca', 'Malbec', '2022', 11, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29ba1bbab8b2.32069366.jpg', 0, '2026-06-10 18:57:21', 1, 5),
(7, 'Zuccardi Q Cabernet Franc', 'Cabernet Franc moderno, sofisticado y de gran carácter.', 23900.00, 'Argentina', 'Mendoza', 'Zuccardi', 'Cabernet Franc', '2021', 8, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b9f029f909.29106995.jpg', 1, '2026-06-10 18:57:21', 1, 6),
(8, 'Trapiche Medalla Malbec', 'Malbec argentino clásico, intenso y equilibrado.', 21500.00, 'Argentina', 'Mendoza', 'Trapiche', 'Malbec', '2021', 10, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b9b4b397f8.83682914.jpg', 0, '2026-06-10 18:57:21', 1, 7),
(9, 'Rutini Cabernet Malbec', 'Blend argentino elegante con estructura y complejidad.', 33500.00, 'Argentina', 'Mendoza', 'Rutini Wines', 'Blend', '2021', 7, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b982886703.50972676.jpg', 1, '2026-06-10 18:57:21', 6, 8),
(10, 'Concha y Toro Reservado', 'Cabernet chileno suave, frutado y accesible.', 14900.00, 'Chile', 'Valle Central', 'Concha y Toro', 'Cabernet Sauvignon', '2022', 20, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b929d5fa27.92100467.jpg', 0, '2026-06-10 18:57:21', 5, 9),
(11, 'Montes Alpha Carménère', 'Carménère intenso con especias y fruta madura.', 24500.00, 'Chile', 'Colchagua', 'Montes', 'Carménère', '2021', 9, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b8f6cc7219.18278913.jpg', 1, '2026-06-10 18:57:21', 5, 10),
(12, 'Casillero del Diablo Cabernet Sauvignon', 'Tinto chileno reconocido mundialmente por su equilibrio y carácter.', 17200.00, 'Chile', 'Valle Central', 'Casillero del Diablo', 'Cabernet Sauvignon', '2022', 14, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b8d38b4682.70644464.jpg', 0, '2026-06-10 18:57:21', 5, 11),
(13, 'Louis Jadot Chardonnay', 'Blanco francés refinado, fresco y equilibrado.', 28900.00, 'Francia', 'Borgoña', 'Louis Jadot', 'Chardonnay', '2020', 7, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b8b37cae23.20887774.jpg', 1, '2026-06-10 18:57:21', 5, 12),
(14, 'Bordeaux Rouge Classic', 'Tinto clásico francés con perfil elegante y tradicional.', 31900.00, 'Francia', 'Bordeaux', 'Maison Bordeaux', 'Blend', '2019', 5, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b8842bba60.25809205.jpg', 0, '2026-06-10 18:57:21', 5, 13),
(15, 'Moët & Chandon Brut Impérial', 'Espumante francés icónico, elegante y fresco.', 82000.00, 'Francia', 'Champagne', 'Moët & Chandon', 'Blend', '2022', 4, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b851024870.66082811.jpg', 1, '2026-06-10 18:57:21', 6, 14),
(16, 'Ruffino Chianti', 'Chianti tradicional italiano con gran personalidad.', 26900.00, 'Italia', 'Toscana', 'Ruffino', 'Sangiovese', '2021', 10, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b82e3b44c8.61425453.jpg', 1, '2026-06-10 18:57:21', 5, 15),
(17, 'Villa Antinori Rosso', 'Tinto premium italiano moderno, complejo y elegante.', 35500.00, 'Italia', 'Toscana', 'Antinori', 'Blend', '2020', 4, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b80012f099.70869713.jpg', 1, '2026-06-10 18:57:21', 6, 16),
(18, 'Masi Costasera Amarone', 'Amarone italiano intenso, complejo y de gran cuerpo.', 76000.00, 'Italia', 'Veneto', 'Masi', 'Corvina Blend', '2019', 2, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b7d496d896.78438320.png', 1, '2026-06-10 18:57:21', 6, 17),
(19, 'Marqués de Riscal Reserva', 'Rioja tradicional con crianza elegante y final persistente.', 27900.00, 'España', 'Rioja', 'Marqués de Riscal', 'Tempranillo', '2020', 8, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b762128331.43950710.jpg', 1, '2026-06-10 18:57:21', 5, 18),
(20, 'Torres Sangre de Toro', 'Tinto español clásico, frutado y amable.', 18900.00, 'España', 'Cataluña', 'Torres', 'Blend', '2021', 13, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b743a41937.65108114.png', 0, '2026-06-10 18:57:21', 5, 19),
(21, 'Freixenet Cordon Negro Brut', 'Cava español fresco, seco y versátil.', 22500.00, 'España', 'Cataluña', 'Freixenet', 'Blend', '2022', 9, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b6e8251da3.06415534.jpg', 0, '2026-06-10 18:57:21', 3, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_backup`
--

CREATE TABLE `productos_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `bodega` varchar(120) NOT NULL,
  `cepa` varchar(100) NOT NULL,
  `anada` year(4) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) NOT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL,
  `bodega_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos_backup`
--

INSERT INTO `productos_backup` (`id`, `nombre`, `descripcion`, `precio`, `pais`, `region`, `bodega`, `cepa`, `anada`, `stock`, `imagen`, `destacado`, `creado_en`, `categoria_id`, `bodega_id`) VALUES
(1, 'Malbec Reserva', 'Vino argentino elegante, con notas frutadas y gran estructura.', 18500.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 12, 'https://via.placeholder.com/300x350?text=Malbec+Reserva', 1, '2026-04-16 20:10:18', 1, 1),
(2, 'Cabernet Sauvignon', 'Vino intenso del Valle Central con final persistente.', 22900.00, 'Chile', 'Valle Central', 'Concha y Toro', 'Cabernet Sauvignon', '2021', 8, 'https://via.placeholder.com/300x350?text=Cabernet+Sauvignon', 1, '2026-04-16 20:10:18', 4, 7),
(3, 'Chardonnay Premium', 'Blanco sofisticado, equilibrado y fresco.', 29400.00, 'Francia', 'Borgoña', 'Louis Jadot', 'Chardonnay', '2020', 5, 'https://via.placeholder.com/300x350?text=Chardonnay+Premium', 1, '2026-04-16 20:10:18', 4, 9),
(4, 'Sangiovese Classico', 'Vino italiano con gran personalidad y perfil tradicional.', 31200.00, 'Italia', 'Toscana', 'Ruffino', 'Sangiovese', '2021', 10, 'https://via.placeholder.com/300x350?text=Sangiovese+Classico', 0, '2026-04-16 20:10:18', 4, 11),
(5, 'Catena Malbec', 'Malbec elegante con notas de frutos negros y final persistente.', 18500.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 15, 'https://via.placeholder.com/300x350?text=Catena+Malbec', 1, '2026-04-22 16:40:09', 1, 1),
(6, 'Norton Reserva Cabernet', 'Cabernet Sauvignon intenso y estructurado.', 16900.00, 'Argentina', 'Mendoza', 'Norton', 'Cabernet Sauvignon', '2021', 12, 'https://via.placeholder.com/300x350?text=Norton+Cabernet', 0, '2026-04-22 16:40:09', 1, 2),
(7, 'El Esteco Torrontés', 'Blanco aromático típico del norte argentino.', 15200.00, 'Argentina', 'Salta', 'El Esteco', 'Torrontés', '2023', 10, 'https://via.placeholder.com/300x350?text=El+Esteco+Torrontes', 1, '2026-04-22 16:40:09', 2, 3),
(8, 'Bodega Chacra Pinot Noir', 'Pinot Noir patagónico fino y elegante.', 32500.00, 'Argentina', 'Patagonia', 'Bodega Chacra', 'Pinot Noir', '2021', 6, 'https://via.placeholder.com/300x350?text=Chacra+Pinot+Noir', 1, '2026-04-22 16:40:09', 1, 4),
(9, 'Luigi Bosca Malbec', 'Malbec clásico argentino con gran balance.', 19800.00, 'Argentina', 'Mendoza', 'Luigi Bosca', 'Malbec', '2022', 11, 'https://via.placeholder.com/300x350?text=Luigi+Bosca+Malbec', 0, '2026-04-22 16:40:09', 1, 5),
(10, 'Zuccardi Q Cabernet Franc', 'Cabernet Franc moderno y sofisticado.', 23900.00, 'Argentina', 'Mendoza', 'Zuccardi', 'Cabernet Franc', '2021', 8, 'https://via.placeholder.com/300x350?text=Zuccardi+Cabernet+Franc', 1, '2026-04-22 16:40:09', 1, 6),
(11, 'Concha y Toro Reservado', 'Cabernet chileno suave y accesible.', 14900.00, 'Chile', 'Valle Central', 'Concha y Toro', 'Cabernet Sauvignon', '2022', 20, 'https://via.placeholder.com/300x350?text=Concha+y+Toro', 0, '2026-04-22 16:40:09', 4, 7),
(12, 'Montes Alpha Carménère', 'Carménère intenso con especias y fruta madura.', 24500.00, 'Chile', 'Colchagua', 'Montes', 'Carménère', '2021', 9, 'https://via.placeholder.com/300x350?text=Montes+Alpha', 1, '2026-04-22 16:40:09', 4, 8),
(13, 'Louis Jadot Chardonnay', 'Blanco francés refinado y equilibrado.', 28900.00, 'Francia', 'Borgoña', 'Louis Jadot', 'Chardonnay', '2020', 7, 'https://via.placeholder.com/300x350?text=Louis+Jadot', 1, '2026-04-22 16:40:09', 4, 9),
(14, 'Bordeaux Rouge Classic', 'Tinto clásico francés con perfil elegante.', 31900.00, 'Francia', 'Bordeaux', 'Maison Bordeaux', 'Blend', '2019', 5, 'https://via.placeholder.com/300x350?text=Bordeaux+Rouge', 0, '2026-04-22 16:40:09', 4, 10),
(15, 'Ruffino Chianti', 'Chianti tradicional italiano con gran personalidad.', 26900.00, 'Italia', 'Toscana', 'Ruffino', 'Sangiovese', '2021', 10, 'https://via.placeholder.com/300x350?text=Ruffino+Chianti', 1, '2026-04-22 16:40:09', 4, 11),
(16, 'Villa Antinori Rosso', 'Tinto premium italiano moderno y complejo.', 35500.00, 'Italia', 'Toscana', 'Antinori', 'Blend', '2020', 4, 'https://via.placeholder.com/300x350?text=Antinori+Rosso', 1, '2026-04-22 16:40:09', 4, 12),
(17, 'Marqués de Riscal Reserva', 'Rioja tradicional con crianza elegante.', 27900.00, 'España', 'Rioja', 'Marqués de Riscal', 'Tempranillo', '2020', 8, 'https://via.placeholder.com/300x350?text=Marques+de+Riscal', 1, '2026-04-22 16:40:09', 4, 13),
(18, 'Torres Sangre de Toro', 'Tinto español clásico, frutado y amable.', 18900.00, 'Argentina', 'Mendoza', 'Norton', 'Blend', '2021', 12, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29b1bfdd1d88.57196305.jpg', 0, '2026-04-22 16:40:09', 4, 14),
(19, 'Catena Zapata Malbec Argentino', 'Malbec argentino premium de alta gama elaborado por Catena Zapata. Presenta gran concentración, elegancia y complejidad, con notas de frutos negros, especias, moka, tabaco y final largo y persistente. Su etiqueta representa la historia del Malbec desde Francia hasta Argentina.', 50000.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 9, '/proyecto_cava_Noble/assets/uploads/products/vino_6a29a91eeda949.58321227.jpg', 1, '2026-06-10 18:12:46', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `selector` varchar(64) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `usuario_id`, `selector`, `token_hash`, `expires_at`, `created_at`) VALUES
(7, 1, '300de9aa727222830115658d99295fed', '$2y$10$4gLjCYtejjtXa2itFseTDuR4fppupkzYP.J/sDst4Cb6S6mrC0PG2', '2026-08-01 18:10:17', '2026-07-02 16:10:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suspicious_ips`
--

CREATE TABLE `suspicious_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `rol` varchar(20) NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `username`, `dni`, `email`, `password`, `creado_en`, `rol`) VALUES
(1, 'Administrador', 'Cava Noble', 'admin', '20123456', 'admin@cavanoble.com', '$2y$10$ueWGU20zYyB4GyL6bs0cZuqSM9L.i0AlaD0wEgeGzmy.dvFERHZly', '2026-04-16 20:35:17', 'admin'),
(2, 'Ignacio', 'Alcañiz', 'ignacioalcaniz', '40123456', 'ignaalcaniz@gmail.com', '$2y$10$sn.ddRxoWg5j3Zgz0wyvAOSc2BYYWhUon1GGq1aizjTd5wU2eXBae', '2026-07-14 23:11:28', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indices de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_email_ip` (`email`,`ip_address`,`attempted_at`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_productos_categorias` (`categoria_id`),
  ADD KEY `fk_productos_bodegas` (`bodega_id`);

--
-- Indices de la tabla `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_remember_selector` (`selector`),
  ADD KEY `idx_remember_usuario` (`usuario_id`);

--
-- Indices de la tabla `suspicious_ips`
--
ALTER TABLE `suspicious_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suspicious_ips_ip` (`ip_address`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `bodegas`
--
ALTER TABLE `bodegas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `suspicious_ips`
--
ALTER TABLE `suspicious_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_admin_logs_usuario` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `fk_pedido_items_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pedido_items_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_bodegas` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`),
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_remember_tokens_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
