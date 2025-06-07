// Module pour gérer les modals de l'interface
import { ActionFormatter } from './actionFormatter.js';
export class ModalManager {
    constructor() {
        this.setupOverlay();
        this.setupBindingsModal();
    }

    setupOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.style.cssText = `
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #222;
            color: #fff;
            font-size: 2.2em;
            font-weight: bold;
            padding: 0.4em 1.2em;
            border-radius: 0.5em;
            box-shadow: 0 2px 16px #000a;
            z-index: 9999;
            display: none;
        `;
        document.body.appendChild(this.overlay);
    }

    setupBindingsModal() {
        this.bindingsModal = document.getElementById('joystick-bindings-modal');
        if (!this.bindingsModal) return;

        document.querySelectorAll('.show-bindings').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.showBindingsForInstance(link.getAttribute('data-instance'));
            });
        });
    }

    showOverlay(text, duration = 1200) {
        this.overlay.textContent = text;
        this.overlay.style.display = 'block';
        clearTimeout(this.overlay._timeout);
        this.overlay._timeout = setTimeout(() => {
            this.overlay.style.display = 'none';
        }, duration);
    }

    showBindingsForInstance(instance) {
        const rows = Array.from(document.querySelectorAll('#bindings-table tr')).slice(1); // skip header
        const bindingsByButton = {};

        // Collecter les bindings
        rows.forEach(row => {
            const inputCell = row.querySelector('input[name^="input["]');
            if (!inputCell) return;
            
            const val = inputCell.value.trim();
            const match = val.match(/^(js|kb)([0-9]+)_([\w\d]+)$/i);
            if (match && match[2] == instance) {
                const button = match[0]; // ex: js1_button1
                const action = row.cells[2].textContent;
                const category = row.cells[0].textContent;
                bindingsByButton[button] = bindingsByButton[button] || [];
                bindingsByButton[button].push({
                    action: action,
                    category: category,
                    opts: row.cells[4].querySelector('input')?.value || '',
                    value: row.cells[5].querySelector('input')?.value || ''
                });
            }
        });

        this.renderBindingsModal(instance, bindingsByButton);
    }

    renderBindingsModal(instance, bindingsByButton) {
        let html = `<b>Bindings pour Joystick #${instance}</b> ` +
                  `<button onclick="document.getElementById('joystick-bindings-modal').style.display='none'">✖</button><br>`;

        if (Object.keys(bindingsByButton).length === 0) {
            html += '<div>Aucun binding trouvé.</div>';
        } else {
            html += '<ul>';
            Object.keys(bindingsByButton)
                .sort((a, b) => {
                    const reg = /_(\D+)?(\d+)$/;
                    const ma = a.match(reg);
                    const mb = b.match(reg);
                    if (ma && mb && ma[2] && mb[2] && ma[1] === mb[1]) {
                        return parseInt(ma[2], 10) - parseInt(mb[2], 10);
                    }
                    return a.localeCompare(b);
                })
                .forEach(button => {
                    html += `<li><b>${button}</b><ul>`;
                    bindingsByButton[button].forEach(item => {
                        // Utiliser ActionFormatter pour formater l'action avec traduction et préfixe
                        const formattedAction = ActionFormatter.formatActionName(item.action, item.opts, item.value);
                        html += `<li>${formattedAction} <span style="color:#888">(${item.category})</span></li>`;
                    });
                    html += '</ul></li>';
                });
            html += '</ul>';
        }

        this.bindingsModal.innerHTML = html;
        this.bindingsModal.style.display = 'block';
    }
}
