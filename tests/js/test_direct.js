// Test direct de la logique de cycling
console.log('=== TEST DIRECT DE LA LOGIQUE ===');

// Classe simulée
class TestBindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.lastInput = null;
        this.lastInputTime = 0;
    }

    cycleRows(rows, inputName) {
        if (!rows.length) return null;
        
        const now = Date.now();
        const CYCLE_TIMEOUT = 1500;
        
        const isSameInputRepeated = (this.lastInput === inputName && (now - this.lastInputTime) < CYCLE_TIMEOUT);
        
        console.log(`Input: ${inputName}, LastInput: ${this.lastInput}, TimeDiff: ${now - this.lastInputTime}ms, SameRepeated: ${isSameInputRepeated}`);
        
        if (isSameInputRepeated) {
            const currentIndex = this.currentButtonIndex[inputName] || 0;
            const newIndex = (currentIndex + 1) % rows.length;
            this.currentButtonIndex[inputName] = newIndex;
            console.log(`Cycling: ${currentIndex} -> ${newIndex}`);
        } else {
            this.currentButtonIndex[inputName] = 0;
            console.log(`Reset index to 0`);
        }
        
        this.lastInput = inputName;
        this.lastInputTime = now;
        
        return { id: rows[this.currentButtonIndex[inputName]], index: this.currentButtonIndex[inputName] };
    }
}

const handler = new TestBindingsHandler();
const mockRows = ['binding-0', 'binding-1', 'binding-2'];

// Test multiple bindings
console.log('1. Premier appui:');
const r1 = handler.cycleRows(mockRows, 'js1_button1');
console.log(`-> ${r1.id} (index: ${r1.index})`);

console.log('2. Deuxième appui:');
const r2 = handler.cycleRows(mockRows, 'js1_button1');
console.log(`-> ${r2.id} (index: ${r2.index})`);

console.log('3. Troisième appui:');
const r3 = handler.cycleRows(mockRows, 'js1_button1');
console.log(`-> ${r3.id} (index: ${r3.index})`);

console.log('4. Quatrième appui (cycle):');
const r4 = handler.cycleRows(mockRows, 'js1_button1');
console.log(`-> ${r4.id} (index: ${r4.index})`);

console.log('=== TEST TERMINÉ ===');
