# CAHIER DES CHARGES
## Application Web Fit&Fun

---

## 1. PRÉSENTATION GÉNÉRALE

### Client
- **Nom** : Association Fit&Fun
- **Type** : Association sportive loi 1901
- **Contact** : Julie Fort – Présidente
- **Email** : julie.fort@fitandfun-association.fr
- **Téléphone** : 06 12 34 56 78
- **Adresse** : 12 rue Vaillant, 21000 Dijon

### Contexte
L'association Fit&Fun propose diverses activités sportives (fitness, zumba, yoga, renforcement musculaire) à environ 120 adhérents, encadrées par 4 animateurs qualifiés.

Actuellement, la gestion administrative se fait sur papier et par e-mail, ce qui entraîne :
- Des erreurs de saisie
- Des pertes d'information
- Une difficulté à suivre les inscriptions
- Un temps considérable consacré aux tâches administratives
- Une absence de visibilité pour les adhérents sur les activités disponibles

### Problématique
Comment moderniser la gestion de l'association et améliorer l'expérience des adhérents grâce à une application web ?

---

## 2. OBJECTIFS DU PROJET

### Objectif principal
Créer une application web permettant de gérer efficacement les adhérents, les activités et les inscriptions de l'association.

### Objectifs spécifiques
1. **Digitaliser la gestion** : Remplacer la gestion papier par un système informatisé
2. **Automatiser les inscriptions** : Permettre aux adhérents de s'inscrire en ligne
3. **Améliorer la communication** : Offrir une visibilité permanente sur le planning et les activités
4. **Faciliter l'administration** : Centraliser toutes les informations dans une base de données
5. **Sécuriser les données** : Assurer la protection des données personnelles

---

## 3. UTILISATEURS CIBLES

| Profil | Description | Droits et fonctionnalités |
|--------|-------------|---------------------------|
| **Visiteur** | Toute personne consultant le site | - Consulter la page d'accueil<br>- Voir les activités proposées<br>- Consulter le planning<br>- Soumettre une demande d'inscription |
| **Adhérent** | Personne inscrite à l'association | - Toutes les fonctionnalités du visiteur<br>- S'inscrire/se désinscrire aux activités<br>- Consulter ses inscriptions<br>- Voir son profil |
| **Animateur** | Encadrant d'activités | - Toutes les fonctionnalités de l'adhérent<br>- Consulter la liste des inscrits à ses séances<br>- Modifier les informations de ses séances |
| **Bureau** | Membre du bureau de l'association | - Toutes les fonctionnalités précédentes<br>- Gérer les adhérents (CRUD)<br>- Gérer les activités (CRUD)<br>- Traiter les demandes d'inscription<br>- Accéder aux statistiques |

---

## 4. PÉRIMÈTRE FONCTIONNEL

### 4.1. Fonctionnalités minimales (MVP)

#### Espace public
- **Page d'accueil**
  - Présentation de l'association
  - Mise en avant des activités
  - Call-to-action pour l'inscription
  
- **Liste des activités**
  - Affichage de toutes les activités avec détails (jour, heure, animateur, lieu)
  - Indication du nombre de places disponibles
  - Possibilité de s'inscrire (si connecté)
  
- **Planning**
  - Visualisation du planning hebdomadaire
  - Organisation par jour de la semaine
  - Informations complètes pour chaque séance
  
- **Formulaire d'inscription**
  - Formulaire de demande d'adhésion
  - Envoi à la base de données pour traitement par le bureau

#### Espace adhérent
- **Authentification**
  - Connexion sécurisée (email + mot de passe)
  - Déconnexion
  
- **Gestion des inscriptions**
  - S'inscrire à une ou plusieurs activités
  - Se désinscrire d'une activité
  - Consulter la liste de ses inscriptions
  
- **Profil**
  - Consultation de ses informations personnelles
  - Statut de la cotisation

#### Espace administration (Bureau)
- **Gestion des adhérents**
  - Créer un adhérent
  - Consulter la liste des adhérents
  - Modifier les informations d'un adhérent
  - Supprimer un adhérent
  - Marquer la cotisation comme payée/non payée
  
- **Gestion des activités**
  - Créer une activité
  - Consulter la liste des activités
  - Modifier une activité
  - Supprimer une activité
  
- **Traitement des demandes**
  - Consulter les demandes d'inscription
  - Valider ou refuser une demande
  - Créer un compte adhérent depuis une demande

### 4.2. Fonctionnalités optionnelles (évolutions futures)
- Envoi d'emails automatiques (confirmation d'inscription, rappels)
- Gestion des paiements en ligne
- Calendrier interactif
- Système de présence/absence
- Export PDF des documents (planning, listes)
- Notifications push
- Application mobile
- Système de messagerie interne
- Gestion des salles et du matériel

---

## 5. DESCRIPTION DES PAGES

### Pages publiques

#### Page d'accueil (`index.php`)
- **En-tête** : Logo, menu de navigation
- **Hero section** : Titre accrocheur, bouton d'inscription
- **Section "Qui sommes-nous"** : Présentation de l'association
- **Section "Nos activités"** : Aperçu des activités (4 cartes)
- **Call-to-action** : Incitation à s'inscrire
- **Pied de page** : Informations de contact, liens rapides

#### Page des activités (`activites.php`)
- Liste complète des activités sous forme de cartes détaillées
- Informations : nom, description, jour, heure, animateur, lieu, places disponibles
- Bouton d'inscription (si connecté et places disponibles)
- Indication "Complet" si capacité maximale atteinte
- Indication "Inscrit" si l'utilisateur est déjà inscrit

#### Page planning (`planning.php`)
- Organisation par jour de la semaine
- Pour chaque jour : liste des séances avec horaires
- Informations compactes : activité, animateur, lieu, places
- Lien vers la page des activités pour s'inscrire

#### Page inscription (`inscription.php`)
- Formulaire de demande d'adhésion
- Champs : nom, prénom, email, téléphone, activité souhaitée, message
- Validation des données
- Message de confirmation après envoi

#### Page connexion (`login.php`)
- Formulaire de connexion (email + mot de passe)
- Lien vers la page d'inscription
- Comptes de démonstration affichés pour les tests

### Pages authentifiées

#### Mes inscriptions (`mes-inscriptions.php`)
- **Profil** : Informations personnelles, statut de cotisation
- **Mes activités** : Liste des activités auxquelles l'utilisateur est inscrit
- Bouton de désinscription pour chaque activité
- Lien vers la page des activités pour découvrir d'autres séances

#### Administration (à développer)
- Dashboard avec statistiques
- Gestion des adhérents (tableau avec actions CRUD)
- Gestion des activités (tableau avec actions CRUD)
- Gestion des demandes d'inscription
- Gestion des animateurs

---

## 6. CONTRAINTES TECHNIQUES

### 6.1. Infrastructure
- **Hébergement** : Serveur local (DDEV recommandé)
- **Serveur web** : Apache ou Nginx
- **Base de données** : MySQL 5.7+ ou MariaDB 10.3+
- **Environnement PHP** : PHP 7.4 minimum (8.0+ recommandé)

### 6.2. Technologies imposées
- **Backend** : PHP (procédural ou orienté objet)
- **Frontend** : HTML5, CSS3, JavaScript (vanilla)
- **Base de données** : SQL (MySQL/MariaDB)
- **Architecture** : MVC ou architecture similaire

### 6.3. Sécurité
- **Mots de passe** : Hashage avec `password_hash()` (bcrypt)
- **Requêtes SQL** : Utilisation de requêtes préparées (PDO)
- **Sessions** : Gestion sécurisée des sessions PHP
- **Validation** : Validation côté serveur de tous les formulaires
- **Protection CSRF** : Pour les formulaires importants (optionnel mais recommandé)
- **Sanitisation** : Échappement des données affichées (`htmlspecialchars`)

### 6.4. Qualité du code
- Code commenté et documenté
- Respect des conventions de nommage
- Séparation des responsabilités (modèle, vue, contrôleur)
- Code réutilisable et maintenable
- Gestion des erreurs appropriée

### 6.5. Responsive design
- Interface adaptée aux différentes tailles d'écran
- Mobile-friendly (smartphones et tablettes)
- Navigation intuitive sur tous les appareils

---

## 7. ARCHITECTURE RÉSEAU

### Schéma réseau simplifié

```
┌─────────────────┐
│   Navigateur    │
│    (Client)     │
└────────┬────────┘
         │ HTTP/HTTPS
         │ Port 80/443
         │
┌────────▼────────┐
│  Serveur Web    │
│  (Apache/Nginx) │
│  Port 80/443    │
└────────┬────────┘
         │
         │ PHP
         │
┌────────▼────────┐
│  Application    │
│  PHP/PHP-FPM    │
└────────┬────────┘
         │
         │ PDO/MySQL
         │ Port 3306
         │
┌────────▼────────┐
│   Serveur BDD   │
│ MySQL/MariaDB   │
│   Port 3306     │
└─────────────────┘
```

### Composants réseau
- **Client** : Navigateur web (Chrome, Firefox, Safari, Edge)
- **Protocole** : HTTP/HTTPS
- **Serveur web** : Apache 2.4+ ou Nginx
- **Port web** : 80 (HTTP) ou 443 (HTTPS)
- **Serveur d'application** : PHP 7.4+
- **Serveur de base de données** : MySQL/MariaDB
- **Port BDD** : 3306 (MySQL)
- **DNS local** : projetsio.ddev.site (avec DDEV)

---

## 8. MODÈLE DE DONNÉES

### 8.1. Schéma de base de données

#### Table `utilisateurs`
Gère l'authentification et les rôles.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email de connexion |
| mot_de_passe | VARCHAR(255) | NOT NULL | Mot de passe hashé |
| role | ENUM | NOT NULL | visiteur, adherent, animateur, bureau |
| date_creation | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de création |
| date_modification | TIMESTAMP | ON UPDATE | Date de dernière modification |

#### Table `adherents`
Informations détaillées des adhérents.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| utilisateur_id | INT | FK(utilisateurs.id) | Lien vers le compte utilisateur |
| nom | VARCHAR(100) | NOT NULL | Nom de famille |
| prenom | VARCHAR(100) | NOT NULL | Prénom |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email |
| telephone | VARCHAR(20) | | Numéro de téléphone |
| adresse | TEXT | | Adresse postale |
| date_naissance | DATE | | Date de naissance |
| cotisation_payee | BOOLEAN | DEFAULT FALSE | Statut cotisation |
| date_inscription | DATE | DEFAULT CURRENT_DATE | Date d'adhésion |
| statut | ENUM | DEFAULT 'actif' | actif, inactif, suspendu |

#### Table `animateurs`
Informations des animateurs.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| utilisateur_id | INT | FK(utilisateurs.id) | Lien vers le compte utilisateur |
| nom | VARCHAR(100) | NOT NULL | Nom |
| prenom | VARCHAR(100) | NOT NULL | Prénom |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email |
| telephone | VARCHAR(20) | | Téléphone |
| specialite | VARCHAR(255) | | Spécialité(s) |

#### Table `activites`
Activités proposées par l'association.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL | Nom de l'activité |
| description | TEXT | | Description détaillée |
| animateur_id | INT | FK(animateurs.id) | Animateur responsable |
| jour_semaine | ENUM | NOT NULL | Jour de la semaine |
| heure_debut | TIME | NOT NULL | Heure de début |
| heure_fin | TIME | | Heure de fin |
| duree_minutes | INT | DEFAULT 60 | Durée en minutes |
| capacite_max | INT | DEFAULT 20 | Nombre max de participants |
| lieu | VARCHAR(255) | | Lieu de la séance |
| statut | ENUM | DEFAULT 'active' | active, annulee, suspendue |

#### Table `inscriptions`
Inscriptions des adhérents aux activités.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| adherent_id | INT | FK(adherents.id), NOT NULL | Adhérent inscrit |
| activite_id | INT | FK(activites.id), NOT NULL | Activité choisie |
| date_inscription | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date d'inscription |
| statut | ENUM | DEFAULT 'active' | active, annulee |
| | | UNIQUE(adherent_id, activite_id) | Un adhérent par activité |

#### Table `demandes_inscription`
Demandes d'adhésion depuis le formulaire public.

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identifiant unique |
| nom | VARCHAR(100) | NOT NULL | Nom |
| prenom | VARCHAR(100) | NOT NULL | Prénom |
| email | VARCHAR(255) | NOT NULL | Email |
| telephone | VARCHAR(20) | | Téléphone |
| activite_souhaitee | VARCHAR(100) | | Activité demandée |
| message | TEXT | | Message libre |
| date_demande | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Date de la demande |
| statut | ENUM | DEFAULT 'en_attente' | en_attente, traitee, refusee |

### 8.2. Relations entre les tables
- Un **utilisateur** peut être lié à un **adhérent** ou un **animateur** (1:1)
- Un **animateur** encadre plusieurs **activités** (1:N)
- Une **activité** a un **animateur** (N:1)
- Un **adhérent** peut s'inscrire à plusieurs **activités** (N:M via inscriptions)
- Une **activité** peut avoir plusieurs **adhérents** inscrits (N:M via inscriptions)

---

## 9. MAQUETTES

Les maquettes sont à réaliser avec un outil comme :
- Figma
- Penpot (open source)
- Excalidraw
- Draw.io
- Papier/crayon puis scan

### Pages à maquetter
1. Page d'accueil
2. Page des activités
3. Page de connexion
4. Page d'inscription (formulaire)
5. Page "Mes inscriptions"
6. Page d'administration (optionnel)

---

## 10. LIVRABLES ATTENDUS

### 10.1. Livrables techniques
1. **Application web fonctionnelle**
   - Code source complet
   - Application déployée sur serveur local
   - Accessible via navigateur

2. **Base de données**
   - Script SQL de création (`schema.sql`)
   - Données de démonstration incluses
   - Documentation du modèle de données

3. **Code source sur GitHub**
   - Repository organisé
   - README.md complet
   - Commits réguliers et pertinents

### 10.2. Livrables documentaires
1. **Cahier des charges** (ce document)
   - Analyse du besoin
   - Spécifications fonctionnelles et techniques
   
2. **Schéma réseau**
   - Architecture client-serveur
   - Protocoles et ports utilisés
   
3. **Modèle de données**
   - Schéma entité-association ou diagramme de classes
   - Description des tables
   
4. **Maquettes**
   - Maquettes des principales pages
   - Charte graphique (couleurs, typographie)
   
5. **Documentation utilisateur**
   - Guide d'utilisation pour chaque profil
   - FAQ
   
6. **Documentation technique**
   - Guide d'installation
   - Architecture du code
   - API (si applicable)

### 10.3. Présentation finale
- Support de présentation (PowerPoint, PDF, etc.)
- Démonstration de l'application
- Durée : 15-20 minutes
- Questions/réponses : 5-10 minutes

---

## 11. ORGANISATION DU PROJET

### 11.1. Rôles de l'équipe
(À compléter selon la composition de votre équipe)

| Membre | Rôle(s) | Responsabilités |
|--------|---------|-----------------|
| | Chef de projet | Coordination, planning, suivi |
| | Développeur Backend | PHP, BDD, logique métier |
| | Développeur Frontend | HTML, CSS, JS, interface |
| | Designer | Maquettes, charte graphique |
| | Testeur | Tests, validation, débogage |

*Note : Les rôles peuvent se chevaucher selon la taille de l'équipe.*

### 11.2. Méthodologie
- **Méthode** : Agile (Scrum simplifié)
- **Sprints** : 1-2 semaines
- **Réunions** : Stand-up quotidiens ou hebdomadaires
- **Outils** :
  - Gestion de projet : Trello, Notion, GitHub Projects
  - Versioning : Git + GitHub
  - Communication : Discord, Slack, Teams

### 11.3. Planning prévisionnel

| Phase | Durée | Tâches principales |
|-------|-------|-------------------|
| **Analyse** | 1 semaine | Analyse du besoin, cahier des charges, maquettes |
| **Conception** | 1 semaine | Modèle de données, architecture, schémas |
| **Développement** | 3-4 semaines | Codage de l'application (backend + frontend) |
| **Tests** | 1 semaine | Tests fonctionnels, correction de bugs |
| **Documentation** | En continu | Rédaction de la documentation |
| **Présentation** | 1 semaine | Préparation de la soutenance |

**Durée totale estimée** : 6-8 semaines

---

## 12. CRITÈRES DE VALIDATION

Le projet sera validé selon les critères suivants :

### 12.1. Fonctionnement global (40%)
- L'application fonctionne sans erreurs critiques
- Toutes les fonctionnalités minimales sont implémentées
- Navigation fluide et intuitive
- Responsive design fonctionnel

### 12.2. Base de données (20%)
- Schéma cohérent et normalisé
- Données de test pertinentes
- Requêtes SQL optimisées
- Intégrité référentielle respectée

### 12.3. Qualité du code (20%)
- Code propre et commenté
- Architecture claire (séparation des responsabilités)
- Sécurité implémentée (hash, requêtes préparées)
- Gestion des erreurs

### 12.4. Documentation (10%)
- Cahier des charges complet
- Schémas clairs et pertinents
- README.md détaillé
- Documentation technique

### 12.5. Présentation (10%)
- Clarté de l'exposé
- Démonstration convaincante
- Maîtrise du sujet
- Réponses aux questions

---

## 13. OUTILS ET RESSOURCES

### 13.1. Outils de développement
- **IDE** : VS Code, PhpStorm
- **Serveur local** : DDEV, XAMPP, WAMP, MAMP
- **Versioning** : Git + GitHub
- **BDD** : MySQL Workbench, PhpMyAdmin

### 13.2. Outils de conception
- **Maquettes** : Figma, Penpot, Excalidraw
- **Schémas** : Draw.io, Lucidchart, dbdiagram.io
- **Gestion de projet** : Trello, Notion, Asana

### 13.3. Ressources documentaires
- Documentation PHP : php.net
- W3Schools : HTML, CSS, JavaScript
- MDN Web Docs : Ressources web
- Stack Overflow : Résolution de problèmes

---

## 14. DONNÉES INITIALES FOURNIES

### Activités
1. **Fitness** - Julie Fort - Lundi 18h00
2. **Zumba** - Rachelle Leroy - Mardi 19h00
3. **Yoga** - Caroline Petit - Jeudi 18h30
4. **Renforcement musculaire** - Mathilde Rey - Vendredi 19h00

### Adhérents exemples
1. Dupont Bertille - bertille.dupont@gmail.com - Yoga
2. Bernard Lucas - lucas.bernard@outlook.fr - Fitness
3. Roux Alexandre - alexandre.roux@yahoo.fr - Zumba

---

## 15. ÉVOLUTIONS FUTURES

Après validation du MVP, les évolutions suivantes pourront être envisagées :

### Court terme
- Envoi d'emails automatiques
- Système de récupération de mot de passe
- Filtres et recherche dans les listes
- Export PDF des documents

### Moyen terme
- Paiement en ligne des cotisations
- Système de présence/absence
- Calendrier interactif
- Statistiques avancées

### Long terme
- Application mobile native (React Native, Flutter)
- API REST pour services tiers
- Système de notation des cours
- Gestion multi-sites

---

## VALIDATION DU CAHIER DES CHARGES

**Client** : Association Fit&Fun  
**Représentant** : Julie Fort, Présidente  
**Date** : ___/___/______  
**Signature** : ________________

**Équipe projet**  
**Chef de projet** : ________________  
**Date** : ___/___/______  
**Signature** : ________________

---

*Document rédigé dans le cadre du projet BTS SIO - Applications Web*
