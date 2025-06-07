#!/bin/bash

# Script de prévisualisation de l'organisation des fichiers
# Montre ce qui sera déplacé SANS rien déplacer

echo "🔍 Prévisualisation de l'organisation des fichiers"
echo "================================================="

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Compteurs
total_files=0

# Fonction pour analyser et compter
analyze_move() {
    local source="$1"
    local target_dir="$2"
    local category="$3"
    local description="$4"
    
    if [ -f "$source" ]; then
        echo -e "${GREEN}📄 $(basename "$source")${NC}"
        echo -e "   ${BLUE}De:${NC} $(pwd)/$(basename "$source")"
        echo -e "   ${CYAN}Vers:${NC} $target_dir/"
        echo -e "   ${YELLOW}Type:${NC} $category"
        echo -e "   💭 $description"
        echo ""
        ((total_files++))
    fi
}

echo ""
echo -e "${YELLOW}=== TESTS PHP (vers tests/php/) ===${NC}"
analyze_move "test_application.php" "tests/php" "TEST PHP" "Test de l'application principale"
analyze_move "test_device_matching.php" "tests/php" "TEST PHP" "Test du matching des devices"
analyze_move "test_exact_reproduction.php" "tests/php" "TEST PHP" "Test de reproduction exacte"
analyze_move "test_fgetcsv.php" "tests/php" "TEST PHP" "Test de la fonction fgetcsv"
analyze_move "test_fgetcsv_final.php" "tests/php" "TEST PHP" "Test fgetcsv final"
analyze_move "test_flow_complet.php" "tests/php" "TEST PHP" "Test du flux complet"
analyze_move "test_flux_complet.php" "tests/php" "TEST PHP" "Test du flux complet (variante)"
analyze_move "test_template_generation.php" "tests/php" "TEST PHP" "Test de génération de templates"
analyze_move "test_validation_finale.php" "tests/php" "TEST PHP" "Test de validation finale"
analyze_move "test_xml_loading.php" "tests/php" "TEST PHP" "Test de chargement XML"

echo -e "${YELLOW}=== TESTS HTML (vers tests/html/) ===${NC}"
analyze_move "test_complete_flow.html" "tests/html" "TEST HTML" "Test du flux complet HTML"
analyze_move "test_integration_complete.html" "tests/html" "TEST HTML" "Test d'intégration complète"
analyze_move "test_main_interface.html" "tests/html" "TEST HTML" "Test de l'interface principale"
analyze_move "test_render_function.html" "tests/html" "TEST HTML" "Test de fonction de rendu"
analyze_move "test_virtual_devices.html" "tests/html" "TEST HTML" "Test des devices virtuels"
analyze_move "test_xml_devices_final.html" "tests/html" "TEST HTML" "Test des devices XML final"

echo -e "${YELLOW}=== FICHIERS XML (vers tests/xml/) ===${NC}"
analyze_move "test_xml_joysticks.xml" "tests/xml" "TEST XML" "Fichier XML de test pour joysticks"

echo -e "${YELLOW}=== DEBUG (vers debug/devices/) ===${NC}"
analyze_move "debug_devices_data.php" "debug/devices" "DEBUG" "Debug des données devices"
analyze_move "debug_devices_transmission.php" "debug/devices" "DEBUG" "Debug de transmission devices"

echo -e "${YELLOW}=== CSV MAPPINGS (vers temp/mappings/) ===${NC}"
analyze_move "high_confidence_mappings.csv" "temp/mappings" "CSV MAPPING" "Mappings haute confiance"
analyze_move "low_confidence_mappings.csv" "temp/mappings" "CSV MAPPING" "Mappings faible confiance"
analyze_move "medium_confidence_mappings.csv" "temp/mappings" "CSV MAPPING" "Mappings moyenne confiance"
analyze_move "mapping_suggestions.csv" "temp/mappings" "CSV MAPPING" "Suggestions de mapping"
analyze_move "low_confidence_for_review.csv" "temp/mappings" "CSV MAPPING" "Faible confiance à réviser"
analyze_move "medium_confidence_for_review.csv" "temp/mappings" "CSV MAPPING" "Moyenne confiance à réviser"

echo -e "${YELLOW}=== SCRIPTS ANALYSE (vers scripts/analysis/) ===${NC}"
analyze_move "analyze_missing_actions.php" "scripts/analysis" "SCRIPT ANALYSE" "Analyse des actions manquantes"
analyze_move "analyze_suggestions.php" "scripts/analysis" "SCRIPT ANALYSE" "Analyse des suggestions"

echo -e "${YELLOW}=== SCRIPTS INTÉGRATION (vers scripts/integration/) ===${NC}"
analyze_move "integrate_mappings.php" "scripts/integration" "SCRIPT INTÉGRATION" "Intégration des mappings"
analyze_move "integrate_mappings_smart.php" "scripts/integration" "SCRIPT INTÉGRATION" "Intégration smart des mappings"
analyze_move "review_mappings.php" "scripts/integration" "SCRIPT INTÉGRATION" "Révision des mappings"

echo -e "${YELLOW}=== SCRIPTS PYTHON (vers scripts/python/) ===${NC}"
analyze_move "extractor.py" "scripts/python" "SCRIPT PYTHON" "Script extracteur"
analyze_move "importer.py" "scripts/python" "SCRIPT PYTHON" "Script importeur"

echo ""
echo -e "${YELLOW}=== TESTS HTML EXISTANTS (réorganisation) ===${NC}"

if [ -d "tests/html" ]; then
    echo "📁 Les tests HTML existants seront réorganisés en sous-dossiers:"
    echo ""
    
    # Analyser les tests de debug
    echo -e "${CYAN}🐛 Debug (vers tests/html/debug/)${NC}"
    cd tests/html 2>/dev/null || exit 1
    for file in debug_*.html diagnostic_*.html; do
        if [ -f "$file" ]; then
            echo -e "   ${GREEN}📄 $file${NC}"
            ((total_files++))
        fi
    done
    
    # Analyser les tests Hold
    echo -e "${CYAN}🎯 Hold (vers tests/html/hold/)${NC}"
    for file in test_*hold*.html; do
        if [ -f "$file" ]; then
            echo -e "   ${GREEN}📄 $file${NC}"
            ((total_files++))
        fi
    done
    
    # Analyser les tests de navigation
    echo -e "${CYAN}🧭 Navigation (vers tests/html/navigation/)${NC}"
    for file in test_*anchor*.html test_*cycling*.html test_*navigation*.html; do
        if [ -f "$file" ]; then
            echo -e "   ${GREEN}📄 $file${NC}"
            ((total_files++))
        fi
    done
    
    # Analyser les tests de filtres
    echo -e "${CYAN}🔍 Filtres (vers tests/html/filters/)${NC}"
    for file in test_*filter*.html test_*overlay*.html; do
        if [ -f "$file" ]; then
            echo -e "   ${GREEN}📄 $file${NC}"
            ((total_files++))
        fi
    done
    
    # Analyser les tests gamepad
    echo -e "${CYAN}🎮 Gamepad (vers tests/html/gamepad/)${NC}"
    for file in test_*gamepad*.html test_*hat*.html; do
        if [ -f "$file" ]; then
            echo -e "   ${GREEN}📄 $file${NC}"
            ((total_files++))
        fi
    done
    
    # Analyser les tests d'intégration (sauf finaux)
    echo -e "${CYAN}🔧 Intégration (vers tests/html/integration/)${NC}"
    for file in test_*integration*.html test_*system*.html test_*complete*.html; do
        if [ -f "$file" ]; then
            if [[ "$file" == *"final"* ]] || [[ "$file" == "test_complete_system.html" ]]; then
                echo -e "   ${BLUE}🔒 $file (gardé à la racine - fichier final)${NC}"
            else
                echo -e "   ${GREEN}📄 $file${NC}"
                ((total_files++))
            fi
        fi
    done
    
    cd ../..
fi

echo ""
echo -e "${YELLOW}=== FICHIERS CONSERVÉS À LA RACINE ===${NC}"
echo -e "${GREEN}✅ Code source:${NC} src/, templates/, assets/"
echo -e "${GREEN}✅ Configuration:${NC} config.php, bootstrap.php, keybind_editor.php"
echo -e "${GREEN}✅ Documentation:${NC} README.md, docs/"
echo -e "${GREEN}✅ Tests finaux:${NC} tests/html/test_final_*.html, test_complete_system.html"
echo -e "${GREEN}✅ Scripts nettoyage:${NC} cleanup_*.sh, analyze_cleanup.sh"
echo -e "${GREEN}✅ Fichiers système:${NC} .gitignore, Dockerfile, favicon.ico"

echo ""
echo -e "${YELLOW}=== DOSSIERS QUI SERONT CRÉÉS ===${NC}"
echo "📁 tests/php/ (tests PHP)"
echo "📁 tests/xml/ (fichiers XML de test)"
echo "📁 tests/html/debug/ (tests de debug HTML)"
echo "📁 tests/html/hold/ (tests Hold HTML)"
echo "📁 tests/html/navigation/ (tests navigation HTML)"
echo "📁 tests/html/integration/ (tests intégration HTML)"
echo "📁 tests/html/filters/ (tests filtres HTML)"
echo "📁 tests/html/gamepad/ (tests gamepad HTML)"
echo "📁 debug/devices/ (debug devices)"
echo "📁 temp/mappings/ (CSV temporaires)"
echo "📁 scripts/analysis/ (scripts d'analyse)"
echo "📁 scripts/integration/ (scripts d'intégration)"
echo "📁 scripts/python/ (scripts Python)"

echo ""
echo "📊 RÉSUMÉ"
echo "========="
echo -e "${GREEN}📄 Total fichiers à déplacer: $total_files${NC}"
echo -e "${BLUE}📁 Dossiers à créer: ~13${NC}"
echo -e "${YELLOW}🔒 Fichiers importants conservés à la racine${NC}"

echo ""
echo "💡 PROCHAINES ÉTAPES"
echo "==================="
echo "1. 🔍 Cette prévisualisation (terminée)"
echo "2. 🚀 Exécuter: ./organize_files.sh (avec confirmations)"
echo "3. 🧪 Tester que tout fonctionne"
echo "4. 📝 Committer: git add . && git commit -m 'Organize files into folders'"

echo ""
echo "🎯 AVANTAGES DE CETTE ORGANISATION"
echo "=================================="
echo "✅ Racine du projet plus propre"
echo "✅ Fichiers groupés par fonction"
echo "✅ Tests organisés par catégorie"
echo "✅ Fichiers temporaires séparés"
echo "✅ Structure plus professionnelle"
echo "✅ Navigation plus facile"

echo ""
echo "🔥 Pour exécuter l'organisation: ./organize_files.sh"
