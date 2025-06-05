// Gestion des bindings et des actions
export class BindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
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

    cycleRows(rows, inputName, currentIndexMap) {
        if (!rows.length) {
            console.log(`[CycleRows] Aucune ligne trouvée pour ${inputName}`);
            return null;
        }
        
        const now = Date.now();
        const CYCLE_TIMEOUT = 1500; // 1.5 secondes pour considérer que c'est un nouvel input
        const MIN_CALL_INTERVAL = 50; // Minimum 50ms entre les appels pour le même input
        
        // Protection anti-spam : éviter les appels trop rapprochés
        const lastCallTime = this.lastCallTime[inputName] || 0;
        if (now - lastCallTime < MIN_CALL_INTERVAL) {
            console.log(`[CycleRows] Appel ignoré pour ${inputName} (spam protection: ${now - lastCallTime}ms < ${MIN_CALL_INTERVAL}ms)`);
            return null;
        }
        this.lastCallTime[inputName] = now;
        
        // Vérifier si c'est le même input répété rapidement
        const isSameInputRepeated = (this.lastInput === inputName && (now - this.lastInputTime) < CYCLE_TIMEOUT);
        
        console.log(`[CycleRows] Input: ${inputName}, Rows: ${rows.length}, LastInput: ${this.lastInput}, TimeDiff: ${now - this.lastInputTime}ms, SameRepeated: ${isSameInputRepeated}`);
        
        if (isSameInputRepeated) {
            // C'est un appui répété, on avance dans le cycle
            const currentIndex = currentIndexMap[inputName] || 0;
            const newIndex = (currentIndex + 1) % rows.length;
            currentIndexMap[inputName] = newIndex;
            console.log(`[CycleRows] Cycling: ${currentIndex} -> ${newIndex}`);
        } else {
            // Nouveau input, on commence au début
            currentIndexMap[inputName] = 0;
            console.log(`[CycleRows] Nouveau input, index reset à 0`);
        }
        
        // Mettre à jour le tracker
        this.lastInput = inputName;
        this.lastInputTime = now;
        
        const selectedRow = rows[currentIndexMap[inputName]];
        const action = selectedRow?.cells[2]?.textContent || 'Unknown';
        console.log(`[CycleRows] Sélection index ${currentIndexMap[inputName]}: ${action}`);
        
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
            let regex = new RegExp(`^js${jsIdx}_hat1_${hatDir}$`, 'i');
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
