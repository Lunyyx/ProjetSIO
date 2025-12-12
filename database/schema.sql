-- Base de données pour l'application Fit&Fun
-- Création de la base de données
-- CREATE DATABASE IF NOT EXISTS db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db;

-- Table des utilisateurs (pour l'authentification)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('visiteur', 'adherent', 'animateur', 'bureau') DEFAULT 'visiteur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des adhérents
CREATE TABLE IF NOT EXISTS adherents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    date_naissance DATE,
    cotisation_payee BOOLEAN DEFAULT FALSE,
    date_inscription DATE DEFAULT (CURRENT_DATE),
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des animateurs
CREATE TABLE IF NOT EXISTS animateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    specialite VARCHAR(255),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des activités
CREATE TABLE IF NOT EXISTS activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    animateur_id INT,
    jour_semaine ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME,
    duree_minutes INT DEFAULT 60,
    capacite_max INT DEFAULT 20,
    lieu VARCHAR(255),
    statut ENUM('active', 'annulee', 'suspendue') DEFAULT 'active',
    FOREIGN KEY (animateur_id) REFERENCES animateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des inscriptions aux activités
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adherent_id INT NOT NULL,
    activite_id INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('active', 'annulee') DEFAULT 'active',
    FOREIGN KEY (adherent_id) REFERENCES adherents(id) ON DELETE CASCADE,
    FOREIGN KEY (activite_id) REFERENCES activites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inscription (adherent_id, activite_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des demandes d'inscription (formulaire public)
CREATE TABLE IF NOT EXISTS demandes_inscription (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    activite_souhaitee VARCHAR(100),
    message TEXT,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'traitee', 'refusee') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des tokens (réinitialisation mot de passe, activation, etc.)
CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    type ENUM('password_reset', 'password_set', 'email_verification') DEFAULT 'password_reset',
    expire_at DATETIME NOT NULL,
    utilise BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de démonstration (optionnelles, à supprimer en production)
-- Pour créer le premier compte administrateur, utilisez la page /setup.php
