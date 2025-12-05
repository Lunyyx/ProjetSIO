<?php
/**
 * Classe Token - Gestion des tokens de réinitialisation/activation
 */

class Token {
    
    /**
     * Génère un token unique et le stocke en base
     */
    public static function generer(int $utilisateurId, string $type = 'password_reset'): string {
        $db = Database::getInstance()->getConnection();
        
        // Supprimer les anciens tokens de ce type pour cet utilisateur
        $stmt = $db->prepare("DELETE FROM tokens WHERE utilisateur_id = ? AND type = ?");
        $stmt->execute([$utilisateurId, $type]);
        
        // Générer un nouveau token
        $token = bin2hex(random_bytes(32)); // 64 caractères
        $expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Stocker le token
        $stmt = $db->prepare("INSERT INTO tokens (utilisateur_id, token, type, expire_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$utilisateurId, $token, $type, $expiration]);
        
        return $token;
    }
    
    /**
     * Vérifie si un token est valide
     */
    public static function verifier(string $token, string $type = 'password_reset'): ?array {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT t.*, u.email, u.id as utilisateur_id 
            FROM tokens t
            JOIN utilisateurs u ON t.utilisateur_id = u.id
            WHERE t.token = ? AND t.type = ? AND t.expire_at > NOW() AND t.utilise = 0
        ");
        $stmt->execute([$token, $type]);
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Marque un token comme utilisé
     */
    public static function marquerUtilise(string $token): bool {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE tokens SET utilise = 1 WHERE token = ?");
        return $stmt->execute([$token]);
    }
    
    /**
     * Supprime les tokens expirés
     */
    public static function nettoyerExpires(): int {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("DELETE FROM tokens WHERE expire_at < NOW()");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
