<?php
declare(strict_types=1);

class CalendarModel {
    private PDO $pdo;

    public function __construct() {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db   = $_ENV['DB_NAME'] ?? 'calendrier';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function getAvailability(): array {
        return $this->pdo->query("SELECT start_hour AS start, end_hour AS end FROM availability")->fetchAll();
    }

    public function getEvents(): array {
        return $this->pdo->query("SELECT * FROM activities ORDER BY FIELD(day, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), start_time")->fetchAll();
    }

    public function addEvent(string $day, string $time, string $title, int $duration, string $color): void {
        $stmt = $this->pdo->prepare("INSERT INTO activities (day, start_time, title, duration, color) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$day, $time, $title, $duration, $color]);
    }

    public function deleteEvent(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getTitleColorMapping(): array {
        $events = $this->pdo->query("SELECT title, color FROM activities")->fetchAll();
        $counts = [];
        
        foreach ($events as $e) {
            $t = trim($e['title']);
            $c = strtolower(trim($e['color']));
            
            if (!isset($counts[$t])) $counts[$t] = [];
            if (!isset($counts[$t][$c])) $counts[$t][$c] = 0;
            $counts[$t][$c]++;
        }
        
        $mapping = [];
        foreach ($counts as $title => $colors) {
            arsort($colors); 
            $mapping[$title] = array_key_first($colors);
        }
        
        return $mapping;
    }

    public function updateEventTime(int $id, string $day, string $time): void {
        $stmt = $this->pdo->prepare("UPDATE activities SET day = ?, start_time = ? WHERE id = ?");
        $stmt->execute([$day, $time, $id]);
    }
}