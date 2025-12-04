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
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $specialties = !empty($_POST['specialties']) ? trim($_POST['specialties']) : null;

    try {
        // Vérifier si l'email existe déjà (si fourni)
        if ($email) {
            $stmt = $conn->prepare("SELECT id FROM instructors WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                header("Location: manage.php?error=email_exists");
                exit();
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO instructors (first_name, last_name, email, phone, specialties, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$first_name, $last_name, $email, $phone, $specialties]);

        if ($result) {
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
