-- --------------------------------------------------------
-- Hôte :                        localhost
-- Version du serveur:           10.4.32-MariaDB - mariadb.org binary distribution
-- SE du serveur:                Win64
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Export de la structure de la base pour quincaillerie
CREATE DATABASE IF NOT EXISTS `quincaillerie` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `quincaillerie`;


-- Export de la structure de table quincaillerie. achats
DROP TABLE IF EXISTS `achats`;
CREATE TABLE IF NOT EXISTS `achats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fournisseur_id` int(11) NOT NULL,
  `date_achat` datetime NOT NULL,
  `totalHT` decimal(15,2) NOT NULL,
  `taxe` decimal(15,2) NOT NULL,
  `remise` decimal(15,2) NOT NULL,
  `totalTTC` decimal(15,2) NOT NULL,
  `montant_verse` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reste_a_payer` decimal(15,2) NOT NULL DEFAULT 0.00,
  `type_achat` enum('Comptant','Crédit') DEFAULT 'Comptant',
  `mode_paiement` enum('Espèces','Virement','Chèque','Mobile Money') DEFAULT 'Espèces',
  `statut` enum('valide','annule','credit','paye') DEFAULT 'valide',
  `annule` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fournisseur_id` (`fournisseur_id`),
  CONSTRAINT `achats_ibfk_1` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.achats : ~1 rows (environ)
/*!40000 ALTER TABLE `achats` DISABLE KEYS */;
INSERT INTO `achats` (`id`, `fournisseur_id`, `date_achat`, `totalHT`, `taxe`, `remise`, `totalTTC`, `montant_verse`, `reste_a_payer`, `type_achat`, `mode_paiement`, `statut`, `annule`, `created_at`) VALUES
	(1, 1, '2025-09-25 16:17:00', 14000.00, 2520.00, 0.00, 16520.00, 0.00, 16520.00, 'Comptant', 'Espèces', '', 0, '2025-09-25 14:18:20');
/*!40000 ALTER TABLE `achats` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. achats_details
DROP TABLE IF EXISTS `achats_details`;
CREATE TABLE IF NOT EXISTS `achats_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `achat_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(15,2) DEFAULT NULL,
  `depot_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `achat_id` (`achat_id`),
  KEY `produit_id` (`produit_id`),
  KEY `FK_achats_details_depots` (`depot_id`),
  CONSTRAINT `FK_achats_details_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`),
  CONSTRAINT `achats_details_ibfk_1` FOREIGN KEY (`achat_id`) REFERENCES `achats` (`id`),
  CONSTRAINT `achats_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.achats_details : ~1 rows (environ)
/*!40000 ALTER TABLE `achats_details` DISABLE KEYS */;
INSERT INTO `achats_details` (`id`, `achat_id`, `produit_id`, `quantite`, `prix_unitaire`, `depot_id`) VALUES
	(1, 1, 1, 5, 2800.00, 1);
/*!40000 ALTER TABLE `achats_details` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.categories : ~1 rows (environ)
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `nom`, `description`, `created_at`) VALUES
	(1, 'Matériel de construction', 'Matériaux pour les bâtiments', '2025-09-20 13:49:34');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. clients
DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `idClient` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `exonere` tinyint(1) DEFAULT 0,
  `type` enum('Particulier','Entreprise','Passager') DEFAULT 'Particulier',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idClient`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.clients : ~1 rows (environ)
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` (`idClient`, `nom`, `telephone`, `email`, `adresse`, `exonere`, `type`, `created_at`) VALUES
	(1, 'Modou Diop', '772583610', 'modou@gmail.com', 'Grand Thiès', 1, 'Particulier', '2025-09-23 12:34:28');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. depots
DROP TABLE IF EXISTS `depots`;
CREATE TABLE IF NOT EXISTS `depots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.depots : ~1 rows (environ)
/*!40000 ALTER TABLE `depots` DISABLE KEYS */;
INSERT INTO `depots` (`id`, `nom`, `description`, `created_at`) VALUES
	(1, 'Dépôt 1', 'dépôt n° 1', '2025-09-23 12:20:10');
/*!40000 ALTER TABLE `depots` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. devices
DROP TABLE IF EXISTS `devices`;
CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.devices : ~0 rows (environ)
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. device_sync
DROP TABLE IF EXISTS `device_sync`;
CREATE TABLE IF NOT EXISTS `device_sync` (
  `device_id` varchar(100) NOT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`info`)),
  PRIMARY KEY (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.device_sync : ~0 rows (environ)
/*!40000 ALTER TABLE `device_sync` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_sync` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. fournisseurs
DROP TABLE IF EXISTS `fournisseurs`;
CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `exonere` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.fournisseurs : ~1 rows (environ)
/*!40000 ALTER TABLE `fournisseurs` DISABLE KEYS */;
INSERT INTO `fournisseurs` (`id`, `nom`, `telephone`, `email`, `adresse`, `exonere`, `created_at`) VALUES
	(1, 'Sococim', '339521014', 'sococim@gmail.com', 'Rufisque', 0, '2025-09-25 12:21:25');
/*!40000 ALTER TABLE `fournisseurs` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. mouvements_stock
DROP TABLE IF EXISTS `mouvements_stock`;
CREATE TABLE IF NOT EXISTS `mouvements_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_source_id` int(11) DEFAULT NULL,
  `depot_dest_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `type` enum('achat','vente','retour','transfert','correction') NOT NULL,
  `reference_table` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `date_mouvement` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `depot_source_id` (`depot_source_id`),
  KEY `depot_dest_id` (`depot_dest_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `idx_mouv_prod` (`produit_id`),
  CONSTRAINT `mouvements_stock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `mouvements_stock_ibfk_2` FOREIGN KEY (`depot_source_id`) REFERENCES `depots` (`id`),
  CONSTRAINT `mouvements_stock_ibfk_3` FOREIGN KEY (`depot_dest_id`) REFERENCES `depots` (`id`),
  CONSTRAINT `mouvements_stock_ibfk_4` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.mouvements_stock : ~4 rows (environ)
/*!40000 ALTER TABLE `mouvements_stock` DISABLE KEYS */;
INSERT INTO `mouvements_stock` (`id`, `produit_id`, `depot_source_id`, `depot_dest_id`, `quantite`, `type`, `reference_table`, `reference_id`, `utilisateur_id`, `note`, `date_mouvement`) VALUES
	(1, 1, 1, NULL, 1, 'vente', 'ventes', 1, NULL, NULL, '2025-09-23 23:02:25'),
	(2, 1, 1, NULL, 1, 'vente', 'ventes', 2, NULL, NULL, '2025-09-24 22:16:03'),
	(3, 1, NULL, 1, 5, 'achat', 'achats', 1, NULL, NULL, '2025-09-25 14:18:20'),
	(4, 1, 1, NULL, 1, 'vente', 'ventes', 3, NULL, NULL, '2025-09-25 15:25:03');
/*!40000 ALTER TABLE `mouvements_stock` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. produits
DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `prix_achat` decimal(15,2) DEFAULT NULL,
  `prix_vente` decimal(15,2) DEFAULT NULL,
  `stock_total` int(11) DEFAULT 0,
  `seuil_alerte` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `categorie_id` int(11) DEFAULT NULL,
  `depot_id` int(11) DEFAULT NULL,
  `image` varchar(50) DEFAULT NULL,
  `unite_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `categorie_id` (`categorie_id`),
  KEY `FK_produits_depots` (`depot_id`),
  CONSTRAINT `FK_produits_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`),
  CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.produits : ~1 rows (environ)
/*!40000 ALTER TABLE `produits` DISABLE KEYS */;
INSERT INTO `produits` (`id`, `nom`, `reference`, `prix_achat`, `prix_vente`, `stock_total`, `seuil_alerte`, `created_at`, `categorie_id`, `depot_id`, `image`, `unite_id`) VALUES
	(1, 'Ciment Sococim', '45R', 2800.00, 3200.00, 1, 0, '2025-09-20 13:57:38', 1, 1, NULL, NULL);
/*!40000 ALTER TABLE `produits` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. retours
DROP TABLE IF EXISTS `retours`;
CREATE TABLE IF NOT EXISTS `retours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `date_retour` datetime NOT NULL,
  `raison` text NOT NULL,
  `depot_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `depot_id` (`depot_id`),
  KEY `FK_retours_utilisateurs` (`utilisateur_id`),
  KEY `retours_ibfk_1` (`client_id`),
  CONSTRAINT `FK_retours_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `retours_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`),
  CONSTRAINT `retours_ibfk_3` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.retours : ~0 rows (environ)
/*!40000 ALTER TABLE `retours` DISABLE KEYS */;
/*!40000 ALTER TABLE `retours` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. retours_details
DROP TABLE IF EXISTS `retours_details`;
CREATE TABLE IF NOT EXISTS `retours_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `retour_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `retour_id` (`retour_id`),
  KEY `produit_id` (`produit_id`),
  CONSTRAINT `retours_details_ibfk_1` FOREIGN KEY (`retour_id`) REFERENCES `retours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `retours_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.retours_details : ~0 rows (environ)
/*!40000 ALTER TABLE `retours_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `retours_details` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. stock_depot
DROP TABLE IF EXISTS `stock_depot`;
CREATE TABLE IF NOT EXISTS `stock_depot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `depot_id` (`depot_id`),
  KEY `idx_stock_prod_depot` (`produit_id`,`depot_id`),
  CONSTRAINT `stock_depot_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `stock_depot_ibfk_2` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.stock_depot : ~1 rows (environ)
/*!40000 ALTER TABLE `stock_depot` DISABLE KEYS */;
INSERT INTO `stock_depot` (`id`, `produit_id`, `depot_id`, `quantite`) VALUES
	(1, 1, 1, 4);
/*!40000 ALTER TABLE `stock_depot` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. sync_conflicts
DROP TABLE IF EXISTS `sync_conflicts`;
CREATE TABLE IF NOT EXISTS `sync_conflicts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `server_copy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`server_copy`)),
  `client_copy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`client_copy`)),
  `reason` text DEFAULT NULL,
  `resolved` enum('no','yes') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.sync_conflicts : ~0 rows (environ)
/*!40000 ALTER TABLE `sync_conflicts` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_conflicts` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. sync_log
DROP TABLE IF EXISTS `sync_log`;
CREATE TABLE IF NOT EXISTS `sync_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL DEFAULT 0,
  `operation` enum('insert','update','delete') DEFAULT NULL COMMENT '''ventes'',''ventes_lignes''',
  `table_name` varchar(255) DEFAULT NULL,
  `payload` longtext DEFAULT NULL COMMENT 'données envoyées par le client',
  `statut` enum('En cours','En traitement','Fait','Erreur') DEFAULT 'En cours',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.sync_log : ~0 rows (environ)
/*!40000 ALTER TABLE `sync_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_log` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. transferts
DROP TABLE IF EXISTS `transferts`;
CREATE TABLE IF NOT EXISTS `transferts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_source` int(11) NOT NULL,
  `depot_dest` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `statut` enum('En cours','Terminé') NOT NULL DEFAULT 'En cours',
  `commentaire` text NOT NULL DEFAULT 'En cours',
  `date_transfert` datetime NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `depot_source` (`depot_source`),
  KEY `depot_dest` (`depot_dest`),
  KEY `FK_transferts_utilisateurs` (`utilisateur_id`),
  CONSTRAINT `FK_transferts_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `transferts_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  CONSTRAINT `transferts_ibfk_2` FOREIGN KEY (`depot_source`) REFERENCES `depots` (`id`),
  CONSTRAINT `transferts_ibfk_3` FOREIGN KEY (`depot_dest`) REFERENCES `depots` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.transferts : ~1 rows (environ)
/*!40000 ALTER TABLE `transferts` DISABLE KEYS */;
/*!40000 ALTER TABLE `transferts` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. utilisateurs
DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','vendeur','magasinier') DEFAULT 'vendeur',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.utilisateurs : ~0 rows (environ)
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. ventes
DROP TABLE IF EXISTS `ventes`;
CREATE TABLE IF NOT EXISTS `ventes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `date_vente` datetime NOT NULL,
  `totalHT` decimal(15,2) DEFAULT NULL,
  `taxe` decimal(15,2) DEFAULT NULL,
  `remise` decimal(15,2) DEFAULT NULL,
  `totalTTC` decimal(15,2) DEFAULT NULL,
  `montant_verse` decimal(15,2) DEFAULT NULL,
  `reste_a_payer` decimal(15,2) DEFAULT NULL,
  `type_vente` enum('Comptant','Crédit') DEFAULT 'Comptant',
  `mode_paiement` enum('Espèces','Virement','Chèque','Mobile Money') DEFAULT 'Espèces',
  `statut` enum('Payé','Impayé') DEFAULT 'Impayé',
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `annule` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `ventes_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.ventes : ~2 rows (environ)
/*!40000 ALTER TABLE `ventes` DISABLE KEYS */;
INSERT INTO `ventes` (`id`, `client_id`, `date_vente`, `totalHT`, `taxe`, `remise`, `totalTTC`, `montant_verse`, `reste_a_payer`, `type_vente`, `mode_paiement`, `statut`, `commentaire`, `created_at`, `annule`) VALUES
	(1, 1, '2025-09-05 23:00:00', 3200.00, 0.00, 0.00, 3200.00, 3200.00, 0.00, 'Comptant', 'Espèces', 'Payé', NULL, '2025-09-23 23:02:25', 0),
	(2, 1, '2025-09-24 22:15:00', 3200.00, 0.00, 0.00, 3200.00, 0.00, 3200.00, 'Comptant', 'Espèces', 'Impayé', '', '2025-09-24 22:16:03', 0),
	(3, 1, '2025-09-25 17:24:00', 3200.00, 0.00, 0.00, 3200.00, 3200.00, 0.00, 'Comptant', 'Espèces', 'Payé', '', '2025-09-25 15:25:02', 0);
/*!40000 ALTER TABLE `ventes` ENABLE KEYS */;


-- Export de la structure de table quincaillerie. ventes_details
DROP TABLE IF EXISTS `ventes_details`;
CREATE TABLE IF NOT EXISTS `ventes_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vente_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(15,2) DEFAULT NULL,
  `depot_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vente_id` (`vente_id`),
  KEY `produit_id` (`produit_id`),
  KEY `FK_ventes_details_depots` (`depot_id`),
  CONSTRAINT `FK_ventes_details_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`),
  CONSTRAINT `ventes_details_ibfk_1` FOREIGN KEY (`vente_id`) REFERENCES `ventes` (`id`),
  CONSTRAINT `ventes_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Export de données de la table quincaillerie.ventes_details : ~2 rows (environ)
/*!40000 ALTER TABLE `ventes_details` DISABLE KEYS */;
INSERT INTO `ventes_details` (`id`, `vente_id`, `produit_id`, `quantite`, `prix_unitaire`, `depot_id`) VALUES
	(1, 1, 1, 1, 3200.00, 1),
	(2, 2, 1, 1, 3200.00, 1),
	(3, 3, 1, 1, 3200.00, 1);
/*!40000 ALTER TABLE `ventes_details` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
