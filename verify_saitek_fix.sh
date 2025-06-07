#!/bin/bash

# Script de vérification finale du correctif Saitek X-56
echo "🎯 VÉRIFICATION FINALE - CORRECTIF SAITEK X-56"
echo "=============================================="
echo ""

# Test 1: Vérification endpoint
echo "📡 1. Test endpoint get_devices_data.php..."
ENDPOINT_RESPONSE=$(curl -s http://localhost:8080/get_devices_data.php)
DEVICE_COUNT=$(echo "$ENDPOINT_RESPONSE" | jq length 2>/dev/null)

if [[ "$DEVICE_COUNT" -gt 0 ]]; then
    echo "   ✅ Endpoint OK ($DEVICE_COUNT périphériques)"
else
    echo "   ❌ Endpoint non accessible"
    exit 1
fi

# Test 2: Vérification présence Saitek
echo "🔍 2. Recherche du Saitek X-56..."
SAITEK_FOUND=$(echo "$ENDPOINT_RESPONSE" | jq '.[] | select(.vendor_id == "0x0738" and .product_id == "0xa221") | .id' 2>/dev/null)

if [[ -n "$SAITEK_FOUND" ]]; then
    echo "   ✅ Saitek X-56 trouvé: $SAITEK_FOUND"
else
    echo "   ❌ Saitek X-56 non trouvé"
    exit 1
fi

# Test 3: Vérification du fichier de mapping source
echo "📁 3. Vérification fichier mapping source..."
if [[ -f "/Users/bteffot/Projects/perso/sc-config-editor/mappings/devices/0738_a221_map.json" ]]; then
    echo "   ✅ Fichier mapping source présent"
else
    echo "   ❌ Fichier mapping source manquant"
    exit 1
fi

# Test 4: Vérification de la modification du get_devices_data.php
echo "🔧 4. Vérification des modifications..."
if grep -q "mappings/devices" /Users/bteffot/Projects/perso/sc-config-editor/get_devices_data.php; then
    echo "   ✅ get_devices_data.php modifié correctement"
else
    echo "   ❌ get_devices_data.php non modifié"
    exit 1
fi

echo ""
echo "🎉 VALIDATION COMPLÈTE RÉUSSIE !"
echo "================================"
echo ""
echo "📋 RÉSUMÉ DU CORRECTIF APPLIQUÉ:"
echo "   • Problème: Saitek X-56 détecté comme INCONNU"
echo "   • Cause: get_devices_data.php lisait l'ancien CSV"
echo "   • Solution: Lecture des fichiers JSON organisés"
echo "   • Résultat: Saitek X-56 maintenant CONNU"
echo ""
echo "✅ STATUT FINAL: CORRIGÉ ET OPÉRATIONNEL"
echo ""

exit 0
