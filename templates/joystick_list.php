<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Éditeur de keybinds XML Star Citizen</title>
    <style>body { font-family: monospace; }</style>
</head>
<body>
<?= $actionsInfo ?>
<div style="margin-bottom:1em;"><b>Joysticks détectés :</b><br><?= implode('<br>', $joysticks) ?></div>
<div id="joy_iframe_container" style="display:none;position:absolute;top:20px;right:20px;z-index:1000;background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:0;"></div>
<script src="joy_iframe.js"></script>
</body>
</html>
