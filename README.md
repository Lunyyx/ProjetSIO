# Fit&Fun - Application Web

Application web pour l'association sportive Fit&Fun développée en PHP.

## 🎯 Objectif du projet

Application de gestion pour une association sportive permettant :
- La gestion des adhérents et des utilisateurs
- La gestion des activités et du planning interactif
- Les inscriptions en ligne avec validation par email
- L'envoi d'emails automatisés (inscription, réinitialisation de mot de passe)
- La consultation publique des informations

## 📋 Prérequis

- PHP 8.0+
- MySQL/MariaDB
- DDEV (recommandé) ou serveur web (Apache/Nginx)
- Composer (pour les dépendances)

## 🚀 Installation avec DDEV

1. Cloner le projet :
```bash
git clone <url-du-repo>
cd ProjetSIO
```

2. Copier le fichier d'environnement et le configurer :
```bash
cp .env.example .env
# Éditer .env avec vos paramètres SMTP (Resend, etc.)
```

3. Démarrer DDEV :
```bash
ddev start
```

4. Installer les dépendances :
```bash
ddev composer install
```

5. Importer la base de données :
```bash
ddev import-db --src=database/schema.sql
```

6. Accéder à l'application :
- Site web : https://projetsio.ddev.site
- PhpMyAdmin : https://projetsio.ddev.site:8036

7. **Configuration initiale** :
   - À la première visite, vous serez redirigé vers `/setup.php`
   - Créez votre compte administrateur (membre du bureau)
   - Le setup se désactive automatiquement après la création

## 📁 Structure du projet

```
ProjetSIO/
├── config/                  # Fichiers de configuration
│   ├── config.php           # Configuration générale
│   └── database.php         # Configuration BDD
├── public/                  # Pages publiques (point d'entrée web)
│   ├── index.php            # Page d'accueil
│   ├── setup.php            # Configuration initiale
│   ├── login.php            # Connexion
│   ├── logout.php           # Déconnexion
│   ├── inscription.php      # Inscription adhérent
│   ├── definir-mot-de-passe.php    # Définition mot de passe
│   ├── mot-de-passe-oublie.php     # Récupération mot de passe
│   ├── reinitialiser-mot-de-passe.php  # Reset mot de passe
│   ├── profil.php           # Gestion du profil
│   ├── activites.php        # Liste des activités
│   ├── planning.php         # Planning interactif (FullCalendar)
│   ├── mes-inscriptions.php # Inscriptions adhérent
│   ├── gestion-seances.php  # Gestion séances animateur
│   ├── admin/               # Espace administration
│   │   ├── index.php        # Tableau de bord
│   │   ├── adherents.php    # Gestion adhérents
│   │   ├── animateurs.php   # Gestion animateurs
│   │   ├── activites.php    # Gestion activités
│   │   ├── inscriptions.php # Gestion inscriptions
│   │   ├── demandes.php     # Traitement demandes
│   │   └── utilisateurs.php # Gestion des rôles
│   ├── api/                 # API JSON
│   │   ├── planning.php     # Données planning
│   │   └── inscriptions.php # Vérification inscriptions
│   └── assets/              # Ressources statiques
│       ├── css/
│       └── js/
├── src/
│   ├── models/              # Classes métier (OOP)
│   │   ├── Utilisateur.php
│   │   ├── Adherent.php
│   │   ├── Animateur.php
│   │   ├── Activite.php
│   │   ├── Inscription.php
│   │   └── Token.php
│   ├── services/            # Services
│   │   └── MailService.php  # Envoi d'emails (PHPMailer)
│   └── includes/            # Templates
│       ├── header.php
│       └── footer.php
├── database/                # Scripts SQL
│   └── schema.sql           # Schéma de la base
├── vendor/                  # Dépendances Composer
├── docs/                    # Documentation
├── .env                     # Variables d'environnement (à créer)
└── composer.json            # Dépendances PHP
```

## 🗄️ Base de données

Tables principales :
- `utilisateurs` : Authentification et rôles
- `adherents` : Informations des adhérents
- `animateurs` : Informations des animateurs
- `activites` : Activités proposées
- `inscriptions` : Inscriptions aux activités
- `demandes_inscription` : Demandes depuis le formulaire public
- `tokens` : Tokens de sécurité (reset mot de passe, activation)

## 🔐 Rôles utilisateurs

1. **Visiteur** : Consultation publique + demande d'inscription
2. **Adhérent** : Inscription aux activités, consultation du planning, gestion du profil
3. **Animateur** : Gestion de ses séances et participants
4. **Bureau** : Administration complète (gestion des utilisateurs, rôles, etc.)

## 📝 Fonctionnalités

### Espace public
- Présentation de l'association
- Liste des activités avec inscription
- Planning interactif (FullCalendar)
- Formulaire de demande d'inscription
- Récupération de mot de passe

### Espace adhérent
- Inscription/désinscription aux activités
- Consultation des inscriptions
- Gestion du profil personnel
- Changement de mot de passe

### Espace animateur
- Gestion de ses séances
- Liste des participants inscrits

### Espace bureau (Administration)
- Tableau de bord avec statistiques
- Gestion des adhérents (CRUD)
- Gestion des animateurs (CRUD)
- Gestion des activités (CRUD)
- Gestion des utilisateurs et rôles
- Traitement des demandes d'inscription
- Création d'activités depuis le planning (double-clic)

## 📧 Configuration email

L'application utilise PHPMailer avec Resend pour l'envoi d'emails.

Créez un fichier `.env` à la racine :
```env
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_votre_api_key
MAIL_FROM_ADDRESS=noreply@votredomaine.fr
MAIL_FROM_NAME="Fit&Fun"
```

Emails envoyés :
- Confirmation d'inscription (définition du mot de passe)
- Réinitialisation de mot de passe
- Invitation membre du bureau

## 🛠️ Technologies utilisées

- **Backend** : PHP 8.0+ (POO, PDO)
- **Base de données** : MariaDB
- **Frontend** : HTML5, CSS3, JavaScript
- **Planning** : FullCalendar 6.1
- **Emails** : PHPMailer + Resend SMTP
- **Environnement** : DDEV (Docker)
- **Dépendances** : Composer, phpdotenv
- **Sécurité** : password_hash, tokens sécurisés, requêtes préparées

## 📊 Améliorations possibles

- [x] ~~Notifications par email~~
- [x] ~~Calendrier interactif~~
- [x] ~~Interface d'administration complète~~
- [x] ~~Gestion des rôles utilisateurs~~
- [ ] Système de paiement en ligne pour les cotisations
- [ ] Export PDF des plannings
- [ ] API REST pour une application mobile
- [ ] Gestion des absences/présences
- [ ] Système de réservation de créneaux

## 📧 Contact

Association Fit&Fun
- Email : contact@fitandfun.fr
- Téléphone : 06 12 34 56 78
- Adresse : 12 rue Vaillant, 21000 Dijon

## 📄 Licence

Projet éducatif - BTS SIO
