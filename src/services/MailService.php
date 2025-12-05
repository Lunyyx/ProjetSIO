<?php
/**
 * Service d'envoi d'emails
 * Fit&Fun - Association sportive
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private PHPMailer $mailer;
    private bool $isConfigured = false;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    /**
     * Configure le mailer avec les paramètres SMTP
     */
    private function configure(): void {
        try {
            // Configuration du serveur
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? '';
            $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            
            // Encryption
            $encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            if ($encryption === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            // Expéditeur par défaut
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@fitandfun.fr',
                $_ENV['MAIL_FROM_NAME'] ?? 'Fit&Fun'
            );
            
            // Configuration générale
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
            
            // Vérifier si la config est complète
            $this->isConfigured = !empty($_ENV['MAIL_HOST']) && !empty($_ENV['MAIL_USERNAME']);
            
        } catch (Exception $e) {
            error_log("Erreur configuration mail: " . $e->getMessage());
            $this->isConfigured = false;
        }
    }
    
    /**
     * Vérifie si le service mail est configuré
     */
    public function isConfigured(): bool {
        return $this->isConfigured;
    }
    
    /**
     * Envoie un email
     */
    public function envoyer(string $destinataire, string $sujet, string $contenuHtml, string $contenuTexte = ''): bool {
        if (!$this->isConfigured) {
            error_log("Service mail non configuré - Email non envoyé à: $destinataire");
            return false;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinataire);
            $this->mailer->Subject = $sujet;
            $this->mailer->Body = $contenuHtml;
            $this->mailer->AltBody = $contenuTexte ?: strip_tags($contenuHtml);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur envoi mail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envoie un email de définition de mot de passe
     */
    public function envoyerLienMotDePasse(string $email, string $nom, string $prenom, string $token): bool {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $lien = $appUrl . '/definir-mot-de-passe.php?token=' . urlencode($token);
        
        $sujet = "Bienvenue chez Fit&Fun - Définissez votre mot de passe";
        
        $contenuHtml = $this->getTemplateMotDePasse($nom, $prenom, $lien);
        
        return $this->envoyer($email, $sujet, $contenuHtml);
    }
    
    /**
     * Template HTML pour l'email de définition de mot de passe
     */
    private function getTemplateMotDePasse(string $nom, string $prenom, string $lien): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #ff6b35, #004e89); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🏋️ Fit&Fun</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0;">Association sportive</p>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <h2 style="color: #004e89;">Bonjour {$prenom} {$nom} !</h2>
        
        <p>Votre compte a été créé sur la plateforme Fit&Fun.</p>
        
        <p>Pour finaliser votre inscription et accéder à votre espace membre, veuillez définir votre mot de passe en cliquant sur le bouton ci-dessous :</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{$lien}" style="background: #ff6b35; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Définir mon mot de passe
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            Ce lien est valable pendant <strong>24 heures</strong>. Passé ce délai, vous devrez contacter l'administration pour obtenir un nouveau lien.
        </p>
        
        <p style="color: #666; font-size: 14px;">
            Si vous n'avez pas demandé la création de ce compte, veuillez ignorer cet email.
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
            <a href="{$lien}" style="color: #004e89; word-break: break-all;">{$lien}</a>
        </p>
    </div>
    
    <div style="background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
        <p style="margin: 0; font-size: 14px;">© 2025 Fit&Fun - Association sportive</p>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Envoie un email de confirmation d'inscription
     */
    public function envoyerConfirmationInscription(string $email, string $nom, string $prenom): bool {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        
        $sujet = "Confirmation d'inscription - Fit&Fun";
        
        $contenuHtml = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #ff6b35, #004e89); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">🏋️ Fit&Fun</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <h2 style="color: #004e89;">Bienvenue {$prenom} !</h2>
        
        <p>Votre inscription chez Fit&Fun a bien été enregistrée.</p>
        
        <p>Notre équipe va examiner votre demande et vous recevrez un email de confirmation une fois celle-ci validée.</p>
        
        <p>Vous pourrez alors vous connecter à votre espace membre et vous inscrire aux activités de votre choix.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{$appUrl}" style="background: #1a936f; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Visiter notre site
            </a>
        </div>
    </div>
    
    <div style="background: #333; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px;">
        <p style="margin: 0; font-size: 14px;">© 2025 Fit&Fun - Association sportive</p>
    </div>
</body>
</html>
HTML;
        
        return $this->envoyer($email, $sujet, $contenuHtml);
    }
}
