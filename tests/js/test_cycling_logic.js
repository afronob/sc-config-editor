// Test Node.js pour vérifier la logique de cycling
// Simulation du BindingsHandler pour test unitaire

class MockBindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
        this.lastInput = null;
        this.lastInputTime = 0;
    }

    cycleRows(rows, inputName, currentIndexMap) {
        if (!rows.length) {
            console.log(`[CycleRows] Aucune ligne trouvée pour ${inputName}`);
            return null;
        }
        
        const now = Date.now();
        const CYCLE_TIMEOUT = 1500; // 1.5 secondes
        
        // Vérifier si c'est le même input répété rapidement
        const isSameInputRepeated = (this.lastInput === inputName && (now - this.lastInputTime) < CYCLE_TIMEOUT);
        
        console.log(`[CycleRows] Input: ${inputName}, Rows: ${rows.length}, LastInput: ${this.lastInput}, TimeDiff: ${now - this.lastInputTime}ms, SameRepeated: ${isSameInputRepeated}`);
        
        if (isSameInputRepeated) {
            // C'est un appui répété, on avance dans le cycle
            const currentIndex = currentIndexMap[inputName] || 0;
            const newIndex = (currentIndex + 1) % rows.length;
            currentIndexMap[inputName] = newIndex;
            console.log(`[CycleRows] Cycling: ${currentIndex} -> ${newIndex}`);
        } else {
            // Nouveau input, on commence au début
            currentIndexMap[inputName] = 0;
            console.log(`[CycleRows] Nouveau input, index reset à 0`);
        }
        
        // Mettre à jour le tracker
        this.lastInput = inputName;
        this.lastInputTime = now;
        
        const selectedRow = rows[currentIndexMap[inputName]];
        console.log(`[CycleRows] Sélection index ${currentIndexMap[inputName]}: ${selectedRow.id}`);
        
        return selectedRow;
    }
}

// Mock data pour les tests
const mockRows = [
    { id: 'binding-0', cells: [null, null, { textContent: 'Throttle Forward' }] },
    { id: 'binding-1', cells: [null, null, { textContent: 'Strafe Forward' }] },
    { id: 'binding-2', cells: [null, null, { textContent: 'Flight Ready' }] }
];

const singleRow = [
    { id: 'binding-3', cells: [null, null, { textContent: 'Space Brake' }] }
];

// Tests
console.log('=== TEST CYCLING LOGIC ===');

const handler = new MockBindingsHandler();

// Test 1: Premier appui
console.log('\n--- Test 1: Premier appui ---');
const result1 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
console.log(`Résultat: ${result1.id}, Index: ${handler.currentButtonIndex['js1_button1']}`);

// Test 2: Deuxième appui rapide
console.log('\n--- Test 2: Deuxième appui rapide ---');
const result2 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
console.log(`Résultat: ${result2.id}, Index: ${handler.currentButtonIndex['js1_button1']}`);

// Test 3: Troisième appui rapide  
console.log('\n--- Test 3: Troisième appui rapide ---');
const result3 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
console.log(`Résultat: ${result3.id}, Index: ${handler.currentButtonIndex['js1_button1']}`);

// Test 4: Quatrième appui rapide (retour au début)
console.log('\n--- Test 4: Quatrième appui rapide (retour au début) ---');
const result4 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
console.log(`Résultat: ${result4.id}, Index: ${handler.currentButtonIndex['js1_button1']}`);

// Test 5: Binding unique
console.log('\n--- Test 5: Binding unique ---');
const result5 = handler.cycleRows(singleRow, 'js1_button2', handler.currentButtonIndex);
console.log(`Résultat: ${result5.id}, Index: ${handler.currentButtonIndex['js1_button2']}`);

const result6 = handler.cycleRows(singleRow, 'js1_button2', handler.currentButtonIndex);
console.log(`Résultat: ${result6.id}, Index: ${handler.currentButtonIndex['js1_button2']}`); // Doit rester 0

// Test 6: Timeout reset
console.log('\n--- Test 6: Timeout reset ---');
setTimeout(() => {
    const result7 = handler.cycleRows(mockRows, 'js1_button1', handler.currentButtonIndex);
    console.log(`Après timeout - Résultat: ${result7.id}, Index: ${handler.currentButtonIndex['js1_button1']}`); // Doit être 0
}, 1600);

console.log('\n=== TESTS TERMINÉS ===');
