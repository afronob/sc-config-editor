<?php
// Template pour afficher le formulaire d'édition des keybinds
// Variables attendues : $xmlName, $xml, $actionNames, $actionmaps_root, $joysticks, $actionsInfo
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="utf-8"><title>Éditeur de keybinds XML Star Citizen</title><style>body { font-family: monospace; }</style></head>
<body>
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
<div id="joy_iframe_container" style="display:none;position:absolute;top:20px;right:20px;z-index:1000;background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:0;"></div>
<div id="joystick-bindings-modal" style="display:none;position:fixed;top:10%;left:50%;transform:translateX(-50%);background:#fff;border:1px solid #888;box-shadow:2px 2px 10px #888;padding:1em;z-index:2000;max-width:90vw;max-height:80vh;overflow:auto;"></div>
<script src="joy_iframe.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filterBox = document.getElementById('filter-nonempty');
    var table = document.getElementById('bindings-table');
    function isBindingEmpty(val) {
        // On considère vide :
        // - chaîne vide
        // - jsX_, kbX_, moX_ (X = 1 ou 2 chiffres)
        // - mo1_ (souris), mo2_ etc.
        // - mo_ (cas rare)
        return val.trim() === '' || /^((js|kb|mo)[0-9]+_)$/i.test(val.trim()) || /^mo_$/i.test(val.trim());
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
// --- Détection bouton joystick et navigation/auto-remplissage ---
let lastButtonStates = {};
let lastAxesStates = {};
let buttonNamesByInstance = {};
<?php if (!empty($devicesData)): ?>
    <?php foreach ($devicesData as $device): if (!empty($device['xml_instance'])): ?>
        buttonNamesByInstance[<?= json_encode($device['xml_instance']) ?>] = <?= json_encode($device['buttons']) ?>;
    <?php endif; endforeach; ?>
<?php endif; ?>

function getActiveInput() {
    return document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement.type === 'text';
}

function highlightRow(row) {
    row.style.background = '#ffe066';
    setTimeout(() => { row.style.background = ''; }, 1500);
    row.scrollIntoView({behavior: 'smooth', block: 'center'});
}

function findRowsForButton(jsIdx, btnIdx, mode) {
    // mode: '', 'double_tap', 'hold' (pour activationMode/multiTap)
    let selector = `input[name^='input[']`;
    let rows = [];
    document.querySelectorAll(selector).forEach(input => {
        let val = input.value.trim();
        let regex = new RegExp(`^js${jsIdx}_button${btnIdx}$`, 'i');
        if (regex.test(val)) {
            let tr = input.closest('tr');
            if (tr) {
                // Vérifie activationMode/multiTap si demandé
                let opts = tr.querySelector('input[name^="opts["]')?.value.toLowerCase() || '';
                let value = tr.querySelector('input[name^="value["]')?.value.toLowerCase() || '';
                if (mode === 'double_tap' && (opts === 'activationmode' && value === 'double_tap' || opts === 'multitap' && value === '2')) {
                    rows.push(tr);
                } else if (mode === 'hold' && (opts === 'activationmode' && value === 'hold')) {
                    rows.push(tr);
                } else if (!mode || (mode === '' && (!opts || (opts !== 'activationmode' && opts !== 'multitap')))) {
                    rows.push(tr);
                }
            }
        }
    });
    return rows;
}

function getInstanceFromGamepad(gamepad) {
    // On cherche dans devicesData le device dont l'id correspond à gamepad.id
    let found = null;
    if (window.devicesDataJs) {
        window.devicesDataJs.forEach(function(dev) {
            // Correction : certains gamepad.id sont plus longs, on fait une recherche plus souple
            if (gamepad.id && dev.id && (gamepad.id.indexOf(dev.id.trim()) !== -1 || dev.id.trim().indexOf(gamepad.id) !== -1) && dev.xml_instance) {
                found = dev.xml_instance;
            }
        });
    }
    // Fallback : si pas trouvé, on tente par index
    if (!found && gamepad.index !== undefined) {
        // On cherche le device JSON qui a le même index
        if (window.devicesDataJs) {
            window.devicesDataJs.forEach(function(dev) {
                if (dev.index == gamepad.index && dev.xml_instance) {
                    found = dev.xml_instance;
                }
            });
        }
    }
    return found;
}

// Affichage du bouton pressé en overlay
const overlay = document.createElement('div');
overlay.style.position = 'fixed';
overlay.style.top = '30px';
overlay.style.left = '50%';
overlay.style.transform = 'translateX(-50%)';
overlay.style.background = '#222';
overlay.style.color = '#fff';
overlay.style.fontSize = '2.2em';
overlay.style.fontWeight = 'bold';
overlay.style.padding = '0.4em 1.2em';
overlay.style.borderRadius = '0.5em';
overlay.style.boxShadow = '0 2px 16px #000a';
overlay.style.zIndex = '9999';
overlay.style.display = 'none';
document.body.appendChild(overlay);

function showOverlay(text) {
    overlay.textContent = text;
    overlay.style.display = 'block';
    clearTimeout(overlay._timeout);
    overlay._timeout = setTimeout(() => { overlay.style.display = 'none'; }, 1200);
}

function handleGamepadInput() {
    let gamepads = navigator.getGamepads ? navigator.getGamepads() : [];
    for (let i = 0; i < gamepads.length; i++) {
        let gp = gamepads[i];
        if (!gp) continue;
        // DEBUG : Affiche l'id du gamepad détecté
        if (!gp._debugged) {
            console.log('Gamepad détecté:', gp.id, 'index:', gp.index);
            gp._debugged = true;
        }
        let instance = getInstanceFromGamepad(gp);
        if (!instance) {
            // DEBUG : Affiche un warning si aucune instance trouvée
            console.warn('Aucune instance XML trouvée pour', gp.id, 'index:', gp.index);
            continue;
        }
        if (!buttonNamesByInstance[instance]) {
            console.warn('Pas de mapping buttonNamesByInstance pour instance', instance);
            continue;
        }
        for (let b = 0; b < gp.buttons.length; b++) {
            let pressed = gp.buttons[b].pressed;
            if (!lastButtonStates[instance]) lastButtonStates[instance] = [];
            if (pressed && !lastButtonStates[instance][b]) {
                // Correction : Star Citizen indexe les boutons à partir de 1 (js1_button1)
                let btnName = `js${instance}_button${b+1}`;
                let mode = '';
                showOverlay(btnName);
                console.log('Bouton pressé:', btnName, 'gamepad:', gp.id, 'instance:', instance);
                if (getActiveInput()) {
                    document.activeElement.value = btnName;
                } else {
                    let rows = findRowsForButton(instance, b+1, mode); // Correction ici aussi
                    if (rows.length) rows.forEach(highlightRow);
                }
            }
            lastButtonStates[instance][b] = pressed;
        }
    }
    requestAnimationFrame(handleGamepadInput);
}

window.addEventListener('DOMContentLoaded', function() {
    requestAnimationFrame(handleGamepadInput);
});
window.devicesDataJs = <?php echo json_encode($devicesData); ?>;

// --- DEBUG ---
console.log('devicesDataJs:', window.devicesDataJs);

function getInstanceFromGamepad(gamepad) {
    let found = null;
    if (window.devicesDataJs) {
        window.devicesDataJs.forEach(function(dev) {
            // On simplifie aussi côté JS pour matcher comme côté PHP
            let devIdSimple = dev.id.replace(/\(Vendor:.*$/, '').trim();
            let gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim();
            if ((gamepadIdSimple && devIdSimple && (gamepadIdSimple.indexOf(devIdSimple) !== -1 || devIdSimple.indexOf(gamepadIdSimple) !== -1)) && dev.xml_instance) {
                found = dev.xml_instance;
            }
        });
    }
    // Fallback : si pas trouvé, on tente par index
    if (!found && gamepad.index !== undefined) {
        if (window.devicesDataJs) {
            window.devicesDataJs.forEach(function(dev) {
                if (dev.index == gamepad.index && dev.xml_instance) {
                    found = dev.xml_instance;
                }
            });
        }
    }
    return found;
}

function renderGamepadDevicesList() {
    let html = '<b>Devices connectés (API Gamepad):</b><br>';
    let gamepads = navigator.getGamepads ? navigator.getGamepads() : [];
    let any = false;
    for (let i = 0; i < gamepads.length; i++) {
        let gp = gamepads[i];
        if (!gp) continue;
        any = true;
        let instance = getInstanceFromGamepad(gp) || '?';
        html += `<div style="margin-bottom:0.5em; padding-left:1em;">
            <b>${gp.id}</b> (index: ${gp.index}, instance XML: js${instance})<br>`;
        if (gp.buttons && gp.buttons.length) {
            let btns = [];
            for (let b = 0; b < gp.buttons.length; b++) {
                // Affichage raccourci : b0 (jsX_buttonY)
                btns.push(`b${b} (js${instance}_button${b+1})`);
            }
            html += `<span>Boutons :</span> ${btns.join(', ')}<br>`;
        }
        if (gp.axes && gp.axes.length) {
            let axes = [];
            for (let a = 0; a < gp.axes.length; a++) {
                // Affichage raccourci : a0 (jsX_axisY)
                axes.push(`a${a} (js${instance}_axis${a+1})`);
            }
            html += `<span>Axes :</span> ${axes.join(', ')}<br>`;
        }
        html += '</div>';
    }
    if (!any) html += '<i>Aucun device détecté.</i>';
    document.getElementById('gamepad-devices-list').innerHTML = html;
}
window.addEventListener('gamepadconnected', renderGamepadDevicesList);
window.addEventListener('gamepaddisconnected', renderGamepadDevicesList);
document.addEventListener('DOMContentLoaded', function() {
    renderGamepadDevicesList();
    // ...existing code...
});
</script>
</body>
</html>
