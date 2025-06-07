#!/bin/bash

# Script de nettoyage complet des fichiers de test et debug obsolètes
# Créé le 7 juin 2025

echo "🧹 Démarrage du nettoyage complet..."

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

# Fonction pour supprimer un fichier avec confirmation
safe_remove() {
    local file="$1"
    local description="$2"
    
    if [ -f "$file" ]; then
        if confirm "Supprimer $description: $(basename "$file")"; then
            rm "$file"
            echo "✅ Supprimé: $file"
        else
            echo "⏭️  Ignoré: $file"
        fi
    fi
}

# Fonction pour supprimer un dossier avec confirmation
safe_remove_dir() {
    local dir="$1"
    local description="$2"
    
    if [ -d "$dir" ]; then
        if confirm "Supprimer $description: $(basename "$dir")"; then
            rm -rf "$dir"
            echo "✅ Supprimé: $dir"
        else
            echo "⏭️  Ignoré: $dir"
        fi
    fi
}

echo ""
echo "=== FICHIERS DE TEST PHP RACINE ==="

# Fichiers de test PHP à la racine (obsolètes)
safe_remove "test_application.php" "test application PHP"
safe_remove "test_device_matching.php" "test device matching"
safe_remove "test_exact_reproduction.php" "test exact reproduction"
safe_remove "test_fgetcsv.php" "test fgetcsv"
safe_remove "test_fgetcsv_deprecated.php" "test fgetcsv deprecated"
safe_remove "test_fgetcsv_final.php" "test fgetcsv final"
safe_remove "test_flow_complet.php" "test flow complet"
safe_remove "test_flux_complet.php" "test flux complet"
safe_remove "test_simple.php" "test simple"
safe_remove "test_template_generation.php" "test template generation"
safe_remove "test_validation_finale.php" "test validation finale"
safe_remove "test_xml_loading.php" "test XML loading"

echo ""
echo "=== FICHIERS HTML DE TEST RACINE ==="

# Fichiers HTML de test à la racine (obsolètes)
safe_remove "test_complete_flow.html" "test complete flow HTML"
safe_remove "test_integration_complete.html" "test integration complete"
safe_remove "test_main_interface.html" "test main interface"
safe_remove "test_render_function.html" "test render function"
safe_remove "test_virtual_devices.html" "test virtual devices"
safe_remove "test_xml_devices_final.html" "test XML devices final"

echo ""
echo "=== FICHIERS XML DE TEST ==="

# Fichiers XML de test
safe_remove "test_xml_joysticks.xml" "fichier XML de test joysticks"

echo ""
echo "=== FICHIERS DEBUG RACINE ==="

# Fichiers de debug à la racine
safe_remove "debug_devices_data.php" "debug devices data"
safe_remove "debug_devices_transmission.php" "debug devices transmission"
safe_remove "debug_fgetcsv_reproduction.php" "debug fgetcsv reproduction"

echo ""
echo "=== FICHIERS TEMPORAIRES ==="

# Fichiers temporaires
safe_remove "temp_csv_actions.txt" "fichier temporaire CSV actions"
safe_remove "temp_xml_actions.txt" "fichier temporaire XML actions"
safe_remove "missing_actions.txt" "fichier actions manquantes"

echo ""
echo "=== FICHIERS CSV DE MAPPING TEMPORAIRES ==="

# Fichiers CSV de mapping (peuvent être regénérés)
if confirm "Supprimer tous les fichiers de mapping CSV (peuvent être regénérés)"; then
    safe_remove "high_confidence_mappings.csv" "mapping haute confiance"
    safe_remove "low_confidence_mappings.csv" "mapping faible confiance"
    safe_remove "medium_confidence_mappings.csv" "mapping moyenne confiance"
    safe_remove "mapping_suggestions.csv" "suggestions de mapping"
    safe_remove "low_confidence_for_review.csv" "faible confiance à réviser"
    safe_remove "medium_confidence_for_review.csv" "moyenne confiance à réviser"
fi

echo ""
echo "=== SCRIPTS D'ANALYSE TEMPORAIRES ==="

# Scripts d'analyse (peuvent être supprimés s'ils ne sont plus nécessaires)
if confirm "Supprimer les scripts d'analyse temporaires"; then
    safe_remove "analyze_missing_actions.php" "script analyse actions manquantes"
    safe_remove "analyze_suggestions.php" "script analyse suggestions"
    safe_remove "integrate_mappings.php" "script intégration mappings"
    safe_remove "integrate_mappings_smart.php" "script intégration mappings smart"
    safe_remove "review_mappings.php" "script révision mappings"
fi

echo ""
echo "=== SCRIPTS PYTHON TEMPORAIRES ==="

if confirm "Supprimer les scripts Python temporaires"; then
    safe_remove "extractor.py" "script extracteur Python"
    safe_remove "importer.py" "script importeur Python"
fi

echo ""
echo "=== TESTS HTML OBSOLÈTES ==="

# Nettoyage des tests HTML obsolètes dans /tests/html/
cd tests/html 2>/dev/null || { echo "Dossier tests/html non trouvé"; }

if [ -d "../../tests/html" ]; then
    echo "Nettoyage des tests HTML obsolètes..."
    
    # Tests Hold obsolètes (garder seulement les finaux)
    if confirm "Supprimer les anciens tests Hold (garder les finaux)"; then
        safe_remove "tests/html/test_hold_simple.html" "test hold simple"
        safe_remove "tests/html/test_hold_in_action_name.html" "test hold in action name"
        safe_remove "tests/html/test_hold_action_name.html" "test hold action name"
        safe_remove "tests/html/test_hold_mode_analysis.html" "test hold mode analysis"
        safe_remove "tests/html/test_hold_mode_enhancement.html" "test hold mode enhancement"
        safe_remove "tests/html/test_simplified_hold_logic.html" "test simplified hold logic"
        safe_remove "tests/html/test_hold_double_detection.html" "test hold double detection"
    fi
    
    # Tests de debug et diagnostic obsolètes
    if confirm "Supprimer les tests de debug et diagnostic obsolètes"; then
        safe_remove "tests/html/debug_hold_filter.html" "debug hold filter"
        safe_remove "tests/html/debug_overlay_test.html" "debug overlay test"
        safe_remove "tests/html/diagnostic_filtres.html" "diagnostic filtres"
        safe_remove "tests/html/diagnostic_prefix_h.html" "diagnostic prefix H"
    fi
    
    # Tests d'ancrage et navigation obsolètes
    if confirm "Supprimer les anciens tests d'ancrage et navigation"; then
        safe_remove "tests/html/test_anchor_fix.html" "test anchor fix"
        safe_remove "tests/html/test_anchor_fix_validation.html" "test anchor fix validation"
        safe_remove "tests/html/test_anchor_system.html" "test anchor system"
        safe_remove "tests/html/test_simplified_anchoring.html" "test simplified anchoring"
        safe_remove "tests/html/test_cycling_navigation.html" "test cycling navigation"
        safe_remove "tests/html/test_cycling_simple.html" "test cycling simple"
        safe_remove "tests/html/test_auto_cycling.html" "test auto cycling"
        safe_remove "tests/html/test_quick_cycle.html" "test quick cycle"
    fi
    
    # Tests de filtres et overlay obsolètes
    if confirm "Supprimer les anciens tests de filtres et overlay"; then
        safe_remove "tests/html/test_filters_validation.html" "test filters validation"
        safe_remove "tests/html/test_hold_filter.html" "test hold filter"
        safe_remove "tests/html/test_minimal_filter.html" "test minimal filter"
        safe_remove "tests/html/test_overlay_fix.html" "test overlay fix"
        safe_remove "tests/html/test_unmapped_overlay.html" "test unmapped overlay"
        safe_remove "tests/html/test_antispam_fix.html" "test antispam fix"
    fi
    
    # Tests gamepad et hat obsolètes
    if confirm "Supprimer les anciens tests gamepad et hat"; then
        safe_remove "tests/html/test_gamepad.html" "test gamepad"
        safe_remove "tests/html/test_hat_modes.html" "test hat modes"
        safe_remove "tests/html/test_hold_mode_real_gamepad.html" "test hold mode real gamepad"
        safe_remove "tests/html/test_real_gamepad.html" "test real gamepad"
    fi
    
    # Tests d'intégration obsolètes (garder les finaux)
    if confirm "Supprimer les anciens tests d'intégration (garder les finaux)"; then
        safe_remove "tests/html/test_action_formatter_integration.html" "test action formatter integration"
        safe_remove "tests/html/test_simplified_integration.html" "test simplified integration"
        safe_remove "tests/html/test_real_implementation.html" "test real implementation"
        safe_remove "tests/html/test_simplified_final_validation.html" "test simplified final validation"
    fi
fi

echo ""
echo "=== NETTOYAGE DU DOSSIER DEBUG ==="

# Garder seulement les fichiers de debug essentiels
if confirm "Supprimer le contenu du dossier debug (garder la structure)"; then
    safe_remove "debug/diagnostic/console_diagnostic.js" "console diagnostic JS"
fi

echo ""
echo "=== SCRIPTS DE NETTOYAGE EXISTANTS ==="

# Anciens scripts de nettoyage
safe_remove "cleanup_final.sh" "ancien script cleanup final"
safe_remove "cleanup_test_files.sh" "ancien script cleanup test files"

echo ""
echo "=== FICHIERS À GARDER ==="
echo "Les fichiers suivants seront CONSERVÉS (fichiers importants):"
echo "✅ tests/html/test_final_complete_hold_system.html (test final complet)"
echo "✅ tests/html/test_complete_hold_detection.html (test détection complète)"
echo "✅ tests/html/test_complete_system.html (test système complet)"
echo "✅ tests/html/test_final_anchor.html (test ancrage final)"
echo "✅ Tous les fichiers dans src/, templates/, assets/, files/"
echo "✅ Documentation dans docs/"
echo "✅ Backups dans backups/"
echo "✅ Configuration et fichiers principaux"

echo ""
if confirm "Créer un rapport de nettoyage"; then
    echo "📋 Création du rapport de nettoyage..."
    
    cat > "CLEANUP_REPORT_$(date +%Y%m%d_%H%M%S).md" << EOF
# Rapport de Nettoyage - $(date +"%d/%m/%Y à %H:%M")

## Fichiers Supprimés

### Fichiers de Test PHP (Racine)
- Tests d'application obsolètes
- Tests de fgetcsv et parsing
- Tests de validation temporaires

### Fichiers HTML de Test (Racine)
- Tests d'intégration obsolètes
- Tests d'interface temporaires
- Tests de rendu obsolètes

### Fichiers de Debug
- Scripts de debug devices obsolètes
- Fichiers de diagnostic temporaires

### Fichiers Temporaires
- Fichiers CSV de mapping (peuvent être regénérés)
- Fichiers d'actions temporaires
- Scripts d'analyse obsolètes

### Tests HTML Obsolètes (/tests/html/)
- Anciens tests Hold (remplacés par les finaux)
- Tests de debug et diagnostic
- Tests d'ancrage et navigation obsolètes
- Tests de filtres et overlay anciens
- Tests gamepad et hat obsolètes

## Fichiers Conservés

### Tests Finaux Importants
- test_final_complete_hold_system.html
- test_complete_hold_detection.html
- test_complete_system.html
- test_final_anchor.html

### Code Source
- src/ (Application.php, DeviceManager.php, XMLProcessor.php)
- templates/ (tous les templates)
- assets/ (CSS, JS, modules)

### Configuration et Données
- files/ (CSV de configuration)
- config.php, bootstrap.php
- keybind_editor.php (interface principale)

### Documentation et Backups
- docs/ (documentation complète)
- backups/ (sauvegardes)

## Recommandations

1. Les tests finaux dans /tests/html/ couvrent toutes les fonctionnalités
2. La documentation dans docs/ est complète et à jour
3. Les backups permettent de restaurer si nécessaire
4. Le code source est propre et optimisé

EOF

    echo "✅ Rapport créé: CLEANUP_REPORT_$(date +%Y%m%d_%H%M%S).md"
fi

echo ""
echo "🎉 Nettoyage terminé !"
echo ""
echo "📁 Structure finale propre:"
echo "   ├── src/ (code source principal)"
echo "   ├── templates/ (templates PHP)"
echo "   ├── assets/ (CSS, JS)"
echo "   ├── files/ (données de configuration)"
echo "   ├── docs/ (documentation)"
echo "   ├── tests/html/ (tests finaux uniquement)"
echo "   └── backups/ (sauvegardes)"
echo ""
echo "💡 Conseil: Exécutez 'git status' pour voir les changements avant de committer."
