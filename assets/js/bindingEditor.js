// Variables globales
let lastButtonStates = {};
let lastAxesStates = {};
let buttonNamesByInstance = {};
let currentButtonIndex = {};  // format: "js1_button1" => indexCourant
let currentAxisIndex = {};    // format: "js1_x" => indexCourant
let currentHatIndex = {};     // format: "js1_hat1_up" => indexCourant

// Fonctions utilitaires
function getActiveInput() {
    return document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement.type === 'text';
}

function clearAllHighlights() {
    document.querySelectorAll('tr').forEach(row => {
        row.style.background = '';
    });
}

function highlightRow(row) {
    clearAllHighlights();
    row.style.background = '#ffe066';
    row.scrollIntoView({behavior: 'smooth', block: 'center'});
}

function cycleRows(rows, inputName, currentIndexMap) {
    if (!rows.length) return;
    
    // Initialiser l'index si nécessaire
    if (currentIndexMap[inputName] === undefined || currentIndexMap[inputName] >= rows.length) {
        currentIndexMap[inputName] = 0;
    }

    // Surbriller la ligne courante
    highlightRow(rows[currentIndexMap[inputName]]);
    
    // Incrémenter pour le prochain appui
    currentIndexMap[inputName]++;
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

function findRowsForAxis(jsIdx, axisName) {
    let selector = `input[name^='input[']`;
    let rows = [];
    document.querySelectorAll(selector).forEach(input => {
        let val = input.value.trim();
        let regex = new RegExp(`^js${jsIdx}_${axisName}$`, 'i');
        if (regex.test(val)) {
            let tr = input.closest('tr');
            if (tr) rows.push(tr);
        }
    });
    return rows;
}

function findRowsForHat(jsIdx, hatDir) {
    let selector = `input[name^='input[']`;
    let rows = [];
    document.querySelectorAll(selector).forEach(input => {
        let val = input.value.trim();
        let regex = new RegExp(`^js${jsIdx}_hat1_${hatDir}$`, 'i');
        if (regex.test(val)) {
            let tr = input.closest('tr');
            if (tr) rows.push(tr);
        }
    });
    return rows;
}

function getInstanceFromGamepad(gp) {
    if (!window.devicesDataJs) return null;
    
    // Extraire vendor_id et product_id du gamepad
    const gpIdMatch = gp.id.match(/vendor:\s*([0-9a-f]{4})\s*product:\s*([0-9a-f]{4})/i);
    if (!gpIdMatch) return null;
    
    const gpVendorId = gpIdMatch[1].toLowerCase();
    const gpProductId = gpIdMatch[2].toLowerCase();
    
    let found = window.devicesDataJs.find(dev => {
        const devVendorId = dev.vendor_id?.replace('0x', '').toLowerCase();
        const devProductId = dev.product_id?.replace('0x', '').toLowerCase();
        return devVendorId === gpVendorId && devProductId === gpProductId;
    });
    
    return found ? found.xml_instance : null;
}

// Gestion de l'overlay pour afficher les inputs
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

function showInputOverlay(text) {
    showOverlay(text);
}

// Gestion des inputs gamepad
function handleGamepadInput() {
    let gamepads = navigator.getGamepads ? navigator.getGamepads() : [];
    for (let i = 0; i < gamepads.length; i++) {
        let gp = gamepads[i];
        if (!gp) continue;
        let instance = getInstanceFromGamepad(gp);
        let deviceMap = null;
        if (window.devicesDataJs && instance) {
            deviceMap = window.devicesDataJs.find(dev => dev.xml_instance == instance);
        }
        if (!instance || !buttonNamesByInstance[instance]) {
            continue;
        }

        // Initialisation des états
        if (!lastButtonStates[instance]) lastButtonStates[instance] = [];
        if (!lastAxesStates[instance]) lastAxesStates[instance] = [];

        // Gestion des boutons
        for (let b = 0; b < gp.buttons.length; b++) {
            let pressed = gp.buttons[b].pressed;
            if (pressed && !lastButtonStates[instance][b]) {
                let btnName = `js${instance}_button${b+1}`;
                let mode = '';
                showOverlay(btnName);
                if (getActiveInput()) {
                    document.activeElement.value = btnName;
                } else {
                    let rows = findRowsForButton(instance, b+1, mode);
                    cycleRows(rows, btnName, currentButtonIndex);
                }
            }
            lastButtonStates[instance][b] = pressed;
        }

        // Gestion des axes
        for (let a = 0; a < gp.axes.length; a++) {
            if (!lastAxesStates[instance]) lastAxesStates[instance] = [];
            let val = gp.axes[a];
            let hatDir = null;
            let hatDetected = false;

            // Gestion du mapping axes/hat
            if (deviceMap && deviceMap.hats && deviceMap.hats[a]) {
                // Axe traité comme hat
                const hat = deviceMap.hats[a];
                for (const dir in hat.directions) {
                    const d = hat.directions[dir];
                    if (parseInt(d.axis) === a && val >= d.value_min && val <= d.value_max) {
                        hatDir = dir;
                        hatDetected = true;
                        const xmlName = `js${instance}_hat1_${dir}`;
                        showInputOverlay(xmlName);
                        if (getActiveInput()) {
                            document.activeElement.value = xmlName;
                        } else {
                            let rows = findRowsForHat(instance, dir);
                            cycleRows(rows, xmlName, currentHatIndex);
                        }
                        break;
                    }
                }
                if (!hatDetected && hat.rest && val >= hat.rest.value_min && val <= hat.rest.value_max) {
                    hatDetected = true;
                }
            }

            // Axes classiques
            if (!hatDetected && deviceMap && deviceMap.axes_map && deviceMap.axes_map.hasOwnProperty(a)) {
                let axisName = `js${instance}_${deviceMap.axes_map[a]}`;
                let axisValue = val;
                let lastValue = lastAxesStates[instance][a] || 0;
                
                if (Math.abs(axisValue) > 0.5 && Math.abs(lastValue) < 0.5) {
                    showInputOverlay(axisName);
                    if (getActiveInput()) {
                        document.activeElement.value = axisName;
                    } else {
                        let rows = findRowsForAxis(instance, deviceMap.axes_map[a]);
                        cycleRows(rows, axisName, currentAxisIndex);
                    }
                }
            }
            lastAxesStates[instance][a] = val;
        }
    }
    requestAnimationFrame(handleGamepadInput);
}

// Initialisation de la liste des périphériques
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
            <b>${gp.id}</b> (index: ${gp.index}, instance XML: js${instance})</div>`;
    }
    if (!any) html += '<i>Aucun device détecté.</i>';
    document.getElementById('gamepad-devices-list').innerHTML = html;
}

// Gestion du filtre des bindings
function initializeBindingFilter() {
    const filterBox = document.getElementById('filter-nonempty');
    if (!filterBox) return;

    filterBox.addEventListener('change', function() {
        var showOnlyNonEmpty = filterBox.checked;
        var table = document.getElementById('bindings-table');
        if (!table) return;

        Array.from(table.rows).forEach(function(row, idx) {
            if (idx === 0) return; // skip header
            var inputCell = row.querySelector('input[name^="input["]');
            if (!inputCell) return;
            var val = inputCell.value;
            row.style.display = showOnlyNonEmpty && !val.trim() ? 'none' : '';
        });
    });
}

// Gestion de la modal des bindings
function initializeBindingsModal() {
    const modal = document.getElementById('joystick-bindings-modal');
    if (!modal) return;

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
                    var button = match[0];
                    var action = row.cells[2].textContent;
                    var category = row.cells[0].textContent;
                    bindingsByButton[button] = bindingsByButton[button] || [];
                    bindingsByButton[button].push({
                        action: action,
                        category: category,
                        opts: row.cells[4].querySelector('input')?.value || '',
                        value: row.cells[5].querySelector('input')?.value || ''
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
                        var reg = /_(\D+)?(\d+)$/;
                        var ma = a.match(reg);
                        var mb = b.match(reg);
                        if (ma && mb && ma[2] && mb[2] && ma[1] === mb[1]) {
                            return parseInt(ma[2], 10) - parseInt(mb[2], 10);
                        }
                        return a.localeCompare(b);
                    })
                    .forEach(function(button) {
                        html += '<li><b>' + button + '</b><ul>';
                        bindingsByButton[button].forEach(function(item) {
                            var prefix = '';
                            if ((item.opts.toLowerCase() === 'activationmode' && item.value.toLowerCase() === 'double_tap') ||
                                (item.opts.toLowerCase() === 'multitap' && item.value === '2')) {
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
}

// Initialisation
function initialize(data) {
    window.devicesDataJs = data.devicesData;
    buttonNamesByInstance = data.buttonNamesByInstance;

    // Initialisation des états des gamepads
    let gamepads = navigator.getGamepads();
    for (let i = 0; i < gamepads.length; i++) {
        let gp = gamepads[i];
        if (!gp) continue;
        let instance = getInstanceFromGamepad(gp);
        if (instance) {
            lastAxesStates[instance] = new Array(gp.axes.length).fill(0);
            lastButtonStates[instance] = new Array(gp.buttons.length).fill(false);
        }
    }

    // Initialisation des composants
    initializeBindingFilter();
    initializeBindingsModal();
    renderGamepadDevicesList();

    // Démarrage de la détection des inputs
    requestAnimationFrame(handleGamepadInput);
}

// Event listeners
window.addEventListener('gamepadconnected', renderGamepadDevicesList);
window.addEventListener('gamepaddisconnected', renderGamepadDevicesList);

// Export de l'initialisation
export { initialize };
