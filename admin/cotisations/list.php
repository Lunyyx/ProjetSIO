<?php 
$active = "admin-area";

include_once "../../config/database.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
}

$database = new Database();
$conn = $database->getConnection();

// Récupérer toutes les cotisations avec les informations des membres
$stmt = $conn->prepare("
    SELECT c.*, m.first_name, m.last_name, m.email 
    FROM cotisations c 
    LEFT JOIN members m ON c.member_id = m.id 
    ORDER BY c.payment_date DESC
");
$stmt->execute();
$cotisations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
    <head>
        <title>Liste des cotisations - Fit&Fun</title>
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
                                <i class="bi bi-cash-coin text-info me-2"></i>
                                Liste des cotisations
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= count($cotisations) ?> cotisation(s) enregistrée(s)
                            </p>
                        </div>
                        <div>
                            <a href="../area.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <a href="add.php" class="btn btn-info text-white">
                                <i class="bi bi-plus-circle-fill me-2"></i>Nouvelle cotisation
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
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une cotisation...">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="cotisationsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3">
                                                <i class="bi bi-person me-2"></i>Adhérent
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-currency-euro me-2"></i>Montant
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-calendar-check me-2"></i>Date de paiement
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-calendar-range me-2"></i>Période
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-credit-card me-2"></i>Mode de paiement
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-check-circle me-2"></i>Statut
                                            </th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($cotisations) > 0): ?>
                                            <?php foreach($cotisations as $c): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="fw-semibold"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge bg-info text-white fs-6"><?= number_format($c['amount'], 2, ',', ' ') ?> €</span>
                                                </td>
                                                <td class="py-3">
                                                    <?= date('d/m/Y', strtotime($c['payment_date'])) ?>
                                                </td>
                                                <td class="py-3">
                                                    <?= date('d/m/Y', strtotime($c['start_date'])) ?> 
                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                    <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $payment_icons = [
                                                        'Espèces' => 'cash-stack',
                                                        'Carte bancaire' => 'credit-card-2-front',
                                                        'Chèque' => 'receipt',
                                                        'Virement' => 'bank'
                                                    ];
                                                    $icon = $payment_icons[$c['payment_method']] ?? 'cash';
                                                    ?>
                                                    <i class="bi bi-<?= $icon ?> me-1"></i>
                                                    <?= htmlspecialchars($c['payment_method']) ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $status_class = $c['status'] == 'Payée' ? 'success' : ($c['status'] == 'En attente' ? 'warning' : 'danger');
                                                    ?>
                                                    <span class="badge bg-<?= $status_class ?>">
                                                        <?= htmlspecialchars($c['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary" title="Voir">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-outline-warning" title="Modifier">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" title="Supprimer">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                                    <p class="mt-3 mb-0">Aucune cotisation trouvée</p>
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
                background-color: rgba(13, 202, 240, 0.05);
            }
            
            .btn-group .btn {
                transition: all 0.2s ease;
            }
        </style>

        <script>
            // Fonction de recherche
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const table = document.getElementById('cotisationsTable');
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const text = row.textContent.toLowerCase();
                    
                    if (text.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        </script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
