<?php
// Test script pour tester l'étape 2 avec des données XML de test
session_start();

// Simuler un XML de test dans la session
$testXML = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1" optionsVersion="2" rebindVersion="2" profileName="layout_Gamepad_singlestick_xml">
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

$_SESSION['stepByStep'] = [
    'xmlData' => $testXML,
    'xmlName' => 'test_config.xml'
];

echo "Session initialisée avec des données XML de test.\n";
echo "Vous pouvez maintenant tester l'étape 2 avec action=detect\n";
echo "URL: http://localhost:8080/step_by_step_handler.php?step=2&action=detect\n";
?>
