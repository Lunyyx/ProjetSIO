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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cotisation_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM cotisations WHERE id = ?");
        $stmt->execute([$_POST['cotisation_id']]);
        
        header("Location: manage.php?success=deleted");
        exit();
    } catch(PDOException $e) {
        error_log("Erreur suppression cotisation : " . $e->getMessage());
        header("Location: manage.php?error=delete_failed");
        exit();
    }
} else {
    header("Location: manage.php");
    exit();
}
