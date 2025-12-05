<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Adherent.php';
require_once __DIR__ . '/../../src/models/Activite.php';
require_once __DIR__ . '/../../src/models/Inscription.php';

// Vérifier que l'utilisateur a les droits d'administration
if (!estConnecte() || !aLeDroit('bureau')) {
    setMessage('Accès refusé. Droits d\'administration requis.', 'danger');
    rediriger('/login.php');
}

$adherentModel = new Adherent();
$activiteModel = new Activite();
$inscriptionModel = new Inscription();

// Statistiques
$nbAdherents = $adherentModel->compterActifs();
$activites = $activiteModel->getTous();
$nbActivites = count($activites);

// Calculer le nombre total d'inscriptions
$totalInscriptions = 0;
foreach ($activites as $activite) {
    $totalInscriptions += $activiteModel->getNombreInscrits($activite['id']);
}

$db = Database::getInstance()->getConnection();
$stmtDemandes = $db->query("SELECT COUNT(*) as total FROM demandes_inscription WHERE statut = 'en_attente'");
$nbDemandes = $stmtDemandes->fetch()['total'];

include __DIR__ . '/../../src/includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>🎯 Tableau de bord</h1>
        <p>Bienvenue dans l'espace d'administration de Fit&Fun</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $nbAdherents; ?></h3>
                <p>Adhérents actifs</p>
            </div>
            <a href="/admin/adherents.php" class="stat-link">Gérer →</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🏋️</div>
            <div class="stat-content">
                <h3><?php echo $nbActivites; ?></h3>
                <p>Activités</p>
            </div>
            <a href="/admin/activites.php" class="stat-link">Gérer →</a>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $totalInscriptions; ?></h3>
                <p>Inscriptions totales</p>
            </div>
            <a href="/admin/inscriptions.php" class="stat-link">Voir →</a>
        </div>
        
        <div class="stat-card <?php echo $nbDemandes > 0 ? 'highlight' : ''; ?>">
            <div class="stat-icon">✉️</div>
            <div class="stat-content">
                <h3><?php echo $nbDemandes; ?></h3>
                <p>Demandes en attente</p>
            </div>
            <a href="/admin/demandes.php" class="stat-link">Traiter →</a>
        </div>
    </div>
    
    <div class="admin-sections">
        <div class="admin-section">
            <h2>Dernières activités</h2>
            <div class="activity-list">
                <?php 
                $activitesRecentes = array_slice($activites, 0, 5);
                foreach ($activitesRecentes as $activite): 
                    $nbInscrits = $activiteModel->getNombreInscrits($activite['id']);
                ?>
                    <div class="activity-item">
                        <div>
                            <strong><?php echo e($activite['nom']); ?></strong>
                            <span class="text-muted"> - <?php echo e($activite['jour_semaine']); ?> <?php echo substr($activite['heure_debut'], 0, 5); ?></span>
                        </div>
                        <div>
                            <span class="badge <?php echo $nbInscrits >= $activite['capacite_max'] ? 'badge-danger' : 'badge-success'; ?>">
                                <?php echo $nbInscrits; ?> / <?php echo $activite['capacite_max']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="/admin/activites.php" class="btn btn-secondary">Voir toutes les activités</a>
        </div>
        
        <div class="admin-section">
            <h2>Actions rapides</h2>
            <div class="quick-actions">
                <a href="/admin/adherents.php?action=ajouter" class="action-btn">
                    <span>➕</span>
                    <span>Ajouter un adhérent</span>
                </a>
                <a href="/admin/activites.php?action=ajouter" class="action-btn">
                    <span>🏋️</span>
                    <span>Créer une activité</span>
                </a>
                <a href="/admin/animateurs.php" class="action-btn">
                    <span>🧑‍🏫</span>
                    <span>Gérer les animateurs</span>
                </a>
                <a href="/admin/demandes.php" class="action-btn">
                    <span>📧</span>
                    <span>Traiter les demandes</span>
                </a>
                <a href="/admin/inscriptions.php" class="action-btn">
                    <span>📋</span>
                    <span>Voir inscriptions</span>
                </a>
                <a href="/planning.php" class="action-btn">
                    <span>📅</span>
                    <span>Voir le planning</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.admin-header {
    text-align: center;
    margin-bottom: 3rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stat-card.highlight {
    border: 2px solid var(--warning-color);
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: var(--primary-color);
}

.stat-content p {
    margin: 0;
    color: #666;
}

.stat-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.admin-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}

.admin-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.admin-section h2 {
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.activity-list {
    margin-bottom: 1.5rem;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    background: var(--light-color);
    border-radius: 10px;
    text-decoration: none;
    color: var(--dark-color);
    transition: all 0.3s;
}

.action-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

.action-btn span:first-child {
    font-size: 2rem;
}

@media (max-width: 768px) {
    .admin-sections {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../../src/includes/footer.php'; ?>
