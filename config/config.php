<?php
/**
 * Configuration générale de l'application
 * Fit&Fun - Association sportive
 */

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de l'application
define('APP_NAME', 'Fit&Fun');
define('APP_URL', 'http://projetsio.ddev.site');
define('APP_VERSION', '1.0.0');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Inclusion de la configuration de la base de données
require_once __DIR__ . '/database.php';

// Gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Paris');

/**
 * Fonction d'autoload simple pour charger les classes
 */
spl_autoload_register(function ($class) {
    $file = SRC_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Fonction pour vérifier si l'utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

/**
 * Fonction pour vérifier le rôle de l'utilisateur
 */
function aLeDroit($role) {
    if (!estConnecte()) {
        return false;
    }
    
    $roles = ['visiteur', 'adherent', 'animateur', 'bureau'];
    $roleUtilisateur = $_SESSION['role'] ?? 'visiteur';
    
    return array_search($roleUtilisateur, $roles) >= array_search($role, $roles);
}

/**
 * Fonction pour rediriger
 */
function rediriger($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Fonction pour échapper les données HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Fonction pour afficher les messages flash
 */
function afficherMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        echo '<div class="alert alert-' . $type . '">' . e($message) . '</div>';
    }
}

/**
 * Fonction pour définir un message flash
 */
function setMessage($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}
