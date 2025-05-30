<?php
// Template pour afficher le message de succès et le lien de téléchargement après modification
// Variables attendues : $downloadName, $xmlStr
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Éditeur de keybinds XML Star Citizen</title>
    <style>body { font-family: monospace; }</style>
</head>
<body>
<h3>Modifications enregistrées !</h3>
<a href="data:application/xml;charset=utf-8,<?= rawurlencode($xmlStr) ?>" download="<?= htmlspecialchars($downloadName) ?>">Télécharger le XML modifié</a>
<hr><a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Retour</a>
</body>
</html>
