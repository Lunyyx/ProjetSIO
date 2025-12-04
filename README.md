# Fit&Fun - Application Web de Gestion d'Association Sportive

## 📋 Présentation du Projet

Application web développée pour l'association **Fit&Fun**, une association sportive loi 1901 basée à Dijon proposant différentes activités (fitness, zumba, yoga, renforcement musculaire).

**Contact Client**: Julie Fort - Présidente  
**Email**: julie.fort@fitandfun-association.fr  
**Adresse**: 12 rue Vaillant, 21000 Dijon

---

## ✅ Conformité au Cahier des Charges

### 1. Objectifs Fonctionnels

#### ✅ Gestion des Adhérents
- [x] Enregistrement des adhérents (nom, prénom, email, activités, cotisation)
- [x] Consultation / modification / suppression d'un adhérent
- [x] Interface dédiée : `/admin/members/manage.php`
- [x] Rôles : Adhérent, Visiteur
- [x] **3 adhérents exemples créés** selon le cahier des charges

#### ✅ Gestion des Activités
- [x] Liste des activités proposées (nom, description, couleur)
- [x] Ajout / suppression / modification d'une activité
- [x] Interface dédiée : `/admin/planning/manage.php`
- [x] **5 activités créées** incluant les 4 du cahier des charges

#### ✅ Planning
- [x] Visualisation des créneaux d'activités
- [x] Affichage sous forme de calendrier (FullCalendar)
- [x] Interface publique : `/planning.php`
- [x] Interface admin : `/admin/planning/manage.php`
- [x] **4 créneaux créés** selon le cahier des charges :
  - Fitness — Julie Fort — Lundi 18h00
  - Zumba — Rachelle Leroy — Mardi 19h00
  - Yoga — Caroline Petit — Jeudi 18h30
  - Renforcement musculaire — Mathilde Rey — Vendredi 19h00

#### ✅ Espace Public
- [x] Page d'accueil présentant l'association : `/index.php`
- [x] Formulaire d'inscription en ligne : `/inscription.php`
- [x] Planning accessible au public : `/planning.php`

---

### 2. Utilisateurs et Droits

| Profil | Droits | Interface | ✅ |
|--------|--------|-----------|---|
| **Visiteur** | Consulter planning, s'inscrire | Public | ✅ |
| **Adhérent** | Consulter planning, gérer profil | Public | ✅ |
| **Animateur** | Modifier ses séances | `/animateurs/dashboard.php` | ✅ |
| **Membre du bureau** | Gérer tout (adhérents, activités, planning, cotisations) | `/admin/area.php` | ✅ |

**Système de permissions** : `/includes/permissions.php`

---

### 3. Contraintes Techniques

#### ✅ Technologies Utilisées
- [x] **Base de données** : MySQL / MariaDB (via DDEV)
- [x] **Backend** : PHP 8.x
- [x] **Frontend** : HTML5, CSS3, Bootstrap 5.3.8
- [x] **JavaScript** : FullCalendar 6.1.10 pour le planning
- [x] **Hébergement** : Serveur local DDEV
- [x] **Accès** : http://fitfun.ddev.site
- [x] **Sécurité** : Sessions PHP, mots de passe hashés (bcrypt), requêtes préparées PDO

#### ✅ Architecture Base de Données

**4 Tables créées** :

1. **users** (table unifiée)
   - Champs : id, first_name, last_name, email, password, role, phone, address, address_pc, address_city, preferred_activities, specialties, created_at, password_reset_token, password_reset_expires, updated_at
   - Rôles : adherent, animateur, membre_bureau, visiteur
   - **Nouveau** : Support des tokens de réinitialisation de mot de passe

2. **activities**
   - Champs : id, name, description, color, created_at
   - 5 activités configurées

3. **schedule**
   - Champs : id, activity_id, user_id, day_of_week, start_time, end_time, max_participants, location, is_recurring, is_active
   - 4 créneaux configurés

4. **cotisations**
   - Champs : id, user_id, amount, payment_date, start_date, end_date, payment_method, status

---

### 4. Données Initiales du Client

#### ✅ Activités (5/5)
1. Fitness ✅
2. Zumba ✅
3. Yoga ✅
4. Renforcement musculaire ✅
5. Pilates (bonus) ✅

#### ✅ Animateurs (4/4)
1. Julie Fort - julie.fort@fitandfun-association.fr - Fitness ✅
2. Rachelle Leroy - rachelle.leroy@fitfun.fr - Zumba ✅
3. Caroline Petit - caroline.petit@fitfun.fr - Yoga ✅
4. Mathilde Rey - mathilde.rey@fitfun.fr - Renforcement musculaire ✅

**Mot de passe par défaut** : password123

#### ✅ Adhérents Exemples (3/3)
1. Bertille Dupont - bertille.dupont@gmail.com - Yoga ✅
2. Lucas Bernard - lucas.bernard@outlook.fr - Fitness ✅
3. Alexandre Roux - alexandre.roux@yahoo.fr - Zumba ✅

#### ✅ Planning de la Semaine (4/4)
- Lundi 18h00 : Fitness (Julie Fort) ✅
- Mardi 19h00 : Zumba (Rachelle Leroy) ✅
- Jeudi 18h30 : Yoga (Caroline Petit) ✅
- Vendredi 19h00 : Renforcement musculaire (Mathilde Rey) ✅

---

## 🗂️ Structure du Projet

```
ProjetSIO/
├── admin/                    # Zone d'administration (membre bureau)
│   ├── area.php             # Dashboard admin
│   ├── members/             # Gestion adhérents
│   ├── instructors/         # Gestion animateurs
│   ├── bureau/              # Gestion membres bureau
│   ├── planning/            # Gestion planning
│   └── cotisations/         # Gestion cotisations
├── animateurs/              # Espace animateurs
│   └── dashboard.php        # Dashboard animateur
├── auth/                    # Authentification
│   ├── login.php            # Connexion
│   └── logout.php           # Déconnexion
├── api/                     # API REST
│   └── fetch_schedule.php   # Récupération planning JSON
├── config/                  # Configuration
│   └── database.php         # Connexion BDD + variables .env
├── includes/                # Fichiers communs
│   ├── header.php           # En-tête navigation
│   ├── permissions.php      # Système de permissions
│   └── mailer.php           # Classe d'envoi d'emails ✅ (NOUVEAU)
├── auth/                    # Authentification
│   ├── login.php            # Connexion
│   ├── logout.php           # Déconnexion
│   └── setup_password.php   # Définition mot de passe ✅ (NOUVEAU)
├── assets/                  # Ressources statiques
│   ├── css/
│   └── images/
├── .env                     # Configuration (SMTP, BDD) ✅
├── .env.example             # Exemple de configuration ✅
├── index.php                # Page d'accueil publique ✅
├── planning.php             # Planning public ✅
├── inscription.php          # Formulaire inscription ✅
├── process_inscription.php  # Traitement inscription + email ✅
├── test_email.php           # Script de test emails ✅ (NOUVEAU)
├── DEMARRAGE_EMAIL.md       # Guide rapide emails ✅ (NOUVEAU)
└── CONFIGURATION_EMAIL.md   # Documentation complète emails ✅ (NOUVEAU)
```

---

## 🚀 Installation et Démarrage

### Prérequis
- DDEV installé
- PHP 8.x
- MySQL/MariaDB

### Démarrage
```bash
# Démarrer le projet
ddev start

# Accéder à l'application
ddev launch

# Accéder à phpMyAdmin
ddev phpmyadmin
```

### Configuration des emails (obligatoire)

**Méthode rapide** : Voir le guide `DEMARRAGE_EMAIL.md`

```bash
# 1. Créer un compte Mailtrap gratuit sur https://mailtrap.io

# 2. Configurer .env avec vos credentials
nano .env

# 3. Tester l'envoi
ddev exec php test_email.php
```

**Documentation complète** : `CONFIGURATION_EMAIL.md`
ddev launch

# Accéder à phpMyAdmin
ddev phpmyadmin
```

### URL d'accès
- **Application** : http://fitfun.ddev.site
- **phpMyAdmin** : http://fitfun.ddev.site:8036

---

## 👥 Comptes de Test

### Membre du Bureau
- Email : `admin@fitfun.fr`
- Mot de passe : `password123`
- Accès : Toute l'administration

### Animateurs
- Email : `julie.fort@fitandfun-association.fr` (et autres animateurs)
- Mot de passe : `password123`
- Accès : Dashboard animateur

---

## 📊 Fonctionnalités Principales

### Pour les Visiteurs (Public)
1. Consulter la page d'accueil
2. Voir le planning des cours
3. S'inscrire via le formulaire en ligne ✅ (NOUVEAU)

### Pour les Adhérents
1. Consulter le planning
2. Voir leurs activités préférées
3. Consulter leur profil

### Pour les Animateurs
1. Consulter le planning
2. Gérer leurs séances (dashboard)
3. Voir la liste de leurs cours

### Pour les Membres du Bureau
1. Gérer les adhérents (CRUD complet)
2. Gérer les animateurs (CRUD complet)
3. Gérer les membres du bureau (CRUD complet)
4. Gérer les activités (CRUD complet)
5. Gérer le planning (CRUD complet)
6. Gérer les cotisations (CRUD complet)
7. Voir les statistiques

---

## ✅ Vérification Cahier des Charges

### Objectifs Globaux
- [x] Concepts réseau (serveur local DDEV, requêtes HTTP)
- [x] Base de données (modèle relationnel, MySQL, requêtes SQL)
- [x] Développement web (frontend Bootstrap / backend PHP)
- [x] Mise en production (serveur web local)
- [x] Gestion de projet (besoin analysé, données du client intégrées)

### Livrables
- [x] Application web fonctionnelle
- [ ] Schéma réseau (à documenter)
- [x] Modèle de données (4 tables créées)
- [ ] Maquette (à documenter)
- [x] Code source fonctionnel
- [x] README documenté
- [ ] Présentation finale (à préparer)

---

## 📝 Notes Importantes

### Améliorations Récentes
1. ✅ Formulaire d'inscription public créé (`/inscription.php`)
2. ✅ Lien "Inscription" ajouté dans le menu de navigation
3. ✅ 4 animateurs du cahier des charges créés
4. ✅ 3 adhérents exemples créés
5. ✅ 4 créneaux du planning configurés exactement comme demandé
6. ✅ Toutes les activités du cahier des charges présentes
7. ✅ **Système d'envoi d'emails configuré (PHPMailer)**
8. ✅ **Email automatique avec lien de définition de mot de passe**
9. ✅ **Notifications au bureau lors des inscriptions**

### Points d'Attention
- Les emails nécessitent une configuration SMTP (voir `DEMARRAGE_EMAIL.md`)
- Configuration SMTP dans le fichier `.env` (Mailtrap recommandé pour dev)
- Les tokens de mot de passe expirent après 24 heures
- Le projet est prêt pour la présentation finale

---

## 👨‍💻 Développement

### Technologies
- **PHP** : POO, PDO, Sessions, Password Hashing
- **MySQL** : Schéma relationnel, clés étrangères, requêtes JOIN
- **Bootstrap 5** : Design responsive, composants modernes
- **FullCalendar** : Calendrier interactif pour le planning
- **DDEV** : Environnement de développement local

### Sécurité
- Requêtes préparées PDO (protection SQL injection)
- Hashage des mots de passe (bcrypt)
- Validation des sessions
- Système de permissions par rôle
- Protection CSRF (à améliorer)

---

## 📞 Contact

**Association Fit&Fun**  
12 rue Vaillant  
21000 Dijon  
Email: julie.fort@fitandfun-association.fr  
Tél: 06 12 34 56 78

---

**Projet développé dans le cadre du BTS SIO - Applications Web**
