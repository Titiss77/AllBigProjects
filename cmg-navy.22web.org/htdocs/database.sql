-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 15 sep. 2026 à 12:32
-- Version du serveur : 8.0.39
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sprint_metrics_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `metrics_history`
--

CREATE TABLE `metrics_history` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `gender` varchar(10) NOT NULL,
  `age` int NOT NULL DEFAULT '25',
  `height` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `neck` decimal(5,2) NOT NULL,
  `waist` decimal(5,2) NOT NULL,
  `hip` decimal(5,2) DEFAULT NULL,
  `wrist` decimal(5,2) NOT NULL DEFAULT '17.00',
  `calf` decimal(5,2) NOT NULL DEFAULT '38.00',
  `thigh` decimal(5,2) NOT NULL DEFAULT '55.00',
  `activity_multiplier` decimal(4,3) NOT NULL,
  `is_athlete` tinyint(1) NOT NULL DEFAULT '0',
  `body_fat` decimal(5,2) NOT NULL,
  `fat_mass` decimal(5,2) NOT NULL,
  `lean_mass` decimal(5,2) NOT NULL,
  `bmr` int NOT NULL,
  `tdee` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `metrics_history`
--

INSERT INTO `metrics_history` (`id`, `id_user`, `gender`, `age`, `height`, `weight`, `neck`, `waist`, `hip`, `wrist`, `calf`, `thigh`, `activity_multiplier`, `is_athlete`, `body_fat`, `fat_mass`, `lean_mass`, `bmr`, `tdee`, `created_at`) VALUES
(20, 1, 'male', 20, 172.00, 73.70, 39.00, 79.00, 95.00, 16.00, 39.50, 59.00, 1.725, 0, 16.90, 12.45, 61.25, 1693, 2920, '2026-09-15 10:09:00'),
(21, 1, 'male', 20, 172.00, 74.40, 39.00, 79.00, 95.00, 16.00, 39.50, 59.00, 1.550, 0, 17.06, 12.69, 61.71, 1703, 2640, '2026-09-09 10:10:00'),
(22, 1, 'male', 20, 170.00, 75.00, 39.00, 80.00, 95.00, 16.00, 39.50, 59.00, 1.550, 0, 18.08, 13.56, 61.44, 1697, 2631, '2026-07-29 10:10:00'),
(23, 1, 'male', 20, 170.00, 76.40, 39.00, 80.00, 95.00, 16.00, 38.00, 58.00, 1.550, 0, 18.49, 14.13, 62.27, 1715, 2658, '2026-07-27 09:49:00'),
(24, 1, 'male', 20, 170.00, 75.10, 39.00, 81.00, 96.00, 16.00, 38.00, 57.00, 1.550, 0, 18.55, 13.93, 61.17, 1691, 2621, '2026-07-25 10:11:00'),
(25, 1, 'male', 19, 170.00, 77.80, 39.00, 92.00, 97.00, 16.00, 38.00, 56.00, 1.550, 0, 21.96, 17.09, 60.71, 1681, 2606, '2026-05-07 10:11:00'),
(26, 1, 'male', 19, 170.00, 79.30, 39.00, 97.00, 98.00, 16.00, 38.00, 55.00, 1.550, 0, 23.48, 18.62, 60.68, 1681, 2605, '2026-01-08 11:11:00'),
(27, 1, 'male', 19, 170.00, 79.90, 39.00, 100.00, 99.00, 16.00, 38.00, 55.00, 1.375, 0, 24.28, 19.40, 60.50, 1677, 2306, '2025-11-16 11:11:00'),
(28, 1, 'male', 19, 170.00, 80.00, 39.00, 107.00, 100.00, 16.00, 37.00, 55.00, 1.200, 0, 25.76, 20.60, 59.40, 1653, 1984, '2025-10-01 10:10:00'),
(29, 1, 'male', 19, 170.00, 86.60, 39.00, 112.00, 100.00, 16.00, 37.00, 55.00, 1.200, 0, 28.06, 24.30, 62.30, 1716, 2059, '2025-09-25 10:10:00');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'mathisfrances11@gmail.com', '$2y$10$AjeXQVTeEn5HCDfH1pi.T.KxCFdyx8.kGI5pPirN46XSQ5yx50CUa', '2026-07-24 11:26:49');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `metrics_history`
--
ALTER TABLE `metrics_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `metrics_history`
--
ALTER TABLE `metrics_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `metrics_history`
--
ALTER TABLE `metrics_history`
  ADD CONSTRAINT `metrics_history_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
