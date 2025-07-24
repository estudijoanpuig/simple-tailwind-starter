-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Temps de generació: 24-07-2025 a les 02:41:25
-- Versió del servidor: 8.4.4
-- Versió de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dades: `autonomo_contabilidad`
--

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_categoria_productos`
--

CREATE TABLE `wp_contabilidad_categoria_productos` (
  `id` int NOT NULL,
  `nombre_categoria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_clientes`
--

CREATE TABLE `wp_contabilidad_clientes` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_compras`
--

CREATE TABLE `wp_contabilidad_compras` (
  `id` mediumint NOT NULL,
  `fecha` date NOT NULL,
  `proveedor_id` mediumint NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_detalles_compra`
--

CREATE TABLE `wp_contabilidad_detalles_compra` (
  `id` mediumint NOT NULL,
  `compra_id` mediumint NOT NULL,
  `producto_id` mediumint NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `iva_porcentaje` decimal(5,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_detalles_venta`
--

CREATE TABLE `wp_contabilidad_detalles_venta` (
  `id` mediumint NOT NULL,
  `venta_id` mediumint NOT NULL,
  `producto_id` mediumint NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_empleados`
--

CREATE TABLE `wp_contabilidad_empleados` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_import_logs`
--

CREATE TABLE `wp_contabilidad_import_logs` (
  `id` int NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `import_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_productos`
--

CREATE TABLE `wp_contabilidad_productos` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_categoria_producto` int NOT NULL DEFAULT '7',
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `protocol` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_proveedores`
--

CREATE TABLE `wp_contabilidad_proveedores` (
  `id` mediumint NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `direccion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `wp_contabilidad_ventas`
--

CREATE TABLE `wp_contabilidad_ventas` (
  `id` mediumint NOT NULL,
  `fecha` date NOT NULL,
  `cliente_id` mediumint NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva_porcentaje` decimal(5,2) NOT NULL,
  `iva_monto` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `empleado_id` mediumint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Índexs per a les taules bolcades
--

--
-- Índexs per a la taula `wp_contabilidad_categoria_productos`
--
ALTER TABLE `wp_contabilidad_categoria_productos`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_clientes`
--
ALTER TABLE `wp_contabilidad_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_compras`
--
ALTER TABLE `wp_contabilidad_compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_compra` (`fecha`,`proveedor_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Índexs per a la taula `wp_contabilidad_detalles_compra`
--
ALTER TABLE `wp_contabilidad_detalles_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Índexs per a la taula `wp_contabilidad_detalles_venta`
--
ALTER TABLE `wp_contabilidad_detalles_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Índexs per a la taula `wp_contabilidad_empleados`
--
ALTER TABLE `wp_contabilidad_empleados`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_import_logs`
--
ALTER TABLE `wp_contabilidad_import_logs`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_productos`
--
ALTER TABLE `wp_contabilidad_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria_producto` (`id_categoria_producto`);

--
-- Índexs per a la taula `wp_contabilidad_proveedores`
--
ALTER TABLE `wp_contabilidad_proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Índexs per a la taula `wp_contabilidad_ventas`
--
ALTER TABLE `wp_contabilidad_ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_venta` (`fecha`,`cliente_id`,`empleado_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_categoria_productos`
--
ALTER TABLE `wp_contabilidad_categoria_productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_clientes`
--
ALTER TABLE `wp_contabilidad_clientes`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_compras`
--
ALTER TABLE `wp_contabilidad_compras`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_detalles_compra`
--
ALTER TABLE `wp_contabilidad_detalles_compra`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_detalles_venta`
--
ALTER TABLE `wp_contabilidad_detalles_venta`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_empleados`
--
ALTER TABLE `wp_contabilidad_empleados`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_import_logs`
--
ALTER TABLE `wp_contabilidad_import_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_productos`
--
ALTER TABLE `wp_contabilidad_productos`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_proveedores`
--
ALTER TABLE `wp_contabilidad_proveedores`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la taula `wp_contabilidad_ventas`
--
ALTER TABLE `wp_contabilidad_ventas`
  MODIFY `id` mediumint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
