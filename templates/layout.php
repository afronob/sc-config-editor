<?php
// Template pour l'en-tête HTML commun
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?= $title ?? 'Éditeur de keybinds XML Star Citizen' ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <?php echo $head ?? ''; ?>
</head>
<body>
    <?php echo $content ?? ''; ?>
    <?php echo $scripts ?? ''; ?>
    
    <div id="joy_iframe_container" style="display:none;position:absolute;top:20px;right:20px;z-index:1000;background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:0;"></div>
    
    <script type="module">
        import { SCConfigEditor } from '/assets/js/scConfigEditor.js';
        
        // Configuration initiale depuis PHP
        const config = {
            buttonNamesByInstance: <?= json_encode($buttonNamesByInstance ?? []); ?>,
            devicesData: <?= json_encode($devicesData ?? []); ?>
        };
        
        // Initialisation de l'éditeur
        window.addEventListener('DOMContentLoaded', () => {
            window.scConfigEditor = new SCConfigEditor(config);
        });
    </script>
</body>
</html>
