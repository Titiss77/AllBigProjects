<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une date</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>

<body>

    <h1>Paramètres du compteur</h1>
    <a href="<?= base_url('/') ?>" class="back-link">⬅ Retour au Dashboard</a>

    <div class="edit-container">

        <?php if ($type === 'countdown'): ?>
        <div class="card" style="width: 100%;">
            <h2>Modifier : Compte à rebours</h2>
            <form action="<?= base_url('home/update'); ?>" method="post">
                <div class="form-group">
                    <label>Nouvelle date cible :</label>
                    <input type="datetime-local" name="countdown_date"
                        value="<?= (!empty($countdown['countdown_date']) && $countdown['countdown_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d\TH:i', strtotime($countdown['countdown_date'])) : '' ?>">
                </div>
                <button type="submit">Enregistrer</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($type === 'past'): ?>
        <div class="card" style="width: 100%;">
            <h2>Modifier : Passé</h2>
            <form action="<?= base_url('home/update'); ?>" method="post">
                <div class="form-group">
                    <label>Nouvelle date de départ :</label>
                    <input type="datetime-local" name="past_date"
                        value="<?= (!empty($countdown['past_date']) && $countdown['past_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d\TH:i', strtotime($countdown['past_date'])) : '' ?>">
                </div>
                <button type="submit">Enregistrer</button>
            </form>
        </div>
        <?php endif; ?>

    </div>

</body>

</html>