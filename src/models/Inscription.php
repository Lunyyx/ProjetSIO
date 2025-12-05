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

    /**
     * Récupérer toutes les inscriptions (pour admin)
     */
    public static function getToutesLesInscriptions($filtreActivite = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT i.*, 
                a.nom as activite_nom, a.jour_semaine, a.heure_debut,
                CONCAT(ad.prenom, ' ', ad.nom) as adherent_nom,
                ad.email as adherent_email
                FROM inscriptions i
                JOIN activites a ON i.activite_id = a.id
                JOIN adherents ad ON i.adherent_id = ad.id";
        
        $params = [];
        if ($filtreActivite && $filtreActivite !== 'toutes') {
            $sql .= " WHERE i.activite_id = ?";
            $params[] = $filtreActivite;
        }
        
        $sql .= " ORDER BY i.date_inscription DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Annuler une inscription par ID
     */
    public static function annulerParId($inscriptionId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE inscriptions SET statut = 'annulee' WHERE id = ?");
        return $stmt->execute([$inscriptionId]);
    }

    // ===============================
    // GESTION DES DEMANDES D'INSCRIPTION AU CLUB
    // ===============================

    /**
     * Récupérer les demandes d'inscription au club
     */
    public static function getDemandesInscription($filtreStatut = 'tous') {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM demandes_inscription";
        $params = [];
        
        if ($filtreStatut !== 'tous') {
            $statut = $filtreStatut;
            if ($filtreStatut === 'validee') $statut = 'traitee';
            $sql .= " WHERE statut = ?";
            $params[] = $statut;
        }
        
        $sql .= " ORDER BY date_demande DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer une demande par ID
     */
    public static function getDemandeParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM demandes_inscription WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Valider une demande d'inscription
     */
    public static function validerDemande($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE demandes_inscription SET statut = 'traitee' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Refuser une demande d'inscription
     */
    public static function refuserDemande($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE demandes_inscription SET statut = 'refusee' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Supprimer une demande d'inscription
     */
    public static function supprimerDemande($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM demandes_inscription WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Compter les demandes en attente
     */
    public static function compterDemandesEnAttente() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as total FROM demandes_inscription WHERE statut = 'en_attente'");
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Créer une nouvelle demande d'inscription
     */
    public static function creerDemande($donnees) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO demandes_inscription (nom, prenom, email, telephone, activite_souhaitee, message) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            $donnees['telephone'] ?? null,
            $donnees['activite_souhaitee'] ?? null,
            $donnees['message'] ?? null
        ]);
    }
}
