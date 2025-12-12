<?php
/**
 * Page de configuration initiale du site Fit&Fun
 * Cette page permet de créer le premier compte administrateur (bureau)
 * Elle se désactive automatiquement après l'installation
 */

require_once __DIR__ . '/../config/database.php';

// Fichier de verrouillage pour désactiver le setup
$lockFile = __DIR__ . '/../.setup_complete';

// Si le setup est déjà terminé, rediriger vers l'accueil
if (file_exists($lockFile)) {
    header('Location: /');
    exit();
}

// Vérifier si un compte bureau existe déjà
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE role = 'bureau'");
$stmt->execute();
$hasBureau = $stmt->fetchColumn() > 0;

// Si un compte bureau existe, créer le fichier de verrouillage et rediriger
if ($hasBureau) {
    file_put_contents($lockFile, date('Y-m-d H:i:s') . ' - Setup completed');
    header('Location: /');
    exit();
}

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';
    
    // Validation
    if (empty($nom)) {
        $erreurs[] = 'Le nom est requis.';
    }
    if (empty($prenom)) {
        $erreurs[] = 'Le prénom est requis.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse email invalide.';
    }
    if (strlen($mot_de_passe) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($mot_de_passe !== $mot_de_passe_confirm) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }
    
    if (empty($erreurs)) {
        try {
            $db->beginTransaction();
            
            // Créer l'utilisateur avec le rôle bureau
            $hashedPassword = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO utilisateurs (email, mot_de_passe, role) VALUES (?, ?, 'bureau')");
            $stmt->execute([$email, $hashedPassword]);
            
            $db->commit();
            
            // Créer le fichier de verrouillage
            file_put_contents($lockFile, date('Y-m-d H:i:s') . ' - Setup completed by ' . $email);
            
            $succes = true;
            
        } catch (Exception $e) {
            $db->rollBack();
            $erreurs[] = 'Erreur lors de la création du compte : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Fit&Fun</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f3d62 0%, #0c253d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .setup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .setup-header {
            background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%);
            padding: 2.5rem;
            text-align: center;
            color: white;
        }
        
        .setup-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .setup-header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .setup-body {
            padding: 2.5rem;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
        }
        
        .step.active {
            background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%);
            color: white;
        }
        
        .step.completed {
            background: #0fbf9f;
            color: white;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ff7a59;
            box-shadow: 0 0 0 4px rgba(255, 122, 89, 0.15);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff7a59 0%, #ff5a36 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 122, 89, 0.35);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #0fbf9f 0%, #0a8f77 100%);
            color: white;
            width: 100%;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 1.25rem;
        }
        
        .success-icon {
            font-size: 4rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .success-message {
            text-align: center;
        }
        
        .success-message h2 {
            color: #0fbf9f;
            margin-bottom: 1rem;
        }
        
        .success-message p {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #0369a1;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🏃 Fit&Fun</h1>
            <p>Configuration initiale</p>
        </div>
        
        <div class="setup-body">
            <?php if ($succes): ?>
                <div class="success-message">
                    <div class="success-icon">✅</div>
                    <h2>Installation terminée !</h2>
                    <p>Votre compte administrateur a été créé avec succès. Vous pouvez maintenant vous connecter et commencer à gérer votre association.</p>
                    <a href="/login.php" class="btn btn-success">Accéder à la connexion</a>
                </div>
            <?php else: ?>
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                </div>
                
                <div class="info-box">
                    <strong>ℹ️ Création du compte administrateur</strong>
                    Ce compte aura accès à l'ensemble des fonctionnalités d'administration du site (gestion des adhérents, animateurs, activités, etc.)
                </div>
                
                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?php echo htmlspecialchars($erreur); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="form-control" required 
                                   value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" class="form-control" required 
                                   value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required 
                               minlength="8" placeholder="Minimum 8 caractères">
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_de_passe_confirm">Confirmer le mot de passe</label>
                        <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        Créer le compte administrateur
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
