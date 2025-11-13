<?php 
$active = "member-area";

session_start();

include_once "../../config/database.php";

$database = new Database();
$conn = $database->getConnection();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $member = $stmt->fetch();

        if ($member) {
            if (password_verify($password, $member['password'])) {
                $_SESSION['user_id'] = $member['id'];
                $_SESSION['email'] = $member['email'];
                $_SESSION['first_name'] = $member['first_name'];
                $_SESSION['last_name'] = $member['last_name'];
                                
                // Redirection avec exit obligatoire
                header("Location: ../area.php");
                exit();
            } else {
                $error = "Le mot de passe est incorrect !";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
        }
    } catch(PDOException $e) {
        error_log("Erreur login : " . $e->getMessage());
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Connexion - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body>
        <?php include_once("../../includes/header.php") ?>

        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4 text-primary fw-bold">Connexion</h2>
                            <p class="text-center text-muted mb-4">Connectez-vous à votre espace adhérent Fit&Fun</p>

                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required minlength="8">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">Se connecter</button>
                                </div>

                                <div class="text-center mt-3">
                                    <p class="text-muted">Vous n'avez pas de compte ? <a href="register.php" class="text-primary fw-semibold text-decoration-none">S'inscrire</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>