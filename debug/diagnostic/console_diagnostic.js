// Script de diagnostic pour le système de filtres hold
// À exécuter dans la console du navigateur sur la page d'édition

console.log('🔍 Diagnostic du système de filtres Hold Mode');
console.log('=============================================');

// 1. Vérifier les éléments DOM
console.log('\n1. Vérification des éléments DOM:');
const filterNonEmpty = document.getElementById('filter-nonempty');
const filterHold = document.getElementById('filter-hold');
const table = document.getElementById('bindings-table');

console.log('  filter-nonempty:', filterNonEmpty ? '✅ TROUVÉ' : '❌ MANQUANT');
console.log('  filter-hold:', filterHold ? '✅ TROUVÉ' : '❌ MANQUANT');
console.log('  bindings-table:', table ? `✅ TROUVÉ (${table.rows.length} lignes)` : '❌ MANQUANT');

// 2. Vérifier l'instance SCConfigEditor
console.log('\n2. Vérification SCConfigEditor:');
if (window.scConfigEditor) {
    console.log('  scConfigEditor:', '✅ TROUVÉ');
    console.log('  filter instance:', window.scConfigEditor.filter ? '✅ TROUVÉ' : '❌ MANQUANT');
    
    if (window.scConfigEditor.filter) {
        const handler = window.scConfigEditor.filter;
        console.log('  isBindingEmpty method:', typeof handler.isBindingEmpty);
        console.log('  isHoldModeBinding method:', typeof handler.isHoldModeBinding);
        console.log('  updateFilters method:', typeof handler.updateFilters);
    }
} else {
    console.log('  scConfigEditor: ❌ MANQUANT');
}

// 3. Test des fonctions de filtrage
console.log('\n3. Test des fonctions:');
if (window.scConfigEditor && window.scConfigEditor.filter) {
    const handler = window.scConfigEditor.filter;
    
    try {
        // Test isBindingEmpty
        console.log('  isBindingEmpty tests:');
        console.log('    "" (vide):', handler.isBindingEmpty(''));
        console.log('    "js1_" (préfixe):', handler.isBindingEmpty('js1_'));
        console.log('    "js1_button1" (valide):', handler.isBindingEmpty('js1_button1'));
        
        // Test isHoldModeBinding
        console.log('  isHoldModeBinding tests:');
        console.log('    activationmode + hold:', handler.isHoldModeBinding('activationmode', 'hold'));
        console.log('    activationmode + HOLD:', handler.isHoldModeBinding('activationmode', 'HOLD'));
        console.log('    activationmode + press:', handler.isHoldModeBinding('activationmode', 'press'));
        console.log('    autre + hold:', handler.isHoldModeBinding('autre', 'hold'));
        
    } catch (error) {
        console.log('  ❌ ERREUR lors des tests:', error.message);
    }
}

// 4. Analyser la structure de la table
console.log('\n4. Analyse de la table:');
if (table) {
    const rows = Array.from(table.rows).slice(1); // Skip header
    console.log(`  ${rows.length} lignes de données trouvées`);
    
    let holdCount = 0;
    let emptyCount = 0;
    let validCount = 0;
    
    rows.forEach((row, idx) => {
        const inputCell = row.querySelector('input[name^="input["]');
        const optsCell = row.querySelector('input[name^="opts["]');
        const valueCell = row.querySelector('input[name^="value["]');
        
        if (inputCell && optsCell && valueCell) {
            const inputVal = inputCell.value;
            const optsVal = optsCell.value;
            const valueVal = valueCell.value;
            
            if (window.scConfigEditor && window.scConfigEditor.filter) {
                const handler = window.scConfigEditor.filter;
                const isEmpty = handler.isBindingEmpty(inputVal);
                const isHold = handler.isHoldModeBinding(optsVal, valueVal);
                
                if (isEmpty) emptyCount++;
                if (isHold) holdCount++;
                if (!isEmpty) validCount++;
                
                if (idx < 5) { // Afficher les 5 premières lignes
                    console.log(`    Ligne ${idx + 1}: "${inputVal}" | "${optsVal}" | "${valueVal}" | Empty:${isEmpty} Hold:${isHold}`);
                }
            }
        }
    });
    
    console.log(`  Résumé: ${emptyCount} vides, ${holdCount} hold, ${validCount} valides`);
}

// 5. Test des événements
console.log('\n5. Test des événements:');
if (filterHold && table) {
    console.log('  Test du filtre hold...');
    
    const initialVisible = Array.from(table.rows).slice(1).filter(row => row.style.display !== 'none').length;
    console.log(`    Lignes visibles avant: ${initialVisible}`);
    
    filterHold.checked = true;
    filterHold.dispatchEvent(new Event('change'));
    
    const afterHold = Array.from(table.rows).slice(1).filter(row => row.style.display !== 'none').length;
    console.log(`    Lignes visibles après filtre hold: ${afterHold}`);
    
    filterHold.checked = false;
    filterHold.dispatchEvent(new Event('change'));
    
    const afterReset = Array.from(table.rows).slice(1).filter(row => row.style.display !== 'none').length;
    console.log(`    Lignes visibles après reset: ${afterReset}`);
    
    if (afterHold < initialVisible) {
        console.log('  ✅ Le filtre hold semble fonctionner!');
    } else {
        console.log('  ❌ Le filtre hold ne fonctionne pas correctement');
    }
}

console.log('\n🎯 Diagnostic terminé');
console.log('Si vous voyez des ❌, c\'est là que se trouve le problème!');
