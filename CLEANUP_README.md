# Scripts de Nettoyage

Ce dossier contient plusieurs scripts pour nettoyer les fichiers temporaires, de test et de debug obsolètes.

## 🔍 Scripts Disponibles

### 1. `analyze_cleanup.sh` - Analyse sans suppression
```bash
./analyze_cleanup.sh
```

**Fonction :** Analyse et liste tous les fichiers qui peuvent être supprimés, sans rien supprimer.

**Utilisation :** 
- Voir quels fichiers peuvent être nettoyés
- Obtenir des statistiques sur les fichiers temporaires
- Planifier le nettoyage avant de l'exécuter

### 2. `cleanup_quick.sh` - Nettoyage rapide automatique
```bash
./cleanup_quick.sh
```

**Fonction :** Supprime automatiquement les fichiers temporaires évidents et sûrs.

**Ce qui est supprimé :**
- ✅ Fichiers temporaires (temp_*.txt)
- ✅ Tests de debug évidents
- ✅ Anciens tests Hold obsolètes
- ✅ Tests de navigation simples
- ✅ Scripts de nettoyage précédents

**Avantages :**
- Rapide et sûr
- Pas de confirmations demandées
- Supprime seulement les fichiers évidents

### 3. `cleanup_comprehensive.sh` - Nettoyage complet avec confirmations
```bash
./cleanup_comprehensive.sh
```

**Fonction :** Nettoyage complet avec demande de confirmation pour chaque type de fichier.

**Ce qui peut être supprimé :**
- 🗃️ Fichiers temporaires
- 🐛 Fichiers de debug
- 🧪 Tests PHP obsolètes
- 🌐 Tests HTML obsolètes
- 📊 Fichiers CSV de mapping (regénérables)
- 🐍 Scripts Python temporaires
- 🧹 Anciens scripts de nettoyage

**Avantages :**
- Contrôle total sur ce qui est supprimé
- Confirmations pour chaque catégorie
- Génère un rapport de nettoyage
- Plus sûr pour les fichiers importants

## 📋 Workflow Recommandé

### Étape 1 : Analyse
```bash
./analyze_cleanup.sh
```
Voir ce qui peut être nettoyé sans rien supprimer.

### Étape 2A : Nettoyage Rapide (recommandé)
```bash
./cleanup_quick.sh
```
Supprimer les fichiers temporaires évidents.

### Étape 2B : Nettoyage Complet (optionnel)
```bash
./cleanup_comprehensive.sh
```
Pour un nettoyage plus approfondi avec confirmations.

### Étape 3 : Vérification
```bash
git status
```
Voir les changements avant de committer.

## 🛡️ Fichiers Protégés (JAMAIS supprimés)

### Code Source Principal
- `src/` (Application.php, DeviceManager.php, XMLProcessor.php)
- `templates/` (tous les templates PHP)
- `assets/` (CSS, JS, modules)

### Configuration et Données
- `files/` (CSV de configuration)
- `config.php`, `bootstrap.php`
- `keybind_editor.php` (interface principale)

### Tests Finaux Importants
- `tests/html/test_final_complete_hold_system.html`
- `tests/html/test_complete_hold_detection.html`
- `tests/html/test_complete_system.html`
- `tests/html/test_final_anchor.html`

### Documentation et Backups
- `docs/` (documentation complète)
- `backups/` (sauvegardes)
- `README.md`

## 📊 Types de Fichiers Nettoyés

| Catégorie | Exemples | Sûr à supprimer |
|-----------|----------|------------------|
| **Temporaires** | `temp_*.txt`, `missing_actions.txt` | ✅ Oui |
| **Debug** | `debug_*.php`, diagnostic files | ✅ Oui |
| **Tests PHP obsolètes** | `test_*.php` (racine) | ✅ Oui |
| **Tests HTML debug** | `debug_*.html`, `diagnostic_*.html` | ✅ Oui |
| **Anciens tests Hold** | Tests remplacés par finaux | ✅ Oui |
| **Tests navigation** | Tests d'ancrage obsolètes | ✅ Oui |
| **CSV mapping** | Fichiers regénérables | ⚠️ Avec confirmation |
| **Scripts Python** | Scripts temporaires | ⚠️ Avec confirmation |

## 💡 Conseils

1. **Toujours analyser d'abord** avec `analyze_cleanup.sh`
2. **Commencer par le nettoyage rapide** avec `cleanup_quick.sh`
3. **Utiliser git status** pour vérifier les changements
4. **Garder les backups** au cas où
5. **Lire les confirmations** dans le script complet

## 🆘 Récupération

Si vous supprimez accidentellement des fichiers importants :

1. **Git :** `git checkout -- <fichier>` pour restaurer un fichier
2. **Backups :** Restaurer depuis le dossier `backups/`
3. **Tests finaux :** Toujours conservés automatiquement

## 📈 Résultats Attendus

Après nettoyage, la structure sera :

```
sc-config-editor/
├── src/                    # Code source principal
├── templates/              # Templates PHP
├── assets/                 # CSS, JS, modules
├── files/                  # Configuration CSV
├── docs/                   # Documentation
├── tests/html/             # Tests finaux seulement
├── backups/                # Sauvegardes
├── config.php              # Configuration
├── bootstrap.php           # Bootstrap
├── keybind_editor.php      # Interface principale
└── README.md               # Documentation
```

**Bénéfices :**
- 🧹 Projet plus propre et organisé
- 📦 Taille réduite du workspace
- 🚀 Développement plus efficace
- 📋 Focus sur les fichiers importants
