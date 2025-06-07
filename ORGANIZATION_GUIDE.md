# Guide d'Organisation des Fichiers

Ce guide explique comment organiser le projet en déplaçant les fichiers de test, debug et temporaires dans des dossiers appropriés.

## 🎯 Objectif

Organiser la structure du projet pour :
- ✅ **Racine plus propre** - Seulement les fichiers essentiels
- ✅ **Meilleure navigation** - Fichiers groupés par fonction
- ✅ **Structure professionnelle** - Organisation claire et logique
- ✅ **Tests organisés** - Par catégorie et fonction

## 📁 Structure Actuelle vs Cible

### Avant (Racine encombrée)
```
sc-config-editor/
├── test_application.php          ❌ Test à la racine
├── test_device_matching.php      ❌ Test à la racine
├── debug_devices_data.php        ❌ Debug à la racine
├── high_confidence_mappings.csv  ❌ CSV temporaire à la racine
├── analyze_missing_actions.php   ❌ Script temporaire à la racine
├── extractor.py                  ❌ Script Python à la racine
└── ... (50+ fichiers à la racine)
```

### Après (Structure organisée)
```
sc-config-editor/
├── src/                          ✅ Code source principal
├── templates/                    ✅ Templates PHP
├── assets/                       ✅ CSS, JS, modules
├── files/                        ✅ Configuration CSV
├── tests/
│   ├── php/                      ✅ Tests PHP
│   ├── html/
│   │   ├── debug/                ✅ Tests debug HTML
│   │   ├── hold/                 ✅ Tests Hold
│   │   ├── navigation/           ✅ Tests navigation
│   │   ├── integration/          ✅ Tests intégration
│   │   ├── filters/              ✅ Tests filtres
│   │   ├── gamepad/              ✅ Tests gamepad
│   │   └── [tests finaux]        ✅ Tests importants
│   └── xml/                      ✅ Fichiers XML de test
├── debug/
│   ├── devices/                  ✅ Debug devices
│   └── diagnostic/               ✅ Diagnostic
├── temp/
│   └── mappings/                 ✅ CSV temporaires
├── scripts/
│   ├── analysis/                 ✅ Scripts d'analyse
│   ├── integration/              ✅ Scripts d'intégration
│   └── python/                   ✅ Scripts Python
├── config.php                    ✅ Configuration
├── keybind_editor.php            ✅ Interface principale
└── README.md                     ✅ Documentation
```

## 🚀 Scripts Disponibles

### 1. **Prévisualisation** (recommandé en premier)
```bash
./preview_organization.sh
```
- 🔍 **Montre** ce qui sera déplacé sans rien faire
- 📊 **Compte** les fichiers à organiser
- 💡 **Explique** la nouvelle structure

### 2. **Organisation complète** (avec confirmations)
```bash
./organize_files.sh
```
- 📁 **Crée** les dossiers nécessaires
- 🚚 **Déplace** les fichiers avec confirmations
- 📝 **Génère** des index README
- 🎯 **Met à jour** le .gitignore

## 📋 Types de Fichiers Organisés

### Tests PHP → `tests/php/`
| Fichier | Description |
|---------|-------------|
| `test_application.php` | Test de l'application |
| `test_device_matching.php` | Test matching devices |
| `test_fgetcsv*.php` | Tests CSV parsing |
| `test_validation_finale.php` | Test validation |

### Tests HTML → `tests/html/[catégorie]/`
| Catégorie | Dossier | Exemples |
|-----------|---------|----------|
| **Debug** | `debug/` | `debug_*.html`, `diagnostic_*.html` |
| **Hold** | `hold/` | `test_*hold*.html` |
| **Navigation** | `navigation/` | `test_*anchor*.html`, `test_*cycling*.html` |
| **Filtres** | `filters/` | `test_*filter*.html`, `test_*overlay*.html` |
| **Gamepad** | `gamepad/` | `test_*gamepad*.html`, `test_*hat*.html` |
| **Intégration** | `integration/` | Tests d'intégration (sauf finaux) |

### Debug → `debug/devices/`
- `debug_devices_data.php`
- `debug_devices_transmission.php`

### Mappings de Joystick → `mappings/`
- **Devices** (`mappings/devices/`) : Mappings finalisés `*_map.json`
- **Generated** (`mappings/generated/`) : CSV auto-générés avec niveaux de confiance
- **Templates** (`mappings/templates/`) : Modèles réutilisables par fabricant/type
- **Validation** (`mappings/validation/`) : Tests et validations temporaires

### CSV Temporaires → `temp/mappings/` (DÉPRÉCIÉ - utiliser `mappings/generated/`)
- `high_confidence_mappings.csv`
- `mapping_suggestions.csv`
- `*_confidence_*.csv`

### Scripts → `scripts/[type]/`
| Type | Dossier | Fichiers |
|------|---------|----------|
| **Analyse** | `analysis/` | `analyze_*.php` |
| **Intégration** | `integration/` | `integrate_*.php`, `review_*.php` |
| **Python** | `python/` | `*.py` |

## 🔒 Fichiers Protégés (Jamais déplacés)

### Code Source Principal
- ✅ `src/` (Application.php, DeviceManager.php, XMLProcessor.php)
- ✅ `templates/` (tous les templates PHP)
- ✅ `assets/` (CSS, JS, modules)

### Configuration et Interface
- ✅ `config.php`, `bootstrap.php`
- ✅ `keybind_editor.php` (interface principale)
- ✅ `files/` (CSV de configuration)

### Tests Finaux Importants
- ✅ `tests/html/test_final_complete_hold_system.html`
- ✅ `tests/html/test_complete_hold_detection.html`
- ✅ `tests/html/test_complete_system.html`

### Documentation et Système
- ✅ `docs/` (documentation complète)
- ✅ `backups/` (sauvegardes)
- ✅ `README.md`, `.gitignore`
- ✅ `Dockerfile`, `favicon.ico`

## 🛠️ Workflow Recommandé

### Étape 1 : Prévisualisation
```bash
# Voir ce qui sera organisé
./preview_organization.sh
```

### Étape 2 : Organisation
```bash
# Exécuter l'organisation avec confirmations
./organize_files.sh
```

### Étape 3 : Vérification
```bash
# Vérifier la nouvelle structure
tree
# ou
ls -la

# Vérifier que tout fonctionne
# Tester l'interface principale
open keybind_editor.php
```

### Étape 4 : Commit
```bash
# Voir les changements
git status

# Ajouter et committer
git add .
git commit -m "Organization: moved test/debug files to appropriate folders

- Moved test_*.php to tests/php/
- Organized HTML tests in tests/html/[category]/
- Moved debug files to debug/devices/
- Moved CSV mappings to temp/mappings/
- Moved scripts to scripts/[type]/
- Updated .gitignore for new structure
- Created README indexes in each folder"
```

## 📊 Avantages de l'Organisation

### Avant
❌ **50+ fichiers** à la racine  
❌ **Navigation difficile** entre les fichiers  
❌ **Structure confuse** pour les nouveaux développeurs  
❌ **Tests mélangés** avec le code source  
❌ **Fichiers temporaires** visibles  

### Après
✅ **~10 fichiers essentiels** à la racine  
✅ **Navigation intuitive** par dossier  
✅ **Structure professionnelle** et claire  
✅ **Tests organisés** par catégorie  
✅ **Fichiers temporaires** cachés  

## 🔧 Personnalisation

Si vous voulez modifier l'organisation :

1. **Éditez** `organize_files.sh`
2. **Ajustez** les patterns de fichiers
3. **Modifiez** les dossiers de destination
4. **Testez** avec `preview_organization.sh`

## 🆘 Récupération

Si quelque chose ne va pas :

### Git (si committé)
```bash
# Annuler le dernier commit
git reset --hard HEAD~1

# Ou restaurer un fichier
git checkout HEAD~1 -- <fichier>
```

### Backup manuel
```bash
# Avant d'organiser, créer un backup
cp -r . ../sc-config-editor-backup
```

## 📞 Support

En cas de problème :
1. Vérifiez les fichiers dans `backups/`
2. Utilisez `git status` et `git log`
3. Les tests finaux restent toujours accessibles
4. La documentation dans `docs/` est préservée

---

**Prêt à organiser ?** Commencez par `./preview_organization.sh` ! 🚀
