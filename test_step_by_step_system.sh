#!/bin/bash

# Script de test pour l'éditeur étape par étape
# Test de validation complète du nouveau système

echo "🚀 Test de l'Éditeur Étape par Étape"
echo "======================================"
echo ""

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les résultats
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

# Fonction pour afficher des informations
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Fonction pour afficher des avertissements
print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# 1. Vérification de la structure des fichiers
echo "1. Vérification de la structure des fichiers"
echo "---------------------------------------------"

files_to_check=(
    "src/StepByStepEditor.php"
    "step_by_step_handler.php"
    "templates/step_by_step/step1_upload.php"
    "templates/step_by_step/step2_devices.php"
    "templates/step_by_step/step3_edit.php"
    "templates/step_by_step/step4_summary.php"
    "test_step_by_step_editor.html"
)

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        print_result 0 "Fichier existe: $file"
    else
        print_result 1 "Fichier manquant: $file"
    fi
done

echo ""

# 2. Vérification de la syntaxe PHP
echo "2. Vérification de la syntaxe PHP"
echo "----------------------------------"

php_files=(
    "src/StepByStepEditor.php"
    "step_by_step_handler.php"
    "templates/step_by_step/step1_upload.php"
    "templates/step_by_step/step2_devices.php"
    "templates/step_by_step/step3_edit.php"
    "templates/step_by_step/step4_summary.php"
)

for file in "${php_files[@]}"; do
    if [ -f "$file" ]; then
        php -l "$file" > /dev/null 2>&1
        print_result $? "Syntaxe PHP: $file"
    fi
done

echo ""

# 3. Vérification des permissions
echo "3. Vérification des permissions"
echo "-------------------------------"

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        if [ -r "$file" ]; then
            print_result 0 "Permissions lecture: $file"
        else
            print_result 1 "Permissions lecture: $file"
        fi
    fi
done

echo ""

# 4. Vérification du dossier templates
echo "4. Vérification du dossier templates"
echo "------------------------------------"

if [ -d "templates/step_by_step" ]; then
    print_result 0 "Dossier templates/step_by_step existe"
    
    # Vérifier les permissions d'écriture
    if [ -w "templates/step_by_step" ]; then
        print_result 0 "Permissions écriture sur templates/step_by_step"
    else
        print_result 1 "Permissions écriture sur templates/step_by_step"
    fi
else
    print_result 1 "Dossier templates/step_by_step manquant"
    print_info "Création du dossier..."
    mkdir -p "templates/step_by_step"
    print_result $? "Création du dossier templates/step_by_step"
fi

echo ""

# 5. Test de la classe StepByStepEditor
echo "5. Test de la classe StepByStepEditor"
echo "-------------------------------------"

# Créer un script de test temporaire
cat > test_stepbystep_class.php << 'EOF'
<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/src/StepByStepEditor.php';

try {
    // Test d'instanciation
    $editor = new StepByStepEditor();
    echo "✅ Instanciation réussie\n";
    
    // Test des méthodes publiques
    $result = $editor->canAccessStep(1);
    echo "✅ Méthode canAccessStep() fonctionne\n";
    
    $step = $editor->getHighestAccessibleStep();
    echo "✅ Méthode getHighestAccessibleStep() fonctionne\n";
    
    $name = $editor->getStepName(1);
    echo "✅ Méthode getStepName() fonctionne\n";
    
    $validation = $editor->validateXMLContent('<?xml version="1.0"?><ActionMaps></ActionMaps>');
    echo "✅ Méthode validateXMLContent() fonctionne\n";
    
    $devices = $editor->detectConnectedDevices();
    echo "✅ Méthode detectConnectedDevices() fonctionne\n";
    
    echo "✅ Tous les tests de classe réussis\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
EOF

# Exécuter le test
php test_stepbystep_class.php
test_class_result=$?
print_result $test_class_result "Test de la classe StepByStepEditor"

# Nettoyer
rm -f test_stepbystep_class.php

echo ""

# 6. Test des templates HTML
echo "6. Test des templates HTML"
echo "--------------------------"

html_files=(
    "templates/step_by_step/step1_upload.php"
    "templates/step_by_step/step2_devices.php"
    "templates/step_by_step/step3_edit.php"
    "templates/step_by_step/step4_summary.php"
    "test_step_by_step_editor.html"
)

for file in "${html_files[@]}"; do
    if [ -f "$file" ]; then
        # Vérifier la présence de balises HTML de base
        if grep -q "<!DOCTYPE html>" "$file" && grep -q "<html" "$file" && grep -q "</html>" "$file"; then
            print_result 0 "Structure HTML valide: $file"
        else
            print_result 1 "Structure HTML invalide: $file"
        fi
    fi
done

echo ""

# 7. Test de l'intégration avec l'index
echo "7. Test de l'intégration avec l'index"
echo "--------------------------------------"

if [ -f "index.html" ]; then
    if grep -q "step_by_step_handler.php" "index.html"; then
        print_result 0 "Lien vers l'éditeur étape par étape présent dans index.html"
    else
        print_result 1 "Lien vers l'éditeur étape par étape manquant dans index.html"
    fi
fi

if [ -f "keybind_editor.php" ]; then
    if grep -q "step-by-step" "keybind_editor.php"; then
        print_result 0 "Intégration dans keybind_editor.php présente"
    else
        print_result 1 "Intégration dans keybind_editor.php manquante"
    fi
fi

echo ""

# 8. Vérification des dépendances
echo "8. Vérification des dépendances"
echo "-------------------------------"

dependencies=(
    "src/Application.php"
    "src/XMLProcessor.php"
    "bootstrap.php"
    "config.php"
)

for dep in "${dependencies[@]}"; do
    if [ -f "$dep" ]; then
        print_result 0 "Dépendance disponible: $dep"
    else
        print_result 1 "Dépendance manquante: $dep"
    fi
done

echo ""

# 9. Test du serveur (si en cours d'exécution)
echo "9. Test du serveur web"
echo "----------------------"

# Vérifier si un serveur est en cours d'exécution
if pgrep -f "php.*server" > /dev/null || pgrep -f "php -S" > /dev/null; then
    print_result 0 "Serveur PHP détecté"
    
    # Tenter une requête de test
    if command -v curl > /dev/null; then
        if curl -s -f -o /dev/null "http://localhost:8000/step_by_step_handler.php?step=1" 2>/dev/null; then
            print_result 0 "Endpoint step_by_step_handler.php accessible"
        else
            print_result 1 "Endpoint step_by_step_handler.php non accessible"
        fi
    else
        print_warning "curl non disponible pour tester les endpoints"
    fi
else
    print_warning "Aucun serveur PHP détecté. Lancez: php -S localhost:8000"
fi

echo ""

# 10. Résumé final
echo "10. Résumé final"
echo "=================="

echo ""
print_info "🎯 Tests terminés pour l'éditeur étape par étape"
echo ""
print_info "Pour tester manuellement:"
echo "  1. Lancez le serveur: php -S localhost:8000"
echo "  2. Ouvrez: http://localhost:8000/test_step_by_step_editor.html"
echo "  3. Ou directement: http://localhost:8000/step_by_step_handler.php?step=1"
echo ""
print_info "Documentation des étapes:"
echo "  • Étape 1: Upload du fichier XML avec validation"
echo "  • Étape 2: Reconnaissance et configuration des dispositifs"
echo "  • Étape 3: Édition des mappings avec interface intuitive"
echo "  • Étape 4: Résumé et téléchargement du fichier modifié"
echo ""

if [ $test_class_result -eq 0 ]; then
    echo -e "${GREEN}🎉 L'éditeur étape par étape est prêt à être testé !${NC}"
else
    echo -e "${RED}⚠️  Des erreurs ont été détectées. Vérifiez les logs ci-dessus.${NC}"
fi

echo ""
