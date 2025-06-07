#!/bin/bash

# Vérification Rapide du Système de Mapping
# Usage: ./quick_check.sh

echo "🎮 Vérification Rapide du Système"
echo "=================================="

# Compteurs
ok=0
issues=0

check() {
    if [ $2 -eq 0 ]; then
        echo "✅ $1"
        ((ok++))
    else
        echo "❌ $1"
        ((issues++))
    fi
}

# Vérifications essentielles
[ -d "mappings/devices" ] && device_files=$(ls mappings/devices/*.json 2>/dev/null | wc -l | tr -d ' ') || device_files=0
[ -d "mappings/templates" ] && template_files=$(ls mappings/templates/*.json 2>/dev/null | wc -l | tr -d ' ') || template_files=0

check "Structure mappings/ ($device_files devices, $template_files templates)" $([[ $device_files -gt 0 && $template_files -gt 0 ]] && echo 0 || echo 1)

# Validation
validation_result=$(./validate_mappings.sh 2>/dev/null | tail -1)
check "Validation des mappings" $([[ "$validation_result" == *"réussie"* ]] && echo 0 || echo 1)

# Serveur
pgrep -f "php.*8080" > /dev/null
check "Serveur actif (port 8080)" $?

# Accès web
curl -s -m 3 http://localhost:8080 > /dev/null 2>&1
check "Interface web accessible" $?

echo ""
echo "📊 Résultat: $ok/4 vérifications réussies"

if [ $issues -eq 0 ]; then
    echo "🎉 Système opérationnel !"
    echo "   → http://localhost:8080"
else
    echo "⚠️  $issues problème(s) détecté(s)"
    echo "   → Consultez la documentation pour résoudre"
fi
