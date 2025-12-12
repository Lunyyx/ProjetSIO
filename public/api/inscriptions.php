<?php
/**
 * API pour vérifier les inscriptions d'un adhérent
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Inscription.php';
require_once __DIR__ . '/../../src/models/Adherent.php';

$response = ['inscrit' => false, 'inscriptions' => []];

if (estConnecte() && ($_SESSION['role'] ?? '') === 'adherent') {
    $adherentModel = new Adherent();
    $adherent = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);
    
    if ($adherent) {
        $inscriptionModel = new Inscription();
        $inscriptions = $inscriptionModel->getParAdherent($adherent['id']);
        
        // Extraire les IDs des activités
        $activiteIds = array_column($inscriptions, 'activite_id');
        
        $response['inscriptions'] = $activiteIds;
        
        // Si on demande une activité spécifique
        if (isset($_GET['activite_id'])) {
            $activiteId = (int)$_GET['activite_id'];
            $response['inscrit'] = in_array($activiteId, $activiteIds);
        }
    }
}

echo json_encode($response);
