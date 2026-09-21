<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - MG&M</title>
    <link rel="stylesheet" href="public/style.css">
</head>

<body>
    <div class="widget-container auth-container">
        <h2>Inscription</h2>

        <?php if (!empty($error)) { ?>
        <div class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="index.php?action=register">
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe (min. 6 caractères)</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    autocomplete="new-password">
            </div>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>

        <div class="auth-footer">
            Déjà un compte ? <a href="index.php?action=login">Se connecter</a>
        </div>
    </div>
</body>

</html>