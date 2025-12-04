<?php
session_start();
$active = "inscription";

require_once('includes/permissions.php');

if(isset($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role'] ?? null);
}
?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
    <head>
        <title>Inscription - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="assets/css/index.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="assets/css/common.css" rel="stylesheet">
    </head>
    <body class="h-100 d-flex flex-column bg-light">
        <?php include_once("includes/header.php") ?>

        <div class="container my-5 flex-grow-1">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h1 class="mb-3">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i>
                            Demande d'inscription
                        </h1>
                        <p class="lead text-muted">
                            Rejoignez notre communauté sportive et découvrez nos activités !
                        </p>
                    </div>

                    <!-- Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Inscription réussie !</strong><br>
                            Un email vous a été envoyé avec un lien pour définir votre mot de passe. 
                            Veuillez vérifier votre boîte de réception (et vos spams si nécessaire).
                            <?php if (isset($_GET['email_warning'])): ?>
                                <br><small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Note: L'envoi de l'email a rencontré un problème. Contactez-nous si vous ne le recevez pas.
                                </small>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php
                            switch($_GET['error']) {
                                case 'email_exists': echo 'Cet email est déjà enregistré.'; break;
                                case 'database': echo 'Erreur de base de données.'; break;
                                default: echo 'Une erreur est survenue. Veuillez réessayer.';
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <form action="process_inscription.php" method="POST">
                                <h4 class="mb-4">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Informations personnelles
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            Prénom <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="first_name" required 
                                               placeholder="Votre prénom">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            Nom <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="last_name" required 
                                               placeholder="Votre nom">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" name="email" required 
                                           placeholder="votre.email@exemple.fr">
                                    <small class="text-muted">
                                        Nous utiliserons cet email pour vous contacter
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Téléphone
                                    </label>
                                    <input type="tel" class="form-control" name="phone" 
                                           placeholder="06 12 34 56 78">
                                </div>

                                <hr class="my-4">

                                <h4 class="mb-4">
                                    <i class="bi bi-geo-alt-fill me-2"></i>
                                    Adresse
                                </h4>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rue et numéro</label>
                                    <input type="text" class="form-control" name="address" 
                                           placeholder="12 rue de la République">
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Code postal</label>
                                        <input type="text" class="form-control" name="postal_code" 
                                               placeholder="21000">
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-semibold">Ville</label>
                                        <input type="text" class="form-control" name="city" 
                                               placeholder="Dijon">
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h4 class="mb-4">
                                    <i class="bi bi-activity me-2"></i>
                                    Activités souhaitées
                                </h4>

                                <?php
                                include_once "config/database.php";
                                $database = new Database();
                                $conn = $database->getConnection();
                                $stmt = $conn->prepare("SELECT * FROM activities ORDER BY name");
                                $stmt->execute();
                                $activities = $stmt->fetchAll();
                                ?>

                                <div class="row">
                                    <?php foreach($activities as $activity): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="activities[]" value="<?= $activity['id'] ?>" 
                                                       id="activity_<?= $activity['id'] ?>">
                                                <label class="form-check-label" for="activity_<?= $activity['id'] ?>">
                                                    <span class="badge" style="background-color: <?= $activity['color'] ?>">
                                                        <?= htmlspecialchars($activity['name']) ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <hr class="my-4">

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Message (optionnel)</label>
                                    <textarea class="form-control" name="message" rows="4" 
                                              placeholder="Questions ou informations complémentaires..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>À savoir :</strong> Cette demande sera traitée par notre équipe. 
                                    Nous vous contacterons sous 48h pour finaliser votre inscription.
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send-fill me-2"></i>
                                        Envoyer ma demande
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Retour à l'accueil
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-question-circle text-primary me-2"></i>
                                        Besoin d'aide ?
                                    </h5>
                                    <p class="card-text mb-0">
                                        Contactez-nous : <br>
                                        <i class="bi bi-envelope me-1"></i> julie.fort@fitandfun-association.fr<br>
                                        <i class="bi bi-telephone me-1"></i> 06 12 34 56 78
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>
                                        Notre adresse
                                    </h5>
                                    <p class="card-text mb-0">
                                        Association Fit&Fun<br>
                                        12 rue Vaillant<br>
                                        21000 Dijon
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
