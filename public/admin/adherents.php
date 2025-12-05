<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Adherent.php';
require_once __DIR__ . '/../../src/models/Utilisateur.php';

// Vérifier les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$adherentModel = new Adherent();
$utilisateurModel = new Utilisateur();
$db = Database::getInstance()->getConnection();

$action = $_GET['action'] ?? 'liste';
$id = $_GET['id'] ?? null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'creer') {
        // Créer un compte utilisateur d'abord
        $email = $_POST['email'];
        $motDePasse = $_POST['mot_de_passe'] ?? 'password123';
        
        $utilisateurId = $utilisateurModel->inscription($email, $motDePasse, 'adherent');
        
        if ($utilisateurId) {
            $donnees = [
                'utilisateur_id' => $utilisateurId,
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'telephone' => $_POST['telephone'] ?? null,
                'adresse' => $_POST['adresse'] ?? null,
                'date_naissance' => $_POST['date_naissance'] ?: null,
                'cotisation_payee' => isset($_POST['cotisation_payee']) ? 1 : 0
            ];
            
            if ($adherentModel->creer($donnees)) {
                setMessage('Adhérent créé avec succès !', 'success');
            } else {
                setMessage('Erreur lors de la création de l\'adhérent.', 'danger');
            }
        } else {
            setMessage('Erreur : cet email existe déjà.', 'danger');
        }
        
        rediriger('/admin/adherents.php');
        
    } elseif ($postAction === 'modifier') {
        $donnees = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'] ?? null,
            'adresse' => $_POST['adresse'] ?? null,
            'date_naissance' => $_POST['date_naissance'] ?: null,
            'cotisation_payee' => isset($_POST['cotisation_payee']) ? 1 : 0,
            'statut' => $_POST['statut'] ?? 'actif'
        ];
        
        if ($adherentModel->modifier($_POST['id'], $donnees)) {
            setMessage('Adhérent modifié avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la modification.', 'danger');
        }
        
        rediriger('/admin/adherents.php');
        
    } elseif ($postAction === 'supprimer') {
        $adherent = $adherentModel->getParId($_POST['id']);
        
        // Supprimer l'adhérent
        if ($adherentModel->supprimer($_POST['id'])) {
            // Supprimer aussi le compte utilisateur associé
            if ($adherent['utilisateur_id']) {
                $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
                $stmt->execute([$adherent['utilisateur_id']]);
            }
            setMessage('Adhérent supprimé avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la suppression.', 'danger');
        }
        
        rediriger('/admin/adherents.php');
        
    } elseif ($postAction === 'toggle_cotisation') {
        $adherent = $adherentModel->getParId($_POST['id']);
        $nouvelleCotisation = $adherent['cotisation_payee'] ? 0 : 1;
        
        $stmt = $db->prepare("UPDATE adherents SET cotisation_payee = ? WHERE id = ?");
        $stmt->execute([$nouvelleCotisation, $_POST['id']]);
        
        setMessage('Cotisation mise à jour !', 'success');
        rediriger('/admin/adherents.php');
    }
}

// Récupérer les données selon l'action
$adherent = null;
if ($action === 'modifier' && $id) {
    $adherent = $adherentModel->getParId($id);
    if (!$adherent) {
        setMessage('Adhérent non trouvé.', 'danger');
        rediriger('/admin/adherents.php');
    }
}

$adherents = $adherentModel->getTous();

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <div class="admin-page-header">
        <h1>👥 Gestion des adhérents</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
    
    <?php if ($action === 'liste'): ?>
        <!-- Liste des adhérents -->
        <div class="admin-toolbar">
            <a href="/admin/adherents.php?action=ajouter" class="btn btn-primary">➕ Nouvel adhérent</a>
            <span class="count"><?php echo count($adherents); ?> adhérent(s)</span>
        </div>
        
        <?php if (empty($adherents)): ?>
            <div class="alert alert-info">Aucun adhérent enregistré.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Cotisation</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adherents as $adh): ?>
                            <tr>
                                <td><?php echo e($adh['nom']); ?></td>
                                <td><?php echo e($adh['prenom']); ?></td>
                                <td><?php echo e($adh['email']); ?></td>
                                <td><?php echo e($adh['telephone'] ?? '-'); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_cotisation">
                                        <input type="hidden" name="id" value="<?php echo $adh['id']; ?>">
                                        <button type="submit" class="badge <?php echo $adh['cotisation_payee'] ? 'badge-success' : 'badge-warning'; ?>" style="cursor: pointer; border: none;">
                                            <?php echo $adh['cotisation_payee'] ? '✓ Payée' : '✗ Non payée'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge <?php echo $adh['statut'] === 'actif' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($adh['statut']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="/admin/adherents.php?action=modifier&id=<?php echo $adh['id']; ?>" class="btn btn-small btn-secondary">✏️ Modifier</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet adhérent ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?php echo $adh['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">🗑️ Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    <?php elseif ($action === 'ajouter'): ?>
        <!-- Formulaire d'ajout -->
        <div class="admin-form-container">
            <h2>Nouvel adhérent</h2>
            
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="creer">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe (défaut: password123)</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="Laisser vide pour le mot de passe par défaut">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="cotisation_payee" value="1">
                        Cotisation payée
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✓ Créer l'adhérent</button>
                    <a href="/admin/adherents.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'modifier' && $adherent): ?>
        <!-- Formulaire de modification -->
        <div class="admin-form-container">
            <h2>Modifier l'adhérent</h2>
            
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" value="<?php echo $adherent['id']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required value="<?php echo e($adherent['nom']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required value="<?php echo e($adherent['prenom']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo e($adherent['email']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" value="<?php echo e($adherent['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" class="form-control" value="<?php echo $adherent['date_naissance'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <textarea id="adresse" name="adresse" class="form-control" rows="2"><?php echo e($adherent['adresse'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="actif" <?php echo $adherent['statut'] === 'actif' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo $adherent['statut'] === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label" style="margin-top: 2rem;">
                            <input type="checkbox" name="cotisation_payee" value="1" <?php echo $adherent['cotisation_payee'] ? 'checked' : ''; ?>>
                            Cotisation payée
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✓ Enregistrer les modifications</button>
                    <a href="/admin/adherents.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
