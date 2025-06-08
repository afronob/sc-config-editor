<?php
/**
 * Test direct pour vérifier la récupération des dispositifs XML
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le bootstrap
require_once __DIR__ . '/bootstrap.php';

use SCConfigEditor\XMLProcessor;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Dispositifs XML</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .device { 
            background: #f5f5f5; 
            padding: 10px; 
            margin: 5px 0; 
            border-left: 4px solid #007cba;
        }
        .stats { 
            background: #e8f5e8; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0;
        }
    </style>
</head>
<body>";

echo "<h1>🔍 Test Dispositifs XML</h1>";

try {
    // Test avec le fichier XML existant
    $xmlFile = __DIR__ . '/test_config_avec_dispositifs.xml';
    
    if (!file_exists($xmlFile)) {
        echo "<p class='error'>❌ Fichier test_config.xml introuvable</p>";
        exit;
    }
    
    echo "<h2>📄 Analyse du fichier: " . basename($xmlFile) . "</h2>";
    
    // Initialiser XMLProcessor
    $xmlProcessor = new XMLProcessor($xmlFile, basename($xmlFile));
    
    // Récupérer les statistiques
    $stats = $xmlProcessor->getStats();
    echo "<div class='stats'>";
    echo "<h3>📊 Statistiques des actions</h3>";
    echo "<p><strong>Total actions:</strong> {$stats['total']}</p>";
    echo "<p><strong>Actions configurées:</strong> {$stats['used']}</p>";
    if ($stats['total'] > 0) {
        $percentage = round(($stats['used'] / $stats['total']) * 100, 1);
        echo "<p><strong>Pourcentage:</strong> {$percentage}%</p>";
    }
    echo "</div>";
    
    // Récupérer les dispositifs
    $devices = $xmlProcessor->getJoysticks();
    echo "<div class='stats'>";
    echo "<h3>🎮 Dispositifs trouvés: " . count($devices) . "</h3>";
    
    if (empty($devices)) {
        echo "<p class='info'>ℹ️ Aucun dispositif trouvé dans le XML</p>";
    } else {
        foreach ($devices as $index => $device) {
            echo "<div class='device'>";
            echo "<h4>Dispositif " . ($index + 1) . ":</h4>";
            echo "<p><strong>Instance XML:</strong> js{$device['instance']}</p>";
            echo "<p><strong>Nom du produit:</strong> {$device['product']}</p>";
            echo "<p><strong>Nom brut:</strong> {$device['product_raw']}</p>";
            echo "<p><strong>Fichier généré:</strong> {$device['file_title']}</p>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Afficher un extrait du XML pour debug
    echo "<h3>📝 Extrait XML (options joystick)</h3>";
    $xmlContent = file_get_contents($xmlFile);
    $xml = new SimpleXMLElement($xmlContent);
    $joystickOptions = $xml->xpath('//options[@type="joystick"]');
    
    if (!empty($joystickOptions)) {
        echo "<pre style='background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        foreach ($joystickOptions as $option) {
            echo htmlspecialchars($option->asXML()) . "\n\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='info'>ℹ️ Aucune section options[@type='joystick'] trouvée</p>";
    }
    
    echo "<p class='success'>✅ Test terminé avec succès!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>Stack trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
