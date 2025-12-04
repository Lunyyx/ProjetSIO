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
    $member_id = $_POST['member_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'adherent';
    $address = trim($_POST['address']);
    $postal_code = trim($_POST['postal_code']);
    $city = trim($_POST['city']);
    
    // Gérer les activités sélectionnées
    $preferred_activities = null;
    if (!empty($_POST['activities'])) {
        $selected_activities = [];
        foreach ($_POST['activities'] as $activity_id) {
            $stmt_activity = $conn->prepare("SELECT name FROM activities WHERE id = ?");
            $stmt_activity->execute([$activity_id]);
            $activity = $stmt_activity->fetch();
            if ($activity) {
                $selected_activities[] = $activity['name'];
            }
        }
        $preferred_activities = implode(', ', $selected_activities);
    }

    try {
        // Vérifier si l'email existe déjà (sauf pour ce membre)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $member_id]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: manage.php?error=email_exists");
            exit();
        }
        
        // Mettre à jour le membre
        $stmt = $conn->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?, role = ?, address = ?, address_pc = ?, address_city = ?, preferred_activities = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $first_name, 
            $last_name, 
            $email, 
            $role, 
            $address, 
            $postal_code, 
            $city, 
            $preferred_activities,
            $member_id
        ]);

        if ($result) {
            header("Location: manage.php?success=updated");
        } else {
            header("Location: manage.php?error=update_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur modification membre : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
