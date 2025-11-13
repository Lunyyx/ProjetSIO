<?php 
session_start();

if(empty($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include_once "../../config/database.php";

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $conn->prepare("
            INSERT INTO schedule (activity_id, instructor_id, day_of_week, start_time, end_time, location, max_participants, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['activity_id'],
            $_POST['instructor_id'],
            $_POST['day_of_week'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['location'] ?? null,
            $_POST['max_participants']
        ]);
        
        header("Location: manage.php?success=added");
        exit();
        
    } catch(PDOException $e) {
        error_log("Erreur ajout planning : " . $e->getMessage());
        header("Location: manage.php?error=add_failed");
        exit();
    }
}
