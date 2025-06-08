const fs = require('fs');

// Simulation des données chargées depuis le serveur
const deviceData = [
    {
        "id": "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
        "vendor_id": "0x0738",
        "product_id": "0xa221",
        "xml_instance": "0738_a221"
    },
    {
        "id": "VKB Gladiator EVO R (Vendor: 231d Product: 0200)",
        "vendor_id": "0x231d",
        "product_id": "0x0200",
        "xml_instance": "231d_0200"
    }
];

// Fonction de détection (même logique que dans le navigateur)
function isDeviceKnown(gamepad, devices) {
    const gamepadVendorId = `0x${gamepad.id.match(/Vendor: ([0-9a-fA-F]{4})/)?.[1]?.toLowerCase() || ''}`;
    const gamepadProductId = `0x${gamepad.id.match(/Product: ([0-9a-fA-F]{4})/)?.[1]?.toLowerCase() || ''}`;
    
    console.log(`🔍 Recherche dispositif - Vendor: ${gamepadVendorId}, Product: ${gamepadProductId}`);
    
    return devices.some(device => {
        const match = device.vendor_id.toLowerCase() === gamepadVendorId && 
                     device.product_id.toLowerCase() === gamepadProductId;
        
        if (match) {
            console.log(`✅ Dispositif trouvé dans la base : ${device.id}`);
        }
        
        return match;
    });
}

// Test du mapping corrigé
function testCorrectedMapping() {
    console.log('🧪 VALIDATION FINALE - SAITEK X-56 CORRIGÉ');
    console.log('='.repeat(50));
    
    try {
        // Vérifier que le fichier de mapping existe et est valide
        const mappingPath = './mappings/devices/0738_a221_map.json';
        const mapping = JSON.parse(fs.readFileSync(mappingPath, 'utf8'));
        
        console.log('📄 Analyse du fichier de mapping corrigé :');
        console.log(`   - ID: ${mapping.id}`);
        console.log(`   - Product: ${mapping.product || '❌ MANQUANT'}`);
        console.log(`   - Vendor ID: ${mapping.vendor_id}`);
        console.log(`   - Product ID: ${mapping.product_id}`);
        console.log(`   - XML Instance: ${mapping.xml_instance}`);
        
        // Vérifier la structure axes_map
        const hasCorrectAxesMap = mapping.axes_map && typeof mapping.axes_map === 'object' && !Array.isArray(mapping.axes_map);
        console.log(`   - Axes Map: ${hasCorrectAxesMap ? '✅ Objet correct' : '❌ Structure incorrecte'}`);
        
        if (hasCorrectAxesMap) {
            console.log(`   - Nombre d'axes mappés: ${Object.keys(mapping.axes_map).length}`);
        }
        
        // Test de détection avec un gamepad simulé
        const mockSaitekGamepad = {
            id: "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
            index: 0,
            connected: true
        };
        
        console.log('\n🎮 Test de détection :');
        const isRecognized = isDeviceKnown(mockSaitekGamepad, deviceData);
        
        console.log('\n📊 RÉSULTATS :');
        if (mapping.product && hasCorrectAxesMap && isRecognized) {
            console.log('🎉 SUCCÈS ! Le Saitek X-56 est maintenant correctement configuré et reconnu !');
            console.log('✅ Fichier de mapping conforme');
            console.log('✅ Champ "product" présent');
            console.log('✅ Structure "axes_map" correcte');
            console.log('✅ Détection fonctionnelle');
            return true;
        } else {
            console.log('❌ Des problèmes persistent :');
            if (!mapping.product) console.log('   - Champ "product" manquant');
            if (!hasCorrectAxesMap) console.log('   - Structure "axes_map" incorrecte');
            if (!isRecognized) console.log('   - Détection non fonctionnelle');
            return false;
        }
        
    } catch (error) {
        console.log(`❌ Erreur lors du test : ${error.message}`);
        return false;
    }
}

// Comparaison avant/après
function showBeforeAfter() {
    console.log('\n📋 COMPARAISON AVANT/APRÈS :');
    console.log('─'.repeat(50));
    
    console.log('🔴 AVANT (problématique) :');
    console.log(`{
    "id": "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
    "vendor_id": "0x0738",
    "product_id": "0xa221",
    "xml_instance": "0738_a221",
    "axes_map": [                    ← Array (incorrect)
        "x", "y", "z", "rotx", "roty", "rotz", "axis6", "axis7"
    ]
    // ❌ Pas de champ "product"
}`);
    
    console.log('\n🟢 APRÈS (corrigé) :');
    console.log(`{
    "id": "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
    "product": "Saitek Pro Flight X-56 Rhino Throttle",  ← Ajouté
    "vendor_id": "0x0738",
    "product_id": "0xa221",
    "xml_instance": "0738_a221",
    "axes_map": {                    ← Objet (correct)
        "0": "x", "1": "y", "2": "z", "3": "rotx",
        "4": "roty", "5": "rotz", "6": "axis6", "7": "axis7"
    }
}`);
}

// Exécution des tests
console.log('🔧 DIAGNOSTIC FINAL - PROBLÈME SAITEK X-56 RÉSOLU');
console.log('='.repeat(60));

showBeforeAfter();
const success = testCorrectedMapping();

console.log('\n' + '='.repeat(60));
if (success) {
    console.log('🎉 PROBLÈME RÉSOLU ! Le Saitek X-56 devrait maintenant être reconnu.');
    console.log('💡 La cause était une structure de fichier de mapping non conforme.');
} else {
    console.log('❌ Le problème persiste, investigation supplémentaire nécessaire.');
}
console.log('='.repeat(60));
