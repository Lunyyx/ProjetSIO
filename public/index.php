<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Activite.php';

$activiteModel = new Activite();
$activites = $activiteModel->getTous();

include __DIR__ . '/../src/includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1>Bienvenue chez <?php echo APP_NAME; ?></h1>
        <p>Votre association sportive à Dijon</p>
        <a href="/inscription.php" class="btn btn-primary btn-large">Rejoignez-nous !</a>
    </div>
</div>

<?php afficherMessage(); ?>

<section class="section">
    <div class="container">
        <h2>Qui sommes-nous ?</h2>
        <div class="about-grid">
            <div class="about-card">
                <h3>📍 Notre localisation</h3>
                <p>12 rue Vaillant<br>21000 Dijon</p>
            </div>
            <div class="about-card">
                <h3>👥 Notre communauté</h3>
                <p>Plus de 120 adhérents<br>4 animateurs qualifiés</p>
            </div>
            <div class="about-card">
                <h3>🎯 Notre mission</h3>
                <p>Promouvoir le sport et le bien-être<br>dans une ambiance conviviale</p>
            </div>
        </div>
        
        <div class="description">
            <p>
                Fit&Fun est une association sportive loi 1901 créée pour permettre à tous de pratiquer 
                une activité physique dans un cadre agréable et accessible. Nous proposons des cours 
                variés adaptés à tous les niveaux, encadrés par des professionnels passionnés.
            </p>
        </div>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        <h2>Nos activités</h2>
        <div class="activities-grid">
            <?php foreach ($activites as $activite): ?>
                <div class="activity-card">
                    <h3><?php echo e($activite['nom']); ?></h3>
                    <?php if ($activite['description']): ?>
                        <p><?php echo e($activite['description']); ?></p>
                    <?php endif; ?>
                    <div class="activity-details">
                        <p><strong>🗓️ <?php echo e($activite['jour_semaine']); ?></strong></p>
                        <p>⏰ <?php echo substr($activite['heure_debut'], 0, 5); ?></p>
                        <p>👤 <?php echo e($activite['animateur_nom']); ?></p>
                        <?php if ($activite['lieu']): ?>
                            <p>📍 <?php echo e($activite['lieu']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (estConnecte() && aLeDroit('adherent')): ?>
                        <a href="/activites.php?id=<?php echo $activite['id']; ?>" class="btn btn-primary">S'inscrire</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="/activites.php" class="btn btn-secondary">Voir toutes les activités</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Rejoignez-nous !</h2>
        <div class="cta-content">
            <p>
                Vous souhaitez découvrir nos activités et rejoindre notre association ? 
                Inscrivez-vous dès maintenant et profitez de nos cours encadrés par des professionnels !
            </p>
            <a href="/inscription.php" class="btn btn-primary btn-large">Demander mon inscription</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
