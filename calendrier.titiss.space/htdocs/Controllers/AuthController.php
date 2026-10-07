<?php
declare(strict_types=1);
require_once __DIR__.'/../Models/AuthModel.php';

class AuthController {
    public function handle(): void {
        $mode=(string)($_GET['auth']??'login');
        if (currentUser() && $mode!=='logout') { header('Location: index.php'); exit; }
        if ($mode==='logout') {
            if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit('Méthode non autorisée.'); }
            requireValidCsrf(); logoutUser(); header('Location: index.php?auth=login'); exit;
        }
        if (!in_array($mode,['login','register'],true)) $mode='login';
        $errorMessage='';
        $model=new AuthModel();
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            requireValidCsrf();
            $email=trim((string)($_POST['email']??''));
            $password=(string)($_POST['password']??'');
            if ($mode==='register') {
                if ($model->registrationIsLimited()) {
                    $errorMessage='Trop de demandes de création de compte. Réessayez un peu plus tard.';
                } else {
                $model->recordRegistrationAttempt();
                $name=trim((string)($_POST['name']??''));
                $nameLength=preg_match_all('/./us',$name,$matches)?:0;
                $passwordLength=preg_match_all('/./us',$password,$matches)?:0;
                $algorithm=defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT;
                $maxPasswordBytes=$algorithm===PASSWORD_DEFAULT && PASSWORD_DEFAULT===PASSWORD_BCRYPT?72:1024;
                if ($name==='' || $nameLength>80 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || $passwordLength<12 || strlen($password)>$maxPasswordBytes) {
                    $errorMessage='Renseignez un nom et un courriel valides. Le mot de passe doit contenir au moins 12 caractères.';
                } else {
                    try {
                        $id=$model->register($name,$email,$password);
                        loginUser(['id'=>$id,'name'=>$name,'email'=>strtolower($email)]);
                        header('Location: index.php'); exit;
                    } catch (PDOException $e) {
                        if ((string)$e->getCode()==='23000') $errorMessage='Impossible de créer le compte avec ces informations.';
                        else throw $e;
                    }
                }
                }
            } else {
                if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen($password)>1024 || $model->loginIsLimited($email)) {
                    $errorMessage='Adresse e-mail ou mot de passe incorrect.';
                } else {
                    $user=$model->findUserByEmail($email);
                    $algorithm=defined('PASSWORD_ARGON2ID')?PASSWORD_ARGON2ID:PASSWORD_DEFAULT;
                    if ($user) {
                        $valid=password_verify($password,$user['password_hash']);
                    } else {
                        password_hash($password===''?'not-a-real-password':$password,$algorithm);
                        $valid=false;
                    }
                    if ($user && $valid) {
                        $model->clearLoginAttempts($email);
                        if (password_needs_rehash($user['password_hash'],$algorithm)) {
                            $model->updatePasswordHash((int)$user['id'],password_hash($password,$algorithm));
                        }
                        loginUser($user); header('Location: index.php'); exit;
                    }
                    $model->recordFailedLogin($email);
                    $errorMessage='Adresse e-mail ou mot de passe incorrect.';
                }
            }
        }
        require __DIR__.'/../Views/auth_view.php';
    }
}
