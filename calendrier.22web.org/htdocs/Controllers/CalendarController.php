<?php
declare(strict_types=1);
require_once __DIR__ . '/../Models/CalendarModel.php';

class CalendarController {
    
    public function index(): void {
        $model = new CalendarModel();

        // 1. Gestion des requêtes POST (Formulaires et AJAX Drag & Drop)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Requête AJAX pour le Drag & Drop
            if (isset($_POST['action']) && $_POST['action'] === 'move') {
                header('Content-Type: application/json');
                try {
                    $model->updateEventTime((int)$_POST['id'], $_POST['day'], $_POST['time']);
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                exit;
            }

            // Requêtes classiques (Ajout/Suppression)
            if (isset($_POST['action']) && $_POST['action'] === 'add') {
                $model->addEvent($_POST['day'], $_POST['time'], $_POST['title'], (int)$_POST['duration'], $_POST['color']);
            } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $model->deleteEvent((int)$_POST['id']);
            }
            
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        // 2. Préparation des données pour l'affichage
        $rawEvents = $model->getEvents();
        $availabilityPeriods = $model->getAvailability();
        $titleColorMapping = $model->getTitleColorMapping();

        $events = [];
        $eventsByDay = [];
        $globalStart = 24; 
        $globalEnd = 0;    
        $maxEventHour = 0;
        $minEventHour = 24;

        foreach ($availabilityPeriods as $period) {
            if ($period['start'] < $globalStart) $globalStart = $period['start'];
            if ($period['end'] > $globalEnd) $globalEnd = $period['end'];
        }

        if (empty($availabilityPeriods)) {
            $availabilityPeriods[] = ['start' => 9, 'end' => 20];
            $globalStart = 9;
            $globalEnd = 20;
        }

        foreach ($rawEvents as $event) {
            $eventsByDay[$event['day']][] = $event;
            
            $timeParts = explode(':', $event['start_time']);
            $startHour = (int)$timeParts[0];
            $minuteOffset = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
            $duration = (int)($event['duration'] ?? 60);
            
            if ($startHour < $minEventHour) $minEventHour = $startHour;
            
            $endMinutes = ($startHour * 60) + $minuteOffset + $duration;
            $requiredHour = (int)ceil($endMinutes / 60) - 1;
            
            if ($requiredHour > $maxEventHour) $maxEventHour = $requiredHour;
        }

        if ($maxEventHour >= $globalEnd) $globalEnd = $maxEventHour + 1; 
        if ($minEventHour < $globalStart) $globalStart = $minEventHour;

        foreach ($eventsByDay as $day => $dayEvents) {
            usort($dayEvents, function($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });

            for ($i = 0; $i < count($dayEvents); $i++) {
                $evt = $dayEvents[$i];
                $timeParts = explode(':', $evt['start_time']);
                $hourKey = $timeParts[0] . ':00';
                $minuteOffset = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                $duration = (int)($evt['duration'] ?? 60);

                $gapToNext = 0;
                if ($i < count($dayEvents) - 1) {
                    $nextEvt = $dayEvents[$i+1];
                    $currEndMinutes = ((int)$timeParts[0] * 60) + $minuteOffset + $duration;
                    $nextTimeParts = explode(':', $nextEvt['start_time']);
                    $nextStartMinutes = ((int)$nextTimeParts[0] * 60) + ((int)($nextTimeParts[1] ?? 0));
                    $gapToNext = $nextStartMinutes - $currEndMinutes;
                }

                if (!isset($events[$day][$hourKey])) {
                    $events[$day][$hourKey] = [];
                }

                $events[$day][$hourKey][] = [
                    'id' => $evt['id'],
                    'title' => $evt['title'],
                    'start_time' => $evt['start_time'],
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