<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier le mot de passe · Mon planning</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:#f4f7fb;color:#172033;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        main{width:min(100%,500px);padding:clamp(22px,5vw,38px);background:#fff;border:1px solid #e6eaf1;border-radius:20px;box-shadow:0 18px 55px rgba(25,45,80,.1)}
        a{color:#2563eb;text-decoration:none;font-weight:700;font-size:14px}h1{margin:24px 0 8px;font-size:28px;letter-spacing:-.04em}p{color:#697386;line-height:1.55}
        label{display:block;margin:17px 0 7px;font-size:13px;font-weight:700}input{width:100%;height:46px;padding:0 13px;border:1px solid #d8deea;border-radius:10px;font-size:15px}input:focus{outline:3px solid #dbeafe;border-color:#60a5fa}
        button{width:100%;height:48px;margin-top:22px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-size:15px;font-weight:750;cursor:pointer}button:hover{background:#1d4ed8}
        .message{padding:12px 14px;border-radius:10px}.error{color:#991b1b;background:#fef2f2;border:1px solid #fecaca}.success{color:#166534;background:#f0fdf4;border:1px solid #bbf7d0}
    </style>
</head>
<body>
    <main>
        <a href="index.php">← Retour au planning</a>
        <h1>Modifier le mot de passe</h1>
        <p>Confirmez votre mot de passe actuel, puis choisissez un nouveau mot de passe d’au moins 12 caractères.</p>
        <?php if ($errorMessage!==''): ?><p class="message error" role="alert"><?= htmlspecialchars($errorMessage,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
        <?php if ($successMessage!==''): ?><p class="message success" role="status"><?= htmlspecialchars($successMessage,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
        <form method="post" action="?auth=password" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
            <label for="current_password">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="1024" required>
            <label for="new_password">Nouveau mot de passe</label>
            <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="12" maxlength="1024" required>
            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="1024" required>
            <button type="submit">Enregistrer le nouveau mot de passe</button>
        </form>
    </main>
</body>
</html>
