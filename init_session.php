<?php
session_start();

// XML de test simple
$testXML = '<?xml version="1.0" encoding="UTF-8"?>
<ActionMaps version="1">
    <actionmap name="spaceship_movement">
        <action name="v_pitch">
            <rebind input="js1_x"/>
        </action>
    </actionmap>
</ActionMaps>';

$_SESSION['stepByStep'] = [
    'xmlData' => $testXML,
    'xmlName' => 'test.xml'
];

echo "OK - Session XML initialisée";
?>
