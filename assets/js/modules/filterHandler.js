import { ActionFormatter } from './actionFormatter.js';

export class FilterHandler {
    constructor() {
        this.initialized = false;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Use a slight delay to ensure DOM elements are available
        setTimeout(() => {
            this.initializeFilters();
        }, 100);
    }

    initializeFilters() {
        const filterNonEmpty = document.getElementById('filter-nonempty');
        const filterHold = document.getElementById('filter-hold');
        const table = document.getElementById('bindings-table');
        
        if (filterNonEmpty) {
            filterNonEmpty.addEventListener('change', () => {
                this.updateFilters(table);
            });
        }
        
        if (filterHold) {
            filterHold.addEventListener('change', () => {
                this.updateFilters(table);
            });
        }
        
        this.initialized = true;
        console.log('🎯 FilterHandler initialized successfully', {
            nonEmptyFilter: !!filterNonEmpty,
            holdFilter: !!filterHold,
            table: !!table
        });
    }

    updateFilters(table) {
        if (!table) return;
        
        // Ensure filters are initialized
        if (!this.initialized) {
            this.initializeFilters();
        }
        
        const filterNonEmpty = document.getElementById('filter-nonempty');
        const filterHold = document.getElementById('filter-hold');
        
        const showOnlyNonEmpty = filterNonEmpty && filterNonEmpty.checked;
        const showOnlyHold = filterHold && filterHold.checked;
        
        console.log('🔍 Updating filters:', { showOnlyNonEmpty, showOnlyHold });
        
        Array.from(table.rows).forEach((row, idx) => {
            if (idx === 0) return; // skip header
            
            // Récupérer les valeurs nécessaires
            const inputCell = row.cells[3] ? row.cells[3].querySelector('input') : null;
            const actionId = row.cells[1] ? row.cells[1].textContent.trim() : ''; // 2ème colonne = ID de l'action
            const actionName = row.cells[2] ? row.cells[2].textContent.trim() : ''; // 3ème colonne = nom de l'action
            
            if (!inputCell) return;
            
            const inputVal = inputCell.value;
            
            let shouldShow = true;
            
            // Appliquer le filtre non-vide si activé
            if (showOnlyNonEmpty && this.isBindingEmpty(inputVal)) {
                shouldShow = false;
            }
            
            // Appliquer le filtre hold si activé (regarder l'ID ET le nom de l'action)
            if (showOnlyHold && !this.isHoldModeAction(actionId, actionName)) {
                shouldShow = false;
            }
            
            row.style.display = shouldShow ? '' : 'none';
        });
    }

    isBindingEmpty(val) {
        return val.trim() === '' || 
               /^((js|kb|mo)[0-9]+_)$/i.test(val.trim()) || 
               /^mo_$/i.test(val.trim());
    }

    isHoldModeAction(actionId, actionName) {
        // Vérifier l'ID de l'action (colonne "action")
        const actionIdLower = actionId.toLowerCase();
        const hasHoldInId = actionIdLower.includes('_hold') || 
                           actionIdLower.endsWith('hold') ||
                           actionIdLower.includes('hold_');
        
        // Vérifier le nom de l'action (colonne "name") 
        const actionNameLower = actionName.toLowerCase();
        const hasHoldInName = actionNameLower.includes('(hold)') || 
                             actionNameLower.includes('hold') ||
                             actionNameLower.includes('maintenir') ||
                             actionNameLower.includes('continuous') ||
                             actionNameLower.includes('continu') ||
                             actionNameLower.includes('temporarily');
        
        // Retourner true si l'une des deux colonnes indique Hold
        return hasHoldInId || hasHoldInName;
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
            // Chercher l'input dans la 4ème colonne (index 3)
            const inputCell = row.cells[3] ? row.cells[3].querySelector('input') : null;
            if (!inputCell) return;
            
            const val = inputCell.value.trim();
            const match = val.match(/^(js|kb)([0-9]+)_([\w\d]+)$/i);
            
            if (match && match[2] == instance) {
                const button = match[0];
                const action = row.cells[1].textContent;
                const category = row.cells[0].textContent;
                
                // Chercher opts et value dans les colonnes 5 et 6
                const opts = row.cells[4] ? (row.cells[4].querySelector('input')?.value || '') : '';
                const value = row.cells[5] ? (row.cells[5].querySelector('input')?.value || '') : '';
                
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
                        // Utiliser ActionFormatter pour formater l'action avec traduction et préfixe
                        const formattedAction = ActionFormatter.formatActionName(item.action, item.opts, item.value);
                        html += `<li>${formattedAction} <span style="color:#888">(${item.category})</span></li>`;
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
}
