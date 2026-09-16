<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/CalendarModel.php';

class CalendarController {
    
public function index(): void {
        $model = new CalendarModel();
        $rawEvents = $model->getEvents();

        $events = [];
        $eventsByDay = [];
        
        $availabilityPeriods = [];
        $globalStart = 24; 
        $globalEnd = 0;    
        
        // Variables pour pister les débordements des activités
        $maxEventHour = 0;
        $minEventHour = 24;

        // 1. Extraction de la configuration et détection des débordements
        foreach ($rawEvents as $event) {
            if (isset($event['is_config']) && $event['is_config'] === true) {
                $start = (int)($event['available_start'] ?? 9);
                $end = (int)($event['available_end'] ?? 20);
                $availabilityPeriods[] = ['start' => $start, 'end' => $end];
                
                if ($start < $globalStart) $globalStart = $start;
                if ($end > $globalEnd) $globalEnd = $end;
                
                continue; 
            }
            if (isset($event['day'], $event['time'], $event['title'])) {
                $eventsByDay[$event['day']][] = $event;
                
                // Calcul pour gérer l'affichage dynamique
                $timeParts = explode(':', $event['time']);
                $startHour = (int)$timeParts[0];
                $minuteOffset = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                $duration = (int)($event['duration'] ?? 60);
                
                // Si l'activité commence avant la disponibilité
                if ($startHour < $minEventHour) {
                    $minEventHour = $startHour;
                }
                
                // Calcul de l'heure max sur laquelle l'activité va s'étendre
                $endMinutes = ($startHour * 60) + $minuteOffset + $duration;
                // Ex: 19h30 + 90min = 21h00 (1260 min) -> requiert d'afficher la ligne de 20h
                $requiredHour = (int)ceil($endMinutes / 60) - 1;
                
                if ($requiredHour > $maxEventHour) {
                    $maxEventHour = $requiredHour;
                }
            }
        }

        // Sécurité si aucune configuration n'est trouvée
        if (empty($availabilityPeriods)) {
            $availabilityPeriods[] = ['start' => 9, 'end' => 20];
            $globalStart = 9;
            $globalEnd = 20;
        }

        // --- AJUSTEMENT DYNAMIQUE DES BORNES ---
        // Si une activité nécessite une case au-delà de l'heure de fin, on ajoute les lignes
        if ($maxEventHour >= $globalEnd) {
            $globalEnd = $maxEventHour + 1; 
        }
        // Pareil si une activité commence plus tôt
        if ($minEventHour < $globalStart) {
            $globalStart = $minEventHour;
        }

        // 2. Tri chronologique et calcul des écarts (flèches)
        foreach ($eventsByDay as $day => $dayEvents) {
            usort($dayEvents, function($a, $b) {
                return strtotime($a['time']) - strtotime($b['time']);
            });

            for ($i = 0; $i < count($dayEvents); $i++) {
                $evt = $dayEvents[$i];
                $timeParts = explode(':', $evt['time']);
                $hourKey = $timeParts[0] . ':00';
                $minuteOffset = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                $duration = (int)($evt['duration'] ?? 60);

                $gapToNext = 0;
                if ($i < count($dayEvents) - 1) {
                    $nextEvt = $dayEvents[$i+1];
                    $currEndMinutes = ((int)$timeParts[0] * 60) + $minuteOffset + $duration;
                    
                    $nextTimeParts = explode(':', $nextEvt['time']);
                    $nextStartMinutes = ((int)$nextTimeParts[0] * 60) + ((int)($nextTimeParts[1] ?? 0));

                    $gapToNext = $nextStartMinutes - $currEndMinutes;
                }

                if (!isset($events[$day][$hourKey])) {
                    $events[$day][$hourKey] = [];
                }

                $events[$day][$hourKey][] = [
                    'title' => $evt['title'],
                    'duration' => $duration,
                    'offset' => $minuteOffset,
                    'color' => $evt['color'] ?? '#007aff',
                    'gap_to_next' => $gapToNext > 0 ? $gapToNext : 0
                ];
            }
        }

        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);
        }

        require_once __DIR__ . '/../Views/calendar_view.php';
    }
}