#!/bin/bash

# Script de validation finale de la correction du bouton Configurer
echo "🧪 VALIDATION FINALE - Correction bouton Configurer"
echo "=================================================="

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo ""
echo -e "${BLUE}1. Vérification du serveur...${NC}"

# Vérifier que le serveur est accessible
if curl -s http://localhost:8080 > /dev/null; then
    echo -e "${GREEN}✅ Serveur accessible sur http://localhost:8080${NC}"
else
    echo -e "${RED}❌ Serveur non accessible. Démarrez le serveur avec './start-server.sh'${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}2. Vérification des fichiers modifiés...${NC}"

# Vérifier que les corrections sont présentes
if grep -q "import { DeviceSetupUI }" /Users/bteffot/Projects/perso/sc-config-editor/templates/step_by_step/step2_devices.php; then
    echo -e "${GREEN}✅ Import DeviceSetupUI présent dans step2_devices.php${NC}"
else
    echo -e "${RED}❌ Import DeviceSetupUI manquant${NC}"
fi

if grep -q "deviceSetupUI = new DeviceSetupUI" /Users/bteffot/Projects/perso/sc-config-editor/templates/step_by_step/step2_devices.php; then
    echo -e "${GREEN}✅ Initialisation DeviceSetupUI présente${NC}"
else
    echo -e "${RED}❌ Initialisation DeviceSetupUI manquante${NC}"
fi

if grep -q "deviceSetupUI.startSetup(deviceKey)" /Users/bteffot/Projects/perso/sc-config-editor/templates/step_by_step/step2_devices.php; then
    echo -e "${GREEN}✅ Appel deviceSetupUI.startSetup() présent${NC}"
else
    echo -e "${RED}❌ Appel deviceSetupUI.startSetup() manquant${NC}"
fi

if grep -q "deviceAutoDetection.unknownDevices.set(deviceKey, deviceInfo)" /Users/bteffot/Projects/perso/sc-config-editor/templates/step_by_step/step2_devices.php; then
    echo -e "${GREEN}✅ Synchronisation dispositifs présente${NC}"
else
    echo -e "${RED}❌ Synchronisation dispositifs manquante${NC}"
fi

echo ""
echo -e "${BLUE}3. Test des endpoints nécessaires...${NC}"

# Tester get_devices_data.php
if curl -s http://localhost:8080/get_devices_data.php | grep -q '\['; then
    echo -e "${GREEN}✅ get_devices_data.php fonctionne${NC}"
else
    echo -e "${RED}❌ get_devices_data.php ne fonctionne pas${NC}"
fi

# Tester step2
if curl -s http://localhost:8080/step_by_step_handler.php?step=2 | grep -q "Détection automatique"; then
    echo -e "${GREEN}✅ Page step2 accessible${NC}"
else
    echo -e "${RED}❌ Page step2 non accessible${NC}"
fi

echo ""
echo -e "${BLUE}4. Vérification des modules JavaScript...${NC}"

# Vérifier que les modules existent
if [ -f "/Users/bteffot/Projects/perso/sc-config-editor/assets/js/modules/deviceAutoDetection.js" ]; then
    echo -e "${GREEN}✅ Module deviceAutoDetection.js présent${NC}"
else
    echo -e "${RED}❌ Module deviceAutoDetection.js manquant${NC}"
fi

if [ -f "/Users/bteffot/Projects/perso/sc-config-editor/assets/js/modules/deviceSetupUI.js" ]; then
    echo -e "${GREEN}✅ Module deviceSetupUI.js présent${NC}"
else
    echo -e "${RED}❌ Module deviceSetupUI.js manquant${NC}"
fi

# Vérifier que startSetup existe dans deviceSetupUI.js
if grep -q "startSetup(deviceKey)" /Users/bteffot/Projects/perso/sc-config-editor/assets/js/modules/deviceSetupUI.js; then
    echo -e "${GREEN}✅ Méthode startSetup() présente dans DeviceSetupUI${NC}"
else
    echo -e "${RED}❌ Méthode startSetup() manquante dans DeviceSetupUI${NC}"
fi

echo ""
echo -e "${BLUE}5. Résumé des corrections apportées...${NC}"
echo -e "${GREEN}✅ Import et initialisation de DeviceSetupUI${NC}"
echo -e "${GREEN}✅ Remplacement de l'alert par deviceSetupUI.startSetup()${NC}"
echo -e "${GREEN}✅ Synchronisation des dispositifs avec le module${NC}"
echo -e "${GREEN}✅ Gestion d'erreur améliorée${NC}"

echo ""
echo -e "${YELLOW}📋 INSTRUCTIONS DE TEST MANUEL :${NC}"
echo ""
echo "1. Ouvrez http://localhost:8080/step_by_step_handler.php?step=2"
echo "2. Cliquez sur 'Détecter les dispositifs connectés'"
echo "3. Si un dispositif inconnu est détecté, cliquez sur 'Configurer'"
echo "4. Résultat attendu : Interface de configuration (modal) s'ouvre"
echo "5. Résultat incorrect : Message d'erreur 'Ce dispositif n'est plus disponible'"

echo ""
echo -e "${YELLOW}🧪 TEST AUTOMATIQUE DISPONIBLE :${NC}"
echo "Ouvrez http://localhost:8080/test_bouton_configurer_final.html"

echo ""
echo -e "${GREEN}🎉 VALIDATION TERMINÉE${NC}"
echo ""
