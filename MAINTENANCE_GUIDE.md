# 🛠️ Guide d'Utilisation - Scripts de Maintenance

## 🎯 **Point d'entrée unique - Script Maître**

```bash
./scripts/maintenance.sh
```

Ce script centralise tous les outils de maintenance du projet SC Config Editor.

## 📋 **Menu Principal**

```
🛠️  Outils de Maintenance - SC Config Editor
=============================================

Choisissez une action :

🧹 NETTOYAGE :
  1) Analyse des fichiers à nettoyer (recommandé en premier)
  2) Nettoyage rapide automatique
  3) Nettoyage complet avec confirmations

💾 CACHE :
  4) Nettoyer le cache PHP
  5) Nettoyer le cache web

📁 ORGANISATION :
  6) Prévisualiser l'organisation des fichiers
  7) Organiser les fichiers

  0) Quitter
```

## 🗂️ **Structure des Scripts**

### 🧹 **scripts/cleanup/** - Nettoyage
- `analyze_cleanup.sh` - Analyse les fichiers à nettoyer (sans suppression)
- `cleanup_quick.sh` - Nettoyage automatique rapide des fichiers temporaires
- `cleanup_comprehensive.sh` - Nettoyage complet avec confirmations

### 💾 **scripts/cache/** - Cache
- `clear_cache.php` - Nettoie le cache PHP de l'application
- `clear_cache_web.php` - Nettoie le cache web et les sessions

### 📁 **scripts/organization/** - Organisation
- `organize_files.sh` - Organise les fichiers de test/debug dans des dossiers
- `preview_organization.sh` - Prévisualise l'organisation sans modifier
- `organize_remaining_scripts.sh` - Organise les scripts de maintenance

### 🔍 **scripts/analysis/** - Analyse
- `analyze_missing_actions.php` - Analyse les actions manquantes
- `analyze_suggestions.php` - Analyse les suggestions de mapping

### 🔧 **scripts/integration/** - Intégration
- `integrate_mappings.php` - Intègre les mappings dans le système
- `integrate_mappings_smart.php` - Intégration intelligente des mappings
- `review_mappings.php` - Révision des mappings avant intégration

### 🐍 **scripts/python/** - Python
- `extractor.py` - Extracteur de données
- `importer.py` - Importeur de données

## 🚀 **Utilisation Recommandée**

### 1. **Analyse préliminaire**
```bash
./scripts/maintenance.sh
# Choisir option 1 : Analyse des fichiers à nettoyer
```

### 2. **Nettoyage du projet**
```bash
./scripts/maintenance.sh
# Choisir option 2 ou 3 selon vos besoins
```

### 3. **Maintenance du cache**
```bash
./scripts/maintenance.sh
# Choisir option 4 ou 5 pour nettoyer les caches
```

### 4. **Organisation des fichiers**
```bash
./scripts/maintenance.sh
# Choisir option 6 pour prévisualiser, puis 7 pour organiser
```

## 🔧 **Utilisation Directe**

Si vous préférez utiliser les scripts directement :

```bash
# Analyse des fichiers
./scripts/cleanup/analyze_cleanup.sh

# Nettoyage rapide
./scripts/cleanup/cleanup_quick.sh

# Organisation avec prévisualisation
./scripts/organization/preview_organization.sh
./scripts/organization/organize_files.sh

# Cache PHP
php scripts/cache/clear_cache.php

# Analyse avancée
php scripts/analysis/analyze_missing_actions.php
```

## 📝 **Documentation**

Chaque dossier contient un `README.md` avec :
- Description des scripts
- Instructions d'utilisation
- Exemples de commandes
- Date de création

## ⚠️ **Recommandations**

1. **Toujours** utiliser l'analyse avant le nettoyage
2. **Prévisualiser** l'organisation avant de l'appliquer
3. **Sauvegarder** les fichiers importants avant nettoyage
4. **Tester** les fonctionnalités après maintenance

## 🎯 **Avantages de cette Organisation**

- ✅ **Point d'entrée unique** - Plus besoin de chercher les scripts
- ✅ **Organisation logique** - Scripts groupés par fonction
- ✅ **Documentation complète** - README dans chaque dossier
- ✅ **Maintenance facilitée** - Structure claire et maintenable
- ✅ **Sécurité** - Scripts avec confirmations pour éviter les erreurs

---

*Cette organisation a été créée le 07/06/2025 pour améliorer la maintenabilité du projet SC Config Editor.*
