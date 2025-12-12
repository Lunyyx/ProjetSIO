<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Activite.php';
require_once __DIR__ . '/../src/models/Animateur.php';
require_once __DIR__ . '/../src/models/Inscription.php';

// Vérifier que l'utilisateur est connecté et est animateur ou bureau
if (!estConnecte() || (!aLeDroit('animateur') && !aLeDroit('bureau'))) {
    setMessage('Accès refusé. Vous devez être animateur pour accéder à cette page.', 'danger');
    rediriger('/');
}

$activiteModel = new Activite();
$animateurModel = new Animateur();
$inscriptionModel = new Inscription();

// Récupérer l'animateur connecté
$animateur = $animateurModel->getParUtilisateurId($_SESSION['utilisateur_id']);

// Si l'utilisateur est bureau mais pas animateur, il peut voir toutes les activités
$estBureau = aLeDroit('bureau');

// Récupérer les activités de l'animateur (ou toutes si bureau)
if ($animateur) {
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT a.*, 
            CONCAT(an.prenom, ' ', an.nom) as animateur_nom
            FROM activites a
            LEFT JOIN animateurs an ON a.animateur_id = an.id
            WHERE a.animateur_id = ? AND a.statut = 'active'
            ORDER BY FIELD(a.jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), a.heure_debut";
    $stmt = $db->prepare($sql);
    $stmt->execute([$animateur['id']]);
    $activites = $stmt->fetchAll();
} elseif ($estBureau) {
    $activites = $activiteModel->getTous();
} else {
    setMessage('Profil animateur non trouvé.', 'danger');
    rediriger('/');
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $activiteId = $_POST['activite_id'] ?? null;
    
    if ($action === 'annuler_seance' && $activiteId) {
        // Annuler une séance (change le statut temporairement)
        $stmt = $db->prepare("UPDATE activites SET statut = 'annulee' WHERE id = ?");
        if ($stmt->execute([$activiteId])) {
            setMessage('La séance a été annulée.', 'success');
        }
        rediriger('/gestion-seances.php');
    }
    
    if ($action === 'reactiver_seance' && $activiteId) {
        $stmt = $db->prepare("UPDATE activites SET statut = 'active' WHERE id = ?");
        if ($stmt->execute([$activiteId])) {
            setMessage('La séance a été réactivée.', 'success');
        }
        rediriger('/gestion-seances.php');
    }
}

// Activité sélectionnée pour voir les détails
$activiteSelectionnee = null;
$inscrits = [];
if (isset($_GET['activite'])) {
    $activiteSelectionnee = $activiteModel->getParId($_GET['activite']);
    
    // Vérifier que l'animateur a le droit de voir cette activité
    if ($activiteSelectionnee && ($estBureau || ($animateur && $activiteSelectionnee['animateur_id'] == $animateur['id']))) {
        // Récupérer les inscrits
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT ad.*, i.date_inscription, i.statut as statut_inscription
                FROM inscriptions i
                JOIN adherents ad ON i.adherent_id = ad.id
                WHERE i.activite_id = ? AND i.statut = 'active'
                ORDER BY ad.nom, ad.prenom";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_GET['activite']]);
        $inscrits = $stmt->fetchAll();
    } else {
        $activiteSelectionnee = null;
    }
}

include __DIR__ . '/../src/includes/header.php';
?>

<div class="container">
    <?php afficherMessage(); ?>
    
    <div class="page-header">
        <h1>Gestion des séances</h1>
        <?php if ($animateur): ?>
            <p class="subtitle">Bienvenue, <?php echo e($animateur['prenom'] . ' ' . $animateur['nom']); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="seances-grid">
        <!-- Liste des activités -->
        <div class="seances-list">
            <div class="card">
                <div class="card-header">
                    <h2>Mes activités</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($activites)): ?>
                        <p class="empty-state">Aucune activité assignée.</p>
                    <?php else: ?>
                        <div class="activites-cards">
                            <?php foreach ($activites as $activite): ?>
                                <?php 
                                $nbInscrits = $activiteModel->getNombreInscrits($activite['id']);
                                $estComplete = $nbInscrits >= $activite['capacite_max'];
                                $estSelectionnee = $activiteSelectionnee && $activiteSelectionnee['id'] == $activite['id'];
                                ?>
                                <a href="?activite=<?php echo $activite['id']; ?>" 
                                   class="activite-item <?php echo $estSelectionnee ? 'active' : ''; ?>">
                                    <div class="activite-jour"><?php echo $activite['jour_semaine']; ?></div>
                                    <div class="activite-info">
                                        <h3><?php echo e($activite['nom']); ?></h3>
                                        <div class="activite-horaire">
                                            <?php echo substr($activite['heure_debut'], 0, 5); ?>
                                            <?php if ($activite['heure_fin']): ?>
                                                - <?php echo substr($activite['heure_fin'], 0, 5); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($activite['lieu']): ?>
                                            <div class="activite-lieu">📍 <?php echo e($activite['lieu']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activite-inscrits">
                                        <span class="badge <?php echo $estComplete ? 'badge-danger' : 'badge-success'; ?>">
                                            <?php echo $nbInscrits; ?>/<?php echo $activite['capacite_max']; ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Détails de l'activité sélectionnée -->
        <div class="seances-details">
            <?php if ($activiteSelectionnee): ?>
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo e($activiteSelectionnee['nom']); ?></h2>
                        <span class="badge badge-primary"><?php echo $activiteSelectionnee['jour_semaine']; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Horaire</span>
                                <span class="detail-value">
                                    <?php echo substr($activiteSelectionnee['heure_debut'], 0, 5); ?>
                                    <?php if ($activiteSelectionnee['heure_fin']): ?>
                                        - <?php echo substr($activiteSelectionnee['heure_fin'], 0, 5); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Durée</span>
                                <span class="detail-value"><?php echo $activiteSelectionnee['duree_minutes']; ?> min</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Lieu</span>
                                <span class="detail-value"><?php echo e($activiteSelectionnee['lieu'] ?: 'Non défini'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Capacité</span>
                                <span class="detail-value">
                                    <?php echo $activiteModel->getNombreInscrits($activiteSelectionnee['id']); ?> / 
                                    <?php echo $activiteSelectionnee['capacite_max']; ?> places
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($activiteSelectionnee['description']): ?>
                            <div class="description-block">
                                <h4>Description</h4>
                                <p><?php echo nl2br(e($activiteSelectionnee['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Liste des inscrits -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Participants inscrits (<?php echo count($inscrits); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($inscrits)): ?>
                            <p class="empty-state">Aucun participant inscrit pour cette activité.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Téléphone</th>
                                            <th>Date d'inscription</th>
                                            <th>Cotisation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inscrits as $inscrit): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e($inscrit['prenom'] . ' ' . $inscrit['nom']); ?></strong>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo e($inscrit['email']); ?>">
                                                        <?php echo e($inscrit['email']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo e($inscrit['telephone'] ?: '-'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($inscrit['date_inscription'])); ?></td>
                                                <td>
                                                    <?php if ($inscrit['cotisation_payee']): ?>
                                                        <span class="badge badge-success">Payée</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Non payée</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Export / Actions -->
                            <div class="actions-bar">
                                <button type="button" class="btn btn-secondary" onclick="window.print()">
                                    🖨️ Imprimer la liste
                                </button>
                                <a href="mailto:<?php echo implode(',', array_column($inscrits, 'email')); ?>" class="btn btn-primary">
                                    ✉️ Envoyer un email groupé
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state-large">
                            <div class="empty-icon">📋</div>
                            <h3>Sélectionnez une activité</h3>
                            <p>Cliquez sur une activité dans la liste pour voir les détails et la liste des participants.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    margin-bottom: 0.5rem;
}

.page-header .subtitle {
    color: var(--text-muted);
    font-size: 1.1rem;
}

.seances-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    align-items: start;
}

.activites-cards {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.activite-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: var(--radius-sm);
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}

.activite-item:hover {
    background: white;
    border-color: var(--primary);
    color: inherit;
}

.activite-item.active {
    background: white;
    border-color: var(--primary);
    box-shadow: var(--shadow);
}

.activite-jour {
    background: var(--secondary);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-xs);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    min-width: 70px;
    text-align: center;
}

.activite-info {
    flex: 1;
}

.activite-info h3 {
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--secondary);
}

.activite-horaire {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.activite-lieu {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.activite-inscrits .badge {
    font-size: 0.85rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.detail-item {
    background: var(--bg-light);
    padding: 1rem;
    border-radius: var(--radius-xs);
}

.detail-label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.detail-value {
    font-weight: 600;
    color: var(--secondary);
}

.description-block {
    padding: 1rem;
    background: var(--bg-light);
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--primary);
}

.description-block h4 {
    font-size: 0.9rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.mt-3 {
    margin-top: 1.5rem;
}

.actions-bar {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.empty-state {
    text-align: center;
    color: var(--text-muted);
    padding: 2rem;
}

.empty-state-large {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-state-large h3 {
    color: var(--secondary);
    margin-bottom: 0.5rem;
}

.empty-state-large p {
    color: var(--text-muted);
}

@media (max-width: 900px) {
    .seances-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-bar {
        flex-direction: column;
    }
}

@media print {
    .navbar, .seances-list, .actions-bar, .btn {
        display: none !important;
    }
    
    .seances-grid {
        display: block;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
}
</style>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
