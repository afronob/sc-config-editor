#!/bin/bash

# Test Complet du Système de Mapping Organisé
# Vérifie toutes les fonctionnalités après réorganisation

echo "🎮 Test Complet du Système de Mapping Organisé"
echo "=============================================="

cd "$(dirname "$0")"

# Compteurs de résultats
total_tests=0
passed_tests=0
failed_tests=0

# Fonction pour les tests
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo -n "🔍 $test_name... "
    ((total_tests++))
    
    if eval "$test_command" > /dev/null 2>&1; then
        echo "✅ Réussi"
        ((passed_tests++))
    else
        echo "❌ Échoué"
        ((failed_tests++))
    fi
}

echo ""
echo "📁 Phase 1: Vérification de la Structure"
echo "========================================="

run_test "Répertoire mappings/devices" "[ -d 'mappings/devices' ]"
run_test "Répertoire mappings/templates" "[ -d 'mappings/templates' ]"
run_test "Répertoire mappings/generated" "[ -d 'mappings/generated' ]"
run_test "Répertoire mappings/validation" "[ -d 'mappings/validation' ]"
run_test "Documentation mappings" "[ -f 'mappings/README.md' ]"
run_test "Guide de référence rapide" "[ -f 'mappings/QUICK_REFERENCE.md' ]"

echo ""
echo "📦 Phase 2: Validation des Mappings"
echo "===================================="

# Exécuter la validation
if ./validate_mappings.sh | grep -q "Validation réussie"; then
    echo "✅ Validation des mappings réussie"
    ((passed_tests++))
else
    echo "❌ Validation des mappings échouée"
    ((failed_tests++))
fi
((total_tests++))

echo ""
echo "🔧 Phase 3: Test des Scripts d'Automation"
echo "=========================================="

run_test "Script de validation exécutable" "[ -x './validate_mappings.sh' ]"
run_test "Script de normalisation exécutable" "[ -x './normalize_mapping_names.sh' ]"
run_test "Script de migration exécutable" "[ -x './migrate_mappings.sh' ]"
run_test "Script de démarrage serveur exécutable" "[ -x './start-server.sh' ]"

echo ""
echo "📝 Phase 4: Vérification des Fichiers de Mapping"
echo "================================================="

# Compter les fichiers de mapping
device_count=$(find mappings/devices -name "*.json" | wc -l | tr -d ' ')
template_count=$(find mappings/templates -name "*.json" | wc -l | tr -d ' ')
csv_count=$(find mappings/generated -name "*.csv" | wc -l | tr -d ' ')

echo "📦 Devices mappings trouvés: $device_count"
echo "📋 Templates trouvés: $template_count"  
echo "📊 Fichiers CSV générés: $csv_count"

# Vérifier que tous les mappings ont les champs requis
for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        test_name="Mapping $filename (champs requis)"
        
        if grep -q '"xml_instance"' "$file" && grep -q '"vendor_id"' "$file" && grep -q '"product_id"' "$file"; then
            echo "✅ $test_name"
            ((passed_tests++))
        else
            echo "❌ $test_name"
            ((failed_tests++))
        fi
        ((total_tests++))
    fi
done

echo ""
echo "🌐 Phase 5: Test de l'Intégration Web"
echo "====================================="

# Vérifier que le serveur répond
if curl -s http://localhost:8080 > /dev/null 2>&1; then
    echo "✅ Serveur web accessible"
    ((passed_tests++))
else
    echo "❌ Serveur web non accessible"
    ((failed_tests++))
fi
((total_tests++))

# Test de l'API de sauvegarde
test_data='{"id":"Test Device System","vendor_id":"0x9999","product_id":"0x9999","xml_instance":"test_system_999","axes_map":{}}'
response=$(curl -s -X POST -H "Content-Type: application/json" -d "$test_data" http://localhost:8080/save_device_mapping.php)

if echo "$response" | grep -q '"success":true'; then
    echo "✅ API de sauvegarde fonctionnelle"
    ((passed_tests++))
    # Nettoyage
    rm -f "mappings/devices/test_system_999_map.json" 2>/dev/null
else
    echo "❌ API de sauvegarde non fonctionnelle"
    ((failed_tests++))
fi
((total_tests++))

echo ""
echo "🔄 Phase 6: Test de Cohérence"
echo "=============================="

# Vérifier qu'il n'y a pas de doublons d'xml_instance
xml_instances=$(grep -h '"xml_instance"' mappings/devices/*.json | sort)
unique_instances=$(echo "$xml_instances" | uniq)

if [ "$(echo "$xml_instances" | wc -l)" = "$(echo "$unique_instances" | wc -l)" ]; then
    echo "✅ Aucun doublon xml_instance détecté"
    ((passed_tests++))
else
    echo "❌ Doublons xml_instance détectés"
    ((failed_tests++))
fi
((total_tests++))

# Vérifier la convention de nommage
naming_errors=0
for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        if [[ ! "$filename" =~ ^[0-9a-f]{4}_[0-9a-f]{4}_map\.json$ ]]; then
            ((naming_errors++))
        fi
    fi
done

if [ $naming_errors -eq 0 ]; then
    echo "✅ Convention de nommage respectée"
    ((passed_tests++))
else
    echo "❌ $naming_errors erreur(s) de convention de nommage"
    ((failed_tests++))
fi
((total_tests++))

echo ""
echo "📊 Rapport Final"
echo "================"
echo "🧪 Tests exécutés: $total_tests"
echo "✅ Réussites: $passed_tests"
echo "❌ Échecs: $failed_tests"

success_rate=$((passed_tests * 100 / total_tests))
echo "📈 Taux de réussite: $success_rate%"

echo ""
if [ $failed_tests -eq 0 ]; then
    echo "🎉 Tous les tests sont passés ! Le système est prêt pour la production."
    echo ""
    echo "🚀 Actions recommandées:"
    echo "   • Le système de mapping est complètement organisé"
    echo "   • Tous les fichiers sont dans la structure correcte"
    echo "   • La validation automatique fonctionne"
    echo "   • L'intégration web est opérationnelle"
    echo ""
    echo "💡 Pour utiliser le système:"
    echo "   1. Ouvrez http://localhost:8080"
    echo "   2. Testez la détection avec http://localhost:8080/test_device_detection.html"
    echo "   3. Utilisez ./validate_mappings.sh pour valider les nouveaux mappings"
    echo ""
    exit 0
else
    echo "⚠️  $failed_tests test(s) ont échoué. Vérifiez les erreurs ci-dessus."
    echo ""
    echo "🔧 Actions recommandées:"
    echo "   • Corriger les erreurs identifiées"
    echo "   • Relancer ce script de test"
    echo "   • Consulter les logs pour plus de détails"
    echo ""
    exit 1
fi
