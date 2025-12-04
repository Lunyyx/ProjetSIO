<?php
header('Content-Type: application/json');

include_once "../config/database.php";

$database = new Database();
$conn = $database->getConnection();

try {
    // Récupérer tous les cours du planning
    $stmt = $conn->prepare("
        SELECT s.*, 
               a.name as activity_name, 
               a.color as activity_color,
               i.first_name, 
               i.last_name
        FROM schedule s
        JOIN activities a ON s.activity_id = a.id
        JOIN users i ON s.user_id = i.id
        WHERE s.is_active = 1
        ORDER BY s.day_of_week, s.start_time
    ");
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $events = [];
    
    foreach ($schedules as $schedule) {
        $event = [
            'id' => $schedule['id'],
            'title' => $schedule['activity_name'],
            'backgroundColor' => $schedule['activity_color'],
            'borderColor' => $schedule['activity_color'],
            'extendedProps' => [
                'instructor' => $schedule['first_name'] . ' ' . $schedule['last_name'],
                'location' => $schedule['location'] ?? null
            ]
        ];
        
        // Si le cours est récurrent, utiliser daysOfWeek pour répéter chaque semaine
        if ($schedule['is_recurring'] == 1) {
            $event['daysOfWeek'] = [$schedule['day_of_week'] % 7]; // FullCalendar utilise 0=Dimanche
            $event['startTime'] = $schedule['start_time'];
            $event['endTime'] = $schedule['end_time'];
        } else {
            // Cours ponctuel : calculer la date précise
            $currentWeek = date('Y-m-d', strtotime('this week'));
            $dayOffset = $schedule['day_of_week'] - 1;
            $eventDate = date('Y-m-d', strtotime($currentWeek . ' +' . $dayOffset . ' days'));
            
            $event['start'] = $eventDate . 'T' . $schedule['start_time'];
            $event['end'] = $eventDate . 'T' . $schedule['end_time'];
        }
        
        $events[] = $event;
    }
    
    echo json_encode($events);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
    error_log("Erreur fetch_schedule: " . $e->getMessage());
}
