<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Association sportive</title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../../public/assets/css/style.css'); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/"><?php echo APP_NAME; ?></a>
            </div>
            <ul class="nav-menu">
                <li><a href="/">Accueil</a></li>
                <li><a href="/activites.php">Activités</a></li>
                <li><a href="/planning.php">Planning</a></li>
                <?php if (estConnecte()): ?>
                    <?php if (aLeDroit('adherent')): ?>
                        <li><a href="/mes-inscriptions.php">Mes inscriptions</a></li>
                    <?php endif; ?>
                    <?php if (aLeDroit('animateur')): ?>
                        <li><a href="/gestion-seances.php">Gérer séances</a></li>
                    <?php endif; ?>
                    <?php if (aLeDroit('bureau')): ?>
                        <li><a href="/admin/">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="/login.php">Connexion</a></li>
                    <li><a href="/inscription.php" class="btn-primary">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <main>
        <?php afficherMessage(); ?>
