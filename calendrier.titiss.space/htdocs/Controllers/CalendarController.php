<?php
declare(strict_types=1);
require_once __DIR__ . '/../Models/CalendarModel.php';

class CalendarController {
    private function textLength(string $text): int {
        return preg_match_all('/./us', $text, $matches) ?: 0;
    }
    private function redirectToCalendar(int $id): void {
        header('Location: ?calendar=' . $id);
        exit;
    }

    private function validDate(string $date): bool {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    public function index(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') requireValidCsrf();
        $model = new CalendarModel();
        $calendars = $model->getCalendars();
        $today = date('Y-m-d');
        $globalCalendar = null;
        $currentCalendar = null;
        foreach ($calendars as $calendar) {
            if ((int)$calendar['is_global'] === 1) $globalCalendar = $calendar;
            if ($calendar['start_date'] !== null && $calendar['end_date'] !== null && $calendar['start_date'] <= $today && $calendar['end_date'] >= $today) {
                if ($currentCalendar === null || $calendar['start_date'] > $currentCalendar['start_date']) $currentCalendar = $calendar;
            }
        }
        if ($globalCalendar === null) throw new RuntimeException('Le calendrier global est introuvable.');
        $defaultCalendar = $currentCalendar ?? $globalCalendar;
        $requestedId = (int)($_GET['calendar'] ?? $_POST['calendar_id'] ?? $defaultCalendar['id']);
        $requestedCalendar=$model->getCalendar($requestedId);
        if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['calendar_id']) && !$requestedCalendar) {
            http_response_code(404);
            exit('Calendrier introuvable.');
        }
        $calendar = $requestedCalendar ?? $defaultCalendar;
        $calendarId = (int)$calendar['id'];
        $isArchived = (bool)$calendar['archived'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = (string)($_POST['action'] ?? '');
            if ($action === 'save_availability') {
                $starts=$_POST['availability_start']??[]; $ends=$_POST['availability_end']??[];
                if (!is_array($starts) || !is_array($ends) || count($starts)<1 || count($starts)>8 || count($starts)!==count($ends)) {
                    http_response_code(400); $errorMessage='Ajoutez entre 1 et 8 plages horaires.';
                } else {
                    $periods=[]; $valid=true;
                    foreach($starts as $index=>$startValue){
                        $endValue=$ends[$index]??null;
                        if (!is_string($startValue) || !is_string($endValue) || !preg_match('/^(?:[0-9]|1[0-9]|2[0-3])$/',$startValue) || !preg_match('/^(?:[1-9]|1[0-9]|2[0-3]|24)$/',$endValue) || (int)$startValue >= (int)$endValue) { $valid=false; break; }
                        $periods[]=['start'=>(int)$startValue,'end'=>(int)$endValue];
                    }
                    usort($periods,static fn($a,$b)=>$a['start']<=>$b['start']);
                    for($i=1;$i<count($periods);$i++) if($periods[$i]['start']<$periods[$i-1]['end']) $valid=false;
                    if(!$valid) { http_response_code(400); $errorMessage='Vérifiez les heures : chaque fin doit suivre son début et les plages ne doivent pas se chevaucher.'; }
                    else { $model->saveAvailability($periods); $this->redirectToCalendar($calendarId); }
                }
            } elseif (in_array($action, ['create_calendar','update_calendar','duplicate_calendar'], true)) {
                $name = trim((string)($_POST['name'] ?? ''));
                $start = (string)($_POST['start_date'] ?? '');
                $end = (string)($_POST['end_date'] ?? '');
                if ($name === '' || $this->textLength($name) > 100 || !$this->validDate($start) || !$this->validDate($end) || $end < $start) {
                    http_response_code(400);
                    $errorMessage = 'Vérifiez le nom et les dates : la date de fin doit être égale ou postérieure à la date de début.';
                } else {
                    if ($action === 'update_calendar' && (int)$calendar['is_global'] !== 1) {
                        $model->updateCalendar($calendarId,$name,$start,$end);
                        $this->redirectToCalendar($calendarId);
                    } elseif ($action === 'duplicate_calendar') {
                        $newId=$model->duplicateCalendar($calendarId,$name,$start,$end);
                        $this->redirectToCalendar($newId);
                    } elseif ($action === 'create_calendar') {
                        $newId = $model->createCalendar($name, $start, $end);
                        $this->redirectToCalendar($newId);
                    }
                }
            } elseif ($action === 'delete_calendar') {
                if ((int)$calendar['is_global'] !== 1) {
                    $model->deleteCalendar($calendarId);
                    $this->redirectToCalendar((int)$globalCalendar['id']);
                }
            } elseif (in_array($action, ['add', 'delete', 'move'], true)) {
                if ($isArchived) {
                    if ($action === 'move') { header('Content-Type: application/json'); http_response_code(403); echo json_encode(['success' => false, 'error' => 'Ce calendrier est archivé.']); exit; }
                    http_response_code(403);
                    $errorMessage = 'Ce calendrier est archivé et ne peut plus être modifié.';
                } elseif ($action === 'move') {
                    header('Content-Type: application/json');
                    try {
                        $day = (string)($_POST['day'] ?? ''); $time = (string)($_POST['time'] ?? '');
                        if (!in_array($day, ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'], true) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) throw new InvalidArgumentException('Jour ou heure invalide.');
                        $model->updateEventTime($calendarId, (int)($_POST['id'] ?? 0), $day, $time);
                        echo json_encode(['success' => true]);
                    } catch (Throwable $e) { http_response_code(400); echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
                    exit;
                } elseif ($action === 'add') {
                    $day=(string)($_POST['day']??''); $time=(string)($_POST['time']??''); $title=trim((string)($_POST['title']??''));
                    $duration=(int)($_POST['duration']??0); $color=(string)($_POST['color']??'');
                    if (in_array($day,['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'],true) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',$time) && $title!=='' && $this->textLength($title)<=100 && $duration>=1 && $duration<=1440 && preg_match('/^#[0-9a-fA-F]{6}$/',$color)) {
                        $model->addEvent($calendarId,$day,$time,$title,$duration,$color);
                        $this->redirectToCalendar($calendarId);
                    } else { http_response_code(400); $errorMessage='Les informations de l’activité sont invalides.'; }
                } elseif ($action === 'delete') {
                    $model->deleteEvent($calendarId,(int)($_POST['id']??0));
                    $this->redirectToCalendar($calendarId);
                }
            }
        }

        $rawEvents = $model->getEvents($calendarId);
        $availabilityPeriods = $model->getAvailability();
        $titleColorMapping = $model->getTitleColorMapping($calendarId);
        $events=[]; $eventsByDay=[]; $globalStart=24; $globalEnd=0; $maxEventHour=0; $minEventHour=24;
        foreach($availabilityPeriods as $period){if($period['start']<$globalStart)$globalStart=(int)$period['start'];if($period['end']>$globalEnd)$globalEnd=(int)$period['end'];}
        if(empty($availabilityPeriods)){$availabilityPeriods=[['start'=>9,'end'=>20]];$globalStart=9;$globalEnd=20;}
        foreach($rawEvents as $event){
            $eventsByDay[$event['day']][]=$event; $timeParts=explode(':',$event['start_time']);$startHour=(int)$timeParts[0];$minuteOffset=isset($timeParts[1])?(int)$timeParts[1]:0;$duration=(int)$event['duration'];
            if($startHour<$minEventHour)$minEventHour=$startHour;$requiredHour=(int)ceil(($startHour*60+$minuteOffset+$duration)/60)-1;if($requiredHour>$maxEventHour)$maxEventHour=$requiredHour;
        }
        if($maxEventHour>=$globalEnd)$globalEnd=$maxEventHour+1;if($minEventHour<$globalStart)$globalStart=$minEventHour;
        foreach($eventsByDay as $day=>$dayEvents){
            usort($dayEvents,fn($a,$b)=>strtotime($a['start_time'])-strtotime($b['start_time']));
            for($i=0;$i<count($dayEvents);$i++){$evt=$dayEvents[$i];$parts=explode(':',$evt['start_time']);$hourKey=$parts[0].':00';$minute=(int)($parts[1]??0);$duration=(int)$evt['duration'];$gap=0;
                if($i<count($dayEvents)-1){$next=explode(':',$dayEvents[$i+1]['start_time']);$gap=(int)$next[0]*60+(int)($next[1]??0)-((int)$parts[0]*60+$minute+$duration);}
                $events[$day][$hourKey][]=['id'=>$evt['id'],'title'=>$evt['title'],'start_time'=>$evt['start_time'],'duration'=>$duration,'offset'=>$minute,'color'=>$evt['color']??'#007aff','gap_to_next'=>$gap>0?$gap:0];
            }
        }
        $days=['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];$hours=[];for($i=0;$i<24;$i++)$hours[]=sprintf('%02d:00',$i);
        $todayString=$days[(int)date('N')-1];
        require __DIR__ . '/../Views/calendar_view.php';
    }
}
