// Gestion de l'interface utilisateur
export class UIHandler {
    constructor(bindingsHandler) {
        this.overlay = this.createOverlay();
        this.bindingsHandler = bindingsHandler;
        this.scrollTimeout = null;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Event listeners removed - events are now handled through SCConfigEditor delegation
        // to avoid double processing of the same events
    }

    handleButtonPress({ instance, buttonName, mode }) {
        let displayText = buttonName;
        if (mode === 'hold') {
            displayText = '[H] ' + buttonName;
        } else if (mode === 'double_tap') {
            displayText = '[DT] ' + buttonName;
        }
        
        this.showOverlay(displayText);
        if (this.getActiveInput()) {
            document.activeElement.value = buttonName;
        } else {
            // Extraire le numéro de bouton de buttonName (ex: js1_button1 -> 1)
            const buttonMatch = buttonName.match(/button(\d+)$/);
            const buttonNumber = buttonMatch ? buttonMatch[1] : null;
            
            if (buttonNumber) {
                const rows = this.bindingsHandler.findMappingRows('button', instance, buttonNumber, mode);
                const row = this.bindingsHandler.cycleRows(rows, buttonName, this.bindingsHandler.currentButtonIndex, mode);
                if (row) this.highlightRow(row);
            }
        }
    }

    handleAxisMove({ instance, axisName, value }) {
        this.showOverlay(axisName);
        if (this.getActiveInput()) {
            document.activeElement.value = axisName;
        } else {
            // Extraire le nom de l'axe sans le préfixe (ex: js1_axis9 -> axis9)
            const axisMatch = axisName.match(/^js\d+_(.+)$/);
            const cleanAxisName = axisMatch ? axisMatch[1] : axisName;
            
            const rows = this.bindingsHandler.findMappingRows('axis', instance, cleanAxisName);
            const row = this.bindingsHandler.cycleRows(rows, axisName, this.bindingsHandler.currentAxisIndex, '');
            if (row) this.highlightRow(row);
        }
    }

    handleHatMove({ instance, hatName, direction, mode }) {
        let displayText = hatName;
        if (mode === 'hold') {
            displayText = '[H] ' + hatName;
        } else if (mode === 'double_tap') {
            displayText = '[DT] ' + hatName;
        }
        
        this.showOverlay(displayText);
        if (this.getActiveInput()) {
            document.activeElement.value = hatName;
        } else {
            const rows = this.bindingsHandler.findMappingRows('hat', instance, direction, mode);
            const row = this.bindingsHandler.cycleRows(rows, hatName, this.bindingsHandler.currentHatIndex, mode);
            if (row) this.highlightRow(row);
        }
    }

    getActiveInput() {
        return document.activeElement && 
               document.activeElement.tagName === 'INPUT' && 
               document.activeElement.type === 'text';
    }

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '30px';
        overlay.style.left = '50%';
        overlay.style.transform = 'translateX(-50%)';
        overlay.style.background = '#222';
        overlay.style.color = '#fff';
        overlay.style.fontSize = '2.2em';
        overlay.style.fontWeight = 'bold';
        overlay.style.padding = '0.4em 1.2em';
        overlay.style.borderRadius = '0.5em';
        overlay.style.boxShadow = '0 2px 16px #000a';
        overlay.style.zIndex = '9999';
        overlay.style.display = 'none';
        document.body.appendChild(overlay);
        return overlay;
    }

    showOverlay(text) {
        this.overlay.textContent = text;
        this.overlay.style.display = 'block';
        clearTimeout(this.overlay._timeout);
        this.overlay._timeout = setTimeout(() => {
            this.overlay.style.display = 'none';
        }, 1200);
    }

    clearAllHighlights() {
        document.querySelectorAll('tr').forEach(row => {
            row.style.background = '';
        });
    }

    highlightRow(row) {
        this.clearAllHighlights();
        row.style.background = '#ffe066';
        
        // Annuler tout scroll en cours avant de démarrer le nouveau
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        // Défilement avec un petit délai pour éviter les conflits
        this.scrollTimeout = setTimeout(() => {
            row.scrollIntoView({behavior: 'smooth', block: 'center'});
        }, 50);
    }
}
