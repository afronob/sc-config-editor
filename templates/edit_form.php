<?php
// Template pour afficher le formulaire d'édition des keybinds
// Variables attendues : $xmlName, $xml, $actionNames, $actionmaps_root, $joysticks, $actionsInfo
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="utf-8"><title>Éditeur de keybinds XML Star Citizen</title><style>body { font-family: monospace; }</style></head>
<body>
<h2>Édition des actions</h2>
<?php if (!empty($joysticks)) : ?>
    <div style="margin-bottom:1em;"><b>Joysticks détectés :</b><br><?= implode('<br>', $joysticks) ?></div>
<?php endif; ?>
<?= isset($actionsInfo) ? $actionsInfo : '' ?>
<form method="post">
    <input type="hidden" name="save" value="1">
    <input type="hidden" name="xmlname" value="<?= htmlspecialchars($xmlName) ?>">
    <input type="hidden" name="xmldata" value="<?= htmlspecialchars($xml->asXML()) ?>">
    <table border="1" cellpadding="4">
        <tr>
            <th>category</th>
            <th>action</th>
            <th>name</th>
            <th>input</th>
            <th>opts</th>
            <th>value</th>
        </tr>
        <?php foreach ($actionmaps_root->actionmap as $actionmap):
            $cat = (string)$actionmap['name'];
            foreach ($actionmap->action as $action):
                $act = (string)$action['name'];
                $name = isset($actionNames[$act]) ? $actionNames[$act] : '';
                $i = 0;
                foreach ($action->rebind as $rebind):
                    $input = (string)$rebind['input'];
                    $opts = '';
                    $value = '';
                    foreach ($rebind->attributes() as $k => $v) {
                        if ($k != 'input') {
                            $opts = $k;
                            $value = (string)$v;
                            break;
                        }
                    }
        ?>
        <tr>
            <td><?= htmlspecialchars($cat) ?></td>
            <td><?= htmlspecialchars($act) ?></td>
            <td><?= htmlspecialchars($name) ?></td>
            <td><input name="input[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($input) ?>" /></td>
            <td><input name="opts[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($opts) ?>" /></td>
            <td><input name="value[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($value) ?>" /></td>
        </tr>
        <?php $i++; endforeach; endforeach; endforeach; ?>
    </table>
    <button type="submit">Enregistrer et générer le lien de téléchargement</button>
</form>
<div id="joy_iframe_container" style="display:none;position:absolute;top:20px;right:20px;z-index:1000;background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:0;"></div>
<script src="joy_iframe.js"></script>
</body>
</html>
