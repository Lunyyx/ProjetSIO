<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

// Vérifier que l'utilisateur est connecté et a le rôle animateur
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'animateur' && $_SESSION['role'] !== 'membre_bureau')) {
    header("Location: ../auth/login.php");
    exit();
}

$active = 'animateur';

$database = new Database();
$conn = $database->getConnection();

// Récupérer les statistiques de l'animateur
try {
    // Compter les cours de l'animateur
    $stmt = $conn->prepare("SELECT COUNT(*) FROM schedule WHERE user_id = :user_id AND is_active = 1");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $total_cours = $stmt->fetchColumn();
    
    // Compter les activités différentes
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT activity_id) 
        FROM schedule 
        WHERE user_id = :user_id AND is_active = 1
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $total_activites = $stmt->fetchColumn();
    
    // Compter les participants (adhérents avec activités correspondantes)
    $stmt = $conn->prepare("
        SELECT DISTINCT a.name
        FROM schedule s
        JOIN activities a ON s.activity_id = a.id
        WHERE s.user_id = :user_id AND s.is_active = 1
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $mes_activites_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $total_participants = 0;
    if (!empty($mes_activites_names)) {
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT id)
            FROM users
            WHERE role IN ('adherent', 'visiteur')
            AND preferred_activities IS NOT NULL
        ");
        $stmt->execute();
        $all_users = $stmt->fetchColumn();
        
        // Approximation: on compte les users qui ont au moins une des activités
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT id)
            FROM users
            WHERE role IN ('adherent', 'visiteur')
            AND preferred_activities LIKE :activity
        ");
        foreach ($mes_activites_names as $activity) {
            $stmt->execute(['activity' => "%$activity%"]);
            $total_participants += $stmt->fetchColumn();
        }
    }
    
} catch(PDOException $e) {
    error_log("Erreur stats animateur : " . $e->getMessage());
    $total_cours = $total_activites = $total_participants = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Animateur - Fit&Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/common.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container my-5">
        <!-- Header -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                    <div class="card-body p-4">
                        <h1 class="mb-2 text-dark">
                            🎓 Bonjour <?= htmlspecialchars($_SESSION['first_name']) ?> !
                        </h1>
                        <p class="lead mb-0 text-dark">Bienvenue sur votre espace animateur Fit&Fun</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-calendar-week text-primary me-2" style="font-size: 2rem;"></i>
                            <h2 class="mb-0 text-primary"><?= $total_cours ?></h2>
                        </div>
                        <p class="text-muted mb-0 small">Cours par semaine</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-trophy text-success me-2" style="font-size: 2rem;"></i>
                            <h2 class="mb-0 text-success"><?= $total_activites ?></h2>
                        </div>
                        <p class="text-muted mb-0 small">Activités différentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-people-fill text-info me-2" style="font-size: 2rem;"></i>
                            <h2 class="mb-0 text-info"><?= $total_participants ?></h2>
                        </div>
                        <p class="text-muted mb-0 small">Participants</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu principal -->
        <h3 class="mb-4"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Espace Animateur</h3>

        <div class="row g-4">
            <!-- Planning -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-calendar-week text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="card-title mb-3">Planning</h3>
                        <p class="text-muted mb-4">
                            Consultez le planning des cours et vos sessions.
                        </p>
                        <a href="../planning.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-calendar-check me-2"></i>Voir le planning
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mes Cours -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-mortarboard text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="card-title mb-3">Mes Cours</h3>
                        <p class="text-muted mb-4">
                            Gérez vos cours et consultez la liste des participants.
                        </p>
                        <a href="mes_cours.php" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-list-check me-2"></i>Mes cours
                        </a>
                    </div>
                </div>
            </div>

            <!-- Participants -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-people text-info" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="card-title mb-3">Participants</h3>
                        <p class="text-muted mb-4">
                            Consultez la liste des adhérents inscrits à vos activités.
                        </p>
                        <a href="participants.php" class="btn btn-info text-white btn-lg w-100">
                            <i class="bi bi-people-fill me-2"></i>Voir les participants
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mon Profil -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle text-secondary" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="card-title mb-3">Mon Profil</h3>
                        <p class="text-muted mb-4">
                            Modifiez vos informations personnelles.
                        </p>
                        <a href="profil.php" class="btn btn-secondary btn-lg w-100">
                            <i class="bi bi-pencil-square me-2"></i>Mon profil
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
