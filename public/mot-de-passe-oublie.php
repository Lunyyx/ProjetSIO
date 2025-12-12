<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Utilisateur.php';
require_once __DIR__ . '/../src/models/Token.php';
require_once __DIR__ . '/../src/services/MailService.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    rediriger('/');
}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $erreur = 'Veuillez entrer votre adresse email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } else {
        // Vérifier si l'utilisateur existe
        $utilisateur = Utilisateur::getParEmail($email);
        
        if ($utilisateur) {
            // Générer un token de réinitialisation
            $token = Token::generer($utilisateur['id'], 'password_reset');
            
            // Envoyer l'email
            $mailService = new MailService();
            $lien = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/reinitialiser-mot-de-passe.php?token=' . $token;
            
            $sujet = "Réinitialisation de votre mot de passe - " . APP_NAME;
            $contenu = "
                <h2>Réinitialisation de mot de passe</h2>
                <p>Bonjour,</p>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$lien}' style='background: #ff7a59; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;'>
                        Réinitialiser mon mot de passe
                    </a>
                </p>
                <p><small>Ce lien expire dans 24 heures.</small></p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
            ";
            
            if ($mailService->envoyer($email, $sujet, $contenu)) {
                $succes = 'Un email de réinitialisation a été envoyé à votre adresse.';
            } else {
                $erreur = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.';
            }
        } else {
            // Ne pas révéler si l'email existe ou non (sécurité)
            $succes = 'Si cette adresse existe dans notre système, vous recevrez un email de réinitialisation.';
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <?php afficherMessage(); ?>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Mot de passe oublié</h1>
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation.</p>
            
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?php echo e($erreur); ?></div>
            <?php endif; ?>
            
            <?php if ($succes): ?>
                <div class="alert alert-success"><?php echo e($succes); ?></div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo e($_POST['email'] ?? ''); ?>"
                               placeholder="votre@email.com">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Envoyer le lien de réinitialisation
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p><a href="/login.php">← Retour à la connexion</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
