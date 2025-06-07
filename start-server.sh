#!/bin/bash

# Script de démarrage standardisé pour SC Config Editor
# PORT STANDARDISÉ : 8080

echo "🚀 Démarrage du serveur SC Config Editor..."
echo "📍 Port standardisé : 8080"
echo "🌐 URL : http://localhost:8080"
echo ""

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "index.html" ]; then
    echo "❌ Erreur : Ce script doit être exécuté depuis la racine du projet"
    echo "📁 Naviguez vers le dossier sc-config-editor avant d'exécuter ce script"
    exit 1
fi

# Démarrer le serveur PHP
echo "⚡ Démarrage du serveur PHP sur le port 8080..."
php -S localhost:8080
