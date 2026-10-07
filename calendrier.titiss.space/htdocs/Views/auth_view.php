<?php $isRegister=$mode==='register'; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= $isRegister?'Créer un compte':'Connexion' ?> · Mon planning</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(145deg,#f5f8ff,#eef2f8);color:#172033;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        .auth-card{width:min(100%,440px);padding:clamp(24px,6vw,42px);background:#fff;border:1px solid #e6eaf1;border-radius:22px;box-shadow:0 22px 70px rgba(25,45,80,.12)}
        .brand{display:inline-flex;align-items:center;gap:10px;margin-bottom:28px;color:#2563eb;font-size:14px;font-weight:800;letter-spacing:.02em}.brand-icon{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#eaf2ff;font-size:19px}
        h1{margin:0 0 8px;font-size:28px;letter-spacing:-.04em}.lead{margin:0 0 24px;color:#697386;line-height:1.55}
        .field{margin:16px 0}.field label{display:block;margin-bottom:7px;font-size:13px;font-weight:700}.field input{width:100%;height:46px;padding:0 13px;border:1px solid #d8deea;border-radius:10px;background:white;color:#172033;font-size:15px}.field input:focus{outline:3px solid #dbeafe;border-color:#60a5fa}
        .submit{width:100%;min-height:48px;margin-top:8px;border:0;border-radius:10px;background:#2563eb;color:white;font-weight:750;font-size:15px;cursor:pointer}.submit:hover{background:#1d4ed8}
        .error{padding:12px 14px;border:1px solid #fecaca;border-radius:10px;background:#fef2f2;color:#991b1b;font-size:14px;line-height:1.45}.switch{margin:22px 0 0;text-align:center;color:#697386;font-size:14px}.switch a{color:#1d4ed8;font-weight:700;text-decoration:none}.switch a:hover{text-decoration:underline}
        .privacy{margin:22px 0 0;color:#8a94a6;font-size:12px;line-height:1.5;text-align:center}
    </style>
</head>
<body>
    <main class="auth-card">
        <div class="brand"><span class="brand-icon" aria-hidden="true">▦</span>MON PLANNING</div>
        <h1><?= $isRegister?'Créer votre compte':'Content de vous revoir' ?></h1>
        <p class="lead"><?= $isRegister?'Créez votre espace personnel pour organiser vos calendriers.':'Connectez-vous pour retrouver vos calendriers.' ?></p>
        <?php if ($errorMessage!==''): ?><p class="error" role="alert"><?= htmlspecialchars($errorMessage,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
        <form method="post" action="?auth=<?= $isRegister?'register':'login' ?>" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
            <?php if ($isRegister): ?><div class="field"><label for="name">Nom affiché</label><input id="name" name="name" type="text" maxlength="80" autocomplete="name" required value="<?= htmlspecialchars((string)($_POST['name']??''),ENT_QUOTES,'UTF-8') ?>"></div><?php endif; ?>
            <div class="field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= htmlspecialchars((string)($_POST['email']??''),ENT_QUOTES,'UTF-8') ?>"></div>
            <div class="field"><label for="password">Mot de passe</label><input id="password" name="password" type="password" <?= $isRegister?'minlength="12" maxlength="1024" autocomplete="new-password"':'maxlength="1024" autocomplete="current-password"' ?> required><?php if($isRegister): ?><small style="display:block;margin-top:6px;color:#697386;">12 caractères minimum.</small><?php endif; ?></div>
            <button class="submit" type="submit"><?= $isRegister?'Créer mon compte':'Se connecter' ?></button>
        </form>
        <p class="switch"><?= $isRegister?'Vous avez déjà un compte ?':'Pas encore de compte ?' ?> <a href="?auth=<?= $isRegister?'login':'register' ?>"><?= $isRegister?'Se connecter':'Créer un compte' ?></a></p>
        <p class="privacy">Vos calendriers sont privés et rattachés à votre compte.</p>
    </main>
</body>
</html>
