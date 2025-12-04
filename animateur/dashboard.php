<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

// Vérifier que l'utilisateur est connecté et a le rôle animateur
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'animateur' && $_SESSION['role'] !== 'membre_bureau')) {
    header("Location: ../admin/auth/login.php");
    exit();
}

$page = 'animateur_dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Animateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-person-video3"></i> Espace Animateur
                </h1>
                <p class="lead">
                    Bienvenue <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?> 
                    <span class="badge bg-warning">Animateur</span>
                </p>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Planning -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-week text-primary"></i> Planning
                        </h5>
                        <p class="card-text">Consultez le planning des cours et vos sessions.</p>
                        <a href="../planning.php" class="btn btn-primary">
                            <i class="bi bi-calendar-check"></i> Voir le planning
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mes Cours -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-mortarboard text-success"></i> Mes Cours
                        </h5>
                        <p class="card-text">Gérez vos cours et consultez la liste des participants.</p>
                        <a href="mes_cours.php" class="btn btn-success">
                            <i class="bi bi-list-check"></i> Mes cours
                        </a>
                    </div>
                </div>
            </div>

            <!-- Participants -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-people text-info"></i> Participants
                        </h5>
                        <p class="card-text">Consultez la liste des adhérents inscrits à vos activités.</p>
                        <a href="participants.php" class="btn btn-info text-white">
                            <i class="bi bi-people-fill"></i> Voir les participants
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mon Profil -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-person-circle text-secondary"></i> Mon Profil
                        </h5>
                        <p class="card-text">Modifiez vos informations personnelles.</p>
                        <a href="profil.php" class="btn btn-secondary">
                            <i class="bi bi-pencil-square"></i> Mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'membre_bureau'): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="bi bi-shield-check"></i> 
                    <strong>Accès Bureau :</strong> 
                    Vous avez également accès à l'<a href="../admin/area.php" class="alert-link">espace administration</a>.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
