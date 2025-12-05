# Fit&Fun - Application Web

Application web pour l'association sportive Fit&Fun développée en PHP.

## 🎯 Objectif du projet

Application de gestion pour une association sportive permettant :
- La gestion des adhérents
- La gestion des activités et du planning
- Les inscriptions en ligne
- La consultation publique des informations

## 📋 Prérequis

- PHP 7.4+
- MySQL/MariaDB
- DDEV (recommandé) ou serveur web (Apache/Nginx)

## 🚀 Installation avec DDEV

1. Cloner le projet :
```bash
git clone <url-du-repo>
cd ProjetSIO
```

2. Démarrer DDEV :
```bash
ddev start
```

3. Importer la base de données :
```bash
ddev import-db --src=database/schema.sql
```

4. Accéder à l'application :
- Site web : https://projetsio.ddev.site
- PhpMyAdmin : https://projetsio.ddev.site:8036

## 👥 Comptes de test

### Administrateur (Bureau)
- Email : admin@fitandfun.fr
- Mot de passe : password

### Animateur
- Email : julie.fort@fitandfun-association.fr
- Mot de passe : password

### Adhérent
- Email : bertille.dupont@gmail.com
- Mot de passe : password

## 📁 Structure du projet

```
ProjetSIO/
├── config/              # Fichiers de configuration
│   ├── config.php       # Configuration générale
│   └── database.php     # Configuration BDD
├── public/              # Pages publiques
│   ├── index.php        # Page d'accueil
│   ├── login.php        # Connexion
│   ├── inscription.php  # Formulaire d'inscription
│   ├── activites.php    # Liste des activités
│   ├── planning.php     # Planning
│   └── mes-inscriptions.php  # Gestion des inscriptions adhérent
├── src/
│   ├── models/          # Classes métier
│   │   ├── Utilisateur.php
│   │   ├── Adherent.php
│   │   ├── Activite.php
│   │   └── Inscription.php
│   └── includes/        # Templates
│       ├── header.php
│       └── footer.php
├── database/            # Scripts SQL
│   └── schema.sql       # Schéma de la base
├── assets/              # Ressources statiques
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
└── docs/                # Documentation
```

## 🗄️ Base de données

Tables principales :
- `utilisateurs` : Authentification et rôles
- `adherents` : Informations des adhérents
- `animateurs` : Informations des animateurs
- `activites` : Activités proposées
- `inscriptions` : Inscriptions aux activités
- `demandes_inscription` : Demandes depuis le formulaire public

## 🔐 Rôles utilisateurs

1. **Visiteur** : Consultation publique + demande d'inscription
2. **Adhérent** : Inscription aux activités, consultation du planning
3. **Animateur** : Gestion de ses séances
4. **Bureau** : Administration complète

## 📝 Fonctionnalités

### Espace public
- Présentation de l'association
- Liste des activités
- Planning des séances
- Formulaire de demande d'inscription

### Espace adhérent
- Inscription aux activités
- Consultation des inscriptions
- Désinscription

### Espace bureau (Administration)
- Gestion des adhérents
- Gestion des activités
- Traitement des demandes d'inscription
- Statistiques

## 🛠️ Technologies utilisées

- **Backend** : PHP 7.4+
- **Base de données** : MySQL/MariaDB
- **Frontend** : HTML5, CSS3, JavaScript
- **Architecture** : MVC simplifié
- **Sécurité** : PDO (requêtes préparées), password_hash

## 📊 Améliorations possibles

- [ ] Système de paiement en ligne pour les cotisations
- [ ] Notifications par email
- [ ] Calendrier interactif
- [ ] Export PDF des plannings
- [ ] Interface d'administration complète
- [ ] API REST pour une application mobile
- [ ] Gestion des absences/présences
- [ ] Système de réservation de créneaux

## 📧 Contact

Association Fit&Fun
- Email : julie.fort@fitandfun-association.fr
- Téléphone : 06 12 34 56 78
- Adresse : 12 rue Vaillant, 21000 Dijon

## 📄 Licence

Projet éducatif - BTS SIO
