#!/bin/bash

# Script d'organisation des fichiers dans des dossiers appropriés
# Déplace les fichiers de test, debug et temporaires vers des dossiers organisés

echo "📁 Organisation des fichiers dans des dossiers appropriés..."

# Couleurs pour le terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compteurs
moved_count=0
created_dirs=0
skipped_count=0

# Fonction pour créer un dossier s'il n'existe pas
ensure_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo -e "${BLUE}📁 Créé: $dir${NC}"
        ((created_dirs++))
    fi
}

# Fonction pour déplacer un fichier
move_file() {
    local source="$1"
    local target_dir="$2"
    local description="$3"
    
    if [ -f "$source" ]; then
        ensure_dir "$target_dir"
        mv "$source" "$target_dir/"
        echo -e "${GREEN}✅ Déplacé: $(basename "$source") → $target_dir/${NC}"
        echo "   💭 $description"
        ((moved_count++))
    else
        ((skipped_count++))
    fi
}

# Fonction pour demander confirmation
confirm() {
    read -p "$1 (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

echo ""
echo "=== ORGANISATION DES DOSSIERS ==="

# Créer la structure de dossiers
ensure_dir "tests/php"
ensure_dir "tests/html/debug"
ensure_dir "tests/html/obsolete"
ensure_dir "tests/xml"
ensure_dir "debug/devices"
ensure_dir "debug/csv"
ensure_dir "debug/scripts"
ensure_dir "temp"
ensure_dir "temp/csv"
ensure_dir "temp/mappings"
ensure_dir "temp/analysis"
ensure_dir "scripts/analysis"
ensure_dir "scripts/integration"
ensure_dir "scripts/python"

echo ""
echo "=== DÉPLACEMENT DES FICHIERS ==="

if confirm "Déplacer les fichiers de test PHP vers tests/php/"; then
    echo -e "${YELLOW}📂 Tests PHP...${NC}"
    move_file "test_application.php" "tests/php" "Test de l'application principale"
    move_file "test_device_matching.php" "tests/php" "Test du matching des devices"
    move_file "test_exact_reproduction.php" "tests/php" "Test de reproduction exacte"
    move_file "test_fgetcsv.php" "tests/php" "Test de la fonction fgetcsv"
    move_file "test_fgetcsv_final.php" "tests/php" "Test fgetcsv final"
    move_file "test_flow_complet.php" "tests/php" "Test du flux complet"
    move_file "test_flux_complet.php" "tests/php" "Test du flux complet (variante)"
    move_file "test_template_generation.php" "tests/php" "Test de génération de templates"
    move_file "test_validation_finale.php" "tests/php" "Test de validation finale"
    move_file "test_xml_loading.php" "tests/php" "Test de chargement XML"
fi

if confirm "Déplacer les fichiers de test HTML vers tests/html/"; then
    echo -e "${YELLOW}📂 Tests HTML...${NC}"
    move_file "test_complete_flow.html" "tests/html" "Test du flux complet HTML"
    move_file "test_integration_complete.html" "tests/html" "Test d'intégration complète"
    move_file "test_main_interface.html" "tests/html" "Test de l'interface principale"
    move_file "test_render_function.html" "tests/html" "Test de fonction de rendu"
    move_file "test_virtual_devices.html" "tests/html" "Test des devices virtuels"
    move_file "test_xml_devices_final.html" "tests/html" "Test des devices XML final"
fi

if confirm "Déplacer les fichiers XML de test vers tests/xml/"; then
    echo -e "${YELLOW}📂 Tests XML...${NC}"
    move_file "test_xml_joysticks.xml" "tests/xml" "Fichier XML de test pour joysticks"
fi

if confirm "Déplacer les fichiers de debug vers debug/"; then
    echo -e "${YELLOW}🐛 Debug...${NC}"
    move_file "debug_devices_data.php" "debug/devices" "Debug des données devices"
    move_file "debug_devices_transmission.php" "debug/devices" "Debug de transmission devices"
fi

if confirm "Déplacer les fichiers CSV de mapping vers temp/mappings/"; then
    echo -e "${YELLOW}📊 CSV Mappings...${NC}"
    move_file "high_confidence_mappings.csv" "temp/mappings" "Mappings haute confiance"
    move_file "low_confidence_mappings.csv" "temp/mappings" "Mappings faible confiance"
    move_file "medium_confidence_mappings.csv" "temp/mappings" "Mappings moyenne confiance"
    move_file "mapping_suggestions.csv" "temp/mappings" "Suggestions de mapping"
    move_file "low_confidence_for_review.csv" "temp/mappings" "Faible confiance à réviser"
    move_file "medium_confidence_for_review.csv" "temp/mappings" "Moyenne confiance à réviser"
fi

if confirm "Déplacer les scripts d'analyse vers scripts/analysis/"; then
    echo -e "${YELLOW}🔍 Scripts d'analyse...${NC}"
    move_file "analyze_missing_actions.php" "scripts/analysis" "Analyse des actions manquantes"
    move_file "analyze_suggestions.php" "scripts/analysis" "Analyse des suggestions"
fi

if confirm "Déplacer les scripts d'intégration vers scripts/integration/"; then
    echo -e "${YELLOW}🔧 Scripts d'intégration...${NC}"
    move_file "integrate_mappings.php" "scripts/integration" "Intégration des mappings"
    move_file "integrate_mappings_smart.php" "scripts/integration" "Intégration smart des mappings"
    move_file "review_mappings.php" "scripts/integration" "Révision des mappings"
fi

if confirm "Déplacer les scripts Python vers scripts/python/"; then
    echo -e "${YELLOW}🐍 Scripts Python...${NC}"
    move_file "extractor.py" "scripts/python" "Script extracteur"
    move_file "importer.py" "scripts/python" "Script importeur"
fi

echo ""
echo "=== ORGANISATION DES TESTS HTML EXISTANTS ==="

# Organiser les tests HTML existants dans tests/html/
if [ -d "tests/html" ] && confirm "Organiser les tests HTML existants en sous-dossiers"; then
    echo -e "${YELLOW}🗂️  Organisation des tests HTML...${NC}"
    
    # Créer les sous-dossiers
    ensure_dir "tests/html/debug"
    ensure_dir "tests/html/hold"
    ensure_dir "tests/html/navigation"
    ensure_dir "tests/html/integration"
    ensure_dir "tests/html/filters"
    ensure_dir "tests/html/gamepad"
    ensure_dir "tests/html/obsolete"
    
    cd tests/html 2>/dev/null || exit 1
    
    # Déplacer les tests de debug
    for file in debug_*.html diagnostic_*.html; do
        if [ -f "$file" ]; then
            mv "$file" "debug/"
            echo -e "${GREEN}✅ $file → debug/${NC}"
            ((moved_count++))
        fi
    done
    
    # Déplacer les tests Hold
    for file in test_*hold*.html; do
        if [ -f "$file" ]; then
            mv "$file" "hold/"
            echo -e "${GREEN}✅ $file → hold/${NC}"
            ((moved_count++))
        fi
    done
    
    # Déplacer les tests de navigation
    for file in test_*anchor*.html test_*cycling*.html test_*navigation*.html; do
        if [ -f "$file" ]; then
            mv "$file" "navigation/"
            echo -e "${GREEN}✅ $file → navigation/${NC}"
            ((moved_count++))
        fi
    done
    
    # Déplacer les tests de filtres
    for file in test_*filter*.html test_*overlay*.html; do
        if [ -f "$file" ]; then
            mv "$file" "filters/"
            echo -e "${GREEN}✅ $file → filters/${NC}"
            ((moved_count++))
        fi
    done
    
    # Déplacer les tests gamepad
    for file in test_*gamepad*.html test_*hat*.html; do
        if [ -f "$file" ]; then
            mv "$file" "gamepad/"
            echo -e "${GREEN}✅ $file → gamepad/${NC}"
            ((moved_count++))
        fi
    done
    
    # Déplacer les tests d'intégration
    for file in test_*integration*.html test_*system*.html test_*complete*.html; do
        if [ -f "$file" ]; then
            # Garder les finaux à la racine, déplacer les autres
            if [[ "$file" == *"final"* ]] || [[ "$file" == "test_complete_system.html" ]]; then
                echo -e "${BLUE}🔒 Gardé à la racine: $file (fichier final important)${NC}"
            else
                mv "$file" "integration/"
                echo -e "${GREEN}✅ $file → integration/${NC}"
                ((moved_count++))
            fi
        fi
    done
    
    cd ../..
fi

echo ""
echo "=== CRÉATION D'INDEX ET DOCUMENTATION ==="

# Créer un index dans chaque dossier
create_index() {
    local dir="$1"
    local description="$2"
    
    if [ -d "$dir" ]; then
        cat > "$dir/README.md" << EOF
# $description

Ce dossier contient des fichiers de type: $description

## Fichiers présents

$(ls -la "$dir" | grep -v "^total" | grep -v "README.md" | tail -n +2)

## Date de création
$(date +"%d/%m/%Y à %H:%M")

## Organisation
Ces fichiers ont été déplacés automatiquement depuis la racine du projet pour une meilleure organisation.
EOF
        echo -e "${BLUE}📄 Index créé: $dir/README.md${NC}"
    fi
}

if confirm "Créer des index README dans chaque dossier"; then
    create_index "tests/php" "Tests PHP"
    create_index "tests/html" "Tests HTML"
    create_index "tests/xml" "Fichiers XML de test"
    create_index "debug/devices" "Debug des devices"
    create_index "temp/mappings" "Mappings CSV temporaires"
    create_index "scripts/analysis" "Scripts d'analyse"
    create_index "scripts/integration" "Scripts d'intégration"
    create_index "scripts/python" "Scripts Python"
fi

echo ""
echo "=== MISE À JOUR DU GITIGNORE ==="

if confirm "Mettre à jour le .gitignore pour ignorer les dossiers temporaires"; then
    cat >> .gitignore << EOF

# Dossiers temporaires et de test (ajouté le $(date +%Y-%m-%d))
temp/
tests/php/
tests/xml/
debug/devices/
scripts/analysis/
scripts/integration/
scripts/python/

# Fichiers de mapping temporaires
temp/mappings/*.csv

# Tests HTML obsolètes
tests/html/obsolete/
tests/html/debug/
EOF
    echo -e "${GREEN}✅ .gitignore mis à jour${NC}"
fi

echo ""
echo "=== STRUCTURE FINALE ==="

echo -e "${BLUE}📁 Structure organisée:${NC}"
echo "├── tests/"
echo "│   ├── html/ (tests HTML organisés)"
echo "│   │   ├── debug/ (tests de debug)"
echo "│   │   ├── hold/ (tests Hold)"
echo "│   │   ├── navigation/ (tests navigation)"
echo "│   │   ├── integration/ (tests intégration)"
echo "│   │   ├── filters/ (tests filtres)"
echo "│   │   ├── gamepad/ (tests gamepad)"
echo "│   │   └── [tests finaux à la racine]"
echo "│   ├── php/ (tests PHP)"
echo "│   ├── xml/ (fichiers XML de test)"
echo "│   ├── js/ (tests JavaScript)"
echo "│   └── validation/ (tests de validation)"
echo "├── debug/"
echo "│   ├── devices/ (debug devices)"
echo "│   └── diagnostic/ (diagnostic)"
echo "├── temp/"
echo "│   └── mappings/ (CSV temporaires)"
echo "├── scripts/"
echo "│   ├── analysis/ (scripts d'analyse)"
echo "│   ├── integration/ (scripts d'intégration)"
echo "│   └── python/ (scripts Python)"
echo "└── [fichiers principaux à la racine]"

echo ""
echo "📊 RÉSUMÉ DE L'ORGANISATION"
echo "=========================="
echo -e "${GREEN}📁 Dossiers créés: $created_dirs${NC}"
echo -e "${GREEN}📄 Fichiers déplacés: $moved_count${NC}"
echo -e "${YELLOW}⏭️  Fichiers ignorés: $skipped_count${NC}"

echo ""
echo -e "${GREEN}🎉 Organisation terminée !${NC}"
echo ""
echo "💡 Recommandations:"
echo "   1. Vérifiez la nouvelle structure avec: tree ou ls -la"
echo "   2. Testez que tout fonctionne toujours"
echo "   3. Commitez les changements: git add . && git commit -m 'Organization: moved test/debug files to appropriate folders'"
echo "   4. Les tests finaux importants restent facilement accessibles"
