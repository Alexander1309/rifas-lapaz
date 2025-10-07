-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-10-2025 a las 17:05:34
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
-- Base de datos: `u715569272_rifalapaz`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updateAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deletedBy` int(11) DEFAULT NULL,
  `deletedAt` timestamp NULL DEFAULT NULL,
  `status` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `role`
--

INSERT INTO `role` (`id`, `name`, `description`, `createdBy`, `createdAt`, `updatedBy`, `updateAt`, `deletedBy`, `deletedAt`, `status`) VALUES
(1, 'root', 'Accesos a todas las vistas', 1, '2025-09-25 14:17:32', NULL, NULL, NULL, NULL, b'1'),
(2, 'admin', 'Accesos a vistas de llenado', 1, '2025-09-25 14:18:06', NULL, NULL, NULL, NULL, b'1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `idRol` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deletedBy` int(11) DEFAULT NULL,
  `deletedAt` timestamp NULL DEFAULT NULL,
  `status` bit(1) NOT NULL DEFAULT b'1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `idRol`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `deletedBy`, `deletedAt`, `status`) VALUES
(1, 'root', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 1, 1, '2025-09-25 14:20:52', 1, '2025-09-30 14:38:26', NULL, NULL, b'1'),
(2, 'Alejandro', '74f48bd28e36f02ce72eacd6528b5bd7cdb4fd4544678021b0c7e0f53828dec6', 1, 1, '2025-10-01 20:35:48', NULL, NULL, NULL, NULL, b'1'),
(3, 'Mayra', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 2, 1, '2025-10-01 20:36:21', NULL, NULL, NULL, NULL, b'1');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_role_created_by` (`createdBy`),
  ADD KEY `idx_role_updated_by` (`updatedBy`),
  ADD KEY `idx_role_deleted_by` (`deletedBy`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_role` (`idRol`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_updated_by` (`updatedBy`),
  ADD KEY `idx_deleted_by` (`deletedBy`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `idx_role_created_by` FOREIGN KEY (`createdBy`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `idx_role_deleted_by` FOREIGN KEY (`deletedBy`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `idx_role_updated_by` FOREIGN KEY (`updatedBy`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `idx_created_by` FOREIGN KEY (`createdBy`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `idx_deleted_by` FOREIGN KEY (`deletedBy`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `idx_role` FOREIGN KEY (`idRol`) REFERENCES `role` (`id`),
  ADD CONSTRAINT `idx_updated_by` FOREIGN KEY (`updatedBy`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
