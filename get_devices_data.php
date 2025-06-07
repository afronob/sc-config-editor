<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Chemin vers le fichier CSV des devices
    $csvFile = __DIR__ . '/files/devices_data.csv';
    
    // Si le fichier CSV n'existe pas, on crée une structure de base
    if (!file_exists($csvFile)) {
        // Retourner un tableau vide en JSON
        echo json_encode([]);
        exit;
    }
    
    $devices = [];
    
    // Lire le fichier CSV
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ',', '"', '\\'); // Lire la première ligne (headers)
        
        if ($headers) {
            while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) !== FALSE) {
                $device = [];
                foreach ($headers as $index => $header) {
                    $device[$header] = isset($data[$index]) ? $data[$index] : '';
                }
                $devices[] = $device;
            }
        }
        fclose($handle);
    }
    
    // Retourner les devices en JSON
    echo json_encode($devices);
    
} catch (Exception $e) {
    // En cas d'erreur, retourner un tableau vide
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>