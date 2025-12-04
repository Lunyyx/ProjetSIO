<?php 
$active = "admin-area";

include_once "../includes/permissions.php";
include_once "../config/database.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Vérifier que l'utilisateur est membre du bureau
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'membre_bureau') {
    header("Location: ../index.php?error=access_denied");
    exit();
}

// Récupérer les statistiques
$database = new Database();
$conn = $database->getConnection();

try {
    // Compter les utilisateurs par rôle
    $stmt = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $total_adherents = $stats['adherent'] ?? 0;
    $total_animateurs = $stats['animateur'] ?? 0;
    $total_bureau = $stats['membre_bureau'] ?? 0;
    
    // Compter les cours actifs
    $stmt = $conn->query("SELECT COUNT(*) FROM schedule WHERE is_active = 1");
    $total_cours = $stmt->fetchColumn();
    
    // Compter les cotisations actives (celles dont la date de fin n'est pas dépassée)
    $stmt = $conn->query("SELECT COUNT(*) FROM cotisations WHERE end_date >= CURDATE()");
    $cotisations_actives = $stmt->fetchColumn();
    
    // Compter le total des activités
    $stmt = $conn->query("SELECT COUNT(*) FROM activities");
    $total_activites = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    error_log("Erreur stats : " . $e->getMessage());
    $total_adherents = $total_animateurs = $total_bureau = $total_cours = $cotisations_actives = $total_activites = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Espace administrateur - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="../assets/css/common.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <?php include_once("../includes/header.php") ?>

        <div class="container my-5">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <h1 class="mb-2">👋 Bonjour <?= htmlspecialchars($_SESSION['first_name']) ?> !</h1>
                            <p class="lead mb-0">Bienvenue sur votre espace administrateur Fit&Fun</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-people-fill text-success me-2" style="font-size: 2rem;"></i>
                                <h2 class="mb-0 text-success"><?= $total_adherents ?></h2>
                            </div>
                            <p class="text-muted mb-0 small">Adhérents</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-person-badge text-primary me-2" style="font-size: 2rem;"></i>
                                <h2 class="mb-0 text-primary"><?= $total_animateurs ?></h2>
                            </div>
                            <p class="text-muted mb-0 small">Animateurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-calendar-event text-warning me-2" style="font-size: 2rem;"></i>
                                <h2 class="mb-0 text-warning"><?= $total_cours ?></h2>
                            </div>
                            <p class="text-muted mb-0 small">Cours actifs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <i class="bi bi-cash-coin text-info me-2" style="font-size: 2rem;"></i>
                                <h2 class="mb-0 text-info"><?= $cotisations_actives ?></h2>
                            </div>
                            <p class="text-muted mb-0 small">Cotisations actives</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestion principale -->
            <h3 class="mb-4"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Gestion</h3>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-people-fill text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="card-title mb-3">Gestion des adhérents</h3>
                            <p class="text-muted mb-4">
                                <strong><?= $total_adherents ?></strong> adhérent(s) inscrit(s)<br>
                                <small>Ajoutez, modifiez et consultez tous les membres</small>
                            </p>
                            <a href="members/manage.php" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-list-ul me-2"></i>Gérer les adhérents
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-person-badge text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="card-title mb-3">Gestion des animateurs</h3>
                            <p class="text-muted mb-4">
                                <strong><?= $total_animateurs ?></strong> animateur(s) actif(s)<br>
                                <small>Gérez les instructeurs et leurs spécialités</small>
                            </p>
                            <a href="instructors/manage.php" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-list-ul me-2"></i>Gérer les animateurs
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-calendar-week text-warning mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Planning</h5>
                            <p class="text-muted small mb-3">Gérer les <?= $total_cours ?> cours</p>
                            <a href="planning/manage.php" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-pencil-square me-1"></i>Gérer
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-cash-stack text-info mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Cotisations</h5>
                            <p class="text-muted small mb-3"><?= $cotisations_actives ?> cotisations actives</p>
                            <a href="cotisations/manage.php" class="btn btn-outline-info btn-sm w-100">
                                <i class="bi bi-pencil-square me-1"></i>Gérer
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-trophy-fill text-success mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Activités</h5>
                            <p class="text-muted small mb-3"><?= $total_activites ?> activités disponibles</p>
                            <a href="../planning.php" class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-eye me-1"></i>Voir le planning
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-center">
                    <a href="../auth/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Se déconnecter
                    </a>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>