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

// Récupérer l'ID du membre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$member_id = $_GET['id'];

// Récupérer les données du membre
try {
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        header("Location: list.php?error=not_found");
        exit();
    }
} catch(PDOException $e) {
    error_log("Erreur récupération membre : " . $e->getMessage());
    header("Location: list.php?error=db_error");
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $postal_code = trim($_POST['postal_code']);
    $city = trim($_POST['city']);

    try {
        // Vérifier si l'email existe déjà (sauf pour ce membre)
        $stmt = $conn->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $stmt->execute([$email, $member_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé par un autre adhérent.";
        } else {
            $stmt = $conn->prepare("UPDATE members SET first_name = ?, last_name = ?, email = ?, address = ?, address_pc = ?, address_city = ? WHERE id = ?");
            
            $result = $stmt->execute([$first_name, $last_name, $email, $address, $postal_code, $city, $member_id]);

            if ($result) {
                $success = "Adhérent modifié avec succès !";
                // Recharger les données
                $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
                $stmt->execute([$member_id]);
                $member = $stmt->fetch();
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
        <title>Modifier un adhérent - Fit&Fun</title>
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
                            <h2 class="text-center mb-4 text-primary fw-bold">
                                <i class="bi bi-pencil-square me-2"></i>Modifier un adhérent
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
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($member['first_name']) ?>" placeholder="Prénom" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($member['last_name']) ?>" placeholder="Nom" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" placeholder="exemple@email.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($member['address']) ?>" placeholder="Numéro et rue" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="postal_code" class="form-label">Code postal <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= htmlspecialchars($member['address_pc']) ?>" placeholder="75000" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="city" class="form-label">Ville <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($member['address_city']) ?>" placeholder="Ville" required>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">
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
