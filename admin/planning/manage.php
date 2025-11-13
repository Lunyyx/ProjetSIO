<?php 
$active = "admin-area";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once "../../config/database.php";

$database = new Database();
$conn = $database->getConnection();

// Récupérer toutes les activités
$stmt = $conn->prepare("SELECT * FROM activities ORDER BY name");
$stmt->execute();
$activities = $stmt->fetchAll();

// Récupérer tous les animateurs
$stmt = $conn->prepare("SELECT * FROM instructors ORDER BY last_name, first_name");
$stmt->execute();
$instructors = $stmt->fetchAll();

// Récupérer le planning complet
$stmt = $conn->prepare("
    SELECT s.*, 
           a.name as activity_name, 
           a.color as activity_color,
           i.first_name, 
           i.last_name
    FROM schedule s
    JOIN activities a ON s.activity_id = a.id
    JOIN instructors i ON s.instructor_id = i.id
    WHERE s.is_active = 1
    ORDER BY s.day_of_week, s.start_time
");
$stmt->execute();
$schedules = $stmt->fetchAll();

// Organiser le planning par jour
$days = [
    1 => 'Lundi',
    2 => 'Mardi',
    3 => 'Mercredi',
    4 => 'Jeudi',
    5 => 'Vendredi',
    6 => 'Samedi',
    7 => 'Dimanche'
];

$planning_by_day = [];
foreach ($schedules as $schedule) {
    $planning_by_day[$schedule['day_of_week']][] = $schedule;
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Gestion du planning - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <?php include_once("../../includes/header.php") ?>

        <div class="container my-5">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="mb-2">
                                <i class="bi bi-calendar-week text-warning me-2"></i>
                                Gestion du Planning
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Planifiez et organisez les cours de la semaine
                            </p>
                        </div>
                        <div>
                            <a href="../area.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter un créneau
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar3 me-2"></i>
                                Planning de la semaine
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3" width="120">Jour</th>
                                            <th class="py-3" width="100">Horaire</th>
                                            <th class="py-3">Activité</th>
                                            <th class="py-3">Animateur</th>
                                            <th class="py-3">Lieu</th>
                                            <th class="py-3 text-center">Places</th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($schedules) > 0): ?>
                                            <?php foreach ($days as $day_num => $day_name): ?>
                                                <?php if (isset($planning_by_day[$day_num])): ?>
                                                    <?php foreach ($planning_by_day[$day_num] as $index => $schedule): ?>
                                                    <tr>
                                                        <?php if ($index === 0): ?>
                                                        <td class="px-4 py-3 fw-bold" rowspan="<?= count($planning_by_day[$day_num]) ?>">
                                                            <span class="badge bg-primary rounded-pill"><?= $day_name ?></span>
                                                        </td>
                                                        <?php endif; ?>
                                                        <td class="py-3">
                                                            <small class="fw-semibold">
                                                                <?= date('H:i', strtotime($schedule['start_time'])) ?> - <?= date('H:i', strtotime($schedule['end_time'])) ?>
                                                            </small>
                                                        </td>
                                                        <td class="py-3">
                                                            <span class="badge" style="background-color: <?= $schedule['activity_color'] ?>">
                                                                <?= htmlspecialchars($schedule['activity_name']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="py-3">
                                                            <?= htmlspecialchars($schedule['first_name'] . ' ' . $schedule['last_name']) ?>
                                                        </td>
                                                        <td class="py-3">
                                                            <i class="bi bi-geo-alt text-muted me-1"></i>
                                                            <?= htmlspecialchars($schedule['location'] ?? 'Non défini') ?>
                                                        </td>
                                                        <td class="py-3 text-center">
                                                            <span class="badge bg-secondary"><?= $schedule['max_participants'] ?></span>
                                                        </td>
                                                        <td class="py-3 text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-warning" title="Modifier">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" title="Supprimer">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                                    <p class="mt-3 mb-0">Aucun créneau planifié</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addScheduleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            Ajouter un créneau
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="add_schedule.php">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Activité *</label>
                                    <select class="form-select" name="activity_id" required>
                                        <option value="">Choisir une activité</option>
                                        <?php foreach ($activities as $activity): ?>
                                        <option value="<?= $activity['id'] ?>"><?= htmlspecialchars($activity['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Animateur *</label>
                                    <select class="form-select" name="instructor_id" required>
                                        <option value="">Choisir un animateur</option>
                                        <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?= $instructor['id'] ?>">
                                            <?= htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jour *</label>
                                    <select class="form-select" name="day_of_week" required>
                                        <option value="">Choisir un jour</option>
                                        <?php foreach ($days as $num => $name): ?>
                                        <option value="<?= $num ?>"><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Heure de début *</label>
                                    <input type="time" class="form-control" name="start_time" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Heure de fin *</label>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lieu</label>
                                    <input type="text" class="form-control" name="location" placeholder="Ex: Salle A">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre de places *</label>
                                    <input type="number" class="form-control" name="max_participants" value="20" min="1" max="100" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>