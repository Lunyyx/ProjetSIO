<?php
$cssPath = dirname(__DIR__, 2) . '/public/assets/css/style.css';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Association sportive</title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo $cssVersion; ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="/"><?php echo APP_NAME; ?></a>
            </div>
            
            <!-- Menu principal -->
            <ul class="nav-menu">
                <li><a href="/">Accueil</a></li>
                <li><a href="/activites.php">Activités</a></li>
                <li><a href="/planning.php">Planning</a></li>
                
                <?php if (estConnecte()): ?>
                    <?php if (($_SESSION['role'] ?? '') === 'adherent'): ?>
                        <li><a href="/mes-inscriptions.php">Mes inscriptions</a></li>
                    <?php endif; ?>
                    
                    <?php if (($_SESSION['role'] ?? '') === 'animateur'): ?>
                        <li><a href="/gestion-seances.php">Mes séances</a></li>
                    <?php endif; ?>
                    
                    <?php if (($_SESSION['role'] ?? '') === 'bureau'): ?>
                        <li><a href="/admin/" class="nav-admin">Administration</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Menu utilisateur -->
            <div class="nav-user">
                <?php if (estConnecte()): ?>
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <span class="user-icon">👤</span>
                            <span class="user-name"><?php echo e($_SESSION['email']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/profil.php">Mon profil</a>
                            <a href="/logout.php" class="logout-link">Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-outline">Connexion</a>
                    <a href="/inscription.php" class="btn btn-primary">S'inscrire</a>
                <?php endif; ?>
            </div>
            
            <!-- Menu mobile -->
            <button class="nav-toggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>
    
    <main>
