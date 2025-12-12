<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Utilisateur.php';
require_once __DIR__ . '/../../src/models/Token.php';
require_once __DIR__ . '/../../src/services/MailService.php';

// Vérifier les droits d'administration
if (!estConnecte() || ($_SESSION['role'] ?? '') !== 'bureau') {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$utilisateurModel = new Utilisateur();
$tokenModel = new Token();
$db = Database::getInstance()->getConnection();

$action = $_GET['action'] ?? 'liste';
$id = $_GET['id'] ?? null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'modifier_role') {
        $userId = $_POST['id'] ?? null;
        $newRole = $_POST['role'] ?? null;
        
        // Empêcher de modifier son propre rôle
        if ($userId == $_SESSION['utilisateur_id']) {
            setMessage('Vous ne pouvez pas modifier votre propre rôle.', 'danger');
        } elseif ($userId && in_array($newRole, ['visiteur', 'adherent', 'animateur', 'bureau'])) {
            $stmt = $db->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
            if ($stmt->execute([$newRole, $userId])) {
                setMessage('Rôle modifié avec succès !', 'success');
            } else {
                setMessage('Erreur lors de la modification du rôle.', 'danger');
            }
        }
        
        rediriger('/admin/utilisateurs.php');
        
    } elseif ($postAction === 'supprimer') {
        $userId = $_POST['id'] ?? null;
        
        // Empêcher de supprimer son propre compte
        if ($userId == $_SESSION['utilisateur_id']) {
            setMessage('Vous ne pouvez pas supprimer votre propre compte.', 'danger');
        } elseif ($userId) {
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
            if ($stmt->execute([$userId])) {
                setMessage('Utilisateur supprimé avec succès !', 'success');
            } else {
                setMessage('Erreur lors de la suppression.', 'danger');
            }
        }
        
        rediriger('/admin/utilisateurs.php');
        
    } elseif ($postAction === 'creer_bureau') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setMessage('Adresse email invalide.', 'danger');
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                setMessage('Cet email existe déjà.', 'danger');
            } else {
                // Créer l'utilisateur avec un mot de passe temporaire (non utilisable)
                $tempPassword = bin2hex(random_bytes(32));
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, 'bureau')");
                
                if ($stmt->execute([$email, $hashedPassword])) {
                    $userId = $db->lastInsertId();
                    
                    // Créer un token pour définir le mot de passe
                    $token = $tokenModel->creer($userId, 'password_set', 72); // 72 heures
                    
                    if ($token) {
                        // Envoyer l'email d'invitation
                        $lien = "https://" . $_SERVER['HTTP_HOST'] . "/definir-mot-de-passe.php?token=" . $token;
                        
                        $sujet = "Invitation à rejoindre Fit&Fun - Définissez votre mot de passe";
                        $contenu = "
                            <h2>Bienvenue dans l'équipe Fit&Fun !</h2>
                            <p>Vous avez été invité(e) à rejoindre l'équipe d'administration de Fit&Fun en tant que membre du bureau.</p>
                            <p>Cliquez sur le lien ci-dessous pour définir votre mot de passe et activer votre compte :</p>
                            <p style='text-align: center; margin: 30px 0;'>
                                <a href='{$lien}' style='background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>
                                    Définir mon mot de passe
                                </a>
                            </p>
                            <p><strong>Ce lien est valable pendant 72 heures.</strong></p>
                            <p>Si vous n'avez pas demandé cette invitation, vous pouvez ignorer cet email.</p>
                        ";
                        
                        if (MailService::envoyer($email, $sujet, $contenu)) {
                            setMessage('Invitation envoyée ! Un email a été envoyé à ' . e($email) . ' pour définir son mot de passe.', 'success');
                        } else {
                            setMessage('Compte créé mais erreur lors de l\'envoi de l\'email. Lien direct : ' . $lien, 'warning');
                        }
                    } else {
                        setMessage('Compte créé mais erreur lors de la génération du token.', 'warning');
                    }
                } else {
                    setMessage('Erreur lors de la création.', 'danger');
                }
            }
        }
        
        rediriger('/admin/utilisateurs.php');
    }
}

// Récupérer tous les utilisateurs
$stmt = $db->query("SELECT * FROM utilisateurs ORDER BY role DESC, email ASC");
$utilisateurs = $stmt->fetchAll();

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des utilisateurs</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour</a>
    </div>

    <?php afficherMessage(); ?>

    <!-- Créer un nouveau membre du bureau -->
    <div class="admin-section">
        <h2>Inviter un membre du bureau</h2>
        <p class="text-muted mb-2">Un email sera envoyé à l'utilisateur pour qu'il définisse son mot de passe.</p>
        <form method="POST" class="admin-form-inline">
            <input type="hidden" name="action" value="creer_bureau">
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="email@exemple.fr">
                </div>
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">📧 Envoyer l'invitation</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="admin-section">
        <h2>Liste des utilisateurs</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Rôle actuel</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr class="<?php echo $user['id'] == $_SESSION['utilisateur_id'] ? 'highlight-row' : ''; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <?php echo e($user['email']); ?>
                                <?php if ($user['id'] == $_SESSION['utilisateur_id']): ?>
                                    <span class="badge badge-info">Vous</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $roleLabels = [
                                    'visiteur' => '<span class="badge badge-secondary">Visiteur</span>',
                                    'adherent' => '<span class="badge badge-info">Adhérent</span>',
                                    'animateur' => '<span class="badge badge-warning">Animateur</span>',
                                    'bureau' => '<span class="badge badge-primary">Bureau</span>'
                                ];
                                echo $roleLabels[$user['role']] ?? $user['role'];
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['utilisateur_id']): ?>
                                    <div class="actions">
                                        <!-- Modifier le rôle -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="modifier_role">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                                <option value="visiteur" <?php echo $user['role'] === 'visiteur' ? 'selected' : ''; ?>>Visiteur</option>
                                                <option value="adherent" <?php echo $user['role'] === 'adherent' ? 'selected' : ''; ?>>Adhérent</option>
                                                <option value="animateur" <?php echo $user['role'] === 'animateur' ? 'selected' : ''; ?>>Animateur</option>
                                                <option value="bureau" <?php echo $user['role'] === 'bureau' ? 'selected' : ''; ?>>Bureau</option>
                                            </select>
                                        </form>
                                        
                                        <!-- Supprimer -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    data-confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="admin-section">
        <div class="info-box">
            <strong>ℹ️ Information sur les rôles</strong>
            <ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
                <li><strong>Visiteur</strong> : Peut consulter le site sans actions</li>
                <li><strong>Adhérent</strong> : Peut s'inscrire aux activités</li>
                <li><strong>Animateur</strong> : Peut gérer ses séances</li>
                <li><strong>Bureau</strong> : Accès complet à l'administration</li>
            </ul>
        </div>
    </div>
</div>

<style>
.admin-form-inline .form-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.admin-form-inline .form-group {
    flex: 1;
    min-width: 200px;
}

.form-control-sm {
    padding: 0.4rem 0.6rem;
    font-size: 0.85rem;
}

.info-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: 0.9rem;
    color: #0369a1;
}

.info-box ul {
    margin-bottom: 0;
}

.badge-primary {
    background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%);
}

.badge-warning {
    background: #f0b429;
    color: #1a1a1a;
}

.badge-info {
    background: #3f8cff;
}

.badge-secondary {
    background: #6b7280;
}
</style>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
