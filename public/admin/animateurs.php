<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Animateur.php';
require_once __DIR__ . '/../../src/models/Utilisateur.php';
require_once __DIR__ . '/../../src/models/Token.php';
require_once __DIR__ . '/../../src/services/MailService.php';

// Vérifier les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$animateurModel = new Animateur();
$utilisateurModel = new Utilisateur();
$tokenModel = new Token();
$dbInstance = Database::getInstance();
if (!$dbInstance) {
    die('Erreur de connexion à la base de données');
}
$db = $dbInstance->getConnection();

$action = $_GET['action'] ?? 'liste';
$id = $_GET['id'] ?? null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'creer') {
        $email = trim($_POST['email']);
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        
        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            setMessage('Erreur : cet email existe déjà.', 'danger');
        } else {
            // Créer l'utilisateur avec un mot de passe temporaire
            $tempPassword = bin2hex(random_bytes(32));
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, 'animateur')");
            
            if ($stmt->execute([$email, $hashedPassword])) {
                $utilisateurId = $db->lastInsertId();
                
                $donnees = [
                    'utilisateur_id' => $utilisateurId,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $_POST['telephone'] ?? null,
                    'specialite' => $_POST['specialite'] ?? null
                ];
                
                if ($animateurModel->creer($donnees)) {
                    // Créer un token pour définir le mot de passe
                    $token = $tokenModel->creer($utilisateurId, 'password_set', 72);
                    
                    if ($token) {
                        $lien = "https://" . $_SERVER['HTTP_HOST'] . "/definir-mot-de-passe.php?token=" . $token;
                        
                        $sujet = "Bienvenue chez Fit&Fun - Définissez votre mot de passe";
                        $contenu = "
                            <h2>Bienvenue {$prenom} !</h2>
                            <p>Vous avez été ajouté(e) en tant qu'animateur/animatrice chez Fit&Fun.</p>
                            <p>Cliquez sur le lien ci-dessous pour définir votre mot de passe et activer votre compte :</p>
                            <p style='text-align: center; margin: 30px 0;'>
                                <a href='{$lien}' style='background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>
                                    Définir mon mot de passe
                                </a>
                            </p>
                            <p><strong>Ce lien est valable pendant 72 heures.</strong></p>
                            <p>Une fois connecté(e), vous pourrez gérer vos séances depuis l'espace \"Mes séances\".</p>
                        ";
                        
                        if (MailService::envoyer($email, $sujet, $contenu)) {
                            setMessage('Animateur créé ! Un email a été envoyé à ' . e($email) . ' pour définir son mot de passe.', 'success');
                        } else {
                            setMessage('Animateur créé mais erreur lors de l\'envoi de l\'email. Lien : ' . $lien, 'warning');
                        }
                    } else {
                        setMessage('Animateur créé mais erreur lors de la génération du token.', 'warning');
                    }
                } else {
                    setMessage('Erreur lors de la création de l\'animateur.', 'danger');
                }
            } else {
                setMessage('Erreur lors de la création du compte utilisateur.', 'danger');
            }
        }
        
        rediriger('/admin/animateurs.php');
        
    } elseif ($postAction === 'modifier') {
        $donnees = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'] ?? null,
            'specialite' => $_POST['specialite'] ?? null
        ];
        
        if ($animateurModel->modifier($_POST['id'], $donnees)) {
            setMessage('Animateur modifié avec succès !', 'success');
        } else {
            setMessage('Erreur lors de la modification.', 'danger');
        }
        
        rediriger('/admin/animateurs.php');
        
    } elseif ($postAction === 'supprimer') {
        // Vérifier s'il y a des activités associées
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM activites WHERE animateur_id = ?");
        $stmt->execute([$_POST['id']]);
        $nbActivites = $stmt->fetch()['total'];
        
        if ($nbActivites > 0) {
            setMessage("Impossible de supprimer : {$nbActivites} activité(s) associée(s).", 'danger');
        } else {
            $animateur = $animateurModel->getParId($_POST['id']);
            
            if ($animateurModel->supprimer($_POST['id'])) {
                // Supprimer aussi le compte utilisateur associé
                if ($animateur['utilisateur_id']) {
                    $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
                    $stmt->execute([$animateur['utilisateur_id']]);
                }
                setMessage('Animateur supprimé avec succès !', 'success');
            } else {
                setMessage('Erreur lors de la suppression.', 'danger');
            }
        }
        
        rediriger('/admin/animateurs.php');
    }
}

// Récupérer les données selon l'action
$animateur = null;
if (($action === 'modifier' || $action === 'voir') && $id) {
    $animateur = $animateurModel->getParId($id);
    if (!$animateur) {
        setMessage('Animateur non trouvé.', 'danger');
        rediriger('/admin/animateurs.php');
    }
}

$animateurs = $animateurModel->getTous();

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <div class="admin-page-header">
        <h1>🎓 Gestion des animateurs</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
    
    <?php if ($action === 'liste'): ?>
        <!-- Liste des animateurs -->
        <div class="admin-toolbar">
            <a href="/admin/animateurs.php?action=ajouter" class="btn btn-primary">➕ Nouvel animateur</a>
            <span class="count"><?php echo count($animateurs); ?> animateur(s)</span>
        </div>
        
        <?php if (empty($animateurs)): ?>
            <div class="alert alert-info">Aucun animateur enregistré.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Spécialité</th>
                            <th>Activités</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animateurs as $anim): ?>
                            <?php
                            $activites = $animateurModel->getActivites($anim['id']);
                            ?>
                            <tr>
                                <td><?php echo e($anim['nom']); ?></td>
                                <td><?php echo e($anim['prenom']); ?></td>
                                <td><?php echo e($anim['email']); ?></td>
                                <td><?php echo e($anim['telephone'] ?? '-'); ?></td>
                                <td><?php echo e($anim['specialite'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo count($activites); ?> activité(s)</span>
                                </td>
                                <td class="actions">
                                    <a href="/admin/animateurs.php?action=voir&id=<?php echo $anim['id']; ?>" class="btn btn-small btn-secondary">👁️</a>
                                    <a href="/admin/animateurs.php?action=modifier&id=<?php echo $anim['id']; ?>" class="btn btn-small btn-secondary">✏️</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet animateur ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?php echo $anim['id']; ?>">
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
            <h2>Nouvel animateur</h2>
            <p class="text-muted">Un email sera envoyé à l'animateur pour qu'il définisse son mot de passe.</p>
            
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
                    <small class="form-text">Un email d'invitation sera envoyé à cette adresse.</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <input type="text" id="specialite" name="specialite" class="form-control" placeholder="Ex: Yoga, Fitness, Zumba...">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✓ Créer l'animateur</button>
                    <a href="/admin/animateurs.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'modifier' && $animateur): ?>
        <!-- Formulaire de modification -->
        <div class="admin-form-container">
            <h2>Modifier l'animateur</h2>
            
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" value="<?php echo $animateur['id']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required value="<?php echo e($animateur['nom']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required value="<?php echo e($animateur['prenom']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo e($animateur['email']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" value="<?php echo e($animateur['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <input type="text" id="specialite" name="specialite" class="form-control" value="<?php echo e($animateur['specialite'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✓ Enregistrer les modifications</button>
                    <a href="/admin/animateurs.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'voir' && $animateur): ?>
        <!-- Détails de l'animateur -->
        <div class="admin-detail-container">
            <h2><?php echo e($animateur['prenom'] . ' ' . $animateur['nom']); ?></h2>
            
            <div class="detail-grid">
                <div class="detail-card">
                    <h3>📋 Informations</h3>
                    <p><strong>Email :</strong> <?php echo e($animateur['email']); ?></p>
                    <p><strong>Téléphone :</strong> <?php echo e($animateur['telephone'] ?? 'Non renseigné'); ?></p>
                    <p><strong>Spécialité :</strong> <?php echo e($animateur['specialite'] ?? 'Non définie'); ?></p>
                </div>
            </div>
            
            <h3>🏋️ Activités encadrées</h3>
            <?php $activites = $animateurModel->getActivites($animateur['id']); ?>
            
            <?php if (empty($activites)): ?>
                <div class="alert alert-info">Aucune activité assignée.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Activité</th>
                                <th>Jour</th>
                                <th>Horaire</th>
                                <th>Lieu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activites as $act): ?>
                                <tr>
                                    <td><?php echo e($act['nom']); ?></td>
                                    <td><?php echo e($act['jour_semaine']); ?></td>
                                    <td><?php echo substr($act['heure_debut'], 0, 5); ?></td>
                                    <td><?php echo e($act['lieu'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <a href="/admin/animateurs.php?action=modifier&id=<?php echo $animateur['id']; ?>" class="btn btn-primary">✏️ Modifier</a>
                <a href="/admin/animateurs.php" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
