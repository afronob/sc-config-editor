#!/bin/bash

# Script de vérification finale - SC Config Editor
# Vérifie tous les aspects de la mise à jour de la page d'accueil

echo "🚀 VÉRIFICATION FINALE - SC CONFIG EDITOR"
echo "=========================================="

PROJECT_ROOT="/Users/bteffot/Projects/perso/sc-config-editor"
cd "$PROJECT_ROOT"

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

success_count=0
warning_count=0
error_count=0

function check_success() {
    echo -e "${GREEN}✅ $1${NC}"
    ((success_count++))
}

function check_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
    ((warning_count++))
}

function check_error() {
    echo -e "${RED}❌ $1${NC}"
    ((error_count++))
}

function check_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

echo ""
echo "📁 1. VÉRIFICATION DES FICHIERS PRINCIPAUX"
echo "-------------------------------------------"

# Vérifier les fichiers HTML essentiels
files_html=("index.html" "keybind_editor.php" "test_final_system.html" "test_configure_button.html" "test_gamepad.html" "test_hat_modes.html" "test_complete_system.html" "validate_button_fix.html")

for file in "${files_html[@]}"; do
    if [ -f "$file" ]; then
        check_success "$file existe"
    else
        check_error "$file manquant"
    fi
done

echo ""
echo "📚 2. VÉRIFICATION DE LA DOCUMENTATION"
echo "--------------------------------------"

# Vérifier les fichiers de documentation
docs=("DEVICE_AUTO_DETECTION_GUIDE.md" "DEVICE_AUTO_DETECTION_FINAL_REPORT.md" "VALIDATION_REPORT_BUTTON_FIX.md" "HAT_DYNAMIC_KEYS_FIX_COMPLETE.md" "MAINTENANCE_GUIDE.md")

for doc in "${docs[@]}"; do
    if [ -f "$doc" ]; then
        check_success "$doc disponible"
    else
        check_error "$doc manquant"
    fi
done

echo ""
echo "🔧 3. VÉRIFICATION DES FONCTIONNALITÉS HOMEPAGE"
echo "------------------------------------------------"

# Vérifier le contenu de index.html
if grep -q "avec détection automatique des devices" index.html; then
    check_success "Subtitle mis à jour avec détection automatique"
else
    check_error "Subtitle pas mis à jour"
fi

if grep -q "new-features" index.html; then
    check_success "Section nouvelles fonctionnalités présente"
else
    check_error "Section nouvelles fonctionnalités manquante"
fi

if grep -q "feature-tag" index.html; then
    check_success "Tags de fonctionnalités présents"
else
    check_error "Tags de fonctionnalités manquants"
fi

# Vérifier les nouveaux liens
new_links=("test_final_system.html" "test_configure_button.html" "validate_button_fix.html")
for link in "${new_links[@]}"; do
    if grep -q "href=\"$link\"" index.html; then
        check_success "Lien vers $link présent"
    else
        check_error "Lien vers $link manquant"
    fi
done

echo ""
echo "🌐 4. VÉRIFICATION SERVER ET ACCESSIBILITÉ"
echo "-------------------------------------------"

# Tester si le serveur répond
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/index.html | grep -q "200"; then
    check_success "Page d'accueil accessible (HTTP 200)"
else
    check_warning "Serveur non démarré ou page inaccessible"
fi

# Tester l'accessibilité des nouvelles pages
for page in "${new_links[@]}"; do
    if curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/$page" | grep -q "200"; then
        check_success "$page accessible"
    else
        check_warning "$page non accessible"
    fi
done

echo ""
echo "📱 5. VÉRIFICATION DU JAVASCRIPT"
echo "--------------------------------"

# Vérifier les fonctions JavaScript critiques
if grep -q "checkGamepads()" index.html; then
    check_success "Fonction checkGamepads() présente"
else
    check_error "Fonction checkGamepads() manquante"
fi

if grep -q "openDocumentation" index.html; then
    check_success "Menu documentation amélioré présent"
else
    check_error "Menu documentation amélioré manquant"
fi

if grep -q "gamepadconnected" index.html; then
    check_success "Événements gamepad configurés"
else
    check_error "Événements gamepad manquants"
fi

echo ""
echo "🎨 6. VÉRIFICATION DU CSS"
echo "-------------------------"

# Vérifier les nouvelles classes CSS
if grep -q "\.new-features" index.html; then
    check_success "Classes CSS nouvelles fonctionnalités"
else
    check_error "Classes CSS nouvelles fonctionnalités manquantes"
fi

if grep -q "\.feature-tag" index.html; then
    check_success "Styles feature-tag présents"
else
    check_error "Styles feature-tag manquants"
fi

if grep -q "icon-auto::before" index.html; then
    check_success "Nouvelles icônes CSS présentes"
else
    check_error "Nouvelles icônes CSS manquantes"
fi

echo ""
echo "📊 RÉSUMÉ FINAL"
echo "=================="
total=$((success_count + warning_count + error_count))
success_rate=0
if [ $total -gt 0 ]; then
    success_rate=$((success_count * 100 / total))
fi

echo -e "${GREEN}✅ Succès: $success_count${NC}"
echo -e "${YELLOW}⚠️  Avertissements: $warning_count${NC}"
echo -e "${RED}❌ Erreurs: $error_count${NC}"
echo -e "${BLUE}📈 Taux de réussite: $success_rate%${NC}"

echo ""
if [ $success_rate -ge 90 ]; then
    echo -e "${GREEN}🎉 EXCELLENT! La mise à jour de la homepage est complète et fonctionnelle.${NC}"
elif [ $success_rate -ge 75 ]; then
    echo -e "${YELLOW}👍 BIEN! La mise à jour est largement réussie avec quelques points d'attention.${NC}"
else
    echo -e "${RED}⚠️  ATTENTION! Des problèmes importants nécessitent une correction.${NC}"
fi

echo ""
echo "🔗 LIENS UTILES:"
echo "• Page d'accueil: http://localhost:8000/index.html"
echo "• Test système complet: http://localhost:8000/test_complete_system.html"
echo "• Validation: http://localhost:8000/validate_button_fix.html"

echo ""
echo "✨ Vérification terminée - $(date)"
