<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        try {
            // Configuration SMTP depuis .env
            $this->mail->isSMTP();
            $this->mail->Host = $_ENV['SMTP_HOST'] ?? 'localhost';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $_ENV['SMTP_USERNAME'] ?? '';
            $this->mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';
            $this->mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $_ENV['SMTP_PORT'] ?? 587;
            
            // Encodage et langue
            $this->mail->CharSet = 'UTF-8';
            $this->mail->setLanguage('fr', __DIR__ . '/../vendor/phpmailer/phpmailer/language/');
            
            // Expéditeur par défaut
            $this->mail->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@fitfun.fr',
                $_ENV['SMTP_FROM_NAME'] ?? 'Fit&Fun'
            );
            
        } catch (Exception $e) {
            error_log("Erreur configuration PHPMailer: {$this->mail->ErrorInfo}");
        }
    }
    
    /**
     * Envoyer un email de définition de mot de passe
     */
    public function sendPasswordSetupEmail($email, $firstName, $token) {
        try {
            $this->mail->addAddress($email, $firstName);
            $this->mail->Subject = 'Bienvenue chez Fit&Fun - Définissez votre mot de passe';
            
            $app_url = $_ENV['APP_URL'] ?? 'http://fitfun.ddev.site';
            $setup_link = $app_url . '/auth/setup_password.php?token=' . $token;
            
            // Corps HTML
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getPasswordSetupEmailTemplate($firstName, $setup_link);
            
            // Corps texte alternatif
            $this->mail->AltBody = "Bonjour $firstName,\n\n"
                . "Bienvenue chez Fit&Fun !\n\n"
                . "Pour activer votre compte, veuillez définir votre mot de passe en cliquant sur le lien suivant :\n"
                . "$setup_link\n\n"
                . "Ce lien est valide pendant 24 heures.\n\n"
                . "À bientôt,\nL'équipe Fit&Fun";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur envoi email: {$this->mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Template HTML pour l'email de définition de mot de passe
     */
    private function getPasswordSetupEmailTemplate($firstName, $setupLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 30px; border-radius: 10px;'>
                <h1 style='color: #007bff; margin-bottom: 20px;'>Bienvenue chez Fit&Fun !</h1>
                
                <p style='font-size: 16px;'>Bonjour <strong>$firstName</strong>,</p>
                
                <p style='font-size: 16px;'>
                    Votre compte a été créé avec succès. Pour commencer à utiliser votre espace membre, 
                    vous devez définir votre mot de passe.
                </p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$setupLink' 
                       style='background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                        Définir mon mot de passe
                    </a>
                </div>
                
                <p style='font-size: 14px; color: #666;'>
                    <strong>Note :</strong> Ce lien est valide pendant 24 heures. 
                    Si vous n'avez pas demandé la création de ce compte, veuillez ignorer cet email.
                </p>
                
                <p style='font-size: 14px; color: #666; margin-top: 30px;'>
                    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :<br>
                    <a href='$setupLink' style='color: #007bff; word-break: break-all;'>$setupLink</a>
                </p>
                
                <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                
                <p style='font-size: 14px; color: #888; text-align: center;'>
                    À bientôt,<br>
                    <strong>L'équipe Fit&Fun</strong>
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Envoyer un email de notification au bureau
     */
    public function sendNewMemberNotification($memberEmail, $memberName) {
        try {
            // Récupérer les emails des membres du bureau
            $database = new Database();
            $conn = $database->getConnection();
            
            $stmt = $conn->prepare("SELECT email, first_name FROM users WHERE role = 'membre_bureau'");
            $stmt->execute();
            $bureau_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($bureau_members as $member) {
                $this->mail->clearAddresses();
                $this->mail->addAddress($member['email'], $member['first_name']);
                $this->mail->Subject = 'Nouvelle inscription - Fit&Fun';
                
                $this->mail->isHTML(true);
                $this->mail->Body = "
                    <h2>Nouvelle inscription</h2>
                    <p>Bonjour {$member['first_name']},</p>
                    <p>Une nouvelle personne s'est inscrite sur le site :</p>
                    <ul>
                        <li><strong>Nom :</strong> $memberName</li>
                        <li><strong>Email :</strong> $memberEmail</li>
                    </ul>
                    <p>Rendez-vous sur l'espace administrateur pour consulter les détails.</p>
                ";
                
                $this->mail->send();
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur notification bureau: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>
