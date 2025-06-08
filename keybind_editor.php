<?php
require_once __DIR__ . '/bootstrap.php';

use SCConfigEditor\Application;

// Vérifier si l'utilisateur veut utiliser l'éditeur étape par étape
if (isset($_GET['mode']) && $_GET['mode'] === 'step-by-step') {
    header('Location: step_by_step_handler.php?step=1');
    exit;
}

// Initialisation de l'application classique
$app = new Application($config);

// Ajouter un lien vers l'éditeur étape par étape dans l'interface
$stepByStepLink = '<div style="text-align: center; margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px;">
    <a href="?mode=step-by-step" style="color: white; text-decoration: none; font-weight: bold; font-size: 1.1em;">
        <i class="fas fa-list-ol"></i> Essayer l\'éditeur étape par étape
    </a>
    <p style="margin: 5px 0 0 0; color: rgba(255,255,255,0.8); font-size: 0.9em;">
        Interface guidée en 4 étapes pour une configuration plus simple
    </p>
</div>';

// Exécution
$output = $app->run();

// Injecter le lien dans la sortie (après le premier div ou form)
$output = preg_replace('/(<div[^>]*>|<form[^>]*>)/', '$1' . $stepByStepLink, $output, 1);

echo $output;
