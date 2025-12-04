<?php
session_start();
include_once "../../config/database.php";
include_once "../../includes/mailer.php";
require_once "../../includes/permissions.php";

if(empty($_SESSION['user_id']) || !isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    try {
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: manage.php?error=email_exists");
            exit();
        }
        
        // Générer un token de définition de mot de passe
        $token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $conn->prepare("
            INSERT INTO users (
                first_name, last_name, email, phone, role,
                password_reset_token, password_reset_expires,
                created_by, created_at
            ) 
            VALUES (?, ?, ?, ?, 'membre_bureau', ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $first_name, 
            $last_name, 
            $email, 
            $phone,
            $token,
            $token_expiry,
            $_SESSION['user_id']
        ]);

        if ($result) {
            // Envoyer l'email de définition de mot de passe
            $mailer = new Mailer();
            $mailer->sendPasswordSetupEmail($email, $first_name, $token);
            
            header("Location: manage.php?success=added");
        } else {
            header("Location: manage.php?error=add_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur ajout instructeur : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
