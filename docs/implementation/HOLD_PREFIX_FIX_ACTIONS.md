# CORRECTION PRÉFIXES [H] - ACTIONS AVEC "HOLD"

## Problème Identifié
Le préfixe `[H]` n'apparaissait pas dans les modales "Voir bindings" car la logique se basait uniquement sur `activationmode="hold"` au lieu de vérifier si le nom de l'action contient le terme "hold".

## Solution Implémentée

### 1. Logique Corrigée dans ActionFormatter
```javascript
static getActionPrefix(opts = '', value = '', actionName = '') {
    const optsLower = opts.toLowerCase();
    const valueLower = value.toLowerCase();
    
    // Mode Double Tap
    if ((optsLower === 'activationmode' && valueLower === 'double_tap') ||
        (optsLower === 'multitap' && value === '2')) {
        return '[DT] ';
    }
    
    // Mode Hold - basé sur le nom de l'action contenant "hold"
    if (actionName && actionName.toLowerCase().includes('hold')) {
        return '[H] ';
    }
    
    // Mode Hold - basé sur activationmode (garde pour compatibilité)
    if (optsLower === 'activationmode' && valueLower === 'hold') {
        return '[H] ';
    }
    
    return '';
}
```

### 2. Actions Concernées
Actions réelles trouvées dans la configuration qui devraient maintenant afficher le préfixe `[H]` :

- `v_ads_hold` → `[H] Visée (Hold)`
- `v_target_toggle_pin_index_1_hold` → `[H] Épingler Cible 1 (Hold)`
- `v_target_toggle_pin_index_2_hold` → `[H] Épingler Cible 2 (Hold)`
- `v_target_toggle_pin_index_3_hold` → `[H] Épingler Cible 3 (Hold)`
- `v_target_unpin_selected_hold` → `[H] Désépingler (Hold)`
- `v_weapon_bombing_toggle_desired_impact_point_hold` → `[H] Point d'Impact (Hold)`
- `turret_esp_hold` → `[H] ESP Tourelle (Hold)`
- `v_ifcs_esp_hold` → `[H] ESP IFCS (Hold)`

### 3. Logique de Priorité
1. **Double Tap** : `activationmode="double_tap"` OU `multitap="2"` → `[DT]`
2. **Hold (nom)** : nom de l'action contient "hold" → `[H]`
3. **Hold (mode)** : `activationmode="hold"` → `[H]`
4. **Aucun** : pas de préfixe

### 4. Compatibilité
- Garde la logique `activationmode="hold"` pour la compatibilité descendante
- Priorise la détection par nom d'action pour les actions avec "hold"
- Les deux conditions peuvent coexister (une action avec "hold" dans le nom ET `activationmode="hold"`)

## Tests
- **Test créé** : `/tests/html/test_final_hold_actions.html`
- **Actions testées** : 8 actions réelles avec "hold" dans le nom
- **Vérification** : Modal et formatage direct

## Statut
✅ **CORRECTION APPLIQUÉE ET TESTÉE**
- ActionFormatter mis à jour
- Tests créés et validés
- Compatible avec l'architecture existante
- Préfixes `[H]` maintenant visibles dans les modales

## Fichiers Modifiés
- `/assets/js/modules/actionFormatter.js` - Logique corrigée
- `/tests/html/test_final_hold_actions.html` - Test de validation

Date : 7 juin 2025
