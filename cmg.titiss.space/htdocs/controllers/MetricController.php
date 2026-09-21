<?php

require_once 'models/MetricModel.php';

class MetricController
{
    private $model;

    public function __construct()
    {
        $this->model = new MetricModel();
    }

    public function index()
    {
        $id_user = $_SESSION['user_id'];
        $history = $this->model->getAllHistory($id_user);
        require 'views/calculator_view.php';
    }

    public function save()
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (!isset($_SESSION['user_id'])) {
                $this->sendJson(['success' => false, 'message' => 'Non authentifié.']);
            }
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $this->sendJson(['success' => false, 'message' => 'Token de sécurité invalide.']);
            }

            $id = isset($_POST['id']) && !empty($_POST['id']) ? (int) $_POST['id'] : null;
            $id_user = $_SESSION['user_id'];
            $gender = $_POST['gender'] ?? 'male';
            
            $age = (int) ($_POST['age'] ?? 25);
            $height = (float) str_replace(',', '.', $_POST['height'] ?? 0);
            $weight = (float) str_replace(',', '.', $_POST['weight'] ?? 0);
            $neck = (float) str_replace(',', '.', $_POST['neck'] ?? 0);
            $waist = (float) str_replace(',', '.', $_POST['waist'] ?? 0);
            $hip = (float) str_replace(',', '.', $_POST['hip'] ?? 0);
            $wrist = (float) str_replace(',', '.', $_POST['wrist'] ?? 0);
            $calf = (float) str_replace(',', '.', $_POST['calf'] ?? 0);
            $thigh = (float) str_replace(',', '.', $_POST['thigh'] ?? 0);
            
            $activity = (float) ($_POST['activity'] ?? 1.2);
            $isAthlete = isset($_POST['is_athlete']) && '1' == $_POST['is_athlete'];

            $createdAt = isset($_POST['created_at']) && !empty($_POST['created_at'])
                ? $_POST['created_at'].' '.date('H:i:s')
                : date('Y-m-d H:i:s');

            if ($height <= 0 || $waist <= 0 || $age <= 0) {
                $this->sendJson(['success' => false, 'message' => 'Mensurations invalides ou incomplètes.']);
            }

            // Récupération des données calculées via la méthode privée
            $metrics = $this->computeMetrics($gender, $age, $height, $weight, $waist, $hip, $wrist, $calf, $thigh, $isAthlete, $activity);

            $data = [
                ':id_user' => $id_user,
                ':gender' => $gender,
                ':age' => $age,
                ':height' => $height,
                ':weight' => $weight,
                ':neck' => $neck,
                ':waist' => $waist,
                ':hip' => $hip,
                ':wrist' => $wrist,
                ':calf' => $calf,
                ':thigh' => $thigh,
                ':activity' => $activity,
                ':is_athlete' => $isAthlete ? 1 : 0,
                ':body_fat' => $metrics['body_fat'],
                ':fat_mass' => $metrics['fat_mass'],
                ':lean_mass' => $metrics['lean_mass'],
                ':bmr' => $metrics['bmr'],
                ':tdee' => $metrics['tdee'],
                ':created_at' => $createdAt,
            ];

            if ($id) {
                $data[':id'] = $id;
                if ($this->model->updateMetric($data)) {
                    $this->sendJson(['success' => true, 'message' => 'Mesure mise à jour.']);
                } else {
                    $this->sendJson(['success' => false, 'message' => 'Erreur SQL lors de la mise à jour.']);
                }
            } else {
                if ($this->model->insertMetric($data)) {
                    $this->sendJson(['success' => true, 'message' => 'Mesure sauvegardée avec succès.']);
                } else {
                    $this->sendJson(['success' => false, 'message' => 'Erreur SQL lors de la sauvegarde.']);
                }
            }
        }
    }

    public function exportCSV()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $id_user = $_SESSION['user_id'];
        $history = $this->model->getAllHistory($id_user);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=historique_metriques.csv');
        $output = fopen('php://output', 'w');

        // EXPORT STRICT DES DONNÉES UTILISATEUR UNIQUEMENT (13 colonnes)
        fputcsv($output, ['created_at', 'gender', 'age', 'height', 'weight', 'waist', 'neck', 'hip', 'wrist', 'calf', 'thigh', 'activity_multiplier', 'is_athlete'], ';');

        foreach ($history as $row) {
            fputcsv($output, [
                $row['created_at'], $row['gender'], $row['age'], $row['height'], $row['weight'],
                $row['waist'], $row['neck'], $row['hip'], $row['wrist'], $row['calf'], $row['thigh'],
                $row['activity_multiplier'], $row['is_athlete']
            ], ';');
        }
        fclose($output);
        exit;
    }

    public function importCSV()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $id_user = $_SESSION['user_id'];

            if (!empty($file) && ($handle = fopen($file, 'r')) !== false) {
                fgetcsv($handle, 1000, ';'); // Ignore l'en-tête

                while (($dataRow = fgetcsv($handle, 1000, ';')) !== false) {
                    // On s'attend à 13 colonnes minimum selon le nouvel export
                    if (count($dataRow) < 13) {
                        continue;
                    }

                    $rawDate = trim($dataRow[0]);
                    
                    // On remplace les '/' par des '-' pour que strtotime() comprenne 
                    // qu'il s'agit du format européen (JJ-MM-AAAA) et non américain
                    $cleanDate = str_replace('/', '-', $rawDate);
                    
                    // On force le format strict attendu par MySQL
                    $createdAt = date('Y-m-d H:i:s', strtotime($cleanDate));
                    
                    $gender = $dataRow[1];
                    $age = (int) $dataRow[2];
                    $height = (float) $dataRow[3];
                    $weight = (float) $dataRow[4];
                    $waist = (float) $dataRow[5];
                    $neck = (float) $dataRow[6];
                    $hip = empty($dataRow[7]) ? 0 : (float) $dataRow[7];
                    $wrist = empty($dataRow[8]) ? 0 : (float) $dataRow[8];
                    $calf = empty($dataRow[9]) ? 0 : (float) $dataRow[9];
                    $thigh = empty($dataRow[10]) ? 0 : (float) $dataRow[10];
                    $activity = (float) $dataRow[11];
                    $isAthlete = (int) $dataRow[12];

                    if ($height <= 0 || $waist <= 0 || $age <= 0) {
                        continue;
                    }

                    // Recalcul instantané des métriques avec les données importées
                    $metrics = $this->computeMetrics($gender, $age, $height, $weight, $waist, $hip, $wrist, $calf, $thigh, $isAthlete == 1, $activity);

                    $dataToInsert = [
                        ':id_user' => $id_user,
                        ':gender' => $gender,
                        ':age' => $age,
                        ':height' => $height,
                        ':weight' => $weight,
                        ':neck' => $neck,
                        ':waist' => $waist,
                        ':hip' => $hip,
                        ':wrist' => $wrist,
                        ':calf' => $calf,
                        ':thigh' => $thigh,
                        ':activity' => $activity,
                        ':is_athlete' => $isAthlete ? 1 : 0,
                        ':body_fat' => $metrics['body_fat'],
                        ':fat_mass' => $metrics['fat_mass'],
                        ':lean_mass' => $metrics['lean_mass'],
                        ':bmr' => $metrics['bmr'],
                        ':tdee' => $metrics['tdee'],
                        ':created_at' => $createdAt,
                    ];

                    $this->model->insertMetric($dataToInsert);
                }
                fclose($handle);
            }
            header('Location: index.php');
            exit;
        }
    }

    public function delete()
    {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            if (!isset($_SESSION['user_id'])) {
                $this->sendJson(['success' => false, 'message' => 'Non authentifié.']);
            }
            $id = $_POST['id'] ?? null;
            $id_user = $_SESSION['user_id'];

            if ($id && $this->model->deleteMetric($id, $id_user)) {
                $this->sendJson(['success' => true, 'message' => 'Mesure supprimée.']);
            } else {
                $this->sendJson(['success' => false, 'message' => 'Erreur lors de la suppression.']);
            }
        }
    }

    private function computeMetrics($gender, $age, $height, $weight, $waist, $hip, $wrist, $calf, $thigh, $isAthlete, $activity)
    {
        // --- MODÈLE HYBRIDE CLINIQUE (CUN-BAE + RFM + BAILEY) ---
        $imc = $weight / (($height / 100) ** 2);
        
        // 1. CUN-BAE
        $sexCun = ($gender === 'male') ? 0 : 1;
        $cunBae = -44.988 + (0.503 * $age) + (3.172 * $imc) - (0.026 * ($imc ** 2)) 
                + (10.689 * $sexCun) + (0.028 * $age * $sexCun) - (0.02 * $imc * $age) 
                + (0.00021 * ($imc ** 2) * $age) + (0.015 * ($imc ** 2) * $sexCun);

        // 2. RFM
        $rfmConstant = ($gender === 'male') ? 64 : 76;
        $rfm = $rfmConstant - (20 * ($height / $waist));

        // 3. Covert Bailey (Version métrique ajustée)
        if ($gender === 'male') {
            $covertBailey = ($waist + 0.5 * $hip) * 0.35 - ($wrist * 1.2) - ($calf * 0.2) - ($thigh * 0.2) - 3;
        } else {
            $covertBailey = ($hip + 0.8 * $thigh) * 0.35 - ($calf * 0.5) - ($wrist * 0.5) - 10;
        }

        // Moyenne pondérée (CUN-BAE et RFM sont cliniques, Bailey est anthropométrique)
        if ($wrist > 0 && $calf > 0 && $thigh > 0 && $hip > 0) {
            $bodyFat = ($cunBae * 0.4) + ($rfm * 0.4) + ($covertBailey * 0.2);
        } else {
            $bodyFat = ($cunBae + $rfm) / 2;
        }

        // Correction Athlète (Corrige le biais de l'IMC de CUN-BAE pour les musclés)
        if ($isAthlete) {
            $bodyFat *= ($gender === 'male' ? 0.85 : 0.90);
        }

        $minFatPercent = 'male' === $gender ? 4.0 : 12.0;
        $bodyFat = max($minFatPercent, min($bodyFat, 60));

        $fatMass = $weight * ($bodyFat / 100);
        $leanMass = $weight - $fatMass;

        // Katch-McArdle
        $bmr = 370 + (21.6 * $leanMass);
        $tdee = $bmr * $activity;

        return [
            'body_fat' => round($bodyFat, 2),
            'fat_mass' => round($fatMass, 2),
            'lean_mass' => round($leanMass, 2),
            'bmr' => round($bmr),
            'tdee' => round($tdee)
        ];
    }

    private function sendJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}