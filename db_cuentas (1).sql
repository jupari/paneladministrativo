-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2025 a las 16:53:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_cuentas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `pais_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id`, `nombre`, `departamento_id`, `pais_id`, `created_at`, `updated_at`, `active`) VALUES
(1, 'Cali', 76, 1, '2025-01-14 19:21:43', NULL, 1),
(3, 'Tulua', 76, 1, '2025-01-14 19:21:43', NULL, 1),
(4, 'Cartago', 76, 1, '2025-01-14 19:21:43', NULL, 1),
(5, 'Buga', 76, 1, '2025-01-14 19:21:43', NULL, 1),
(6, 'Cerrito', 76, 1, '2025-01-14 19:21:43', NULL, 1),
(7, 'Guacarí', 76, 1, '2025-01-14 19:21:43', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `terceros`
--

CREATE TABLE `terceros` (
  `id` int(11) NOT NULL,
  `tercerotipo_id` int(11) NOT NULL DEFAULT 1,
  `tipoidentificacion_id` int(11) NOT NULL COMMENT 'llave foranea a tipos de identificación',
  `identificacion` varchar(50) NOT NULL,
  `dv` varchar(1) DEFAULT NULL,
  `tipopersona_id` int(11) NOT NULL COMMENT 'llave foranea con tipo de identificacion',
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `nombre_establecimiento` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `correo_fe` varchar(50) DEFAULT NULL COMMENT 'correo de factura electronica',
  `ciudad_id` int(11) NOT NULL COMMENT 'llave foranea a la tabla de ciudades',
  `direccion` varchar(50) NOT NULL,
  `vendedor` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `terceros`
--

INSERT INTO `terceros` (`id`, `tercerotipo_id`, `tipoidentificacion_id`, `identificacion`, `dv`, `tipopersona_id`, `nombres`, `apellidos`, `nombre_establecimiento`, `telefono`, `celular`, `correo`, `correo_fe`, `ciudad_id`, `direccion`, `vendedor`, `created_at`, `updated_at`, `user_id`) VALUES
(1, 1, 1, '94531852', '1', 1, 'Juan', 'Rios', 'juanesssss', '3174925199', '3174925199', 'juan@gmail.com', 'juan@gmail.com', 2, 'Cra 1A 66 20', 'vendedor1', '2025-01-14 20:00:33', '2025-01-14 20:27:19', 1),
(2, 1, 1, '1130648031', '1', 1, 'Viviana', 'Londoño', 'agrovelez', '3008260528', '3008260528', 'vivi@gmail.com', 'vivi@gmail.com', 1, 'Cra 1A 66 20', 'vendedor 1', '2025-01-14 22:54:02', NULL, 1),
(4, 1, 1, '33333151', '4', 1, 'Zoe', 'Rios', 'Zoeeeesss', '445235', '542352352', 'zoe@gmail.com', 'zoe@gmail.com', 1, 'Dia 16 # 72 a 83', 'vendedor 2', '2025-01-15 02:05:16', NULL, 1),
(5, 1, 2, '99989798', NULL, 1, 'sdafdsakfjk3333', 'sdakfkas2222', 'sdfmmmckmdmf', '23242345000', '2342423000', 'otros@gmail.com', 'otros@gmail.com', 1, 'Dia 16 # 72 a 83333', 'vendedor 3222', '2025-01-15 02:10:17', '2025-01-15 02:14:15', 1),
(6, 1, 1, '113000813143', NULL, 1, 'Lorenzo mario', 'Lamas', 'renegado', '3174925199', '3174925199', 'lorenzo@gmail.com', 'lorenzo@gmail.com', 1, 'cra 50 25-36', 'vendedor 6', '2025-01-15 15:04:02', '2025-01-15 15:45:05', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `terceros_sucursales`
--

CREATE TABLE `terceros_sucursales` (
  `id` int(11) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ext` varchar(10) DEFAULT NULL,
  `cargo` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `tercero_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `terceros_sucursales`
--

INSERT INTO `terceros_sucursales` (`id`, `nombres`, `apellidos`, `correo`, `celular`, `telefono`, `ext`, `cargo`, `created_at`, `updated_at`, `user_id`, `tercero_id`) VALUES
(11, 'roberto', 'Noreña', 'roberto@gmail.com', '5565565656', '3174925199', NULL, 'Gerente', '2025-01-15 01:24:04', NULL, NULL, 2),
(17, 'Juan', 'Rios', 'jt@gmail.com', '5565565656', '3174925199', NULL, 'Gerente', '2025-01-15 02:05:17', NULL, NULL, 4),
(19, 'pepe', 'gonzales', 'pepe@gmail.com', '12342432', '34124312', NULL, 'Aux', '2025-01-15 02:10:57', NULL, NULL, 5),
(20, 'Contacto1', 'contact', 'adsffs@gmail.com', '3005862834', NULL, '33', 'Aux', '2025-01-15 15:04:33', '2025-01-15 15:43:51', NULL, 6),
(21, 'contacto tres', 'contacto', 'contacto3@gmail.com', '3174925199', NULL, '369', 'Aux', '2025-01-15 15:10:32', '2025-01-15 15:44:41', NULL, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `terceros_tipos`
--

CREATE TABLE `terceros_tipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `terceros_tipos`
--

INSERT INTO `terceros_tipos` (`id`, `nombre`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Cliente', 1, '2025-01-14 17:57:13', NULL),
(2, 'Proveedor', 1, '2025-01-14 17:57:13', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_identificacion`
--

CREATE TABLE `tipo_identificacion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_identificacion`
--

INSERT INTO `tipo_identificacion` (`id`, `nombre`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Cédula', 1, '2025-01-14 17:55:12', NULL),
(2, 'NIT', 1, '2025-01-14 17:55:12', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_persona`
--

CREATE TABLE `tipo_persona` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_persona`
--

INSERT INTO `tipo_persona` (`id`, `nombre`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Natural', 1, '2025-01-14 17:55:39', NULL),
(2, 'Jurídica', 1, '2025-01-14 17:55:39', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `terceros`
--
ALTER TABLE `terceros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `terceros_sucursales`
--
ALTER TABLE `terceros_sucursales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `terceros_tipos`
--
ALTER TABLE `terceros_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_identificacion`
--
ALTER TABLE `tipo_identificacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_persona`
--
ALTER TABLE `tipo_persona`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `terceros`
--
ALTER TABLE `terceros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `terceros_sucursales`
--
ALTER TABLE `terceros_sucursales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `terceros_tipos`
--
ALTER TABLE `terceros_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_identificacion`
--
ALTER TABLE `tipo_identificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_persona`
--
ALTER TABLE `tipo_persona`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
