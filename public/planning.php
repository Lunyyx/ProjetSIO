<?php
require_once __DIR__ . '/../config/config.php';

include __DIR__ . '/../src/includes/header.php';
?>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<style>
    .fc {
        font-family: inherit;
        --fc-border-color: var(--border-color);
        --fc-button-bg-color: var(--primary);
        --fc-button-border-color: var(--primary);
        --fc-button-hover-bg-color: var(--primary-dark);
        --fc-button-hover-border-color: var(--primary-dark);
        --fc-button-active-bg-color: var(--secondary);
        --fc-button-active-border-color: var(--secondary);
        --fc-today-bg-color: rgba(255, 122, 89, 0.1);
    }
    
    .fc .fc-toolbar-title {
        font-size: 1.5rem;
        color: var(--secondary);
        font-weight: 700;
    }
    
    .fc .fc-button {
        border-radius: 8px;
        font-weight: 500;
        text-transform: capitalize;
    }
    
    .fc .fc-col-header-cell {
        background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        color: white;
        font-weight: 600;
        padding: 8px 0;
    }
    
    .fc .fc-timegrid-slot {
        height: 3.5em;
    }
    
    .fc .fc-timegrid-slot-minor {
        border-top: none;
    }
    
    .fc-event {
        border-radius: 4px;
        font-size: 0.9rem;
        overflow: visible;
    }
    
    .fc-event-main {
        padding: 4px 6px;
        overflow: visible;
        white-space: normal;
    }
    
    .fc-event-title {
        font-weight: 600;
        white-space: normal;
        overflow: visible;
    }
    
    /* Modal pour détails */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 61, 98, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-overlay.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 450px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
    }
    
    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-muted);
        cursor: pointer;
    }
    
    .modal-close:hover {
        color: var(--primary);
    }
    
    .modal-header {
        margin-bottom: 20px;
    }
    
    .modal-title {
        font-size: 1.5rem;
        color: var(--secondary);
        margin: 0 0 8px 0;
    }
    
    .modal-time {
        color: var(--primary);
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .modal-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .modal-detail {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg-light);
        border-radius: 10px;
    }
    
    .modal-detail-icon {
        font-size: 1.3rem;
    }
    
    .modal-detail-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: block;
    }
    
    .modal-detail-value {
        font-weight: 600;
        color: var(--secondary);
    }
    
    .modal-capacity {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-radius: 10px;
        margin-top: 8px;
    }
    
    .modal-capacity.free {
        background: linear-gradient(135deg, rgba(15, 191, 159, 0.1) 0%, rgba(15, 191, 159, 0.05) 100%);
        border: 1px solid var(--accent);
    }
    
    .modal-capacity.full {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
        border: 1px solid #ef4444;
    }
    
    .capacity-text {
        font-weight: 600;
    }
    
    .modal-capacity.free .capacity-text {
        color: var(--accent);
    }
    
    .modal-capacity.full .capacity-text {
        color: #ef4444;
    }
    
    .modal-description {
        margin-top: 16px;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 10px;
        color: var(--text-color);
        line-height: 1.6;
    }
    
    .planning-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }
    
    .legend-label {
        font-size: 0.9rem;
        color: var(--text-color);
    }
    
    /* Modal création */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    
    #createModal .form-group {
        margin-bottom: 1rem;
    }
    
    #createModal .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--secondary);
    }
    
    #createModal .form-control {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-xs);
        font-size: 0.95rem;
    }
    
    #createModal .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 122, 89, 0.15);
    }
    
    /* Actions modal */
    .modal-actions {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }
    
    .modal-actions .btn {
        flex: 1;
        text-align: center;
        min-width: 150px;
    }
    
    .msg-info {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
        width: 100%;
        text-align: center;
    }
    
    .msg-info a {
        color: var(--primary);
        font-weight: 600;
    }
    
    .msg-warning {
        color: var(--warning);
        font-size: 0.9rem;
        margin: 0;
        width: 100%;
        text-align: center;
        font-weight: 500;
    }
    
    .msg-success {
        color: var(--success);
        font-size: 0.9rem;
        margin: 0;
        width: 100%;
        text-align: center;
        font-weight: 500;
    }
</style>

<div class="container">
    <?php afficherMessage(); ?>
    <h1>Planning des activités</h1>
    
    <div class="planning-legend">
        <div class="legend-item">
            <span class="legend-color" style="background: #ff7a59;"></span>
            <span class="legend-label">Fitness</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #0fbf9f;"></span>
            <span class="legend-label">Zumba</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #3f8cff;"></span>
            <span class="legend-label">Yoga</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #f0b429;"></span>
            <span class="legend-label">Renforcement musculaire</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #9aa4b5;"></span>
            <span class="legend-label">Complet</span>
        </div>
    </div>
    
    <div id="calendar"></div>
    
    <div class="planning-footer" style="margin-top: 32px;">
        <p>
            <?php if (estConnecte() && aLeDroit('adherent')): ?>
                Consultez vos <a href="/mes-inscriptions.php">inscriptions</a> ou découvrez toutes nos <a href="/activites.php">activités</a>.
            <?php else: ?>
                <a href="/inscription.php">Inscrivez-vous</a> pour participer à nos activités !
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Modal détails -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle"></h2>
            <div class="modal-time" id="modalTime"></div>
        </div>
        <div class="modal-details">
            <div class="modal-detail">
                <span class="modal-detail-icon">👤</span>
                <div>
                    <span class="modal-detail-label">Animateur</span>
                    <span class="modal-detail-value" id="modalAnimateur"></span>
                </div>
            </div>
            <div class="modal-detail" id="modalLieuContainer">
                <span class="modal-detail-icon">📍</span>
                <div>
                    <span class="modal-detail-label">Lieu</span>
                    <span class="modal-detail-value" id="modalLieu"></span>
                </div>
            </div>
            <div class="modal-capacity" id="modalCapacity">
                <div>
                    <span class="modal-detail-label">Places</span>
                    <span class="capacity-text" id="modalPlaces"></span>
                </div>
                <span class="modal-detail-icon">👥</span>
            </div>
        </div>
        <div class="modal-description" id="modalDescription" style="display: none;"></div>
        
        <!-- Actions selon le rôle -->
        <div class="modal-actions">
            <input type="hidden" id="modalActiviteId">
            
            <?php if (estConnecte() && aLeDroit('animateur')): ?>
                <!-- Bouton modifier pour animateurs/bureau -->
                <a href="#" id="btnModifierActivite" class="btn btn-secondary">
                    ✏️ Modifier l'activité
                </a>
            <?php endif; ?>
            
            <?php if (estConnecte() && ($_SESSION['role'] ?? '') === 'adherent'): ?>
                <!-- Bouton inscription pour adhérents -->
                <form method="POST" action="/activites.php" style="display: inline;">
                    <input type="hidden" name="action" value="inscrire">
                    <input type="hidden" name="activite_id" id="formActiviteId">
                    <button type="submit" id="btnInscrire" class="btn btn-primary">
                        ✅ S'inscrire à cette activité
                    </button>
                </form>
                <p id="msgDejaInscrit" class="msg-info" style="display: none;">
                    ✓ Vous êtes déjà inscrit à cette activité
                </p>
                <p id="msgComplet" class="msg-warning" style="display: none;">
                    ⚠️ Cette activité est complète
                </p>
            <?php elseif (!estConnecte()): ?>
                <p class="msg-info">
                    <a href="/login.php">Connectez-vous</a> pour vous inscrire à cette activité.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (estConnecte() && aLeDroit('bureau')): ?>
<!-- Modal création d'activité -->
<div class="modal-overlay" id="createModal">
    <div class="modal-content" style="max-width: 500px;">
        <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        <div class="modal-header">
            <h2 class="modal-title">Nouvelle activité</h2>
            <div class="modal-time" id="createModalTime"></div>
        </div>
        <form id="createActivityForm" method="POST" action="/admin/activites.php">
            <input type="hidden" name="action" value="creer">
            <input type="hidden" name="jour_semaine" id="createJour">
            <input type="hidden" name="heure_debut" id="createHeureDebut">
            <input type="hidden" name="redirect" value="/planning.php">
            
            <div class="form-group">
                <label for="createNom">Nom de l'activité *</label>
                <input type="text" id="createNom" name="nom" required class="form-control">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="createHeureFin">Heure de fin</label>
                    <input type="time" id="createHeureFin" name="heure_fin" class="form-control">
                </div>
                <div class="form-group">
                    <label for="createCapacite">Capacité max</label>
                    <input type="number" id="createCapacite" name="capacite_max" value="20" min="1" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="createAnimateur">Animateur</label>
                <select id="createAnimateur" name="animateur_id" class="form-control">
                    <option value="">-- Sélectionner --</option>
                    <?php
                    require_once __DIR__ . '/../src/models/Animateur.php';
                    $animateurModel = new Animateur();
                    $animateurs = $animateurModel->getTous();
                    foreach ($animateurs as $anim): ?>
                        <option value="<?php echo $anim['id']; ?>"><?php echo e($anim['prenom'] . ' ' . $anim['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="createLieu">Lieu</label>
                <input type="text" id="createLieu" name="lieu" class="form-control" placeholder="Ex: Salle principale">
            </div>
            
            <div class="form-group">
                <label for="createDescription">Description</label>
                <textarea id="createDescription" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer l'activité</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/fr.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var canCreateActivities = <?php echo (estConnecte() && aLeDroit('bureau')) ? 'true' : 'false'; ?>;
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: "Aujourd'hui",
            week: 'Semaine',
            day: 'Jour',
            list: 'Liste'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        allDaySlot: false,
        weekends: true,
        nowIndicator: true,
        slotDuration: '00:30:00',
        slotLabelInterval: '01:00:00',
        expandRows: true,
        height: 'auto',
        contentHeight: 400,
        stickyHeaderDates: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: '/api/planning.php',
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventDidMount: function(info) {
            // Tooltip simple au survol
            info.el.title = info.event.title + ' - ' + 
                info.event.extendedProps.inscrits + '/' + 
                info.event.extendedProps.capacite + ' places';
        },
        dateClick: function(info) {
            if (!canCreateActivities) return;

            // Double-clic détection
            if (this.lastClick && (new Date() - this.lastClick) < 300) {
                openCreateModal(info);
            }
            this.lastClick = new Date();
        },
        selectable: canCreateActivities,
        select: function(info) {
            if (!canCreateActivities) return;
            openCreateModal(info);
        }
    });
    
    calendar.render();
});

function showEventDetails(event) {
    var activiteId = event.extendedProps.activite_id;
    
    document.getElementById('modalTitle').textContent = event.title;
    
    var start = event.start.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
    var end = event.end.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
    var dayName = event.start.toLocaleDateString('fr-FR', {weekday: 'long', day: 'numeric', month: 'long'});
    document.getElementById('modalTime').textContent = dayName + ' • ' + start + ' - ' + end;
    
    document.getElementById('modalAnimateur').textContent = event.extendedProps.animateur || 'Non assigné';
    
    var lieuContainer = document.getElementById('modalLieuContainer');
    if (event.extendedProps.lieu) {
        lieuContainer.style.display = 'flex';
        document.getElementById('modalLieu').textContent = event.extendedProps.lieu;
    } else {
        lieuContainer.style.display = 'none';
    }
    
    var capacityEl = document.getElementById('modalCapacity');
    var inscrits = event.extendedProps.inscrits;
    var capacite = event.extendedProps.capacite;
    var plein = event.extendedProps.plein;
    
    capacityEl.className = 'modal-capacity ' + (plein ? 'full' : 'free');
    document.getElementById('modalPlaces').textContent = inscrits + ' / ' + capacite + (plein ? ' (Complet)' : ' places');
    
    var descEl = document.getElementById('modalDescription');
    if (event.extendedProps.description) {
        descEl.style.display = 'block';
        descEl.textContent = event.extendedProps.description;
    } else {
        descEl.style.display = 'none';
    }
    
    // Stocker l'ID de l'activité
    document.getElementById('modalActiviteId').value = activiteId;
    
    // Bouton modifier (animateurs/bureau)
    var btnModifier = document.getElementById('btnModifierActivite');
    if (btnModifier) {
        btnModifier.href = '/admin/activites.php?action=modifier&id=' + activiteId;
    }
    
    // Bouton inscription (adhérents)
    var formActiviteId = document.getElementById('formActiviteId');
    if (formActiviteId) {
        formActiviteId.value = activiteId;
    }
    
    // Gestion affichage boutons pour adhérents
    var btnInscrire = document.getElementById('btnInscrire');
    var msgComplet = document.getElementById('msgComplet');
    var msgDejaInscrit = document.getElementById('msgDejaInscrit');
    
    if (btnInscrire) {
        // D'abord cacher tout
        btnInscrire.style.display = 'none';
        if (msgComplet) msgComplet.style.display = 'none';
        if (msgDejaInscrit) msgDejaInscrit.style.display = 'none';
        
        // Vérifier si déjà inscrit via API
        fetch('/api/inscriptions.php?activite_id=' + activiteId)
            .then(response => response.json())
            .then(data => {
                if (data.inscrit) {
                    if (msgDejaInscrit) msgDejaInscrit.style.display = 'block';
                } else if (plein) {
                    if (msgComplet) msgComplet.style.display = 'block';
                } else {
                    btnInscrire.style.display = 'inline-block';
                }
            })
            .catch(() => {
                // En cas d'erreur, afficher le bouton si pas complet
                if (!plein) {
                    btnInscrire.style.display = 'inline-block';
                } else {
                    if (msgComplet) msgComplet.style.display = 'block';
                }
            });
    }
    
    document.getElementById('eventModal').classList.add('active');
}

function closeModal() {
    document.getElementById('eventModal').classList.remove('active');
}

// Fermer la modal en cliquant à l'extérieur
document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        if (typeof closeCreateModal === 'function') {
            closeCreateModal();
        }
    }
});

<?php if (estConnecte() && aLeDroit('bureau')): ?>
// Fonctions pour la création d'activité
var joursMap = {
    0: 'Dimanche',
    1: 'Lundi',
    2: 'Mardi',
    3: 'Mercredi',
    4: 'Jeudi',
    5: 'Vendredi',
    6: 'Samedi'
};

function openCreateModal(info) {
    var date = info.start || info.date;
    var jour = joursMap[date.getDay()];
    var heure = date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
    
    document.getElementById('createJour').value = jour;
    document.getElementById('createHeureDebut').value = heure.replace('h', ':');
    
    // Calculer heure de fin (+1h par défaut)
    var heureFin = new Date(date.getTime() + 60*60*1000);
    document.getElementById('createHeureFin').value = heureFin.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'}).replace('h', ':');
    
    document.getElementById('createModalTime').textContent = jour + ' à ' + heure;
    document.getElementById('createModal').classList.add('active');
    document.getElementById('createNom').focus();
}

function closeCreateModal() {
    document.getElementById('createModal').classList.remove('active');
    document.getElementById('createActivityForm').reset();
}

// Fermer la modal création en cliquant à l'extérieur
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../src/includes/footer.php'; ?>
