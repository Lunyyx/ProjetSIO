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

// Récupérer l'ID de la cotisation
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$cotisation_id = $_GET['id'];

// Récupérer les données de la cotisation
try {
    $stmt = $conn->prepare("SELECT * FROM cotisations WHERE id = ?");
    $stmt->execute([$cotisation_id]);
    $cotisation = $stmt->fetch();
    
    if (!$cotisation) {
        header("Location: list.php?error=not_found");
        exit();
    }
} catch(PDOException $e) {
    error_log("Erreur récupération cotisation : " . $e->getMessage());
    header("Location: list.php?error=db_error");
    exit();
}

// Récupérer la liste des membres pour le formulaire
$stmt = $conn->prepare("SELECT id, first_name, last_name FROM members ORDER BY last_name, first_name");
$stmt->execute();
$members = $stmt->fetchAll();

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $member_id = $_POST['member_id'];
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE cotisations SET member_id = ?, amount = ?, payment_date = ?, start_date = ?, end_date = ?, payment_method = ?, status = ? WHERE id = ?");
        
        $result = $stmt->execute([$member_id, $amount, $payment_date, $start_date, $end_date, $payment_method, $status, $cotisation_id]);

        if ($result) {
            $success = "Cotisation modifiée avec succès !";
            // Recharger les données
            $stmt = $conn->prepare("SELECT * FROM cotisations WHERE id = ?");
            $stmt->execute([$cotisation_id]);
            $cotisation = $stmt->fetch();
        } else {
            $error = "Erreur lors de la modification. Veuillez réessayer.";
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
        <title>Modifier une cotisation - Fit&Fun</title>
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
                                <i class="bi bi-pencil-square me-2"></i>Modifier une cotisation
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
                                            <option value="<?= $member['id'] ?>" <?= $member['id'] == $cotisation['member_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($member['last_name'] . ' ' . $member['first_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="amount" class="form-label">Montant (€) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="amount" name="amount" value="<?= htmlspecialchars($cotisation['amount']) ?>" step="0.01" min="0" required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Date de paiement <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= htmlspecialchars($cotisation['payment_date']) ?>" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Début de période <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($cotisation['start_date']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">Fin de période <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($cotisation['end_date']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Sélectionnez un mode</option>
                                        <option value="Espèces" <?= $cotisation['payment_method'] == 'Espèces' ? 'selected' : '' ?>>Espèces</option>
                                        <option value="Carte bancaire" <?= $cotisation['payment_method'] == 'Carte bancaire' ? 'selected' : '' ?>>Carte bancaire</option>
                                        <option value="Chèque" <?= $cotisation['payment_method'] == 'Chèque' ? 'selected' : '' ?>>Chèque</option>
                                        <option value="Virement" <?= $cotisation['payment_method'] == 'Virement' ? 'selected' : '' ?>>Virement</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Sélectionnez un statut</option>
                                        <option value="Payée" <?= $cotisation['status'] == 'Payée' ? 'selected' : '' ?>>Payée</option>
                                        <option value="En attente" <?= $cotisation['status'] == 'En attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="Annulée" <?= $cotisation['status'] == 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-info btn-lg fw-semibold text-white">
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
