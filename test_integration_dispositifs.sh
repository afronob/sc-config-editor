#!/bin/bash

# Script de test automatisé pour vérifier l'intégration de la gestion des dispositifs
# Test du workflow complet : keybind_editor.php → upload XML → section gestion dispositifs

echo "🔧 Test Automatisé - Intégration Gestion Dispositifs"
echo "================================================"

cd /Users/bteffot/Projects/perso/sc-config-editor

# Vérifier que le serveur est lancé
echo "📡 Vérification serveur..."
if ! curl -s http://localhost:8080/ > /dev/null; then
    echo "❌ Serveur non accessible sur localhost:8080"
    echo "Lancez d'abord: ./start-server.sh"
    exit 1
fi
echo "✅ Serveur accessible"

# Vérifier les fichiers clés
echo ""
echo "📁 Vérification fichiers..."
files=(
    "assets/js/modules/bindingEditorIntegration.js"
    "assets/js/bindingEditor.js"
    "assets/js/modules/globalLogger.js"
    "keybind_editor.php"
    "templates/edit_form.php"
    "test_integration_xml.xml"
)

for file in "${files[@]}"; do
    if [[ -f "$file" ]]; then
        echo "✅ $file"
    else
        echo "❌ $file MANQUANT"
        exit 1
    fi
done

# Test de la page keybind_editor.php
echo ""
echo "🌐 Test page keybind_editor.php..."
response=$(curl -s -w "%{http_code}" http://localhost:8080/keybind_editor.php)
http_code=${response: -3}

if [[ "$http_code" == "200" ]]; then
    echo "✅ Page keybind_editor.php accessible (200)"
else
    echo "❌ Erreur HTTP $http_code pour keybind_editor.php"
    exit 1
fi

# Vérifier que les modules JS sont chargés
echo ""
echo "📦 Vérification modules JavaScript..."
js_files=(
    "assets/js/modules/bindingEditorIntegration.js"
    "assets/js/bindingEditor.js" 
    "assets/js/modules/globalLogger.js"
)

for js_file in "${js_files[@]}"; do
    response=$(curl -s -w "%{http_code}" "http://localhost:8080/$js_file")
    http_code=${response: -3}
    
    if [[ "$http_code" == "200" ]]; then
        echo "✅ $js_file accessible"
    else
        echo "❌ Erreur $http_code pour $js_file"
        exit 1
    fi
done

# Test simulation upload XML
echo ""
echo "📤 Test simulation upload XML..."

# Créer un petit script pour tester l'upload
upload_test_html=$(cat << 'EOF'
<!DOCTYPE html>
<html>
<head><title>Test Upload</title></head>
<body>
<form action="keybind_editor.php" method="post" enctype="multipart/form-data" id="uploadForm">
    <input type="file" name="file" accept=".xml" required>
    <input type="submit" value="Upload">
</form>
<script>
// Simuler sélection fichier et upload
setTimeout(() => {
    console.log('Test upload XML simulation');
    // En réalité, on va tester via interface automatisée
}, 1000);
</script>
</body>
</html>
EOF
)

echo "$upload_test_html" > test_upload_temp.html
echo "✅ Interface test upload créée"

# Test de l'interface automatisée
echo ""
echo "🔄 Test interface automatisée..."
response=$(curl -s -w "%{http_code}" http://localhost:8080/test_workflow_automatise.html)
http_code=${response: -3}

if [[ "$http_code" == "200" ]]; then
    echo "✅ Interface test automatisé accessible"
else
    echo "❌ Erreur $http_code pour interface test"
fi

# Vérifier les logs serveur
echo ""
echo "📋 Vérification logs serveur (dernières lignes)..."
if [[ -f "server.log" ]]; then
    echo "Dernières entrées du log serveur:"
    tail -5 server.log
else
    echo "⚠️ Pas de fichier server.log trouvé"
fi

# Instructions pour test manuel
echo ""
echo "🎯 Instructions pour test manuel:"
echo "1. Ouvrir: http://localhost:8080/test_workflow_automatise.html"
echo "2. Cliquer sur 'Test Complet Automatisé'"
echo "3. Vérifier que la section 'Gestion des dispositifs' apparaît"
echo "4. Tester les boutons 'Gérer les dispositifs' et 'Importer JSON'"
echo ""
echo "🔍 Pour debug approfondi:"
echo "- http://localhost:8080/test_injection_probleme.html"
echo "- http://localhost:8080/test_advanced_debug.html"
echo ""

# Test de contenu des fichiers clés
echo "🔍 Vérification contenu fichiers clés..."

# Vérifier que bindingEditorIntegration.js contient les nouvelles méthodes
if grep -q "debugDOMStructure" assets/js/modules/bindingEditorIntegration.js; then
    echo "✅ bindingEditorIntegration.js contient les améliorations debug"
else
    echo "⚠️ bindingEditorIntegration.js pourrait manquer des améliorations"
fi

if grep -q "TreeWalker" assets/js/modules/bindingEditorIntegration.js; then
    echo "✅ bindingEditorIntegration.js contient la recherche TreeWalker"
else
    echo "⚠️ bindingEditorIntegration.js pourrait manquer TreeWalker"
fi

# Vérifier que edit_form.php contient les éléments attendus
if grep -q "filter-nonempty" templates/edit_form.php; then
    echo "✅ edit_form.php contient filter-nonempty"
else
    echo "❌ edit_form.php MANQUE filter-nonempty"
fi

if grep -q "bindings-table" templates/edit_form.php; then
    echo "✅ edit_form.php contient bindings-table"
else
    echo "❌ edit_form.php MANQUE bindings-table"
fi

echo ""
echo "✅ Tests automatisés terminés!"
echo "🎯 Étape suivante: Tester manuellement avec l'interface automatisée"

# Nettoyer
rm -f test_upload_temp.html
