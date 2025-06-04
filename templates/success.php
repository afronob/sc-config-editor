<?php
// Template pour afficher le message de succès et le lien de téléchargement après modification
// Variables attendues : $downloadName, $xmlStr

$title = 'Éditeur de keybinds XML Star Citizen';
$content = <<<HTML
<h3>Modifications enregistrées !</h3>
<a href="data:application/xml;charset=utf-8,{rawurlencode($xmlStr)}" download="{htmlspecialchars($downloadName)}">Télécharger le XML modifié</a>
<hr><a href="{htmlspecialchars($_SERVER['PHP_SELF'])}">Retour</a>
HTML;

require __DIR__ . '/layout.php';
