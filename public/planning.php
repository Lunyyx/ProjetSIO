<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Activite.php';

$activiteModel = new Activite();
$activites = $activiteModel->getTous();

// Organiser les activités par jour
$joursOrdre = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$planning = [];

foreach ($joursOrdre as $jour) {
    $planning[$jour] = [];
}

foreach ($activites as $activite) {
    $planning[$activite['jour_semaine']][] = $activite;
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <h1>Planning des activités</h1>
    
    <div class="planning-container">
        <?php foreach ($joursOrdre as $jour): ?>
            <?php if (!empty($planning[$jour])): ?>
                <div class="planning-day">
                    <h2><?php echo $jour; ?></h2>
                    <div class="planning-activities">
                        <?php foreach ($planning[$jour] as $activite): ?>
                            <?php $nbInscrits = $activiteModel->getNombreInscrits($activite['id']); ?>
                            <div class="planning-activity">
                                <div class="planning-time">
                                    <?php echo substr($activite['heure_debut'], 0, 5); ?>
                                </div>
                                <div class="planning-details">
                                    <h3><?php echo e($activite['nom']); ?></h3>
                                    <p>
                                        <strong>Animateur :</strong> <?php echo e($activite['animateur_nom']); ?><br>
                                        <?php if ($activite['lieu']): ?>
                                            <strong>Lieu :</strong> <?php echo e($activite['lieu']); ?><br>
                                        <?php endif; ?>
                                        <strong>Inscrits :</strong> <?php echo $nbInscrits; ?> / <?php echo $activite['capacite_max']; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <div class="planning-footer">
        <p>
            <?php if (estConnecte() && aLeDroit('adherent')): ?>
                Consultez vos <a href="/mes-inscriptions.php">inscriptions</a> ou découvrez toutes nos <a href="/activites.php">activités</a>.
            <?php else: ?>
                <a href="/inscription.php">Inscrivez-vous</a> pour participer à nos activités !
            <?php endif; ?>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
