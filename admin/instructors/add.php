<?php
$active = "admin-area";

include_once "../../config/database.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../area.php");
}

$database = new Database();
$conn = $database->getConnection();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $specialties = !empty($_POST['specialties']) ? trim($_POST['specialties']) : null;
    

    try {
        // Vérifier si l'email existe déjà (seulement si un email est fourni)
        if ($email) {
            $stmt = $conn->prepare("SELECT id FROM instructors WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $stmt = $conn->prepare("INSERT INTO instructors (first_name, last_name, email, phone, specialties, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                
                $result = $stmt->execute([$first_name, $last_name, $email, $phone, $specialties]);

                if ($result) {
                    $success = "Animateur ajouté avec succès !";
                } else {
                    $error = "Erreur lors de l'ajout de l'animateur. Veuillez réessayer.";
                }
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO instructors (first_name, last_name, email, phone, specialties, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            
            $result = $stmt->execute([$first_name, $last_name, $email, $phone, $specialties]);

            if ($result) {
                $success = "Animateur ajouté avec succès !";
            } else {
                $error = "Erreur lors de l'ajout de l'animateur. Veuillez réessayer.";
            }
        }
    } catch(PDOException $e) {
        error_log("Erreur ajout : " . $e->getMessage());
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Ajouter un animateur - Fit&Fun</title>
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
                            <h2 class="text-center mb-4 fw-bold" style="color: #6f42c1;">
                                <i class="bi bi-person-badge me-2"></i>Ajouter un animateur
                            </h2>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    ?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    </i><?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Prénom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Nom" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com">
                                    <small class="text-muted">Optionnel</small>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="+33 6 12 34 56 78">
                                    <small class="text-muted">Optionnel</small>
                                </div>

                                <div class="mb-3">
                                    <label for="specialties" class="form-label">Spécialités</label>
                                    <input type="text" class="form-control" id="specialties" name="specialties" placeholder="Yoga, Pilates, Fitness...">
                                    <small class="text-muted">Ex: Yoga, Pilates (séparées par des virgules)</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-lg fw-semibold" style="background-color: #6f42c1; color: white; border-color: #6f42c1;">
                                        <i class="bi bi-check-circle me-2"></i>Ajouter l'animateur
                                    </button>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>