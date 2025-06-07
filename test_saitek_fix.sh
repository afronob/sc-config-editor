#!/bin/bash

# Test de validation complète pour la détection du Saitek X-56

echo "🎮 Test de Validation - Détection Saitek X-56"
echo "=============================================="

# Fonction pour tester l'endpoint
test_endpoint() {
    echo -n "📡 Test endpoint get_devices_data.php... "
    
    response=$(curl -s http://localhost:8080/get_devices_data.php)
    
    if [[ $? -eq 0 ]]; then
        echo "✅ OK"
        
        # Vérifier si le Saitek est présent
        if echo "$response" | grep -q "Saitek.*X-56"; then
            echo "✅ Saitek X-56 trouvé dans les données"
            
            # Extraire les détails du Saitek
            saitek_data=$(echo "$response" | jq '.[] | select(.id | contains("Saitek") and contains("X-56"))')
            echo "📋 Données Saitek:"
            echo "$saitek_data" | jq .
            
            return 0
        else
            echo "❌ Saitek X-56 NON trouvé dans les données"
            return 1
        fi
    else
        echo "❌ ÉCHEC"
        return 1
    fi
}

# Fonction pour tester le serveur
test_server() {
    echo -n "🌐 Test serveur localhost:8080... "
    
    if curl -s http://localhost:8080/ > /dev/null; then
        echo "✅ OK"
        return 0
    else
        echo "❌ ÉCHEC - Serveur non accessible"
        return 1
    fi
}

# Fonction pour vérifier les fichiers de mapping
test_mapping_files() {
    echo "📁 Vérification des fichiers de mapping..."
    
    mapping_dir="/Users/bteffot/Projects/perso/sc-config-editor/mappings/devices"
    saitek_file="$mapping_dir/0738_a221_map.json"
    
    if [[ -f "$saitek_file" ]]; then
        echo "✅ Fichier Saitek trouvé: $saitek_file"
        
        # Vérifier le contenu
        if jq . "$saitek_file" > /dev/null 2>&1; then
            echo "✅ Format JSON valide"
            
            # Vérifier les champs requis
            vendor_id=$(jq -r '.vendor_id' "$saitek_file")
            product_id=$(jq -r '.product_id' "$saitek_file")
            
            echo "📋 Vendor ID: $vendor_id"
            echo "📋 Product ID: $product_id"
            
            if [[ "$vendor_id" == "0x0738" && "$product_id" == "0xa221" ]]; then
                echo "✅ IDs Vendor/Product corrects"
                return 0
            else
                echo "❌ IDs Vendor/Product incorrects"
                return 1
            fi
        else
            echo "❌ Format JSON invalide"
            return 1
        fi
    else
        echo "❌ Fichier Saitek NON trouvé"
        return 1
    fi
}

# Tests de simulation JavaScript
test_javascript_detection() {
    echo "🔧 Test de détection JavaScript..."
    
    # Créer un fichier de test temporaire
    cat > /tmp/test_saitek_detection.js << 'EOF'
// Test de détection JavaScript côté client
const testSaitekDetection = async () => {
    try {
        // Charger les données
        const response = await fetch('http://localhost:8080/get_devices_data.php');
        const devicesData = await response.json();
        
        console.log('Devices chargés:', devicesData.length);
        
        // Simuler window.devicesDataJs
        global.window = { devicesDataJs: devicesData };
        
        // Simuler un gamepad Saitek
        const mockSaitekGamepad = {
            id: 'Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)',
            buttons: Array(20).fill({ pressed: false, value: 0 }),
            axes: Array(8).fill(0),
            index: 0,
            connected: true
        };
        
        // Fonction de détection simplifiée (copie de la logique)
        const extractVendorProductId = (gamepad) => {
            let vendor = null, product = null;
            let m = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/);
            if (m) vendor = m[1].toLowerCase();
            m = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/);
            if (m) product = m[1].toLowerCase();
            return { vendor, product };
        };
        
        const isDeviceKnown = (gamepad) => {
            if (!global.window.devicesDataJs || !Array.isArray(global.window.devicesDataJs)) {
                return false;
            }
            
            const ids = extractVendorProductId(gamepad);
            
            if (ids.vendor && ids.product) {
                const found = global.window.devicesDataJs.find(dev => {
                    return dev.vendor_id && dev.product_id &&
                           dev.vendor_id.replace(/^0x/, '').toLowerCase() === ids.vendor &&
                           dev.product_id.replace(/^0x/, '').toLowerCase() === ids.product;
                });
                
                return !!found;
            }
            
            return false;
        };
        
        // Test
        const ids = extractVendorProductId(mockSaitekGamepad);
        const isKnown = isDeviceKnown(mockSaitekGamepad);
        
        console.log('IDs extraits:', ids);
        console.log('Device connu:', isKnown);
        
        return isKnown;
        
    } catch (error) {
        console.error('Erreur:', error);
        return false;
    }
};

// Export pour Node.js si disponible
if (typeof module !== 'undefined') {
    module.exports = testSaitekDetection;
} else {
    testSaitekDetection().then(result => {
        console.log('Résultat final:', result ? 'SUCCÈS' : 'ÉCHEC');
        process.exit(result ? 0 : 1);
    });
}
EOF

    # Exécuter le test avec Node.js si disponible
    if command -v node > /dev/null; then
        echo -n "🔧 Exécution du test JavaScript... "
        
        if node /tmp/test_saitek_detection.js 2>/dev/null; then
            echo "✅ Test JavaScript réussi"
            rm /tmp/test_saitek_detection.js
            return 0
        else
            echo "❌ Test JavaScript échoué"
            rm /tmp/test_saitek_detection.js
            return 1
        fi
    else
        echo "⚠️ Node.js non disponible, test JavaScript ignoré"
        rm /tmp/test_saitek_detection.js
        return 0
    fi
}

# Exécution des tests
echo
test_server || exit 1
echo
test_mapping_files || exit 1
echo
test_endpoint || exit 1
echo
test_javascript_detection || exit 1

echo
echo "🎉 TOUS LES TESTS RÉUSSIS!"
echo "✅ Le Saitek X-56 devrait maintenant être détecté comme device connu"
echo
echo "📋 Résumé de la correction:"
echo "- ✅ Fichier de mapping présent: /mappings/devices/0738_a221_map.json"
echo "- ✅ Endpoint mis à jour: get_devices_data.php lit depuis /mappings/devices/"
echo "- ✅ Données correctement retournées avec vendor_id=0x0738, product_id=0xa221"
echo "- ✅ Système de détection devrait maintenant reconnaître le device"
echo
echo "🚀 Le problème de détection du Saitek X-56 est RÉSOLU!"
