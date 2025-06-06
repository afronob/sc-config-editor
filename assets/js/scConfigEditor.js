// Point d'entrée principal de l'application
import { GamepadHandler } from './modules/gamepadHandler.js';
import { UIHandler } from './modules/uiHandler.js';
import { BindingsHandler } from './modules/bindingsHandler.js';
import { SimplifiedBindingsHandler } from './modules/simplifiedBindingsHandler.js';
import { FilterHandler, BindingsModal } from './modules/filterHandler.js';

export class SCConfigEditor {
    constructor(config = {}) {
        // Utiliser le système simplifié pour l'ancrage direct
        this.simplifiedBindings = new SimplifiedBindingsHandler();
        
        // Garder l'ancien système en backup si nécessaire
        this.bindings = new BindingsHandler();
        this.ui = new UIHandler(this.bindings);
        this.gamepad = new GamepadHandler();
        this.filter = new FilterHandler();
        this.modal = new BindingsModal();
        
        // Configuration pour activer le mode simplifié
        this.useSimplifiedAnchoring = config.useSimplifiedAnchoring !== false; // Activé par défaut
        
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
        
        if (this.useSimplifiedAnchoring) {
            // Utiliser le système d'ancrage simplifié
            this.handleSimplifiedButtonPress(data);
        } else {
            // Déléguer à UIHandler (ancien système)
            this.ui.handleButtonPress({ instance, buttonName, mode });
        }
    }
    
    handleAxisMoved(data) {
        const { instance, axisName, value } = data;
        
        if (this.useSimplifiedAnchoring) {
            // Les axes n'ont pas de modes, ancrage direct
            this.handleSimplifiedAxisMove(data);
        } else {
            // Déléguer à UIHandler (ancien système)  
            this.ui.handleAxisMove({ instance, axisName, value });
        }
    }
    
    handleHatMoved(data) {
        const { instance, hatName, direction, mode } = data;
        
        if (this.useSimplifiedAnchoring) {
            // Utiliser le système d'ancrage simplifié pour les HATs
            this.handleSimplifiedHatMove(data);
        } else {
            // Déléguer à UIHandler (ancien système)
            this.ui.handleHatMove({ instance, hatName, direction, mode });
        }
    }
    
    // === SYSTÈME D'ANCRAGE SIMPLIFIÉ ===
    
    handleSimplifiedButtonPress(data) {
        const { instance, buttonName, mode } = data;
        
        // Extraire le numéro de bouton depuis buttonName (ex: js1_button1 -> button1)
        const buttonMatch = buttonName.match(/button(\d+)$/);
        if (!buttonMatch) {
            console.warn(`[SimplifiedAnchor] Format de bouton invalide: ${buttonName}`);
            return;
        }
        
        const elementName = `button${buttonMatch[1]}`;
        console.log(`[SimplifiedAnchor] Bouton: js${instance}_${elementName} mode: ${mode}`);
        
        // Ancrage direct selon le mode
        this.simplifiedBindings.anchorToInput('button', instance, elementName, mode);
    }
    
    handleSimplifiedAxisMove(data) {
        const { instance, axisName, value } = data;
        
        // Extraire le numéro d'axe depuis axisName (ex: js1_axis2 -> axis2)
        const axisMatch = axisName.match(/axis(\d+)$/);
        if (!axisMatch) {
            console.warn(`[SimplifiedAnchor] Format d'axe invalide: ${axisName}`);
            return;
        }
        
        const elementName = `axis${axisMatch[1]}`;
        console.log(`[SimplifiedAnchor] Axe: js${instance}_${elementName} (valeur: ${value})`);
        
        // Les axes n'ont pas de modes
        this.simplifiedBindings.anchorToInput('axis', instance, elementName, '');
    }
    
    handleSimplifiedHatMove(data) {
        const { instance, hatName, direction, mode } = data;
        
        // Construire le nom de l'élément HAT (ex: hat1_up)
        const hatMatch = hatName.match(/hat(\d+)$/);
        if (!hatMatch) {
            console.warn(`[SimplifiedAnchor] Format de HAT invalide: ${hatName}`);
            return;
        }
        
        const elementName = `hat${hatMatch[1]}_${direction}`;
        console.log(`[SimplifiedAnchor] HAT: js${instance}_${elementName} mode: ${mode}`);
        
        // Ancrage direct selon le mode
        this.simplifiedBindings.anchorToInput('hat', instance, elementName, mode);
    }
}
