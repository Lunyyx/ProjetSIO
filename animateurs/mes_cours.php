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

// Récupérer les cours de l'animateur
$stmt = $conn->prepare("
    SELECT s.*, 
           a.name as activity_name, 
           a.color as activity_color,
           a.description as activity_description
    FROM schedule s
    JOIN activities a ON s.activity_id = a.id
    WHERE s.user_id = :user_id AND s.is_active = 1
    ORDER BY s.day_of_week, s.start_time
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$mes_cours = $stmt->fetchAll();

// Organiser par jour
$days = [
    1 => 'Lundi',
    2 => 'Mardi',
    3 => 'Mercredi',
    4 => 'Jeudi',
    5 => 'Vendredi',
    6 => 'Samedi',
    7 => 'Dimanche'
];

$cours_by_day = [];
foreach ($mes_cours as $cours) {
    $cours_by_day[$cours['day_of_week']][] = $cours;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Cours - Fit&Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/common.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="bi bi-mortarboard text-success me-2"></i>
                            Mes Cours
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Planning de vos cours de la semaine
                        </p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                Opération réussie !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($mes_cours)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Vous n'avez pas encore de cours planifiés. Contactez l'administration pour en ajouter.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white border-0">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-week me-2"></i>
                                Planning hebdomadaire
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($days as $day_num => $day_name): ?>
                                <?php if (isset($cours_by_day[$day_num])): ?>
                                    <h5 class="mt-4 mb-3">
                                        <i class="bi bi-calendar-day text-success me-2"></i>
                                        <?= $day_name ?>
                                    </h5>
                                    <div class="row">
                                        <?php foreach ($cours_by_day[$day_num] as $cours): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card h-100 border-start border-4" style="border-color: <?= htmlspecialchars($cours['activity_color']) ?> !important;">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <span class="badge" style="background-color: <?= htmlspecialchars($cours['activity_color']) ?>;">
                                                                <?= htmlspecialchars($cours['activity_name']) ?>
                                                            </span>
                                                        </h6>
                                                        <p class="card-text">
                                                            <i class="bi bi-clock text-primary"></i>
                                                            <strong><?= date('H:i', strtotime($cours['start_time'])) ?></strong>
                                                            - <?= date('H:i', strtotime($cours['end_time'])) ?>
                                                        </p>
                                                        <?php if ($cours['location']): ?>
                                                            <p class="card-text">
                                                                <i class="bi bi-geo-alt text-danger"></i>
                                                                <?= htmlspecialchars($cours['location']) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if ($cours['max_participants']): ?>
                                                            <p class="card-text">
                                                                <i class="bi bi-people text-info"></i>
                                                                Max. <?= $cours['max_participants'] ?> participants
                                                            </p>
                                                        <?php endif; ?>
                                                        <?php if ($cours['activity_description']): ?>
                                                            <p class="card-text text-muted small">
                                                                <?= htmlspecialchars($cours['activity_description']) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card mt-4 border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h2 class="text-success"><?= count($mes_cours) ?></h2>
                                        <p class="text-muted">Cours par semaine</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h2 class="text-primary">
                                            <?php
                                            $total_hours = 0;
                                            foreach ($mes_cours as $cours) {
                                                $start = new DateTime($cours['start_time']);
                                                $end = new DateTime($cours['end_time']);
                                                $total_hours += ($end->getTimestamp() - $start->getTimestamp()) / 3600;
                                            }
                                            echo number_format($total_hours, 1);
                                            ?>h
                                        </h2>
                                        <p class="text-muted">Heures par semaine</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3">
                                        <h2 class="text-info">
                                            <?php
                                            $activities = array_unique(array_column($mes_cours, 'activity_name'));
                                            echo count($activities);
                                            ?>
                                        </h2>
                                        <p class="text-muted">Activités différentes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
