#!/bin/bash

# Script de normalisation des noms de fichiers de mapping
# Standardise tous les fichiers selon la convention: {vendor_id}_{product_id}_map.json

echo "🔧 Normalisation des Noms de Fichiers de Mapping"
echo "=============================================="

cd "$(dirname "$0")"

# Fonction pour nettoyer les IDs (supprimer 0x, mettre en minuscules)
clean_id() {
    echo "$1" | sed 's/0x//g' | tr '[:upper:]' '[:lower:]'
}

# Fonction pour extraire vendor/product depuis le contenu JSON
extract_ids_from_json() {
    local file="$1"
    local vendor_id=""
    local product_id=""
    
    # Extraire vendor_id (gérer les espaces autour des deux-points)
    vendor_id=$(grep -o '"vendor_id"[[:space:]]*:[[:space:]]*"[^"]*"' "$file" | sed 's/.*"vendor_id"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/')
    # Extraire product_id
    product_id=$(grep -o '"product_id"[[:space:]]*:[[:space:]]*"[^"]*"' "$file" | sed 's/.*"product_id"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/')
    
    # Nettoyer les IDs
    vendor_id=$(clean_id "$vendor_id")
    product_id=$(clean_id "$product_id")
    
    echo "${vendor_id}_${product_id}"
}

# Fonction pour proposer un nouveau nom basé sur le contenu
suggest_new_name() {
    local file="$1"
    local ids=$(extract_ids_from_json "$file")
    echo "${ids}_map.json"
}

# Créer un backup avant modifications
backup_dir="backups/mapping_normalization_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$backup_dir"
echo "📁 Création du backup dans: $backup_dir"

# Analyser tous les fichiers dans mappings/devices/
renamed_count=0
skipped_count=0
errors=0

echo ""
echo "🔍 Analyse des fichiers de mapping..."

for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        echo ""
        echo "📄 Analyse: $filename"
        
        # Créer un backup
        cp "$file" "$backup_dir/"
        
        # Suggérer le nouveau nom
        suggested_name=$(suggest_new_name "$file")
        echo "   💡 Nom suggéré: $suggested_name"
        
        # Vérifier si le nom est déjà correct
        if [ "$filename" = "$suggested_name" ]; then
            echo "   ✅ Déjà au bon format"
            ((skipped_count++))
            continue
        fi
        
        # Vérifier que le nouveau nom n'existe pas déjà
        new_path="mappings/devices/$suggested_name"
        if [ -f "$new_path" ]; then
            echo "   ⚠️  Le fichier $suggested_name existe déjà, ajout d'un suffixe"
            timestamp=$(date +%s)
            suggested_name="${suggested_name%.json}_${timestamp}.json"
            new_path="mappings/devices/$suggested_name"
        fi
        
        # Vérifier que les IDs sont valides
        if [[ ! "$suggested_name" =~ ^[a-f0-9]+_[a-f0-9]+_map\.json$ ]]; then
            echo "   ❌ IDs invalides détectés, conservation du nom original"
            ((errors++))
            continue
        fi
        
        # Renommer le fichier
        echo "   🔄 Renommage: $filename → $suggested_name"
        mv "$file" "$new_path"
        
        if [ $? -eq 0 ]; then
            echo "   ✅ Renommé avec succès"
            ((renamed_count++))
        else
            echo "   ❌ Erreur lors du renommage"
            ((errors++))
        fi
    fi
done

# Mettre à jour les références dans le code
echo ""
echo "🔄 Mise à jour des références dans le code..."

# Rechercher les références aux anciens noms dans les scripts de test
for script in test_device_detection_system.sh validate_mappings.sh; do
    if [ -f "$script" ]; then
        echo "📝 Vérification des références dans $script..."
        
        # Chercher les références aux fichiers de mapping
        if grep -q "device_.*_map\.json" "$script"; then
            echo "   ⚠️  Références à corriger trouvées dans $script"
        else
            echo "   ✅ Aucune référence spécifique trouvée"
        fi
    fi
done

# Générer un rapport de correspondance
echo ""
echo "📋 Génération du rapport de correspondance..."
correspondence_file="$backup_dir/renaming_correspondence.txt"

echo "# Rapport de Correspondance - Normalisation des Noms" > "$correspondence_file"
echo "# Date: $(date)" >> "$correspondence_file"
echo "# Backup: $backup_dir" >> "$correspondence_file"
echo "" >> "$correspondence_file"

for backup_file in "$backup_dir"/*.json; do
    if [ -f "$backup_file" ]; then
        old_name=$(basename "$backup_file")
        new_name=$(suggest_new_name "$backup_file")
        
        if [ "$old_name" != "$new_name" ]; then
            echo "$old_name -> $new_name" >> "$correspondence_file"
        fi
    fi
done

# Valider les nouveaux noms
echo ""
echo "🔍 Validation des nouveaux noms..."
./validate_mappings.sh > /dev/null 2>&1
validation_status=$?

# Rapport final
echo ""
echo "📊 Rapport de Normalisation"
echo "=========================="
echo "🔄 Fichiers renommés: $renamed_count"
echo "✅ Fichiers déjà conformes: $skipped_count"
echo "❌ Erreurs: $errors"
echo "📁 Backup: $backup_dir"
echo "📋 Correspondance: $correspondence_file"

if [ $validation_status -eq 0 ]; then
    echo "✅ Validation des mappings: SUCCÈS"
else
    echo "⚠️  Validation des mappings: AVERTISSEMENTS (voir détails avec ./validate_mappings.sh)"
fi

echo ""
if [ $errors -eq 0 ]; then
    echo "🎉 Normalisation terminée avec succès!"
    echo "📏 Convention appliquée: {vendor_id}_{product_id}_map.json"
    echo "   - vendor_id et product_id en hexadécimal minuscule"
    echo "   - Sans préfixe '0x'"
    echo "   - Format: [a-f0-9]+_[a-f0-9]+_map.json"
else
    echo "⚠️  Normalisation terminée avec $errors erreur(s)"
    echo "🔧 Vérifiez les fichiers signalés"
fi

echo ""
echo "💡 Prochaines étapes:"
echo "   1. Vérifiez les nouveaux noms: ls mappings/devices/"
echo "   2. Testez le système: ./test_device_detection_system.sh"
echo "   3. Si tout fonctionne, supprimez le backup: rm -rf $backup_dir"
