<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Inscription.php';
require_once __DIR__ . '/../../src/models/Activite.php';
require_once __DIR__ . '/../../src/models/Adherent.php';

// Vérifier les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$inscriptionModel = new Inscription();
$activiteModel = new Activite();
$adherentModel = new Adherent();
$dbInstance = Database::getInstance();
if (!$dbInstance) {
    die('Erreur de connexion à la base de données');
}
$db = $dbInstance->getConnection();

// Filtres
$filtreActivite = $_GET['activite'] ?? '';
$filtreStatut = $_GET['statut'] ?? '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'supprimer') {
        $stmt = $db->prepare("DELETE FROM inscriptions WHERE adherent_id = ? AND activite_id = ?");
        if ($stmt->execute([$_POST['adherent_id'], $_POST['activite_id']])) {
            setMessage('Inscription supprimée avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la suppression.', 'danger');
        }
        rediriger('/admin/inscriptions.php');
        
    } elseif ($postAction === 'ajouter') {
        $adherentId = $_POST['adherent_id'];
        $activiteId = $_POST['activite_id'];
        
        // Vérifier si l'inscription existe déjà
        if ($inscriptionModel->estInscrit($adherentId, $activiteId)) {
            setMessage('Cet adhérent est déjà inscrit à cette activité.', 'warning');
        } else {
            // Vérifier si l'activité n'est pas complète
            if ($activiteModel->estComplete($activiteId)) {
                setMessage('Cette activité est complète.', 'danger');
            } else {
                if ($inscriptionModel->inscrire($adherentId, $activiteId)) {
                    setMessage('Inscription ajoutée avec succès !', 'success');
                } else {
                    setMessage('Erreur lors de l\'inscription.', 'danger');
                }
            }
        }
        rediriger('/admin/inscriptions.php');
        
    } elseif ($postAction === 'changer_statut') {
        $stmt = $db->prepare("UPDATE inscriptions SET statut = ? WHERE adherent_id = ? AND activite_id = ?");
        if ($stmt->execute([$_POST['nouveau_statut'], $_POST['adherent_id'], $_POST['activite_id']])) {
            setMessage('Statut mis à jour !', 'success');
        } else {
            setMessage('Erreur lors de la mise à jour.', 'danger');
        }
        rediriger('/admin/inscriptions.php');
    }
}

// Construire la requête avec filtres
$sql = "SELECT i.*, 
        a.nom as adherent_nom, a.prenom as adherent_prenom, a.email as adherent_email,
        act.nom as activite_nom, act.jour_semaine, act.heure_debut
        FROM inscriptions i
        JOIN adherents a ON i.adherent_id = a.id
        JOIN activites act ON i.activite_id = act.id
        WHERE 1=1";

$params = [];

if ($filtreActivite) {
    $sql .= " AND i.activite_id = ?";
    $params[] = $filtreActivite;
}

if ($filtreStatut) {
    $sql .= " AND i.statut = ?";
    $params[] = $filtreStatut;
}

$sql .= " ORDER BY i.date_inscription DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$inscriptions = $stmt->fetchAll();

// Récupérer les listes pour les filtres et le formulaire d'ajout
$activites = $activiteModel->getTous();
$adherents = $adherentModel->getTous();

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <div class="admin-page-header">
        <h1>📝 Gestion des inscriptions</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
    
    <!-- Filtres -->
    <div class="admin-filters">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="activite">Activité</label>
                <select id="activite" name="activite" class="form-control">
                    <option value="">Toutes les activités</option>
                    <?php foreach ($activites as $act): ?>
                        <option value="<?php echo $act['id']; ?>" <?php echo $filtreActivite == $act['id'] ? 'selected' : ''; ?>>
                            <?php echo e($act['nom']); ?> (<?php echo e($act['jour_semaine']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?php echo $filtreStatut === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="annulee" <?php echo $filtreStatut === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">🔍 Filtrer</button>
            <?php if ($filtreActivite || $filtreStatut): ?>
                <a href="/admin/inscriptions.php" class="btn btn-secondary">✕ Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Formulaire d'ajout rapide -->
    <div class="admin-quick-add">
        <h3>➕ Ajouter une inscription</h3>
        <form method="POST" class="quick-add-form">
            <input type="hidden" name="action" value="ajouter">
            <div class="form-group">
                <select name="adherent_id" class="form-control" required>
                    <option value="">-- Choisir un adhérent --</option>
                    <?php foreach ($adherents as $adh): ?>
                        <option value="<?php echo $adh['id']; ?>">
                            <?php echo e($adh['prenom'] . ' ' . $adh['nom']); ?> (<?php echo e($adh['email']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="activite_id" class="form-control" required>
                    <option value="">-- Choisir une activité --</option>
                    <?php foreach ($activites as $act): ?>
                        <?php $nbInscrits = $activiteModel->getNombreInscrits($act['id']); ?>
                        <option value="<?php echo $act['id']; ?>" <?php echo $nbInscrits >= $act['capacite_max'] ? 'disabled' : ''; ?>>
                            <?php echo e($act['nom']); ?> - <?php echo e($act['jour_semaine']); ?> <?php echo substr($act['heure_debut'], 0, 5); ?>
                            (<?php echo $nbInscrits; ?>/<?php echo $act['capacite_max']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Inscrire</button>
        </form>
    </div>
    
    <!-- Liste des inscriptions -->
    <div class="admin-toolbar">
        <span class="count"><?php echo count($inscriptions); ?> inscription(s)</span>
    </div>
    
    <?php if (empty($inscriptions)): ?>
        <div class="alert alert-info">Aucune inscription trouvée.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Adhérent</th>
                        <th>Email</th>
                        <th>Activité</th>
                        <th>Jour/Heure</th>
                        <th>Date inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscriptions as $insc): ?>
                        <tr class="<?php echo $insc['statut'] !== 'active' ? 'inactive-row' : ''; ?>">
                            <td><strong><?php echo e($insc['adherent_prenom'] . ' ' . $insc['adherent_nom']); ?></strong></td>
                            <td><?php echo e($insc['adherent_email']); ?></td>
                            <td><?php echo e($insc['activite_nom']); ?></td>
                            <td>
                                <?php echo e($insc['jour_semaine']); ?><br>
                                <small><?php echo substr($insc['heure_debut'], 0, 5); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($insc['date_inscription'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="changer_statut">
                                    <input type="hidden" name="adherent_id" value="<?php echo $insc['adherent_id']; ?>">
                                    <input type="hidden" name="activite_id" value="<?php echo $insc['activite_id']; ?>">
                                    <select name="nouveau_statut" class="form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="active" <?php echo $insc['statut'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="annulee" <?php echo $insc['statut'] === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                                    </select>
                                </form>
                            </td>
                            <td class="actions">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette inscription ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="adherent_id" value="<?php echo $insc['adherent_id']; ?>">
                                    <input type="hidden" name="activite_id" value="<?php echo $insc['activite_id']; ?>">
                                    <button type="submit" class="btn btn-small btn-danger">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Statistiques -->
    <div class="admin-stats-section">
        <h3>📊 Statistiques des inscriptions</h3>
        <div class="stats-grid">
            <?php foreach ($activites as $act): ?>
                <?php $nbInscrits = $activiteModel->getNombreInscrits($act['id']); ?>
                <div class="mini-stat-card">
                    <strong><?php echo e($act['nom']); ?></strong>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo ($nbInscrits / $act['capacite_max']) * 100; ?>%"></div>
                    </div>
                    <small><?php echo $nbInscrits; ?> / <?php echo $act['capacite_max']; ?> inscrits</small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
