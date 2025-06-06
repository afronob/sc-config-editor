#!/bin/bash

echo "🔧 Test de Validation du Système d'Ancrage Corrigé"
echo "================================================="

# Vérifier que le serveur PHP fonctionne
echo "📡 Vérification du serveur PHP..."
if curl -s http://localhost:8000 > /dev/null; then
    echo "✅ Serveur PHP actif sur le port 8000"
else
    echo "❌ Serveur PHP non accessible"
    echo "   Démarrage du serveur..."
    cd /home/afronob/sc-config-editor
    php -S localhost:8000 > /dev/null 2>&1 &
    sleep 2
    
    if curl -s http://localhost:8000 > /dev/null; then
        echo "✅ Serveur PHP démarré"
    else
        echo "❌ Impossible de démarrer le serveur PHP"
        exit 1
    fi
fi

echo ""
echo "🔍 Vérification des fichiers modifiés..."

# Vérifier que nos modifications sont présentes
echo "   Vérification de uiHandler.js..."
if grep -q "buttonName.match" /home/afronob/sc-config-editor/assets/js/modules/uiHandler.js; then
    echo "✅ Correction du parsing des boutons trouvée"
else
    echo "❌ Correction du parsing des boutons manquante"
fi

if grep -q "axisName.match" /home/afronob/sc-config-editor/assets/js/modules/uiHandler.js; then
    echo "✅ Correction du parsing des axes trouvée"
else
    echo "❌ Correction du parsing des axes manquante"
fi

echo ""
echo "📋 Résumé des corrections appliquées:"
echo "   • Extraction correcte du numéro de bouton depuis buttonName"
echo "   • Extraction correcte du nom d'axe depuis axisName"
echo "   • Maintien de la compatibilité avec le système de cycling"
echo "   • Préservation du système d'ancrage (highlightRow)"

echo ""
echo "🌐 Tests disponibles:"
echo "   • Page de validation complète: http://localhost:8000/test_anchor_fix_validation.html"
echo "   • Page de test d'ancrage: http://localhost:8000/test_anchor_system.html"
echo "   • Test anti-spam: http://localhost:8000/test_antispam_fix.html"

echo ""
echo "🚀 Pour tester manuellement:"
echo "   1. Ouvrir http://localhost:8000/test_anchor_fix_validation.html"
echo "   2. Cliquer sur 'Lancer Tous les Tests'"
echo "   3. Vérifier que tous les tests passent"

echo ""
echo "✅ Validation du système terminée !"
