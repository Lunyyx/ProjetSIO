<?php
function getNavLinkClass($page, $active) {
    $isActive = $active == $page;
    
    if (!$isActive) {
        // Lien non actif - couleur dépend du thème
        if (isset($GLOBALS['linkTextClass'])) {
            return $GLOBALS['linkTextClass'];
        }
        return "text-white";
    }
    
    // Lien actif - fond blanc avec texte coloré selon la page
    if (isset($active)) {
        switch($active) {
            case 'animateur':
                return "active bg-white text-warning";
            case 'planning':
                return "active bg-white text-info";
            case 'inscription':
                return "active bg-white text-success";
            case 'admin-area':
            case 'admin':
            default:
                return "active bg-white text-primary";
        }
    }
    
    // Si on a un thème défini (pages de gestion), utiliser la couleur du thème
    if (isset($GLOBALS['theme_text'])) {
        $colorClass = str_replace('text-', '', $GLOBALS['theme_text']);
        return "active bg-white text-{$colorClass}";
    }
    
    return "active bg-white text-primary";
}

// Déterminer la couleur du header en fonction de la page active
$headerColor = 'bg-primary'; // Couleur par défaut
$navbarTheme = 'navbar-dark';
$brandTextClass = 'text-white';
$linkTextClass = 'text-white';

// Priorité 1: Thème spécifique de la page (pages de gestion)
if (isset($theme_bg)) {
    $headerColor = $theme_bg;
    
    if ($theme_bg === 'bg-warning') {
        $navbarTheme = 'navbar-light';
        $brandTextClass = 'text-dark';
        $linkTextClass = 'text-dark';
    } else {
        $navbarTheme = 'navbar-dark';
        $brandTextClass = 'text-white';
        $linkTextClass = 'text-white';
    }
    
    // Rendre le thème accessible pour getNavLinkClass
    $GLOBALS['theme_text'] = $theme_text;
}
// Priorité 2: Variable $active (pages générales)
elseif (isset($active)) {
    switch($active) {
        case 'admin-area':
        case 'admin':
            $headerColor = 'bg-primary';
            $navbarTheme = 'navbar-dark';
            $brandTextClass = 'text-white';
            $linkTextClass = 'text-white';
            break;
        case 'animateur':
            $headerColor = 'bg-warning';
            $navbarTheme = 'navbar-light';
            $brandTextClass = 'text-dark';
            $linkTextClass = 'text-dark';
            break;
        case 'planning':
            $headerColor = 'bg-info';
            $navbarTheme = 'navbar-dark';
            $brandTextClass = 'text-white';
            $linkTextClass = 'text-white';
            break;
        case 'inscription':
            $headerColor = 'bg-success';
            $navbarTheme = 'navbar-dark';
            $brandTextClass = 'text-white';
            $linkTextClass = 'text-white';
            break;
        default:
            $headerColor = 'bg-primary';
            $navbarTheme = 'navbar-dark';
            $brandTextClass = 'text-white';
            $linkTextClass = 'text-white';
            break;
    }
}

// Rendre accessible pour getNavLinkClass
$GLOBALS['linkTextClass'] = $linkTextClass;
?>
<nav class="navbar navbar-expand-lg <?= $navbarTheme ?> <?= $headerColor ?> shadow-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-3" href="/" style="letter-spacing: 1px;">
            <span class="<?= $brandTextClass ?>">Fit</span><span class="<?= $active === 'animateur' ? 'text-primary' : 'text-warning' ?>">&</span><span class="<?= $brandTextClass ?>">Fun</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-2">
                <li class="nav-item">
                    <a class="nav-link <?= getNavLinkClass('home', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/index.php">
                        Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= getNavLinkClass('planning', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/planning.php">
                        Planning
                    </a>
                </li>
                <?php if(empty($_SESSION['user_id'])) { ?>
                <li class="nav-item">
                    <a class="nav-link <?= getNavLinkClass('inscription', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/inscription.php">
                        Inscription
                    </a>
                </li>
                <?php } ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['role'])): ?>
                        <?php if ($_SESSION['role'] === 'animateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= getNavLinkClass('animateur', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/animateurs/dashboard.php">
                                    Espace Animateur
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['role'] === 'membre_bureau'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= getNavLinkClass('admin-area', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/admin/area.php">
                                    Administration
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $linkTextClass ?> px-4 py-2 rounded-pill fw-semibold" href="/auth/logout.php">
                            Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $linkTextClass ?> px-4 py-2 rounded-pill fw-semibold" href="/auth/login.php">
                            Connexion
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    .nav-link:not(.active):hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
    }
    
    .nav-link.active {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
</style>
