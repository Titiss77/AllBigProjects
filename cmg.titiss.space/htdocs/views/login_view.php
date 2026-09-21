<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MG&M</title>
    <link rel="stylesheet" href="public/style.css">
</head>

<body>
    <div class="widget-container auth-container">
        <h2>Connexion</h2>

        <?php if (!empty($error)) { ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="index.php?action=login">
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>

        <div class="auth-footer">
            Pas encore de compte ? <a href="index.php?action=register">Créer un compte</a>
        </div>
    </div>
</body>

</html>