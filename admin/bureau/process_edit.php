<?php
session_start();
include_once "../../config/database.php";
require_once "../../includes/permissions.php";

if(empty($_SESSION['user_id']) || !isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bureau_id = $_POST['bureau_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    try {
        // Vérifier si l'email existe déjà (sauf pour cet instructeur)
        if ($email) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $instructor_id]);
            
            if ($stmt->rowCount() > 0) {
                header("Location: manage.php?error=email_exists");
                exit();
            }
        }
        
        $stmt = $conn->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, phone = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$first_name, $last_name, $email, $phone, $bureau_id]);

        if ($result) {
            header("Location: manage.php?success=updated");
        } else {
            header("Location: manage.php?error=update_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur modification instructeur : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
