// Point d'entrée principal de l'application
import { GamepadHandler } from './modules/gamepadHandler.js';
import { UIHandler } from './modules/uiHandler.js';
import { BindingsHandler } from './modules/bindingsHandler.js';
import { FilterHandler, BindingsModal } from './modules/filterHandler.js';

export class SCConfigEditor {
    constructor(config = {}) {
        this.bindings = new BindingsHandler();
        this.ui = new UIHandler(this.bindings);
        this.gamepad = new GamepadHandler();
        this.filter = new FilterHandler();
        this.modal = new BindingsModal();
        
        // Bind methods to preserve context
        this.bindings.findMappingRows = this.bindings.findMappingRows.bind(this.bindings);
        this.bindings.findRowsForButton = this.bindings.findRowsForButton.bind(this.bindings);
        this.bindings.findRowsForAxis = this.bindings.findRowsForAxis.bind(this.bindings);
        this.bindings.findRowsForHat = this.bindings.findRowsForHat.bind(this.bindings);
        this.bindings.cycleRows = this.bindings.cycleRows.bind(this.bindings);
        
        // Configuration initiale
        this.gamepad.buttonNamesByInstance = config.buttonNamesByInstance || {};
        this.devicesDataJs = config.devicesDataJs || [];

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.gamepad.init();
        this.renderGamepadDevicesList();
    }

    setupEventListeners() {
        window.addEventListener('gamepadconnected', () => this.renderGamepadDevicesList());
        window.addEventListener('gamepaddisconnected', () => this.renderGamepadDevicesList());
        
        // Écouter les événements de gamepad
        window.addEventListener('buttonPressed', (event) => {
            this.handleButtonPressed(event.detail);
        });
        
        window.addEventListener('axisMoved', (event) => {
            this.handleAxisMoved(event.detail);
        });
        
        window.addEventListener('hatMoved', (event) => {
            this.handleHatMoved(event.detail);
        });
    }

    renderGamepadDevicesList() {
        const container = document.getElementById('gamepad-devices-list');
        if (!container) return;

        let html = '<b>Devices connectés (API Gamepad):</b><br>';
        let gamepads = navigator.getGamepads ? navigator.getGamepads() : [];
        let any = false;

        for (let gp of gamepads) {
            if (!gp) continue;
            any = true;
            let instance = this.gamepad.getInstanceFromGamepad(gp) || '?';
            html += `<div style="margin-bottom:0.5em; padding-left:1em;">
                <b>${gp.id}</b> (index: ${gp.index}, instance XML: js${instance})<br>
                </div>`;
        }

        if (!any) html += '<i>Aucun device détecté.</i>';
        container.innerHTML = html;
    }
    
    // Gestionnaires d'événements gamepad
    handleButtonPressed(data) {
        const { instance, buttonName, mode } = data;
        
        // Afficher dans l'overlay avec le mode
        let displayText = buttonName;
        if (mode === 'hold') {
            displayText += ' [HOLD]';
        } else if (mode === 'double_tap') {
            displayText += ' [DOUBLE_TAP]';
        }
        
        this.ui.showInputOverlay(displayText);
        
        // Trouver et surligner les lignes correspondantes
        const rows = this.bindings.findRowsForButton(buttonName);
        if (rows.length > 0) {
            this.bindings.cycleRows(rows);
        }
    }
    
    handleAxisMoved(data) {
        const { instance, axisName, value } = data;
        
        // Afficher dans l'overlay
        this.ui.showInputOverlay(`${axisName}: ${value.toFixed(2)}`);
        
        // Trouver et surligner les lignes correspondantes
        const rows = this.bindings.findRowsForAxis(axisName);
        if (rows.length > 0) {
            this.bindings.cycleRows(rows);
        }
    }
    
    handleHatMoved(data) {
        const { instance, hatName } = data;
        
        // Afficher dans l'overlay
        this.ui.showInputOverlay(hatName);
        
        // Trouver et surligner les lignes correspondantes
        const rows = this.bindings.findRowsForHat(hatName);
        if (rows.length > 0) {
            this.bindings.cycleRows(rows);
        }
    }
}
