<?php
require_once __DIR__ . '/../config/config.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $activite = trim($_POST['activite'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($nom) || empty($prenom) || empty($email)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'adresse email n\'est pas valide.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO demandes_inscription (nom, prenom, email, telephone, activite_souhaitee, message) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$nom, $prenom, $email, $telephone, $activite, $message])) {
                $succes = 'Votre demande d\'inscription a été envoyée avec succès ! Nous vous contacterons bientôt.';
                // Réinitialiser le formulaire
                $_POST = [];
            } else {
                $erreur = 'Une erreur est survenue lors de l\'envoi de votre demande.';
            }
        } catch (PDOException $e) {
            $erreur = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Demande d'inscription</h1>
            <p>Remplissez le formulaire ci-dessous pour rejoindre notre association.</p>
            
            <?php if ($erreur): ?>
                <div class="alert alert-danger"><?php echo e($erreur); ?></div>
            <?php endif; ?>
            
            <?php if ($succes): ?>
                <div class="alert alert-success"><?php echo e($succes); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/inscription.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" required 
                               value="<?php echo e($_POST['nom'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" required 
                               value="<?php echo e($_POST['prenom'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo e($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" class="form-control" 
                           value="<?php echo e($_POST['telephone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="activite">Activité souhaitée</label>
                    <select id="activite" name="activite" class="form-control">
                        <option value="">-- Choisir une activité --</option>
                        <option value="Fitness" <?php echo ($_POST['activite'] ?? '') === 'Fitness' ? 'selected' : ''; ?>>Fitness</option>
                        <option value="Zumba" <?php echo ($_POST['activite'] ?? '') === 'Zumba' ? 'selected' : ''; ?>>Zumba</option>
                        <option value="Yoga" <?php echo ($_POST['activite'] ?? '') === 'Yoga' ? 'selected' : ''; ?>>Yoga</option>
                        <option value="Renforcement musculaire" <?php echo ($_POST['activite'] ?? '') === 'Renforcement musculaire' ? 'selected' : ''; ?>>Renforcement musculaire</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Message (optionnel)</label>
                    <textarea id="message" name="message" class="form-control" rows="4"><?php echo e($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Envoyer ma demande</button>
            </form>
            
            <div class="auth-footer">
                <p>Déjà inscrit ? <a href="/login.php">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
