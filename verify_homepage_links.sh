#!/bin/bash

# Script de vérification des liens de la page d'accueil
# Test de tous les fichiers référencés dans index.html

echo "🔍 Vérification des liens de la page d'accueil SC Config Editor"
echo "================================================================="

# Dossier racine du projet
PROJECT_ROOT="/Users/bteffot/Projects/perso/sc-config-editor"
cd "$PROJECT_ROOT"

# Liste des fichiers à vérifier
declare -a files=(
    "index.html"
    "keybind_editor.php"
    "test_final_system.html"
    "test_configure_button.html"
    "test_gamepad.html"
    "test_hat_modes.html"
    "test_complete_system.html"
    "validate_button_fix.html"
    "DEVICE_AUTO_DETECTION_GUIDE.md"
    "DEVICE_AUTO_DETECTION_FINAL_REPORT.md"
    "VALIDATION_REPORT_BUTTON_FIX.md"
    "HAT_DYNAMIC_KEYS_FIX_COMPLETE.md"
    "MAINTENANCE_GUIDE.md"
    "favicon.ico"
)

# Variables de comptage
total_files=0
existing_files=0
missing_files=0

echo ""
echo "📁 Vérification des fichiers..."

for file in "${files[@]}"; do
    total_files=$((total_files + 1))
    if [ -f "$file" ]; then
        echo "✅ $file - EXISTE"
        existing_files=$((existing_files + 1))
    else
        echo "❌ $file - MANQUANT"
        missing_files=$((missing_files + 1))
    fi
done

echo ""
echo "📊 Résumé:"
echo "   Total fichiers vérifiés: $total_files"
echo "   Fichiers existants: $existing_files"
echo "   Fichiers manquants: $missing_files"

# Vérification des dossiers assets
echo ""
echo "📂 Vérification des ressources..."

if [ -d "assets/css" ]; then
    echo "✅ assets/css - EXISTE"
    if [ -f "assets/css/styles.css" ]; then
        echo "✅ assets/css/styles.css - EXISTE"
    else
        echo "❌ assets/css/styles.css - MANQUANT"
    fi
else
    echo "❌ assets/css - MANQUANT"
fi

if [ -d "assets/js" ]; then
    echo "✅ assets/js - EXISTE"
    if [ -d "assets/js/modules" ]; then
        echo "✅ assets/js/modules - EXISTE"
        
        # Vérifier les modules JavaScript essentiels
        js_modules=("deviceAutoDetector.js" "deviceSetupUI.js" "scConfigEditor.js")
        for module in "${js_modules[@]}"; do
            if [ -f "assets/js/modules/$module" ] || [ -f "assets/js/$module" ]; then
                echo "✅ Module JS: $module - EXISTE"
            else
                echo "❌ Module JS: $module - MANQUANT"
            fi
        done
    else
        echo "❌ assets/js/modules - MANQUANT"
    fi
else
    echo "❌ assets/js - MANQUANT"
fi

# Test de connectivité du serveur local (si il tourne)
echo ""
echo "🌐 Test de connectivité..."

if command -v curl >/dev/null 2>&1; then
    if curl -s -f "http://localhost:8000/index.html" >/dev/null; then
        echo "✅ Serveur local accessible sur http://localhost:8000"
        
        # Test de quelques pages clés
        test_pages=("test_final_system.html" "test_configure_button.html" "validate_button_fix.html")
        for page in "${test_pages[@]}"; do
            if curl -s -f "http://localhost:8000/$page" >/dev/null; then
                echo "✅ Page $page accessible"
            else
                echo "⚠️ Page $page non accessible"
            fi
        done
    else
        echo "⚠️ Serveur local non accessible (normal si non démarré)"
        echo "   Pour démarrer: python3 -m http.server 8000"
    fi
else
    echo "⚠️ curl non disponible, impossible de tester la connectivité"
fi

# Récapitulatif final
echo ""
echo "🎯 État général de la page d'accueil:"
if [ $missing_files -eq 0 ]; then
    echo "✅ EXCELLENT - Tous les fichiers sont présents"
    echo "🚀 La page d'accueil est prête pour la production"
else
    echo "⚠️ ATTENTION - $missing_files fichier(s) manquant(s)"
    echo "🔧 Vérifiez les fichiers manquants avant la mise en production"
fi

echo ""
echo "📋 Nouvelles fonctionnalités mises en avant:"
echo "   🤖 Système de détection automatique des devices"
echo "   ⚙️ Assistant de configuration guidée"
echo "   🔘 Correction du bouton 'Configurer maintenant'"
echo "   ✅ Suite de validation complète"
echo "   📖 Documentation mise à jour"

echo ""
echo "================================================================="
echo "Vérification terminée !"
