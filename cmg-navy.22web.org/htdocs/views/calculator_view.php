<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculateur de Masse Grasse & Métabolisme</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
    /* Les styles CSS intégrés restent identiques */
    .save-section {
        margin-top: 2.5rem;
        padding-top: 1.5rem;
        border-top: 1px dashed var(--border);
        text-align: center;
    }

    .save-section p {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .info-section {
        margin-top: 2.5rem;
        border-top: 1px dashed red;
        text-align: center;
    }

    .info-section p {
        font-size: 0.85rem;
        color: red;
        margin-bottom: -2rem;
    }

    .btn-save {
        width: 100%;
        padding: 1rem;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.3s, transform 0.1s;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
    }

    .btn-save:hover {
        background-color: #2563eb;
        transform: translateY(-1px);
    }

    .history-container {
        margin-top: 3rem;
        background: #ffffff;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .history-table th,
    .history-table td {
        border-bottom: 1px solid var(--border);
        padding: 10px 8px;
        text-align: center;
    }

    .history-table th {
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        position: sticky;
        top: 0;
        background-color: #ffffff;
        z-index: 10;
        border-bottom: 2px solid var(--border);
    }

    .table-wrapper {
        max-height: 500px;
        overflow-y: auto;
        margin-bottom: 1rem;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    </style>
</head>

<body>
    <div class="user-bar">
        <span>Connecté en tant que
            <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong></span>

        <!-- Formulaire d'importation CSV 
        <form action="index.php?action=import" method="POST" enctype="multipart/form-data"
            style="display:inline; margin-left:15px;">
            <input type="file" name="csv_file" accept=".csv" required style="display: none;" id="csv-upload"
                onchange="this.form.submit()">
            <label for="csv-upload" class="btn-export" style="background: var(--primary); cursor: pointer;">Importer
                CSV</label>
        </form>
        -->

        <a href="index.php?action=export" class="btn-export">Exporter CSV</a>
        <a href="index.php?action=logout" style="color: var(--danger);">Se déconnecter</a>
    </div>

    <div class="widget-container">
        <h2>Modèle Clinique DEXA-Like (CUN-BAE + RFM)</h2>
        <form id="metric-form" method="POST" action="index.php?action=save">
            <input type="hidden" name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id" id="edit_id" value="">

            <div class="form-group">
                <label for="created_at">Date de la mesure</label>
                <input type="date" id="created_at" name="created_at" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="gender">Genre</label>
                <select id="gender" name="gender">
                    <option value="male">Homme</option>
                    <option value="female">Femme</option>
                </select>
            </div>

            <?php
            $last_age = (!empty($history) && isset($history[0]['age'])) ? $history[0]['age'] : '25';
            $last_height = !empty($history) ? $history[0]['height'] : '170';
            $last_weight = !empty($history) ? $history[0]['weight'] : '75';
            $last_neck = !empty($history) ? $history[0]['neck'] : '37'; // Optionnel dans le calcul, conservé pour data
            $last_waist = !empty($history) ? $history[0]['waist'] : '79';
            $last_hip = (!empty($history) && null !== $history[0]['hip']) ? $history[0]['hip'] : '95';
            $last_wrist = (!empty($history) && isset($history[0]['wrist'])) ? $history[0]['wrist'] : '17.0';
            $last_calf = (!empty($history) && isset($history[0]['calf'])) ? $history[0]['calf'] : '38.0';
            $last_thigh = (!empty($history) && isset($history[0]['thigh'])) ? $history[0]['thigh'] : '55.0';
            $last_is_athlete = (!empty($history) && isset($history[0]['is_athlete']) && 1 == $history[0]['is_athlete']) ? 'checked' : '';
            ?>

            <div class="form-group">
                <label for="age">Âge</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="1" id="age" name="age" value="<?php echo $last_age; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="height">Taille (cm)</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="height" name="height" value="<?php echo $last_height; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="weight">Poids (kg)</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.1" id="weight" name="weight" value="<?php echo $last_weight; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="waist">Tour de taille (cm) - Au niveau du nombril</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="waist" name="waist" value="<?php echo $last_waist; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group" id="hip-group">
                <label for="hip">Tour de hanches (cm) - Point le plus large (Requis pour modèle Bailey)</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="hip" name="hip" value="<?php echo $last_hip; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="thigh">Tour de cuisse (cm) - Point le plus large</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="thigh" name="thigh" value="<?php echo $last_thigh; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="calf">Tour de mollet (cm) - Point le plus large</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="calf" name="calf" value="<?php echo $last_calf; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group">
                <label for="wrist">Tour de poignet (cm)</label>
                <div class="stepper">
                    <button type="button" class="minus">-</button>
                    <input type="number" step="0.5" id="wrist" name="wrist" value="<?php echo $last_wrist; ?>">
                    <button type="button" class="plus">+</button>
                </div>
            </div>

            <div class="form-group" style="display:none;">
                <label for="neck">Tour de cou (cm)</label>
                <input type="number" step="0.5" id="neck" name="neck" value="<?php echo $last_neck; ?>">
            </div>

            <div class="form-group checkbox-group"
                style="display: flex; align-items: center; gap: 10px; padding: 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                <input type="checkbox" id="is-athlete" name="is_athlete" value="1"
                    style="width: auto; transform: scale(1.3); cursor: pointer;" <?php echo $last_is_athlete; ?>>
                <label for="is-athlete" style="margin-bottom: 0; color: #1e3a8a; cursor: pointer;">
                    <strong>Profil Athlète Actif</strong><br>
                    <span style="font-size: 0.75rem; font-weight: normal; color: #475569;">
                        Applique une décote de -10 à -15% sur la graisse calculée par l'algorithme CUN-BAE (Corrige la
                        surévaluation due à l'IMC chez les profils très musclés).
                    </span>
                </label>
            </div>

            <div class="form-group">
                <label for="activity">Niveau d'activité hebdomadaire</label>
                <select id="activity" name="activity">
                    <option value="1.2">Sédentaire</option>
                    <option value="1.375">Légèrement actif (Sport 1 à 3 fois/semaine)</option>
                    <option value="1.55" selected>Modérément actif (Sport 5 fois/semaine)</option>
                    <option value="1.725">Très actif (Entraînement intense 6 à 7 jours/semaine)</option>
                    <option value="1.9">Extrêmement actif (Biquotidien)</option>
                </select>
            </div>

            <!-- Graphique Graisse Corporelle -->
            <div class="chart-container">
                <div class="zones">
                    <div class="zone" id="zone-bas" style="background-color: #d1d5db;">Bas</div>
                    <div class="zone" id="zone-ess" style="background-color: #b4c6e7;">Essentielle</div>
                    <div class="zone" id="zone-ath" style="background-color: #b5d3ba;">Athlètes</div>
                    <div class="zone" id="zone-fit" style="background-color: #eff6ff;">Fitness</div>
                    <div class="zone" id="zone-moy" style="background-color: #ffe699;">Moyen</div>
                    <div class="zone" id="zone-obe" style="background-color: #e6b8b7;">Obèse</div>
                </div>
                <div class="axis">
                    <span>0</span><span>5</span><span>10</span><span>15</span>
                    <span>20</span><span>25</span><span>30</span><span>35</span>
                    <span>40</span><span>45</span>
                </div>
                <div class="indicator" id="indicator">
                    <div class="indicator-value" id="indicator-value">--%</div>
                    <div class="indicator-line"></div>
                </div>
            </div>

            <div class="summary-container">
                <div class="summary-box">
                    <div class="summary-label">GRAISSE CORPORELLE</div>
                    <div class="summary-value-text" id="summary-bf">--%</div>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-box">
                    <div class="summary-label">CATÉGORIE</div>
                    <div class="summary-cat-text" id="summary-cat">--</div>
                </div>
            </div>

            <div class="info-section">
                <p>L'IMC est un indicateur purement statistique et indicatif.</p>
            </div>
            <div class="chart-container" style="margin-top: 3.5rem;">
                <div class="zones">
                    <div class="zone" style="background-color: #b4c6e7; width: 37.03%;">Maigreur</div>
                    <div class="zone" style="background-color: #b5d3ba; width: 22.22%;">Normal</div>
                    <div class="zone" style="background-color: #ffe699; width: 18.52%;">Surpoids</div>
                    <div class="zone" style="background-color: #e6b8b7; width: 22.22%;">Obèse</div>
                </div>
                <div class="axis">
                    <span>0</span><span>5</span><span>10</span><span>15</span>
                    <span>18.5</span><span>25</span><span>30</span><span>35</span><span>40</span>
                </div>
                <div class="axis-label">Indice de Masse Corporelle (IMC) &rarr;</div>
                <div class="indicator" id="imc-indicator">
                    <div class="indicator-value" id="imc-indicator-value">--</div>
                    <div class="indicator-line"></div>
                </div>
            </div>

            <div class="details-container">
                <div class="detail-box">
                    <div class="detail-label">MASSE GRASSE</div>
                    <div class="detail-value" id="detail-fat">-- kg</div>
                    <div class="detail-sub">Tissu adipeux pur</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">MASSE MAIGRE</div>
                    <div class="detail-value" id="detail-lean">-- kg</div>
                    <div class="detail-sub">Muscles, eau, os</div>
                </div>
            </div>

            <div class="energy-container">
                <div class="energy-box">
                    <div class="energy-label">MÉTABOLISME (BMR)</div>
                    <div class="energy-value" id="energy-bmr">-- kcal</div>
                </div>
                <div class="energy-box highlight-box">
                    <div class="energy-label">MAINTIEN CALORIQUE (TDEE)</div>
                    <div class="energy-value highlight-text" id="energy-tdee">-- kcal</div>
                </div>
            </div>

            <div class="macros-section" style="margin-top: 2rem;">
                <h4
                    style="text-align: center; color: var(--text-muted); text-transform: uppercase; font-size: 0.85rem;">
                    Répartition Macronutriments</h4>
                <select id="training-type" class="training-select"
                    style="margin-bottom: 1rem; width: 100%; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border);">
                    <option value="repos">Jour de repos</option>
                    <option value="force" selected>Séance Force (Hypertrophie)</option>
                    <option value="endurance">Séance Vitesse/Endurance (Monopalme, Course)</option>
                </select>
                <div class="macros-grid">
                    <div class="macro-box"><span class="macro-label">Protéines</span><strong
                            id="macro-protein">--g</strong>
                    </div>
                    <div class="macro-box"><span class="macro-label">Glucides</span><strong
                            id="macro-carbs">--g</strong>
                    </div>
                    <div class="macro-box"><span class="macro-label">Lipides</span><strong id="macro-fat">--g</strong>
                    </div>
                </div>
            </div>

            <div class="save-section">
                <button type="submit" class="btn-save">Enregistrer le relevé officiel</button>
            </div>
        </form>
    </div>

    <?php if (!empty($history)) { ?>
    <div class="widget-container history-container" style="max-width: 650px; margin-top: 2rem;">
        <h3 style="margin-top: 0; text-align: center; color: var(--text-main);">Historique des Mesures</h3>
        <div class="table-wrapper">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Poids</th>
                        <th>MG (%)</th>
                        <th>M. Maigre</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $row) { ?>
                    <tr>
                        <td><?php echo date('d/m/y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo $row['weight']; ?> kg</td>
                        <td style="color: var(--primary); font-weight: bold;"><?php echo $row['body_fat']; ?>%</td>
                        <td><?php echo $row['lean_mass']; ?> kg</td>
                        <td>
                            <button type="button" class="btn-edit" data-id="<?php echo $row['id']; ?>"
                                data-date="<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>"
                                data-gender="<?php echo $row['gender']; ?>" data-age="<?php echo $row['age'] ?? 25; ?>"
                                data-height="<?php echo $row['height']; ?>" data-weight="<?php echo $row['weight']; ?>"
                                data-waist="<?php echo $row['waist']; ?>" data-neck="<?php echo $row['neck']; ?>"
                                data-hip="<?php echo $row['hip'] ?? ''; ?>"
                                data-wrist="<?php echo $row['wrist'] ?? ''; ?>"
                                data-calf="<?php echo $row['calf'] ?? ''; ?>"
                                data-thigh="<?php echo $row['thigh'] ?? ''; ?>"
                                data-activity="<?php echo $row['activity_multiplier']; ?>"
                                data-athlete="<?php echo $row['is_athlete'] ?? 0; ?>"
                                style="color: var(--primary); background: none; border: none; cursor: pointer; font-size: 1.2rem; margin-right: 8px;"
                                title="Modifier">✎</button>
                            <button class="btn-delete" data-id="<?php echo $row['id']; ?>"
                                style="color: var(--danger); background: none; border: none; cursor: pointer; font-size: 1.2rem;"
                                title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border);">
            <canvas id="historyChart"></canvas>
        </div>
        <?php
        if (count($history) >= 2) {
            $current = $history[0];
            $first = end($history);
            $deltaWeight = $current['weight'] - $first['weight'];
            $deltaBF = $current['body_fat'] - $first['body_fat'];
            $deltaLean = $current['lean_mass'] - $first['lean_mass'];
            function formatDelta($val, $unit, $invertColors = false)
            {
                if (0 == $val) {
                    return "<span style='color: #64748b; font-weight: bold;'>= 0 {$unit}</span>";
                }
                $sign = $val > 0 ? '+' : '';
                $color = '#64748b';
                if ($val > 0) {
                    $color = $invertColors ? '#ef4444' : '#22c55e';
                }
                if ($val < 0) {
                    $color = $invertColors ? '#22c55e' : '#ef4444';
                }

                return "<span style='color: {$color}; font-weight: bold;'>".$sign.number_format($val, 2, ',', ' ')." {$unit}</span>";
            }
            ?>
        <div class="badges-container">
            <div class="badge">
                <div class="badge-title">Poids Total</div>
                <div class="badge-value"><?php echo formatDelta($deltaWeight, 'kg'); ?></div>
                <div class="badge-context">Masse Maigre + Masse Grasse</div>
            </div>
            <div class="badge">
                <div class="badge-title">Masse Grasse</div>
                <div class="badge-value"><?php echo formatDelta($deltaBF, '%', true); ?></div>
                <div class="badge-context">Tissus Adipeux</div>
            </div>
            <div class="badge">
                <div class="badge-title">Masse Maigre</div>
                <div class="badge-value"><?php echo formatDelta($deltaLean, 'kg'); ?></div>
                <div class="badge-context">Os, Muscles, Eau</div>
            </div>
        </div>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const historyData = <?php echo json_encode(array_reverse($history)); ?>;
    if (historyData.length > 0) {
        const labels = historyData.map(row => {
            const d = new Date(row.created_at);
            return d.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit'
            });
        });
        const weights = historyData.map(row => row.weight);
        const leanMasses = historyData.map(row => row.lean_mass);
        const bodyFats = historyData.map(row => row.body_fat);

        function calculateEMA(data, period = 3) {
            if (data.length === 0) return [];
            const k = 2 / (period + 1);
            let emaData = [data[0]];
            for (let i = 1; i < data.length; i++) {
                emaData.push((data[i] * k) + (emaData[i - 1] * (1 - k)));
            }
            return emaData;
        }
        const smoothedLeanMasses = calculateEMA(leanMasses);
        const smoothedBodyFats = calculateEMA(bodyFats);
        const ctx = document.getElementById('historyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Poids (kg)',
                        data: weights,
                        borderColor: '#64748b',
                        backgroundColor: 'rgba(100, 116, 139, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4
                    },
                    {
                        label: 'Masse Maigre (kg)',
                        data: smoothedLeanMasses,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        yAxisID: 'y',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Masse Grasse (%)',
                        data: smoothedBodyFats,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        yAxisID: 'y1',
                        tension: 0.4,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        suggestedMin: Math.min(...leanMasses) - 2,
                        suggestedMax: Math.max(...weights) + 2
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
    </script>
    <?php } ?>
    <script type="module" src="public/script.js"></script>
</body>

</html>