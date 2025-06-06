# Système de Détection Gamepad - SC Config Editor

## 🎯 Vue d'ensemble

Le système de détection gamepad a été entièrement refactorisé pour supporter la détection avancée des modes d'input avec traitement uniforme des événements.

## 🚀 Fonctionnalités

### **Modes de Détection Supportés**

1. **Simple Press** - Appui normal
2. **Double Tap** - Double appui rapide (≤300ms entre relâchements)
3. **Hold** - Maintien prolongé (≥500ms)

### **Types d'Input Supportés**

- ✅ **Boutons** : `js1_button1`, `js1_button2`, etc.
- ✅ **Hats/D-Pad** : `js1_hat1_up`, `js1_hat1_down`, `js1_hat1_left`, `js1_hat1_right`
- ✅ **Axes** : `js1_x`, `js1_y`, `js1_z`, etc.

## 🏗️ Architecture

### **Modules JavaScript**

```
assets/js/
├── bindingEditor.js          # Point d'entrée et initialisation
├── scConfigEditor.js         # Classe principale et coordination
└── modules/
    ├── gamepadHandler.js     # Détection et traitement des inputs
    ├── uiHandler.js          # Interface utilisateur et overlay
    ├── bindingsHandler.js    # Gestion des bindings et surbrillance
    └── filterHandler.js      # Filtres et modales
```

### **Flux de Données**

1. **Détection** : `GamepadHandler` surveille les gamepads
2. **Traitement** : Analyse des modes (simple/double/hold)
3. **Événements** : Émission d'événements personnalisés
4. **Affichage** : `UIHandler` affiche l'overlay
5. **Bindings** : `BindingsHandler` surligne les lignes correspondantes

## 🔧 Détails Techniques

### **Logique de Détection des Modes**

#### Boutons
```javascript
// Tous les événements traités au RELÂCHEMENT uniquement
if (pressDuration >= HOLD_DELAY) {
    // HOLD détecté (≥500ms)
} else if (timeSinceLastRelease <= DOUBLE_TAP_DELAY) {
    // DOUBLE TAP détecté (≤300ms)
} else {
    // SIMPLE PRESS (avec délai d'attente pour double tap)
}
```

#### Hats (D-Pad)
```javascript
// Même logique que les boutons, appliquée à chaque direction
for (const dir in hat.directions) {
    let isActive = (val >= d.value_min && val <= d.value_max);
    // Traitement identique aux boutons pour chaque direction
}
```

### **Gestion des États**

- `lastButtonStates[instance][buttonIndex]` - État actuel des boutons
- `lastHatStates[instance][hatName]` - État actuel des directions de hat
- `pressStartTime[instance][buttonIndex]` - Temps de début d'appui
- `lastPressTime[instance][buttonIndex]` - Temps du dernier relâchement

## 🎮 Configuration des Devices

### **Structure des Données Device**

```javascript
{
    id: "VKB-Sim Gladiator NXT R",
    vendor_id: "0x231d",
    product_id: "0x0120",
    xml_instance: "1",
    buttons: {
        1: "js1_button1",
        2: "js1_button2"
    },
    hats: {
        9: {
            directions: {
                "up": { axis: 9, value_min: -1.05, value_max: -0.95 },
                "down": { axis: 9, value_min: 0.08, value_max: 0.18 },
                "left": { axis: 9, value_min: 0.66, value_max: 0.76 },
                "right": { axis: 9, value_min: -0.48, value_max: -0.38 }
            }
        }
    }
}
```

## 📊 Interface Utilisateur

### **Overlay d'Affichage**

- Position fixe en haut à droite
- Affichage temporaire (2 secondes)
- Format : `js1_button1 [HOLD]` ou `js1_hat1_up [DOUBLE_TAP]`

### **Surbrillance des Bindings**

- Surbrillance jaune des lignes correspondantes
- Cycling automatique entre plusieurs bindings
- Défilement automatique vers la ligne active

## 🧪 Tests

### **Page de Test**

Accès : `http://localhost:8000/test_gamepad.html`

### **Tests Recommandés**

1. **Boutons** :
   - Appui simple
   - Double tap rapide
   - Hold prolongé

2. **Hat/D-Pad** :
   - Directions simples
   - Double tap sur directions
   - Hold sur directions

3. **Combinaisons** :
   - Alternance entre modes
   - Transitions rapides
   - Tests de timing

## 🚨 Debugging

### **Logs Console**

Les événements hat incluent des logs debug :
```
Hat js1_hat1_up: PRESSED (axe 9, val: -0.85)
Hat js1_hat1_up released after 150ms, time since last release: 0ms
Hat activé: js1_hat1_up (up)
```

### **Variables Globales**

- `window.scConfigEditor` - Instance principale
- `window.devicesDataJs` - Données des devices

## 🔄 Événements Personnalisés

### **buttonPressed**
```javascript
{
    instance: "1",
    buttonName: "js1_button1", 
    mode: "hold" // "", "double_tap", "hold"
}
```

### **hatMoved**
```javascript
{
    instance: "1",
    hatName: "js1_hat1_up",
    direction: "up",
    mode: "double_tap" // "", "double_tap", "hold"
}
```

### **axisMoved**
```javascript
{
    instance: "1",
    axisName: "js1_x",
    value: 0.75
}
```

## 📈 Améliorations Futures

- Support des axes avec modes (pour triggers analogiques)
- Configuration personnalisable des délais
- Enregistrement de macros complexes
- Support multi-device simultané
