<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Utilisateur.php';
require_once __DIR__ . '/../../src/models/Adherent.php';
require_once __DIR__ . '/../../src/models/Inscription.php';
require_once __DIR__ . '/../../src/models/Token.php';
require_once __DIR__ . '/../../src/services/MailService.php';

// Vérifier les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$message = '';
$erreur = '';
$mailService = new MailService();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $demandeId = $_POST['demande_id'] ?? 0;

    if ($action === 'valider' && $demandeId) {
        // Récupérer la demande d'inscription
        $demande = Inscription::getDemandeParId($demandeId);
        
        if ($demande && $demande['statut'] === 'en_attente') {
            try {
                // Vérifier si un utilisateur existe déjà avec cet email
                $utilisateurExistant = Utilisateur::getParEmail($demande['email']);
                
                if ($utilisateurExistant) {
                    // L'utilisateur existe déjà, vérifier s'il a un profil adhérent
                    $adherentExistant = Adherent::getParUtilisateurIdStatic($utilisateurExistant['id']);
                    
                    if (!$adherentExistant) {
                        // Créer le profil adhérent
                        Adherent::creerStatic([
                            'utilisateur_id' => $utilisateurExistant['id'],
                            'nom' => $demande['nom'],
                            'prenom' => $demande['prenom'],
                            'email' => $demande['email'],
                            'date_naissance' => $demande['date_naissance'] ?? null,
                            'telephone' => $demande['telephone'] ?? null,
                            'adresse' => $demande['adresse'] ?? null,
                            'cotisation_payee' => 0
                        ]);
                    }
                    
                    $message = "Demande validée ! L'adhérent existait déjà dans le système.";
                } else {
                    // Créer un nouveau compte utilisateur avec mot de passe temporaire
                    $motDePasseTemporaire = bin2hex(random_bytes(32));
                    
                    $utilisateurId = Utilisateur::creer([
                        'email' => $demande['email'],
                        'mot_de_passe' => password_hash($motDePasseTemporaire, PASSWORD_DEFAULT),
                        'role' => 'adherent'
                    ]);
                    
                    // Créer le profil adhérent
                    Adherent::creerStatic([
                        'utilisateur_id' => $utilisateurId,
                        'nom' => $demande['nom'],
                        'prenom' => $demande['prenom'],
                        'email' => $demande['email'],
                        'date_naissance' => $demande['date_naissance'] ?? null,
                        'telephone' => $demande['telephone'] ?? null,
                        'adresse' => $demande['adresse'] ?? null,
                        'cotisation_payee' => 0
                    ]);
                    
                    // Générer un token pour définir le mot de passe
                    $token = Token::generer($utilisateurId, 'password_set');
                    
                    // Envoyer l'email
                    if ($mailService->isConfigured()) {
                        $emailEnvoye = $mailService->envoyerLienMotDePasse(
                            $demande['email'],
                            $demande['nom'],
                            $demande['prenom'],
                            $token
                        );
                        
                        if ($emailEnvoye) {
                            $message = "Demande validée ! Un email a été envoyé à <strong>{$demande['email']}</strong> pour définir son mot de passe.";
                        } else {
                            $message = "Compte créé mais l'email n'a pas pu être envoyé. Lien direct : <br><code>" . APP_URL . "/definir-mot-de-passe.php?token={$token}</code>";
                        }
                    } else {
                        // Mail non configuré - afficher le lien directement
                        $message = "Compte créé ! (Email non configuré)<br>Lien à envoyer à l'adhérent :<br><code>" . APP_URL . "/definir-mot-de-passe.php?token={$token}</code>";
                    }
                }
                
                // Mettre à jour le statut de la demande
                Inscription::validerDemande($demandeId);
                
            } catch (Exception $e) {
                $erreur = "Erreur lors de la validation : " . $e->getMessage();
            }
        } else {
            $erreur = "Demande introuvable ou déjà traitée.";
        }
    } elseif ($action === 'refuser' && $demandeId) {
        try {
            Inscription::refuserDemande($demandeId);
            $message = "Demande refusée avec succès.";
        } catch (Exception $e) {
            $erreur = "Erreur lors du refus : " . $e->getMessage();
        }
    } elseif ($action === 'supprimer' && $demandeId) {
        try {
            Inscription::supprimerDemande($demandeId);
            $message = "Demande supprimée avec succès.";
        } catch (Exception $e) {
            $erreur = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Récupérer les demandes d'inscription
$filtreStatut = $_GET['statut'] ?? 'tous';
$demandes = Inscription::getDemandesInscription($filtreStatut);

$pageTitle = "Gestion des demandes d'inscription";
require_once __DIR__ . '/../../src/includes/header.php';
?>

<main class="container">
    <div class="page-header">
        <h1>📋 Gestion des demandes d'inscription</h1>
        <a href="/admin/" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($erreur): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="filters-bar">
        <form method="get" class="filter-form">
            <label for="statut">Filtrer par statut :</label>
            <select name="statut" id="statut" onchange="this.form.submit()">
                <option value="tous" <?php echo $filtreStatut === 'tous' ? 'selected' : ''; ?>>Tous</option>
                <option value="en_attente" <?php echo $filtreStatut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                <option value="validee" <?php echo $filtreStatut === 'validee' ? 'selected' : ''; ?>>Validées</option>
                <option value="refusee" <?php echo $filtreStatut === 'refusee' ? 'selected' : ''; ?>>Refusées</option>
            </select>
        </form>
    </div>

    <?php if (empty($demandes)): ?>
        <div class="empty-state">
            <p>Aucune demande d'inscription <?php echo $filtreStatut !== 'tous' ? 'avec ce statut' : ''; ?>.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date demande</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><?php echo $demande['id']; ?></td>
                            <td><?php echo htmlspecialchars($demande['nom']); ?></td>
                            <td><?php echo htmlspecialchars($demande['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($demande['email']); ?></td>
                            <td><?php echo htmlspecialchars($demande['telephone'] ?? '-'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                $statusText = '';
                                switch ($demande['statut']) {
                                    case 'en_attente':
                                        $statusClass = 'status-pending';
                                        $statusText = '⏳ En attente';
                                        break;
                                    case 'validee':
                                        $statusClass = 'status-active';
                                        $statusText = '✅ Validée';
                                        break;
                                    case 'refusee':
                                        $statusClass = 'status-inactive';
                                        $statusText = '❌ Refusée';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="actions-cell">
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="valider">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-success" 
                                                onclick="return confirm('Valider cette demande ? Un compte sera créé automatiquement.');">
                                            ✓ Valider
                                        </button>
                                    </form>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="refuser">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger"
                                                onclick="return confirm('Refuser cette demande ?');">
                                            ✗ Refuser
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger"
                                                onclick="return confirm('Supprimer définitivement cette demande ?');">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Informations -->
    <div class="info-box">
        <h3>ℹ️ Informations</h3>
        <ul>
            <li><strong>Validation :</strong> Crée automatiquement un compte utilisateur et un profil adhérent avec un mot de passe temporaire.</li>
            <li><strong>Refus :</strong> Marque la demande comme refusée sans créer de compte.</li>
            <li><strong>Suppression :</strong> Supprime définitivement la demande de la base de données.</li>
        </ul>
    </div>
</main>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>
