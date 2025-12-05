<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Classe Utilisateur - Gestion des utilisateurs
 */
class Utilisateur {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function connexion($email, $motDePasse) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch();
        
        if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['role'] = $utilisateur['role'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function inscription($email, $motDePasse, $role = 'adherent') {
        $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $motDePasseHash, $role]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Déconnexion
     */
    public function deconnexion() {
        session_destroy();
    }
    
    /**
     * Récupérer un utilisateur par ID
     */
    public function getParId($id) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer tous les utilisateurs
     */
    public function getTous() {
        $stmt = $this->db->query("SELECT * FROM utilisateurs ORDER BY date_creation DESC");
        return $stmt->fetchAll();
    }
}
