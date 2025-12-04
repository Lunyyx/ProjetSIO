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
        // Suppression logique (marquer comme inactif) plutôt que suppression physique
        $stmt = $conn->prepare("UPDATE schedule SET is_active = 0 WHERE id = ?");
        $stmt->execute([$_POST['schedule_id']]);
        
        header("Location: manage.php?success=deleted");
        exit();
        
    } catch(PDOException $e) {
        error_log("Erreur suppression planning : " . $e->getMessage());
        header("Location: manage.php?error=delete_failed");
        exit();
    }
}
