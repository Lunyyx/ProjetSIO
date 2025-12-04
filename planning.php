<?php
session_start();
$active = "planning";
?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
    <head>
        <title>Planning des cours - Fit&Fun</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="assets/css/index.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link href="assets/css/common.css" rel="stylesheet">
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
        <style>
            #calendar {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .fc-event {
                cursor: pointer;
                border-radius: 4px;
                padding: 2px 4px;
            }
            
            .fc-event:hover {
                opacity: 0.9;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            
            .fc-toolbar-title {
                font-size: 1.5rem !important;
                font-weight: 600;
            }
            
            .fc-button {
                text-transform: capitalize !important;
            }
        </style>
    </head>
    <body class="h-100 d-flex flex-column bg-light">
        <?php include_once("includes/header.php") ?>

        <div class="container my-5 flex-grow-1">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="text-center">
                        <h1 class="mb-2">
                            <i class="bi bi-calendar-week text-primary me-2"></i>
                            Planning des cours
                        </h1>
                        <p class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Consultez l'emploi du temps de tous nos cours
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Légende -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informations</h5>
                            <p class="mb-2"><i class="bi bi-mouse me-2 text-primary"></i>Cliquez sur un cours pour voir les détails</p>
                            <p class="mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Naviguez entre les semaines avec les flèches</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour afficher les détails du cours -->
        <div class="modal fade" id="eventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventModalTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="eventModalBody">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'timeGridWeek',
                    locale: 'fr',
                    firstDay: 1, // Semaine commence le lundi
                    allDaySlot: false,
                    slotMinTime: '07:00:00',
                    slotMaxTime: '22:00:00',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridWeek,timeGridDay'
                    },
                    buttonText: {
                        today: "Aujourd'hui",
                        week: 'Semaine',
                        day: 'Jour'
                    },
                    events: 'api/fetch_schedule.php',
                    eventContent: function(arg) {
                        // Personnaliser l'affichage de l'événement
                        let arrayOfDomNodes = [];
                        let titleEl = document.createElement('div');
                        titleEl.classList.add('fc-event-title');
                        titleEl.innerHTML = '<b>' + arg.event.title + '</b>';
                        
                        if (arg.event.extendedProps.location) {
                            let locationEl = document.createElement('div');
                            locationEl.classList.add('fc-event-location');
                            locationEl.style.fontSize = '0.85em';
                            locationEl.innerHTML = '📍 ' + arg.event.extendedProps.location;
                            arrayOfDomNodes.push(locationEl);
                        }
                        
                        arrayOfDomNodes.unshift(titleEl);
                        return { domNodes: arrayOfDomNodes };
                    },
                    eventClick: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;
                        
                        document.getElementById('eventModalTitle').innerHTML = 
                            '<i class="bi bi-calendar-event me-2"></i>' + event.title;
                        
                        const startTime = event.start.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                        const endTime = event.end.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
                        
                        document.getElementById('eventModalBody').innerHTML = `
                            <div class="mb-3">
                                <strong><i class="bi bi-clock me-2"></i>Horaire :</strong> ${startTime} - ${endTime}
                            </div>
                            <div class="mb-3">
                                <strong><i class="bi bi-person-badge me-2"></i>Animateur :</strong> ${props.instructor}
                            </div>
                            ${props.location ? `
                            <div class="mb-3">
                                <strong><i class="bi bi-geo-alt me-2"></i>Salle :</strong> ${props.location}
                            </div>` : ''}
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Pour réserver votre place, contactez-nous ou venez directement à la salle.
                            </div>
                        `;
                        
                        eventModal.show();
                    },
                    eventDidMount: function(info) {
                        // Ajouter une info-bulle
                        const location = info.event.extendedProps.location;
                        info.el.title = info.event.title + '\n' + 
                                       'Animateur: ' + info.event.extendedProps.instructor +
                                       (location ? '\nSalle: ' + location : '');
                    }
                });
                
                calendar.render();
            });
        </script>
    </body>
</html>