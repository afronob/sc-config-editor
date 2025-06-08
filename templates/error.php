<?php
// Template pour afficher un message d'erreur
// Variables attendues : $errorMsg

$title = 'Erreur - Éditeur de keybinds XML Star Citizen';
$head = '<style>body { color: red; }</style>';

// Sécuriser la variable errorMsg
$errorMsg = $errorMsg ?? 'Erreur inconnue';

$content = <<<HTML
<h3>Erreur</h3>
<p>{$errorMsg}</p>
<hr><a href="{$_SERVER['PHP_SELF']}">Retour</a>
HTML;

require __DIR__ . '/layout.php';
