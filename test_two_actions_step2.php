<?php
/**
 * Test complet des deux actions nécessaires de l'étape 2
 */

require_once 'config.php';
require_once 'src/DeviceManager.php';
require_once 'src/XMLProcessor.php';
require_once 'src/RedisManager.php';
require_once 'src/StepByStepEditor.php';

// Configuration pour les tests
$config = [
    'files_dir' => __DIR__ . '/mappings/devices',
    'templates_dir' => __DIR__ . '/templates',
    'redis' => [
        'enabled' => false
    ]
];

echo "🎯 Test complet des deux actions nécessaires - Étape 2\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // Initialiser l'éditeur
    $editor = new SCConfigEditor\StepByStepEditor($config);
    
    // Simuler un XML chargé en session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // XML de test plus complexe avec structure Star Citizen correcte
    $testXML = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="testProfile">
    <CustomisationUIHeader label="Test Profile" description="Profile de test" image=""/>
    
    <!-- Options des dispositifs existants -->
    <options type="joystick" instance="1" Product="VKB Gladiator NXT R {231D0200-0000-0000-0000-504944564944}"/>
    <options type="joystick" instance="2" Product="Thrustmaster T.16000M FCS {044F-B10A-0000-0000-504944564944}"/>
    
    <!-- ActionMaps avec actions configurées -->
    <actionmap name="spaceship_movement">
        <action name="v_pitch">
            <rebind input="js1_x"/>
        </action>
        <action name="v_yaw">
            <rebind input="js1_y"/>
        </action>
    </actionmap>
    <actionmap name="spaceship_weapons">
        <action name="v_attack1_group1">
            <rebind input="js1_button1"/>
        </action>
    </actionmap>
</ActionMaps>';
    
    // Créer un fichier temporaire pour le XML afin de pouvoir l'initialiser avec XMLProcessor
    $tempXmlFile = tempnam(sys_get_temp_dir(), 'test_xml_');
    file_put_contents($tempXmlFile, $testXML);
    
    // Initialiser le XMLProcessor avec le fichier temporaire pour valider la structure
    $xmlProcessor = new SCConfigEditor\XMLProcessor($tempXmlFile, 'test_profile_complet.xml');
    
    // Mettre le XML en session
    $_SESSION['stepByStep'] = [
        'currentStep' => 2,
        'xmlData' => $testXML,
        'xmlName' => 'test_profile_complet.xml',
        'devices' => [],
        'newDevices' => [],
        'modifications' => [],
        'completed' => false
    ];
    
    echo "✅ Session initialisée avec XML de test complet\n\n";
    
    // Nettoyer le fichier temporaire
    unlink($tempXmlFile);
    
    // =============================================
    // TEST 1: Création de mapping de dispositif
    // =============================================
    echo "📁 ACTION 1: Test de création de mapping JSON\n";
    echo "=" . str_repeat("-", 50) . "\n";
    
    // Simuler les données d'un nouveau dispositif
    $deviceData = [
        'name' => 'Test Joystick VKB',
        'guid' => '{231D0201-0000-0000-0000-504944564944}',
        'buttons' => 12,
        'axes' => 6,
        'hats' => 1,
        'xml_instance' => '3'
    ];
    
    // Vérifier le répertoire de mappings
    $mappingsDir = __DIR__ . '/mappings/devices';
    echo "   Répertoire mappings: {$mappingsDir}\n";
    echo "   Existe: " . (is_dir($mappingsDir) ? "✅ Oui" : "❌ Non") . "\n";
    
    // Lister les fichiers existants
    $existingFiles = glob($mappingsDir . '/*.json');
    echo "   Fichiers existants: " . count($existingFiles) . "\n";
    foreach ($existingFiles as $file) {
        echo "     - " . basename($file) . "\n";
    }
    
    // Tester la création de mapping via StepByStepEditor
    $reflection = new ReflectionClass($editor);
    $createMappingMethod = $reflection->getMethod('createDeviceMapping');
    $createMappingMethod->setAccessible(true);
    
    echo "\n   Test de createDeviceMapping():\n";
    try {
        $mappingResult = $createMappingMethod->invoke($editor, $deviceData);
        echo "   ✅ Mapping créé avec succès:\n";
        echo "      Fichier: " . $mappingResult['filename'] . "\n";
        echo "      Status: " . $mappingResult['status'] . "\n";
        echo "      Chemin: " . $mappingResult['filepath'] . "\n";
        
        // Vérifier le contenu du fichier créé
        if (file_exists($mappingResult['filepath'])) {
            $content = file_get_contents($mappingResult['filepath']);
            $mapping = json_decode($content, true);
            echo "      Contenu valide: " . (is_array($mapping) ? "✅ Oui" : "❌ Non") . "\n";
            echo "      Vendor ID: " . ($mapping['vendor_id'] ?? 'N/A') . "\n";
            echo "      Product ID: " . ($mapping['product_id'] ?? 'N/A') . "\n";
            echo "      Boutons: " . (isset($mapping['buttons']) ? count($mapping['buttons']) : '0') . "\n";
            echo "      Axes: " . (isset($mapping['axes_map']) ? count($mapping['axes_map']) : '0') . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    }
    
    // =============================================
    // TEST 2: Intégration XML complète
    // =============================================
    echo "\n\n🔧 ACTION 2: Test d'intégration XML complète\n";
    echo "=" . str_repeat("-", 50) . "\n";
    
    // Tester l'ajout au XML via addDeviceToXML
    $addDeviceMethod = $reflection->getMethod('addDeviceToXML');
    $addDeviceMethod->setAccessible(true);
    
    echo "   Test de addDeviceToXML():\n";
    try {
        $addDeviceMethod->invoke($editor, $deviceData);
        echo "   ✅ Dispositif ajouté au XML avec succès\n";
        
        // Récupérer les données de session mises à jour
        $sessionProperty = $reflection->getProperty('sessionData');
        $sessionProperty->setAccessible(true);
        $sessionData = $sessionProperty->getValue($editor);
        
        // Vérifier le XML modifié
        $modifiedXml = $sessionData['xmlData'];
        echo "   XML modifié: " . (strlen($modifiedXml) > 0 ? "✅ Présent" : "❌ Absent") . "\n";
        
        // Analyser le XML modifié
        $xml = simplexml_load_string($modifiedXml);
        if ($xml) {
            // Compter les options joystick (équivalent des joysticks dans ce format)
            $joystickOptions = $xml->xpath('//options[@type="joystick"]');
            echo "   Joysticks dans XML: " . count($joystickOptions) . "\n";
            
            // Vérifier si notre nouveau joystick est présent
            $newJoystick = $xml->xpath("//options[@type='joystick' and @instance='3']");
            echo "   Nouveau joystick (instance 3): " . (!empty($newJoystick) ? "✅ Présent" : "❌ Absent") . "\n";
            
            // Vérifier les options
            $options = $xml->xpath('//options[@type="joystick"]');
            echo "   Options joystick: " . count($options) . "\n";
            
            // Vérifier les actionmaps
            $actionmaps = $xml->xpath('//actionmap');
            echo "   ActionMaps: " . count($actionmaps) . "\n";
            
            // Vérifier les actions configurées
            $actions = $xml->xpath('//action');
            echo "   Actions totales: " . count($actions) . "\n";
            
            $configuredActions = $xml->xpath('//action[rebind[@input!=""]]');
            echo "   Actions configurées: " . count($configuredActions) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    }
    
    // =============================================
    // TEST 3: Vérification des statistiques
    // =============================================
    echo "\n\n📊 VÉRIFICATION: Mise à jour des statistiques\n";
    echo "=" . str_repeat("-", 50) . "\n";
    
    // Tester la mise à jour des stats
    $updateStatsMethod = $reflection->getMethod('updateXmlStats');
    $updateStatsMethod->setAccessible(true);
    
    try {
        $updateStatsMethod->invoke($editor);
        
        $sessionProperty = $reflection->getProperty('sessionData');
        $sessionProperty->setAccessible(true);
        $sessionData = $sessionProperty->getValue($editor);
        
        if (isset($sessionData['xmlStats'])) {
            $stats = $sessionData['xmlStats'];
            echo "   Stats mises à jour: ✅ Oui\n";
            echo "   Actions totales: " . $stats['total'] . "\n";
            echo "   Actions utilisées: " . $stats['used'] . "\n";
            if ($stats['total'] > 0) {
                echo "   Pourcentage: " . round(($stats['used'] / $stats['total']) * 100, 1) . "%\n";
            }
        } else {
            echo "   Stats mises à jour: ❌ Non\n";
        }
        
        if (isset($sessionData['xmlDevices'])) {
            $devices = $sessionData['xmlDevices'];
            echo "   Dispositifs XML: " . count($devices) . "\n";
            foreach ($devices as $device) {
                echo "     - " . $device['instance'] . ": " . $device['product'] . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ RÉSULTAT: Tests des deux actions complétés !\n";
    echo "🎯 Action 1 (Mapping): Présence du mapping dans mappings/devices/\n";
    echo "🎯 Action 2 (XML): Intégration complète avec toutes les balises nécessaires\n\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
