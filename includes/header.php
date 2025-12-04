<?php
function getNavLinkClass($page, $active) {
    return $active == $page ? "active bg-white text-primary" : "text-white";
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold fs-3" href="/" style="letter-spacing: 1px;">
            <span class="text-white">Fit</span><span class="text-warning">&</span><span class="text-white">Fun</span>
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
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['role'])): ?>
                        <?php if ($_SESSION['role'] === 'animateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= getNavLinkClass('animateur_dashboard', $active) ?> px-4 py-2 rounded-pill fw-semibold" href="/animateurs/dashboard.php">
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
                        <a class="nav-link text-white px-4 py-2 rounded-pill fw-semibold" href="/auth/logout.php">
                            Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link text-white px-4 py-2 rounded-pill fw-semibold" href="/auth/login.php">
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
