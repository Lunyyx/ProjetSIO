<?php 
session_start();

$active = "member-area";

include_once "../../config/database.php";

if(isset($_SESSION['user_id'])) {
    header("Location: ../area.php");
}

$database = new Database();
$conn = $database->getConnection();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['first_name'] = $admin['first_name'];
                $_SESSION['last_name'] = $admin['last_name'];
                $_SESSION['role'] = 'membre_bureau'; // Les admins sont tous membres du bureau
                
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
<html lang="fr">
    <head>
        <title>Connexion - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="../../assets/css/common.css" rel="stylesheet">
    </head>
    <body>
        <?php include_once("../../includes/header.php") ?>

        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4 text-primary fw-bold">Connexion</h2>
                            <p class="text-center text-muted mb-4">Connectez-vous à votre espace administrateur Fit&Fun</p>

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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>