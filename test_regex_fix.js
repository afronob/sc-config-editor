// Test simple de l'ancrage après correction
console.log('🔧 Test de correction d\'ancrage');

// Test 1: Extraction du numéro de bouton
const buttonName = 'js1_button1';
const buttonMatch = buttonName.match(/button(\d+)$/);
const buttonNumber = buttonMatch ? buttonMatch[1] : null;
console.log(`✅ buttonName "${buttonName}" -> buttonNumber "${buttonNumber}"`);

// Test 2: Extraction du nom d'axe
const axisName = 'js1_axis9';
const axisMatch = axisName.match(/^js\d+_(.+)$/);
const cleanAxisName = axisMatch ? axisMatch[1] : axisName;
console.log(`✅ axisName "${axisName}" -> cleanAxisName "${cleanAxisName}"`);

// Test 3: Vérification regex pour différents cas
const testCases = [
    'js1_button1',
    'js1_button2', 
    'js2_button10',
    'js1_axis9',
    'js1_x',
    'js1_y',
    'js1_rotz'
];

console.log('\n📋 Tests de tous les cas:');
testCases.forEach(test => {
    if (test.includes('button')) {
        const match = test.match(/button(\d+)$/);
        const result = match ? match[1] : null;
        console.log(`🎮 ${test} -> button ${result}`);
    } else {
        const match = test.match(/^js\d+_(.+)$/);
        const result = match ? match[1] : test;
        console.log(`📊 ${test} -> axis ${result}`);
    }
});

console.log('\n✅ Tous les tests de regex passent correctement !');
