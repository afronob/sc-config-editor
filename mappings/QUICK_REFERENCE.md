# 🎮 Guide de Référence - Mappings de Joystick

## 🚀 Démarrage Rapide

### Nouveau Mapping Device
```bash
# 1. Détecter le device (via l'interface web)
open http://localhost:8080/test_device_detection.html

# 2. Le système génère automatiquement dans mappings/validation/
# 3. Tester et valider
# 4. Déplacer vers mappings/devices/ quand finalisé
```

### Utiliser un Template
```bash
# Copier un template existant
cp mappings/templates/template_vkb_base.json mappings/validation/new_device.json

# Éditer selon le device spécifique
# Tester via l'interface web
# Finaliser dans mappings/devices/
```

## 📁 Organisation des Fichiers

```
mappings/
├── devices/           # ✅ PRODUCTION - Mappings validés
│   ├── vkb_231d_0120_map.json
│   ├── vkb_231d_0201_map.json
│   └── device_0738_a221_map.json
├── generated/         # 🔄 AUTO-GÉNÉRÉS - CSV de confiance
│   ├── high_confidence_mappings.csv
│   ├── medium_confidence_mappings.csv
│   └── mapping_suggestions.csv
├── templates/         # 📋 MODÈLES - Templates réutilisables
│   ├── template_generic_joystick.json
│   └── template_vkb_base.json
└── validation/        # 🧪 TESTS - En cours de validation
    └── (fichiers temporaires)
```

## 🔧 Scripts Utiles

```bash
# Migration depuis ancienne structure
./migrate_mappings.sh

# Test du système complet
./test_device_detection_system.sh

# Démarrage serveur standardisé
./start-server.sh
```

## 📋 Convention de Nommage

### Fichiers de Device (Production)
```
{vendor_id}_{product_id}_map.json
```
**Exemples:**
- `vkb_231d_0120_map.json` (VKB Gladiator NXT)
- `device_0738_a221_map.json` (Mad Catz)

### Templates
```
template_{type}_{manufacturer}.json
```
**Exemples:**
- `template_generic_joystick.json`
- `template_vkb_base.json`
- `template_thrustmaster_base.json`

### CSV Générés
```
{confidence_level}_mappings.csv
{confidence_level}_for_review.csv
```

## 🛠️ Workflow de Mapping

1. **🔍 Détection** → Device détecté automatiquement
2. **📝 Génération** → Mapping créé dans `validation/`
3. **🧪 Test** → Validation via interface web
4. **✅ Production** → Déplacement vers `devices/`

## 🚨 Bonnes Pratiques

- ✅ Toujours tester avant de finaliser
- ✅ Utiliser les templates pour accélérer
- ✅ Documenter les devices spéciaux
- ❌ Ne jamais modifier directement dans `generated/`
- ❌ Ne pas supprimer sans backup

## 🔗 Liens Utiles

- **Test Wizard**: http://localhost:8080/test_wizard_complete.html
- **Détection Device**: http://localhost:8080/test_device_detection.html
- **Documentation**: [README.md](./README.md)
