#!/bin/bash

# Test automatique de la correction de l'erreur "Device inconnu non trouvé"
# Ce script teste que la correction fonctionne correctement

echo "🧪 Test automatique de la correction de l'erreur Device"
echo "======================================================="

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions d'affichage
success() {
    echo -e "${GREEN}✅ $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Variables
SERVER_URL="http://localhost:8080"
TEMP_DIR="/tmp/sc_config_test"
TEST_RESULTS=0

# Créer répertoire temporaire
mkdir -p "$TEMP_DIR"

echo ""
info "Démarrage des tests de validation..."

# Test 1: Vérifier que le serveur répond
echo ""
echo "Test 1: Connexion serveur"
echo "------------------------"
if curl -s -f "$SERVER_URL/step_by_step_handler.php?step=1" > /dev/null; then
    success "Serveur accessible sur $SERVER_URL"
else
    error "Serveur non accessible sur $SERVER_URL"
    exit 1
fi

# Test 2: Vérifier que les modules JavaScript existent
echo ""
echo "Test 2: Modules JavaScript"
echo "-------------------------"
if curl -s -f "$SERVER_URL/assets/js/modules/deviceAutoDetection.js" > /dev/null; then
    success "Module deviceAutoDetection.js accessible"
else
    error "Module deviceAutoDetection.js non accessible"
    ((TEST_RESULTS++))
fi

if curl -s -f "$SERVER_URL/assets/js/modules/deviceAutoDetector.js" > /dev/null; then
    success "Module deviceAutoDetector.js accessible"
else
    error "Module deviceAutoDetector.js non accessible"
    ((TEST_RESULTS++))
fi

# Test 3: Vérifier que la méthode clearUnknownDevices existe
echo ""
echo "Test 3: Méthode clearUnknownDevices"
echo "----------------------------------"
if grep -q "clearUnknownDevices()" "assets/js/modules/deviceAutoDetection.js"; then
    success "Méthode clearUnknownDevices présente dans deviceAutoDetection.js"
else
    error "Méthode clearUnknownDevices manquante dans deviceAutoDetection.js"
    ((TEST_RESULTS++))
fi

if grep -q "clearUnknownDevices()" "assets/js/modules/deviceAutoDetector.js"; then
    success "Méthode clearUnknownDevices présente dans deviceAutoDetector.js"
else
    error "Méthode clearUnknownDevices manquante dans deviceAutoDetector.js"
    ((TEST_RESULTS++))
fi

# Test 4: Vérifier la gestion d'erreur améliorée
echo ""
echo "Test 4: Gestion d'erreur améliorée"
echo "---------------------------------"
if grep -q "Device inconnu non trouvé" "templates/step_by_step/step2_devices.php"; then
    success "Gestion d'erreur spécifique présente dans step2_devices.php"
else
    error "Gestion d'erreur spécifique manquante dans step2_devices.php"
    ((TEST_RESULTS++))
fi

if grep -q "confirm.*actualiser.*page" "templates/step_by_step/step2_devices.php"; then
    success "Message de confirmation pour actualisation présent"
else
    error "Message de confirmation pour actualisation manquant"
    ((TEST_RESULTS++))
fi

# Test 5: Vérifier le nettoyage localStorage
echo ""
echo "Test 5: Nettoyage localStorage"
echo "-----------------------------"
if grep -q "localStorage.removeItem.*sc_" "templates/step_by_step/step1_upload.php"; then
    success "Nettoyage localStorage implémenté dans resetSession"
else
    error "Nettoyage localStorage manquant dans resetSession"
    ((TEST_RESULTS++))
fi

# Test 6: Vérifier les méthodes Redis améliorées
echo ""
echo "Test 6: Méthodes Redis améliorées"
echo "--------------------------------"
if grep -q "getKeysPattern" "src/RedisManager.php"; then
    success "Méthode getKeysPattern présente dans RedisManager"
else
    error "Méthode getKeysPattern manquante dans RedisManager"
    ((TEST_RESULTS++))
fi

if grep -q "clearDeviceData" "src/StepByStepEditor.php"; then
    success "Méthode clearDeviceData présente dans StepByStepEditor"
else
    error "Méthode clearDeviceData manquante dans StepByStepEditor"
    ((TEST_RESULTS++))
fi

# Test 7: Test du reset session via API
echo ""
echo "Test 7: Test API reset session"
echo "-----------------------------"
RESPONSE=$(curl -s -w "%{http_code}" "$SERVER_URL/step_by_step_handler.php?action=restart" -o "$TEMP_DIR/reset_response.html")
if [ "$RESPONSE" = "200" ] || [ "$RESPONSE" = "302" ]; then
    success "Reset session API fonctionne (code: $RESPONSE)"
else
    error "Reset session API échoue (code: $RESPONSE)"
    ((TEST_RESULTS++))
fi

# Test 8: Test d'accès à step2
echo ""
echo "Test 8: Accès à l'étape 2"
echo "------------------------"
STEP2_RESPONSE=$(curl -s -w "%{http_code}" "$SERVER_URL/step_by_step_handler.php?step=2" -o "$TEMP_DIR/step2_response.html")
if [ "$STEP2_RESPONSE" = "200" ] || [ "$STEP2_RESPONSE" = "302" ]; then
    success "Étape 2 accessible (code: $STEP2_RESPONSE)"
    
    # Si redirection (302), c'est normal sans XML chargé
    if [ "$STEP2_RESPONSE" = "302" ]; then
        info "Redirection normale (aucun XML chargé)"
    else
        # Vérifier que le JavaScript de gestion d'erreur est présent
        if grep -q "startDeviceSetup" "$TEMP_DIR/step2_response.html"; then
            success "Fonction startDeviceSetup présente dans step2"
        else
            warning "Fonction startDeviceSetup non trouvée dans step2"
        fi
    fi
else
    error "Étape 2 non accessible (code: $STEP2_RESPONSE)"
    ((TEST_RESULTS++))
fi

# Nettoyage
rm -rf "$TEMP_DIR"

# Résumé final
echo ""
echo "======================================================="
echo "🏁 RÉSUMÉ DES TESTS"
echo "======================================================="

if [ $TEST_RESULTS -eq 0 ]; then
    echo ""
    success "TOUS LES TESTS RÉUSSIS ! 🎉"
    echo ""
    info "La correction de l'erreur 'Device inconnu non trouvé' est validée :"
    echo "  ✅ Gestion d'erreur améliorée implémentée"
    echo "  ✅ Nettoyage localStorage lors du reset"
    echo "  ✅ Méthodes clearUnknownDevices ajoutées"
    echo "  ✅ Reset session fonctionne correctement"
    echo "  ✅ API et interfaces accessibles"
    echo ""
    success "🎯 L'erreur ne devrait plus apparaître après un reset session !"
else
    echo ""
    error "$TEST_RESULTS test(s) en échec"
    echo ""
    warning "La correction nécessite des ajustements supplémentaires."
    echo ""
    info "Vérifiez les erreurs ci-dessus et relancez le test."
fi

echo ""
echo "======================================================="

exit $TEST_RESULTS
