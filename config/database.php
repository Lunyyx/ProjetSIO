<?php
/**
 * Configuration de la base de données
 * Fit&Fun - Association sportive
 */

// Charger l'autoloader Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Charger les variables d'environnement depuis .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Définir les constantes à partir des variables d'environnement
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

/**
 * Classe Database - Gestion de la connexion PDO
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    /**
     * Récupère l'instance unique de la connexion (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Récupère la connexion PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Empêche le clonage de l'instance
     */
    private function __clone() {}
    
    /**
     * Empêche la désérialisation de l'instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
