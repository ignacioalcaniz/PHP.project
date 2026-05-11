-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-05-2026 a las 22:05:40
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
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `pais`, `region`, `bodega`, `cepa`, `anada`, `stock`, `imagen`, `destacado`, `creado_en`) VALUES
(1, 'Malbec Reserva', 'Vino argentino elegante, con notas frutadas y gran estructura.', 18500.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 12, 'https://via.placeholder.com/300x350?text=Malbec+Reserva', 1, '2026-04-16 20:10:18'),
(2, 'Cabernet Sauvignon', 'Vino intenso del Valle Central con final persistente.', 22900.00, 'Chile', 'Valle Central', 'Concha y Toro', 'Cabernet Sauvignon', '2021', 8, 'https://via.placeholder.com/300x350?text=Cabernet+Sauvignon', 1, '2026-04-16 20:10:18'),
(3, 'Chardonnay Premium', 'Blanco sofisticado, equilibrado y fresco.', 29400.00, 'Francia', 'Borgoña', 'Louis Jadot', 'Chardonnay', '2020', 5, 'https://via.placeholder.com/300x350?text=Chardonnay+Premium', 1, '2026-04-16 20:10:18'),
(4, 'Sangiovese Classico', 'Vino italiano con gran personalidad y perfil tradicional.', 31200.00, 'Italia', 'Toscana', 'Ruffino', 'Sangiovese', '2021', 10, 'https://via.placeholder.com/300x350?text=Sangiovese+Classico', 0, '2026-04-16 20:10:18'),
(5, 'Catena Malbec', 'Malbec elegante con notas de frutos negros y final persistente.', 18500.00, 'Argentina', 'Mendoza', 'Catena Zapata', 'Malbec', '2022', 15, 'https://via.placeholder.com/300x350?text=Catena+Malbec', 1, '2026-04-22 16:40:09'),
(6, 'Norton Reserva Cabernet', 'Cabernet Sauvignon intenso y estructurado.', 16900.00, 'Argentina', 'Mendoza', 'Norton', 'Cabernet Sauvignon', '2021', 12, 'https://via.placeholder.com/300x350?text=Norton+Cabernet', 0, '2026-04-22 16:40:09'),
(7, 'El Esteco Torrontés', 'Blanco aromático típico del norte argentino.', 15200.00, 'Argentina', 'Salta', 'El Esteco', 'Torrontés', '2023', 10, 'https://via.placeholder.com/300x350?text=El+Esteco+Torrontes', 1, '2026-04-22 16:40:09'),
(8, 'Bodega Chacra Pinot Noir', 'Pinot Noir patagónico fino y elegante.', 32500.00, 'Argentina', 'Patagonia', 'Bodega Chacra', 'Pinot Noir', '2021', 6, 'https://via.placeholder.com/300x350?text=Chacra+Pinot+Noir', 1, '2026-04-22 16:40:09'),
(9, 'Luigi Bosca Malbec', 'Malbec clásico argentino con gran balance.', 19800.00, 'Argentina', 'Mendoza', 'Luigi Bosca', 'Malbec', '2022', 11, 'https://via.placeholder.com/300x350?text=Luigi+Bosca+Malbec', 0, '2026-04-22 16:40:09'),
(10, 'Zuccardi Q Cabernet Franc', 'Cabernet Franc moderno y sofisticado.', 23900.00, 'Argentina', 'Mendoza', 'Zuccardi', 'Cabernet Franc', '2021', 8, 'https://via.placeholder.com/300x350?text=Zuccardi+Cabernet+Franc', 1, '2026-04-22 16:40:09'),
(11, 'Concha y Toro Reservado', 'Cabernet chileno suave y accesible.', 14900.00, 'Chile', 'Valle Central', 'Concha y Toro', 'Cabernet Sauvignon', '2022', 20, 'https://via.placeholder.com/300x350?text=Concha+y+Toro', 0, '2026-04-22 16:40:09'),
(12, 'Montes Alpha Carménère', 'Carménère intenso con especias y fruta madura.', 24500.00, 'Chile', 'Colchagua', 'Montes', 'Carménère', '2021', 9, 'https://via.placeholder.com/300x350?text=Montes+Alpha', 1, '2026-04-22 16:40:09'),
(13, 'Louis Jadot Chardonnay', 'Blanco francés refinado y equilibrado.', 28900.00, 'Francia', 'Borgoña', 'Louis Jadot', 'Chardonnay', '2020', 7, 'https://via.placeholder.com/300x350?text=Louis+Jadot', 1, '2026-04-22 16:40:09'),
(14, 'Bordeaux Rouge Classic', 'Tinto clásico francés con perfil elegante.', 31900.00, 'Francia', 'Bordeaux', 'Maison Bordeaux', 'Blend', '2019', 5, 'https://via.placeholder.com/300x350?text=Bordeaux+Rouge', 0, '2026-04-22 16:40:09'),
(15, 'Ruffino Chianti', 'Chianti tradicional italiano con gran personalidad.', 26900.00, 'Italia', 'Toscana', 'Ruffino', 'Sangiovese', '2021', 10, 'https://via.placeholder.com/300x350?text=Ruffino+Chianti', 1, '2026-04-22 16:40:09'),
(16, 'Villa Antinori Rosso', 'Tinto premium italiano moderno y complejo.', 35500.00, 'Italia', 'Toscana', 'Antinori', 'Blend', '2020', 4, 'https://via.placeholder.com/300x350?text=Antinori+Rosso', 1, '2026-04-22 16:40:09'),
(17, 'Marqués de Riscal Reserva', 'Rioja tradicional con crianza elegante.', 27900.00, 'España', 'Rioja', 'Marqués de Riscal', 'Tempranillo', '2020', 8, 'https://via.placeholder.com/300x350?text=Marques+de+Riscal', 1, '2026-04-22 16:40:09'),
(18, 'Torres Sangre de Toro', 'Tinto español clásico, frutado y amable.', 18900.00, 'España', 'Cataluña', 'Torres', 'Blend', '2021', 13, 'https://via.placeholder.com/300x350?text=Torres+Sangre+de+Toro', 0, '2026-04-22 16:40:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `rol` varchar(20) NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `creado_en`, `rol`) VALUES
(1, 'ignacio', 'alcaniz', 'ignaalcaniz@gmail.com', '$2y$10$R1IPhlS991nHW3o.WD/28ObV.qFSfj472FqjZQ3Rxc7YAKFQ9usYa', '2026-04-16 20:35:17', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
