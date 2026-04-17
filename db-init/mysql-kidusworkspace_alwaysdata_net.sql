-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Generation Time: Apr 17, 2026 at 10:32 PM
-- Server version: 10.11.15-MariaDB
-- PHP Version: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `accompagnateur_event_asso`
--

CREATE TABLE `accompagnateur_event_asso` (
  `Id_Accompagnateur` int(11) NOT NULL,
  `Id_Membre` int(11) NOT NULL,
  `Id_Event_associatif` int(11) NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Prenom` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Tarif` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Id_Admin` int(11) NOT NULL,
  `identifiant` varchar(100) NOT NULL,
  `Mail` varchar(255) NOT NULL,
  `Mot_de_passe` varchar(255) NOT NULL,
  `token_reset` varchar(255) DEFAULT NULL,
  `token_reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`Id_Admin`, `identifiant`, `Mail`, `Mot_de_passe`, `token_reset`, `token_reset_expires`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$a94FcpPJm7dgpJTHGC.8zuwghiF2tOZSMrtWYiVGa6OkIC0Z83Dra', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aide_benevole`
--

CREATE TABLE `aide_benevole` (
  `Id_Membre` int(11) NOT NULL,
  `Id_creneau` int(11) NOT NULL,
  `Presence` tinyint(1) DEFAULT 0,
  `Date_inscription` datetime DEFAULT current_timestamp(),
  `Preference_Poste` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aide_benevole`
--

INSERT INTO `aide_benevole` (`Id_Membre`, `Id_creneau`, `Presence`, `Date_inscription`, `Preference_Poste`) VALUES
(25, 2, 0, '2026-04-08 14:31:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categorie_evenement`
--

CREATE TABLE `categorie_evenement` (
  `Id_Categorie_evenement` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categorie_evenement`
--

INSERT INTO `categorie_evenement` (`Id_Categorie_evenement`, `libelle`) VALUES
(6, 'Autre'),
(2, 'Crossfit'),
(1, 'Hyrox'),
(5, 'Natation'),
(3, 'Run'),
(4, 'Tournoi');

-- --------------------------------------------------------

--
-- Table structure for table `categorie_poste`
--

CREATE TABLE `categorie_poste` (
  `Id_Poste` int(11) NOT NULL,
  `Id_Categorie_evenement` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `creneau_event`
--

CREATE TABLE `creneau_event` (
  `Id_creneau` int(11) NOT NULL,
  `Type` enum('preparation','jour_j','rangement') NOT NULL,
  `Commentaire` text DEFAULT NULL,
  `Date_creneau` date NOT NULL,
  `Heure_Debut` time NOT NULL,
  `Heure_Fin` time NOT NULL,
  `Id_Event_sportif` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creneau_event`
--

INSERT INTO `creneau_event` (`Id_creneau`, `Type`, `Commentaire`, `Date_creneau`, `Heure_Debut`, `Heure_Fin`, `Id_Event_sportif`) VALUES
(1, 'preparation', 'Installation', '2026-04-22', '08:00:00', '10:00:00', 1),
(2, 'jour_j', 'Compétition', '2026-04-22', '10:00:00', '18:00:00', 1),
(3, 'jour_j', 'Départs Vagues', '2026-04-22', '09:00:00', '17:00:00', 2),
(4, 'jour_j', 'Course', '2026-04-22', '09:30:00', '12:30:00', 3);

-- --------------------------------------------------------

--
-- Table structure for table `creneau_poste`
--

CREATE TABLE `creneau_poste` (
  `Id_creneau` int(11) NOT NULL,
  `Id_Poste` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_associatif`
--

CREATE TABLE `event_associatif` (
  `Id_Event_associatif` int(11) NOT NULL,
  `Titre` varchar(255) NOT NULL,
  `Descriptif` text DEFAULT NULL,
  `Url_Image` varchar(500) DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL,
  `Code_Postal` varchar(10) DEFAULT NULL,
  `Ville` varchar(100) DEFAULT NULL,
  `Lien_Maps` varchar(500) DEFAULT NULL,
  `Date_Visibilite` datetime DEFAULT NULL,
  `Date_Cloture` datetime DEFAULT NULL,
  `Tarif` decimal(10,2) DEFAULT 9.99,
  `Url_HelloAsso` varchar(500) DEFAULT NULL,
  `Prive` tinyint(1) DEFAULT 0,
  `Date_Evenement` date DEFAULT NULL,
  `Date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_associatif`
--

INSERT INTO `event_associatif` (`Id_Event_associatif`, `Titre`, `Descriptif`, `Url_Image`, `Adresse`, `Code_Postal`, `Ville`, `Lien_Maps`, `Date_Visibilite`, `Date_Cloture`, `Tarif`, `Url_HelloAsso`, `Prive`, `Date_Evenement`, `Date_creation`) VALUES
(1, 'Gala de Charité 2026', 'Grand dîner annuel.', NULL, 'Salle des Congrès', '31000', 'Toulouse', NULL, '2026-01-01 00:00:00', '2026-04-20 23:59:00', 25.00, NULL, 0, '2026-04-22', '2026-02-02 22:54:41'),
(2, 'Pique-nique de Printemps', 'Rencontre conviviale.', NULL, 'Parc de la Prairie', '31400', 'Toulouse', NULL, '2026-02-01 00:00:00', '2026-04-20 18:00:00', 0.00, NULL, 1, '2026-04-22', '2026-02-02 22:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `event_sportif`
--

CREATE TABLE `event_sportif` (
  `Id_Event_sportif` int(11) NOT NULL,
  `Titre` varchar(255) NOT NULL,
  `Descriptif` text DEFAULT NULL,
  `Url_Image` varchar(500) DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL,
  `Code_Postal` varchar(10) DEFAULT NULL,
  `Ville` varchar(100) DEFAULT NULL,
  `Lien_Maps` varchar(500) DEFAULT NULL,
  `Date_Visibilite` datetime DEFAULT NULL,
  `Date_Cloture` datetime DEFAULT NULL,
  `Id_Categorie_evenement` int(11) NOT NULL,
  `Date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_sportif`
--

INSERT INTO `event_sportif` (`Id_Event_sportif`, `Titre`, `Descriptif`, `Url_Image`, `Adresse`, `Code_Postal`, `Ville`, `Lien_Maps`, `Date_Visibilite`, `Date_Cloture`, `Id_Categorie_evenement`, `Date_creation`) VALUES
(1, 'Tournoi Crossfit Avril', 'Compétition régionale.', NULL, 'Box KastAsso', '31200', 'Toulouse', NULL, '2026-01-15 00:00:00', '2026-04-20 23:59:00', 2, '2026-02-02 22:54:41'),
(2, 'Hyrox Challenge 22', 'Le défi ultime.', NULL, 'Palais des Sports', '31000', 'Toulouse', NULL, '2026-02-01 00:00:00', '2026-04-20 23:59:00', 1, '2026-02-02 22:54:41'),
(3, 'La Toulousaine Run', 'Course de 10km.', NULL, 'Quai de la Daurade', '31000', 'Toulouse', NULL, '2026-01-20 00:00:00', '2026-04-20 12:00:00', 3, '2026-02-02 22:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `liaison_pref`
--

CREATE TABLE `liaison_pref` (
  `Id_Membre` int(11) NOT NULL,
  `Id_Preference_alimentaire` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `liaison_regime`
--

CREATE TABLE `liaison_regime` (
  `Id_Membre` int(11) NOT NULL,
  `Id_Restriction_alimentaire` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membre`
--

CREATE TABLE `membre` (
  `Id_Membre` int(11) NOT NULL,
  `Prenom` varchar(100) NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Sexe` enum('H','F') DEFAULT NULL,
  `Mail` varchar(255) NOT NULL,
  `Mot_de_passe` varchar(255) NOT NULL,
  `Telephone` varchar(20) NOT NULL,
  `Url_Photo_Profil` varchar(500) DEFAULT NULL,
  `Taille_Teeshirt` varchar(10) DEFAULT NULL,
  `Taille_Pull` varchar(10) DEFAULT NULL,
  `Adherent` tinyint(1) DEFAULT 0,
  `Statut_adhesion` enum('en_attente','accepte','refuse') DEFAULT NULL,
  `Date_demande_adhesion` datetime DEFAULT NULL,
  `Date_validation_adhesion` datetime DEFAULT NULL,
  `Message_adhesion` text DEFAULT NULL,
  `Url_Adhesion` varchar(500) DEFAULT NULL,
  `Statut_compte` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `Date_statut_compte` datetime DEFAULT current_timestamp(),
  `Message_statut_compte` text DEFAULT NULL,
  `Commentaire_Alimentaire` text DEFAULT NULL,
  `regime_alimentaire_id` int(10) UNSIGNED DEFAULT NULL,
  `Gestionnaire` tinyint(1) DEFAULT 0,
  `Date_creation` datetime DEFAULT current_timestamp(),
  `token_reset` varchar(255) DEFAULT NULL,
  `token_reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `membre`
--

INSERT INTO `membre` (`Id_Membre`, `Prenom`, `Nom`, `Sexe`, `Mail`, `Mot_de_passe`, `Telephone`, `Url_Photo_Profil`, `Taille_Teeshirt`, `Taille_Pull`, `Adherent`, `Statut_adhesion`, `Date_demande_adhesion`, `Date_validation_adhesion`, `Message_adhesion`, `Url_Adhesion`, `Statut_compte`, `Date_statut_compte`, `Message_statut_compte`, `Commentaire_Alimentaire`, `regime_alimentaire_id`, `Gestionnaire`, `Date_creation`, `token_reset`, `token_reset_expires`) VALUES
(1, 'Thomas', 'Gestion', 'H', 'gestionnaire1@kastasso.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0601020304', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 1, '2026-02-03 08:16:59', NULL, NULL),
(2, 'Marie', 'Bureau', 'F', 'gestionnaire2@kastasso.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0611223344', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 1, '2026-02-03 08:16:59', NULL, NULL),
(3, 'Alice', 'Dubois', 'F', 'alice.dubois@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0601010101', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 1, 0, '2026-02-03 08:16:59', NULL, NULL),
(4, 'Benoit', 'Chevalier', 'H', 'benoit.chevalier@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0602020202', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(5, 'Chloé', 'Morel', 'F', 'chloe.morel@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0603030303', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 2, 0, '2026-02-03 08:16:59', NULL, NULL),
(6, 'David', 'Simon', 'H', 'david.simon@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0604040404', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 1, '2026-02-03 08:16:59', NULL, NULL),
(7, 'Elodie', 'Michel', 'F', 'elodie.michel@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0605050505', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 3, 0, '2026-02-03 08:16:59', NULL, NULL),
(8, 'François', 'Lefevre', 'H', 'francois.lefevre@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0606060606', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(9, 'Gaelle', 'Leroy', 'F', 'gaelle.leroy@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0607070707', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(10, 'Hugo', 'Roux', 'H', 'hugo.roux@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0608080808', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 5, 0, '2026-02-03 08:16:59', NULL, NULL),
(11, 'Ines', 'David', 'F', 'ines.david@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0609090909', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(12, 'Julien', 'Bertrand', 'H', 'julien.bertrand@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0610101010', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 6, 0, '2026-02-03 08:16:59', NULL, NULL),
(13, 'Karine', 'Girard', 'F', 'karine.girard@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0611111111', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 1, '2026-02-03 08:16:59', NULL, NULL),
(14, 'Lucas', 'Bonnet', 'H', 'lucas.bonnet@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0612121212', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(15, 'Manon', 'Dupuis', 'F', 'manon.dupuis@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0613131313', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 1, 0, '2026-02-03 08:16:59', NULL, NULL),
(16, 'Nicolas', 'Gauthier', 'H', 'nicolas.gauthier@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0614141414', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(17, 'Océane', 'Perrin', 'F', 'oceane.perrin@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0615151515', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(18, 'Pierre', 'Fournier', 'H', 'pierre.fournier@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0616161616', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 4, 0, '2026-02-03 08:16:59', NULL, NULL),
(19, 'Quentin', 'Mercier', 'H', 'quentin.mercier@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0617171717', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(20, 'Romane', 'Blanc', 'F', 'romane.blanc@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0618181818', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(21, 'Sébastien', 'Guerin', 'H', 'sebastien.guerin@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0619191919', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, NULL, 0, '2026-02-03 08:16:59', NULL, NULL),
(22, 'Tatiana', 'Faure', 'F', 'tatiana.faure@test.fr', '$2y$10$5N7.W/k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z0123456789ABCDEF', '0620202020', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'valide', '2026-02-03 08:16:59', NULL, NULL, 2, 0, '2026-02-03 08:16:59', NULL, NULL),
(23, 'Bekila', 'Abebe', 'H', 'abebe@gmail.com', '$2y$12$u3KRgaxfNWvQMBamVoiApOfN8Jm67Wcw1yxSVrQWcZsQuASIROf36', '0953911481', NULL, 'L', 'M', 1, 'accepte', NULL, '2026-03-11 16:50:46', NULL, '', 'valide', '2026-02-03 09:36:00', 'Compte validé', 'Aucun', 3, 1, '2026-02-03 09:35:47', NULL, NULL),
(24, 'Assefa', 'Kirubel', 'F', 'kirubel@gmail.com', '$2y$12$l18fgyTa0H24jF3yHiWldua0Q8w3k9sWTHi6ZQrY4/ZgKD2iQcQtm', '0953911481', NULL, '', '', 0, NULL, NULL, NULL, NULL, '', 'en_attente', '2026-02-03 09:37:19', 'Votre demande a bien été enregistrée', 'Aucun', NULL, 0, '2026-02-03 09:37:19', NULL, NULL),
(25, 'Test', 'Kastasso', 'H', 'testkastasso123@gmail.com', '$2y$12$oRTnrn1rtIjLZKz0q/6xXuqBpqCq/NDIT5.1sf9kLdP9xbCp2U2Yu', '', 'uploads/img_69d64935020290.37650415.png', 'XL', 'M', 0, 'en_attente', '2026-04-08 14:25:25', NULL, NULL, 'uploads/adhesions/adhesion_25_1775651125.pdf', 'valide', '2026-04-08 14:25:47', 'Compte validé', 'Aucun', 3, 1, '2026-04-08 14:25:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `participer`
--

CREATE TABLE `participer` (
  `Id_Membre` int(11) NOT NULL,
  `Id_Event_associatif` int(11) NOT NULL,
  `Paiement` tinyint(1) DEFAULT 0,
  `nb_invites` int(11) DEFAULT 0,
  `Date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participer`
--

INSERT INTO `participer` (`Id_Membre`, `Id_Event_associatif`, `Paiement`, `nb_invites`, `Date_inscription`) VALUES
(25, 1, 0, 0, '2026-04-08 15:18:38');

-- --------------------------------------------------------

--
-- Table structure for table `poste`
--

CREATE TABLE `poste` (
  `Id_Poste` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `niveau` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `poste`
--

INSERT INTO `poste` (`Id_Poste`, `libelle`, `niveau`) VALUES
(1, 'Buvette', 1),
(2, 'Accueil', 1),
(3, 'Installation', 1),
(4, 'Chronomètre', 2),
(5, 'Circulation', 2),
(6, 'Sécurité', 3),
(7, 'Judging', 3),
(8, 'Médical', 3);

-- --------------------------------------------------------

--
-- Table structure for table `preference_alimentaire`
--

CREATE TABLE `preference_alimentaire` (
  `Id_Preference_alimentaire` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preference_poste`
--

CREATE TABLE `preference_poste` (
  `Id_Membre` int(11) NOT NULL,
  `Id_Poste` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regimes_alimentaires`
--

CREATE TABLE `regimes_alimentaires` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regimes_alimentaires`
--

INSERT INTO `regimes_alimentaires` (`id`, `nom`, `date_creation`) VALUES
(1, 'Végétarien', '2026-02-03 08:16:58'),
(2, 'Végétalien', '2026-02-03 08:16:58'),
(3, 'Sans Gluten', '2026-02-03 08:16:58'),
(4, 'Sans Porc', '2026-02-03 08:16:58'),
(5, 'Halal', '2026-02-03 08:16:58'),
(6, 'Casher', '2026-02-03 08:16:58');

-- --------------------------------------------------------

--
-- Table structure for table `restriction_alimentaire`
--

CREATE TABLE `restriction_alimentaire` (
  `Id_Restriction_alimentaire` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accompagnateur_event_asso`
--
ALTER TABLE `accompagnateur_event_asso`
  ADD PRIMARY KEY (`Id_Accompagnateur`),
  ADD KEY `idx_membre_event` (`Id_Membre`,`Id_Event_associatif`),
  ADD KEY `accompagnateur_event_asso_ibfk_2` (`Id_Event_associatif`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Id_Admin`),
  ADD UNIQUE KEY `identifiant` (`identifiant`),
  ADD UNIQUE KEY `Mail` (`Mail`);

--
-- Indexes for table `aide_benevole`
--
ALTER TABLE `aide_benevole`
  ADD PRIMARY KEY (`Id_Membre`,`Id_creneau`),
  ADD KEY `Id_creneau` (`Id_creneau`);

--
-- Indexes for table `categorie_evenement`
--
ALTER TABLE `categorie_evenement`
  ADD PRIMARY KEY (`Id_Categorie_evenement`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `categorie_poste`
--
ALTER TABLE `categorie_poste`
  ADD PRIMARY KEY (`Id_Poste`,`Id_Categorie_evenement`),
  ADD KEY `Id_Categorie_evenement` (`Id_Categorie_evenement`);

--
-- Indexes for table `creneau_event`
--
ALTER TABLE `creneau_event`
  ADD PRIMARY KEY (`Id_creneau`),
  ADD KEY `Id_Event_sportif` (`Id_Event_sportif`);

--
-- Indexes for table `creneau_poste`
--
ALTER TABLE `creneau_poste`
  ADD PRIMARY KEY (`Id_creneau`,`Id_Poste`),
  ADD KEY `FK_creneau_poste_poste` (`Id_Poste`);

--
-- Indexes for table `event_associatif`
--
ALTER TABLE `event_associatif`
  ADD PRIMARY KEY (`Id_Event_associatif`);

--
-- Indexes for table `event_sportif`
--
ALTER TABLE `event_sportif`
  ADD PRIMARY KEY (`Id_Event_sportif`),
  ADD KEY `Id_Categorie_evenement` (`Id_Categorie_evenement`);

--
-- Indexes for table `liaison_pref`
--
ALTER TABLE `liaison_pref`
  ADD PRIMARY KEY (`Id_Membre`,`Id_Preference_alimentaire`),
  ADD KEY `liaison_pref_ibfk_2` (`Id_Preference_alimentaire`);

--
-- Indexes for table `liaison_regime`
--
ALTER TABLE `liaison_regime`
  ADD PRIMARY KEY (`Id_Membre`,`Id_Restriction_alimentaire`),
  ADD KEY `liaison_regime_ibfk_2` (`Id_Restriction_alimentaire`);

--
-- Indexes for table `membre`
--
ALTER TABLE `membre`
  ADD PRIMARY KEY (`Id_Membre`),
  ADD UNIQUE KEY `Mail` (`Mail`),
  ADD KEY `fk_membre_regime` (`regime_alimentaire_id`);

--
-- Indexes for table `participer`
--
ALTER TABLE `participer`
  ADD PRIMARY KEY (`Id_Membre`,`Id_Event_associatif`),
  ADD KEY `Id_Event_associatif` (`Id_Event_associatif`);

--
-- Indexes for table `poste`
--
ALTER TABLE `poste`
  ADD PRIMARY KEY (`Id_Poste`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `preference_alimentaire`
--
ALTER TABLE `preference_alimentaire`
  ADD PRIMARY KEY (`Id_Preference_alimentaire`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `regimes_alimentaires`
--
ALTER TABLE `regimes_alimentaires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Indexes for table `restriction_alimentaire`
--
ALTER TABLE `restriction_alimentaire`
  ADD PRIMARY KEY (`Id_Restriction_alimentaire`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accompagnateur_event_asso`
--
ALTER TABLE `accompagnateur_event_asso`
  MODIFY `Id_Accompagnateur` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Id_Admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categorie_evenement`
--
ALTER TABLE `categorie_evenement`
  MODIFY `Id_Categorie_evenement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `creneau_event`
--
ALTER TABLE `creneau_event`
  MODIFY `Id_creneau` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `event_associatif`
--
ALTER TABLE `event_associatif`
  MODIFY `Id_Event_associatif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_sportif`
--
ALTER TABLE `event_sportif`
  MODIFY `Id_Event_sportif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membre`
--
ALTER TABLE `membre`
  MODIFY `Id_Membre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `poste`
--
ALTER TABLE `poste`
  MODIFY `Id_Poste` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `preference_alimentaire`
--
ALTER TABLE `preference_alimentaire`
  MODIFY `Id_Preference_alimentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regimes_alimentaires`
--
ALTER TABLE `regimes_alimentaires`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `restriction_alimentaire`
--
ALTER TABLE `restriction_alimentaire`
  MODIFY `Id_Restriction_alimentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accompagnateur_event_asso`
--
ALTER TABLE `accompagnateur_event_asso`
  ADD CONSTRAINT `accompagnateur_event_asso_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `accompagnateur_event_asso_ibfk_2` FOREIGN KEY (`Id_Event_associatif`) REFERENCES `event_associatif` (`Id_Event_associatif`) ON DELETE CASCADE;

--
-- Constraints for table `aide_benevole`
--
ALTER TABLE `aide_benevole`
  ADD CONSTRAINT `aide_benevole_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `aide_benevole_ibfk_2` FOREIGN KEY (`Id_creneau`) REFERENCES `creneau_event` (`Id_creneau`) ON DELETE CASCADE;

--
-- Constraints for table `categorie_poste`
--
ALTER TABLE `categorie_poste`
  ADD CONSTRAINT `categorie_poste_ibfk_1` FOREIGN KEY (`Id_Poste`) REFERENCES `poste` (`Id_Poste`) ON DELETE CASCADE,
  ADD CONSTRAINT `categorie_poste_ibfk_2` FOREIGN KEY (`Id_Categorie_evenement`) REFERENCES `categorie_evenement` (`Id_Categorie_evenement`) ON DELETE CASCADE;

--
-- Constraints for table `creneau_event`
--
ALTER TABLE `creneau_event`
  ADD CONSTRAINT `creneau_event_ibfk_1` FOREIGN KEY (`Id_Event_sportif`) REFERENCES `event_sportif` (`Id_Event_sportif`) ON DELETE CASCADE;

--
-- Constraints for table `creneau_poste`
--
ALTER TABLE `creneau_poste`
  ADD CONSTRAINT `FK_creneau_poste_creneau` FOREIGN KEY (`Id_creneau`) REFERENCES `creneau_event` (`Id_creneau`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_creneau_poste_poste` FOREIGN KEY (`Id_Poste`) REFERENCES `poste` (`Id_Poste`) ON DELETE CASCADE;

--
-- Constraints for table `event_sportif`
--
ALTER TABLE `event_sportif`
  ADD CONSTRAINT `event_sportif_ibfk_1` FOREIGN KEY (`Id_Categorie_evenement`) REFERENCES `categorie_evenement` (`Id_Categorie_evenement`);

--
-- Constraints for table `liaison_pref`
--
ALTER TABLE `liaison_pref`
  ADD CONSTRAINT `liaison_pref_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `liaison_pref_ibfk_2` FOREIGN KEY (`Id_Preference_alimentaire`) REFERENCES `preference_alimentaire` (`Id_Preference_alimentaire`) ON DELETE CASCADE;

--
-- Constraints for table `liaison_regime`
--
ALTER TABLE `liaison_regime`
  ADD CONSTRAINT `liaison_regime_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `liaison_regime_ibfk_2` FOREIGN KEY (`Id_Restriction_alimentaire`) REFERENCES `restriction_alimentaire` (`Id_Restriction_alimentaire`) ON DELETE CASCADE;

--
-- Constraints for table `membre`
--
ALTER TABLE `membre`
  ADD CONSTRAINT `fk_membre_regime` FOREIGN KEY (`regime_alimentaire_id`) REFERENCES `regimes_alimentaires` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `participer`
--
ALTER TABLE `participer`
  ADD CONSTRAINT `participer_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `participer_ibfk_2` FOREIGN KEY (`Id_Event_associatif`) REFERENCES `event_associatif` (`Id_Event_associatif`) ON DELETE CASCADE;

--
-- Constraints for table `preference_poste`
--
ALTER TABLE `preference_poste`
  ADD CONSTRAINT `preference_poste_ibfk_1` FOREIGN KEY (`Id_Membre`) REFERENCES `membre` (`Id_Membre`) ON DELETE CASCADE,
  ADD CONSTRAINT `preference_poste_ibfk_2` FOREIGN KEY (`Id_Poste`) REFERENCES `poste` (`Id_Poste`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;