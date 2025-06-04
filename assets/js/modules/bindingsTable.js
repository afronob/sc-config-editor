// Module pour gérer le tableau des bindings
export class BindingsTable {
    constructor(tableId = 'bindings-table') {
        this.table = document.getElementById(tableId);
        this.bindEvents();
    }

    bindEvents() {
        // Gestionnaire de filtres
        const filterBox = document.getElementById('filter-nonempty');
        if (filterBox) {
            filterBox.addEventListener('change', () => this.handleFilter(filterBox.checked));
        }
    }

    handleFilter(showOnlyNonEmpty) {
        Array.from(this.table.rows).forEach((row, idx) => {
            if (idx === 0) return; // skip header
            const inputCell = row.querySelector('input[name^="input["]');
            if (!inputCell) return;
            const val = inputCell.value;
            if (showOnlyNonEmpty) {
                row.style.display = this.isBindingEmpty(val) ? 'none' : '';
            } else {
                row.style.display = '';
            }
        });
    }

    isBindingEmpty(val) {
        // Considère vide :
        // - chaîne vide
        // - jsX_, kbX_, moX_ (X = 1 ou 2 chiffres)
        // - mo1_ (souris), mo2_ etc.
        // - mo_ (cas rare)
        return val.trim() === '' || 
               /^((js|kb|mo)[0-9]+_)$/i.test(val.trim()) || 
               /^mo_$/i.test(val.trim());
    }
}
