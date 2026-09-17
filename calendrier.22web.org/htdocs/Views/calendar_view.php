<?php
$dayMap = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
$todayString =$dayMap[(int)date('N')];
?>
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

    td {
        border-top: 1px solid #e5e5ea;
        height: 60px;
        vertical-align: top;
        padding: 0;
        position: relative;
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
        cursor: grab;
        transition: opacity 0.2s;
    }

    .event-card:active {
        cursor: grabbing;
    }

    .event-card.dragging {
        opacity: 0.5;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .event-card:hover {
        filter: brightness(0.95);
        z-index: 20;
    }

    th:not(.time-col),
    td:not(.time-col) {
        border-left: 1px solid #e5e5ea;
    }

    th.current-day,
    td.current-day {
        border-left: 2px solid #ff3b30 !important;
    }

    .controls-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 800px;
        margin: 0 auto 25px auto;
    }

    .toggle-container {
        display: flex;
        align-items: center;
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
    }

    input:checked+.slider:before {
        transform: translateX(18px);
    }

    .btn-ios {
        background-color: #007aff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-ios:hover {
        background-color: #005bb5;
    }

    .gap-arrow {
        position: absolute;
        left: 20px;
        width: 2px;
        background-color: #b7b4b4;
        z-index: 5;
        opacity: 0.6;
        pointer-events: none;
    }

    .gap-arrow::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: -4px;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #b7b4b4;
        transform: translateY(100%);
    }

    .gap-text {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 8px;
        background-color: rgba(255, 255, 255, 0.9);
        color: #b7b4b4;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
        z-index: 6;
    }

    table.hide-unavailable tr.night-hour {
        display: none;
    }

    table.hide-unavailable tr.gap-hour td {
        background-color: #f2f2f7;
        background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0, 0, 0, 0.03) 10px, rgba(0, 0, 0, 0.03) 20px);
    }

    table.hide-unavailable tr.gap-hour .time-col {
        opacity: 0.4;
    }

    td.drag-over {
        background-color: rgba(0, 122, 255, 0.05);
        background-image: linear-gradient(to bottom, rgba(0, 122, 255, 0.2) 1px, transparent 1px);
        background-size: 100% 15px;
    }

    .snap-placeholder {
        position: absolute;
        left: 4px;
        right: 4px;
        background-color: rgba(0, 122, 255, 0.05);
        border: 2px dashed #007aff;
        border-radius: 4px;
        pointer-events: none;
        z-index: 15;
        display: none;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        border-radius: 12px;
        width: 500px;
        max-width: 90%;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        max-height: 85vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #e5e5ea;
        padding-bottom: 10px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        color: #8e8e93;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        color: #8e8e93;
        margin-bottom: 4px;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #d1d1d6;
        border-radius: 6px;
        box-sizing: border-box;
        font-size: 14px;
    }

    .color-picker {
        padding: 2px;
        width: 100%;
        height: 36px;
        border: 1px solid #d1d1d6;
        border-radius: 6px;
        cursor: pointer;
    }

    .activity-list {
        margin-top: 25px;
        border-top: 1px solid #e5e5ea;
        padding-top: 15px;
    }

    .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #f2f2f7;
        font-size: 13px;
    }

    .activity-item strong {
        margin-right: 10px;
    }

    .btn-delete {
        background-color: #ff3b30;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
    }
    </style>
</head>

<body>

    <h2>Planning Hebdomadaire</h2>

    <div class="controls-container">
        <div class="toggle-container">
            <label class="switch">
                <input type="checkbox" id="toggleAvailability" checked>
                <span class="slider"></span>
            </label>
            <span class="toggle-label">
                Limiter au temps dispo
                (<?php 
                    $labels = [];
                    foreach ($availabilityPeriods as $period) {$labels[] = sprintf('%02d:00', $period['start']) . ' - ' . sprintf('\%02d:00', $period['end']);
                    }
                    echo implode(' & ', $labels);
                ?>)
            </span>
        </div>
        <button class="btn-ios" id="openModalBtn">Gérer les activités</button>
    </div>

    <table id="calendarTable" class="hide-unavailable">
        <thead>
            <tr>
                <th class="time-col"></th>
                <?php foreach ($days as$day): ?>
                <th class="<?= ($day ===$todayString) ? 'current-day' : '' ?>">
                    <?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hours as$hour): 
                $currentHour = (int)$hour;
                $isNight = ($currentHour < $globalStart || $currentHour >= $globalEnd);
                if (!$isNight) {$isGap = true;
                    foreach ($availabilityPeriods as$period) {
                        if ($currentHour >= $period['start'] &&$currentHour < $period['end']) {$isGap = false; break; 
                        }
                    }
                }
            ?>
            <tr class="<?= $isNight ? 'night-hour' : '' ?> <?= $isGap ? 'gap-hour' : '' ?>">
                <td class="time-col"><span
                        class="time-text"><?= htmlspecialchars($currentHour . ':00', ENT_QUOTES, 'UTF-8') ?></span></td>

                <?php foreach ($days as$day): ?>
                <td data-day="<?= $day ?>" data-hour="<?= $currentHour ?>"
                    class="<?= ($day ===$todayString) ? 'current-day' : '' ?>">
                    <?php if (isset($events[$day][$hour])): ?>
                    <?php foreach ($events[$day][$hour] as$evt): 
                            $duration = (int)$evt['duration'];
                            $offset = (int)$evt['offset'];
                            $gap = (int)$evt['gap_to_next'];
                            $color = htmlspecialchars($evt['color'], ENT_QUOTES, 'UTF-8');
                        ?>
                    <div class="event-card" draggable="true" data-id="<?= $evt['id'] ?>"
                        style="height: <?= $duration ?>px; top: <?= $offset ?>px; border-left-color: <?= $color ?>; color: <?= $color ?>; background-color: <?= $color ?>26;"
                        title="<?= htmlspecialchars($evt['title'] . ' (' .$duration . ' min)', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($evt['title'], ENT_QUOTES, 'UTF-8') ?>
                        <?= htmlspecialchars($evt['start_time'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php if ($gap > 0): 
                            $gapH = floor($gap / 60);
                            $gapM =$gap % 60;
                            $gapText = $gapH > 0 ?$gapH . 'h' . sprintf('%02d', $gapM) :$gapM . ' min';
                        ?>
                    <div class="gap-arrow" style="top: <?= $offset + $duration ?>px; height: <?= $gap ?>px;">
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

    <div class="modal-overlay" id="activityModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Gérer les activités</h3>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label>Titre de l'activité</label>
                    <input type="text" name="title" id="titleInput" class="form-control" required
                        placeholder="Ex: Natation, Salle..." list="titleSuggestions" autocomplete="off">
                    <datalist id="titleSuggestions">
                        <?php foreach ($titleColorMapping as $title =>$color): ?>
                        <option value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endforeach; ?>
                    </datalist>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Jour</label>
                        <select name="day" class="form-control" required>
                            <?php foreach ($days as$day): ?>
                            <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Heure de début</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Durée (minutes)</label>
                        <input type="number" name="duration" class="form-control" value="60" required min="1">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Couleur</label>
                        <input type="color" name="color" id="colorInput" class="color-picker" value="#007aff">
                    </div>
                </div>

                <button type="submit" class="btn-ios" style="width: 100%; margin-top: 10px;">Ajouter au
                    planning</button>
            </form>

            <div class="activity-list">
                <label
                    style="display: block; font-size: 12px; color: #8e8e93; margin-bottom: 10px; font-weight: 500;">Activités
                    enregistrées</label>
                <?php foreach ($rawEvents as$event): ?>
                <div class="activity-item">
                    <div>
                        <strong><?= htmlspecialchars($event['day'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?= htmlspecialchars($event['start_time'], ENT_QUOTES, 'UTF-8') ?> -
                        <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= (int)$event['duration'] ?> min)
                    </div>
                    <form method="POST" action="" style="margin: 0;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
                        <button type="submit" class="btn-delete"
                            onclick="return confirm('Supprimer cet entraînement ?');">Supprimer</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const toggle = document.getElementById('toggleAvailability');
        const table = document.getElementById('calendarTable');

        toggle.addEventListener('change', function() {
            if (this.checked) table.classList.add('hide-unavailable');
            else table.classList.remove('hide-unavailable');
        });

        const modal = document.getElementById('activityModal');
        document.getElementById('openModalBtn').addEventListener('click', () => modal.style.display = 'flex');
        document.getElementById('closeModalBtn').addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        const titleColorMapping =
            <?= json_encode($titleColorMapping, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        document.getElementById('titleInput').addEventListener('input', function() {
            if (titleColorMapping[this.value.trim()]) {
                document.getElementById('colorInput').value = titleColorMapping[this.value.trim()];
            }
        });

        // --- ENCAPSULATION DU DRAG AND DROP POUR POUVOIR LE RELANCER ---
        function initDragAndDrop() {
            const cards = document.querySelectorAll('.event-card');
            const cells = document.querySelectorAll('td[data-day]');

            let placeholder = document.querySelector('.snap-placeholder');
            if (!placeholder) {
                placeholder = document.createElement('div');
                placeholder.className = 'snap-placeholder';
            }

            let dragOffsetY = 0;
            let dragHeight = 60;

            cards.forEach(card => {
                card.addEventListener('dragstart', (e) => {
                    const rect = card.getBoundingClientRect();
                    dragOffsetY = e.clientY - rect.top;
                    dragHeight = parseInt(card.style.height) || 60;

                    e.dataTransfer.setData('text/plain', card.dataset.id);
                    e.dataTransfer.setData('offsetY', dragOffsetY);

                    placeholder.style.height = dragHeight + 'px';
                    setTimeout(() => card.classList.add('dragging'), 0);
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging');
                    placeholder.style.display = 'none';
                    if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
                });
            });

            cells.forEach(cell => {
                cell.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    cell.classList.add('drag-over');

                    if (placeholder.parentNode !== cell) {
                        cell.appendChild(placeholder);
                    }
                    placeholder.style.display = 'block';

                    const baseHour = parseInt(cell.dataset.hour);
                    const rect = cell.getBoundingClientRect();
                    const y = (e.clientY - rect.top) - dragOffsetY;

                    let totalMinutes = (baseHour * 60) + Math.floor(y);
                    let newHour = Math.floor(totalMinutes / 60);
                    let newMinutes = totalMinutes % 60;

                    newMinutes = Math.round(newMinutes / 15) * 15;
                    if (newMinutes === 60) {
                        newHour += 1;
                        newMinutes = 0;
                    }

                    let displayTop = ((newHour - baseHour) * 60) + newMinutes;
                    placeholder.style.top = displayTop + 'px';
                });

                cell.addEventListener('dragleave', (e) => {
                    if (!cell.contains(e.relatedTarget)) {
                        cell.classList.remove('drag-over');
                    }
                });

                cell.addEventListener('drop', (e) => {
                    e.preventDefault();
                    cell.classList.remove('drag-over');

                    const targetCell = e.target.closest('td[data-day]');
                    if (!targetCell) return;

                    const eventId = e.dataTransfer.getData('text/plain');
                    const offsetY = parseFloat(e.dataTransfer.getData('offsetY')) || 0;

                    const targetDay = targetCell.dataset.day;
                    const baseHour = parseInt(targetCell.dataset.hour);

                    const rect = targetCell.getBoundingClientRect();
                    const y = (e.clientY - rect.top) - offsetY;

                    let totalMinutes = (baseHour * 60) + Math.floor(y);
                    let newHour = Math.floor(totalMinutes / 60);
                    let newMinutes = totalMinutes % 60;

                    newMinutes = Math.round(newMinutes / 15) * 15;
                    if (newMinutes === 60) {
                        newHour += 1;
                        newMinutes = 0;
                    }

                    if (newHour < 0) newHour = 0;
                    if (newHour > 23) newHour = 23;

                    const hourStr = String(newHour).padStart(2, '0');
                    const minStr = String(newMinutes).padStart(2, '0');
                    const newTime = `${hourStr}:${minStr}`;

                    // Masque la carte déplacée pour faire "plus propre" pendant la courte requête
                    const draggedCard = document.querySelector(
                        `.event-card[data-id="${eventId}"]`);
                    if (draggedCard) draggedCard.style.opacity = '0.3';

                    const formData = new FormData();
                    formData.append('action', 'move');
                    formData.append('id', eventId);
                    formData.append('day', targetDay);
                    formData.append('time', newTime);

                    fetch('', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // C'EST ICI QUE TOUT CHANGE : On récupère silencieusement le tableau en arrière plan
                                fetch(window.location.href)
                                    .then(res => res.text())
                                    .then(html => {
                                        const parser = new DOMParser();
                                        const doc = parser.parseFromString(html,
                                            'text/html');

                                        // On remplace uniquement l'intérieur du tableau, sans recharger la page
                                        document.querySelector('#calendarTable tbody')
                                            .innerHTML = doc.querySelector(
                                                '#calendarTable tbody').innerHTML;

                                        // On relance la fonction pour attacher les événements de déplacement aux nouvelles cartes
                                        initDragAndDrop();
                                    });
                            } else {
                                alert("Erreur lors du déplacement : " + data.error);
                                if (draggedCard) draggedCard.style.opacity = '1';
                            }
                        })
                        .catch(err => {
                            alert("Erreur réseau : " + err);
                            if (draggedCard) draggedCard.style.opacity = '1';
                        });
                });
            });
        }

        // On lance le Drag and Drop une première fois au chargement
        initDragAndDrop();
    });
    </script>
</body>

</html>