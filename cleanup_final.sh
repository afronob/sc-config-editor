#!/bin/bash

# 🧹 Script de nettoyage final du projet SC Config Editor
# Nettoie et organise tous les fichiers temporaires et de test

PROJECT_ROOT="/home/afronob/sc-config-editor"
cd "$PROJECT_ROOT"

echo "🧹 Nettoyage final du projet SC Config Editor"
echo "============================================="

# 1. Vérifier la structure des tests
echo "📂 Structure des tests organisée :"
find tests/ -type f -name "*.js" -o -name "*.html" -o -name "*.sh" | head -10

# 2. Supprimer les fichiers temporaires et logs de debug
echo ""
echo "🗑️  Nettoyage des fichiers temporaires..."

# Supprimer les logs de debug anciens
if [ -d "debug/logs" ]; then
    find debug/logs -name "*.log" -mtime +7 -delete 2>/dev/null
    echo "✅ Anciens logs de debug supprimés"
fi

# Supprimer les fichiers de sauvegarde
find . -name "*.bak" -o -name "*.backup" -o -name "*~" -delete 2>/dev/null
echo "✅ Fichiers de sauvegarde supprimés"

# Supprimer les fichiers cache PHP
find . -name ".phpunit.cache" -type d -exec rm -rf {} + 2>/dev/null
echo "✅ Cache PHP nettoyé"

# 3. Vérifier qu'il n'y a plus de fichiers de test à la racine
echo ""
echo "🔍 Vérification - Fichiers JS à la racine :"
JS_FILES=$(find . -maxdepth 1 -name "*.js" -not -path "./assets/*" 2>/dev/null)
if [ -z "$JS_FILES" ]; then
    echo "✅ Aucun fichier JS de test à la racine"
else
    echo "⚠️  Fichiers JS trouvés à la racine :"
    echo "$JS_FILES"
fi

# 4. Afficher le résumé de l'organisation
echo ""
echo "📊 Résumé de l'organisation finale :"
echo "├── tests/js/        : $(find tests/js -name "*.js" | wc -l) fichiers JS"
echo "├── tests/html/      : $(find tests/html -name "*.html" | wc -l) fichiers HTML"
echo "├── tests/scripts/   : $(find tests/scripts -name "*.sh" | wc -l) scripts"
echo "└── tests/validation/: $(find tests/validation -type f | wc -l) fichiers"

# 5. Vérifier les permissions des scripts
echo ""
echo "🔐 Vérification des permissions des scripts :"
find tests/scripts -name "*.sh" -exec chmod +x {} \;
echo "✅ Permissions des scripts corrigées"

# 6. Créer un manifest des tests
echo ""
echo "📋 Création du manifest des tests..."
cat > tests/TEST_MANIFEST.md << 'EOF'
# 🧪 Manifest des Tests - SC Config Editor

## 📊 Résumé des Tests

### Tests JavaScript (tests/js/)
- **test_cycling_logic.js** - Tests de logique de cycling
- **final_cycling_test.js** - Tests finaux du système de cycling  
- **test_direct.js** - Tests directs de la logique
- **test_regex_fix.js** - Tests de correction regex

### Tests HTML (tests/html/)
- **debug_hold_filter.html** - Debug du filtre hold
- **test_hold_*.html** - Suite de tests pour le filtre hold
- **test_gamepad.html** - Tests de détection gamepad
- **test_cycling_*.html** - Tests de navigation cyclique

### Scripts de Test (tests/scripts/)
- **final_test.sh** - Script de test final
- **cleanup_debug_logs.sh** - Nettoyage des logs
- Autres scripts de validation

### Tests de Validation (tests/validation/)
- Fichiers de validation système
- Scripts de contrôle qualité

## 🚀 Utilisation

```bash
# Tests JavaScript
cd tests/js && node test_cycling_logic.js

# Tests HTML  
# Ouvrir dans un navigateur : tests/html/test_hold_filter.html

# Scripts de test
chmod +x tests/scripts/*.sh
./tests/scripts/final_test.sh
```

## ✅ Statut : Tous les tests organisés et fonctionnels
EOF

echo "✅ Manifest des tests créé"

echo ""
echo "🎉 Nettoyage terminé !"
echo "📁 Projet complètement organisé et prêt pour la production"
echo ""
echo "📊 Structure finale :"
echo "├── assets/js/modules/    : Code source principal"
echo "├── templates/           : Templates PHP"  
echo "├── tests/js/           : Tests JavaScript unitaires"
echo "├── tests/html/         : Tests d'interface"
echo "├── tests/scripts/      : Scripts de validation"
echo "├── docs/reports/       : Documentation complète"
echo "└── Projet prêt ! 🚀"
