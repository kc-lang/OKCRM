-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 01 juil. 2026 à 11:16
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `okcrm`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `Cid` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Uid` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Cid`),
  KEY `Uid` (`Uid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`Cid`, `nom`, `email`, `tel`, `Uid`, `created_at`) VALUES
(5, 'CHARLIE', 'CHARLIE@GMAIL.COM', '+237 679005645', 6, '2026-06-23 14:59:42'),
(2, 'muscade', 'mus@gmail.com', '67809345', 2, '2026-06-17 13:19:15'),
(3, 'olivia', 'livia@gmail.com', '699999999', 2, '2026-06-18 17:06:25'),
(4, 'caroline', 'caro@okcrm.com', '478964289', 2, '2026-06-22 15:28:22'),
(6, 'Etame', 'etame@gmail.com', '', 4, '2026-06-24 15:37:27'),
(7, 'fabrice', 'fabio@gmail.com', '1234567890', 8, '2026-06-30 08:53:47');

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

DROP TABLE IF EXISTS `evenement`;
CREATE TABLE IF NOT EXISTS `evenement` (
  `Eid` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_heure` datetime NOT NULL,
  `Pid` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Eid`),
  KEY `Pid` (`Pid`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evenement`
--

INSERT INTO `evenement` (`Eid`, `titre`, `date_heure`, `Pid`, `created_at`) VALUES
(1, 'Achat d\'une villa', '2026-06-19 11:48:00', 3, '2026-06-19 10:48:21'),
(2, 'Achat d\'une villa', '2026-06-19 11:50:00', 3, '2026-06-19 10:48:56'),
(3, '12H', '2026-06-19 12:04:00', 3, '2026-06-19 11:02:31'),
(4, 'SITE WEB', '2026-06-19 12:15:00', 2, '2026-06-19 11:11:29'),
(5, 'SITE WEB', '2026-06-19 12:14:00', 3, '2026-06-19 11:13:58'),
(6, 'WD', '2026-06-23 16:14:00', 5, '2026-06-23 15:12:23'),
(7, 'Mise au point des besoins', '2026-06-26 09:00:00', 6, '2026-06-24 15:42:57'),
(8, 'REUNION AVEC MLLE LORANCE', '2026-06-25 09:00:00', NULL, '2026-06-30 14:34:50'),
(9, 'POWERPOINT, Portfolio; deploiement; kanban', '2026-07-01 10:05:00', NULL, '2026-06-30 16:51:16');

-- --------------------------------------------------------

--
-- Structure de la table `facture`
--

DROP TABLE IF EXISTS `facture`;
CREATE TABLE IF NOT EXISTS `facture` (
  `Fid` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(12,2) NOT NULL,
  `statut_paiement` enum('en_attente','payee','annulee') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `date` date NOT NULL,
  `Pid` int NOT NULL,
  `Cid` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Fid`),
  KEY `Pid` (`Pid`),
  KEY `Cid` (`Cid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `facture`
--

INSERT INTO `facture` (`Fid`, `montant`, `statut_paiement`, `date`, `Pid`, `Cid`, `created_at`) VALUES
(1, 2000000.00, 'payee', '2026-06-17', 2, 2, '2026-06-17 13:22:41'),
(2, 1500.00, 'payee', '2026-06-22', 4, 4, '2026-06-22 15:34:26'),
(3, 990554.00, 'payee', '2026-06-30', 5, 5, '2026-06-23 15:10:53'),
(4, 19000.00, 'payee', '2026-06-24', 6, 6, '2026-06-24 15:39:01'),
(5, 200000.00, 'payee', '2026-06-24', 7, 7, '2026-06-30 08:54:49');

-- --------------------------------------------------------

--
-- Structure de la table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE IF NOT EXISTS `portfolio` (
  `Rid` int NOT NULL AUTO_INCREMENT,
  `Uid` int NOT NULL,
  `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `lien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tel` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Rid`),
  KEY `Uid` (`Uid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `portfolio`
--

INSERT INTO `portfolio` (`Rid`, `Uid`, `titre`, `description`, `lien`, `tel`, `photo_path`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 8, 'Déploiment solution SIEM', 'Nous faisons dans les solutions siem pour differentes interface', '', '690514615', '/uploads/portfolio/portfolio_8_1782898160.png', 0, '2026-07-01 09:29:20', '2026-07-01 09:29:20'),
(2, 8, 'realisation', 'Voici quelques propositions en 4 mots :\r\n\r\nProfessionnel indépendant au service\r\nTravailleur indépendant à son compte (5 mots si on compte \"à\")\r\nPrestataire de services indépendant\r\nProfessionnel exerçant en indépendant\r\nExpert travaillant en autonomie\r\nIndépendant offrant ses services', '', '678905478', '/uploads/portfolio/portfolio_8_1782903777.png', 0, '2026-07-01 11:02:57', '2026-07-01 11:02:57'),
(3, 7, 'INSTALLATION NAVIGATEURS', 'MOZILLA, MS EDGE, GOOGLE CHROME', '', 'kpkessy66@gmail.com', '/uploads/portfolio/portfolio_7_1782903925.png', 0, '2026-07-01 11:05:25', '2026-07-01 11:06:27');

-- --------------------------------------------------------

--
-- Structure de la table `projet`
--

DROP TABLE IF EXISTS `projet`;
CREATE TABLE IF NOT EXISTS `projet` (
  `Pid` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('en_attente','en_cours','termine','annule') COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `budget` decimal(12,2) DEFAULT '0.00',
  `Cid` int NOT NULL,
  `Uid` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Pid`),
  KEY `Cid` (`Cid`),
  KEY `Uid` (`Uid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `projet`
--

INSERT INTO `projet` (`Pid`, `titre`, `statut`, `budget`, `Cid`, `Uid`, `created_at`) VALUES
(3, 'Achat d\'une villa', 'en_cours', 10000000.00, 3, 2, '2026-06-18 17:08:23'),
(2, 'SITE WEB', 'en_attente', 2000000.00, 2, 2, '2026-06-17 13:20:56'),
(4, 'MANGER', 'en_cours', 2000.00, 4, 2, '2026-06-22 15:29:21'),
(5, 'INSTALLATION DE KIT STARLINK V3 PLUS LIVRAISON DE DIVERSE OUTILS INFORMATIQUE', 'en_attente', 990550.00, 5, 6, '2026-06-23 15:01:16'),
(6, 'dev01', 'en_attente', 100000.00, 6, 4, '2026-06-24 15:38:05'),
(7, 'kit', 'en_cours', 2000000.00, 7, 8, '2026-06-30 08:54:10');

-- --------------------------------------------------------

--
-- Structure de la table `tache`
--

DROP TABLE IF EXISTS `tache`;
CREATE TABLE IF NOT EXISTS `tache` (
  `Tid` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('a_faire','en_cours','termine') COLLATE utf8mb4_unicode_ci DEFAULT 'a_faire',
  `Pid` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Tid`),
  KEY `Pid` (`Pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tache`
--

INSERT INTO `tache` (`Tid`, `titre`, `statut`, `Pid`, `created_at`) VALUES
(1, 'huhop', 'termine', 5, '2026-06-24 11:27:21'),
(2, 'devoir', 'a_faire', 5, '2026-06-24 11:29:09'),
(3, 'Recueil des besoins', 'a_faire', 6, '2026-06-24 15:38:31'),
(6, '?BJ', 'a_faire', NULL, '2026-06-30 14:34:06');

-- --------------------------------------------------------

--
-- Structure de la table `user_`
--

DROP TABLE IF EXISTS `user_`;
CREATE TABLE IF NOT EXISTS `user_` (
  `Uid` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Uid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_`
--

INSERT INTO `user_` (`Uid`, `nom`, `email`, `Mot_de_passe`, `created_at`, `logo_path`) VALUES
(1, 'Freelance', 'admin@okcrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-12 11:56:09', NULL),
(2, 'elo', 'eli@okcrm.com', '$2y$10$2N1JifY1XKDjoxw0Z5IPauMiDP4/ZkEAz9PdZaw27i3.7Zq.CRIHO', '2026-06-12 12:05:01', NULL),
(3, 'elo', 'kessy@okcrm.com', '$2y$10$/0e9iJ.VTCWatoXzZLqZY.AVbq6VrA96lroqb6tEnw7aySQggUDG.', '2026-06-17 12:38:01', NULL),
(4, 'bi boue', 'bibouechristian@gmail.com', '$2y$10$nkFCZ5AyWxhfOJninbznU.7bVL7dTJKyY9piD5LAlUMjSNN/eJope', '2026-06-22 17:16:08', NULL),
(5, 'caroline', 'caro@okcrm.com', '$2y$10$.wZi3/XfqlSnaS9wcz4cpegfquYLTX7QXZmqpCpCtUklXsYgb5RgC', '2026-06-22 17:19:06', NULL),
(6, 'PABLO ESCOBAR', 'pablo@crm.com', '$2y$10$.r90Fsca9hCoyHOZFAzb0O0z7X4a9Ab7muu5IqBBHt3xESpftqoTC', '2026-06-23 14:58:57', NULL),
(7, 'eloise', 'eloise@mail.com', '$2y$10$isktvNkoheIo7w4UNBjkQ.lYQdBxqqD9ldoMLy4hlMR9ZlOPSXPta', '2026-06-24 15:05:12', NULL),
(8, 'jolie', 'joli@crm.com', '$2y$10$W8jzrIziZkQ4WdlCqb7U6ucMB2C5xxlF5MMkUfw2HqOo33tccqubS', '2026-06-24 15:17:31', '/uploads/logos/logo_8_1782828778.png'),
(9, 'biboue', 'biboue@gmail.com', '$2y$10$Bxx94vNjsiM5eUjuN38vBe.i.Gv6jaeCsM7evv0gZA5YEiJ4dlhjm', '2026-06-24 15:35:34', NULL),
(10, 'kalies', 'Leslie@gmail.com', '$2y$10$bGU3mNqjDEdFfmmB/QLfCO7jkuFQOvbBNp6I3vjq8UybO3PnlAFba', '2026-06-30 11:20:39', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
