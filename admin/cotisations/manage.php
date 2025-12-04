<?php 
$active = "admin-area";

include_once "../../config/database.php";
require_once "../../includes/permissions.php";

session_start();

if(empty($_SESSION['user_id']) || !isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Récupérer toutes les cotisations avec infos membres
$stmt = $conn->prepare("
    SELECT c.*, m.first_name, m.last_name, m.email 
    FROM cotisations c 
    LEFT JOIN members m ON c.member_id = m.id 
    ORDER BY c.payment_date DESC
");
$stmt->execute();
$cotisations = $stmt->fetchAll();

// Récupérer tous les membres pour les formulaires
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM members ORDER BY last_name, first_name");
$stmt->execute();
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Gestion des cotisations - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="../../assets/css/common.css" rel="stylesheet">
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
                                Gestion des Cotisations
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
                            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addCotisationModal">
                                <i class="bi bi-plus-circle me-2"></i>Ajouter une cotisation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php
                    switch($_GET['success']) {
                        case 'added': echo 'Cotisation ajoutée avec succès !'; break;
                        case 'updated': echo 'Cotisation modifiée avec succès !'; break;
                        case 'deleted': echo 'Cotisation supprimée avec succès !'; break;
                        default: echo 'Opération réussie !';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php
                    switch($_GET['error']) {
                        case 'add_failed': echo 'Erreur lors de l\'ajout.'; break;
                        case 'update_failed': echo 'Erreur lors de la modification.'; break;
                        case 'delete_failed': echo 'Erreur lors de la suppression.'; break;
                        case 'database': echo 'Erreur de base de données.'; break;
                        default: echo 'Une erreur est survenue.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

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
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="px-4 py-3">Adhérent</th>
                                            <th class="py-3">Montant</th>
                                            <th class="py-3">Date paiement</th>
                                            <th class="py-3">Période</th>
                                            <th class="py-3">Méthode</th>
                                            <th class="py-3">Statut</th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($cotisations) > 0): ?>
                                            <?php foreach($cotisations as $c): ?>
                                            <?php
                                            $status = (strtotime($c['end_date']) >= time()) ? 'active' : 'expired';
                                            ?>
                                            <tr>
                                                <td class="px-4 py-3 fw-semibold">
                                                    <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                                                </td>
                                                <td class="py-3">
                                                    <strong class="text-success"><?= number_format($c['amount'], 2) ?> €</strong>
                                                </td>
                                                <td class="py-3">
                                                    <?= date('d/m/Y', strtotime($c['payment_date'])) ?>
                                                </td>
                                                <td class="py-3">
                                                    <small class="text-muted">
                                                        Du <?= date('d/m/Y', strtotime($c['start_date'])) ?><br>
                                                        au <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                                                    </small>
                                                </td>
                                                <td class="py-3">
                                                    <?php
                                                    $methods = [
                                                        'especes' => ['icon' => 'bi-cash', 'text' => 'Espèces'],
                                                        'carte' => ['icon' => 'bi-credit-card', 'text' => 'Carte'],
                                                        'cheque' => ['icon' => 'bi-receipt', 'text' => 'Chèque'],
                                                        'virement' => ['icon' => 'bi-bank', 'text' => 'Virement']
                                                    ];
                                                    $method = $methods[$c['payment_method']] ?? ['icon' => 'bi-question', 'text' => $c['payment_method']];
                                                    ?>
                                                    <i class="bi <?= $method['icon'] ?> me-1"></i><?= $method['text'] ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($status === 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Expirée</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <button class="btn btn-sm btn-outline-warning me-1" 
                                                            onclick="editCotisation(<?= htmlspecialchars(json_encode($c)) ?>)"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCotisationModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteCotisationModal"
                                                            onclick="setDeleteId(<?= $c['id'] ?>, '<?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox display-4"></i>
                                                    <p class="mt-3">Aucune cotisation enregistrée</p>
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

        <!-- Modal Ajout -->
        <div class="modal fade" id="addCotisationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Ajouter une cotisation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_add.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Adhérent <span class="text-danger">*</span></label>
                                <select class="form-select" name="member_id" required>
                                    <option value="">Sélectionnez un adhérent</option>
                                    <?php foreach($members as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="payment_date" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="start_date" id="add_start_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="end_date" id="add_end_date" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="especes">Espèces</option>
                                    <option value="carte">Carte bancaire</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-check-lg me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Édition -->
        <div class="modal fade" id="editCotisationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la cotisation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_edit.php" method="POST">
                        <input type="hidden" name="cotisation_id" id="edit_cotisation_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Adhérent <span class="text-danger">*</span></label>
                                <select class="form-select" name="member_id" id="edit_member_id" required>
                                    <?php foreach($members as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="amount" id="edit_amount" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="payment_date" id="edit_payment_date" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fin <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_method" id="edit_payment_method" required>
                                    <option value="especes">Espèces</option>
                                    <option value="carte">Carte bancaire</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-lg me-2"></i>Modifier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Suppression -->
        <div class="modal fade" id="deleteCotisationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Confirmer la suppression</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_delete.php" method="POST">
                        <input type="hidden" name="cotisation_id" id="delete_cotisation_id">
                        <div class="modal-body">
                            <p class="mb-0">Êtes-vous sûr de vouloir supprimer la cotisation de <strong id="delete_cotisation_name"></strong> ?</p>
                            <p class="text-muted small mb-0 mt-2">Cette action est irréversible.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-2"></i>Supprimer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#cotisationsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Auto-calculer date de fin (1 an après début)
        document.getElementById('add_start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + 1);
            document.getElementById('add_end_date').valueAsDate = endDate;
        });

        function editCotisation(cotisation) {
            document.getElementById('edit_cotisation_id').value = cotisation.id;
            document.getElementById('edit_member_id').value = cotisation.member_id;
            document.getElementById('edit_amount').value = cotisation.amount;
            document.getElementById('edit_payment_date').value = cotisation.payment_date;
            document.getElementById('edit_start_date').value = cotisation.start_date;
            document.getElementById('edit_end_date').value = cotisation.end_date;
            document.getElementById('edit_payment_method').value = cotisation.payment_method;
        }

        function setDeleteId(id, name) {
            document.getElementById('delete_cotisation_id').value = id;
            document.getElementById('delete_cotisation_name').textContent = name;
        }
        </script>
    </body>
</html>
