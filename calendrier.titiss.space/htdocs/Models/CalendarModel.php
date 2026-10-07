<?php
declare(strict_types=1);

class CalendarModel {
    private PDO $pdo;
    private int $userId;

    public function __construct() {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db = $_ENV['DB_NAME'] ?? 'calendrier';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $this->pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->userId=(int)($_SESSION['user']['id']??0);
        if($this->userId<1) throw new RuntimeException('Connexion requise.');
        $this->ensureCalendarSchema();
    }

    private function ensureCalendarSchema(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS calendars (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            is_global TINYINT(1) NOT NULL DEFAULT 0,
            user_id INT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_calendars_user (user_id),
            CONSTRAINT fk_calendars_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ownerColumn=$this->pdo->query("SHOW COLUMNS FROM calendars LIKE 'user_id'")->fetch();
        if(!$ownerColumn){
            $this->pdo->exec("ALTER TABLE calendars ADD user_id INT NULL");
            $this->pdo->exec("ALTER TABLE calendars ADD INDEX idx_calendars_user (user_id)");
            $this->pdo->exec("ALTER TABLE calendars ADD CONSTRAINT fk_calendars_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        }
        $claim=$this->pdo->prepare("UPDATE calendars SET user_id=? WHERE user_id IS NULL");
        $claim->execute([$this->userId]);
        if(($ownerColumn['Null']??'YES')==='YES') $this->pdo->exec("ALTER TABLE calendars MODIFY user_id INT NOT NULL");
        $global=$this->pdo->prepare("SELECT id FROM calendars WHERE user_id=? AND is_global=1 LIMIT 1");
        $global->execute([$this->userId]);
        $globalRow=$global->fetch();
        if(!$globalRow){
            $create=$this->pdo->prepare("INSERT INTO calendars(name,start_date,end_date,is_global,user_id) VALUES('Calendrier global',NULL,NULL,1,?)");
            $create->execute([$this->userId]);
            $globalId=(int)$this->pdo->lastInsertId();
        } else {
            $globalId=(int)$globalRow['id'];
        }
        $availabilityOwnerQuery=$this->pdo->query("SELECT user_id FROM calendars WHERE is_global=1 AND user_id IS NOT NULL ORDER BY id LIMIT 1");
        $availabilityOwnerId=(int)($availabilityOwnerQuery->fetchColumn()?:$this->userId);
        $columns = $this->pdo->query("SHOW COLUMNS FROM activities LIKE 'calendar_id'")->fetch();
        if (!$columns) {
            $this->pdo->exec("ALTER TABLE activities ADD calendar_id INT NULL");
            $assign=$this->pdo->prepare("UPDATE activities SET calendar_id=?");
            $assign->execute([$globalId]);
            $this->pdo->exec("ALTER TABLE activities MODIFY calendar_id INT NOT NULL DEFAULT 1");
            $this->pdo->exec("ALTER TABLE activities ADD INDEX idx_activities_calendar (calendar_id)");
            $this->pdo->exec("ALTER TABLE activities ADD CONSTRAINT fk_activities_calendar FOREIGN KEY (calendar_id) REFERENCES calendars(id) ON DELETE CASCADE");
        }
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS availability (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            start_hour INT NOT NULL,
            end_hour INT NOT NULL,
            user_id INT NULL,
            KEY idx_availability_user (user_id),
            CONSTRAINT fk_availability_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $availabilityOwner=$this->pdo->query("SHOW COLUMNS FROM availability LIKE 'user_id'")->fetch();
        if(!$availabilityOwner){
            $this->pdo->exec("ALTER TABLE availability ADD user_id INT NULL");
            $this->pdo->exec("ALTER TABLE availability ADD INDEX idx_availability_user (user_id)");
            $this->pdo->exec("ALTER TABLE availability ADD CONSTRAINT fk_availability_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        }
        $claimAvailability=$this->pdo->prepare("UPDATE availability SET user_id=? WHERE user_id IS NULL");
        $claimAvailability->execute([$availabilityOwnerId]);
        if(($availabilityOwner['Null']??'YES')==='YES') $this->pdo->exec("ALTER TABLE availability MODIFY user_id INT NOT NULL");
        $availabilityCount=$this->pdo->prepare("SELECT COUNT(*) FROM availability WHERE user_id=?");
        $availabilityCount->execute([$this->userId]);
        if((int)$availabilityCount->fetchColumn()===0){
            $insert=$this->pdo->prepare("INSERT INTO availability(start_hour,end_hour,user_id) VALUES(?,?,?)");
            $insert->execute([9,12,$this->userId]); $insert->execute([13,20,$this->userId]);
        }
    }

    public function getCalendars(): array {
        $stmt=$this->pdo->prepare("SELECT * FROM calendars WHERE user_id=? ORDER BY is_global DESC, start_date DESC, id DESC");
        $stmt->execute([$this->userId]); $calendars=$stmt->fetchAll();
        foreach ($calendars as &$calendar) $calendar['archived'] = $calendar['end_date'] !== null && $calendar['end_date'] < date('Y-m-d');
        unset($calendar);
        return $calendars;
    }
    public function getCalendar(int $id): ?array {
        $stmt=$this->pdo->prepare("SELECT * FROM calendars WHERE id=? AND user_id=?"); $stmt->execute([$id,$this->userId]); $calendar=$stmt->fetch();
        if (!$calendar) return null;
        $calendar['archived'] = $calendar['end_date'] !== null && $calendar['end_date'] < date('Y-m-d');
        return $calendar;
    }
    public function createCalendar(string $name, string $start, string $end): int {
        $stmt=$this->pdo->prepare("INSERT INTO calendars(name,start_date,end_date,is_global,user_id) VALUES(?,?,?,0,?)"); $stmt->execute([$name,$start,$end,$this->userId]); return (int)$this->pdo->lastInsertId();
    }
    public function updateCalendar(int $id, string $name, string $start, string $end): void {
        $stmt=$this->pdo->prepare("UPDATE calendars SET name=?, start_date=?, end_date=? WHERE id=? AND user_id=? AND is_global=0");
        $stmt->execute([$name,$start,$end,$id,$this->userId]);
    }
    public function deleteCalendar(int $id): void {
        $stmt=$this->pdo->prepare("DELETE FROM calendars WHERE id=? AND user_id=? AND is_global=0");
        $stmt->execute([$id,$this->userId]);
    }
    public function duplicateCalendar(int $sourceId, string $name, string $start, string $end): int {
        $this->pdo->beginTransaction();
        try {
            $stmt=$this->pdo->prepare("INSERT INTO calendars(name,start_date,end_date,is_global,user_id) VALUES(?,?,?,0,?)");
            $stmt->execute([$name,$start,$end,$this->userId]);
            $newId=(int)$this->pdo->lastInsertId();
            $copy=$this->pdo->prepare("INSERT INTO activities(calendar_id,day,start_time,title,duration,color) SELECT ?,a.day,a.start_time,a.title,a.duration,a.color FROM activities a INNER JOIN calendars c ON c.id=a.calendar_id WHERE a.calendar_id=? AND c.user_id=?");
            $copy->execute([$newId,$sourceId,$this->userId]);
            $this->pdo->commit();
            return $newId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function getAvailability(): array {
        $stmt=$this->pdo->prepare("SELECT start_hour AS start, end_hour AS end FROM availability WHERE user_id=? ORDER BY start_hour");
        $stmt->execute([$this->userId]); return $stmt->fetchAll();
    }
    public function saveAvailability(array $periods): void {
        $this->pdo->beginTransaction();
        try {
            $delete=$this->pdo->prepare("DELETE FROM availability WHERE user_id=?");
            $delete->execute([$this->userId]);
            $insert=$this->pdo->prepare("INSERT INTO availability(start_hour,end_hour,user_id) VALUES(?,?,?)");
            foreach($periods as $period) $insert->execute([(int)$period['start'],(int)$period['end'],$this->userId]);
            $this->pdo->commit();
        } catch(Throwable $e) { $this->pdo->rollBack(); throw $e; }
    }
    public function getEvents(int $calendarId): array {
        $stmt=$this->pdo->prepare("SELECT * FROM activities WHERE calendar_id=? ORDER BY FIELD(day,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'), start_time"); $stmt->execute([$calendarId]); return $stmt->fetchAll();
    }
    public function addEvent(int $calendarId,string $day,string $time,string $title,int $duration,string $color): void {
        $s=$this->pdo->prepare("INSERT INTO activities(calendar_id,day,start_time,title,duration,color) VALUES(?,?,?,?,?,?)"); $s->execute([$calendarId,$day,$time,$title,$duration,$color]);
    }
    public function deleteEvent(int $calendarId,int $id): void { $s=$this->pdo->prepare("DELETE FROM activities WHERE id=? AND calendar_id=?"); $s->execute([$id,$calendarId]); }
    public function updateEventTime(int $calendarId,int $id,string $day,string $time): void { $s=$this->pdo->prepare("UPDATE activities SET day=?,start_time=? WHERE id=? AND calendar_id=?"); $s->execute([$day,$time,$id,$calendarId]); }
    public function getTitleColorMapping(int $calendarId): array {
        $s=$this->pdo->prepare("SELECT title,color FROM activities WHERE calendar_id=?"); $s->execute([$calendarId]); $counts=[];
        foreach($s->fetchAll() as $e){$t=trim($e['title']);$c=strtolower(trim($e['color']));$counts[$t][$c]=($counts[$t][$c]??0)+1;}
        $mapping=[]; foreach($counts as $t=>$colors){arsort($colors);$mapping[$t]=array_key_first($colors);} return $mapping;
    }
}
