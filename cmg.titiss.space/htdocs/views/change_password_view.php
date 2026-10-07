<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le mot de passe - MG&amp;M</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <main class="widget-container auth-container">
        <h2>Modifier le mot de passe</h2>

        <?php if (!empty($error)) { ?>
            <div class="error-msg" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if (!empty($success)) { ?>
            <div class="success-msg" role="status"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="index.php?action=change-password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe (min. 6 caractères)</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>
            <button type="submit" class="btn-primary">Enregistrer le nouveau mot de passe</button>
        </form>

        <div class="auth-footer">
            <a href="index.php">Retour au calculateur</a>
        </div>
    </main>
</body>
</html>
