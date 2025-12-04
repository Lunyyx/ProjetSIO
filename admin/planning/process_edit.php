<?php 
include_once "../../config/database.php";
require_once "../../includes/permissions.php";

session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
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
            UPDATE schedule 
            SET activity_id = ?, 
                instructor_id = ?, 
                day_of_week = ?, 
                start_time = ?, 
                end_time = ?, 
                location = ?, 
                max_participants = ?,
                is_recurring = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['activity_id'],
            $_POST['instructor_id'],
            $_POST['day_of_week'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['location'] ?? null,
            $_POST['max_participants'],
            $_POST['is_recurring'] ?? 1,
            $_POST['schedule_id']
        ]);
        
        header("Location: manage.php?success=updated");
        exit();
        
    } catch(PDOException $e) {
        error_log("Erreur modification planning : " . $e->getMessage());
        header("Location: manage.php?error=update_failed");
        exit();
    }
}
