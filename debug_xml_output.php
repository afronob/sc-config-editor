<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/DeviceManager.php';
require_once __DIR__ . '/src/XMLProcessor.php';
require_once __DIR__ . '/src/RedisManager.php';
require_once __DIR__ . '/src/StepByStepEditor.php';

// Simuler les données de session avec XML de test
session_start();

$testXml = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="test profile">
    <deviceoptions>
        <joystick instance="1" product="{0738a221-0000-0000-0000-504944564944}">
            <flight_move_pitch exponent="1.50000000" />
            <flight_move_yaw exponent="1.50000000" />
        </joystick>
        <joystick instance="2" product="{231d0200-0000-0000-0000-504944564944}">
            <flight_move_roll exponent="2.00000000" />
        </joystick>
    </deviceoptions>
    <actionmap name="spaceship_movement">
        <action name="v_pitch" device="joystick" instance="1">
            <rebind input="js1_y" />
        </action>
        <action name="v_yaw" device="joystick" instance="1">
            <rebind input="js1_z" />
        </action>
        <action name="v_roll" device="joystick" instance="2">
            <rebind input="js2_x" />
        </action>
    </actionmap>
    <actionmap name="spaceship_targeting">
    </actionmap>
</ActionMaps>';

$_SESSION['xmlData'] = $testXml;

// Créer l'éditeur et les données du dispositif
$editor = new StepByStepEditor();

$deviceData = [
    'name' => 'Test Joystick VKB',
    'guid' => '{0201231d-0000-0000-0000-504944564944}',
    'instance' => '3',
    'buttons' => '12',
    'axes' => '6',
    'hats' => '1',
    'create_mapping' => '1',
    'add_to_xml' => '1'
];

// Utiliser la réflexion pour accéder à la méthode privée
$reflection = new ReflectionClass($editor);
$addDeviceMethod = $reflection->getMethod('addDeviceToXML');
$addDeviceMethod->setAccessible(true);

try {
    $addDeviceMethod->invoke($editor, $deviceData);
    
    // Récupérer les données de session mises à jour
    $sessionProperty = $reflection->getProperty('sessionData');
    $sessionProperty->setAccessible(true);
    $sessionData = $sessionProperty->getValue($editor);
    
    // Afficher le XML modifié
    echo "XML MODIFIÉ:\n";
    echo "=============\n";
    $xml = simplexml_load_string($sessionData['xmlData']);
    echo $xml->asXML();
    
    echo "\n\nANALYSE DU XML:\n";
    echo "================\n";
    
    // Vérifier tous les joysticks
    $joysticks = $xml->xpath('//joystick');
    echo "Nombre total de joysticks: " . count($joysticks) . "\n\n";
    
    foreach ($joysticks as $i => $joystick) {
        $instance = (string)$joystick['instance'];
        $product = (string)$joystick['product'];
        echo "Joystick " . ($i+1) . ":\n";
        echo "  Instance: $instance\n";
        echo "  Product: $product\n\n";
    }
    
    // Rechercher spécifiquement notre nouveau joystick
    $newJoystick = $xml->xpath("//joystick[@product='{0201231d-0000-0000-0000-504944564944}']");
    echo "Nouveau joystick trouvé: " . (!empty($newJoystick) ? "OUI" : "NON") . "\n";
    if (!empty($newJoystick)) {
        echo "Instance du nouveau joystick: " . (string)$newJoystick[0]['instance'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
