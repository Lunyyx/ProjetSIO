<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Classe Activite - Gestion des activités
 */
class Activite {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Récupérer toutes les activités avec les informations des animateurs
     */
    public function getTous() {
        $sql = "SELECT a.*, 
                CONCAT(an.prenom, ' ', an.nom) as animateur_nom,
                an.email as animateur_email
                FROM activites a
                LEFT JOIN animateurs an ON a.animateur_id = an.id
                WHERE a.statut = 'active'
                ORDER BY 
                    FIELD(a.jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
                    a.heure_debut";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une activité par ID
     */
    public function getParId($id) {
        $sql = "SELECT a.*, 
                CONCAT(an.prenom, ' ', an.nom) as animateur_nom,
                an.email as animateur_email
                FROM activites a
                LEFT JOIN animateurs an ON a.animateur_id = an.id
                WHERE a.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Créer une nouvelle activité
     */
    public function creer($donnees) {
        $sql = "INSERT INTO activites (nom, description, animateur_id, jour_semaine, heure_debut, heure_fin, duree_minutes, capacite_max, lieu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['nom'],
            $donnees['description'] ?? null,
            $donnees['animateur_id'],
            $donnees['jour_semaine'],
            $donnees['heure_debut'],
            $donnees['heure_fin'] ?? null,
            $donnees['duree_minutes'] ?? 60,
            $donnees['capacite_max'] ?? 20,
            $donnees['lieu'] ?? null
        ]);
    }
    
    /**
     * Modifier une activité
     */
    public function modifier($id, $donnees) {
        $sql = "UPDATE activites SET nom = ?, description = ?, animateur_id = ?, 
                jour_semaine = ?, heure_debut = ?, heure_fin = ?, duree_minutes = ?, 
                capacite_max = ?, lieu = ?, statut = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $donnees['nom'],
            $donnees['description'] ?? null,
            $donnees['animateur_id'],
            $donnees['jour_semaine'],
            $donnees['heure_debut'],
            $donnees['heure_fin'] ?? null,
            $donnees['duree_minutes'] ?? 60,
            $donnees['capacite_max'] ?? 20,
            $donnees['lieu'] ?? null,
            $donnees['statut'] ?? 'active',
            $id
        ]);
    }
    
    /**
     * Supprimer une activité
     */
    public function supprimer($id) {
        $stmt = $this->db->prepare("DELETE FROM activites WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupérer le nombre d'inscrits pour une activité
     */
    public function getNombreInscrits($activiteId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE activite_id = ? AND statut = 'active'");
        $stmt->execute([$activiteId]);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Vérifier si une activité est complète
     */
    public function estComplete($activiteId) {
        $activite = $this->getParId($activiteId);
        $nbInscrits = $this->getNombreInscrits($activiteId);
        
        return $nbInscrits >= $activite['capacite_max'];
    }
}
