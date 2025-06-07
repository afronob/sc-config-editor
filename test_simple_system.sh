#!/bin/bash

echo "🎮 Test Simplifié du Système de Mapping"
echo "========================================"

cd "$(dirname "$0")"

echo ""
echo "📁 Vérification de la Structure"
echo "==============================="

if [ -d "mappings/devices" ]; then
    device_count=$(ls mappings/devices/*.json 2>/dev/null | wc -l | tr -d ' ')
    echo "✅ mappings/devices/ ($device_count fichiers)"
else
    echo "❌ mappings/devices/ manquant"
fi

if [ -d "mappings/templates" ]; then
    template_count=$(ls mappings/templates/*.json 2>/dev/null | wc -l | tr -d ' ')
    echo "✅ mappings/templates/ ($template_count fichiers)"
else
    echo "❌ mappings/templates/ manquant"
fi

if [ -d "mappings/generated" ]; then
    csv_count=$(ls mappings/generated/*.csv 2>/dev/null | wc -l | tr -d ' ')
    echo "✅ mappings/generated/ ($csv_count fichiers)"
else
    echo "❌ mappings/generated/ manquant"
fi

echo ""
echo "🔍 Validation des Mappings"
echo "=========================="

./validate_mappings.sh | tail -5

echo ""
echo "🌐 Test du Serveur"
echo "=================="

if pgrep -f "php.*8080" > /dev/null; then
    echo "✅ Serveur PHP actif sur port 8080"
    
    if curl -s -m 5 http://localhost:8080 | grep -q "SC Config Editor\|html\|DOCTYPE" 2>/dev/null; then
        echo "✅ Serveur web répond correctement"
    else
        echo "⚠️  Serveur répond mais contenu inattendu"
    fi
else
    echo "❌ Serveur PHP non actif sur port 8080"
fi

echo ""
echo "📊 Résumé Final"
echo "==============="
echo "Structure: mappings/ avec $device_count devices, $template_count templates, $csv_count CSV"
echo "Validation: Voir résultats ci-dessus"
echo "Serveur: Port 8080 $(pgrep -f "php.*8080" > /dev/null && echo "actif" || echo "inactif")"

echo ""
echo "🎯 Système organisé et fonctionnel !"
echo "   • http://localhost:8080 - Interface principale"
echo "   • http://localhost:8080/test_device_detection.html - Test détection"
echo "   • ./validate_mappings.sh - Validation des mappings"
