#!/usr/bin/env node

// Test automatisé du système d'ancrage simplifié
// Simule un DOM minimal pour tester la logique

console.log('🎯 Test Automatisé - Système d\'Ancrage Simplifié');
console.log('=================================================');

// Simulation minimale du DOM
class MockElement {
    constructor(tagName, attributes = {}) {
        this.tagName = tagName;
        this.attributes = attributes;
        this.textContent = '';
        this.value = attributes.value || '';
        this.classList = {
            add: (className) => this.className = className,
            remove: (className) => this.className = '',
            classes: []
        };
        this.children = [];
        this.cells = [];
    }
    
    querySelector(selector) {
        // Simuler querySelector pour les inputs
        if (selector.includes('input[name^="opts["]')) {
            return { value: this.optsValue || '' };
        }
        if (selector.includes('input[name^="value["]')) {
            return { value: this.valueValue || '' };
        }
        return null;
    }
    
    closest(selector) {
        if (selector === 'tr') {
            return this.parentRow;
        }
        return null;
    }
    
    scrollIntoView() {
        console.log(`📍 Scroll vers: ${this.cells[2]?.textContent || 'Unknown'}`);
    }
}

// Simulation du document
global.document = {
    querySelectorAll: (selector) => {
        if (selector.includes('.gamepad-highlight')) {
            return [];
        }
        if (selector.includes('input[name^=\'input[\']')) {
            return mockInputs;
        }
        return [];
    }
};

// Simulation de window.uiHandler
global.window = {
    uiHandler: {
        showOverlay: (inputText, actionText) => {
            console.log(`📺 Overlay: ${inputText} → ${actionText}`);
        }
    }
};

// Données de test simulées
const mockInputs = [];
const testRows = [
    {
        input: 'js1_button1',
        action: 'v_pitch',
        name: 'Pitch',
        opts: '',
        value: '',
        mode: ''
    },
    {
        input: 'js1_button1', 
        action: 'v_ifcs_esp_hold',
        name: 'E.S.P. - Enable Temporarily (Hold)',
        opts: 'ActivationMode',
        value: 'hold',
        mode: 'hold'
    },
    {
        input: 'js1_button1',
        action: 'v_weapon_cycle_fwd', 
        name: 'Cycle Weapons Forward (Double Tap)',
        opts: 'ActivationMode',
        value: 'double_tap',
        mode: 'double_tap'
    },
    {
        input: 'js1_button2',
        action: 'v_weapon_primary',
        name: 'Fire Primary',
        opts: '',
        value: '',
        mode: ''
    },
    {
        input: 'js1_button2',
        action: 'v_weapon_primary_hold',
        name: 'Fire Primary (Hold)',
        opts: 'ActivationMode', 
        value: 'hold',
        mode: 'hold'
    }
];

// Créer les éléments mock
testRows.forEach((rowData, index) => {
    const input = new MockElement('input', {
        name: `input[${index}]`,
        value: rowData.input
    });
    
    const row = new MockElement('tr');
    row.cells = [
        { textContent: 'Category' },
        { textContent: rowData.action },
        { textContent: rowData.name },
        input,
        { textContent: 'Opts' },
        { textContent: 'Value' }
    ];
    
    row.optsValue = rowData.opts;
    row.valueValue = rowData.value;
    
    input.parentRow = row;
    
    // Ajouter les méthodes querySelector au row
    row.querySelector = (selector) => {
        if (selector.includes('input[name^="opts["]')) {
            return { value: rowData.opts };
        }
        if (selector.includes('input[name^="value["]')) {
            return { value: rowData.value };
        }
        return null;
    };
    
    mockInputs.push(input);
});

// Import dynamique du module (simulation)
class SimplifiedBindingsHandler {
    constructor() {
        this.lastCallTime = {};
    }

    anchorToInput(type, instance, elementName, mode = '') {
        const inputName = `js${instance}_${elementName}`;
        const inputModeKey = `${inputName}_${mode}`;
        
        console.log(`🎯 [Test] Ancrage: ${inputName} mode: ${mode}`);
        
        const targetRow = this.findTargetRow(inputName, mode);
        
        if (targetRow) {
            this.anchorToRow(targetRow, inputName, mode);
            return targetRow;
        } else {
            console.log(`❌ [Test] Aucune ligne trouvée pour ${inputName} mode: ${mode}`);
            return null;
        }
    }

    findTargetRow(inputName, mode) {
        const matchingRows = [];
        
        mockInputs.forEach(input => {
            const inputValue = input.value.trim();
            
            if (inputValue === inputName) {
                const row = input.parentRow;
                if (row) {
                    const rowMode = this.getRowMode(row);
                    
                    if (this.modeMatches(rowMode, mode)) {
                        matchingRows.push(row);
                    }
                }
            }
        });
        
        return matchingRows.length > 0 ? matchingRows[0] : null;
    }

    getRowMode(row) {
        const optsInput = row.querySelector('input[name^="opts["]');
        const valueInput = row.querySelector('input[name^="value["]');
        
        const opts = optsInput?.value.toLowerCase() || '';
        const value = valueInput?.value.toLowerCase() || '';
        
        if (opts === 'activationmode' && value === 'hold') {
            return 'hold';
        }
        
        if ((opts === 'activationmode' && value === 'double_tap') ||
            (opts === 'multitap' && value === '2')) {
            return 'double_tap';
        }
        
        return '';
    }

    modeMatches(rowMode, searchMode) {
        return rowMode === searchMode;
    }

    anchorToRow(row, inputName, mode) {
        const action = row.cells[2]?.textContent || 'Unknown';
        const modeDisplay = mode ? `[${mode.toUpperCase()}]` : '';
        
        console.log(`✅ [Test] Ancré sur: ${inputName} ${modeDisplay} → ${action}`);
        
        if (global.window.uiHandler) {
            global.window.uiHandler.showOverlay(`${inputName} ${modeDisplay}`, action);
        }
    }
}

// === EXÉCUTION DES TESTS ===

const handler = new SimplifiedBindingsHandler();

console.log('\n🧪 Tests d\'Ancrage par Mode:');
console.log('-----------------------------');

// Test 1: js1_button1 mode normal
console.log('\n📋 Test 1: Bouton 1 Normal');
const result1 = handler.anchorToInput('button', 1, 'button1', '');

// Test 2: js1_button1 mode hold
console.log('\n📋 Test 2: Bouton 1 Hold');
const result2 = handler.anchorToInput('button', 1, 'button1', 'hold');

// Test 3: js1_button1 mode double_tap
console.log('\n📋 Test 3: Bouton 1 Double Tap');
const result3 = handler.anchorToInput('button', 1, 'button1', 'double_tap');

// Test 4: js1_button2 mode normal
console.log('\n📋 Test 4: Bouton 2 Normal');
const result4 = handler.anchorToInput('button', 1, 'button2', '');

// Test 5: js1_button2 mode hold
console.log('\n📋 Test 5: Bouton 2 Hold');
const result5 = handler.anchorToInput('button', 1, 'button2', 'hold');

// Test 6: Input inexistant
console.log('\n📋 Test 6: Input Inexistant');
const result6 = handler.anchorToInput('button', 1, 'button99', '');

// Résumé des tests
console.log('\n📊 Résumé des Tests:');
console.log('==================');
console.log(`Test 1 (Button1 Normal): ${result1 ? '✅ PASS' : '❌ FAIL'}`);
console.log(`Test 2 (Button1 Hold): ${result2 ? '✅ PASS' : '❌ FAIL'}`);
console.log(`Test 3 (Button1 DoubleTap): ${result3 ? '✅ PASS' : '❌ FAIL'}`);
console.log(`Test 4 (Button2 Normal): ${result4 ? '✅ PASS' : '❌ FAIL'}`);
console.log(`Test 5 (Button2 Hold): ${result5 ? '✅ PASS' : '❌ FAIL'}`);
console.log(`Test 6 (Input Inexistant): ${!result6 ? '✅ PASS' : '❌ FAIL'}`);

const allPassed = result1 && result2 && result3 && result4 && result5 && !result6;
console.log(`\n🎉 Résultat Global: ${allPassed ? '✅ TOUS LES TESTS RÉUSSIS' : '❌ CERTAINS TESTS ONT ÉCHOUÉ'}`);

if (allPassed) {
    console.log('\n🚀 Le système d\'ancrage simplifié fonctionne parfaitement !');
    console.log('   Tous les modes sont correctement détectés et ancrés.');
} else {
    console.log('\n🔧 Certains tests ont échoué. Vérifiez la logique d\'ancrage.');
}
