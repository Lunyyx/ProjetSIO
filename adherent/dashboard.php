<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

// Vérifier que l'utilisateur est connecté et a au moins le rôle adhérent
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['adherent', 'animateur', 'membre_bureau'])) {
    header("Location: ../admin/auth/login.php");
    exit();
}

$page = 'adherent_dashboard';

// Récupérer les informations du membre
try {
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Erreur récupération membre : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Adhérent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-person-badge"></i> Espace Adhérent
                </h1>
                <p class="lead">
                    Bienvenue <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?> 
                    <span class="badge <?= getRoleColor($_SESSION['role']) ?>"><?= getRoleName($_SESSION['role']) ?></span>
                </p>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Planning -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-calendar-week text-primary"></i> Planning des activités
                        </h5>
                        <p class="card-text">Consultez le planning de toutes les activités proposées.</p>
                        <a href="../planning.php" class="btn btn-primary">
                            <i class="bi bi-calendar-check"></i> Voir le planning
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mes Activités -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-list-stars text-success"></i> Mes Activités
                        </h5>
                        <p class="card-text">Gérez vos inscriptions aux activités.</p>
                        <?php if ($member && $member['preferred_activities']): ?>
                            <div class="mb-3">
                                <?php
                                $activities_ids = explode(',', $member['preferred_activities']);
                                foreach($activities_ids as $activity_id):
                                    $stmt_act = $conn->prepare("SELECT name FROM activities WHERE id = ?");
                                    $stmt_act->execute([trim($activity_id)]);
                                    $activity = $stmt_act->fetch();
                                    if ($activity):
                                ?>
                                    <span class="badge bg-success me-1"><?= htmlspecialchars($activity['name']) ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Aucune activité inscrite pour le moment.</p>
                        <?php endif; ?>
                        <a href="mes_activites.php" class="btn btn-success">
                            <i class="bi bi-pencil"></i> Gérer mes activités
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ma Cotisation -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-credit-card text-warning"></i> Ma Cotisation
                        </h5>
                        <p class="card-text">Consultez l'état de votre cotisation annuelle.</p>
                        <?php
                        try {
                            $stmt_cot = $conn->prepare("SELECT * FROM cotisations WHERE member_id = ? ORDER BY end_date DESC LIMIT 1");
                            $stmt_cot->execute([$_SESSION['user_id']]);
                            $cotisation = $stmt_cot->fetch();
                            
                            if ($cotisation):
                                $status = (strtotime($cotisation['end_date']) >= time()) ? 'active' : 'expired';
                        ?>
                                <div class="alert alert-<?= $status === 'active' ? 'success' : 'warning' ?>">
                                    <?php if ($status === 'active'): ?>
                                        <i class="bi bi-check-circle"></i> Cotisation active jusqu'au <?= date('d/m/Y', strtotime($cotisation['end_date'])) ?>
                                    <?php else: ?>
                                        <i class="bi bi-exclamation-triangle"></i> Cotisation expirée. Veuillez la renouveler.
                                    <?php endif; ?>
                                </div>
                        <?php 
                            else:
                        ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Aucune cotisation enregistrée.
                                </div>
                        <?php 
                            endif;
                        } catch(PDOException $e) {
                            error_log("Erreur cotisation : " . $e->getMessage());
                        }
                        ?>
                        <a href="cotisation.php" class="btn btn-warning">
                            <i class="bi bi-receipt"></i> Voir ma cotisation
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

        <?php if (in_array($_SESSION['role'], ['animateur', 'membre_bureau'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-<?= $_SESSION['role'] === 'membre_bureau' ? 'success' : 'warning' ?>">
                    <i class="bi bi-shield-check"></i> 
                    <strong>Accès supplémentaire :</strong> 
                    <?php if ($_SESSION['role'] === 'membre_bureau'): ?>
                        Vous avez également accès à l'<a href="../admin/area.php" class="alert-link">espace administration</a>.
                    <?php elseif ($_SESSION['role'] === 'animateur'): ?>
                        Vous avez également accès à l'<a href="../animateur/dashboard.php" class="alert-link">espace animateur</a>.
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
