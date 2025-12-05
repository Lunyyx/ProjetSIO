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
    $tokenData = Token::verifier($token, 'password_set');
    if ($tokenData) {
        $tokenValide = true;
    } else {
        $erreur = "Ce lien est invalide ou a expiré. Veuillez contacter l'administration.";
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
            $succes = "Votre mot de passe a été défini avec succès ! Vous pouvez maintenant vous connecter.";
            $tokenValide = false; // Empêcher de réutiliser le formulaire
        } else {
            $erreur = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<main class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>🔐 Définir votre mot de passe</h1>
            
            <?php if ($succes): ?>
                <div class="alert alert-success">
                    <?php echo $succes; ?>
                </div>
                <div class="form-actions" style="text-align: center; margin-top: 20px;">
                    <a href="/login.php" class="btn btn-primary">Se connecter</a>
                </div>
                
            <?php elseif ($erreur && !$tokenValide): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
                <div class="form-actions" style="text-align: center; margin-top: 20px;">
                    <a href="/" class="btn btn-secondary">Retour à l'accueil</a>
                </div>
                
            <?php elseif ($tokenValide): ?>
                <?php if ($erreur): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($erreur); ?>
                    </div>
                <?php endif; ?>
                
                <p>Bienvenue ! Veuillez choisir un mot de passe pour votre compte.</p>
                
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="mot_de_passe">Nouveau mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" 
                               class="form-control" required minlength="8"
                               placeholder="Minimum 8 caractères">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="confirmation" name="confirmation" 
                               class="form-control" required minlength="8"
                               placeholder="Confirmez votre mot de passe">
                    </div>
                    
                    <div class="password-requirements">
                        <p><strong>Votre mot de passe doit contenir :</strong></p>
                        <ul>
                            <li>Au moins 8 caractères</li>
                            <li>Idéalement des lettres, chiffres et caractères spéciaux</li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">
                            Définir mon mot de passe
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.password-requirements {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin: 15px 0;
    font-size: 0.9rem;
}
.password-requirements ul {
    margin: 10px 0 0 20px;
    padding: 0;
}
.password-requirements li {
    margin: 5px 0;
    color: #666;
}
</style>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
