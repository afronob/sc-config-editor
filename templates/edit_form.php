<?php
// Template pour afficher le formulaire d'édition des keybinds
// Variables attendues : $xmlName, $xml, $actionNames, $actionmaps_root, $joysticks, $actionsInfo

$title = 'Éditeur de keybinds XML Star Citizen';

// CSS spécifique à la page
$head = <<<HTML
    <style>
        #joystick-bindings-modal {
            display: none;
            position: fixed;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border: 1px solid #888;
            box-shadow: 2px 2px 10px #888;
            padding: 1em;
            z-index: 2000;
            max-width: 90vw;
            max-height: 80vh;
            overflow: auto;
        }
    </style>
HTML;

ob_start();
?>
<h2>Édition des actions</h2>
<div id="gamepad-devices-list" style="margin-bottom:1em;"></div>
<?php if (!empty($joysticks)) : ?>
    <div style="margin-bottom:1em;"><b>Joysticks détectés :</b><br>
    <?php foreach ($joysticks as $idx => $joyHtml): ?>
        <?php
            // Extraire le numéro d'instance réel depuis le HTML généré (ex: js1 : ...)
            if (preg_match('/js(\d+)/i', strip_tags($joyHtml), $m)) {
                $instance = $m[1];
            } else {
                $instance = $idx;
            }
        ?>
        <?= $joyHtml ?>
        <a href="#" class="show-bindings" data-instance="<?= htmlspecialchars($instance) ?>">[Voir bindings]</a><br>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= isset($actionsInfo) ? $actionsInfo : '' ?>
<!-- Filtres -->
<div style="margin-bottom:1em;"><b>Filtres</b><br>
<label><input type="checkbox" id="filter-nonempty"> Afficher seulement les bindings non vides</label><br>
<label><input type="checkbox" id="filter-hold"> Afficher seulement les inputs en mode Hold</label>
</div>
<form method="post">
    <input type="hidden" name="save" value="1">
    <input type="hidden" name="xmlname" value="<?= htmlspecialchars($xmlName) ?>">
    <input type="hidden" name="xmldata" value="<?= htmlspecialchars($xml->asXML(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php
    $profileName = isset($xml['profileName']) ? (string)$xml['profileName'] : '';
    $customLabel = isset($xml->CustomisationUIHeader['label']) ? (string)$xml->CustomisationUIHeader['label'] : '';
    ?>
    <div>
        <label>profileName (ActionMaps) :
            <input name="profileName" value="<?= htmlspecialchars($profileName) ?>" />
        </label>
    </div>
    <div>
        <label>label (CustomisationUIHeader) :
            <input name="customLabel" value="<?= htmlspecialchars($customLabel) ?>" />
        </label>
    </div>
    <table border="1" cellpadding="4" id="bindings-table">
        <tr>
            <th translate="no">category</th>
            <th>action</th>
            <th>name</th>
            <th translate="no">input</th>
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
            <td translate="no"><?= htmlspecialchars($cat) ?></td>
            <td><?= htmlspecialchars($act) ?></td>
            <td><?= htmlspecialchars($name) ?></td>
            <td translate="no"><input name="input[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($input) ?>" /></td>
            <td><input name="opts[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($opts) ?>" /></td>
            <td><input name="value[<?= htmlspecialchars($cat) ?>][<?= htmlspecialchars($act) ?>][<?= $i ?>]" value="<?= htmlspecialchars($value) ?>" /></td>
        </tr>
        <?php $i++; endforeach; endforeach; endforeach; ?>
    </table>
    <button type="submit">Enregistrer et générer le lien de téléchargement</button>
</form>
<!-- Modal pour afficher les bindings par joystick -->
<div id="joystick-bindings-modal"></div>
<?php
$content = ob_get_clean();

// Affichage des joysticks du XML
if (!empty($xmlJoysticks)) {
    echo '<div style="margin-bottom:1em;"><b>Joysticks configurés dans le XML :</b><br>';
    foreach ($xmlJoysticks as $joystick) {
        echo '<div style="padding-left:1em; margin-bottom:0.5em;">';
        echo '<b>' . htmlspecialchars($joystick['product']) . '</b> (instance XML: js' . htmlspecialchars($joystick['instance']) . ')';
        echo ' <a href="#" class="show-bindings" data-instance="' . htmlspecialchars($joystick['instance']) . '">[Voir bindings]</a><br>';
        echo '</div>';
    }
    echo '</div>';
}

// Préparation des données JavaScript
$buttonNamesByInstance = [];
if (!empty($devicesData)) {
    foreach ($devicesData as $device) {
        if (!empty($device['xml_instance']) && isset($device['buttons'])) {
            $buttonNamesByInstance[$device['xml_instance']] = $device['buttons'];
        }
    }
}

// JavaScript spécifique à la page
$devicesDataJson = json_encode($devicesData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$buttonNamesByInstanceJson = json_encode($buttonNamesByInstance, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$actionNamesJson = json_encode($actionNames, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$scripts = <<<HTML
    <script type="module">
        import { initialize } from '/assets/js/bindingEditor.js';
        
        // Initialisation avec les données PHP
        document.addEventListener('DOMContentLoaded', function() {
            initialize({
                devicesData: {$devicesDataJson},
                buttonNamesByInstance: {$buttonNamesByInstanceJson},
                actionNames: {$actionNamesJson}
            });
        });
    </script>
HTML;

require __DIR__ . '/layout.php';
