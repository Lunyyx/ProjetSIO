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

-- Insertion des données initiales

-- Insertion des utilisateurs par défaut
INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES
('admin@fitandfun.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bureau'), -- password: password
('julie.fort@fitandfun-association.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bureau'),
('rachelle.leroy@fitandfun.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'animateur'),
('caroline.petit@fitandfun.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'animateur'),
('mathilde.rey@fitandfun.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'animateur'),
('bertille.dupont@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent'),
('lucas.bernard@outlook.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent'),
('alexandre.roux@yahoo.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'adherent');

-- Insertion des animateurs
INSERT INTO animateurs (utilisateur_id, nom, prenom, email, telephone, specialite) VALUES
(2, 'Fort', 'Julie', 'julie.fort@fitandfun-association.fr', '0612345678', 'Fitness'),
(3, 'Leroy', 'Rachelle', 'rachelle.leroy@fitandfun.fr', '0623456789', 'Zumba'),
(4, 'Petit', 'Caroline', 'caroline.petit@fitandfun.fr', '0634567890', 'Yoga'),
(5, 'Rey', 'Mathilde', 'mathilde.rey@fitandfun.fr', '0645678901', 'Renforcement musculaire');

-- Insertion des activités
INSERT INTO activites (nom, description, animateur_id, jour_semaine, heure_debut, heure_fin, duree_minutes, capacite_max, lieu) VALUES
('Fitness', 'Séance de fitness pour tous niveaux', 1, 'Lundi', '18:00:00', '19:00:00', 60, 20, 'Salle principale'),
('Zumba', 'Danse fitness sur des rythmes latins', 2, 'Mardi', '19:00:00', '20:00:00', 60, 25, 'Salle principale'),
('Yoga', 'Séance de yoga relaxant et tonifiant', 3, 'Jeudi', '18:30:00', '19:30:00', 60, 15, 'Salle zen'),
('Renforcement musculaire', 'Travail de tous les groupes musculaires', 4, 'Vendredi', '19:00:00', '20:00:00', 60, 20, 'Salle principale');

-- Insertion des adhérents
INSERT INTO adherents (utilisateur_id, nom, prenom, email, telephone, cotisation_payee) VALUES
(6, 'Dupont', 'Bertille', 'bertille.dupont@gmail.com', '0656789012', TRUE),
(7, 'Bernard', 'Lucas', 'lucas.bernard@outlook.fr', '0667890123', TRUE),
(8, 'Roux', 'Alexandre', 'alexandre.roux@yahoo.fr', '0678901234', TRUE);

-- Inscription des adhérents aux activités
INSERT INTO inscriptions (adherent_id, activite_id) VALUES
(1, 3), -- Bertille au Yoga
(2, 1), -- Lucas au Fitness
(3, 2); -- Alexandre à la Zumba
