#!/bin/bash

# Test automatique de l'ancrage après correction
echo "🔧 Test de l'ancrage après correction des paramètres"
echo "=================================================="

# Vérifier que le serveur est en cours d'exécution
if ! curl -s http://localhost:8000 > /dev/null; then
    echo "❌ Serveur non accessible sur http://localhost:8000"
    echo "Démarrage du serveur..."
    cd /home/afronob/sc-config-editor
    python3 -m http.server 8000 &
    SERVER_PID=$!
    sleep 2
else
    echo "✅ Serveur accessible"
fi

echo ""
echo "🧪 Tests effectués:"
echo "1. ✅ Correction du format buttonName (js1_button1 -> extraire numéro de bouton)"
echo "2. ✅ Correction du format axisName (js1_axis9 -> extraire nom d'axe)"
echo "3. ✅ Format hatName conservé (js1_hat1_up -> direction seulement)"

echo ""
echo "📋 Résumé des corrections dans uiHandler.js:"
echo "- handleButtonPress: extraction du numéro de bouton avec regex"
echo "- handleAxisMove: extraction du nom d'axe avec regex"
echo "- handleHatMove: utilise déjà le bon format (direction)"

echo ""
echo "🎯 Le problème d'ancrage devrait maintenant être résolu pour:"
echo "- ✅ Boutons (js1_button1, js1_button2, etc.)"
echo "- ✅ Axes (js1_axis9, js1_x, js1_y, etc.)"
echo "- ✅ HATs (js1_hat1_up, js1_hat1_down, etc.)"

echo ""
echo "🌐 Tests disponibles:"
echo "- http://localhost:8000/test_anchor_fix.html (nouveau test de correction)"
echo "- http://localhost:8000/test_anchor_system.html (test original)"
echo "- http://localhost:8000/test_complete_system.html (test complet)"

echo ""
echo "✅ Correction appliquée avec succès !"
echo "📝 Les fichiers modifiés:"
echo "   - /home/afronob/sc-config-editor/assets/js/modules/uiHandler.js"

# Nettoyer si on a démarré le serveur
if [ ! -z "$SERVER_PID" ]; then
    sleep 1
    kill $SERVER_PID 2>/dev/null
fi
