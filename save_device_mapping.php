<?php
/**
 * Script pour sauvegarder les mappings de nouveaux devices
 * Utilisé par le système de détection automatique
 */

// Configuration
require_once 'config.php';

// Headers pour JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fonction pour écrire un log
function writeLog($message) {
    $logFile = __DIR__ . '/debug/device_mapping_saves.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Fonction pour valider un nom de fichier
function validateFileName($fileName) {
    // Autoriser seulement les caractères alphanumériques, tirets, underscores et .json
    return preg_match('/^[a-zA-Z0-9_-]+\.json$/', $fileName);
}

// Fonction pour valider les données de mapping
function validateMappingData($mappingData) {
    if (!is_array($mappingData)) {
        return false;
    }
    
    // Vérifier les champs requis
    $requiredFields = ['id', 'xml_instance'];
    foreach ($requiredFields as $field) {
        if (!isset($mappingData[$field]) || empty($mappingData[$field])) {
            return false;
        }
    }
    
    // Vérifier que xml_instance est unique (pas déjà utilisé)
    $existingMappings = glob(__DIR__ . '/files/*_map.json');
    foreach ($existingMappings as $file) {
        $content = file_get_contents($file);
        if ($content) {
            $existing = json_decode($content, true);
            if ($existing && isset($existing['xml_instance']) && 
                $existing['xml_instance'] === $mappingData['xml_instance']) {
                return false; // Instance déjà utilisée
            }
        }
    }
    
    return true;
}

// Traitement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier l'action
        if (!isset($_POST['action']) || $_POST['action'] !== 'save_device_mapping') {
            throw new Exception('Action non valide');
        }
        
        // Récupérer les données
        $fileName = $_POST['fileName'] ?? '';
        $mappingDataJson = $_POST['mappingData'] ?? '';
        
        // Valider le nom de fichier
        if (!validateFileName($fileName)) {
            throw new Exception('Nom de fichier non valide');
        }
        
        // Décoder les données de mapping
        $mappingData = json_decode($mappingDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Données JSON non valides: ' . json_last_error_msg());
        }
        
        // Valider les données de mapping
        if (!validateMappingData($mappingData)) {
            throw new Exception('Données de mapping non valides ou instance XML déjà utilisée');
        }
        
        // Vérifier que le fichier n'existe pas déjà
        $filePath = __DIR__ . '/files/' . $fileName;
        if (file_exists($filePath)) {
            throw new Exception('Le fichier existe déjà');
        }
        
        // Créer le répertoire si nécessaire
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Formater les données JSON avec indentation
        $formattedJson = json_encode($mappingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Sauvegarder le fichier
        $success = file_put_contents($filePath, $formattedJson);
        
        if ($success === false) {
            throw new Exception('Impossible d\'écrire le fichier');
        }
        
        // Log de succès
        writeLog("Nouveau mapping sauvegardé: $fileName");
        writeLog("Données: " . json_encode($mappingData, JSON_UNESCAPED_UNICODE));
        
        // Réponse de succès
        echo json_encode([
            'success' => true,
            'message' => 'Mapping sauvegardé avec succès',
            'fileName' => $fileName,
            'filePath' => $filePath
        ]);
        
    } catch (Exception $e) {
        // Log d'erreur
        writeLog("Erreur lors de la sauvegarde: " . $e->getMessage());
        
        // Réponse d'erreur
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    // Méthode non autorisée
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée'
    ]);
}
?>