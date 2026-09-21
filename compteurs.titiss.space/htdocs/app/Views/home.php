<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Compteurs</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>

<body>

    <div id="current-time">Heure actuelle (FR) : Chargement...</div>

    <div class="cookie-warning">
        💡 <strong>Note :</strong> Vos dates sont sauvegardées de façon anonyme et liées à votre navigateur actuel. <br>
        Si vous videz vos cookies ou visitez ce site depuis un autre appareil, vos compteurs seront remis à zéro.
    </div>

    <div class="grid-container">
        <!-- Carte 1 : Countdown -->
        <div class="card">
            <h2>
                Compte à rebours
                <a href="<?= base_url('home/edit?type=countdown') ?>" class="edit-icon" title="Modifier">✏️</a>
            </h2>
            <div class="target-date">
                Cible :
                <?= (!empty($countdown['countdown_date']) && $countdown['countdown_date'] !== '0000-00-00 00:00:00') ? date('d/m/Y à H:i', strtotime($countdown['countdown_date'])) : 'Aucune' ?>
            </div>

            <div id="timer" class="timer-box">Chargement...</div>

            <?php if (!empty($countdown['countdown_date']) && $countdown['countdown_date'] !== '0000-00-00 00:00:00'): ?>
            <div class="progress-section">
                <div class="progress-container">
                    <div id="progress-bar" class="progress-bar"></div>
                </div>
                <div id="progress-text">0.00% restant</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Carte 2 : Passé -->
        <div class="card">
            <h2>
                Passé
                <a href="<?= base_url('home/edit?type=past') ?>" class="edit-icon" title="Modifier">✏️</a>
            </h2>
            <div class="target-date">
                Depuis le :
                <?= (!empty($countdown['past_date']) && $countdown['past_date'] !== '0000-00-00 00:00:00') ? date('d/m/Y à H:i', strtotime($countdown['past_date'])) : 'Aucune' ?>
            </div>

            <div id="elapsed-timer" class="timer-box">Chargement...</div>
        </div>

        <!-- Carte 3 : Progression de l'année -->
        <div class="card">
            <h2>Année en cours</h2>
            <div class="target-date" id="current-year-text">Chargement...</div>

            <div class="progress-section" style="width: 100%; margin-top: 30px;">
                <div class="progress-container" style="height: 30px;">
                    <div id="year-progress-bar" class="progress-bar"
                        style="background: linear-gradient(90deg, #ff7e5f, #feb47b); box-shadow: 0 0 10px rgba(255, 126, 95, 0.5);">
                    </div>
                </div>
                <div id="year-progress-text">0.00% restant</div>
            </div>
        </div>

        <!-- Carte 4 : Prochain Week-end -->
        <div class="card">
            <h2>Prochain Week-end</h2>
            <div class="target-date" id="weekend-date-text">Chargement...</div>
            <div id="weekend-timer" class="timer-box">Chargement...</div>
        </div>
    </div>

    <!-- On ignore les 0000-00-00 pour ne pas rendre JavaScript confus -->
    <script>
    window.timerData = {
        targetDate: "<?= (!empty($countdown['countdown_date']) && $countdown['countdown_date'] !== '0000-00-00 00:00:00') ? $countdown['countdown_date'] : '' ?>",
        pastDate: "<?= (!empty($countdown['past_date']) && $countdown['past_date'] !== '0000-00-00 00:00:00') ? $countdown['past_date'] : '' ?>"
    };
    </script>

    <script src="<?= base_url('assets/js/script.js') ?>"></script>

</body>

</html>