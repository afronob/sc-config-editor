<?php
require_once __DIR__ . '/bootstrap.php';

use SCConfigEditor\Application;

// Initialisation de l'application
$app = new Application($config);

// Exécution
echo $app->run();
