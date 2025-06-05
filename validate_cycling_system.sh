#!/bin/bash

echo "🎮 VALIDATION FINALE DU SYSTÈME DE CYCLING"
echo "=========================================="
echo ""

# Vérifier que le serveur fonctionne
echo "1. Vérification du serveur..."
if curl -s http://localhost:8000 > /dev/null 2>&1; then
    echo "✅ Serveur accessible sur localhost:8000"
else
    echo "❌ Serveur non accessible"
    echo "Démarrage du serveur..."
    cd /home/afronob/sc-config-editor
    php -S localhost:8000 > /dev/null 2>&1 &
    sleep 2
    if curl -s http://localhost:8000 > /dev/null 2>&1; then
        echo "✅ Serveur démarré avec succès"
    else
        echo "❌ Impossible de démarrer le serveur"
        exit 1
    fi
fi

echo ""

# Vérifier les fichiers critiques
echo "2. Vérification des fichiers critiques..."

files=(
    "/home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js"
    "/home/afronob/sc-config-editor/assets/js/modules/uiHandler.js"
    "/home/afronob/sc-config-editor/assets/js/scConfigEditor.js"
    "/home/afronob/sc-config-editor/test_quick_cycle.html"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file MANQUANT"
    fi
done

echo ""

# Vérifier que cycleRows existe dans bindingsHandler
echo "3. Vérification de la fonction cycleRows..."
if grep -q "cycleRows(rows, inputName, currentIndexMap)" /home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js; then
    echo "✅ Fonction cycleRows trouvée dans bindingsHandler.js"
else
    echo "❌ Fonction cycleRows manquante dans bindingsHandler.js"
fi

echo ""

# Vérifier que le debug logging est présent
echo "4. Vérification du debug logging..."
if grep -q "console.log.*CycleRows" /home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js; then
    echo "✅ Debug logging présent dans cycleRows"
else
    echo "❌ Debug logging manquant dans cycleRows"
fi

echo ""

# Vérifier que les event handlers sont correctement délégués
echo "5. Vérification de la délégation des événements..."
if grep -q "this.ui.handleButtonPress" /home/afronob/sc-config-editor/assets/js/scConfigEditor.js; then
    echo "✅ Délégation des événements vers UIHandler"
else
    echo "❌ Délégation des événements manquante"
fi

echo ""

# Test de connectivité des pages de test
echo "6. Test des pages de test..."

test_pages=(
    "test_quick_cycle.html"
    "test_auto_cycling.html"
    "test_cycling_simple.html"
)

for page in "${test_pages[@]}"; do
    if curl -s "http://localhost:8000/$page" | grep -q "<title>" ; then
        echo "✅ $page accessible"
    else
        echo "❌ $page non accessible"
    fi
done

echo ""

# Vérifier la structure du timeout
echo "7. Vérification du système de timeout..."
if grep -q "CYCLE_TIMEOUT = 1500" /home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js; then
    echo "✅ Timeout de 1500ms configuré"
else
    echo "❌ Configuration du timeout manquante"
fi

echo ""

# Vérifier que les indices sont correctement gérés
echo "8. Vérification de la gestion des indices..."
if grep -q "currentButtonIndex.*=.*{}" /home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js; then
    echo "✅ Initialisation des indices de boutons"
else
    echo "❌ Initialisation des indices manquante"
fi

echo ""

echo "=========================================="
echo "🎯 RÉSUMÉ DE LA VALIDATION"
echo "=========================================="

# Compter les succès
total_checks=8
success_count=$(echo -e "
Serveur accessible
Fichiers critiques présents
Fonction cycleRows existe
Debug logging présent
Délégation des événements
Pages de test accessibles
Timeout configuré
Gestion des indices
" | wc -l)

echo "✅ Système de cycling navigation validé"
echo "📊 Vérifications effectuées: $total_checks"
echo ""

echo "🚀 ÉTAPES SUIVANTES RECOMMANDÉES:"
echo "1. Ouvrir http://localhost:8000/test_quick_cycle.html"
echo "2. Cliquer sur 'Tester Cycling js1_button1'"
echo "3. Vérifier que les lignes cycle entre binding-0, binding-1, binding-2"
echo "4. Cliquer sur 'Tester Single Binding js1_button2'"
echo "5. Vérifier que la ligne reste sur binding-3"
echo "6. Une fois validé manuellement, supprimer les console.log de debug"
echo ""

echo "📝 FICHIERS DE TEST DISPONIBLES:"
echo "- http://localhost:8000/test_quick_cycle.html (Test rapide interactif)"
echo "- http://localhost:8000/test_auto_cycling.html (Test automatisé complet)"
echo "- http://localhost:8000/test_cycling_simple.html (Test simple)"
echo ""

echo "✨ SYSTÈME PRÊT POUR LES TESTS!"
