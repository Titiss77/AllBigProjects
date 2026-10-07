<?php
declare(strict_types=1);

class AuthModel {
    private PDO $pdo;

    public function __construct() {
        $host=$_ENV['DB_HOST']??'127.0.0.1'; $db=$_ENV['DB_NAME']??'calendrier';
        $user=$_ENV['DB_USER']??'root'; $pass=$_ENV['DB_PASS']??''; $port=$_ENV['DB_PORT']??'3306';
        $this->pdo=new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",$user,$pass,[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(80) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_users_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email_hash CHAR(64) NOT NULL,
            ip_hash CHAR(64) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_login_ip_time (ip_hash, created_at),
            KEY idx_login_email_time (email_hash, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function emailHash(string $email): string { return hash('sha256',strtolower(trim($email))); }
    private function ipHash(): string {
        $configured=$_ENV['APP_KEY']??getenv('APP_KEY');
        $key=is_string($configured)&&$configured!==''?$configured:'change-this-app-key-before-deployment';
        return hash_hmac('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown'),$key);
    }
    public function register(string $name,string $email,string $password): int {
        $stmt=$this->pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
        $algorithm=defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT;
        $stmt->execute([$name,strtolower(trim($email)),password_hash($password,$algorithm)]);
        return (int)$this->pdo->lastInsertId();
    }
    public function findUserByEmail(string $email): ?array {
        $stmt=$this->pdo->prepare('SELECT id,name,email,password_hash FROM users WHERE email=? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]); return $stmt->fetch()?:null;
    }
    public function findUserById(int $id): ?array {
        $stmt=$this->pdo->prepare('SELECT id,name,email,password_hash FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$id]); return $stmt->fetch()?:null;
    }
    public function loginIsLimited(string $email): bool {
        $this->pdo->exec('DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 1 DAY');
        $stmt=$this->pdo->prepare('SELECT
            SUM(ip_hash=?) AS ip_count,
            SUM(email_hash=? AND ip_hash=?) AS pair_count
            FROM login_attempts WHERE created_at >= NOW() - INTERVAL 15 MINUTE');
        $ip=$this->ipHash(); $stmt->execute([$ip,$this->emailHash($email),$ip]); $row=$stmt->fetch();
        return (int)$row['ip_count']>=30 || (int)$row['pair_count']>=8;
    }
    public function recordFailedLogin(string $email): void {
        $stmt=$this->pdo->prepare('INSERT INTO login_attempts(email_hash,ip_hash) VALUES(?,?)');
        $stmt->execute([$this->emailHash($email),$this->ipHash()]);
    }
    public function clearLoginAttempts(string $email): void {
        $stmt=$this->pdo->prepare('DELETE FROM login_attempts WHERE email_hash=? AND ip_hash=?');
        $stmt->execute([$this->emailHash($email),$this->ipHash()]);
    }
    public function registrationIsLimited(): bool {
        $this->pdo->exec('DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 1 DAY');
        $stmt=$this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email_hash=? AND ip_hash=? AND created_at >= NOW() - INTERVAL 1 HOUR");
        $stmt->execute([$this->emailHash('__registration__'),$this->ipHash()]);
        return (int)$stmt->fetchColumn()>=8;
    }
    public function recordRegistrationAttempt(): void {
        $stmt=$this->pdo->prepare('INSERT INTO login_attempts(email_hash,ip_hash) VALUES(?,?)');
        $stmt->execute([$this->emailHash('__registration__'),$this->ipHash()]);
    }
    public function updatePasswordHash(int $id,string $hash): void {
        $stmt=$this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?'); $stmt->execute([$hash,$id]);
    }
}
