<?php 
include_once "../../config/database.php";
require_once "../../includes/permissions.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Vérifier que l'utilisateur est membre du bureau
if (!isMemberBureau()) {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $conn->prepare("
            INSERT INTO schedule (activity_id, user_id, day_of_week, start_time, end_time, location, max_participants, is_recurring, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['activity_id'],
            $_POST['user_id'],
            $_POST['day_of_week'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['location'] ?? null,
            $_POST['max_participants'],
            $_POST['is_recurring'] ?? 1
        ]);
        
        header("Location: manage.php?success=added");
        exit();
        
    } catch(PDOException $e) {
        error_log("Erreur ajout planning : " . $e->getMessage());
        header("Location: manage.php?error=add_failed");
        exit();
    }
}
