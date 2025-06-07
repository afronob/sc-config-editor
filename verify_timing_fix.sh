#!/bin/bash

# Script de vérification du timing fix pour le Saitek X-56

echo "🔧 VÉRIFICATION CORRECTION TIMING - SAITEK X-56"
echo "=============================================="

# Vérifier que le serveur PHP fonctionne
echo "📡 Vérification du serveur..."
if ! curl -s http://localhost:8000/get_devices_data.php > /dev/null; then
    echo "❌ Serveur PHP non accessible sur localhost:8000"
    echo "💡 Démarrez le serveur avec: php -S localhost:8000"
    exit 1
fi

echo "✅ Serveur accessible"

# Vérifier que l'endpoint retourne le Saitek
echo "🔍 Vérification de l'endpoint get_devices_data.php..."
RESPONSE=$(curl -s http://localhost:8000/get_devices_data.php)
SAITEK_COUNT=$(echo "$RESPONSE" | grep -o '"vendor_id":"0x0738".*"product_id":"0xa221"' | wc -l)

if [ "$SAITEK_COUNT" -gt 0 ]; then
    echo "✅ Saitek X-56 trouvé dans les données de l'endpoint"
    TOTAL_DEVICES=$(echo "$RESPONSE" | grep -o '"vendor_id"' | wc -l)
    echo "📊 Total devices dans l'endpoint: $TOTAL_DEVICES"
else
    echo "❌ Saitek X-56 NON trouvé dans les données de l'endpoint"
    echo "🔍 Contenu de la réponse:"
    echo "$RESPONSE" | jq . 2>/dev/null || echo "$RESPONSE"
    exit 1
fi

# Créer un test JavaScript simple pour vérifier le timing
cat > /tmp/test_timing_simple.js << 'EOF'
// Test simple du timing fix
console.log('🧪 Test Timing Fix - Début');

// Simuler le comportement de bindingEditor.js
async function testTimingFix() {
    try {
        // 1. Charger les données comme le fait bindingEditor.js
        console.log('📥 Chargement des données...');
        const response = await fetch('/get_devices_data.php');
        const devicesData = await response.json();
        
        window.devicesDataJs = devicesData;
        console.log('✅ devicesDataJs chargé:', devicesData.length, 'devices');
        
        // 2. Importer SCConfigEditor
        const { SCConfigEditor } = await import('/assets/js/scConfigEditor.js');
        
        // 3. Créer l'instance
        console.log('🚀 Création SCConfigEditor...');
        const editor = new SCConfigEditor({
            devicesData: devicesData,
            useSimplifiedAnchoring: true,
            enableAutoDetection: true
        });
        
        // 4. Attendre 3 secondes puis tester la détection
        setTimeout(() => {
            console.log('🧪 Test de détection du Saitek X-56...');
            
            const mockSaitekGamepad = {
                id: "Saitek Pro Flight X-56 Rhino Throttle (Vendor: 0738 Product: a221)",
                index: 0,
                connected: true
            };
            
            const isKnown = editor.deviceAutoDetection.isDeviceKnown(mockSaitekGamepad);
            
            if (isKnown) {
                console.log('✅ SUCCÈS: Saitek X-56 détecté comme CONNU');
                document.body.innerHTML += '<div style="color: green; font-weight: bold; padding: 20px;">✅ SUCCÈS: Le timing fix fonctionne !</div>';
            } else {
                console.log('❌ ÉCHEC: Saitek X-56 encore détecté comme INCONNU');
                document.body.innerHTML += '<div style="color: red; font-weight: bold; padding: 20px;">❌ ÉCHEC: Le timing fix ne fonctionne pas</div>';
            }
        }, 3000);
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        document.body.innerHTML += '<div style="color: red; font-weight: bold; padding: 20px;">❌ ERREUR: ' + error.message + '</div>';
    }
}

// Lancer le test
testTimingFix();
EOF

# Créer une page HTML simple pour le test
cat > test_timing_simple.html << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Test Timing Fix - Simple</title>
</head>
<body>
    <h1>🧪 Test Timing Fix - Saitek X-56</h1>
    <p>Ouvrez la console pour voir les résultats...</p>
    <script type="module" src="/tmp/test_timing_simple.js"></script>
</body>
</html>
EOF

echo ""
echo "🧪 TEST CRÉÉ"
echo "============"
echo "📝 Fichier de test: test_timing_simple.html"
echo "📝 Script de test: /tmp/test_timing_simple.js"
echo ""
echo "🌐 Pour tester:"
echo "1. Ouvrez http://localhost:8000/test_timing_simple.html"
echo "2. Regardez la console du navigateur"
echo "3. Attendez 3 secondes pour voir le résultat"
echo ""
echo "✅ Si le fix fonctionne: 'SUCCÈS: Saitek X-56 détecté comme CONNU'"
echo "❌ Si le fix échoue: 'ÉCHEC: Saitek X-56 encore détecté comme INCONNU'"
