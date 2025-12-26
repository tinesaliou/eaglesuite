
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id`, `nom`, `description`, `date_creation`) VALUES
	(1, 'admin', 'Accès total', '2025-10-02 21:55:19'),
	(2, 'vendeur', 'Gestion des ventes', '2025-10-02 21:55:19'),
	(3, 'caissier', 'Gestion de la caisse et encaissements', '2025-10-02 21:55:19'),
	(4, 'Stock', 'Gestion des produits et des stocks', '2025-10-04 13:08:32'),
	(5, 'comptable', 'Gestion des flux financier', '2025-10-04 17:51:27'),
	(6, 'utilisateur', 'utilisateur simple', '2025-11-26 16:13:24');


CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `permissions` (`id`, `code`, `description`) VALUES
	(1, 'dashboard.view', 'Accès au dashboard'),
	(2, 'produits.view', 'Voir produits'),
	(3, 'categories.view', 'Voir catégories'),
	(4, 'ventes.view', 'Voir ventes'),
	(5, 'achats.view', 'Voir achats'),
	(6, 'creances.view', 'Voir créances'),
	(7, 'dettes.view', 'Voir dettes'),
	(8, 'depots.view', 'Voir dépôts'),
	(9, 'retours.view', 'Voir retours'),
	(10, 'clients.view', 'Voir clients'),
	(11, 'tresorerie.view', 'Voir trésorerie'),
	(12, 'caisse.especes.view', 'Voir caisse espèces'),
	(13, 'caisse.banque.view', 'Voir caisse banque'),
	(14, 'caisse.mobile.view', 'Voir caisse mobile'),
	(15, 'operations.autres.view', 'Voir autres opérations'),
	(16, 'fournisseurs.view', 'Voir fournisseurs'),
	(17, 'settings.view', 'Voir paramètres'),
	(18, 'users.manage', 'gerer utilisateurs'),
	(19, 'roles.manage', 'gerer rôles'),
	(20, 'rapports.achats.view', 'Voir rapport achats'),
	(21, 'rapports.ventes.view', 'Voir rapport ventes'),
	(22, 'rapports.caisse.view', 'Voir rapport caisse'),
	(23, 'rapports.stocks.view', 'Voir rapport stocks'),
	(24, 'mouvements.view', 'Voir mouvements'),
	(25, 'inventaire.view', 'Voir inventaires'),
	(26, 'crm.dashboard.view', 'Accès Dashboard CRM'),
	(27, 'crm.clients.view', 'Voir client CRM'),
	(28, 'crm.clients.manage', 'Ajouter/éditer clients'),
	(29, 'crm.opportunites.view', 'Voir pipeline'),
	(30, 'crm.opportunites.manage', 'Modifier opportunités'),
	(31, 'crm.interactions.view', 'Voir interactions'),
	(32, 'crm.interactions.manage', 'Ajouter interactions');

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `FK_role_permissions_permissions` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_role_permissions_permissions` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6),
	(1, 7),
	(1, 8),
	(1, 9),
	(1, 10),
	(1, 11),
	(1, 12),
	(1, 13),
	(1, 14),
	(1, 15),
	(1, 16),
	(1, 17),
	(1, 18),
	(1, 19),
	(1, 20),
	(1, 21),
	(1, 22),
	(1, 23),
	(1, 24),
	(1, 25),
	(1, 26),
	(1, 27),
	(1, 28),
	(1, 29),
	(1, 30),
	(1, 31),
	(1, 32),
	(2, 1),
	(2, 4),
	(2, 6),
	(2, 10),
	(3, 1),
	(3, 11),
	(3, 12),
	(3, 13),
	(3, 14),
	(3, 15),
	(4, 1),
	(4, 2),
	(4, 3),
	(4, 8),
	(4, 9),
	(4, 23),
	(5, 1),
	(5, 5),
	(5, 6),
	(5, 7),
	(5, 21),
	(5, 22);

CREATE TABLE IF NOT EXISTS `devises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(6) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `symbole` varchar(8) DEFAULT NULL,
  `taux_par_defaut` decimal(10,2) DEFAULT 1.00,
  `actif` tinyint(1) DEFAULT 1,
  `est_base` tinyint(1) DEFAULT 0,
  `date_mise_a_jour` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `devises` (`id`, `code`, `nom`, `symbole`, `taux_par_defaut`, `actif`, `est_base`, `date_mise_a_jour`) VALUES
	(1, 'XOF', 'F FCA', 'CFA', 1.00, 1, 1, '2025-11-11 09:46:06'),
	(2, 'USD', 'Dollar US', '$', 567.21, 1, 0, '2025-11-11 09:46:06'),
	(3, 'EUR', 'Euro', '€', 655.96, 1, 0, '2025-11-11 09:46:06');

CREATE TABLE IF NOT EXISTS `parametres_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cle` varchar(255) DEFAULT NULL,
  `valeur` text NOT NULL,
  `type` enum('texte','nombre','bool','json') DEFAULT 'texte',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `entreprise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `site_web` varchar(100) DEFAULT NULL,
  `ninea` varchar(100) DEFAULT NULL,
  `rccm` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `unites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `depots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prix_achat` decimal(15,2) DEFAULT NULL,
  `prix_vente` decimal(15,2) DEFAULT NULL,
  `stock_total` int(11) DEFAULT 0,
  `seuil_alerte` int(11) DEFAULT 0,
  `categorie_id` int(11) DEFAULT NULL,
  `depot_id` int(11) DEFAULT NULL,
  `image` varchar(50) DEFAULT NULL,
  `unite_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `categorie_id` (`categorie_id`),
  KEY `FK_produits_depots` (`depot_id`),
  CONSTRAINT `FK_produits_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `caisses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `type` enum('Espèces','Banque','Mobile Money') NOT NULL,
  `devise_id` int(11) DEFAULT 1,
  `solde_initial` decimal(15,2) DEFAULT 0.00,
  `solde_actuel` decimal(15,2) DEFAULT 0.00,
  `actif` tinyint(1) DEFAULT 1,
  `description` varchar(100) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_caisses_devises` (`devise_id`),
  CONSTRAINT `FK_caisses_devises` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `operations_caisse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caisse_id` int(11) NOT NULL,
  `type_operation` enum('entree','sortie') NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `devise_id` int(11) DEFAULT NULL,
  `mode_paiement` enum('Espèces','Virement','Chèque','Mobile Money','Transfert') NOT NULL,
  `reference_table` varchar(50) DEFAULT NULL,
  `reference_id` int(11) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_operation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caisse_id` (`caisse_id`),
  KEY `FK_operations_caisse_devises` (`devise_id`),
  CONSTRAINT `FK_operations_caisse_devises` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `operations_caisse_ibfk_1` FOREIGN KEY (`caisse_id`) REFERENCES `caisses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS `tva` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `taux` decimal(5,2) NOT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `entreprise_id` int(11) DEFAULT 1,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `clients` (
  `idClient` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `exonere` tinyint(1) DEFAULT 0,
  `type` enum('Particulier','Entreprise','Passager') DEFAULT 'Particulier',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('Actif','Inactif','Prospect') DEFAULT 'Actif',
  `origine` enum('Facebook','WhatsApp','Référence','Site Web','Autre') DEFAULT NULL,
  `secteur` varchar(255) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `derniere_interaction` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idClient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `exonere` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `ventes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
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
  `annule` tinyint(1) NOT NULL DEFAULT 0,
  `devise_id` int(11) DEFAULT 1,
  `montant_devise` decimal(15,2) DEFAULT NULL,
  `taux_change` decimal(15,6) DEFAULT NULL,
  `entreprise_id` int(11) DEFAULT 1,
  `utilisateur_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_ventes_entreprise` (`entreprise_id`),
  KEY `ventes_ibfk_1` (`client_id`),
  KEY `FK_ventes_utilisateurs` (`utilisateur_id`),
  CONSTRAINT `FK_ventes_entreprise` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprise` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ventes_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventes_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  CONSTRAINT `FK_ventes_details_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventes_details_ibfk_1` FOREIGN KEY (`vente_id`) REFERENCES `ventes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ventes_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `achats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(50) NOT NULL,
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
  `statut` enum('Payé','Impayé') DEFAULT 'Payé',
  `annule` tinyint(1) unsigned DEFAULT 0,
  `devise_id` int(11) DEFAULT 1,
  `montant_devise` decimal(15,2) DEFAULT NULL,
  `taux_change` decimal(15,6) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `entreprise_id` int(11) DEFAULT 1,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_achats_utilisateurs` (`utilisateur_id`),
  KEY `achats_ibfk_1` (`fournisseur_id`),
  KEY `FK_achats_entreprise` (`entreprise_id`),
  CONSTRAINT `FK_achats_entreprise` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprise` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_achats_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `achats_ibfk_1` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  CONSTRAINT `FK_achats_details_depots` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `achats_details_ibfk_1` FOREIGN KEY (`achat_id`) REFERENCES `achats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `achats_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mouvements_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_source_id` int(11) DEFAULT NULL,
  `depot_dest_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `type` enum('achat','vente','retour','annulation_achat','annulation_vente') NOT NULL,
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
  CONSTRAINT `mouvements_stock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mouvements_stock_ibfk_2` FOREIGN KEY (`depot_source_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mouvements_stock_ibfk_3` FOREIGN KEY (`depot_dest_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mouvements_stock_ibfk_4` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `stock_depot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_id` int(11) NOT NULL,
  `quantite` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `depot_id` (`depot_id`),
  KEY `idx_stock_prod_depot` (`produit_id`,`depot_id`),
  CONSTRAINT `stock_depot_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_depot_ibfk_2` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `caisses_transferts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_caisse_id` int(11) NOT NULL,
  `to_caisse_id` int(11) NOT NULL,
  `montant` decimal(20,2) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `from_caisse_id` (`from_caisse_id`),
  KEY `to_caisse_id` (`to_caisse_id`),
  CONSTRAINT `caisses_transferts_ibfk_1` FOREIGN KEY (`from_caisse_id`) REFERENCES `caisses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caisses_transferts_ibfk_2` FOREIGN KEY (`to_caisse_id`) REFERENCES `caisses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `autres_operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caisse_id` int(11) NOT NULL,
  `type` enum('entree','sortie') NOT NULL,
  `categorie` varchar(100) NOT NULL,
  `montant` decimal(15,2) unsigned NOT NULL,
  `date_operation` datetime NOT NULL DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `devise_id` int(11) DEFAULT 1,
  `montant_devise` decimal(15,2) DEFAULT NULL,
  `taux_change` decimal(15,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caisse_id` (`caisse_id`),
  CONSTRAINT `autres_operations_ibfk_1` FOREIGN KEY (`caisse_id`) REFERENCES `caisses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `retours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `fournisseur_id` int(11) DEFAULT NULL,
  `date_retour` datetime NOT NULL,
  `raison` text NOT NULL,
  `depot_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('client','fournisseur') NOT NULL DEFAULT 'client',
  PRIMARY KEY (`id`),
  KEY `depot_id` (`depot_id`),
  KEY `FK_retours_utilisateurs` (`utilisateur_id`),
  KEY `retours_ibfk_1` (`client_id`),
  CONSTRAINT `retours_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE CASCADE,
  CONSTRAINT `retours_ibfk_3` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  CONSTRAINT `retours_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `creances_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `vente_id` int(11) DEFAULT NULL,
  `montant_total` decimal(15,2) NOT NULL,
  `montant_paye` decimal(15,2) DEFAULT 0.00,
  `reste_a_payer` decimal(15,2) GENERATED ALWAYS AS (`montant_total` - `montant_paye`) STORED,
  `statut` enum('En cours','Soldé') DEFAULT 'En cours',
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `vente_id` (`vente_id`),
  CONSTRAINT `creances_clients_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE CASCADE,
  CONSTRAINT `creances_clients_ibfk_2` FOREIGN KEY (`vente_id`) REFERENCES `ventes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `dettes_fournisseurs` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`fournisseur_id` INT(11) NOT NULL,
	`achat_id` INT(11) NULL DEFAULT NULL,
	`montant_total` DECIMAL(15,2) NOT NULL,
	`montant_paye` DECIMAL(15,2) NULL DEFAULT '0',
	`reste_a_payer` DECIMAL(15,2) NULL DEFAULT NULL,
	`statut` ENUM('En cours','Soldé') NULL DEFAULT 'En cours',
	 `date_creation` datetime DEFAULT current_timestamp(),
	PRIMARY KEY (`id`),
	INDEX `fournisseur_id` (`fournisseur_id`),
	INDEX `achat_id` (`achat_id`),
	CONSTRAINT `dettes_fournisseurs_ibfk_1` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
	CONSTRAINT `dettes_fournisseurs_ibfk_2` FOREIGN KEY (`achat_id`) REFERENCES `achats` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numeroRecu` varchar(20) DEFAULT NULL,
  `idVente` int(11) DEFAULT NULL,
  `datePaiement` datetime DEFAULT current_timestamp(),
  `montantVerse` decimal(12,2) DEFAULT NULL,
  `modePaiement` varchar(50) DEFAULT NULL,
  `referenceTransaction` varchar(50) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idVente` (`idVente`),
  CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`idVente`) REFERENCES `ventes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `transferts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `depot_source` int(11) NOT NULL,
  `depot_dest` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `statut` enum('En cours','Terminé') NOT NULL DEFAULT 'En cours',
  `commentaire` text NOT NULL,
  `date_transfert` datetime NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `depot_source` (`depot_source`),
  KEY `depot_dest` (`depot_dest`),
  KEY `FK_transferts_utilisateurs` (`utilisateur_id`),
  CONSTRAINT `FK_transferts_utilisateurs` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `transferts_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transferts_ibfk_2` FOREIGN KEY (`depot_source`) REFERENCES `depots` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `transferts_ibfk_3` FOREIGN KEY (`depot_dest`) REFERENCES `depots` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `crm_stages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `couleur` varchar(20) NOT NULL DEFAULT 'secondary',
  `position` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `crm_opportunites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `montant` decimal(15,2) DEFAULT 0.00,
  `devise_id` int(11) DEFAULT NULL,
  `etat` varchar(100) NOT NULL DEFAULT 'prospect',
  `probabilite` int(11) DEFAULT 0,
  `date_cloture_prevue` date DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_crm_opp_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE CASCADE,
  CONSTRAINT `fk_crm_opp_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `crm_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `type` enum('note','appel','email','rdv','reunion') NOT NULL DEFAULT 'note',
  `sujet` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_interaction` datetime NOT NULL DEFAULT current_timestamp(),
  `suivi` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_crm_inter_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE CASCADE,
  CONSTRAINT `fk_crm_inter_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `crm_taches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `opportunite_id` int(11) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `statut` enum('ouverte','en_cours','terminee','annulee') NOT NULL DEFAULT 'ouverte',
  `priorite` enum('basse','moyenne','haute') NOT NULL DEFAULT 'moyenne',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `opportunite_id` (`opportunite_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `fk_crm_task_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`idClient`) ON DELETE RESTRICT,
  CONSTRAINT `fk_crm_task_opp` FOREIGN KEY (`opportunite_id`) REFERENCES `crm_opportunites` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_crm_task_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `crm_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objet_type` varchar(50) DEFAULT NULL,
  `objet_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `meta` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `device_sync` (
  `device_id` varchar(100) NOT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `info` longtext DEFAULT NULL,
  PRIMARY KEY (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `sync_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL DEFAULT 0,
  `operation` enum('insert','update','delete') DEFAULT NULL,
  `table_name` varchar(255) DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `statut` enum('En cours','En traitement','Fait','Erreur') DEFAULT 'En cours',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) DEFAULT NULL,
  `action` varchar(150) NOT NULL,
  `objet_type` varchar(100) DEFAULT NULL,
  `objet_id` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe` varchar(50) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `valeur` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `sync_conflicts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `server_copy` longtext DEFAULT NULL,
  `client_copy` longtext DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `resolved` enum('no','yes') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `crm_stages` (`slug`,`nom`,`couleur`,`position`,`active`) VALUES
  ('prospect','Prospect','secondary',0,1),
  ('qualification','Qualification','info',1,1),
  ('negociation','Négociation','warning',2,1),
  ('gagne','Gagnée','success',3,1),
  ('perdu','Perdue','danger',4,1)
ON DUPLICATE KEY UPDATE nom=VALUES(nom);

INSERT INTO `devises` (`code`,`nom`,`symbole`,`taux_par_defaut`,`actif`,`est_base`) VALUES
  ('XOF','Franc CFA','FCFA',1.00,1,1),
  ('EUR','Euro','€',655.957,1,0)
ON DUPLICATE KEY UPDATE symbole=VALUES(symbole);

CREATE TABLE `notifications` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`titre` VARCHAR(255) NULL DEFAULT NULL,
	`message` TEXT NULL DEFAULT NULL,
	`level` ENUM('info','warning','danger') NULL DEFAULT 'info',
	`lu` TINYINT(1) NULL DEFAULT '0',
	`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

