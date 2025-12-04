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
    $cotisation_id = $_POST['cotisation_id'];
    $member_id = $_POST['member_id'];
    $amount = floatval($_POST['amount']);
    $payment_date = $_POST['payment_date'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $payment_method = $_POST['payment_method'];

    try {
        $stmt = $conn->prepare("
            UPDATE cotisations 
            SET user_id = ?, amount = ?, payment_date = ?, start_date = ?, end_date = ?, payment_method = ?, status = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$user_id, $amount, $payment_date, $start_date, $end_date, $payment_method, $status, $id]);

        if ($result) {
            header("Location: manage.php?success=updated");
        } else {
            header("Location: manage.php?error=update_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur modification cotisation : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
