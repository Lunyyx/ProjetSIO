# Guide d'installation - Fit&Fun

## Méthode 1 : Installation avec DDEV (Recommandée)

### Prérequis
- Docker installé
- DDEV installé ([https://ddev.readthedocs.io/](https://ddev.readthedocs.io/))

### Étapes d'installation

1. **Cloner le projet**
```bash
cd /chemin/vers/votre/workspace
git clone <url-du-repo> ProjetSIO
cd ProjetSIO
```

2. **Démarrer DDEV**
```bash
ddev start
```

3. **Importer la base de données**
```bash
ddev import-db --src=database/schema.sql
```

Ou manuellement via PhpMyAdmin :
- Accéder à PhpMyAdmin : https://projetsio.ddev.site:8036
- Se connecter avec : `db` / `db`
- Créer la base `fitandfun`
- Importer le fichier `database/schema.sql`

4. **Accéder à l'application**
- Site web : https://projetsio.ddev.site
- PhpMyAdmin : https://projetsio.ddev.site:8036

### Commandes DDEV utiles

```bash
# Démarrer le projet
ddev start

# Arrêter le projet
ddev stop

# Redémarrer le projet
ddev restart

# Voir les informations du projet
ddev describe

# Accéder au shell du conteneur web
ddev ssh

# Voir les logs
ddev logs

# Exporter la base de données
ddev export-db > backup.sql
```

---

## Méthode 2 : Installation avec XAMPP/WAMP/MAMP

### Prérequis
- XAMPP, WAMP ou MAMP installé
- PHP 7.4+ activé
- MySQL/MariaDB activé

### Étapes d'installation

1. **Copier le projet**
```bash
# Copier le dossier ProjetSIO dans le dossier web du serveur
# XAMPP : C:\xampp\htdocs\ProjetSIO
# WAMP : C:\wamp64\www\ProjetSIO
# MAMP : /Applications/MAMP/htdocs/ProjetSIO
```

2. **Créer la base de données**
- Ouvrir PhpMyAdmin : http://localhost/phpmyadmin
- Créer une nouvelle base de données nommée `fitandfun`
- Importer le fichier `database/schema.sql`

3. **Configurer la connexion à la base**

Modifier le fichier `config/database.php` :
```php
define('DB_HOST', 'localhost');  // ou 127.0.0.1
define('DB_NAME', 'fitandfun');
define('DB_USER', 'root');       // votre utilisateur MySQL
define('DB_PASS', '');           // votre mot de passe MySQL
```

4. **Accéder à l'application**
- Ouvrir : http://localhost/ProjetSIO/public/

---

## Méthode 3 : Installation avec serveur PHP intégré

### Prérequis
- PHP 7.4+ installé
- MySQL/MariaDB installé

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone <url-du-repo> ProjetSIO
cd ProjetSIO
```

2. **Créer la base de données**
```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base et importer
CREATE DATABASE fitandfun CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitandfun;
SOURCE database/schema.sql;
EXIT;
```

3. **Configurer la connexion**

Modifier `config/database.php` avec vos paramètres MySQL.

4. **Démarrer le serveur PHP**
```bash
cd public
php -S localhost:8000
```

5. **Accéder à l'application**
- Ouvrir : http://localhost:8000

---

## Vérification de l'installation

### 1. Tester la connexion à la base de données

Créer un fichier `test-db.php` dans le dossier `public/` :
```php
<?php
require_once __DIR__ . '/../config/config.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Connexion à la base de données réussie !<br>";
    
    // Compter les utilisateurs
    $stmt = $db->query("SELECT COUNT(*) as total FROM utilisateurs");
    $result = $stmt->fetch();
    echo "Nombre d'utilisateurs : " . $result['total'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
```

Accéder à : http://votresite/test-db.php

### 2. Tester les comptes de connexion

Essayer de se connecter avec :
- Email : `admin@fitandfun.fr`
- Mot de passe : `password`

Si la connexion fonctionne, l'installation est réussie !

---

## Résolution des problèmes courants

### Erreur : "Could not connect to database"
**Solution** : Vérifier les identifiants dans `config/database.php`

### Erreur : "Table doesn't exist"
**Solution** : Importer le fichier `database/schema.sql`

### Erreur 404 sur les pages
**Solution** : Vérifier que vous accédez au dossier `public/`

### Les styles CSS ne s'affichent pas
**Solution** : Vérifier les chemins dans les fichiers PHP
- Les chemins doivent être absolus : `/assets/css/style.css`

### Sessions ne fonctionnent pas
**Solution** : Vérifier que PHP peut écrire dans le dossier de sessions
```bash
# Linux
sudo chmod 777 /var/lib/php/sessions

# Ou dans php.ini
session.save_path = "/tmp"
```

### Erreur : "Headers already sent"
**Solution** : Supprimer tout espace/caractère avant `<?php` dans les fichiers

---

## Configuration avancée

### Configuration SSL (HTTPS)

Pour DDEV :
```bash
# HTTPS est automatiquement configuré
# Certificat : ~/.ddev/traefik/certs
```

Pour Apache :
```apache
<VirtualHost *:443>
    ServerName projetsio.local
    DocumentRoot "/chemin/vers/ProjetSIO/public"
    
    SSLEngine on
    SSLCertificateFile "/chemin/vers/cert.crt"
    SSLCertificateKeyFile "/chemin/vers/cert.key"
</VirtualHost>
```

### Configuration du Virtual Host

Pour Apache (`httpd.conf` ou `httpd-vhosts.conf`) :
```apache
<VirtualHost *:80>
    ServerName projetsio.local
    DocumentRoot "/chemin/vers/ProjetSIO/public"
    
    <Directory "/chemin/vers/ProjetSIO/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Ajouter dans `/etc/hosts` (Linux/Mac) ou `C:\Windows\System32\drivers\etc\hosts` (Windows) :
```
127.0.0.1 projetsio.local
```

### Configuration `.htaccess`

Créer `.htaccess` dans `public/` pour la réécriture d'URL :
```apache
RewriteEngine On

# Rediriger vers index.php si le fichier n'existe pas
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## Mise en production

### Checklist avant déploiement

- [ ] Désactiver l'affichage des erreurs
  ```php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```
- [ ] Changer les mots de passe par défaut
- [ ] Configurer HTTPS
- [ ] Sauvegarder la base de données
- [ ] Vérifier les permissions des fichiers
- [ ] Tester tous les formulaires
- [ ] Supprimer les fichiers de test

---

## Support

Pour toute question ou problème :
- Consulter le README.md
- Vérifier la documentation DDEV : https://ddev.readthedocs.io/
- Consulter la documentation PHP : https://www.php.net/

---

*Guide d'installation - Projet BTS SIO*
