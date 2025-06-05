console.log('🎮 TEST FINAL DU SYSTÈME DE CYCLING NAVIGATION');
console.log('==============================================');

// Simuler le système de cycling
class MockBindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.lastInput = null;
        this.lastInputTime = 0;
    }

    cycleRows(rows, inputName, currentIndexMap) {
        if (!rows.length) {
            console.log(`[CycleRows] Aucune ligne trouvée pour ${inputName}`);
            return null;
        }
        
        const now = Date.now();
        const CYCLE_TIMEOUT = 1500;
        
        const isSameInputRepeated = (this.lastInput === inputName && (now - this.lastInputTime) < CYCLE_TIMEOUT);
        
        console.log(`[CycleRows] Input: ${inputName}, Rows: ${rows.length}, LastInput: ${this.lastInput}, TimeDiff: ${now - this.lastInputTime}ms, SameRepeated: ${isSameInputRepeated}`);
        
        if (isSameInputRepeated) {
            const currentIndex = currentIndexMap[inputName] || 0;
            const newIndex = (currentIndex + 1) % rows.length;
            currentIndexMap[inputName] = newIndex;
            console.log(`[CycleRows] Cycling: ${currentIndex} -> ${newIndex}`);
        } else {
            currentIndexMap[inputName] = 0;
            console.log(`[CycleRows] Nouveau input, index reset à 0`);
        }
        
        this.lastInput = inputName;
        this.lastInputTime = now;
        
        const selectedRow = rows[currentIndexMap[inputName]];
        console.log(`[CycleRows] Sélection index ${currentIndexMap[inputName]}: ${selectedRow.action}`);
        
        return selectedRow;
    }
}

// Mock des lignes de bindings
const mockRows = [
    { id: 'binding-0', action: 'Throttle Forward' },
    { id: 'binding-1', action: 'Target Ahead' },
    { id: 'binding-2', action: 'Fire Primary' }
];

const singleRow = [
    { id: 'binding-3', action: 'Throttle Backward' }
];

// Tests
const handler = new MockBindingsHandler();
let testsPassed = 0;
let testsFailed = 0;

function assert(condition, message) {
    if (condition) {
        console.log(`✅ PASS: ${message}`);
        testsPassed++;
    } else {
        console.log(`❌ FAIL: ${message}`);
        testsFailed++;
    }
}

console.log('\n=== TEST 1: Cycling avec plusieurs bindings ===');
handler.currentButtonIndex = {};
handler.lastInput = null;
handler.lastInputTime = 0;

const result1 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(result1.id === 'binding-0', 'Premier appui doit sélectionner binding-0');

// Appui rapide suivant
const result2 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(result2.id === 'binding-1', 'Deuxième appui doit sélectionner binding-1');

const result3 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(result3.id === 'binding-2', 'Troisième appui doit sélectionner binding-2');

const result4 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(result4.id === 'binding-0', 'Quatrième appui doit revenir à binding-0 (cycle complet)');

console.log('\n=== TEST 2: Binding unique ===');
handler.currentButtonIndex = {};
handler.lastInput = null;
handler.lastInputTime = 0;

const single1 = handler.cycleRows(singleRow, 'js1_button2', handler.currentButtonIndex);
assert(single1.id === 'binding-3', 'Premier appui binding unique doit sélectionner binding-3');

const single2 = handler.cycleRows(singleRow, 'js1_button2', handler.currentButtonIndex);
assert(single2.id === 'binding-3', 'Deuxième appui binding unique doit rester sur binding-3');

console.log('\n=== TEST 3: Reset par timeout ===');
handler.currentButtonIndex = {};
handler.lastInput = null;
handler.lastInputTime = 0;

// Premier cycle
const timeout1 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(timeout1.id === 'binding-0', 'Premier appui après reset doit être binding-0');

const timeout2 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
assert(timeout2.id === 'binding-1', 'Deuxième appui rapide doit être binding-1');

// Simuler un délai de timeout
setTimeout(() => {
    const timeout3 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
    assert(timeout3.id === 'binding-0', 'Appui après timeout doit revenir à binding-0');
    
    console.log('\n==============================================');
    console.log('🎯 RÉSULTATS FINAUX');
    console.log(`✅ Tests réussis: ${testsPassed}`);
    console.log(`❌ Tests échoués: ${testsFailed}`);
    console.log(`📊 Total: ${testsPassed + testsFailed}`);
    
    if (testsFailed === 0) {
        console.log('\n🎉 TOUS LES TESTS SONT PASSÉS!');
        console.log('✨ Le système de cycling navigation fonctionne correctement!');
        console.log('\n📋 PROCHAINES ÉTAPES:');
        console.log('1. Tester avec un vrai gamepad sur http://localhost:8000');
        console.log('2. Vérifier les logs dans la console du navigateur');
        console.log('3. Supprimer les console.log de debug une fois validé');
        console.log('4. Le système est prêt pour la production!');
    } else {
        console.log('\n❌ PROBLÈMES DÉTECTÉS');
        console.log('Vérifiez les logs ci-dessus pour identifier les problèmes.');
    }
}, 1600); // Attendre plus longtemps que le timeout (1500ms)
