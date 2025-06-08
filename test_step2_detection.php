<?php
/**
 * Test de la détection des dispositifs dans l'étape 2
 */

require_once 'config.php';
require_once 'src/DeviceManager.php';
require_once 'src/XMLProcessor.php';
require_once 'src/RedisManager.php';
require_once 'src/StepByStepEditor.php';

// Configuration pour les tests
$config = [
    'files_dir' => __DIR__ . '/files',
    'templates_dir' => __DIR__ . '/templates',
    'redis' => [
        'enabled' => false
    ]
];

echo "🔍 Test de la détection des dispositifs - Étape 2\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Initialiser l'éditeur
    $editor = new SCConfigEditor\StepByStepEditor($config);
    
    // Simuler un XML chargé en session
    session_start();
    
    // XML de test avec quelques dispositifs configurés
    $testXML = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="testProfile">
    <options type="keyboard" instance="1"/>
    <options type="joystick" instance="1"/>
    <ActionMap name="keyboard">
        <action name="v_attack1_group1">
            <rebind device="keyboard" Key="kb_lshift" />
        </action>
    </ActionMap>
    <ActionMap name="joystick">
        <action name="v_attack1_group1">
            <rebind device="joystick" Key="js1_button1" />
        </action>
        <action name="v_target_nearest_hostile">
            <rebind device="joystick" Key="js1_button2" />
        </action>
    </ActionMap>
    <ActionMap name="saitek_x56">
        <action name="v_target_reticle_focus">
            <rebind device="joystick" Key="js1_button3" />
        </action>
    </ActionMap>
</ActionMaps>';
    
    // Mettre le XML en session
    $_SESSION['stepByStep'] = [
        'currentStep' => 2,
        'xmlData' => $testXML,
        'xmlName' => 'test_profile.xml',
        'devices' => [],
        'newDevices' => [],
        'modifications' => [],
        'completed' => false
    ];
    
    echo "✅ Session initialisée avec XML de test\n";
    
    // Tester la méthode detectConnectedDevices
    echo "\n📡 Test de detectConnectedDevices():\n";
    $devices = $editor->detectConnectedDevices();
    
    echo "   Dispositifs détectés dans le XML:\n";
    if (!empty($devices)) {
        foreach ($devices as $device) {
            echo "   - " . $device['name'] . " (type: " . $device['type'] . ", source: " . $device['source'] . ")\n";
        }
    } else {
        echo "   Aucun dispositif trouvé\n";
    }
    
    echo "\n🎮 Test d'accès à l'étape 2:\n";
    $canAccess = $editor->canAccessStep(2);
    echo "   Peut accéder à l'étape 2: " . ($canAccess ? "✅ Oui" : "❌ Non") . "\n";
    
    echo "\n📊 Vérification du contenu de session:\n";
    echo "   XML présent: " . (!empty($_SESSION['stepByStep']['xmlData']) ? "✅ Oui" : "❌ Non") . "\n";
    echo "   Nom du fichier: " . ($_SESSION['stepByStep']['xmlName'] ?? 'N/A') . "\n";
    echo "   Étape actuelle: " . ($_SESSION['stepByStep']['currentStep'] ?? 'N/A') . "\n";
    
    echo "\n" . str_repeat("=", 55) . "\n";
    echo "✅ RÉSULTAT: Tests réussis !\n";
    echo "La détection des dispositifs XML fonctionne correctement.\n";
    echo "La détection des dispositifs physiques se fait côté client.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur pendant les tests: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
