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
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $postal_code = trim($_POST['postal_code']);
    $city = trim($_POST['city']);
    

    try {
        $stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $stmt = $conn->prepare("INSERT INTO members (first_name, last_name, email, address, address_pc, address_city, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $result = $stmt->execute([$first_name, $last_name, $email, $address, $postal_code, $city, $_SESSION['user_id']]);

            if ($result) {
                $success = "Adhérent ajouté avec succès !";
            } else {
                $error = "Erreur lors de l'ajout de l'adhérent. Veuillez réessayer.";
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
        <title>Ajouter un adhérent - Fit&Fun</title>
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
                            <h2 class="text-center mb-4 text-primary fw-bold">Ajouter un adhérent</h2>
                            
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
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="exemple@email.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Numéro et rue" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="postal_code" class="form-label">Code postal <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="75000" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="city" class="form-label">Ville <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city" placeholder="Ville" required>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold">Ajouter</button>
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