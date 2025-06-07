#!/bin/bash

# Script de migration pour la nouvelle structure des mappings
# Déplace les fichiers existants vers la nouvelle organisation

echo "🔄 Migration vers la nouvelle structure de mappings"
echo "=================================================="

cd "$(dirname "$0")"

# Créer les dossiers si nécessaire
echo "📁 Création des dossiers..."
mkdir -p mappings/{devices,generated,templates,validation}

# Compteurs pour le rapport
moved_devices=0
moved_generated=0
errors=0

# Migrer les fichiers de mapping JSON depuis files/
echo ""
echo "📦 Migration des fichiers de mapping JSON..."
if [ -d "files" ]; then
    for file in files/*_map.json; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            target="mappings/devices/$filename"
            
            if [ ! -f "$target" ]; then
                mv "$file" "$target"
                echo "✅ Déplacé: $filename → mappings/devices/"
                ((moved_devices++))
            else
                echo "⚠️  Existe déjà: $filename (ignoré)"
            fi
        fi
    done
else
    echo "ℹ️  Dossier files/ non trouvé"
fi

# Migrer les CSV depuis temp/mappings/
echo ""
echo "📊 Migration des fichiers CSV..."
if [ -d "temp/mappings" ]; then
    for file in temp/mappings/*.csv; do
        if [ -f "$file" ]; then
            filename=$(basename "$file")
            target="mappings/generated/$filename"
            
            if [ ! -f "$target" ]; then
                cp "$file" "$target"  # Copier plutôt que déplacer pour garder l'historique
                echo "✅ Copié: $filename → mappings/generated/"
                ((moved_generated++))
            else
                echo "⚠️  Existe déjà: $filename (ignoré)"
            fi
        fi
    done
    
    # Déplacer le README si présent
    if [ -f "temp/mappings/README.md" ]; then
        cp "temp/mappings/README.md" "mappings/generated/README_legacy.md"
        echo "✅ Copié: README.md → mappings/generated/README_legacy.md"
    fi
else
    echo "ℹ️  Dossier temp/mappings/ non trouvé"
fi

# Vérifier l'intégrité des fichiers migrés
echo ""
echo "🔍 Vérification de l'intégrité..."
for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        if ! python3 -m json.tool "$file" > /dev/null 2>&1; then
            echo "❌ JSON invalide: $(basename "$file")"
            ((errors++))
        fi
    fi
done

# Rapport final
echo ""
echo "📋 Rapport de migration:"
echo "   📦 Mappings devices déplacés: $moved_devices"
echo "   📊 CSV générés copiés: $moved_generated"
echo "   ❌ Erreurs détectées: $errors"

# Vérifier la structure finale
echo ""
echo "📁 Structure finale des mappings:"
find mappings -type f -name "*.json" -o -name "*.csv" | sort

echo ""
if [ $errors -eq 0 ]; then
    echo "✅ Migration terminée avec succès!"
    echo "💡 Conseil: Vous pouvez maintenant supprimer temp/mappings/ si tout fonctionne"
else
    echo "⚠️  Migration terminée avec $errors erreur(s)"
    echo "🔧 Vérifiez les fichiers JSON signalés"
fi

echo ""
echo "📖 Consultez mappings/README.md pour la documentation complète"
