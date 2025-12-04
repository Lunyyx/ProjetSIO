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
    $user_id = $_POST['user_id'];
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];

    try {
        $stmt = $conn->prepare("
            INSERT INTO cotisations (user_id, amount, payment_date, start_date, end_date, payment_method, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([$user_id, $amount, $payment_date, $start_date, $end_date, $payment_method]);

        if ($result) {
            header("Location: manage.php?success=added");
        } else {
            header("Location: manage.php?error=add_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur ajout cotisation : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
