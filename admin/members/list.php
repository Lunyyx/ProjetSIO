<?php 
$active = "admin-area";

include_once "../../config/database.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
}

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT * FROM members");
$stmt->execute();
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
    <head>
        <title>Liste des adhérents - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="assets/css/index.css" rel="stylesheet">
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
                                <i class="bi bi-people-fill text-primary me-2"></i>
                                Liste des adhérents
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= count($members) ?> membre(s) inscrit(s)
                            </p>
                        </div>
                        <div>
                            <a href="../area.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <a href="add.php" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill me-2"></i>Ajouter un adhérent
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher un adhérent...">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="membersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3">
                                                <i class="bi bi-person me-2"></i>Prénom
                                            </th>
                                            <th class="py-3">Nom</th>
                                            <th class="py-3">
                                                <i class="bi bi-envelope me-2"></i>Email
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-geo-alt me-2"></i>Adresse
                                            </th>
                                            <th class="py-3">Code Postal</th>
                                            <th class="py-3">Ville</th>
                                            <th class="py-3">
                                                <i class="bi bi-calendar me-2"></i>Inscrit le
                                            </th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($members) > 0): ?>
                                            <?php foreach($members as $m): ?>
                                            <tr>
                                                <td class="px-4 py-3 fw-semibold"><?= htmlspecialchars($m['first_name']) ?></td>
                                                <td class="py-3 fw-semibold"><?= htmlspecialchars($m['last_name']) ?></td>
                                                <td class="py-3">
                                                    <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($m['email']) ?>
                                                    </a>
                                                </td>
                                                <td class="py-3"><?= htmlspecialchars($m['address']) ?></td>
                                                <td class="py-3"><?= htmlspecialchars($m['address_pc']) ?></td>
                                                <td class="py-3"><?= htmlspecialchars($m['address_city']) ?></td>
                                                <td class="py-3">
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($m['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary" title="Voir">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" title="Supprimer">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                    <p class="mt-3 mb-0">Aucun adhérent trouvé</p>
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

        <style>
            .table-hover tbody tr {
                transition: background-color 0.2s ease;
            }
            
            .table-hover tbody tr:hover {
                background-color: rgba(13, 110, 253, 0.05);
            }

            .btn-group .btn {
                transition: all 0.2s ease;
            }

            .btn-group .btn:hover {
                transform: translateY(-2px);
            }
            
            /* Éviter le défilement horizontal */
            .table-responsive {
                overflow-x: auto;
            }
        </style>

        <script>
            // Fonction de recherche en temps réel
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('#membersTable tbody tr');
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchValue) ? '' : 'none';
                });
            });
        </script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>