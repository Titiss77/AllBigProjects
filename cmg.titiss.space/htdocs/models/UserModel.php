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
