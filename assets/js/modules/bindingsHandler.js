// Gestion des bindings et des actions
export class BindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
        
        // Indices séparés par mode pour hold mode anchor enhancement
        this.currentButtonIndexByMode = {}; // Structure: { 'js1_button1_hold': 0, 'js1_button1_': 0, etc. }
        this.currentHatIndexByMode = {};    // Structure: { 'js1_hat1_up_hold': 0, 'js1_hat1_up_': 0, etc. }
        
        this.lastInput = null; // Tracker le dernier input utilisé
        this.lastInputTime = 0; // Temps du dernier input
        this.lastCallTime = {}; // Temps des derniers appels par input pour éviter le spam
    }

    findMappingRows(type, jsIdx, value, mode = '') {
        switch(type) {
            case 'button':
                return this.findRowsForButton(jsIdx, value, mode);
            case 'axis':
                return this.findRowsForAxis(jsIdx, value);
            case 'hat':
                return this.findRowsForHat(jsIdx, value, mode);
        }
    }

    cycleRows(rows, inputName, currentIndexMap, mode = '') {
        if (!rows.length) {
            console.log(`[CycleRows] Aucune ligne trouvée pour ${inputName}`);
            return null;
        }
        
        const now = Date.now();
        const CYCLE_TIMEOUT = 1500; // 1.5 secondes pour considérer que c'est un nouvel input
        const MIN_CALL_INTERVAL = 50; // Minimum 50ms entre les appels pour le même input
        
        // Créer une clé unique pour l'input avec son mode
        const inputModeKey = `${inputName}_${mode}`;
        
        // Protection anti-spam : éviter les appels trop rapprochés
        const lastCallTime = this.lastCallTime[inputModeKey] || 0;
        if (now - lastCallTime < MIN_CALL_INTERVAL) {
            console.log(`[CycleRows] Appel ignoré pour ${inputModeKey} (spam protection: ${now - lastCallTime}ms < ${MIN_CALL_INTERVAL}ms)`);
            return null;
        }
        this.lastCallTime[inputModeKey] = now;
        
        // Hold Mode Anchor Enhancement: utiliser des indices séparés par mode
        let targetIndexMap;
        if (currentIndexMap === this.currentButtonIndex) {
            targetIndexMap = this.currentButtonIndexByMode;
        } else if (currentIndexMap === this.currentHatIndex) {
            targetIndexMap = this.currentHatIndexByMode;
        } else {
            // Fallback pour les axes qui n'ont pas de modes
            targetIndexMap = currentIndexMap;
        }
        
        // Vérifier si c'est le même input+mode répété rapidement
        const isSameInputRepeated = (this.lastInput === inputModeKey && (now - this.lastInputTime) < CYCLE_TIMEOUT);
        
        console.log(`[CycleRows] Input: ${inputModeKey}, Rows: ${rows.length}, LastInput: ${this.lastInput}, TimeDiff: ${now - this.lastInputTime}ms, SameRepeated: ${isSameInputRepeated}`);
        
        if (isSameInputRepeated) {
            // C'est un appui répété, on avance dans le cycle
            const currentIndex = targetIndexMap[inputModeKey] || 0;
            const newIndex = (currentIndex + 1) % rows.length;
            targetIndexMap[inputModeKey] = newIndex;
            console.log(`[CycleRows] Cycling: ${currentIndex} -> ${newIndex} (mode: ${mode})`);
        } else {
            // Nouveau input+mode, on commence au début
            targetIndexMap[inputModeKey] = 0;
            console.log(`[CycleRows] Nouveau input+mode, index reset à 0 (mode: ${mode})`);
        }
        
        // Mettre à jour le tracker
        this.lastInput = inputModeKey;
        this.lastInputTime = now;
        
        const selectedRow = rows[targetIndexMap[inputModeKey]];
        const action = selectedRow?.cells[2]?.textContent || 'Unknown';
        console.log(`[CycleRows] Sélection index ${targetIndexMap[inputModeKey]}: ${action} (mode: ${mode})`);
        
        return selectedRow;
    }

    findRowsForButton(jsIdx, btnIdx, mode) {
        let selector = `input[name^='input[']`;
        let rows = [];
        
        document.querySelectorAll(selector).forEach(input => {
            let val = input.value.trim();
            let regex = new RegExp(`^js${jsIdx}_button${btnIdx}$`, 'i');
            
            if (regex.test(val)) {
                let tr = input.closest('tr');
                if (tr) {
                    let opts = tr.querySelector('input[name^="opts["]')?.value.toLowerCase() || '';
                    let value = tr.querySelector('input[name^="value["]')?.value.toLowerCase() || '';
                    
                    if (mode === 'double_tap' && (opts === 'activationmode' && value === 'double_tap' || opts === 'multitap' && value === '2')) {
                        rows.push(tr);
                    } else if (mode === 'hold' && (opts === 'activationmode' && value === 'hold')) {
                        rows.push(tr);
                    } else if (!mode || (mode === '' && (!opts || (opts !== 'activationmode' && opts !== 'multitap')))) {
                        rows.push(tr);
                    }
                }
            }
        });
        return rows;
    }

    findRowsForAxis(jsIdx, axisName) {
        let selector = `input[name^='input[']`;
        let rows = [];
        document.querySelectorAll(selector).forEach(input => {
            let val = input.value.trim();
            let regex = new RegExp(`^js${jsIdx}_${axisName}$`, 'i');
            if (regex.test(val)) {
                let tr = input.closest('tr');
                if (tr) rows.push(tr);
            }
        });
        return rows;
    }

    findRowsForHat(jsIdx, hatDir, mode) {
        let selector = `input[name^='input[']`;
        let rows = [];
        document.querySelectorAll(selector).forEach(input => {
            let val = input.value.trim();
            // Utiliser un regex qui accepte n'importe quel numéro de HAT, pas seulement "hat1"
            let regex = new RegExp(`^js${jsIdx}_hat\\d+_${hatDir}$`, 'i');
            if (regex.test(val)) {
                let tr = input.closest('tr');
                if (tr) {
                    let opts = tr.querySelector('input[name^="opts["]')?.value.toLowerCase() || '';
                    let value = tr.querySelector('input[name^="value["]')?.value.toLowerCase() || '';
                    if (mode === 'double_tap' && (opts === 'activationmode' && value === 'double_tap' || opts === 'multitap' && value === '2')) {
                        rows.push(tr);
                    } else if (mode === 'hold' && (opts === 'activationmode' && value === 'hold')) {
                        rows.push(tr);
                    } else if (!mode || (mode === '' && (!opts || (opts !== 'activationmode' && opts !== 'multitap')))) {
                        rows.push(tr);
                    }
                }
            }
        });
        return rows;
    }
}
