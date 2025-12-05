<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Classe Animateur - Gestion des animateurs
 */
class Animateur {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer un nouvel animateur
     */
    public function creer($donnees) {
        $sql = "INSERT INTO animateurs (utilisateur_id, nom, prenom, email, telephone, specialite) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['utilisateur_id'] ?? null,
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['telephone'] ?? null,
            $donnees['specialite'] ?? null
        ]);
    }
    
    /**
     * Récupérer tous les animateurs
     */
    public function getTous() {
        $sql = "SELECT a.*, u.role FROM animateurs a 
                LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
                ORDER BY a.nom, a.prenom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un animateur par ID
     */
    public function getParId($id) {
        $stmt = $this->db->prepare("SELECT * FROM animateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer un animateur par utilisateur_id
     */
    public function getParUtilisateurId($utilisateurId) {
        $stmt = $this->db->prepare("SELECT * FROM animateurs WHERE utilisateur_id = ?");
        $stmt->execute([$utilisateurId]);
        return $stmt->fetch();
    }
    
    /**
     * Mettre à jour un animateur
     */
    public function modifier($id, $donnees) {
        $sql = "UPDATE animateurs SET nom = ?, prenom = ?, email = ?, telephone = ?, specialite = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['telephone'] ?? null,
            $donnees['specialite'] ?? null,
            $id
        ]);
    }
    
    /**
     * Supprimer un animateur
     */
    public function supprimer($id) {
        $stmt = $this->db->prepare("DELETE FROM animateurs WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter le nombre d'animateurs
     */
    public function compter() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM animateurs");
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Récupérer les activités d'un animateur
     */
    public function getActivites($animateurId) {
        $sql = "SELECT * FROM activites WHERE animateur_id = ? AND statut = 'active' ORDER BY jour_semaine, heure_debut";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$animateurId]);
        return $stmt->fetchAll();
    }
}
