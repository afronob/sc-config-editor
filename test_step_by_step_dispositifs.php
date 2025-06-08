<?php
/**
 * Test complet de l'étape 1 avec dispositifs XML
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simuler une session AVANT tout output
session_start();

// Inclure le bootstrap
require_once __DIR__ . '/bootstrap.php';

use SCConfigEditor\StepByStepEditor;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Step-by-Step avec Dispositifs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { 
            background: #f9f9f9; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px; 
            border-left: 4px solid #007cba;
        }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>";

echo "<h1>🧪 Test Step-by-Step Editor - Dispositifs XML</h1>";

try {
    // Simuler l'upload d'un fichier XML avec dispositifs
    $xmlFile = __DIR__ . '/test_config_avec_dispositifs.xml';
    
    if (!file_exists($xmlFile)) {
        throw new Exception("Fichier de test manquant: test_config_avec_dispositifs.xml");
    }
    
    echo "<div class='test-section'>";
    echo "<h2>📤 Simulation upload XML</h2>";
    echo "<p><strong>Fichier:</strong> " . basename($xmlFile) . "</p>";
    echo "<p><strong>Taille:</strong> " . number_format(filesize($xmlFile)) . " octets</p>";
    echo "</div>";
    
    // Copier le fichier dans un répertoire temporaire pour simuler l'upload
    $tempFile = sys_get_temp_dir() . '/test_upload_' . time() . '.xml';
    copy($xmlFile, $tempFile);
    
    // Simuler $_FILES
    $_FILES = [
        'xmlfile' => [
            'name' => basename($xmlFile),
            'tmp_name' => $tempFile,
            'size' => filesize($tempFile),
            'error' => UPLOAD_ERR_OK
        ]
    ];
    
    // Simuler les paramètres GET et POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_GET = ['step' => '1', 'action' => 'upload'];
    $_POST = [];
    
    // Créer une instance du Step-by-Step Editor
    $config = [
        'templates_dir' => __DIR__ . '/templates',
        'files_dir' => __DIR__ . '/files'
    ];
    $editor = new StepByStepEditor($config);
    
    // Traiter l'upload
    echo "<div class='test-section'>";
    echo "<h2>⚙️ Traitement de l'upload</h2>";
    
    $result = $editor->run();
    
    echo "<h3>📊 Résultat du traitement:</h3>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>";
    echo $result;
    echo "</div>";
    echo "</div>";
    
    // Vérifier les données de session
    echo "<div class='test-section'>";
    echo "<h2>🔍 Vérification des données de session</h2>";
    
    if (isset($_SESSION['stepByStep'])) {
        $sessionData = $_SESSION['stepByStep'];
        
        echo "<h4>📋 Données XML:</h4>";
        echo "<p><strong>Nom fichier:</strong> " . ($sessionData['xmlName'] ?? 'Non défini') . "</p>";
        
        if (isset($sessionData['xmlStats'])) {
            echo "<h4>📊 Statistiques actions:</h4>";
            echo "<ul>";
            echo "<li><strong>Total:</strong> " . $sessionData['xmlStats']['total'] . "</li>";
            echo "<li><strong>Configurées:</strong> " . $sessionData['xmlStats']['used'] . "</li>";
            if ($sessionData['xmlStats']['total'] > 0) {
                $percentage = round(($sessionData['xmlStats']['used'] / $sessionData['xmlStats']['total']) * 100, 1);
                echo "<li><strong>Pourcentage:</strong> {$percentage}%</li>";
            }
            echo "</ul>";
        }
        
        if (isset($sessionData['xmlDevices'])) {
            echo "<h4>🎮 Dispositifs détectés:</h4>";
            if (empty($sessionData['xmlDevices'])) {
                echo "<p class='info'>Aucun dispositif trouvé</p>";
            } else {
                echo "<ul>";
                foreach ($sessionData['xmlDevices'] as $device) {
                    echo "<li><strong>{$device['instance']}:</strong> {$device['product']}</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p class='error'>❌ Aucune donnée de dispositifs en session</p>";
        }
    } else {
        echo "<p class='error'>❌ Aucune donnée de session trouvée</p>";
    }
    echo "</div>";
    
    // Nettoyer le fichier temporaire
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    echo "<p class='success'>✅ Test terminé avec succès!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
