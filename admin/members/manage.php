<?php 
$active = "admin-area";

include_once "../../config/database.php";
require_once "../../includes/permissions.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if (!isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Récupérer uniquement les adhérents (pas les animateurs ni les membres du bureau)
$stmt = $conn->prepare("SELECT * FROM users WHERE role IN ('adherent', 'visiteur') ORDER BY created_at DESC");
$stmt->execute();
$members = $stmt->fetchAll();

// Récupérer toutes les activités pour les modales
$stmt = $conn->prepare("SELECT * FROM activities ORDER BY name");
$stmt->execute();
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Gestion des adhérents - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="../../assets/css/common.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <?php include_once("../../includes/header.php") ?>

        <div class="container my-5">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="mb-2">
                                <i class="bi bi-people-fill text-success me-2"></i>
                                Gestion des Adhérents
                            </h1>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                <?= count($members) ?> adhérent(s) inscrit(s) • Les animateurs sont gérés séparément
                            </p>
                        </div>
                        <div>
                            <a href="../area.php" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                <i class="bi bi-person-plus-fill me-2"></i>Ajouter un adhérent
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php
                    switch($_GET['success']) {
                        case 'added': echo 'Adhérent ajouté avec succès !'; break;
                        case 'updated': echo 'Adhérent modifié avec succès !'; break;
                        case 'deleted': echo 'Adhérent supprimé avec succès !'; break;
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

            <!-- Recherche -->
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

            <!-- Tableau -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="membersTable">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="px-4 py-3">Prénom</th>
                                            <th class="py-3">Nom</th>
                                            <th class="py-3">Email</th>
                                            <th class="py-3">Ville</th>
                                            <th class="py-3">Rôle</th>
                                            <th class="py-3">Activités</th>
                                            <th class="py-3">Inscrit le</th>
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
                                                <td class="py-3"><?= htmlspecialchars($m['address_city'] ?? '') ?></td>
                                                <td class="py-3">
                                                    <span class="badge bg-<?= getRoleColor($m['role']) ?>">
                                                        <?= getRoleName($m['role']) ?>
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <?php if (!empty($m['preferred_activities'])): ?>
                                                        <?php 
                                                        $acts = explode(',', $m['preferred_activities']);
                                                        foreach(array_slice($acts, 0, 2) as $act): 
                                                        ?>
                                                            <span class="badge bg-primary me-1"><?= htmlspecialchars(trim($act)) ?></span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($acts) > 2): ?>
                                                            <span class="badge bg-secondary">+<?= count($acts) - 2 ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <small class="text-muted">Aucune</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3">
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($m['created_at'])) ?></small>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <button class="btn btn-sm btn-outline-warning me-1" 
                                                            onclick="editMember(<?= htmlspecialchars(json_encode($m)) ?>)"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editMemberModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteMemberModal"
                                                            onclick="setDeleteId(<?= $m['id'] ?>, '<?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox display-4"></i>
                                                    <p class="mt-3">Aucun adhérent inscrit</p>
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
        <div class="modal fade" id="addMemberModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Ajouter un adhérent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_add.php" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rôle</label>
                                <select class="form-select" name="role">
                                    <option value="adherent">Adhérent</option>
                                    <option value="visiteur">Visiteur</option>
                                </select>
                                <small class="text-muted">Pour les animateurs, utilisez la section "Gestion des animateurs"</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Code postal</label>
                                    <input type="text" class="form-control" name="postal_code">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Ville</label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Activités préférées</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach($activities as $activity): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="activities[]" value="<?= $activity['id'] ?>" id="add_activity_<?= $activity['id'] ?>">
                                            <label class="form-check-label" for="add_activity_<?= $activity['id'] ?>">
                                                <?= htmlspecialchars($activity['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-2"></i>Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Édition -->
        <div class="modal fade" id="editMemberModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'adhérent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_edit.php" method="POST">
                        <input type="hidden" name="member_id" id="edit_member_id">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rôle</label>
                                <select class="form-select" name="role" id="edit_role">
                                    <option value="adherent">Adhérent</option>
                                    <option value="visiteur">Visiteur</option>
                                </select>
                                <small class="text-muted">Pour les animateurs, utilisez la section "Gestion des animateurs"</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" name="address" id="edit_address">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Code postal</label>
                                    <input type="text" class="form-control" name="postal_code" id="edit_postal_code">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Ville</label>
                                    <input type="text" class="form-control" name="city" id="edit_city">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Activités préférées</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach($activities as $activity): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="activities[]" value="<?= $activity['id'] ?>" id="edit_activity_<?= $activity['id'] ?>">
                                            <label class="form-check-label" for="edit_activity_<?= $activity['id'] ?>">
                                                <?= htmlspecialchars($activity['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
        <div class="modal fade" id="deleteMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Confirmer la suppression</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="process_delete.php" method="POST">
                        <input type="hidden" name="member_id" id="delete_member_id">
                        <div class="modal-body">
                            <p class="mb-0">Êtes-vous sûr de vouloir supprimer <strong id="delete_member_name"></strong> ?</p>
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
        // Recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#membersTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Éditer un membre
        function editMember(member) {
            document.getElementById('edit_member_id').value = member.id;
            document.getElementById('edit_first_name').value = member.first_name;
            document.getElementById('edit_last_name').value = member.last_name;
            document.getElementById('edit_email').value = member.email;
            document.getElementById('edit_role').value = member.role;
            document.getElementById('edit_address').value = member.address || '';
            document.getElementById('edit_postal_code').value = member.address_pc || '';
            document.getElementById('edit_city').value = member.address_city || '';
            
            // Décocher toutes les activités
            document.querySelectorAll('[id^="edit_activity_"]').forEach(cb => cb.checked = false);
            
            // Cocher les activités sélectionnées
            if (member.preferred_activities) {
                const selectedActivities = member.preferred_activities.split(',').map(a => a.trim());
                <?php foreach($activities as $activity): ?>
                    if (selectedActivities.includes('<?= addslashes($activity['name']) ?>')) {
                        document.getElementById('edit_activity_<?= $activity['id'] ?>').checked = true;
                    }
                <?php endforeach; ?>
            }
        }

        // Supprimer un membre
        function setDeleteId(id, name) {
            document.getElementById('delete_member_id').value = id;
            document.getElementById('delete_member_name').textContent = name;
        }
        </script>
    </body>
</html>
