// Système d'ancrage simplifié pour SC Config Editor
// Version sans cycling, focus sur l'ancrage direct par modes

export class SimplifiedBindingsHandler {
    constructor() {
        this.lastCallTime = {}; // Protection anti-spam
        this.overlayTimeout = null; // Timeout pour masquer l'overlay
    }

    /**
     * Point d'entrée principal : ancrage direct basé sur l'événement gamepad
     * @param {string} type - 'button', 'axis', 'hat'
     * @param {number} instance - Instance du gamepad (ex: 1 pour js1)
     * @param {string} elementName - Nom de l'élément (ex: 'button1', 'axis2', 'hat1_up')
     * @param {string} mode - '', 'hold', 'double_tap'
     */
    anchorToInput(type, instance, elementName, mode = '') {
        const now = Date.now();
        const MIN_CALL_INTERVAL = 50; // Protection anti-spam réduite (50ms au lieu de 100ms)
        
        // Construire le nom complet de l'input
        const inputName = `js${instance}_${elementName}`;
        const inputModeKey = `${inputName}_${mode}`;
        
        // Protection anti-spam
        const lastCallTime = this.lastCallTime[inputModeKey] || 0;
        if (now - lastCallTime < MIN_CALL_INTERVAL) {
            console.log(`[SimplifiedAnchor] Appel ignoré pour ${inputModeKey} (spam protection: ${now - lastCallTime}ms)`);
            return null;
        }
        this.lastCallTime[inputModeKey] = now;
        
        // Trouver la ligne cible selon le mode
        const targetRow = this.findTargetRow(inputName, mode);
        
        if (targetRow) {
            this.anchorToRow(targetRow, inputName, mode);
            return targetRow;
        } else {
            console.log(`[SimplifiedAnchor] Aucune ligne trouvée pour ${inputName} mode: ${mode}`);
            // Afficher overlay rouge pour input non mappé
            this.showUnmappedOverlay(inputName, mode);
            return null;
        }
    }

    /**
     * Trouve la ligne cible basée sur l'input et le mode
     * @param {string} inputName - Nom de l'input (ex: 'js1_button1')
     * @param {string} mode - '', 'hold', 'double_tap'
     */
    findTargetRow(inputName, mode) {
        console.log(`[FindTarget] Recherche pour ${inputName} mode: ${mode}`);
        
        // Sélectionner tous les inputs qui correspondent exactement
        const selector = `input[name^='input[']`;
        const matchingRows = [];
        
        document.querySelectorAll(selector).forEach(input => {
            const inputValue = input.value.trim();
            
            // Vérifier la correspondance exacte avec l'inputName
            if (inputValue === inputName) {
                const row = input.closest('tr');
                if (row) {
                    const rowMode = this.getRowMode(row);
                    console.log(`[FindTarget] Ligne trouvée: ${inputName}, mode ligne: ${rowMode}, mode recherché: ${mode}`);
                    
                    // Vérifier si le mode correspond
                    if (this.modeMatches(rowMode, mode)) {
                        matchingRows.push(row);
                    }
                }
            }
        });
        
        if (matchingRows.length > 0) {
            // Prendre la première ligne qui correspond (pas de cycling)
            const selectedRow = matchingRows[0];
            const action = selectedRow.cells[2]?.textContent || 'Unknown';
            console.log(`[FindTarget] Ligne sélectionnée: ${action} (mode: ${mode})`);
            return selectedRow;
        }
        
        return null;
    }

    /**
     * Détermine le mode d'une ligne basé sur ses colonnes opts et value
     * @param {HTMLElement} row - La ligne du tableau
     * @returns {string} - '', 'hold', 'double_tap'
     */
    getRowMode(row) {
        const optsInput = row.querySelector('input[name^="opts["]');
        const valueInput = row.querySelector('input[name^="value["]');
        
        const opts = optsInput?.value.toLowerCase() || '';
        const value = valueInput?.value.toLowerCase() || '';
        
        // Détecter Hold mode
        if (opts === 'activationmode' && value === 'hold') {
            return 'hold';
        }
        
        // Détecter Double Tap mode
        if ((opts === 'activationmode' && value === 'double_tap') ||
            (opts === 'multitap' && value === '2')) {
            return 'double_tap';
        }
        
        // Mode normal (pas de mode spécial)
        return '';
    }

    /**
     * Vérifie si le mode de la ligne correspond au mode recherché
     * @param {string} rowMode - Mode de la ligne
     * @param {string} searchMode - Mode recherché
     */
    modeMatches(rowMode, searchMode) {
        return rowMode === searchMode;
    }

    /**
     * Ancre sur la ligne trouvée (scroll et highlight)
     * @param {HTMLElement} row - La ligne cible
     * @param {string} inputName - Nom de l'input
     * @param {string} mode - Mode utilisé
     */
    anchorToRow(row, inputName, mode) {
        // Retirer les highlights précédents
        document.querySelectorAll('.gamepad-highlight').forEach(el => {
            el.classList.remove('gamepad-highlight');
        });
        
        // Ajouter le highlight à la nouvelle ligne
        row.classList.add('gamepad-highlight');
        
        // Faire défiler jusqu'à la ligne
        row.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Afficher l'information dans l'overlay
        const action = row.cells[2]?.textContent || 'Unknown';
        let modeDisplay = '';
        if (mode === 'hold') {
            modeDisplay = '[H]';
        } else if (mode === 'double_tap') {
            modeDisplay = '[DT]';
        }
        const displayText = `${inputName} ${modeDisplay}`;
        
        console.log(`[Anchor] Ancré sur: ${displayText} -> ${action}`);
        
        // Afficher l'overlay directement
        this.showOverlay(displayText, action);
    }

    /**
     * Affiche l'overlay avec le texte de l'input et l'action
     * @param {string} inputText - Texte de l'input (ex: "js1_button1 [HOLD]")
     * @param {string} action - Action correspondante
     */
    showOverlay(inputText, action) {
        // Chercher un overlay existant ou en créer un
        let overlay = document.getElementById('gamepad-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'gamepad-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 12px 20px;
                border-radius: 5px;
                z-index: 1000;
                font-weight: bold;
                font-size: 16px;
                font-family: Arial, sans-serif;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
                transition: opacity 0.3s ease;
                opacity: 0;
                pointer-events: none;
            `;
            document.body.appendChild(overlay);
        }
        
        // Mettre à jour le contenu
        overlay.innerHTML = `
            <div style="color: #90EE90; margin-bottom: 5px;">${inputText}</div>
            <div style="color: #FFF; font-size: 14px;">${action}</div>
        `;
        
        // Afficher avec animation
        overlay.style.opacity = '1';
        
        // Masquer après 2 secondes
        clearTimeout(this.overlayTimeout);
        this.overlayTimeout = setTimeout(() => {
            if (overlay) {
                overlay.style.opacity = '0';
            }
        }, 2000);
    }
    
    /**
     * Affiche un overlay rouge pour les inputs non mappés
     * @param {string} inputName - Nom de l'input (ex: "js3_button4")
     * @param {string} mode - Mode recherché ('', 'hold', 'double_tap')
     */
    showUnmappedOverlay(inputName, mode) {
        // Vérifier si on est dans un champ de saisie
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            // Ne pas afficher l'overlay si on est dans un champ
            return;
        }
        
        // Créer l'overlay s'il n'existe pas
        let overlay = document.querySelector('.gamepad-overlay-unmapped');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'gamepad-overlay-unmapped';
            overlay.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                background: rgba(220, 53, 69, 0.95);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                font-weight: bold;
                font-size: 16px;
                font-family: Arial, sans-serif;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
                transition: opacity 0.3s ease;
                opacity: 0;
                pointer-events: none;
                z-index: 10001;
                border-left: 4px solid #dc3545;
            `;
            document.body.appendChild(overlay);
        }
        
        // Formatage du mode pour l'affichage
        let modeDisplay = '';
        if (mode === 'hold') {
            modeDisplay = ' [H]';
        } else if (mode === 'double_tap') {
            modeDisplay = ' [DT]';
        }
        
        // Mettre à jour le contenu
        overlay.innerHTML = `
            <div style="color: #FFE6E6; margin-bottom: 5px; font-size: 14px;">
                <strong>${inputName}${modeDisplay}</strong>
            </div>
            <div style="color: #FFF; font-size: 12px;">
                ⚠️ Bouton non mappé dans la configuration
            </div>
        `;
        
        // Afficher avec animation
        overlay.style.opacity = '1';
        
        // Masquer après 3 secondes (un peu plus long pour les erreurs)
        clearTimeout(this.overlayTimeout);
        this.overlayTimeout = setTimeout(() => {
            if (overlay) {
                overlay.style.opacity = '0';
            }
        }, 3000);
    }
}

// Export pour utilisation modulaire
export default SimplifiedBindingsHandler;
