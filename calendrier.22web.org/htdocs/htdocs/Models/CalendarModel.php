<?php
declare(strict_types=1);

class CalendarModel {
    private string $jsonFile;

    public function __construct() {
        // Récupération du chemin depuis le fichier .env
        $envPath = $_ENV['DATA_FILE_PATH'] ?? '../data.json'; // Valeur par défaut en cas d'échec
        
        // On construit le chemin absolu en se basant sur la position du modèle
        $this->jsonFile = __DIR__ . '/' . $envPath;
    }

    public function getEvents(): array {
        if (!file_exists($this->jsonFile) || !is_readable($this->jsonFile)) {
            return [];
        }
        
        $jsonData = file_get_contents($this->jsonFile);
        if ($jsonData === false) {
            return [];
        }

        $decodedData = json_decode($jsonData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedData)) {
            return [];
        }
        
        return $decodedData;
    }
}