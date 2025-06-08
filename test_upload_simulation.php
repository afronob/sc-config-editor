<?php
// Test direct de simulation d'upload avec statistiques

// Simuler les données de session après un upload réussi
session_start();

// Charger les classes nécessaires
require_once 'bootstrap.php';

use SCConfigEditor\XMLProcessor;
use SCConfigEditor\StepByStepEditor;

echo "<h1>🧪 Test Simulation Upload & Statistiques</h1>";

try {
    // Créer un fichier XML de test temporaire
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="test">
  <actionmap name="spaceship_general">
    <action name="v_power_throttle">
      <rebind input="js1_button1"/>
    </action>
    <action name="v_power_shields">
      <rebind input="js1_button2" ActivationMode="hold"/>
    </action>
    <action name="v_power_weapons">
      <rebind input="js1_button3"/>
    </action>
    <action name="v_toggle_landing_system">
      <rebind input="js1_button4"/>
    </action>
    <action name="v_weapon_arm_missile">
    </action>
    <action name="v_weapon_launch_missile">
    </action>
  </actionmap>
  <actionmap name="spaceship_movement">
    <action name="v_pitch">
      <rebind input="js1_x"/>
    </action>
    <action name="v_yaw">
      <rebind input="js1_y"/>
    </action>
    <action name="v_roll">
    </action>
    <action name="v_strafe_longitudinal">
    </action>
  </actionmap>
</ActionMaps>';

    // Créer un fichier temporaire
    $tempFile = tempnam(sys_get_temp_dir(), 'test_xml');
    file_put_contents($tempFile, $xmlContent);
    
    echo "<h2>📄 Fichier XML de test créé</h2>";
    echo "<pre>" . htmlspecialchars($xmlContent) . "</pre>";
    
    // Tester XMLProcessor::getStats()
    $xmlProcessor = new XMLProcessor($tempFile, 'test_config.xml');
    $stats = $xmlProcessor->getStats();
    
    echo "<h2>📊 Résultats XMLProcessor::getStats()</h2>";
    echo "<ul>";
    echo "<li><strong>Actions totales:</strong> " . $stats['total'] . "</li>";
    echo "<li><strong>Actions configurées:</strong> " . $stats['used'] . "</li>";
    echo "<li><strong>Pourcentage:</strong> " . round(($stats['used'] / $stats['total']) * 100, 1) . "%</li>";
    echo "</ul>";
    
    // Simuler une session step-by-step avec ces données
    $_SESSION['stepByStep'] = [
        'currentStep' => 2,
        'xmlData' => $xmlContent,
        'xmlName' => 'test_config.xml',
        'xmlStats' => $stats,
        'devices' => [],
        'newDevices' => [],
        'modifications' => [],
        'completed' => false
    ];
    
    echo "<h2>💾 Session simulée créée</h2>";
    echo "<p>✅ Session step-by-step configurée avec les statistiques</p>";
    
    // Tester StepByStepEditor avec ces données
    $editor = new StepByStepEditor();
    
    echo "<h2>🎯 Test Step-by-Step Editor</h2>";
    echo "<h3>Étape accessible: " . $editor->getHighestAccessibleStep() . "</h3>";
    
    // Forcer l'affichage de l'étape 1 avec les données simulées
    $_GET['step'] = '1';
    echo "<h3>Rendu étape 1 avec statistiques:</h3>";
    
    $result = $editor->run();
    
    echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 20px 0;'>";
    echo $result;
    echo "</div>";
    
    // Nettoyer
    unlink($tempFile);
    
    echo "<h2>✅ Test terminé avec succès</h2>";
    echo "<p><a href='step_by_step_handler.php?step=1'>🔗 Voir l'étape 1 en direct</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur durant le test</h2>";
    echo "<p>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
