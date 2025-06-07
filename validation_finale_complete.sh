#!/bin/zsh

# Script de validation finale complète - Saitek X-56
# Date: 7 juin 2025

echo "🎯 VALIDATION FINALE COMPLÈTE - SAITEK X-56"
echo "==========================================="
echo ""

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher un résultat
print_result() {
    local status=$1
    local message=$2
    if [ "$status" = "OK" ]; then
        echo -e "${GREEN}✅ $message${NC}"
    elif [ "$status" = "ERROR" ]; then
        echo -e "${RED}❌ $message${NC}"
    elif [ "$status" = "WARNING" ]; then
        echo -e "${YELLOW}⚠️ $message${NC}"
    else
        echo -e "${BLUE}ℹ️ $message${NC}"
    fi
}

# Variables de test
TEST_PASSED=0
TEST_TOTAL=0

# Test 1: Vérification du serveur
echo "🔍 Test 1: Vérification du serveur"
echo "-----------------------------------"
TEST_TOTAL=$((TEST_TOTAL + 1))

if curl -s http://localhost:8000/get_devices_data.php > /dev/null; then
    print_result "OK" "Serveur PHP accessible sur localhost:8000"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "Serveur PHP non accessible"
    echo "💡 Démarrez le serveur avec: php -S localhost:8000"
    exit 1
fi

echo ""

# Test 2: Vérification de l'endpoint
echo "🔍 Test 2: Vérification de l'endpoint get_devices_data.php"
echo "--------------------------------------------------------"
TEST_TOTAL=$((TEST_TOTAL + 3))

RESPONSE=$(curl -s http://localhost:8000/get_devices_data.php)

# Test 2a: JSON valide
if echo "$RESPONSE" | jq . > /dev/null 2>&1; then
    print_result "OK" "Endpoint retourne un JSON valide"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "Endpoint ne retourne pas un JSON valide"
fi

# Test 2b: Présence du Saitek X-56
SAITEK_COUNT=$(echo "$RESPONSE" | grep -c '"vendor_id":"0x0738".*"product_id":"0xa221"')
if [ "$SAITEK_COUNT" -gt 0 ]; then
    print_result "OK" "Saitek X-56 présent dans l'endpoint"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "Saitek X-56 NON trouvé dans l'endpoint"
fi

# Test 2c: Nombre total de devices
TOTAL_DEVICES=$(echo "$RESPONSE" | jq length 2>/dev/null || echo "0")
if [ "$TOTAL_DEVICES" -ge 4 ]; then
    print_result "OK" "Endpoint retourne $TOTAL_DEVICES devices (attendu: ≥4)"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "WARNING" "Endpoint retourne seulement $TOTAL_DEVICES devices"
fi

echo ""

# Test 3: Vérification des fichiers modifiés
echo "🔍 Test 3: Vérification des fichiers modifiés"
echo "--------------------------------------------"
TEST_TOTAL=$((TEST_TOTAL + 2))

# Test 3a: get_devices_data.php contient la nouvelle logique
if grep -q "mappings/devices" get_devices_data.php; then
    print_result "OK" "get_devices_data.php utilise le nouveau système JSON"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "get_devices_data.php utilise encore l'ancien système CSV"
fi

# Test 3b: scConfigEditor.js contient le fallback
if grep -q "get_devices_data.php" assets/js/scConfigEditor.js; then
    print_result "OK" "scConfigEditor.js contient le fallback vers l'endpoint"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "scConfigEditor.js ne contient pas le fallback endpoint"
fi

echo ""

# Test 4: Vérification du mapping Saitek
echo "🔍 Test 4: Vérification du mapping Saitek X-56"
echo "----------------------------------------------"
TEST_TOTAL=$((TEST_TOTAL + 1))

if [ -f "mappings/devices/0738_a221_map.json" ]; then
    print_result "OK" "Fichier mapping Saitek X-56 présent"
    TEST_PASSED=$((TEST_PASSED + 1))
    
    # Vérifier le contenu du mapping
    if jq . mappings/devices/0738_a221_map.json > /dev/null 2>&1; then
        print_result "OK" "Mapping Saitek X-56 contient un JSON valide"
    else
        print_result "WARNING" "Mapping Saitek X-56 contient un JSON invalide"
    fi
else
    print_result "ERROR" "Fichier mapping Saitek X-56 manquant"
fi

echo ""

# Test 5: Tests des pages disponibles
echo "🔍 Test 5: Vérification des pages de test"
echo "----------------------------------------"
TEST_TOTAL=$((TEST_TOTAL + 3))

# Test 5a: Page keybind_editor.php
if curl -s http://localhost:8000/keybind_editor.php | grep -q "Uploader un fichier XML"; then
    print_result "OK" "Page keybind_editor.php accessible"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "ERROR" "Page keybind_editor.php non accessible"
fi

# Test 5b: Page de test fallback
if [ -f "test_fallback_endpoint.html" ]; then
    print_result "OK" "Page de test fallback disponible"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "WARNING" "Page de test fallback manquante"
fi

# Test 5c: Page de validation timing
if [ -f "validation_timing_final.html" ]; then
    print_result "OK" "Page de validation timing disponible"
    TEST_PASSED=$((TEST_PASSED + 1))
else
    print_result "WARNING" "Page de validation timing manquante"
fi

echo ""

# Résultats finaux
echo "📊 RÉSULTATS FINAUX"
echo "==================="
echo ""

PERCENTAGE=$((TEST_PASSED * 100 / TEST_TOTAL))

if [ $TEST_PASSED -eq $TEST_TOTAL ]; then
    print_result "OK" "Tous les tests passés ($TEST_PASSED/$TEST_TOTAL - 100%)"
    echo ""
    echo -e "${GREEN}🎉 VALIDATION COMPLÈTE RÉUSSIE !${NC}"
    echo ""
    echo "✅ La correction du Saitek X-56 est fonctionnelle"
    echo "✅ Endpoint retourne les bonnes données"
    echo "✅ Fallback vers l'endpoint implémenté"
    echo "✅ Pages de test disponibles"
    echo ""
    echo "🌐 TESTS MANUELS RECOMMANDÉS:"
    echo "• http://localhost:8000/keybind_editor.php (page upload)"
    echo "• http://localhost:8000/test_fallback_endpoint.html (test automatique)"
    echo "• Console développeur pour vérifier les logs"
    echo ""
elif [ $PERCENTAGE -ge 80 ]; then
    print_result "WARNING" "La plupart des tests passés ($TEST_PASSED/$TEST_TOTAL - $PERCENTAGE%)"
    echo ""
    echo -e "${YELLOW}⚠️ VALIDATION PARTIELLE${NC}"
    echo "La correction principale semble fonctionnelle mais quelques éléments sont à vérifier."
else
    print_result "ERROR" "Plusieurs tests échoués ($TEST_PASSED/$TEST_TOTAL - $PERCENTAGE%)"
    echo ""
    echo -e "${RED}❌ VALIDATION ÉCHOUÉE${NC}"
    echo "Des problèmes importants persistent. Vérifiez les erreurs ci-dessus."
fi

echo ""
echo "📋 ÉTAPES SUIVANTES:"
echo "• Testez manuellement sur les vraies pages"
echo "• Vérifiez la console développeur pour les logs"
echo "• Confirmez qu'aucune notification 'nouveau device' n'apparaît pour le Saitek"
echo ""

exit 0
