<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Activite.php';
require_once __DIR__ . '/../src/models/Inscription.php';
require_once __DIR__ . '/../src/models/Adherent.php';

$activiteModel = new Activite();
$inscriptionModel = new Inscription();

// Récupérer toutes les activités
$activites = $activiteModel->getTous();

// Gérer l'inscription à une activité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && estConnecte() && aLeDroit('adherent')) {
    $activiteId = $_POST['activite_id'] ?? null;
    
    if ($activiteId) {
        $adherentModel = new Adherent();
        $adherent = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);
        
        if ($adherent) {
            // Vérifier si l'activité n'est pas complète
            if (!$activiteModel->estComplete($activiteId)) {
                if ($inscriptionModel->inscrire($adherent['id'], $activiteId)) {
                    setMessage('Vous êtes maintenant inscrit à cette activité !', 'success');
                } else {
                    setMessage('Vous êtes déjà inscrit à cette activité.', 'warning');
                }
            } else {
                setMessage('Cette activité est complète.', 'danger');
            }
        }
    }
    
    rediriger('/activites.php');
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <h1>Nos activités</h1>
    
    <div class="activities-list">
        <?php foreach ($activites as $activite): ?>
            <?php
                $nbInscrits = $activiteModel->getNombreInscrits($activite['id']);
                $estComplete = $nbInscrits >= $activite['capacite_max'];
                $adherent = null;
                $estInscrit = false;
                
                if (estConnecte() && aLeDroit('adherent')) {
                    $adherentModel = new Adherent();
                    $adherent = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);
                    if ($adherent) {
                        $estInscrit = $inscriptionModel->estInscrit($adherent['id'], $activite['id']);
                    }
                }
            ?>
            
            <div class="activity-detail-card <?php echo $estComplete ? 'complete' : ''; ?>">
                <div class="activity-header">
                    <h2><?php echo e($activite['nom']); ?></h2>
                    <?php if ($estComplete): ?>
                        <span class="badge badge-danger">Complet</span>
                    <?php elseif ($estInscrit): ?>
                        <span class="badge badge-success">Inscrit</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($activite['description']): ?>
                    <p class="activity-description"><?php echo e($activite['description']); ?></p>
                <?php endif; ?>
                
                <div class="activity-info">
                    <div class="info-item">
                        <strong>📅 Jour :</strong> <?php echo e($activite['jour_semaine']); ?>
                    </div>
                    <div class="info-item">
                        <strong>⏰ Horaire :</strong> 
                        <?php echo substr($activite['heure_debut'], 0, 5); ?>
                        <?php if ($activite['heure_fin']): ?>
                            - <?php echo substr($activite['heure_fin'], 0, 5); ?>
                        <?php endif; ?>
                    </div>
                    <div class="info-item">
                        <strong>⏱️ Durée :</strong> <?php echo $activite['duree_minutes']; ?> minutes
                    </div>
                    <div class="info-item">
                        <strong>👤 Animateur :</strong> <?php echo e($activite['animateur_nom']); ?>
                    </div>
                    <?php if ($activite['lieu']): ?>
                        <div class="info-item">
                            <strong>📍 Lieu :</strong> <?php echo e($activite['lieu']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <strong>👥 Inscrits :</strong> <?php echo $nbInscrits; ?> / <?php echo $activite['capacite_max']; ?>
                    </div>
                </div>
                
                <?php if (estConnecte() && aLeDroit('adherent') && !$estInscrit && !$estComplete): ?>
                    <form method="POST" action="/activites.php" class="inline-form">
                        <input type="hidden" name="activite_id" value="<?php echo $activite['id']; ?>">
                        <button type="submit" class="btn btn-primary">S'inscrire à cette activité</button>
                    </form>
                <?php elseif (!estConnecte()): ?>
                    <p class="text-muted">
                        <a href="/login.php">Connectez-vous</a> pour vous inscrire à cette activité.
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
