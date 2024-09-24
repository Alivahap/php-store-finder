-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 08 Eyl 2024, 16:27:13
-- Sunucu sürümü: 8.3.0
-- PHP Sürümü: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `storefinder`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `blocked_points`
--

DROP TABLE IF EXISTS `blocked_points`;
CREATE TABLE IF NOT EXISTS `blocked_points` (
  `name` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `x` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `y` int NOT NULL,
  `z` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `blocked_points`
--

INSERT INTO `blocked_points` (`name`, `x`, `y`, `z`) VALUES
('F3', 'F', 3, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kiosks`
--

DROP TABLE IF EXISTS `kiosks`;
CREATE TABLE IF NOT EXISTS `kiosks` (
  `name` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `x` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `y` int NOT NULL,
  `z` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kiosks`
--

INSERT INTO `kiosks` (`name`, `x`, `y`, `z`, `id`) VALUES
('F1', 'F', 1, 1, 1),
('K5', 'K', 5, 1, 2),
('F11', 'F', 11, 1, 3);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stores`
--

DROP TABLE IF EXISTS `stores`;
CREATE TABLE IF NOT EXISTS `stores` (
  `name` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `x` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `y` int NOT NULL,
  `z` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `stores`
--

INSERT INTO `stores` (`name`, `x`, `y`, `z`, `id`) VALUES
('STB', 'D', 3, 1, 1),
('LCV', 'B', 7, 1, 2),
('BGR', 'C', 6, 2, 3),
('Merdiven', 'E', 5, 1, 4),
('Asansör', 'A', 5, 1, 5),
('KTN', 'H', 7, 1, 6);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
