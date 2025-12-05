<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Classe Adherent - Gestion des adhérents
 */
class Adherent {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer un nouvel adhérent
     */
    public function creer($donnees) {
        $sql = "INSERT INTO adherents (utilisateur_id, nom, prenom, email, telephone, adresse, date_naissance, cotisation_payee) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['utilisateur_id'] ?? null,
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['telephone'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['date_naissance'] ?? null,
            $donnees['cotisation_payee'] ?? false
        ]);
    }
    
    /**
     * Récupérer tous les adhérents
     */
    public function getTous() {
        $sql = "SELECT a.*, u.role FROM adherents a 
                LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
                ORDER BY a.nom, a.prenom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un adhérent par ID
     */
    public function getParId($id) {
        $stmt = $this->db->prepare("SELECT * FROM adherents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer un adhérent par utilisateur_id
     */
    public function getParUtilisateurId($utilisateurId) {
        $stmt = $this->db->prepare("SELECT * FROM adherents WHERE utilisateur_id = ?");
        $stmt->execute([$utilisateurId]);
        return $stmt->fetch();
    }
    
    /**
     * Mettre à jour un adhérent
     */
    public function modifier($id, $donnees) {
        $sql = "UPDATE adherents SET nom = ?, prenom = ?, email = ?, telephone = ?, 
                adresse = ?, date_naissance = ?, cotisation_payee = ?, statut = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['telephone'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['date_naissance'] ?? null,
            $donnees['cotisation_payee'] ?? false,
            $donnees['statut'] ?? 'actif',
            $id
        ]);
    }
    
    /**
     * Supprimer un adhérent
     */
    public function supprimer($id) {
        $stmt = $this->db->prepare("DELETE FROM adherents WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Compter le nombre d'adhérents actifs
     */
    public function compterActifs() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM adherents WHERE statut = 'actif'");
        $result = $stmt->fetch();
        return $result['total'];
    }

    // ===============================
    // MÉTHODES STATIQUES
    // ===============================

    /**
     * Récupérer un adhérent par utilisateur_id (statique)
     */
    public static function getParUtilisateurIdStatic($utilisateurId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM adherents WHERE utilisateur_id = ?");
        $stmt->execute([$utilisateurId]);
        return $stmt->fetch();
    }

    /**
     * Créer un nouvel adhérent (statique)
     */
    public static function creerStatic($donnees) {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO adherents (utilisateur_id, nom, prenom, email, telephone, adresse, date_naissance, cotisation_payee) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $donnees['utilisateur_id'] ?? null,
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'] ?? '',
            $donnees['telephone'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['date_naissance'] ?? null,
            $donnees['cotisation_payee'] ?? false
        ]);
        return $db->lastInsertId();
    }
}
