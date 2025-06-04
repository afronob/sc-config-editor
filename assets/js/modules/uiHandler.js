// Gestion de l'interface utilisateur
export class UIHandler {
    constructor(bindingsHandler) {
        this.overlay = this.createOverlay();
        this.bindingsHandler = bindingsHandler;
        this.setupEventListeners();
    }

    setupEventListeners() {
        window.addEventListener('buttonPressed', (e) => this.handleButtonPress(e.detail));
        window.addEventListener('axisMoved', (e) => this.handleAxisMove(e.detail));
        window.addEventListener('hatMoved', (e) => this.handleHatMove(e.detail));
    }

    handleButtonPress({ instance, buttonName, mode }) {
        this.showOverlay(buttonName);
        if (this.getActiveInput()) {
            document.activeElement.value = buttonName;
        } else {
            const rows = this.bindingsHandler.findMappingRows('button', instance, buttonName, mode);
            const row = this.bindingsHandler.cycleRows(rows, buttonName, this.bindingsHandler.currentButtonIndex);
            if (row) this.highlightRow(row);
        }
    }

    handleAxisMove({ instance, axisName, value }) {
        this.showOverlay(axisName);
        if (this.getActiveInput()) {
            document.activeElement.value = axisName;
        } else {
            const rows = this.bindingsHandler.findMappingRows('axis', instance, axisName);
            const row = this.bindingsHandler.cycleRows(rows, axisName, this.bindingsHandler.currentAxisIndex);
            if (row) this.highlightRow(row);
        }
    }

    handleHatMove({ instance, hatName, direction }) {
        this.showOverlay(hatName);
        if (this.getActiveInput()) {
            document.activeElement.value = hatName;
        } else {
            const rows = this.bindingsHandler.findMappingRows('hat', instance, direction);
            const row = this.bindingsHandler.cycleRows(rows, hatName, this.bindingsHandler.currentHatIndex);
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
        row.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
}
