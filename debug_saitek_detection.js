// Debug script pour analyser la détection du Saitek X-56
console.log('🔍 Démarrage du debug Saitek X-56...');

// Simuler le gamepad Saitek
const mockSaitekGamepad = {
    id: 'Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)',
    index: 0,
    connected: true
};

// Simuler les données de devices (comme chargées depuis le serveur)
const mockDevicesData = [
    {
        "id": "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
        "vendor_id": "0x0738",
        "product_id": "0xa221",
        "xml_instance": "0738_a221",
        "axes_map": [
            "x", "y", "z", "rotx", "roty", "rotz", "axis6", "axis7"
        ]
    }
];

console.log('📋 Données test:');
console.log('Gamepad simulé:', mockSaitekGamepad);
console.log('Devices data:', mockDevicesData);

// Fonction pour extraire vendor/product IDs (copie de la logique)
function extractVendorProductIdFromGamepad(gamepad) {
    let vendor = null, product = null;
    let m = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/);
    if (m) vendor = m[1].toLowerCase();
    m = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/);
    if (m) product = m[1].toLowerCase();
    return { vendor, product };
}

// Fonction de détection (copie de la logique)
function isDeviceKnown(gamepad, devicesData) {
    if (!devicesData || !Array.isArray(devicesData)) {
        return false;
    }

    const ids = extractVendorProductIdFromGamepad(gamepad);
    console.log('🔍 IDs extraits du gamepad:', ids);
    
    // Vérifier par vendor/product ID
    if (ids.vendor && ids.product) {
        console.log('🔍 Recherche par vendor/product ID...');
        const found = devicesData.find(dev => {
            const devVendor = dev.vendor_id ? dev.vendor_id.replace(/^0x/, '').toLowerCase() : null;
            const devProduct = dev.product_id ? dev.product_id.replace(/^0x/, '').toLowerCase() : null;
            
            console.log(`   - Device: ${dev.id}`);
            console.log(`     Vendor: "${devVendor}" vs "${ids.vendor}"`);
            console.log(`     Product: "${devProduct}" vs "${ids.product}"`);
            console.log(`     Match: ${devVendor === ids.vendor && devProduct === ids.product}`);
            
            return dev.vendor_id && dev.product_id &&
                   devVendor === ids.vendor &&
                   devProduct === ids.product;
        });
        
        if (found) {
            console.log('✅ Device trouvé par vendor/product ID!');
            return true;
        } else {
            console.log('❌ Aucun device trouvé par vendor/product ID');
        }
    }
    
    // Vérifier par nom (fallback)
    console.log('🔍 Recherche par nom (fallback)...');
    const gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim();
    console.log(`   Gamepad nom simplifié: "${gamepadIdSimple}"`);
    
    const foundByName = devicesData.find(dev => {
        const devIdSimple = dev.id.replace(/\(Vendor:.*$/, '').trim();
        console.log(`   - Device nom simplifié: "${devIdSimple}"`);
        const match = gamepadIdSimple && devIdSimple && 
               (gamepadIdSimple.indexOf(devIdSimple) !== -1 || 
                devIdSimple.indexOf(gamepadIdSimple) !== -1);
        console.log(`     Match par nom: ${match}`);
        return match;
    });
    
    if (foundByName) {
        console.log('✅ Device trouvé par nom!');
        return true;
    } else {
        console.log('❌ Aucun device trouvé par nom');
    }
    
    return false;
}

// Test
console.log('\n🧪 Test de détection:');
const result = isDeviceKnown(mockSaitekGamepad, mockDevicesData);
console.log(`\n🎯 Résultat final: ${result ? '✅ CONNU' : '❌ INCONNU'}`);

// Test avec différentes variations des IDs
console.log('\n🔬 Tests de variations:');

const variations = [
    { vendor_id: "0x0738", product_id: "0xa221" },
    { vendor_id: "0738", product_id: "a221" },
    { vendor_id: "0X0738", product_id: "0XA221" },
    { vendor_id: "0x0738", product_id: "0xA221" }
];

variations.forEach((variation, index) => {
    console.log(`\nVariation ${index + 1}:`, variation);
    const testData = [{
        ...mockDevicesData[0],
        ...variation
    }];
    const testResult = isDeviceKnown(mockSaitekGamepad, testData);
    console.log(`Résultat: ${testResult ? '✅ CONNU' : '❌ INCONNU'}`);
});
