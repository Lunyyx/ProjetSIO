<?php
/**
 * API JSON pour le planning - Compatible FullCalendar
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/models/Activite.php';

$activiteModel = new Activite();
$activites = $activiteModel->getTous();

// Couleurs par type d'activité
$couleurs = [
    'Fitness' => '#ff7a59',
    'Zumba' => '#0fbf9f',
    'Yoga' => '#3f8cff',
    'Renforcement musculaire' => '#f0b429',
    'default' => '#0f3d62'
];

// Convertir en format FullCalendar
$events = [];

// Générer les événements pour les 4 prochaines semaines
$startDate = new DateTime('monday this week');
$endDate = clone $startDate;
$endDate->modify('+4 weeks');

$joursMap = [
    'Lundi' => 'Monday',
    'Mardi' => 'Tuesday',
    'Mercredi' => 'Wednesday',
    'Jeudi' => 'Thursday',
    'Vendredi' => 'Friday',
    'Samedi' => 'Saturday',
    'Dimanche' => 'Sunday'
];

foreach ($activites as $activite) {
    $jourEn = $joursMap[$activite['jour_semaine']] ?? 'Monday';
    $nbInscrits = $activiteModel->getNombreInscrits($activite['id']);
    $plein = $nbInscrits >= $activite['capacite_max'];
    
    // Générer pour chaque semaine
    $currentDate = clone $startDate;
    while ($currentDate < $endDate) {
        if ($currentDate->format('l') === $jourEn) {
            $dateStr = $currentDate->format('Y-m-d');
            $heureDebut = substr($activite['heure_debut'], 0, 5);
            $heureFin = $activite['heure_fin'] ? substr($activite['heure_fin'], 0, 5) : date('H:i', strtotime($activite['heure_debut']) + $activite['duree_minutes'] * 60);
            
            $couleur = $couleurs[$activite['nom']] ?? $couleurs['default'];
            if ($plein) {
                $couleur = '#9aa4b5'; // Grisé si complet
            }
            
            $events[] = [
                'id' => $activite['id'] . '_' . $dateStr,
                'title' => $activite['nom'],
                'start' => $dateStr . 'T' . $heureDebut . ':00',
                'end' => $dateStr . 'T' . $heureFin . ':00',
                'backgroundColor' => $couleur,
                'borderColor' => $couleur,
                'extendedProps' => [
                    'activite_id' => $activite['id'],
                    'animateur' => $activite['animateur_nom'],
                    'lieu' => $activite['lieu'],
                    'inscrits' => $nbInscrits,
                    'capacite' => $activite['capacite_max'],
                    'description' => $activite['description'],
                    'plein' => $plein
                ]
            ];
        }
        $currentDate->modify('+1 day');
    }
}

echo json_encode($events);
