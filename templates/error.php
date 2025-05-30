<?php
// Template pour afficher un message d'erreur
// Variables attendues : $errorMsg
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Erreur - Éditeur de keybinds XML Star Citizen</title>
    <style>body { font-family: monospace; color: red; }</style>
</head>
<body>
<h3>Erreur</h3>
<p><?= htmlspecialchars($errorMsg) ?></p>
<hr><a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Retour</a>
</body>
</html>
