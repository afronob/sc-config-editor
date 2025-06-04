export class FilterHandler {
    constructor() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        const filterBox = document.getElementById('filter-nonempty');
        const table = document.getElementById('bindings-table');
        
        if (filterBox) {
            filterBox.addEventListener('change', () => {
                this.updateFilters(filterBox.checked, table);
            });
        }
    }

    updateFilters(showOnlyNonEmpty, table) {
        if (!table) return;
        
        Array.from(table.rows).forEach((row, idx) => {
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
        return val.trim() === '' || 
               /^((js|kb|mo)[0-9]+_)$/i.test(val.trim()) || 
               /^mo_$/i.test(val.trim());
    }
}

export class BindingsModal {
    constructor() {
        this.modal = document.getElementById('joystick-bindings-modal');
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.querySelectorAll('.show-bindings').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.showBindings(link.getAttribute('data-instance'));
            });
        });
    }

    showBindings(instance) {
        const rows = Array.from(document.querySelectorAll('#bindings-table tr')).slice(1);
        const bindingsByButton = this.collectBindings(rows, instance);
        this.renderModal(instance, bindingsByButton);
    }

    collectBindings(rows, instance) {
        const bindingsByButton = {};
        
        rows.forEach(row => {
            const inputCell = row.querySelector('input[name^="input["]');
            if (!inputCell) return;
            
            const val = inputCell.value.trim();
            const match = val.match(/^(js|kb)([0-9]+)_([\w\d]+)$/i);
            
            if (match && match[2] == instance) {
                const button = match[0];
                const action = row.cells[2].textContent;
                const category = row.cells[0].textContent;
                const opts = row.cells[4].querySelector('input')?.value || '';
                const value = row.cells[5].querySelector('input')?.value || '';
                
                bindingsByButton[button] = bindingsByButton[button] || [];
                bindingsByButton[button].push({ action, category, opts, value });
            }
        });
        
        return bindingsByButton;
    }

    renderModal(instance, bindingsByButton) {
        let html = `<b>Bindings pour Joystick #${instance}</b> <button onclick="document.getElementById('joystick-bindings-modal').style.display='none'">✖</button><br>`;
        
        if (Object.keys(bindingsByButton).length === 0) {
            html += '<div>Aucun binding trouvé.</div>';
        } else {
            html += '<ul>';
            Object.keys(bindingsByButton)
                .sort(this.sortButtons)
                .forEach(button => {
                    html += '<li><b>' + button + '</b><ul>';
                    bindingsByButton[button].forEach(item => {
                        const prefix = this.getPrefix(item);
                        html += `<li>${prefix}${item.action} <span style="color:#888">(${item.category})</span></li>`;
                    });
                    html += '</ul></li>';
                });
            html += '</ul>';
        }
        
        if (this.modal) {
            this.modal.innerHTML = html;
            this.modal.style.display = 'block';
        }
    }

    sortButtons(a, b) {
        const reg = /_(\D+)?(\d+)$/;
        const ma = a.match(reg);
        const mb = b.match(reg);
        if (ma && mb && ma[2] && mb[2] && ma[1] === mb[1]) {
            return parseInt(ma[2], 10) - parseInt(mb[2], 10);
        }
        return a.localeCompare(b);
    }

    getPrefix(item) {
        if (item.opts.toLowerCase() === 'activationmode' && item.value.toLowerCase() === 'double_tap' ||
            item.opts.toLowerCase() === 'multitap' && item.value === '2') {
            return '[DT] ';
        }
        return '';
    }
}
