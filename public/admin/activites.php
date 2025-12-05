<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Activite.php';
require_once __DIR__ . '/../../src/models/Animateur.php';

// Vérifier les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$activiteModel = new Activite();
$animateurModel = new Animateur();
$db = Database::getInstance()->getConnection();

$action = $_GET['action'] ?? 'liste';
$id = $_GET['id'] ?? null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'creer') {
        $donnees = [
            'nom' => $_POST['nom'],
            'description' => $_POST['description'] ?? null,
            'animateur_id' => $_POST['animateur_id'],
            'jour_semaine' => $_POST['jour_semaine'],
            'heure_debut' => $_POST['heure_debut'],
            'heure_fin' => $_POST['heure_fin'] ?? null,
            'duree_minutes' => $_POST['duree_minutes'] ?? 60,
            'capacite_max' => $_POST['capacite_max'] ?? 20,
            'lieu' => $_POST['lieu'] ?? null
        ];
        
        if ($activiteModel->creer($donnees)) {
            setMessage('Activité créée avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la création de l\'activité.', 'danger');
        }
        
        rediriger('/admin/activites.php');
        
    } elseif ($postAction === 'modifier') {
        $donnees = [
            'nom' => $_POST['nom'],
            'description' => $_POST['description'] ?? null,
            'animateur_id' => $_POST['animateur_id'],
            'jour_semaine' => $_POST['jour_semaine'],
            'heure_debut' => $_POST['heure_debut'],
            'heure_fin' => $_POST['heure_fin'] ?? null,
            'duree_minutes' => $_POST['duree_minutes'] ?? 60,
            'capacite_max' => $_POST['capacite_max'] ?? 20,
            'lieu' => $_POST['lieu'] ?? null,
            'statut' => $_POST['statut'] ?? 'active'
        ];
        
        if ($activiteModel->modifier($_POST['id'], $donnees)) {
            setMessage('Activité modifiée avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la modification.', 'danger');
        }
        
        rediriger('/admin/activites.php');
        
    } elseif ($postAction === 'supprimer') {
        // Vérifier s'il y a des inscriptions
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM inscriptions WHERE activite_id = ?");
        $stmt->execute([$_POST['id']]);
        $nbInscriptions = $stmt->fetch()['total'];
        
        if ($nbInscriptions > 0) {
            setMessage("Impossible de supprimer : {$nbInscriptions} inscription(s) active(s).", 'danger');
        } else {
            if ($activiteModel->supprimer($_POST['id'])) {
                setMessage('Activité supprimée avec succès !', 'success');
            } else {
                setMessage('Erreur lors de la suppression.', 'danger');
            }
        }
        
        rediriger('/admin/activites.php');
    }
}

// Récupérer les données selon l'action
$activite = null;
if (($action === 'modifier' || $action === 'voir') && $id) {
    $activite = $activiteModel->getParId($id);
    if (!$activite) {
        setMessage('Activité non trouvée.', 'danger');
        rediriger('/admin/activites.php');
    }
}

// Récupérer toutes les activités (y compris inactives pour l'admin)
$stmt = $db->query("SELECT a.*, 
    CONCAT(an.prenom, ' ', an.nom) as animateur_nom,
    (SELECT COUNT(*) FROM inscriptions WHERE activite_id = a.id AND statut = 'active') as nb_inscrits
    FROM activites a
    LEFT JOIN animateurs an ON a.animateur_id = an.id
    ORDER BY a.statut DESC, 
    FIELD(a.jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
    a.heure_debut");
$activites = $stmt->fetchAll();

// Récupérer tous les animateurs pour les selects
$animateurs = $animateurModel->getTous();

$joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <div class="admin-page-header">
        <h1>🏋️ Gestion des activités</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
    
    <?php if ($action === 'liste'): ?>
        <!-- Liste des activités -->
        <div class="admin-toolbar">
            <a href="/admin/activites.php?action=ajouter" class="btn btn-primary">➕ Nouvelle activité</a>
            <span class="count"><?php echo count($activites); ?> activité(s)</span>
        </div>
        
        <?php if (empty($activites)): ?>
            <div class="alert alert-info">Aucune activité enregistrée.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Activité</th>
                            <th>Jour</th>
                            <th>Horaire</th>
                            <th>Animateur</th>
                            <th>Lieu</th>
                            <th>Inscrits</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activites as $act): ?>
                            <tr class="<?php echo $act['statut'] !== 'active' ? 'inactive-row' : ''; ?>">
                                <td>
                                    <strong><?php echo e($act['nom']); ?></strong>
                                    <?php if ($act['description']): ?>
                                        <br><small class="text-muted"><?php echo e(substr($act['description'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($act['jour_semaine']); ?></td>
                                <td>
                                    <?php echo substr($act['heure_debut'], 0, 5); ?>
                                    <?php if ($act['heure_fin']): ?>
                                        - <?php echo substr($act['heure_fin'], 0, 5); ?>
                                    <?php endif; ?>
                                    <br><small>(<?php echo $act['duree_minutes']; ?> min)</small>
                                </td>
                                <td><?php echo e($act['animateur_nom'] ?? 'Non assigné'); ?></td>
                                <td><?php echo e($act['lieu'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $act['nb_inscrits'] >= $act['capacite_max'] ? 'badge-danger' : 'badge-info'; ?>">
                                        <?php echo $act['nb_inscrits']; ?> / <?php echo $act['capacite_max']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $act['statut'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($act['statut']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="/admin/activites.php?action=voir&id=<?php echo $act['id']; ?>" class="btn btn-small btn-secondary">👁️</a>
                                    <a href="/admin/activites.php?action=modifier&id=<?php echo $act['id']; ?>" class="btn btn-small btn-secondary">✏️</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette activité ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?php echo $act['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger">🗑️</button>
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
            <h2>Nouvelle activité</h2>
            
            <?php if (empty($animateurs)): ?>
                <div class="alert alert-warning">
                    Vous devez d'abord <a href="/admin/animateurs.php?action=ajouter">créer un animateur</a> avant de créer une activité.
                </div>
            <?php else: ?>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="creer">
                    
                    <div class="form-group">
                        <label for="nom">Nom de l'activité *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required placeholder="Ex: Yoga, Fitness, Zumba...">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Description de l'activité..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="animateur_id">Animateur *</label>
                            <select id="animateur_id" name="animateur_id" class="form-control" required>
                                <option value="">-- Choisir un animateur --</option>
                                <?php foreach ($animateurs as $anim): ?>
                                    <option value="<?php echo $anim['id']; ?>">
                                        <?php echo e($anim['prenom'] . ' ' . $anim['nom']); ?>
                                        <?php if ($anim['specialite']): ?> (<?php echo e($anim['specialite']); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jour_semaine">Jour *</label>
                            <select id="jour_semaine" name="jour_semaine" class="form-control" required>
                                <?php foreach ($joursSemaine as $jour): ?>
                                    <option value="<?php echo $jour; ?>"><?php echo $jour; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="heure_debut">Heure de début *</label>
                            <input type="time" id="heure_debut" name="heure_debut" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="heure_fin">Heure de fin</label>
                            <input type="time" id="heure_fin" name="heure_fin" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="duree_minutes">Durée (min)</label>
                            <input type="number" id="duree_minutes" name="duree_minutes" class="form-control" value="60" min="15" max="240">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="lieu">Lieu</label>
                            <input type="text" id="lieu" name="lieu" class="form-control" placeholder="Ex: Salle principale, Salle zen...">
                        </div>
                        <div class="form-group">
                            <label for="capacite_max">Capacité max</label>
                            <input type="number" id="capacite_max" name="capacite_max" class="form-control" value="20" min="1" max="100">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">✓ Créer l'activité</button>
                        <a href="/admin/activites.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
    <?php elseif ($action === 'modifier' && $activite): ?>
        <!-- Formulaire de modification -->
        <div class="admin-form-container">
            <h2>Modifier l'activité</h2>
            
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" value="<?php echo $activite['id']; ?>">
                
                <div class="form-group">
                    <label for="nom">Nom de l'activité *</label>
                    <input type="text" id="nom" name="nom" class="form-control" required value="<?php echo e($activite['nom']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($activite['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="animateur_id">Animateur *</label>
                        <select id="animateur_id" name="animateur_id" class="form-control" required>
                            <option value="">-- Choisir un animateur --</option>
                            <?php foreach ($animateurs as $anim): ?>
                                <option value="<?php echo $anim['id']; ?>" <?php echo $activite['animateur_id'] == $anim['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($anim['prenom'] . ' ' . $anim['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jour_semaine">Jour *</label>
                        <select id="jour_semaine" name="jour_semaine" class="form-control" required>
                            <?php foreach ($joursSemaine as $jour): ?>
                                <option value="<?php echo $jour; ?>" <?php echo $activite['jour_semaine'] === $jour ? 'selected' : ''; ?>><?php echo $jour; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="heure_debut">Heure de début *</label>
                        <input type="time" id="heure_debut" name="heure_debut" class="form-control" required value="<?php echo substr($activite['heure_debut'], 0, 5); ?>">
                    </div>
                    <div class="form-group">
                        <label for="heure_fin">Heure de fin</label>
                        <input type="time" id="heure_fin" name="heure_fin" class="form-control" value="<?php echo $activite['heure_fin'] ? substr($activite['heure_fin'], 0, 5) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="duree_minutes">Durée (min)</label>
                        <input type="number" id="duree_minutes" name="duree_minutes" class="form-control" value="<?php echo $activite['duree_minutes']; ?>" min="15" max="240">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="lieu">Lieu</label>
                        <input type="text" id="lieu" name="lieu" class="form-control" value="<?php echo e($activite['lieu'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="capacite_max">Capacité max</label>
                        <input type="number" id="capacite_max" name="capacite_max" class="form-control" value="<?php echo $activite['capacite_max']; ?>" min="1" max="100">
                    </div>
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="active" <?php echo $activite['statut'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $activite['statut'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✓ Enregistrer les modifications</button>
                    <a href="/admin/activites.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'voir' && $activite): ?>
        <!-- Détails de l'activité -->
        <div class="admin-detail-container">
            <h2><?php echo e($activite['nom']); ?></h2>
            
            <div class="detail-grid">
                <div class="detail-card">
                    <h3>📋 Informations</h3>
                    <p><strong>Description :</strong> <?php echo e($activite['description'] ?? 'Aucune'); ?></p>
                    <p><strong>Animateur :</strong> <?php echo e($activite['animateur_nom']); ?></p>
                    <p><strong>Lieu :</strong> <?php echo e($activite['lieu'] ?? 'Non défini'); ?></p>
                    <p><strong>Statut :</strong> 
                        <span class="badge <?php echo $activite['statut'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo ucfirst($activite['statut']); ?>
                        </span>
                    </p>
                </div>
                
                <div class="detail-card">
                    <h3>⏰ Horaires</h3>
                    <p><strong>Jour :</strong> <?php echo e($activite['jour_semaine']); ?></p>
                    <p><strong>Heure :</strong> <?php echo substr($activite['heure_debut'], 0, 5); ?>
                        <?php if ($activite['heure_fin']): ?> - <?php echo substr($activite['heure_fin'], 0, 5); ?><?php endif; ?>
                    </p>
                    <p><strong>Durée :</strong> <?php echo $activite['duree_minutes']; ?> minutes</p>
                    <p><strong>Capacité :</strong> <?php echo $activiteModel->getNombreInscrits($activite['id']); ?> / <?php echo $activite['capacite_max']; ?></p>
                </div>
            </div>
            
            <h3>👥 Liste des inscrits</h3>
            <?php
            $stmt = $db->prepare("SELECT a.nom, a.prenom, a.email, i.date_inscription 
                                  FROM inscriptions i 
                                  JOIN adherents a ON i.adherent_id = a.id 
                                  WHERE i.activite_id = ? AND i.statut = 'active'
                                  ORDER BY i.date_inscription");
            $stmt->execute([$activite['id']]);
            $inscrits = $stmt->fetchAll();
            ?>
            
            <?php if (empty($inscrits)): ?>
                <div class="alert alert-info">Aucun inscrit pour cette activité.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscrits as $inscrit): ?>
                                <tr>
                                    <td><?php echo e($inscrit['prenom'] . ' ' . $inscrit['nom']); ?></td>
                                    <td><?php echo e($inscrit['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($inscrit['date_inscription'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <a href="/admin/activites.php?action=modifier&id=<?php echo $activite['id']; ?>" class="btn btn-primary">✏️ Modifier</a>
                <a href="/admin/activites.php" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
