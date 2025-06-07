# Configuration de Développement - SC Config Editor

## 🚀 Serveur de Développement

**PORT STANDARDISÉ : 8080**

### Commandes de démarrage

```bash
# Démarrage du serveur PHP (depuis la racine du projet)
php -S localhost:8080

# URL d'accès
http://localhost:8080
```

### 📋 URLs importantes

- **Page principale** : http://localhost:8080/index.html
- **Test wizard complet** : http://localhost:8080/test_wizard_complete.html
- **Test détection device** : http://localhost:8080/test_device_detection.html
- **Éditeur de keybinds** : http://localhost:8080/keybind_editor.php

### 🎮 Structure des Mappings

- **Mappings finalisés** : `/mappings/devices/` - Fichiers JSON prêts pour production
- **Mappings générés** : `/mappings/generated/` - CSV auto-générés par le système  
- **Templates** : `/mappings/templates/` - Modèles réutilisables par fabricant/type
- **Validation** : `/mappings/validation/` - Tests temporaires avant finalisation

### 🔧 Configuration PHP

Le serveur utilise les paramètres par défaut de PHP avec :
- Document root : `/Users/bteffot/Projects/perso/sc-config-editor`
- Extensions requises : `json`, `fileinfo`, `mbstring`

### 📝 Notes importantes

1. **Toujours utiliser le port 8080** pour éviter les conflits
2. Le serveur doit être démarré depuis la racine du projet
3. Tester toujours sur http://localhost:8080 (pas 8000 ou autres ports)

### 🧪 Tests

Tous les fichiers de test sont configurés pour fonctionner avec le port 8080 :
- Scripts de démarrage automatique
- Liens internes
- Configuration CORS si nécessaire

### 🧹 Nettoyage des Ports

Si vous trouvez des serveurs indésirables sur d'autres ports :

```bash
# Vérifier qui utilise le port 8000 (ou tout autre port)
lsof -ti:8000

# Arrêter les processus indésirables
kill $(lsof -ti:8000)

# Vérifier que seul le port 8080 est utilisé
lsof -ti:8080  # Doit retourner un seul processus PHP
```

---

**⚠️ IMPORTANT** : Ne jamais utiliser d'autres ports (8000, 3000, etc.) pour éviter la confusion !
