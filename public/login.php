<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Utilisateur.php';

// Si déjà connecté, rediriger
if (estConnecte()) {
    rediriger('/');
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    
    if (empty($email) || empty($motDePasse)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $utilisateurModel = new Utilisateur();
        
        if ($utilisateurModel->connexion($email, $motDePasse)) {
            setMessage('Connexion réussie ! Bienvenue.', 'success');
            rediriger('/');
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Connexion</h1>
            
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?php echo e($erreur); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
            
            <div class="auth-footer">
                <p>Pas encore de compte ? <a href="/inscription.php">Inscrivez-vous</a></p>
            </div>
            
            <div class="demo-info">
                <h3>Comptes de démonstration :</h3>
                <ul>
                    <li><strong>Bureau :</strong> admin@fitandfun.fr / password</li>
                    <li><strong>Animateur :</strong> julie.fort@fitandfun-association.fr / password</li>
                    <li><strong>Adhérent :</strong> bertille.dupont@gmail.com / password</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
