#!/bin/bash

# Script maître de maintenance du projet sc-config-editor
# Centralise tous les outils de maintenance et organisation

echo "🛠️  Outils de Maintenance - SC Config Editor"
echo "============================================="
echo ""

# Fonction pour afficher le menu
show_menu() {
    echo "Choisissez une action :"
    echo ""
    echo "🧹 NETTOYAGE :"
    echo "  1) Analyse des fichiers à nettoyer (recommandé en premier)"
    echo "  2) Nettoyage rapide automatique"
    echo "  3) Nettoyage complet avec confirmations"
    echo ""
    echo "💾 CACHE :"
    echo "  4) Nettoyer le cache PHP"
    echo "  5) Nettoyer le cache web"
    echo ""
    echo "📁 ORGANISATION :"
    echo "  6) Prévisualiser l'organisation des fichiers"
    echo "  7) Organiser les fichiers"
    echo ""
    echo "  0) Quitter"
    echo ""
    read -p "Votre choix (0-7): " choice
}

# Boucle principale
while true; do
    show_menu
    
    case $choice in
        1)
            echo "🔍 Lancement de l'analyse..."
            bash scripts/cleanup/analyze_cleanup.sh
            ;;
        2)
            echo "🚀 Lancement du nettoyage rapide..."
            bash scripts/cleanup/cleanup_quick.sh
            ;;
        3)
            echo "🧹 Lancement du nettoyage complet..."
            bash scripts/cleanup/cleanup_comprehensive.sh
            ;;
        4)
            echo "💾 Nettoyage du cache PHP..."
            php scripts/cache/clear_cache.php
            ;;
        5)
            echo "🌐 Nettoyage du cache web..."
            php scripts/cache/clear_cache_web.php
            ;;
        6)
            echo "👀 Prévisualisation de l'organisation..."
            bash scripts/organization/preview_organization.sh
            ;;
        7)
            echo "📁 Organisation des fichiers..."
            bash scripts/organization/organize_files.sh
            ;;
        0)
            echo "👋 Au revoir !"
            exit 0
            ;;
        *)
            echo "❌ Choix invalide. Veuillez entrer un nombre entre 0 et 7."
            ;;
    esac
    
    echo ""
    echo "Appuyez sur Entrée pour continuer..."
    read
    clear
done
