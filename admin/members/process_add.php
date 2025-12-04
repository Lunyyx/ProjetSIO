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
    // Récupérer les données du formulaire
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
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: manage.php?error=email_exists");
            exit();
        }
        
        // Insérer le nouveau membre
        $stmt = $conn->prepare("
            INSERT INTO users (first_name, last_name, email, role, address, address_pc, address_city, preferred_activities, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
            $_SESSION['user_id']
        ]);

        if ($result) {
            header("Location: manage.php?success=added");
        } else {
            header("Location: manage.php?error=add_failed");
        }
    } catch(PDOException $e) {
        error_log("Erreur ajout membre : " . $e->getMessage());
        header("Location: manage.php?error=database");
    }
} else {
    header("Location: manage.php");
}
exit();
