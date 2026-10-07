<?php
declare(strict_types=1);

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode','1');
    ini_set('session.use_only_cookies','1');
    ini_set('session.cookie_httponly','1');
    ini_set('session.gc_maxlifetime','1800');
    $https=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') || (($_SERVER['SERVER_PORT']??'')==='443');
    session_name('calendrier_session');
    session_set_cookie_params([
        'lifetime'=>0,'path'=>'/','secure'=>$https,'httponly'=>true,'samesite'=>'Lax'
    ]);
    session_start();
    if (!isset($_SESSION['created_at'])) $_SESSION['created_at']=time();
    if (time()-(int)$_SESSION['created_at']>900) {
        session_regenerate_id(true);
        $_SESSION['created_at']=time();
    }
    if (isset($_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity']>1800) {
        $_SESSION=[];
        session_regenerate_id(true);
    }
    $_SESSION['last_activity']=time();
}

function currentUser(): ?array {
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return (string)$_SESSION['csrf_token'];
}

function requireValidCsrf(): void {
    $token=(string)($_POST['csrf_token']??'');
    if ($token==='' || !hash_equals((string)($_SESSION['csrf_token']??''),$token)) {
        http_response_code(419);
        exit('La session du formulaire a expiré. Rechargez la page puis réessayez.');
    }
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user']=['id'=>(int)$user['id'],'name'=>(string)$user['name'],'email'=>(string)$user['email']];
    $_SESSION['created_at']=time();
    $_SESSION['last_activity']=time();
    $_SESSION['csrf_token']=bin2hex(random_bytes(32));
}

function logoutUser(): void {
    $_SESSION=[];
    if (ini_get('session.use_cookies')) {
        $params=session_get_cookie_params();
        setcookie(session_name(),'',[
            'expires'=>time()-42000,'path'=>$params['path'],'domain'=>$params['domain'],
            'secure'=>$params['secure'],'httponly'=>$params['httponly'],'samesite'=>'Lax'
        ]);
    }
    session_destroy();
}
