<?php
require_once __DIR__ . '/../../config/config.php';

// Vérifier que l'utilisateur a les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$db = Database::getInstance()->getConnection();

// Gérer les actions sur les demandes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demandeId = $_POST['demande_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if ($demandeId && $action) {
        if ($action === 'valider') {
            // Récupérer la demande
            $stmt = $db->prepare("SELECT * FROM demandes_inscription WHERE id = ?");
            $stmt->execute([$demandeId]);
            $demande = $stmt->fetch();
            
            if ($demande) {
                // Marquer comme traitée
                $stmt = $db->prepare("UPDATE demandes_inscription SET statut = 'traitee' WHERE id = ?");
                $stmt->execute([$demandeId]);
                
                setMessage('La demande a été marquée comme traitée. Vous pouvez maintenant créer manuellement le compte adhérent.', 'success');
            }
        } elseif ($action === 'refuser') {
            $stmt = $db->prepare("UPDATE demandes_inscription SET statut = 'refusee' WHERE id = ?");
            $stmt->execute([$demandeId]);
            
            setMessage('La demande a été refusée.', 'info');
        } elseif ($action === 'supprimer') {
            $stmt = $db->prepare("DELETE FROM demandes_inscription WHERE id = ?");
            $stmt->execute([$demandeId]);
            
            setMessage('La demande a été supprimée.', 'info');
        }
    }
    
    rediriger('/admin/demandes.php');
}

// Récupérer toutes les demandes
$stmt = $db->query("SELECT * FROM demandes_inscription ORDER BY 
    CASE statut 
        WHEN 'en_attente' THEN 1 
        WHEN 'traitee' THEN 2 
        WHEN 'refusee' THEN 3 
    END, 
    date_demande DESC");
$demandes = $stmt->fetchAll();

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <h1>📧 Demandes d'inscription</h1>
    
    <div class="admin-actions">
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
    
    <?php if (empty($demandes)): ?>
        <div class="alert alert-info">
            Aucune demande d'inscription pour le moment.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Activité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <tr class="<?php echo $demande['statut'] === 'en_attente' ? 'highlight-row' : ''; ?>">
                            <td><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                            <td><?php echo e($demande['nom']); ?></td>
                            <td><?php echo e($demande['prenom']); ?></td>
                            <td><?php echo e($demande['email']); ?></td>
                            <td><?php echo e($demande['telephone']); ?></td>
                            <td><?php echo e($demande['activite_souhaitee'] ?: '-'); ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'en_attente' => 'badge-warning',
                                    'traitee' => 'badge-success',
                                    'refusee' => 'badge-danger'
                                ];
                                $statuts = [
                                    'en_attente' => 'En attente',
                                    'traitee' => 'Traitée',
                                    'refusee' => 'Refusée'
                                ];
                                ?>
                                <span class="badge <?php echo $badges[$demande['statut']]; ?>">
                                    <?php echo $statuts[$demande['statut']]; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Valider cette demande ?');">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <input type="hidden" name="action" value="valider">
                                        <button type="submit" class="btn btn-success btn-small">✓ Valider</button>
                                    </form>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Refuser cette demande ?');">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <input type="hidden" name="action" value="refuser">
                                        <button type="submit" class="btn btn-danger btn-small">✗ Refuser</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Supprimer cette demande ?');">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <input type="hidden" name="action" value="supprimer">
                                        <button type="submit" class="btn btn-danger btn-small">🗑️ Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($demande['message']): ?>
                            <tr class="message-row">
                                <td colspan="8">
                                    <strong>Message :</strong> <?php echo nl2br(e($demande['message'])); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.admin-actions {
    margin-bottom: 2rem;
}

.table-responsive {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead {
    background-color: var(--dark-color);
    color: white;
}

.admin-table th,
.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table tbody tr:hover {
    background-color: #f8f9fa;
}

.highlight-row {
    background-color: #fff3cd;
}

.message-row td {
    background-color: #f8f9fa;
    font-size: 0.9rem;
    font-style: italic;
}

.btn-success {
    background-color: var(--success-color);
    color: white;
}

.btn-success:hover {
    background-color: #27ae60;
}
</style>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
