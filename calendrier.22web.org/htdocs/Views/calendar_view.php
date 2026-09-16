<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Hebdomadaire (Style iOS)</title>
    <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        padding: 20px;
        color: #1c1c1e;
        background-color: #ffffff;
    }

    h2 {
        text-align: center;
        font-weight: 600;
        margin-bottom: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th {
        font-weight: 500;
        color: #8e8e93;
        padding-bottom: 15px;
        font-size: 14px;
        text-transform: capitalize;
    }

    /* Les cellules du tableau */
    td {
        border-top: 1px solid #e5e5ea;
        height: 60px;
        /* 1 pixel = 1 minute */
        vertical-align: top;
        padding: 0;
        /* Important: 0 pour que l'événement touche pile la ligne */
        position: relative;
        /* Indispensable pour le positionnement absolu de l'événement */
    }

    .time-col {
        width: 55px;
        border-top: none;
        text-align: right;
        padding-right: 15px;
        color: #8e8e93;
        font-size: 12px;
        font-weight: 400;
    }

    .time-text {
        position: relative;
        top: -8px;
        display: block;
    }

    .event-card {
        position: absolute;
        top: 0;
        left: 4px;
        right: 4px;

        /* Les couleurs ont été supprimées d'ici, elles seront gérées en ligne */
        border-left-width: 3px;
        border-left-style: solid;

        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        box-sizing: border-box;
        z-index: 10;
        overflow: hidden;
        line-height: 1.2;
        cursor: pointer;
    }

    .event-card:hover {
        background-color: rgba(0, 122, 255, 0.25);
        z-index: 20;
    }

    /* Ajout d'une ligne de séparation verticale douce entre les jours */
    th:not(.time-col),
    td:not(.time-col) {
        border-left: 1px solid #e5e5ea;
    }

    /* --- Design du bouton Switch iOS --- */
    .toggle-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
    }

    .toggle-label {
        margin-left: 12px;
        font-size: 14px;
        color: #8e8e93;
        font-weight: 500;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 28px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e5ea;
        /* Gris inactif */
        transition: .3s;
        border-radius: 28px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    input:checked+.slider {
        background-color: #34c759;
        /* Vert actif iOS */
    }

    input:checked+.slider:before {
        transform: translateX(18px);
    }

    /* --- Classe dynamique pour masquer les heures indisponibles --- */
    table.hide-unavailable tr.unavailable-hour {
        display: none;
    }

    /* --- Flèche dynamique entre les activités --- */
    .gap-arrow {
        position: absolute;
        left: 20px;
        /* Alignée proprement à l'intérieur de la case */
        width: 2px;
        /* Épaisseur de la ligne */
        background-color: #b7b4b4;
        /* Rouge vif iOS */
        z-index: 5;
        /* Reste en dessous des cartes d'entraînement */
        opacity: 0.6;
        /* Légère transparence pour la douceur visuelle */
    }

    /* Dessin de la pointe de la flèche en CSS pur */
    .gap-arrow::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: -4px;
        /* Centrage de la pointe sur la ligne (largeur de 10px / 2 = 5, moins la moitié de la ligne) */
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #b7b4b4;
        transform: translateY(100%);
    }

    /* --- Texte affiché à côté de la flèche --- */
    .gap-text {
        position: absolute;
        top: 50%;
        /* Centre le texte verticalement par rapport à la flèche */
        transform: translateY(-50%);
        left: 8px;
        /* Décale le texte légèrement à droite de la ligne rouge */

        background-color: rgba(255, 255, 255, 0.9);
        /* Fond blanc légèrement transparent */
        color: #b7b4b4;
        /* Même rouge que la flèche */
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
        /* Empêche le texte de passer à la ligne */
        z-index: 6;
        /* Au-dessus de la flèche, mais sous les cartes d'entraînement */
    }

    /* --- Gestion des affichages --- */
    /* Nuit absolue : on la masque complètement */
    table.hide-unavailable tr.night-hour {
        display: none;
    }

    /* Zone intermédiaire (heures creuses) : on la grise avec de légères hachures */
    table.hide-unavailable tr.gap-hour td {
        background-color: #f2f2f7;
        /* Gris très clair iOS */
        background-image: repeating-linear-gradient(45deg,
                transparent,
                transparent 10px,
                rgba(0, 0, 0, 0.03) 10px,
                rgba(0, 0, 0, 0.03) 20px);
    }

    /* On adoucit l'heure des zones grisées */
    table.hide-unavailable tr.gap-hour .time-col {
        opacity: 0.4;
    }
    </style>
</head>

<body>

    <h2>Planning Hebdomadaire</h2>

    <!-- Ajout du bouton Switch -->
    <div class="toggle-container">
        <label class="switch">
            <input type="checkbox" id="toggleAvailability" checked>
            <span class="slider"></span>
        </label>
        <span class="toggle-label">
            Limiter au temps disponible
            (<?php 
                $labels = [];
                foreach ($availabilityPeriods as $period) {
                    // On remplace le 30 par 00 pour afficher l'heure de fin exacte
                    $labels[] = sprintf('%02d:00', $period['start']) . ' - ' . sprintf('%02d:00', $period['end']);
                }
                echo implode(' & ', $labels);
            ?>)
        </span>
    </div>

    <table id="calendarTable" class="hide-unavailable">
        <thead>
            <tr>
                <th class="time-col"></th>
                <?php foreach ($days as $day): ?>
                <th><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hours as $hour): 
                $currentHour = (int)$hour;
                
                // 1. Est-ce la nuit absolue ? (AVANT le 1er bloc ou SUPÉRIEUR OU ÉGAL au dernier)
                // CHANGEMENT : on utilise >= pour que l'heure de fin globale bascule dans la nuit
                $isNight = ($currentHour < $globalStart || $currentHour >= $globalEnd);
                
                // 2. Est-ce une zone intermédiaire (creuse) ?
                $isGap = false;
                if (!$isNight) {
                    $isGap = true;
                    foreach ($availabilityPeriods as $period) {
                        // CHANGEMENT : $currentHour < $period['end'] (strictement inférieur = fin exclue)
                        if ($currentHour >= $period['start'] && $currentHour < $period['end']) {
                            $isGap = false; 
                            break; 
                        }
                    }
                }
            ?>
            <!-- Application dynamique des classes -->
            <tr class="<?= $isNight ? 'night-hour' : '' ?> <?= $isGap ? 'gap-hour' : '' ?>">
                <td class="time-col">
                    <span class="time-text">
                        <?= htmlspecialchars($currentHour . ':00', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>

                <?php foreach ($days as $day): ?>
                <td>
                    <?php if (isset($events[$day][$hour])): ?>
                    <?php foreach ($events[$day][$hour] as $evt): 
                                    $duration = (int)$evt['duration'];
                                    $offset = (int)$evt['offset'];
                                    $gap = (int)$evt['gap_to_next'];
                                    $color = htmlspecialchars($evt['color'], ENT_QUOTES, 'UTF-8');
                                ?>
                    <!-- Carte de l'entraînement -->
                    <div class="event-card" style="height: <?= $duration ?>px; 
                                                top: <?= $offset ?>px; 
                                                border-left-color: <?= $color ?>; 
                                                color: <?= $color ?>; 
                                                background-color: <?= $color ?>26;"
                        title="<?= htmlspecialchars($evt['title'] . ' (' . $duration . ' min)', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($evt['title'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <!-- Génération de la flèche s'il y a une activité après -->
                    <?php if ($gap > 0): 
                                        $gapH = floor($gap / 60);
                                        $gapM = $gap % 60;
                                        $gapText = $gapH > 0 ? $gapH . 'h' . sprintf('%02d', $gapM) : $gapM . ' min';
                                    ?>
                    <div class="gap-arrow" style="top: <?= $offset + $duration ?>px; height: <?= $gap ?>px;">
                        <!-- Le texte affiché à côté de la flèche -->
                        <span class="gap-text"><?= $gapText ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('toggleAvailability');
        const table = document.getElementById('calendarTable');

        toggle.addEventListener('change', function() {
            if (this.checked) {
                table.classList.add('hide-unavailable');
            } else {
                table.classList.remove('hide-unavailable');
            }
        });
    });
    </script>
</body>

</html>