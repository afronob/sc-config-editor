<?php
/**
 * Test de l'encart contextuel dans l'étape 2
 * Vérifie que les informations XML sont bien affichées dans l'encart
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
    <title>Test Encart Contextuel - Étape 2</title>
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

echo "<h1>🧪 Test Encart Contextuel - Étape 2</h1>";

try {
    // Charger la configuration correctement
    $config = [
        'files_dir' => __DIR__ . '/files',
        'templates_dir' => __DIR__ . '/templates',
        'external_dir' => __DIR__ . '/external',
        'redis' => ['enabled' => false]
    ];
    $stepByStepEditor = new StepByStepEditor($config);

    // Simuler l'upload d'un fichier XML avec des données de test
    $xmlFile = __DIR__ . '/test_config_avec_dispositifs.xml';
    
    if (!file_exists($xmlFile)) {
        echo "<div class='test-section'>";
        echo "<p class='error'>❌ <strong>Fichier de test manquant:</strong> $xmlFile</p>";
        echo "</div>";
        echo "</body></html>";
        exit;
    }

    // Simuler les données de session comme si un upload avait eu lieu
    $_SESSION['stepByStep'] = [
        'currentStep' => 2,
        'xmlData' => file_get_contents($xmlFile),
        'xmlName' => 'test_config_avec_dispositifs.xml',
        'xmlStats' => ['total' => 12, 'used' => 8],
        'xmlDevices' => [
            ['instance' => 'js1', 'product' => 'VKB Gladiator NXT R'],
            ['instance' => 'js2', 'product' => 'Thrustmaster T.16000M FCS']
        ],
        'devices' => [],
        'newDevices' => [],
        'modifications' => [],
        'completed' => false
    ];

    echo "<div class='test-section'>";
    echo "<h2>📋 Données de session préparées</h2>";
    echo "<p><strong>Fichier XML:</strong> test_config_avec_dispositifs.xml</p>";
    echo "<p><strong>Statistiques:</strong> 8/12 actions configurées (66.7%)</p>";
    echo "<p><strong>Dispositifs:</strong> 2 dispositifs détectés</p>";
    echo "</div>";

    // Récréer l'instance pour qu'elle utilise les nouvelles données de session
    $stepByStepEditor = new StepByStepEditor($config);
    
    echo "<div class='test-section'>";
    echo "<h2>🎯 Test de l'étape 2 avec encart contextuel</h2>";
    
    // Exécuter l'étape 2 et capturer le rendu
    $result = $stepByStepEditor->run();
    
    echo "<h3>✅ Rendu de l'étape 2:</h3>";
    echo "<div style='background: white; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto;'>";
    echo $result;
    echo "</div>";
    echo "</div>";

    echo "<div class='test-section'>";
    echo "<h2>🔍 Vérifications</h2>";
    echo "<p class='success'>✅ Test terminé - Vérifiez visuellement l'encart contextuel dans le rendu ci-dessus</p>";
    echo "<p class='info'>ℹ️ L'encart doit afficher :</p>";
    echo "<ul>";
    echo "<li>Le nom du fichier XML (test_config_avec_dispositifs.xml)</li>";
    echo "<li>Les statistiques des actions (8/12 configurées, 66.7%)</li>";
    echo "<li>La liste des 2 dispositifs détectés</li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='test-section'>";
    echo "<p class='error'>❌ <strong>Erreur:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<div class='test-section'>";
echo "<h2>🚀 Navigation directe</h2>";
echo "<p>Pour tester l'encart dans l'interface complète :</p>";
echo "<p><a href='http://localhost:8080/step_by_step_handler.php?step=2' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📄 Ouvrir l'étape 2</a></p>";
echo "</div>";

echo "</body></html>";
?>
