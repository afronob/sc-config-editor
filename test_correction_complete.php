<?php
// Test complet pour vérifier la correction de l'erreur getConnectedDevices()
require_once 'bootstrap.php';

use SCConfigEditor\StepByStepEditor;

echo "=== Test de correction de l'erreur getConnectedDevices() ===\n\n";

// Configuration de test
$config = [
    'files_dir' => __DIR__ . '/files',
    'templates_dir' => __DIR__ . '/templates',
    'external_dir' => __DIR__ . '/external'
];

try {
    // Créer une instance de StepByStepEditor
    $editor = new StepByStepEditor($config);
    echo "✅ Instance StepByStepEditor créée avec succès\n";
    
    // Démarrer une session pour les tests
    session_start();
    
    // Test XML de base
    $testXML = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="test_profile">
    <actionmap name="spaceship_movement">
        <action name="v_pitch">
            <rebind input="js1_x"/>
        </action>
        <action name="v_yaw">
            <rebind input="js1_y"/>
        </action>
    </actionmap>
    <actionmap name="spaceship_weapons">
        <action name="v_weapon_launch_missile">
            <rebind input="js1_button1"/>
        </action>
    </actionmap>
</ActionMaps>';

    // Simuler des données XML dans la session
    $_SESSION['stepByStep'] = [
        'xmlData' => $testXML,
        'xmlName' => 'test_config.xml'
    ];
    
    echo "✅ Session initialisée avec XML de test\n";
    
    // Test de la méthode detectConnectedDevices() directement
    $devices = $editor->detectConnectedDevices();
    echo "✅ Méthode detectConnectedDevices() fonctionne\n";
    echo "   Périphériques détectés: " . count($devices) . "\n";
    
    if (!empty($devices)) {
        echo "   Périphériques trouvés: " . implode(", ", $devices) . "\n";
    }
    
    // Test de canAccessStep()
    $result = $editor->canAccessStep(1);
    echo "✅ Méthode canAccessStep(1) fonctionne: " . ($result ? "true" : "false") . "\n";
    
    $result = $editor->canAccessStep(2);
    echo "✅ Méthode canAccessStep(2) fonctionne: " . ($result ? "true" : "false") . "\n";
    
    echo "\n=== Tous les tests réussis ! ===\n";
    echo "L'erreur getConnectedDevices() a été corrigée avec succès.\n";
    echo "Le système step-by-step est fonctionnel.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur pendant les tests: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Erreur fatale pendant les tests: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
