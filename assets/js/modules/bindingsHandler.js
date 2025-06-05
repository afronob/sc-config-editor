// Gestion des bindings et des actions
export class BindingsHandler {
    constructor() {
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
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
        if (!rows.length) return;
        
        if (currentIndexMap[inputName] === undefined || currentIndexMap[inputName] >= rows.length) {
            currentIndexMap[inputName] = 0;
        }

        return rows[currentIndexMap[inputName]];
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
