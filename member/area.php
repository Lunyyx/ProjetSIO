<?php 
$active = "member-area";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: auth/login.php");
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>Espace adhérent - Fit&Fun</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body class="d-flex flex-column">
        <?php include_once("../includes/header.php") ?>

        <div class="mx-auto w-75 text-center">
            <h1>Bonjour <?= $_SESSION['first_name'] ?> !</h1>

            <h3>Bienvenue sur votre espace adhérent !</h3>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>