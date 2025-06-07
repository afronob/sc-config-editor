<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Nouveau chemin vers les fichiers de mapping JSON
    $mappingsDir = __DIR__ . '/mappings/devices';
    
    $devices = [];
    
    // Vérifier si le répertoire des mappings existe
    if (is_dir($mappingsDir)) {
        // Parcourir tous les fichiers JSON dans le répertoire
        $files = glob($mappingsDir . '/*_map.json');
        
        foreach ($files as $file) {
            try {
                // Lire et décoder le fichier JSON
                $jsonContent = file_get_contents($file);
                $deviceData = json_decode($jsonContent, true);
                
                // Vérifier que le JSON est valide et contient les champs requis
                if ($deviceData && isset($deviceData['id'])) {
                    // Extraire les données nécessaires pour la détection
                    $device = [
                        'id' => $deviceData['id'],
                        'vendor_id' => $deviceData['vendor_id'] ?? '',
                        'product_id' => $deviceData['product_id'] ?? '',
                        'xml_instance' => $deviceData['xml_instance'] ?? ''
                    ];
                    
                    $devices[] = $device;
                }
            } catch (Exception $e) {
                // En cas d'erreur avec un fichier spécifique, continuer avec les autres
                error_log("Erreur lors de la lecture de $file: " . $e->getMessage());
                continue;
            }
        }
    }
    
    // Fallback : essayer de lire l'ancien fichier CSV s'il existe et si aucun mapping JSON n'a été trouvé
    if (empty($devices)) {
        $csvFile = __DIR__ . '/files/devices_data.csv';
        
        if (file_exists($csvFile)) {
            if (($handle = fopen($csvFile, 'r')) !== FALSE) {
                $headers = fgetcsv($handle, 1000, ',', '"', '\\');
                
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
        }
    }
    
    // Retourner les devices en JSON
    echo json_encode($devices);
    
} catch (Exception $e) {
    // En cas d'erreur, retourner un tableau vide
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>