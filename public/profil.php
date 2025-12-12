<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Adherent.php';
require_once __DIR__ . '/../src/models/Animateur.php';
require_once __DIR__ . '/../src/models/Utilisateur.php';

// Vérifier que l'utilisateur est connecté
if (!estConnecte()) {
    setMessage('Vous devez être connecté pour accéder à cette page.', 'danger');
    rediriger('/login.php');
}

$utilisateurModel = new Utilisateur();
$adherentModel = new Adherent();
$animateurModel = new Animateur();

$utilisateur = $utilisateurModel->getParId($_SESSION['utilisateur_id']);
$profil = null;
$typeProfil = null;

// Récupérer le profil selon le rôle
if ($_SESSION['role'] === 'adherent') {
    $profil = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);
    $typeProfil = 'adherent';
} elseif ($_SESSION['role'] === 'animateur') {
    $profil = $animateurModel->getParUtilisateurId($_SESSION['utilisateur_id']);
    $typeProfil = 'animateur';
}

$erreur = '';
$succes = '';

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'modifier_profil' && $profil) {
        $donnees = [
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
        ];
        
        if ($typeProfil === 'adherent') {
            $donnees['adresse'] = trim($_POST['adresse'] ?? '');
            $donnees['date_naissance'] = $_POST['date_naissance'] ?? null;
            $donnees['cotisation_payee'] = $profil['cotisation_payee']; // Ne pas modifier
            $donnees['statut'] = $profil['statut'];
            
            if ($adherentModel->modifier($profil['id'], $donnees)) {
                $succes = 'Profil mis à jour avec succès !';
                $profil = $adherentModel->getParUtilisateurId($_SESSION['utilisateur_id']);
            } else {
                $erreur = 'Erreur lors de la mise à jour du profil.';
            }
        } elseif ($typeProfil === 'animateur') {
            $donnees['specialite'] = trim($_POST['specialite'] ?? '');
            
            if ($animateurModel->modifier($profil['id'], $donnees)) {
                $succes = 'Profil mis à jour avec succès !';
                $profil = $animateurModel->getParUtilisateurId($_SESSION['utilisateur_id']);
            } else {
                $erreur = 'Erreur lors de la mise à jour du profil.';
            }
        }
    }
    
    if ($action === 'changer_mot_de_passe') {
        $motDePasseActuel = $_POST['mot_de_passe_actuel'] ?? '';
        $nouveauMotDePasse = $_POST['nouveau_mot_de_passe'] ?? '';
        $confirmation = $_POST['confirmation'] ?? '';
        
        if (!password_verify($motDePasseActuel, $utilisateur['mot_de_passe'])) {
            $erreur = 'Mot de passe actuel incorrect.';
        } elseif (strlen($nouveauMotDePasse) < 8) {
            $erreur = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($nouveauMotDePasse !== $confirmation) {
            $erreur = 'Les mots de passe ne correspondent pas.';
        } else {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $hash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
            
            if ($stmt->execute([$hash, $_SESSION['utilisateur_id']])) {
                $succes = 'Mot de passe modifié avec succès !';
            } else {
                $erreur = 'Erreur lors du changement de mot de passe.';
            }
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <?php afficherMessage(); ?>
    
    <h1>Mon profil</h1>
    
    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?php echo e($erreur); ?></div>
    <?php endif; ?>
    
    <?php if ($succes): ?>
        <div class="alert alert-success"><?php echo e($succes); ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Informations du compte -->
        <div class="card">
            <div class="card-header">
                <h2>Informations du compte</h2>
            </div>
            <div class="card-body">
                <div class="profile-info-item">
                    <span class="label">Email</span>
                    <span class="value"><?php echo e($utilisateur['email']); ?></span>
                </div>
                <div class="profile-info-item">
                    <span class="label">Rôle</span>
                    <span class="value">
                        <?php 
                        $roles = ['adherent' => 'Adhérent', 'animateur' => 'Animateur', 'bureau' => 'Bureau'];
                        echo $roles[$utilisateur['role']] ?? $utilisateur['role'];
                        ?>
                    </span>
                </div>
                <div class="profile-info-item">
                    <span class="label">Membre depuis</span>
                    <span class="value"><?php echo date('d/m/Y', strtotime($utilisateur['date_creation'])); ?></span>
                </div>
                
                <?php if ($typeProfil === 'adherent' && $profil): ?>
                    <div class="profile-info-item">
                        <span class="label">Cotisation</span>
                        <span class="value">
                            <?php if ($profil['cotisation_payee']): ?>
                                <span class="badge badge-success">Payée</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Non payée</span>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Modifier le profil -->
        <?php if ($profil): ?>
        <div class="card">
            <div class="card-header">
                <h2>Modifier mes informations</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="modifier_profil">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="form-control" 
                                   value="<?php echo e($profil['prenom']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" class="form-control" 
                                   value="<?php echo e($profil['nom']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo e($profil['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" class="form-control" 
                               value="<?php echo e($profil['telephone'] ?? ''); ?>">
                    </div>
                    
                    <?php if ($typeProfil === 'adherent'): ?>
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <textarea id="adresse" name="adresse" class="form-control" rows="2"><?php echo e($profil['adresse'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_naissance">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" class="form-control" 
                                   value="<?php echo e($profil['date_naissance'] ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($typeProfil === 'animateur'): ?>
                        <div class="form-group">
                            <label for="specialite">Spécialité</label>
                            <input type="text" id="specialite" name="specialite" class="form-control" 
                                   value="<?php echo e($profil['specialite'] ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Changer le mot de passe -->
        <div class="card">
            <div class="card-header">
                <h2>Changer le mot de passe</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="changer_mot_de_passe">
                    
                    <div class="form-group">
                        <label for="mot_de_passe_actuel">Mot de passe actuel</label>
                        <input type="password" id="mot_de_passe_actuel" name="mot_de_passe_actuel" 
                               class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nouveau_mot_de_passe">Nouveau mot de passe</label>
                        <input type="password" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" 
                               class="form-control" required minlength="8" 
                               placeholder="Minimum 8 caractères">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirmation" name="confirmation" 
                               class="form-control" required minlength="8">
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">Changer le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.profile-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.profile-info-item:last-child {
    border-bottom: none;
}

.profile-info-item .label {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.profile-info-item .value {
    font-weight: 600;
    color: var(--secondary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
