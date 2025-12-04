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

// Récupérer les activités de l'animateur
$stmt = $conn->prepare("
    SELECT DISTINCT a.id, a.name, a.color, a.description
    FROM schedule s
    JOIN activities a ON s.activity_id = a.id
    WHERE s.user_id = :user_id AND s.is_active = 1
    ORDER BY a.name
");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$mes_activites = $stmt->fetchAll();

// Récupérer les adhérents intéressés par ces activités
$participants = [];
if (!empty($mes_activites)) {
    $activity_ids = array_column($mes_activites, 'id');
    $activity_names = array_column($mes_activites, 'name');
    
    $stmt = $conn->prepare("
        SELECT DISTINCT u.*, 
               u.preferred_activities
        FROM users u
        WHERE u.role IN ('adherent', 'visiteur')
        AND u.preferred_activities IS NOT NULL
        ORDER BY u.last_name, u.first_name
    ");
    $stmt->execute();
    $all_users = $stmt->fetchAll();
    
    // Filtrer les utilisateurs qui ont au moins une des activités de l'animateur
    foreach ($all_users as $user) {
        foreach ($activity_names as $activity_name) {
            if (stripos($user['preferred_activities'], $activity_name) !== false) {
                $participants[] = $user;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - Fit&Fun</title>
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
                            <i class="bi bi-people text-info me-2"></i>
                            Participants
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Liste des adhérents intéressés par vos activités
                        </p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Mes activités -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-bookmark-star me-2"></i>
                            Mes activités
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mes_activites)): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Vous n'avez pas encore d'activités assignées.
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($mes_activites as $activite): ?>
                                    <span class="badge fs-6 px-3 py-2" style="background-color: <?= htmlspecialchars($activite['color']) ?>;">
                                        <?= htmlspecialchars($activite['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des participants -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i>
                            Adhérents inscrits
                            <span class="badge bg-info"><?= count($participants) ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($participants)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Aucun adhérent inscrit à vos activités pour le moment.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Activités préférées</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $participant): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></strong>
                                                </td>
                                                <td>
                                                    <i class="bi bi-envelope text-primary"></i>
                                                    <?= htmlspecialchars($participant['email']) ?>
                                                </td>
                                                <td>
                                                    <?php if ($participant['phone']): ?>
                                                        <i class="bi bi-telephone text-success"></i>
                                                        <?= htmlspecialchars($participant['phone']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($participant['preferred_activities']): ?>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($participant['preferred_activities']) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($participant['role'] === 'adherent'): ?>
                                                        <span class="badge bg-success">Adhérent</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Visiteur</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                <small>Total : <?= count($participants) ?> participant(s)</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
