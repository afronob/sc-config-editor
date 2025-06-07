#!/bin/bash

# Script d'analyse des fichiers à nettoyer (sans suppression)
# Identifie les fichiers temporaires, tests obsolètes, etc.

echo "🔍 Analyse des fichiers à nettoyer..."
echo "================================================"

# Fonction pour analyser un fichier
analyze_file() {
    local file="$1"
    local category="$2"
    local description="$3"
    
    if [ -f "$file" ]; then
        local size=$(stat -f%z "$file" 2>/dev/null || echo "0")
        local modified=$(stat -f%Sm -t%Y-%m-%d "$file" 2>/dev/null || echo "inconnue")
        echo "📄 $category: $(basename "$file")"
        echo "   📁 Chemin: $file"
        echo "   📏 Taille: $size octets"
        echo "   📅 Modifié: $modified"
        echo "   💭 Description: $description"
        echo ""
        return 0
    else
        return 1
    fi
}

# Fonction pour analyser un dossier
analyze_directory() {
    local dir="$1"
    local category="$2"
    
    if [ -d "$dir" ]; then
        local count=$(find "$dir" -type f | wc -l | tr -d ' ')
        local size=$(du -sh "$dir" 2>/dev/null | cut -f1)
        echo "📁 $category: $(basename "$dir")"
        echo "   📁 Chemin: $dir"
        echo "   📋 Fichiers: $count"
        echo "   📏 Taille: $size"
        echo ""
        return 0
    else
        return 1
    fi
}

temp_files=0
debug_files=0
test_files=0
obsolete_files=0

echo "🗃️  FICHIERS TEMPORAIRES"
echo "========================"

if analyze_file "temp_csv_actions.txt" "TEMPORAIRE" "Fichier temporaire d'actions CSV"; then ((temp_files++)); fi
if analyze_file "temp_xml_actions.txt" "TEMPORAIRE" "Fichier temporaire d'actions XML"; then ((temp_files++)); fi
if analyze_file "missing_actions.txt" "TEMPORAIRE" "Liste d'actions manquantes"; then ((temp_files++)); fi

echo "🐛 FICHIERS DE DEBUG"
echo "==================="

if analyze_file "debug_devices_data.php" "DEBUG" "Debug des données devices"; then ((debug_files++)); fi
if analyze_file "debug_devices_transmission.php" "DEBUG" "Debug transmission devices"; then ((debug_files++)); fi
if analyze_file "debug_fgetcsv_reproduction.php" "DEBUG" "Debug reproduction fgetcsv"; then ((debug_files++)); fi
if analyze_file "debug/diagnostic/console_diagnostic.js" "DEBUG" "Diagnostic console JavaScript"; then ((debug_files++)); fi

echo "🧪 TESTS PHP OBSOLÈTES (RACINE)"
echo "================================"

if analyze_file "test_application.php" "TEST PHP" "Test de l'application"; then ((test_files++)); fi
if analyze_file "test_device_matching.php" "TEST PHP" "Test matching devices"; then ((test_files++)); fi
if analyze_file "test_exact_reproduction.php" "TEST PHP" "Test reproduction exacte"; then ((test_files++)); fi
if analyze_file "test_fgetcsv.php" "TEST PHP" "Test fonction fgetcsv"; then ((test_files++)); fi
if analyze_file "test_fgetcsv_deprecated.php" "TEST PHP" "Test fgetcsv obsolète"; then ((test_files++)); fi
if analyze_file "test_fgetcsv_final.php" "TEST PHP" "Test fgetcsv final"; then ((test_files++)); fi
if analyze_file "test_simple.php" "TEST PHP" "Test simple"; then ((test_files++)); fi
if analyze_file "test_validation_finale.php" "TEST PHP" "Test validation finale"; then ((test_files++)); fi

echo "🌐 TESTS HTML OBSOLÈTES (RACINE)"
echo "================================="

if analyze_file "test_complete_flow.html" "TEST HTML" "Test flux complet"; then ((test_files++)); fi
if analyze_file "test_integration_complete.html" "TEST HTML" "Test intégration complète"; then ((test_files++)); fi
if analyze_file "test_main_interface.html" "TEST HTML" "Test interface principale"; then ((test_files++)); fi
if analyze_file "test_virtual_devices.html" "TEST HTML" "Test devices virtuels"; then ((test_files++)); fi

echo "🔧 TESTS HTML DEBUG (/tests/html/)"
echo "==================================="

if analyze_file "tests/html/debug_hold_filter.html" "TEST DEBUG" "Debug filtre hold"; then ((test_files++)); fi
if analyze_file "tests/html/debug_overlay_test.html" "TEST DEBUG" "Debug overlay"; then ((test_files++)); fi
if analyze_file "tests/html/diagnostic_filtres.html" "TEST DEBUG" "Diagnostic filtres"; then ((test_files++)); fi
if analyze_file "tests/html/diagnostic_prefix_h.html" "TEST DEBUG" "Diagnostic préfixe H"; then ((test_files++)); fi

echo "🎯 ANCIENS TESTS HOLD (/tests/html/)"
echo "====================================="

if analyze_file "tests/html/test_hold_simple.html" "TEST HOLD" "Test hold simple"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_hold_in_action_name.html" "TEST HOLD" "Test hold in action name"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_hold_action_name.html" "TEST HOLD" "Test hold action name"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_simplified_hold_logic.html" "TEST HOLD" "Test logique hold simplifiée"; then ((obsolete_files++)); fi

echo "🧭 ANCIENS TESTS NAVIGATION (/tests/html/)"
echo "==========================================="

if analyze_file "tests/html/test_cycling_simple.html" "TEST NAV" "Test cycling simple"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_auto_cycling.html" "TEST NAV" "Test auto cycling"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_quick_cycle.html" "TEST NAV" "Test quick cycle"; then ((obsolete_files++)); fi
if analyze_file "tests/html/test_anchor_fix.html" "TEST NAV" "Test anchor fix"; then ((obsolete_files++)); fi

echo "📊 FICHIERS CSV TEMPORAIRES"
echo "==========================="

csv_files=0
if analyze_file "high_confidence_mappings.csv" "CSV MAPPING" "Mappings haute confiance"; then ((csv_files++)); fi
if analyze_file "low_confidence_mappings.csv" "CSV MAPPING" "Mappings faible confiance"; then ((csv_files++)); fi
if analyze_file "medium_confidence_mappings.csv" "CSV MAPPING" "Mappings moyenne confiance"; then ((csv_files++)); fi
if analyze_file "mapping_suggestions.csv" "CSV MAPPING" "Suggestions de mapping"; then ((csv_files++)); fi

echo "🐍 SCRIPTS PYTHON TEMPORAIRES"
echo "=============================="

python_files=0
if analyze_file "extractor.py" "PYTHON" "Script extracteur"; then ((python_files++)); fi
if analyze_file "importer.py" "PYTHON" "Script importeur"; then ((python_files++)); fi

echo "🧹 ANCIENS SCRIPTS NETTOYAGE"
echo "============================="

cleanup_files=0
if analyze_file "cleanup_final.sh" "CLEANUP" "Ancien script cleanup final"; then ((cleanup_files++)); fi
if analyze_file "cleanup_test_files.sh" "CLEANUP" "Ancien script cleanup tests"; then ((cleanup_files++)); fi

echo ""
echo "📊 RÉSUMÉ DE L'ANALYSE"
echo "======================"
echo "🗃️  Fichiers temporaires: $temp_files"
echo "🐛 Fichiers de debug: $debug_files"
echo "🧪 Tests obsolètes: $test_files"
echo "⚠️  Fichiers obsolètes: $obsolete_files"
echo "📊 CSV temporaires: $csv_files"
echo "🐍 Scripts Python: $python_files"
echo "🧹 Scripts cleanup: $cleanup_files"
echo ""

total=$((temp_files + debug_files + test_files + obsolete_files + csv_files + python_files + cleanup_files))
echo "📋 TOTAL À NETTOYER: $total fichiers"

if [ $total -gt 0 ]; then
    echo ""
    echo "💡 RECOMMANDATIONS:"
    echo "==================="
    echo "✅ Nettoyage rapide (sûr): ./cleanup_quick.sh"
    echo "🔧 Nettoyage complet (avec confirmations): ./cleanup_comprehensive.sh"
    echo ""
    echo "📁 FICHIERS IMPORTANTS À CONSERVER:"
    echo "==================================="
    echo "✅ tests/html/test_final_complete_hold_system.html (test final complet)"
    echo "✅ tests/html/test_complete_hold_detection.html (test détection complète)"
    echo "✅ tests/html/test_complete_system.html (test système complet)"
    echo "✅ src/ (code source principal)"
    echo "✅ templates/ (templates PHP)"
    echo "✅ assets/ (CSS, JS, modules)"
    echo "✅ files/ (configuration CSV)"
    echo "✅ docs/ (documentation)"
    echo "✅ config.php, bootstrap.php, keybind_editor.php"
else
    echo ""
    echo "🎉 Aucun fichier à nettoyer détecté !"
fi

echo ""
echo "🔍 Analyse terminée."
