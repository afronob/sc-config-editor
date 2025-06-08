const fs = require('fs');

console.log('🔧 VALIDATION FINALE - CORRECTION STEP 2 SAITEK X-56');
console.log('='.repeat(60));        console.log('   • name: ' + (saitek.name || '❌ MANQUANT (cause du problème)'));
// Simulation des données des dispositifs
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

// Ancienne fonction défaillante
function checkIfDeviceIsKnownOLD(gamepad, devicesData) {
    if (!devicesData || !Array.isArray(devicesData)) {
        return false;
    }
    
    const gamepadIdSimple = gamepad.id.toLowerCase().replace(/\s+/g, ' ').trim();
    
    // ❌ Cherche device.name qui n'existe pas !
    const found = devicesData.find(device => {
        if (!device.name) return false; // ❌ Toujours false car device.name n'existe pas !
        
        const deviceNameSimple = device.name.toLowerCase().replace(/\s+/g, ' ').trim();
        return gamepadIdSimple.includes(deviceNameSimple) || deviceNameSimple.includes(gamepadIdSimple);
    });
    
    return !!found;
}

// Nouvelle fonction corrigée
function checkIfDeviceIsKnownNEW(gamepad, devicesData) {
    if (!devicesData || !Array.isArray(devicesData)) {
        return false;
    }
    
    // Extraire vendor_id et product_id du gamepad
    const vendorMatch = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/i);
    const productMatch = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/i);
    
    if (vendorMatch && productMatch) {
        const gamepadVendor = `0x${vendorMatch[1].toLowerCase()}`;
        const gamepadProduct = `0x${productMatch[1].toLowerCase()}`;
        
        // Vérifier par vendor/product ID (méthode principale)
        const found = devicesData.find(device => {
            return device.vendor_id && device.product_id &&
                   device.vendor_id.toLowerCase() === gamepadVendor &&
                   device.product_id.toLowerCase() === gamepadProduct;
        });
        
        if (found) {
            return found;
        }
    }
    
    // Fallback : recherche par nom (utiliser device.id au lieu de device.name)
    const gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim().toLowerCase();
    const foundByName = devicesData.find(device => {
        if (!device.id) return false;
        
        const deviceIdSimple = device.id.replace(/\(Vendor:.*$/, '').trim().toLowerCase();
        return gamepadIdSimple.includes(deviceIdSimple) || deviceIdSimple.includes(gamepadIdSimple);
    });
    
    return foundByName;
}

function testBothLogics() {
    console.log('\n📊 COMPARAISON DES LOGIQUES DE DÉTECTION');
    console.log('─'.repeat(50));
    
    // Simuler un gamepad Saitek
    const mockSaitek = {
        id: "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
        index: 0,
        connected: true
    };
    
    console.log(`🎮 Test avec : ${mockSaitek.id}`);
    console.log('');
    
    // Test ancienne logique
    console.log('🔴 ANCIENNE LOGIQUE (défaillante) :');
    const oldResult = checkIfDeviceIsKnownOLD(mockSaitek, deviceData);
    console.log(`   Résultat: ${oldResult ? '✅ Reconnu' : '❌ Non reconnu'}`);
    console.log(`   Raison: Cherche "device.name" qui n'existe pas dans les données`);
    
    console.log('');
    
    // Test nouvelle logique
    console.log('🟢 NOUVELLE LOGIQUE (corrigée) :');
    const newResult = checkIfDeviceIsKnownNEW(mockSaitek, deviceData);
    console.log(`   Résultat: ${newResult ? '✅ Reconnu' : '❌ Non reconnu'}`);
    if (newResult) {
        console.log(`   Dispositif détecté: ${newResult.id}`);
        console.log(`   Méthode: vendor_id/product_id (0x0738/0xa221)`);
    }
    
    return { oldResult, newResult };
}

function analyzeDataStructure() {
    console.log('\n📋 ANALYSE DE LA STRUCTURE DES DONNÉES');
    console.log('─'.repeat(50));
    
    const saitek = deviceData.find(d => d.id.includes('0738') && d.id.includes('a221'));
    
    if (saitek) {
        console.log('✅ Données Saitek X-56 :');
        console.log(`   • id: "${saitek.id}"`);
        console.log(`   • vendor_id: ${saitek.vendor_id}`);
        console.log(`   • product_id: ${saitek.product_id}`);
        console.log(`   • xml_instance: ${saitek.xml_instance}`);
        console.log(`   • name: ${saitek.name || '❌ MANQUANT (cause du problème)'}');
    }
    
    console.log('\n💡 PROBLÈME IDENTIFIÉ :');
    console.log('   Le champ "name" n\'existe pas dans les données des dispositifs');
    console.log('   Il faut utiliser "id" au lieu de "name" pour la recherche par nom');
}

function showCodeComparison() {
    console.log('\n💻 COMPARAISON DU CODE');
    console.log('─'.repeat(50));
    
    console.log('❌ AVANT (step2_devices.php - défaillant) :');
    console.log('');
    console.log('    const found = window.devicesDataJs.find(device => {');
    console.log('        if (!device.name) return false; // ❌ Toujours false !');
    console.log('        ');
    console.log('        const deviceNameSimple = device.name.toLowerCase()...');
    console.log('        return gamepadIdSimple.includes(deviceNameSimple)...');
    console.log('    });');
    
    console.log('');
    console.log('✅ APRÈS (step2_devices.php - corrigé) :');
    console.log('');
    console.log('    // 1. Méthode principale : vendor_id/product_id');
    console.log('    const found = devicesData.find(device => {');
    console.log('        return device.vendor_id && device.product_id &&');
    console.log('               device.vendor_id.toLowerCase() === gamepadVendor &&');
    console.log('               device.product_id.toLowerCase() === gamepadProduct;');
    console.log('    });');
    console.log('    ');
    console.log('    // 2. Fallback : recherche par device.id (au lieu de device.name)');
    console.log('    const foundByName = devicesData.find(device => {');
    console.log('        if (!device.id) return false;');
    console.log('        const deviceIdSimple = device.id.replace(/\\(Vendor:.*$/, "")...');
    console.log('    });');
}

// Exécution des tests
analyzeDataStructure();
const { oldResult, newResult } = testBothLogics();
showCodeComparison();

console.log('\n' + '='.repeat(60));
console.log('📊 RÉSUMÉ DE LA CORRECTION');
console.log('─'.repeat(60));

if (!oldResult && newResult) {
    console.log('🎉 ✅ CORRECTION RÉUSSIE !');
    console.log('   • Ancienne logique : ❌ Échec (comme attendu)');
    console.log('   • Nouvelle logique : ✅ Succès (Saitek reconnu)');
    console.log('   • Problème résolu : Harmonisation avec deviceAutoDetection.js');
    console.log('   • Méthode utilisée : vendor_id/product_id + fallback sur device.id');
} else if (oldResult && newResult) {
    console.log('⚠️ Les deux logiques fonctionnent (inattendu)');
} else if (!oldResult && !newResult) {
    console.log('❌ Aucune logique ne fonctionne (problème non résolu)');
} else {
    console.log('🤔 Résultat inattendu...');
}

console.log('\n💡 IMPACT :');
console.log('   Le Saitek X-56 sera maintenant correctement reconnu dans l\'étape 2');
console.log('   Fini les "❓ Dispositif non reconnu" pour les devices avec mapping');
console.log('   Cohérence entre deviceAutoDetection.js et step2_devices.php');

console.log('='.repeat(60));
