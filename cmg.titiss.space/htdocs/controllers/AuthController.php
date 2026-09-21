<?php

require_once 'models/UserModel.php';

class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function login()
    {
        // Rediriger si déjà connecté
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');

            exit;
        }

        $error = '';
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!empty($username) && !empty($password)) {
                $user = $this->model->getUserByUsername($username);

                if ($user && password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    header('Location: index.php');

                    exit;
                }
                // Message générique pour éviter l'énumération des utilisateurs
                $error = 'Identifiants ou mot de passe incorrects.';
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        require 'views/login_view.php';
    }

    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php');

            exit;
        }

        $error = '';
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!empty($username) && !empty($password) && !empty($confirm_password)) {
                if (strlen($username) < 3 || strlen($username) > 50) {
                    $error = 'L\'identifiant doit contenir entre 3 et 50 caractères.';
                } elseif (strlen($password) < 6) {
                    $error = 'Le mot de passe doit contenir au moins 6 caractères.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Les mots de passe ne correspondent pas.';
                } else {
                    $existingUser = $this->model->getUserByUsername($username);
                    if (!$existingUser) {
                        if ($this->model->createUser($username, $password)) {
                            $user = $this->model->getUserByUsername($username);
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];

                            header('Location: index.php');

                            exit;
                        }
                        $error = 'Une erreur est survenue lors de l\'inscription.';
                    } else {
                        $error = 'Ce nom d\'utilisateur est déjà pris.';
                    }
                }
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        require 'views/register_view.php';
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        header('Location: index.php?action=login');

        exit;
    }
}
