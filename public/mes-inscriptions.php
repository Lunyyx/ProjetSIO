<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Inscription.php';
require_once __DIR__ . '/../src/models/Adherent.php';

// Vérifier que l'utilisateur est connecté et est adhérent
if (!estConnecte() || !aLeDroit('adherent')) {
    setMessage('Vous devez être connecté en tant qu\'adhérent pour accéder à cette page.', 'danger');
    rediriger('/login.php');
}

$adherentModel = new Adherent();
$inscriptionModel = new Inscription();

$adherent = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);

if (!$adherent) {
    setMessage('Profil adhérent non trouvé.', 'danger');
    rediriger('/');
}

// Gérer la désinscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desinscrire'])) {
    $activiteId = $_POST['activite_id'] ?? null;
    
    if ($activiteId && $inscriptionModel->supprimer($adherent['id'], $activiteId)) {
        setMessage('Vous avez été désinscrit de cette activité.', 'success');
    } else {
        setMessage('Erreur lors de la désinscription.', 'danger');
    }
    
    rediriger('/mes-inscriptions.php');
}

$inscriptions = $inscriptionModel->getParAdherent($adherent['id']);

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <h1>Mes inscriptions</h1>
    
    <div class="profile-info">
        <h2>Mon profil</h2>
        <p>
            <strong>Nom :</strong> <?php echo e($adherent['prenom'] . ' ' . $adherent['nom']); ?><br>
            <strong>Email :</strong> <?php echo e($adherent['email']); ?><br>
            <?php if ($adherent['telephone']): ?>
                <strong>Téléphone :</strong> <?php echo e($adherent['telephone']); ?><br>
            <?php endif; ?>
            <strong>Cotisation :</strong> 
            <?php if ($adherent['cotisation_payee']): ?>
                <span class="badge badge-success">Payée</span>
            <?php else: ?>
                <span class="badge badge-warning">Non payée</span>
            <?php endif; ?>
        </p>
    </div>
    
    <?php if (empty($inscriptions)): ?>
        <div class="alert alert-info">
            <p>Vous n'êtes inscrit à aucune activité pour le moment.</p>
            <a href="/activites.php" class="btn btn-primary">Découvrir nos activités</a>
        </div>
    <?php else: ?>
        <h2>Activités auxquelles je suis inscrit</h2>
        <div class="inscriptions-list">
            <?php foreach ($inscriptions as $inscription): ?>
                <div class="inscription-card">
                    <h3><?php echo e($inscription['activite_nom']); ?></h3>
                    <div class="inscription-details">
                        <p>
                            <strong>📅 Jour :</strong> <?php echo e($inscription['jour_semaine']); ?><br>
                            <strong>⏰ Heure :</strong> <?php echo substr($inscription['heure_debut'], 0, 5); ?><br>
                            <strong>👤 Animateur :</strong> <?php echo e($inscription['animateur_nom']); ?><br>
                            <?php if ($inscription['lieu']): ?>
                                <strong>📍 Lieu :</strong> <?php echo e($inscription['lieu']); ?><br>
                            <?php endif; ?>
                            <strong>📆 Inscrit le :</strong> <?php echo date('d/m/Y', strtotime($inscription['date_inscription'])); ?>
                        </p>
                    </div>
                    <form method="POST" action="/mes-inscriptions.php" onsubmit="return confirm('Êtes-vous sûr de vouloir vous désinscrire de cette activité ?');">
                        <input type="hidden" name="activite_id" value="<?php echo $inscription['activite_id']; ?>">
                        <input type="hidden" name="desinscrire" value="1">
                        <button type="submit" class="btn btn-danger btn-small">Se désinscrire</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="/activites.php" class="btn btn-primary">Découvrir d'autres activités</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
