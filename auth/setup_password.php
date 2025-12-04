<?php
session_start();
include_once "../config/database.php";

// Vérifier le token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: login.php?error=invalid_token");
    exit();
}

$token = $_GET['token'];
$database = new Database();
$conn = $database->getConnection();

// Vérifier si le token est valide et non expiré
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, email 
    FROM users 
    WHERE password_reset_token = :token 
    AND password_reset_expires > NOW()
    AND password IS NULL
");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: login.php?error=expired_token");
    exit();
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    if (empty($errors)) {
        // Hasher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Mettre à jour le mot de passe et supprimer le token
        $stmt = $conn->prepare("
            UPDATE users 
            SET password = :password,
                password_reset_token = NULL,
                password_reset_expires = NULL,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Votre mot de passe a été défini avec succès. Vous pouvez maintenant vous connecter.";
            header("Location: login.php?success=password_set");
            exit();
        } else {
            $errors[] = "Erreur lors de la définition du mot de passe";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Définir votre mot de passe - Fit&Fun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <style>
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 3px;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="h3 mb-3">Définir votre mot de passe</h2>
                            <p class="text-muted">Bonjour <strong><?= htmlspecialchars($user['first_name']) ?></strong>, bienvenue chez Fit&Fun !</p>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="passwordForm">
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="8">
                                <div class="password-strength" id="passwordStrength"></div>
                                <div class="password-requirements">
                                    <small>
                                        <ul class="mb-0 ps-3">
                                            <li>Au moins 8 caractères</li>
                                            <li>Recommandé : majuscules, minuscules, chiffres et caractères spéciaux</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       minlength="8">
                                <div class="invalid-feedback" id="passwordMismatch">
                                    Les mots de passe ne correspondent pas
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Définir mon mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Vérification de la force du mot de passe
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('passwordStrength');
        const mismatchFeedback = document.getElementById('passwordMismatch');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#198754'];
            const widths = [20, 40, 60, 80, 100];
            
            if (password.length > 0) {
                strengthBar.style.backgroundColor = colors[strength - 1] || colors[0];
                strengthBar.style.width = widths[strength - 1] + '%' || '20%';
            } else {
                strengthBar.style.width = '0';
            }
        });

        // Vérification de la correspondance des mots de passe
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.classList.add('is-invalid');
                mismatchFeedback.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                mismatchFeedback.style.display = 'none';
            }
        });

        // Validation avant soumission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                confirmPasswordInput.classList.add('is-invalid');
                mismatchFeedback.style.display = 'block';
            }
        });
    </script>
</body>
</html>
