<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Classe Inscription - Gestion des inscriptions aux activités
 */
class Inscription {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Inscrire un adhérent à une activité
     */
    public function inscrire($adherentId, $activiteId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO inscriptions (adherent_id, activite_id) VALUES (?, ?)");
            return $stmt->execute([$adherentId, $activiteId]);
        } catch (PDOException $e) {
            // Inscription déjà existante
            return false;
        }
    }
    
    /**
     * Annuler une inscription
     */
    public function annuler($adherentId, $activiteId) {
        $stmt = $this->db->prepare("UPDATE inscriptions SET statut = 'annulee' WHERE adherent_id = ? AND activite_id = ?");
        return $stmt->execute([$adherentId, $activiteId]);
    }
    
    /**
     * Supprimer une inscription
     */
    public function supprimer($adherentId, $activiteId) {
        $stmt = $this->db->prepare("DELETE FROM inscriptions WHERE adherent_id = ? AND activite_id = ?");
        return $stmt->execute([$adherentId, $activiteId]);
    }
    
    /**
     * Récupérer les inscriptions d'un adhérent
     */
    public function getParAdherent($adherentId) {
        $sql = "SELECT i.*, a.nom as activite_nom, a.jour_semaine, a.heure_debut, a.lieu,
                CONCAT(an.prenom, ' ', an.nom) as animateur_nom
                FROM inscriptions i
                JOIN activites a ON i.activite_id = a.id
                LEFT JOIN animateurs an ON a.animateur_id = an.id
                WHERE i.adherent_id = ? AND i.statut = 'active'
                ORDER BY FIELD(a.jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
                a.heure_debut";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adherentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les adhérents inscrits à une activité
     */
    public function getParActivite($activiteId) {
        $sql = "SELECT i.*, 
                CONCAT(a.prenom, ' ', a.nom) as adherent_nom,
                a.email as adherent_email,
                a.telephone as adherent_telephone
                FROM inscriptions i
                JOIN adherents a ON i.adherent_id = a.id
                WHERE i.activite_id = ? AND i.statut = 'active'
                ORDER BY a.nom, a.prenom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$activiteId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifier si un adhérent est inscrit à une activité
     */
    public function estInscrit($adherentId, $activiteId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE adherent_id = ? AND activite_id = ? AND statut = 'active'");
        $stmt->execute([$adherentId, $activiteId]);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
}
