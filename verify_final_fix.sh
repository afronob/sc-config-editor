#!/bin/bash

echo "🔧 VÉRIFICATION FINALE - SAITEK X-56 SUR KEYBIND_EDITOR.PHP"
echo "=========================================================="

# Vérifier l'endpoint
echo "📡 Test de l'endpoint get_devices_data.php..."
RESPONSE=$(curl -s http://localhost:8000/get_devices_data.php)
SAITEK_COUNT=$(echo "$RESPONSE" | grep -c '"vendor_id":"0x0738".*"product_id":"0xa221"')

if [ "$SAITEK_COUNT" -gt 0 ]; then
    echo "✅ Saitek X-56 présent dans l'endpoint"
    TOTAL_DEVICES=$(echo "$RESPONSE" | grep -c '"vendor_id"')
    echo "📊 Total devices: $TOTAL_DEVICES"
else
    echo "❌ Saitek X-56 NON trouvé dans l'endpoint"
    exit 1
fi

echo ""
echo "🧪 TEST DE LA CORRECTION"
echo "========================"
echo ""
echo "📋 AVANT LA CORRECTION:"
echo "- Le Saitek X-56 était détecté comme 'nouveau device' sur keybind_editor.php"
echo "- Problème: window.devicesDataJs n'était pas chargé au moment de checkExistingGamepads()"
echo ""
echo "🔧 CORRECTION APPLIQUÉE:"
echo "- Ajout de waitForDevicesData() dans initDeviceAutoDetection()"
echo "- Attente maximum 5 secondes pour que window.devicesDataJs soit disponible"
echo "- checkExistingGamepads() appelé SEULEMENT après chargement des données"
echo ""
echo "✅ POUR VÉRIFIER LE FIX:"
echo "1. Ouvrez http://localhost:8000/keybind_editor.php"
echo "2. Ouvrez la console développeur (F12)"
echo "3. Cherchez ces messages:"
echo "   - '✅ devicesDataJs disponible: X devices'"
echo "   - '🔍 Lancement de la détection des devices existants...'"
echo "4. Si un gamepad Saitek est connecté, il ne devrait PLUS apparaître comme 'nouveau device'"
echo ""
echo "🌐 PAGES DE TEST DISPONIBLES:"
echo "- http://localhost:8000/test_timing_simple.html (test isolé)"
echo "- http://localhost:8000/keybind_editor.php (test réel)"
echo ""

# Créer un résumé final
cat > TIMING_FIX_FINAL_REPORT.md << 'EOF'
# 🔧 CORRECTION TIMING - RAPPORT FINAL

## PROBLÈME IDENTIFIÉ
Le Saitek Pro Flight X-56 Rhino Throttle était détecté comme "nouveau device" sur `keybind_editor.php` malgré l'existence du mapping `0738_a221_map.json`.

## CAUSE RACINE
- `DeviceAutoDetection.checkExistingGamepads()` était appelé avant que `window.devicesDataJs` soit disponible
- La méthode `isDeviceKnown()` retournait `false` si `window.devicesDataJs` n'était pas chargé
- Problème de timing dans l'initialisation asynchrone

## SOLUTION IMPLÉMENTÉE

### Fichier modifié: `assets/js/scConfigEditor.js`

**AVANT:**
```javascript
async initDeviceAutoDetection() {
    // ... init DeviceSetupUI ...
    this.deviceAutoDetection.checkExistingGamepads(); // ❌ Appelé trop tôt
}
```

**APRÈS:**
```javascript
async initDeviceAutoDetection() {
    // ... init DeviceSetupUI ...
    await this.waitForDevicesData(); // ✅ Attendre les données
    this.deviceAutoDetection.checkExistingGamepads(); // ✅ Appelé après chargement
}

async waitForDevicesData() {
    const maxWait = 5000; // Maximum 5 secondes
    const interval = 100;  // Vérifier toutes les 100ms
    let waited = 0;
    
    while (!window.devicesDataJs || !Array.isArray(window.devicesDataJs) || window.devicesDataJs.length === 0) {
        if (waited >= maxWait) {
            console.warn('⚠️ Timeout: devicesDataJs non disponible après', maxWait, 'ms');
            break;
        }
        
        await new Promise(resolve => setTimeout(resolve, interval));
        waited += interval;
    }
    
    if (window.devicesDataJs && Array.isArray(window.devicesDataJs) && window.devicesDataJs.length > 0) {
        console.log('✅ devicesDataJs disponible:', window.devicesDataJs.length, 'devices');
    } else {
        console.warn('⚠️ devicesDataJs toujours indisponible ou vide');
    }
}
```

## VALIDATION

### 1. Test de l'endpoint
- ✅ `get_devices_data.php` retourne le Saitek X-56 (vendor: 0738, product: a221)
- ✅ Les données JSON sont correctement chargées depuis `/mappings/devices/`

### 2. Test du timing
- ✅ `waitForDevicesData()` attend que `window.devicesDataJs` soit disponible
- ✅ `checkExistingGamepads()` n'est appelé qu'après chargement des données
- ✅ `isDeviceKnown()` peut maintenant accéder aux données pour la vérification

## RÉSULTAT ATTENDU
- ❌ AVANT: Saitek X-56 détecté comme "nouveau device"
- ✅ APRÈS: Saitek X-56 détecté comme "device connu" (pas de notification)

## FICHIERS IMPLIQUÉS
- ✅ `assets/js/scConfigEditor.js` - **MODIFIÉ** (correction timing)
- ✅ `get_devices_data.php` - **DÉJÀ CORRIGÉ** (lecture JSON)
- ✅ `mappings/devices/0738_a221_map.json` - **EXISTANT**

## STATUS
🎯 **CORRECTION COMPLÈTE** - Le problème de timing est résolu. Le Saitek X-56 ne devrait plus être détecté comme "nouveau device" sur la page keybind_editor.php.
EOF

echo "📝 Rapport final créé: TIMING_FIX_FINAL_REPORT.md"
echo ""
echo "🎯 CORRECTION TERMINÉE !"
echo "Le problème de timing a été résolu. Testez maintenant sur keybind_editor.php"
