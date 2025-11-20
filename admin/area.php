<?php 
$active = "admin-area";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Espace administrateur - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <?php include_once("../includes/header.php") ?>

        <div class="container my-5">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <h1 class="mb-2">👋 Bonjour <?= $_SESSION['first_name'] ?> !</h1>
                            <p class="lead mb-0">Bienvenue sur votre espace administrateur Fit&Fun</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="card-title mb-3">Ajouter un adhérent</h3>
                            <p class="text-muted mb-4">Inscrivez un nouveau membre à l'association</p>
                            <a href="members/add.php" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-plus-circle me-2"></i>Nouveau membre
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="bi bi-people-fill text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="card-title mb-3">Liste des adhérents</h3>
                            <p class="text-muted mb-4">Consultez et gérez tous les membres</p>
                            <a href="members/list.php" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-list-ul me-2"></i>Voir la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-calendar-event text-warning mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Planning</h5>
                            <p class="text-muted small mb-3">Gérer les cours</p>
                            <a href="planning/manage.php" class="btn btn-outline-warning btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-person-badge mb-2" style="font-size: 2rem; color: #6f42c1;"></i>
                            <h5 class="card-title mb-2">Animateurs</h5>
                            <p class="text-muted small mb-3">Gérer les instructeurs</p>
                            <a href="instructors/list.php" class="btn btn-outline-purple btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-cash-coin text-info mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Cotisations</h5>
                            <p class="text-muted small mb-3">Suivi des paiements</p>
                            <a href="cotisations/list.php" class="btn btn-outline-info btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm hover-lift">
                        <div class="card-body p-3 text-center">
                            <i class="bi bi-gear-fill text-secondary mb-2" style="font-size: 2rem;"></i>
                            <h5 class="card-title mb-2">Paramètres</h5>
                            <p class="text-muted small mb-3">Configuration</p>
                            <a href="#" class="btn btn-outline-secondary btn-sm w-100">Accéder</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12 text-center">
                    <a href="auth/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Se déconnecter
                    </a>
                </div>
            </div>
        </div>

        <style>
            .hover-lift {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            .hover-lift:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
            }

            .card {
                transition: all 0.3s ease;
            }
            
            /* Style pour le bouton violet avec hover */
            .btn-outline-purple {
                color: #6f42c1;
                border-color: #6f42c1;
            }
            
            .btn-outline-purple:hover {
                color: #fff;
                background-color: #6f42c1;
                border-color: #6f42c1;
            }
        </style>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>