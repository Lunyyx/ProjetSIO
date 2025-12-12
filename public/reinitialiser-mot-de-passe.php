<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Token.php';
require_once __DIR__ . '/../src/models/Utilisateur.php';

$token = $_GET['token'] ?? '';
$erreur = '';
$succes = '';
$tokenValide = false;
$tokenData = null;

// Vérifier le token
if ($token) {
    $tokenData = Token::verifier($token, 'password_reset');
    if ($tokenData) {
        $tokenValide = true;
    } else {
        $erreur = "Ce lien est invalide ou a expiré. Veuillez refaire une demande de réinitialisation.";
    }
} else {
    $erreur = "Lien invalide.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValide) {
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    if (strlen($motDePasse) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($motDePasse !== $confirmation) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // Mettre à jour le mot de passe
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
        $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);
        
        if ($stmt->execute([$motDePasseHash, $tokenData['utilisateur_id']])) {
            // Marquer le token comme utilisé
            Token::marquerUtilise($token);
            $succes = "Votre mot de passe a été réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
            $tokenValide = false;
        } else {
            $erreur = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <?php afficherMessage(); ?>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Réinitialiser le mot de passe</h1>
            
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?php echo e($erreur); ?></div>
            <?php endif; ?>
            
            <?php if ($succes): ?>
                <div class="alert alert-success"><?php echo e($succes); ?></div>
                <div class="auth-footer">
                    <p><a href="/login.php" class="btn btn-primary">Se connecter</a></p>
                </div>
            <?php elseif ($tokenValide): ?>
                <p>Choisissez votre nouveau mot de passe.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="mot_de_passe">Nouveau mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" 
                               required minlength="8" placeholder="Minimum 8 caractères">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="confirmation" name="confirmation" class="form-control" 
                               required minlength="8" placeholder="Retapez le mot de passe">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Réinitialiser le mot de passe
                    </button>
                </form>
            <?php else: ?>
                <div class="auth-footer">
                    <p><a href="/mot-de-passe-oublie.php">Demander un nouveau lien</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
