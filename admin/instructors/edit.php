<?php
$active = "admin-area";

include_once "../../config/database.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../area.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$error = "";
$success = "";

// Récupérer l'ID de l'instructeur
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$instructor_id = $_GET['id'];

// Récupérer les données de l'instructeur
try {
    $stmt = $conn->prepare("SELECT * FROM instructors WHERE id = ?");
    $stmt->execute([$instructor_id]);
    $instructor = $stmt->fetch();
    
    if (!$instructor) {
        header("Location: list.php?error=not_found");
        exit();
    }
} catch(PDOException $e) {
    error_log("Erreur récupération instructeur : " . $e->getMessage());
    header("Location: list.php?error=db_error");
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $specialties = !empty($_POST['specialties']) ? trim($_POST['specialties']) : null;

    try {
        // Vérifier si l'email existe déjà (sauf pour cet instructeur)
        if ($email) {
            $stmt = $conn->prepare("SELECT id FROM instructors WHERE email = ? AND id != ?");
            $stmt->execute([$email, $instructor_id]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Cet email est déjà utilisé par un autre animateur.";
            } else {
                $stmt = $conn->prepare("UPDATE instructors SET first_name = ?, last_name = ?, email = ?, phone = ?, specialties = ? WHERE id = ?");
                
                $result = $stmt->execute([$first_name, $last_name, $email, $phone, $specialties, $instructor_id]);

                if ($result) {
                    $success = "Animateur modifié avec succès !";
                    // Recharger les données
                    $stmt = $conn->prepare("SELECT * FROM instructors WHERE id = ?");
                    $stmt->execute([$instructor_id]);
                    $instructor = $stmt->fetch();
                } else {
                    $error = "Erreur lors de la modification. Veuillez réessayer.";
                }
            }
        } else {
            $stmt = $conn->prepare("UPDATE instructors SET first_name = ?, last_name = ?, email = ?, phone = ?, specialties = ? WHERE id = ?");
            
            $result = $stmt->execute([$first_name, $last_name, $email, $phone, $specialties, $instructor_id]);

            if ($result) {
                $success = "Animateur modifié avec succès !";
                // Recharger les données
                $stmt = $conn->prepare("SELECT * FROM instructors WHERE id = ?");
                $stmt->execute([$instructor_id]);
                $instructor = $stmt->fetch();
            } else {
                $error = "Erreur lors de la modification. Veuillez réessayer.";
            }
        }
    } catch(PDOException $e) {
        error_log("Erreur modification : " . $e->getMessage());
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Modifier un animateur - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <?php include_once("../../includes/header.php") ?>

        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4 fw-bold" style="color: #6f42c1;">
                                <i class="bi bi-pencil-square me-2"></i>Modifier un animateur
                            </h2>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($instructor['first_name']) ?>" placeholder="Prénom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($instructor['last_name']) ?>" placeholder="Nom" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($instructor['email'] ?? '') ?>" placeholder="exemple@email.com">
                                    <small class="text-muted">Optionnel</small>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($instructor['phone'] ?? '') ?>" placeholder="+33 6 12 34 56 78">
                                    <small class="text-muted">Optionnel</small>
                                </div>

                                <div class="mb-3">
                                    <label for="specialties" class="form-label">Spécialités</label>
                                    <input type="text" class="form-control" id="specialties" name="specialties" value="<?= htmlspecialchars($instructor['specialties'] ?? '') ?>" placeholder="Yoga, Pilates, Fitness...">
                                    <small class="text-muted">Ex: Yoga, Pilates (séparées par des virgules)</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-lg fw-semibold" style="background-color: #6f42c1; color: white; border-color: #6f42c1;">
                                        <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
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
