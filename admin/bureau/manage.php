<?php 
$active = "admin-area";

// Couleurs thématiques pour le bureau
$theme_color = 'danger';
$theme_bg = 'bg-danger';
$theme_text = 'text-danger';
$theme_btn = 'btn-danger';

include_once "../../config/database.php";
require_once "../../includes/permissions.php";

session_start();

if(empty($_SESSION['user_id']) || !isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'membre_bureau' ORDER BY last_name, first_name");
$stmt->execute();
$bureau_members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Gestion des membres du bureau - Fit&Fun</title>
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
                                <i class="bi bi-shield-check <?= $theme_text ?> me-2"></i>
                                Gestion des Membres du Bureau
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= count($bureau_members) ?> membre du bureau(s) enregistré(s)
                            </p>
                        </div>
                        <div>
                            <a href="../area.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <button class="btn <?= $theme_btn ?>" data-bs-toggle="modal" data-bs-target="#addBureauModal">
                                <i class="bi bi-person-plus-fill me-2"></i>Ajouter un membre du bureau
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
                        case 'added': echo 'Membre du bureau ajouté avec succès !'; break;
                        case 'updated': echo 'Membre du bureau modifié avec succès !'; break;
                        case 'deleted': echo 'Membre du bureau supprimé avec succès !'; break;
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
                        case 'email_exists': echo 'Cet email est déjà utilisé.'; break;
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
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher un membre du bureau...">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="bureauTable">
                                    <thead class="<?= $theme_bg ?> text-white">
                                        <tr>
                                            <th class="px-4 py-3">Prénom</th>
                                            <th class="py-3">Nom</th>
                                            <th class="py-3">Email</th>
                                            <th class="py-3">Téléphone</th>
                                            <th class="py-3">Spécialités</th>
                                            <th class="py-3">Créé le</th>
                                            <th class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($bureau_members) > 0): ?>
                                            <?php foreach($bureau_members as $i): ?>
                                            <tr>
                                                <td class="px-4 py-3 fw-semibold"><?= htmlspecialchars($i['first_name']) ?></td>
                                                <td class="py-3 fw-semibold"><?= htmlspecialchars($i['last_name']) ?></td>
                                                <td class="py-3">
                                                    <?php if ($i['email']): ?>
                                                        <a href="mailto:<?= htmlspecialchars($i['email']) ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($i['email']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <small class="text-muted">Non renseigné</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($i['phone']): ?>
                                                        <a href="tel:<?= htmlspecialchars($i['phone']) ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($i['phone']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <small class="text-muted">Non renseigné</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3">
                                                    <?php if ($i['specialties']): ?>
                                                        <span class="badge bg-info"><?= htmlspecialchars($i['specialties']) ?></span>
                                                    <?php else: ?>
                                                        <small class="text-muted">Aucune</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3">
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($i['created_at'])) ?></small>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <button class="btn btn-sm btn-outline-warning me-1" 
                                                            onclick="editInstructor(<?= htmlspecialchars(json_encode($i)) ?>)"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editBureauModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteBureauModal"
                                                            onclick="setDeleteId(<?= $i['id'] ?>, '<?= htmlspecialchars($i['first_name'] . ' ' . $i['last_name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox display-4"></i>
                                                    <p class="mt-3">Aucun membre du bureau enregistré</p>
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
        <div class="modal fade" id="addBureauModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Ajouter un membre du bureau</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_add.php" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom <span class="<?= $theme_text ?>">*</span></label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom <span class="<?= $theme_text ?>">*</span></label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Spécialités</label>
                                <input type="text" class="form-control" name="specialties" placeholder="Ex: Yoga, Pilates">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn <?= $theme_btn ?>">
                                <i class="bi bi-check-lg me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Édition -->
        <div class="modal fade" id="editBureauModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'membre du bureau</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_edit.php" method="POST">
                        <input type="hidden" name="bureau_id" id="edit_bureau_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom <span class="<?= $theme_text ?>">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom <span class="<?= $theme_text ?>">*</span></label>
                                    <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="edit_email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Spécialités</label>
                                <input type="text" class="form-control" name="specialties" id="edit_specialties">
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
        <div class="modal fade" id="deleteBureauModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Confirmer la suppression</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_delete.php" method="POST">
                        <input type="hidden" name="bureau_id" id="delete_bureau_id">
                        <div class="modal-body">
                            <p class="mb-0">Êtes-vous sûr de vouloir supprimer <strong id="delete_instructor_name"></strong> ?</p>
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
            const rows = document.querySelectorAll('#bureauTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        function editInstructor(instructor) {
            document.getElementById('edit_bureau_id').value = instructor.id;
            document.getElementById('edit_first_name').value = instructor.first_name;
            document.getElementById('edit_last_name').value = instructor.last_name;
            document.getElementById('edit_email').value = instructor.email || '';
            document.getElementById('edit_phone').value = instructor.phone || '';
            document.getElementById('edit_specialties').value = instructor.specialties || '';
        }

        function setDeleteId(id, name) {
            document.getElementById('delete_bureau_id').value = id;
            document.getElementById('delete_instructor_name').textContent = name;
        }
        </script>
    </body>
</html>
