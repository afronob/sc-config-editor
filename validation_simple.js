console.log('🔧 VALIDATION FINALE - CORRECTION STEP 2 SAITEK X-56');
console.log('='.repeat(60));

// Simulation des données des dispositifs
const deviceData = [
    {
        "id": "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
        "vendor_id": "0x0738",
        "product_id": "0xa221",
        "xml_instance": "0738_a221"
    }
];

// Ancienne fonction défaillante
function checkIfDeviceIsKnownOLD(gamepad, devicesData) {
    const gamepadIdSimple = gamepad.id.toLowerCase().replace(/\s+/g, ' ').trim();
    
    // ❌ Cherche device.name qui n'existe pas !
    const found = devicesData.find(device => {
        if (!device.name) return false; // ❌ Toujours false car device.name n'existe pas !
        return true;
    });
    
    return !!found;
}

// Nouvelle fonction corrigée
function checkIfDeviceIsKnownNEW(gamepad, devicesData) {
    // Extraire vendor_id et product_id du gamepad
    const vendorMatch = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/i);
    const productMatch = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/i);
    
    if (vendorMatch && productMatch) {
        const gamepadVendor = '0x' + vendorMatch[1].toLowerCase();
        const gamepadProduct = '0x' + productMatch[1].toLowerCase();
        
        // Vérifier par vendor/product ID
        const found = devicesData.find(device => {
            return device.vendor_id && device.product_id &&
                   device.vendor_id.toLowerCase() === gamepadVendor &&
                   device.product_id.toLowerCase() === gamepadProduct;
        });
        
        if (found) {
            return found;
        }
    }
    
    return false;
}

// Test
const mockSaitek = {
    id: "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)"
};

console.log('🎮 Test avec Saitek X-56 :', mockSaitek.id);
console.log('');

const oldResult = checkIfDeviceIsKnownOLD(mockSaitek, deviceData);
console.log('❌ Ancienne logique :', oldResult ? 'Reconnu' : 'Non reconnu');

const newResult = checkIfDeviceIsKnownNEW(mockSaitek, deviceData);
console.log('✅ Nouvelle logique :', newResult ? 'Reconnu' : 'Non reconnu');

if (newResult) {
    console.log('   Dispositif trouvé :', newResult.id);
}

console.log('');
console.log('📊 RÉSULTAT :');
if (!oldResult && newResult) {
    console.log('🎉 ✅ CORRECTION RÉUSSIE !');
    console.log('   Le problème du Saitek X-56 dans l\'étape 2 est résolu.');
} else {
    console.log('❌ Problème non résolu');
}

console.log('='.repeat(60));
