<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/permissions.php';

// Vérifier que l'utilisateur est connecté et a le rôle animateur
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'animateur' && $_SESSION['role'] !== 'membre_bureau')) {
    header("Location: ../auth/login.php");
    exit();
}

$active = 'animateur';

$database = new Database();
$conn = $database->getConnection();

// Récupérer les informations de l'animateur
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch();

// Traiter la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']) ?: null;
    $specialties = trim($_POST['specialties']) ?: null;
    
    try {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé par un autre compte.";
        } else {
            // Mettre à jour les informations
            $stmt = $conn->prepare("
                UPDATE users 
                SET first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    specialties = :specialties,
                    updated_at = NOW()
                WHERE id = :user_id
            ");
            
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':specialties', $specialties);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Mettre à jour la session
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;
                
                // Recharger les données
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $user = $stmt->fetch();
                
                $success = "Profil mis à jour avec succès !";
            } else {
                $error = "Erreur lors de la mise à jour du profil.";
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur mise à jour profil: " . $e->getMessage());
        $error = "Erreur lors de la mise à jour du profil.";
    }
}

// Traiter le changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_password = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 8) {
        $error_password = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error_password = "Le mot de passe actuel est incorrect.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :user_id");
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success_password = "Mot de passe modifié avec succès !";
        } else {
            $error_password = "Erreur lors du changement de mot de passe.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Fit&Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/common.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="bi bi-person-circle text-secondary me-2"></i>
                            Mon Profil
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Gérez vos informations personnelles
                        </p>
                    </div>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informations personnelles -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Informations personnelles
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="specialties" class="form-label">Spécialités</label>
                                <textarea class="form-control" id="specialties" name="specialties" rows="3"><?= htmlspecialchars($user['specialties'] ?? '') ?></textarea>
                                <small class="text-muted">Ex: Yoga, Pilates, Renforcement musculaire...</small>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Enregistrer les modifications
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Changer le mot de passe -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning border-0">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Changer le mot de passe
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_password)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($success_password) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_password)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error_password) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel *</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe *</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" minlength="8" required>
                                <small class="text-muted">Minimum 8 caractères</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" minlength="8" required>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Changer le mot de passe
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informations du compte
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Rôle :</strong> 
                            <span class="badge bg-warning">Animateur</span>
                        </p>
                        <p class="mb-2">
                            <strong>Membre depuis :</strong>
                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </p>
                        <?php if ($user['updated_at']): ?>
                            <p class="mb-0">
                                <strong>Dernière modification :</strong>
                                <?= date('d/m/Y à H:i', strtotime($user['updated_at'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
