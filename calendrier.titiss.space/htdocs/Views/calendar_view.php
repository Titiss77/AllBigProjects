<?php
$dayMap = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
$todayString =$dayMap[(int)date('N')];
$eventCount = count($rawEvents);
$totalMinutes = array_sum(array_map(static fn($event) => (int)$event['duration'], $rawEvents));
$calendarPeriod = $calendar['start_date'] ? date('d/m/Y', strtotime($calendar['start_date'])) . ' – ' . date('d/m/Y', strtotime($calendar['end_date'])) : 'Sans limite de dates';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($calendar['name'], ENT_QUOTES, 'UTF-8') ?> · Planning</title>
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
    :root { color-scheme: light; --ink:#172033; --muted:#697386; --line:#e7ebf2; --blue:#2563eb; --surface:#fff; --canvas:#f4f7fb; }
    * { box-sizing:border-box; }
    body { margin:0; padding:32px clamp(14px,4vw,56px) 56px; color:var(--ink); background:var(--canvas); font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
    h2 { margin:8px 0 5px; text-align:left; font-size:clamp(25px,4vw,36px); letter-spacing:-.04em; }
    .page-subtitle { margin:0 0 24px; color:var(--muted); }
    .page-header,.calendar-panel,.manage-panel,.controls-container,.calendar-shell { max-width:1440px; margin-left:auto; margin-right:auto; }
    .calendar-panel { background:var(--surface); border:1px solid var(--line); border-radius:18px; padding:18px; box-shadow:0 10px 35px rgba(25,45,80,.05); margin-bottom:18px; }
    .calendar-picker-form { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .calendar-picker-form label { font-size:13px; color:var(--muted); font-weight:700; }
    .calendar-picker-form .form-control { max-width:440px; min-width:min(100%,280px); }
    .calendar-meta { color:var(--muted); font-size:13px; }
    .badge { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#eaf2ff; color:#1d4ed8; font-size:12px; font-weight:700; }
    .badge.archived { background:#eef0f3; color:#606a7a; }
    .manage-panel { padding:0 18px; background:var(--surface); border:1px solid var(--line); border-radius:16px; margin-bottom:18px; }
    .manage-panel summary { padding:16px 2px; cursor:pointer; font-weight:700; }
    .management-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; padding:0 0 18px; }
    .management-card { border:1px solid var(--line); border-radius:13px; padding:16px; }
    .management-card h3 { margin:0 0 12px; font-size:15px; }
    .management-card .form-group { margin-bottom:10px; }
    .btn-ios { background:var(--blue); padding:10px 15px; border-radius:9px; }
    .btn-ios:hover { background:#1d4ed8; }
    .btn-secondary { display:inline-block; padding:9px 13px; color:#334155; background:#eef2f7; border:0; border-radius:9px; font-weight:700; cursor:pointer; }
    .btn-danger { display:inline-block; padding:9px 13px; color:#b42318; background:#fff0ef; border:0; border-radius:9px; font-weight:700; cursor:pointer; }
    .stats-grid { max-width:1440px; margin:0 auto 18px; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .stat-card { background:white; border:1px solid var(--line); border-radius:14px; padding:15px 18px; }
    .stat-label { display:block; color:var(--muted); font-size:12px; margin-bottom:6px; }
    .stat-value { font-size:20px; font-weight:750; letter-spacing:-.02em; }
    .controls-container { padding:0 2px; }
    .calendar-shell { overflow:auto; background:white; border:1px solid var(--line); border-radius:16px; box-shadow:0 10px 35px rgba(25,45,80,.05); }
    #calendarTable { min-width:900px; }
    #calendarTable thead th { position:sticky; top:0; z-index:30; padding:14px 5px; background:#f9fafc; border-bottom:1px solid var(--line); }
    #calendarTable tbody tr { height:64px; }
    #calendarTable td { height:64px; }
    #calendarTable .time-col { position:sticky; left:0; z-index:24; background:#fff; }
    #calendarTable thead .time-col { z-index:35; background:#f9fafc; }
    .event-card { left:5px; right:5px; border-radius:8px; padding:7px 8px; font-size:12px; box-shadow:0 2px 7px rgba(20,30,50,.08); }
    .event-card[draggable="false"] { cursor:default; }
    th.current-day,td.current-day { background-color:#fff8f7; }
    .activity-item { gap:12px; }
    .activity-item form { flex-shrink:0; }
    .form-control { min-height:40px; border-color:#d8deea; border-radius:9px; }
    .form-control:focus { outline:3px solid #dbeafe; border-color:#60a5fa; }
    .modal-content { border-radius:18px; }
    .empty-state { padding:32px 14px; text-align:center; color:var(--muted); }
    .alert-error { padding:11px 13px; color:#991b1b; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; }
    @media (max-width:760px) {
        body { padding:22px 12px 36px; }
        .management-grid { grid-template-columns:1fr; }
        .stats-grid { grid-template-columns:1fr 1fr; }
        .calendar-panel { padding:14px; }
        .calendar-picker-form { align-items:stretch; }
        .calendar-picker-form .form-control { max-width:none; }
        .controls-container { gap:12px; align-items:flex-start; flex-direction:column; }
        .toggle-label { font-size:12px; }
    }
    </style>
</head>

<body>

    <header class="page-header">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
        <h2>Mon planning</h2>
        <p class="page-subtitle">Votre semaine en un coup d’œil. Déplacez les activités pour ajuster les horaires.</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;"><span class="calendar-meta">Bonjour, <?= htmlspecialchars((string)($_SESSION['user']['name']??''),ENT_QUOTES,'UTF-8') ?></span><form method="POST" action="?auth=logout" style="margin:0;"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>"><button class="btn-secondary" type="submit">Déconnexion</button></form></div>
        </div>
    </header>

    <section class="calendar-panel">
        <form class="calendar-picker-form" method="GET" action="">
            <label for="calendarSelect">CALENDRIER</label>
            <select id="calendarSelect" name="calendar" class="form-control" onchange="this.form.submit()">
                <?php foreach ($calendars as $item): ?>
                <option value="<?= (int)$item['id'] ?>" <?= (int)$item['id'] === $calendarId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?><?= (int)$item['is_global'] === 1 ? ' — global' : (($item['archived'] ? ' — archivé' : ' — ' . date('d/m/Y', strtotime($item['start_date'])) . ' au ' . date('d/m/Y', strtotime($item['end_date'])))) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;">
            <div><strong><?= htmlspecialchars($calendar['name'], ENT_QUOTES, 'UTF-8') ?></strong><div class="calendar-meta"><?= htmlspecialchars($calendarPeriod, ENT_QUOTES, 'UTF-8') ?></div></div>
            <span class="badge <?= $isArchived ? 'archived' : '' ?>"><?= $isArchived ? 'Archivé · lecture seule' : ((int)$calendar['is_global'] === 1 ? 'Calendrier global' : 'Calendrier actif') ?></span>
        </div>
        <?php if (isset($errorMessage)): ?><p class="alert-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    </section>

    <section class="stats-grid" aria-label="Résumé du calendrier">
        <div class="stat-card"><span class="stat-label">ACTIVITÉS PAR SEMAINE</span><span class="stat-value"><?= $eventCount ?></span></div>
        <div class="stat-card"><span class="stat-label">TEMPS PLANIFIÉ</span><span class="stat-value"><?= intdiv($totalMinutes,60) ?> h <?= sprintf('%02d', $totalMinutes % 60) ?></span></div>
        <div class="stat-card"><span class="stat-label">PÉRIODE</span><span class="stat-value" style="font-size:15px;"><?= htmlspecialchars($calendarPeriod, ENT_QUOTES, 'UTF-8') ?></span></div>
    </section>

    <details class="manage-panel" <?= isset($errorMessage) ? 'open' : '' ?>>
        <summary>Gérer les calendriers</summary>
        <div class="management-grid">
            <section class="management-card">
                <h3>Créer un calendrier</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="action" value="create_calendar">
                    <div class="form-group"><label>Nom</label><input class="form-control" name="name" maxlength="100" required placeholder="Ex. Saison 2026"></div>
                    <div class="form-group"><label>Date de début</label><input class="form-control" type="date" name="start_date" required></div>
                    <div class="form-group"><label>Date de fin</label><input class="form-control" type="date" name="end_date" required></div>
                    <button class="btn-ios" type="submit">Créer</button>
                </form>
            </section>
            <?php if ((int)$calendar['is_global'] !== 1): ?>
            <section class="management-card">
                <h3>Modifier ce calendrier</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="action" value="update_calendar"><input type="hidden" name="calendar_id" value="<?= $calendarId ?>">
                    <div class="form-group"><label>Nom</label><input class="form-control" name="name" maxlength="100" required value="<?= htmlspecialchars($calendar['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="form-group"><label>Date de début</label><input class="form-control" type="date" name="start_date" required value="<?= htmlspecialchars($calendar['start_date'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="form-group"><label>Date de fin</label><input class="form-control" type="date" name="end_date" required value="<?= htmlspecialchars($calendar['end_date'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <button class="btn-secondary" type="submit">Enregistrer</button>
                </form>
            </section>
            <?php endif; ?>
            <section class="management-card">
                <h3>Dupliquer ce planning</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="action" value="duplicate_calendar"><input type="hidden" name="calendar_id" value="<?= $calendarId ?>">
                    <div class="form-group"><label>Nom du nouveau calendrier</label><input class="form-control" name="name" maxlength="100" required value="Copie - <?= htmlspecialchars($calendar['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="form-group"><label>Date de début</label><input class="form-control" type="date" name="start_date" required></div>
                    <div class="form-group"><label>Date de fin</label><input class="form-control" type="date" name="end_date" required></div>
                    <button class="btn-secondary" type="submit">Dupliquer avec ses activités</button>
                </form>
            </section>
            <?php if ((int)$calendar['is_global'] !== 1): ?>
            <section class="management-card">
                <h3>Supprimer ce calendrier</h3>
                <p class="calendar-meta">Ses activités seront également supprimées.</p>
                <form method="POST" action="" onsubmit="return confirm('Supprimer ce calendrier et toutes ses activités ? Cette action est définitive.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete_calendar"><input type="hidden" name="calendar_id" value="<?= $calendarId ?>">
                    <button class="btn-danger" type="submit">Supprimer le calendrier</button>
                </form>
            </section>
            <?php endif; ?>
        </div>
    </details>

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
        <?php if (!$isArchived): ?><button class="btn-ios" id="openModalBtn">Gérer les activités</button><?php endif; ?>
    </div>

    <div class="calendar-shell">
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
                    <div class="event-card" draggable="<?= $isArchived ? 'false' : 'true' ?>" data-id="<?= $evt['id'] ?>"
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
    </div>

    <div class="modal-overlay" id="activityModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Gérer les activités</h3>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>

            <?php if (!$isArchived): ?><form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="calendar_id" value="<?= $calendarId ?>">

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
            </form><?php endif; ?>

            <div class="activity-list">
                <label
                    style="display: block; font-size: 12px; color: #8e8e93; margin-bottom: 10px; font-weight: 500;">Activités
                    enregistrées</label>
                <?php if (!$rawEvents): ?><p class="empty-state"><?= $isArchived ? 'Aucune activité enregistrée dans ce calendrier.' : 'Aucune activité pour le moment. Ajoutez votre première activité avec le formulaire ci-dessus.' ?></p><?php endif; ?>
                <?php foreach ($rawEvents as$event): ?>
                <div class="activity-item">
                    <div>
                        <strong><?= htmlspecialchars($event['day'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?= htmlspecialchars($event['start_time'], ENT_QUOTES, 'UTF-8') ?> -
                        <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= (int)$event['duration'] ?> min)
                    </div>
                    <?php if (!$isArchived): ?><form method="POST" action="" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="calendar_id" value="<?= $calendarId ?>">
                        <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
                        <button type="submit" class="btn-delete"
                            onclick="return confirm('Supprimer cet entraînement ?');">Supprimer</button>
                    </form><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const toggle = document.getElementById('toggleAvailability');
        const table = document.getElementById('calendarTable');
        const availabilityPreference = localStorage.getItem('calendar-hide-unavailable');
        if (availabilityPreference === 'false') { toggle.checked = false; table.classList.remove('hide-unavailable'); }
        toggle.addEventListener('change', function() {
            if (this.checked) table.classList.add('hide-unavailable');
            else table.classList.remove('hide-unavailable');
            localStorage.setItem('calendar-hide-unavailable', String(this.checked));
        });

        const modal = document.getElementById('activityModal');
        const openModalBtn = document.getElementById('openModalBtn');
        if (openModalBtn) openModalBtn.addEventListener('click', () => modal.style.display = 'flex');
        document.getElementById('closeModalBtn').addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        const titleColorMapping =
            <?= json_encode($titleColorMapping, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const titleInput = document.getElementById('titleInput');
        if (titleInput) titleInput.addEventListener('input', function() {
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
                    formData.append('csrf_token', '<?= htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8') ?>');
                    formData.append('calendar_id', '<?= $calendarId ?>');
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
