<?php
// Template pour afficher le formulaire d'édition des keybinds
// Variables attendues : $xmlName, $xml, $actionNames, $actionmaps_root, $joysticks, $actionsInfo
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="utf-8"><title>Éditeur de keybinds XML Star Citizen</title><style>body { font-family: monospace; }</style></head>
<body>
<h2>Édition des actions</h2>
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
    <label><input type="checkbox" id="filter-nonempty"> Afficher seulement les bindings non vides</label>
</div>
<form method="post">
    <input type="hidden" name="save" value="1">
    <input type="hidden" name="xmlname" value="<?= htmlspecialchars($xmlName) ?>">
    <input type="hidden" name="xmldata" value="<?= htmlspecialchars($xml->asXML()) ?>">
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
<div id="joystick-bindings-modal" style="display:none;position:fixed;top:10%;left:50%;transform:translateX(-50%);background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:1em;z-index:2000;max-width:90vw;max-height:80vh;overflow:auto;"></div>
<script src="joy_iframe.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterBox = document.getElementById('filter-nonempty');
    var table = document.getElementById('bindings-table');
    function isBindingEmpty(val) {
        return val.trim() === '' || /^((js|kb)[0-9]+_)$/i.test(val.trim());
    }
    filterBox.addEventListener('change', function() {
        var showOnlyNonEmpty = filterBox.checked;
        Array.from(table.rows).forEach(function(row, idx) {
            if (idx === 0) return; // skip header
            var inputCell = row.querySelector('input[name^="input["]');
            if (!inputCell) return;
            var val = inputCell.value;
            if (showOnlyNonEmpty) {
                row.style.display = isBindingEmpty(val) ? 'none' : '';
            } else {
                row.style.display = '';
            }
        });
    });
    // Affichage des bindings par joystick
    var modal = document.getElementById('joystick-bindings-modal');
    document.querySelectorAll('.show-bindings').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var instance = this.getAttribute('data-instance');
            var rows = Array.from(document.querySelectorAll('#bindings-table tr')).slice(1); // skip header
            var bindingsByButton = {};
            rows.forEach(function(row) {
                var inputCell = row.querySelector('input[name^="input["]');
                if (!inputCell) return;
                var val = inputCell.value.trim();
                var match = val.match(/^(js|kb)([0-9]+)_([\w\d]+)$/i);
                if (match && match[2] == instance) {
                    var button = match[0]; // ex: js1_button1
                    var action = row.cells[2].textContent;
                    var category = row.cells[0].textContent;
                    bindingsByButton[button] = bindingsByButton[button] || [];
                    bindingsByButton[button].push({
                        action: action,
                        category: category,
                        opts: row.cells[4].querySelector('input') ? row.cells[4].querySelector('input').value : '',
                        value: row.cells[5].querySelector('input') ? row.cells[5].querySelector('input').value : ''
                    });
                }
            });
            var html = '<b>Bindings pour Joystick #' + instance + '</b> <button onclick="document.getElementById(\'joystick-bindings-modal\').style.display=\'none\'">✖</button><br>';
            if (Object.keys(bindingsByButton).length === 0) {
                html += '<div>Aucun binding trouvé.</div>';
            } else {
                html += '<ul>';
                Object.keys(bindingsByButton)
                    .sort(function(a, b) {
                        // Extraire le numéro du bouton pour tri naturel
                        var reg = /_(\D+)?(\d+)$/;
                        var ma = a.match(reg);
                        var mb = b.match(reg);
                        if (ma && mb && ma[2] && mb[2] && ma[1] === mb[1]) {
                            // Même préfixe (ex: button), trier par numéro
                            return parseInt(ma[2], 10) - parseInt(mb[2], 10);
                        }
                        return a.localeCompare(b);
                    })
                    .forEach(function(button) {
                        html += '<li><b>' + button + '</b><ul>';
                        bindingsByButton[button].forEach(function(item) {
                            var prefix = '';
                            // Cas 1 : activationMode=double_tap (insensible à la casse)
                            if (item.opts.toLowerCase() === 'activationmode' && item.value.toLowerCase() === 'double_tap') {
                                prefix = '[DT] ';
                            }
                            // Cas 2 : multiTap=2
                            if (item.opts.toLowerCase() === 'multitap' && item.value === '2') {
                                prefix = '[DT] ';
                            }
                            html += '<li>' + prefix + item.action + ' <span style="color:#888">(' + item.category + ')</span></li>';
                        });
                        html += '</ul></li>';
                    });
                html += '</ul>';
            }
            modal.innerHTML = html;
            modal.style.display = 'block';
        });
    });
});
</script>
</body>
</html>
