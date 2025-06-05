#!/bin/bash

echo "🧹 NETTOYAGE DES LOGS DE DEBUG"
echo "=============================="

# Fichier à nettoyer
FILE="/home/afronob/sc-config-editor/assets/js/modules/bindingsHandler.js"

# Créer une sauvegarde
cp "$FILE" "${FILE}.debug_backup"
echo "✅ Sauvegarde créée: ${FILE}.debug_backup"

# Supprimer les lignes de debug (console.log avec [CycleRows])
sed -i '/console\.log.*\[CycleRows\]/d' "$FILE"

echo "✅ Logs de debug supprimés de bindingsHandler.js"

# Vérifier que les lignes ont été supprimées
DEBUG_COUNT=$(grep -c "console.log.*\[CycleRows\]" "$FILE" || echo "0")

if [ "$DEBUG_COUNT" -eq 0 ]; then
    echo "✅ Nettoyage terminé avec succès"
    echo "📄 Sauvegarde disponible: ${FILE}.debug_backup"
else
    echo "⚠️  Quelques logs de debug restants: $DEBUG_COUNT"
fi

echo ""
echo "🎯 SYSTÈME DE CYCLING PRÊT POUR LA PRODUCTION!"
