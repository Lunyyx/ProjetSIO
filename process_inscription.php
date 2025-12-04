<?php
session_start();

include_once "config/database.php";
include_once "includes/mailer.php";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Récupérer les données du formulaire
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $activities = $_POST['activities'] ?? [];
    $message = trim($_POST['message'] ?? '');
    
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header("Location: inscription.php?error=email_exists");
        exit();
    }
    
    // Récupérer les noms des activités sélectionnées
    $preferred_activities = '';
    if (!empty($activities)) {
        $placeholders = str_repeat('?,', count($activities) - 1) . '?';
        $stmt = $conn->prepare("SELECT name FROM activities WHERE id IN ($placeholders)");
        $stmt->execute($activities);
        $activity_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $preferred_activities = implode(', ', $activity_names);
    }
    
    // Générer un token de définition de mot de passe
    $token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insérer le visiteur dans la base
    $stmt = $conn->prepare("
        INSERT INTO users (
            first_name, last_name, email, phone, 
            address, address_pc, address_city, 
            preferred_activities, role, 
            password_reset_token, password_reset_expires,
            created_at
        ) VALUES (
            :first_name, :last_name, :email, :phone,
            :address, :postal_code, :city,
            :preferred_activities, 'visiteur',
            :token, :token_expiry,
            NOW()
        )
    ");
    
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':postal_code', $postal_code);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':preferred_activities', $preferred_activities);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':token_expiry', $token_expiry);
    
    if ($stmt->execute()) {
        // Envoyer l'email de définition de mot de passe
        $mailer = new Mailer();
        $emailSent = $mailer->sendPasswordSetupEmail($email, $first_name, $token);
        
        // Envoyer une notification au bureau
        $mailer->sendNewMemberNotification($email, "$first_name $last_name");
        
        if ($emailSent) {
            header("Location: inscription.php?success=1");
        } else {
            header("Location: inscription.php?success=1&email_warning=1");
        }
        exit();
    } else {
        header("Location: inscription.php?error=database");
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Erreur inscription: " . $e->getMessage());
    header("Location: inscription.php?error=database");
    exit();
}
?>
