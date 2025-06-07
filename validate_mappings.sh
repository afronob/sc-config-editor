#!/bin/bash

# Script de validation des mappings de joystick
# Vérifie l'intégrité et la cohérence des fichiers de mapping

echo "🔍 Validation des Mappings de Joystick"
echo "===================================="

cd "$(dirname "$0")"

# Compteurs
valid_devices=0
invalid_devices=0
valid_templates=0
invalid_templates=0
warnings=0

# Fonction de validation JSON
validate_json() {
    local file="$1"
    local type="$2"
    
    echo -n "🔍 $(basename "$file")... "
    
    # Vérifier la syntaxe JSON
    if ! python3 -m json.tool "$file" > /dev/null 2>&1; then
        echo "❌ JSON invalide"
        return 1
    fi
    
    # Vérifier les champs requis selon le type
    if [ "$type" = "device" ]; then
        # Champs requis pour un mapping de device
        required_fields=("id" "vendor_id" "product_id" "xml_instance")
        for field in "${required_fields[@]}"; do
            if ! grep -q "\"$field\"" "$file"; then
                echo "❌ Champ manquant: $field"
                return 1
            fi
        done
        
        # Vérifier la structure des axes (axes_map ou axes)
        if ! grep -q "\"axes_map\"" "$file" && ! grep -q "\"axes\"" "$file"; then
            echo "⚠️  Aucun mapping d'axes défini"
            ((warnings++))
        fi
        
    elif [ "$type" = "template" ]; then
        # Champs requis pour un template
        if ! grep -q "\"template_name\"" "$file"; then
            echo "❌ Champ template_name manquant"
            return 1
        fi
        
        if ! grep -q "\"manufacturer\"" "$file" && ! grep -q "\"generic\"" "$file"; then
            echo "⚠️  Pas de fabricant spécifié"
            ((warnings++))
        fi
    fi
    
    echo "✅ Valide"
    return 0
}

# Valider les mappings de devices
echo ""
echo "📦 Validation des mappings de devices..."
if [ -d "mappings/devices" ]; then
    for file in mappings/devices/*.json; do
        if [ -f "$file" ]; then
            if validate_json "$file" "device"; then
                ((valid_devices++))
            else
                ((invalid_devices++))
            fi
        fi
    done
else
    echo "⚠️  Dossier mappings/devices/ non trouvé"
fi

# Valider les templates
echo ""
echo "📋 Validation des templates..."
if [ -d "mappings/templates" ]; then
    for file in mappings/templates/*.json; do
        if [ -f "$file" ]; then
            if validate_json "$file" "template"; then
                ((valid_templates++))
            else
                ((invalid_templates++))
            fi
        fi
    done
else
    echo "⚠️  Dossier mappings/templates/ non trouvé"
fi

# Vérifier les doublons de xml_instance
echo ""
echo "🔄 Vérification des doublons xml_instance..."
xml_instances=()
duplicates=0

for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        instance=$(grep -o '"xml_instance":"[^"]*"' "$file" 2>/dev/null | cut -d'"' -f4)
        if [ -n "$instance" ]; then
            for existing in "${xml_instances[@]}"; do
                if [ "$existing" = "$instance" ]; then
                    echo "❌ Doublon xml_instance: $instance dans $(basename "$file")"
                    ((duplicates++))
                    break
                fi
            done
            xml_instances+=("$instance")
        fi
    fi
done

if [ $duplicates -eq 0 ]; then
    echo "✅ Aucun doublon xml_instance détecté"
fi

# Vérifier la cohérence des noms de fichiers
echo ""
echo "📝 Vérification des noms de fichiers..."
naming_errors=0

for file in mappings/devices/*.json; do
    if [ -f "$file" ]; then
        filename=$(basename "$file")
        if [[ ! "$filename" =~ ^[a-z0-9_]+_[a-z0-9_]+_map\.json$ ]]; then
            echo "⚠️  Nom non conforme: $filename (format attendu: vendor_product_map.json)"
            ((naming_errors++))
        fi
    fi
done

if [ $naming_errors -eq 0 ]; then
    echo "✅ Tous les noms de fichiers sont conformes"
fi

# Rapport final
echo ""
echo "📊 Rapport de Validation"
echo "======================="
echo "📦 Devices:"
echo "   ✅ Valides: $valid_devices"
echo "   ❌ Invalides: $invalid_devices"
echo ""
echo "📋 Templates:"
echo "   ✅ Valides: $valid_templates"
echo "   ❌ Invalides: $invalid_templates"
echo ""
echo "⚠️  Avertissements: $warnings"
echo "🔄 Doublons xml_instance: $duplicates"
echo "📝 Erreurs de nommage: $naming_errors"

total_errors=$((invalid_devices + invalid_templates + duplicates))

echo ""
if [ $total_errors -eq 0 ]; then
    echo "🎉 Validation réussie ! Tous les mappings sont valides."
    if [ $warnings -gt 0 ]; then
        echo "💡 Note: $warnings avertissement(s) à examiner."
    fi
    exit 0
else
    echo "❌ Validation échouée : $total_errors erreur(s) détectée(s)."
    echo "🔧 Corrigez les erreurs avant de continuer."
    exit 1
fi
