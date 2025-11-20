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

// Récupérer la liste des membres pour le formulaire
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM members ORDER BY last_name, first_name");
$stmt->execute();
$members = $stmt->fetchAll();

// Traitement du formulaire d'ajout
if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $member_id = $_POST['member_id'];
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("INSERT INTO cotisations (member_id, amount, payment_date, start_date, end_date, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([$member_id, $amount, $payment_date, $start_date, $end_date, $payment_method, $status]);

        if ($result) {
            $success = "Cotisation ajoutée avec succès !";
        } else {
            $error = "Erreur lors de l'ajout. Veuillez réessayer.";
        }
    } catch(PDOException $e) {
        error_log("Erreur ajout cotisation : " . $e->getMessage());
        $error = "Une erreur est survenue : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Ajouter une cotisation - Fit&Fun</title>
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
                            <h2 class="text-center mb-4 text-info fw-bold">
                                <i class="bi bi-cash-coin me-2"></i>Ajouter une cotisation
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
                                <div class="mb-3">
                                    <label for="member_id" class="form-label">Adhérent <span class="text-danger">*</span></label>
                                    <select class="form-select" id="member_id" name="member_id" required>
                                        <option value="">Sélectionnez un adhérent</option>
                                        <?php foreach($members as $member): ?>
                                            <option value="<?= $member['id'] ?>">
                                                <?= htmlspecialchars($member['last_name'] . ' ' . $member['first_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="amount" class="form-label">Montant (€) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" placeholder="50.00" step="0.01" min="0" required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Début de période <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">Fin de période <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Sélectionnez un mode</option>
                                        <option value="Espèces">Espèces</option>
                                        <option value="Carte bancaire">Carte bancaire</option>
                                        <option value="Chèque">Chèque</option>
                                        <option value="Virement">Virement</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Sélectionnez un statut</option>
                                        <option value="Payée" selected>Payée</option>
                                        <option value="En attente">En attente</option>
                                        <option value="Annulée">Annulée</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-info btn-lg fw-semibold text-white">
                                        <i class="bi bi-check-circle me-2"></i>Ajouter la cotisation
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

        <script>
            // Auto-calculer la fin de période (1 an après le début)
            document.getElementById('start_date').addEventListener('change', function() {
                const startDate = new Date(this.value);
                const endDate = new Date(startDate);
                endDate.setFullYear(endDate.getFullYear() + 1);
                endDate.setDate(endDate.getDate() - 1); // Un jour avant pour éviter le chevauchement
                
                const endDateInput = document.getElementById('end_date');
                endDateInput.value = endDate.toISOString().split('T')[0];
            });
        </script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
