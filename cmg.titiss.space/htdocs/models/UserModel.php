<?php

require_once 'config.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit('Erreur de connexion : '.$e->getMessage());
        }
    }

    public function getUserByUsername($username)
    {
        $sql = 'SELECT * FROM users WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare('SELECT id, username, password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');

        return $stmt->execute([':password_hash' => $passwordHash, ':id' => $id]);
    }

    // AJOUT : Méthode pour insérer un nouvel utilisateur
    public function createUser($username, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':username' => $username,
            ':password_hash' => $passwordHash,
        ]);
    }
}
