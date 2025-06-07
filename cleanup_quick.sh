#!/bin/bash

# Script de nettoyage automatique rapide (sans confirmations)
# Supprime seulement les fichiers temporaires évidents et sûrs

echo "🚀 Nettoyage automatique rapide..."

# Compteurs
removed_count=0
skipped_count=0

# Fonction pour supprimer automatiquement
auto_remove() {
    local file="$1"
    if [ -f "$file" ]; then
        rm "$file"
        echo "✅ Supprimé: $(basename "$file")"
        ((removed_count++))
    else
        ((skipped_count++))
    fi
}

# Fichiers temporaires évidents (sûrs à supprimer)
echo "🗑️  Suppression des fichiers temporaires..."

auto_remove "temp_csv_actions.txt"
auto_remove "temp_xml_actions.txt"
auto_remove "missing_actions.txt"

# Tests de debug évidents
echo "🧪 Suppression des tests de debug évidents..."

auto_remove "tests/html/debug_hold_filter.html"
auto_remove "tests/html/debug_overlay_test.html"
auto_remove "tests/html/diagnostic_filtres.html"
auto_remove "tests/html/diagnostic_prefix_h.html"

# Tests Hold obsolètes (garder les finaux)
echo "🎯 Suppression des anciens tests Hold..."

auto_remove "tests/html/test_hold_simple.html"
auto_remove "tests/html/test_hold_in_action_name.html"
auto_remove "tests/html/test_hold_action_name.html"
auto_remove "tests/html/test_simplified_hold_logic.html"

# Tests de navigation obsolètes
echo "🧭 Suppression des anciens tests de navigation..."

auto_remove "tests/html/test_cycling_simple.html"
auto_remove "tests/html/test_auto_cycling.html"
auto_remove "tests/html/test_quick_cycle.html"

# Tests de filtres simples
echo "🔍 Suppression des tests de filtres simples..."

auto_remove "tests/html/test_minimal_filter.html"
auto_remove "tests/html/test_hold_filter.html"

# Scripts PHP de test à la racine (évidents)
echo "🐘 Suppression des tests PHP temporaires..."

auto_remove "test_simple.php"
auto_remove "test_fgetcsv_deprecated.php"
auto_remove "debug_fgetcsv_reproduction.php"

# Anciens scripts de nettoyage
echo "🧹 Suppression des anciens scripts de nettoyage..."

auto_remove "cleanup_final.sh"
auto_remove "cleanup_test_files.sh"

# Fichier de debug diagnostic
echo "🔧 Nettoyage du diagnostic..."

auto_remove "debug/diagnostic/console_diagnostic.js"

echo ""
echo "📊 Résumé du nettoyage rapide:"
echo "   ✅ Fichiers supprimés: $removed_count"
echo "   ⏭️  Fichiers non trouvés: $skipped_count"
echo ""
echo "📁 Fichiers importants conservés:"
echo "   ✅ tests/html/test_final_complete_hold_system.html"
echo "   ✅ tests/html/test_complete_hold_detection.html"
echo "   ✅ tests/html/test_complete_system.html"
echo "   ✅ Tout le code source (src/, templates/, assets/)"
echo "   ✅ Documentation (docs/)"
echo "   ✅ Configuration (files/, config.php)"
echo ""
echo "🎉 Nettoyage rapide terminé !"
echo ""
echo "💡 Pour un nettoyage plus approfondi, utilisez: ./cleanup_comprehensive.sh"
