#!/bin/bash

# Script pour organiser les derniers scripts de nettoyage et maintenance
# Déplace les scripts clear/clean vers leurs dossiers appropriés

echo "🔧 Organisation des scripts de maintenance restants..."

# Couleurs pour le terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compteurs
moved_count=0
created_dirs=0

# Fonction pour créer un dossier s'il n'existe pas
ensure_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo -e "${BLUE}📁 Créé: $dir${NC}"
        ((created_dirs++))
    fi
}

# Fonction pour déplacer un fichier
move_file() {
    local source="$1"
    local target_dir="$2"
    local description="$3"
    
    if [ -f "$source" ]; then
        ensure_dir "$target_dir"
        mv "$source" "$target_dir/"
        echo -e "${GREEN}✅ Déplacé: $(basename "$source") → $target_dir/${NC}"
        echo "   💭 $description"
        ((moved_count++))
    fi
}

# Fonction pour demander confirmation
confirm() {
    read -p "$1 (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

echo ""
echo "=== CRÉATION DE LA STRUCTURE DE MAINTENANCE ==="

# Créer les dossiers pour les scripts de maintenance
ensure_dir "scripts/maintenance"
ensure_dir "scripts/cleanup"
ensure_dir "scripts/cache"
ensure_dir "scripts/organization"

echo ""
echo "=== DÉPLACEMENT DES SCRIPTS ==="

if confirm "Déplacer les scripts de nettoyage vers scripts/cleanup/"; then
    echo -e "${YELLOW}🧹 Scripts de nettoyage...${NC}"
    move_file "cleanup_comprehensive.sh" "scripts/cleanup" "Script de nettoyage complet avec confirmations"
    move_file "cleanup_quick.sh" "scripts/cleanup" "Script de nettoyage automatique rapide"
    move_file "analyze_cleanup.sh" "scripts/cleanup" "Script d'analyse des fichiers à nettoyer"
fi

if confirm "Déplacer les scripts de cache vers scripts/cache/"; then
    echo -e "${YELLOW}💾 Scripts de cache...${NC}"
    move_file "clear_cache.php" "scripts/cache" "Script de nettoyage du cache PHP"
    move_file "clear_cache_web.php" "scripts/cache" "Script de nettoyage du cache web"
fi

if confirm "Déplacer les scripts d'organisation vers scripts/organization/"; then
    echo -e "${YELLOW}📁 Scripts d'organisation...${NC}"
    move_file "organize_files.sh" "scripts/organization" "Script principal d'organisation des fichiers"
    move_file "preview_organization.sh" "scripts/organization" "Script de prévisualisation de l'organisation"
fi

echo ""
echo "=== CRÉATION D'INDEX ET DOCUMENTATION ==="

# Créer un index dans chaque dossier
create_index() {
    local dir="$1"
    local description="$2"
    local purpose="$3"
    
    if [ -d "$dir" ]; then
        cat > "$dir/README.md" << EOF
# $description

$purpose

## Fichiers présents

$(ls -la "$dir" | grep -v "^total" | grep -v "README.md" | tail -n +2)

## Utilisation

### Scripts disponibles :
$(for file in "$dir"/*.sh "$dir"/*.php; do
    if [ -f "$file" ]; then
        echo "- \`$(basename "$file")\` - $(head -n 10 "$file" | grep -E "^#.*" | tail -1 | sed 's/^# *//')"
    fi
done)

## Date de création
$(date +"%d/%m/%Y à %H:%M")

## Organisation
Ces scripts ont été déplacés automatiquement depuis la racine du projet pour une meilleure organisation.
EOF
        echo -e "${BLUE}📄 Index créé: $dir/README.md${NC}"
    fi
}

if confirm "Créer des index README dans chaque dossier de scripts"; then
    create_index "scripts/cleanup" "Scripts de Nettoyage" "Ce dossier contient les scripts pour nettoyer les fichiers temporaires, tests obsolètes et autres éléments non nécessaires du projet."
    create_index "scripts/cache" "Scripts de Cache" "Ce dossier contient les scripts pour gérer et nettoyer les caches PHP et web de l'application."
    create_index "scripts/organization" "Scripts d'Organisation" "Ce dossier contient les scripts pour organiser et restructurer les fichiers du projet."
fi

echo ""
echo "=== CRÉATION D'UN SCRIPT MAÎTRE ==="

if confirm "Créer un script maître pour tous les outils de maintenance"; then
    cat > "scripts/maintenance.sh" << 'EOF'
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
EOF
    chmod +x "scripts/maintenance.sh"
    echo -e "${GREEN}✅ Script maître créé: scripts/maintenance.sh${NC}"
fi

echo ""
echo "=== MISE À JOUR DU GITIGNORE ==="

if confirm "Mettre à jour le .gitignore pour les nouveaux dossiers de scripts"; then
    # Vérifier si les entrées existent déjà
    if ! grep -q "scripts/cleanup/" .gitignore 2>/dev/null; then
        cat >> .gitignore << EOF

# Scripts de maintenance (ajouté le $(date +%Y-%m-%d))
scripts/cleanup/
scripts/cache/
scripts/organization/

# Logs de maintenance
scripts/*.log
scripts/*/*.log
EOF
        echo -e "${GREEN}✅ .gitignore mis à jour${NC}"
    else
        echo -e "${YELLOW}ℹ️  .gitignore déjà à jour${NC}"
    fi
fi

echo ""
echo "=== RÉSUMÉ DE L'ORGANISATION ==="

echo -e "${BLUE}📁 Nouvelle structure des scripts:${NC}"
echo "scripts/"
echo "├── maintenance.sh (script maître)"
echo "├── cleanup/"
echo "│   ├── analyze_cleanup.sh"
echo "│   ├── cleanup_comprehensive.sh"
echo "│   ├── cleanup_quick.sh"
echo "│   └── README.md"
echo "├── cache/"
echo "│   ├── clear_cache.php"
echo "│   ├── clear_cache_web.php"
echo "│   └── README.md"
echo "├── organization/"
echo "│   ├── organize_files.sh"
echo "│   ├── preview_organization.sh"
echo "│   └── README.md"
echo "├── analysis/ (existant)"
echo "├── integration/ (existant)"
echo "└── python/ (existant)"

echo ""
echo "📊 RÉSUMÉ"
echo "========="
echo -e "${GREEN}📁 Dossiers créés: $created_dirs${NC}"
echo -e "${GREEN}📄 Fichiers déplacés: $moved_count${NC}"

echo ""
echo -e "${GREEN}🎉 Organisation des scripts terminée !${NC}"
echo ""
echo "💡 Pour utiliser les outils de maintenance :"
echo "   ./scripts/maintenance.sh"
echo ""
echo "💡 Recommandations :"
echo "   1. Testez le script maître: ./scripts/maintenance.sh"
echo "   2. Commitez les changements"
echo "   3. Les scripts sont maintenant organisés par fonction"
