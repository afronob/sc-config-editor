# 🎮 Mappings de Joystick - SC Config Editor

## 📁 Structure des Mappings

### `/mappings/devices/`
Fichiers de mapping finalisés pour des dispositifs spécifiques :
- Format : `{vendor_id}_{product_id}_map.json`
- Exemple : `vkb_231d_0120_map.json` (VKB Gladiator NXT)
- **Statut** : Validés et prêts pour la production

### `/mappings/generated/`
Mappings générés automatiquement par le système :
- Fichiers CSV avec différents niveaux de confiance
- Mappings en attente de validation manuelle
- **Statut** : En cours de traitement

### `/mappings/templates/`
Templates de base pour différents types de dispositifs :
- Modèles pour joysticks, throttles, rudder, etc.
- Configurations par défaut pour fabricants courants
- **Statut** : Templates réutilisables

### `/mappings/validation/`
Fichiers temporaires pour la validation :
- Tests de mappings avant finalisation
- Backups des versions précédentes
- **Statut** : En cours de validation

## 🔄 Workflow de Mapping

1. **Détection** → Device détecté automatiquement
2. **Génération** → Mapping auto-généré dans `/generated/`
3. **Validation** → Test et ajustement dans `/validation/`
4. **Finalisation** → Mapping validé déplacé vers `/devices/`

## 📋 Convention de Nommage

### Fichiers de Device
```
{vendor_id}_{product_id}_map.json
```

### Fichiers Générés
```
mapping_{confidence_level}_{timestamp}.csv
```

### Templates
```
template_{device_type}_{manufacturer}.json
```

## 🔧 Maintenance

- **Backup automatique** avant chaque modification
- **Validation** obligatoire avant mise en production
- **Documentation** des changements dans git
