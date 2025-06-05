#!/bin/bash

echo "🔍 DIAGNOSTIC RAPIDE - SYSTÈME CYCLING"
echo "======================================"

echo ""
echo "1. Vérification des corrections appliquées..."

# Vérifier que la double écoute a été supprimée
echo -n "   ✓ Double écoute supprimée dans UIHandler: "
if grep -q "Event listeners removed" /home/afronob/sc-config-editor/assets/js/modules/uiHandler.js; then
    echo "✅ OK"
else
    echo "❌ MANQUANT"
fi

# Vérifier la protection anti-spam
echo -n "   ✓ Protection anti-spam ajoutée: "
if grep -q "MIN_CALL_INTERVAL" /home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js; then
    echo "✅ OK"
else
    echo "❌ MANQUANT"
fi

# Vérifier la gestion des timeouts
echo -n "   ✓ Gestion des timeouts améliorée: "
if grep -q "doubleTapTimeouts" /home/afronob/sc-config-editor/assets/js/modules/gamepadHandler.js; then
    echo "✅ OK"
else
    echo "❌ MANQUANT"
fi

echo ""
echo "2. État du serveur de développement..."
if lsof -i :8000 > /dev/null 2>&1; then
    echo "   ✅ Serveur actif sur port 8000"
else
    echo "   ❌ Serveur non démarré"
fi

echo ""
echo "3. Pages de test disponibles..."
test_pages=("test_antispam_fix.html" "test_quick_cycle.html" "test_auto_cycling.html")
for page in "${test_pages[@]}"; do
    if [ -f "/home/afronob/sc-config-editor/$page" ]; then
        echo "   ✅ $page"
    else
        echo "   ❌ $page MANQUANT"
    fi
done

echo ""
echo "4. Résumé des corrections..."
echo "   🛡️ Protection anti-spam: 50ms minimum entre appels"
echo "   🎯 Double écoute supprimée: 1 seul traitement par événement"
echo "   ⏱️ Timeouts gérés: Annulation des timeouts multiples"
echo "   📝 Debug logging: Actif pour traçabilité"

echo ""
echo "5. Tests recommandés..."
echo "   1. Ouvrir: http://localhost:8000/test_antispam_fix.html"
echo "   2. Cliquer sur 'Test Protection Spam'"
echo "   3. Cliquer sur 'Simuler Spam (10x)'"
echo "   4. Vérifier qu'il n'y a plus de logs répétés"
echo "   5. Tester avec un vrai gamepad sur: http://localhost:8000"

echo ""
echo "6. Une fois validé..."
echo "   Exécuter: ./cleanup_debug_logs.sh"
echo "   Pour supprimer les console.log de debug"

echo ""
echo "🎯 DIAGNOSTIC TERMINÉ"
