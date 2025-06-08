// Système d'ancrage simplifié pour SC Config Editor
// Version sans cycling, focus sur l'ancrage direct par modes

import { ActionFormatter } from './actionFormatter.js';

export class SimplifiedBindingsHandler {
    constructor() {
        this.lastCallTime = {}; // Protection anti-spam
        this.overlayTimeout = null; // Timeout pour masquer l'overlay
    }

    /**
     * Vérifie si un XML est chargé en contrôlant la présence du tableau de bindings avec des données
     * @returns {boolean} - true si un XML est chargé, false sinon
     */
    isXMLLoaded() {
        const table = document.getElementById('bindings-table');
        if (!table) {
            console.log(`[XMLCheck] Tableau bindings-table introuvable`);
            return false;
        }
        
        // Compter les lignes de données (exclure l'en-tête)
        const dataRows = table.querySelectorAll('tbody tr');
        if (dataRows.length === 0) {
            console.log(`[XMLCheck] Aucune ligne de données dans le tableau`);
            return false;
        }
        
        // Vérifier qu'au moins une ligne contient des données valides
        let hasValidData = false;
        for (const row of dataRows) {
            const inputField = row.querySelector('input[name^="input["]');
            if (inputField && inputField.value.trim() !== '') {
                // Vérifier que ce n'est pas juste un préfixe vide (js1_, js2_, etc.)
                if (!/^(js|kb|mo)\d+_?$/.test(inputField.value.trim())) {
                    hasValidData = true;
                    break;
                }
            }
        }
        
        if (!hasValidData) {
            console.log(`[XMLCheck] Aucune donnée de binding valide trouvée`);
            return false;
        }
        
        console.log(`[XMLCheck] XML chargé avec ${dataRows.length} lignes, données valides détectées`);
        return true;
    }

    /**
     * Vérifie si l'instance d'un device est valide (correspond à un device configuré)
     * @param {number|string} instance - Instance du device (ex: 1, "1", "231d_0200")
     * @returns {boolean} - true si l'instance est valide, false sinon
     */
    isDeviceInstanceValid(instance) {
        if (!window.devicesDataJs || !Array.isArray(window.devicesDataJs)) {
            console.log(`[DeviceCheck] devicesDataJs non disponible`);
            return false;
        }
        
        // Convertir l'instance en string pour la comparaison
        const instanceStr = String(instance);
        
        // Vérifier si cette instance existe dans les données des devices
        const deviceFound = window.devicesDataJs.find(device => {
            return device.xml_instance && String(device.xml_instance) === instanceStr;
        });
        
        if (deviceFound) {
            console.log(`[DeviceCheck] Instance ${instanceStr} valide (device: ${deviceFound.product || deviceFound.id})`);
            return true;
        } else {
            console.log(`[DeviceCheck] Instance ${instanceStr} invalide - device non configuré dans le XML`);
            return false;
        }
    }

    /**
     * Point d'entrée principal : ancrage direct basé sur l'événement gamepad
     * @param {string} type - 'button', 'axis', 'hat'
     * @param {number} instance - Instance du gamepad (ex: 1 pour js1)
     * @param {string} elementName - Nom de l'élément (ex: 'button1', 'axis2', 'hat1_up')
     * @param {string} mode - '', 'hold', 'double_tap'
     */
    anchorToInput(type, instance, elementName, mode = '') {
        // Vérifier si un XML est chargé avant d'activer le feedback
        if (!this.isXMLLoaded()) {
            console.log(`[SimplifiedAnchor] Aucun XML chargé, ancrage désactivé`);
            return null;
        }
        
        // Vérifier si l'instance du device est valide (configurée dans le XML)
        if (!this.isDeviceInstanceValid(instance)) {
            console.log(`[SimplifiedAnchor] Instance ${instance} non configurée, proposer configuration...`);
            this.proposeDeviceConfiguration(instance, type, elementName);
            return null;
        }
        
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
        
        // Utiliser ActionFormatter pour formater l'action avec traduction et préfixe
        const formattedAction = ActionFormatter.formatActionNameByMode(action, mode);
        
        // Utiliser ActionFormatter pour formater également le nom de l'input avec préfixe
        const displayText = ActionFormatter.formatActionNameByMode(inputName, mode);
        
        console.log(`[Anchor] Ancré sur: ${displayText} -> ${formattedAction}`);
        
        // Afficher l'overlay directement
        this.showOverlay(displayText, formattedAction);
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
        
        // Formatage du mode pour l'affichage en utilisant ActionFormatter
        const formattedInput = ActionFormatter.formatActionNameByMode(inputName, mode);
        
        // Mettre à jour le contenu
        overlay.innerHTML = `
            <div style="color: #FFE6E6; margin-bottom: 5px; font-size: 14px;">
                <strong>${formattedInput}</strong>
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

    /**
     * Propose la configuration d'un device non instancié
     * @param {number|string} instance - Instance du device (ex: 1, "1")
     * @param {string} type - Type d'input ('button', 'axis', 'hat')  
     * @param {string} elementName - Nom de l'élément (ex: 'button1', 'axis2')
     */
    proposeDeviceConfiguration(instance, type, elementName) {
        // Vérifier si le système de détection automatique est disponible
        if (!window.deviceAutoDetection || !window.deviceSetupUI) {
            console.warn(`[DeviceConfig] Système de configuration automatique non disponible`);
            this.showDeviceConfigUnavailableOverlay(instance, type, elementName);
            return;
        }

        // Chercher le gamepad correspondant à cette instance
        const gamepads = navigator.getGamepads();
        let targetGamepad = null;
        
        // L'instance correspond généralement à l'index du gamepad + 1 (js1 = gamepad[0])
        const gamepadIndex = parseInt(instance) - 1;
        if (gamepadIndex >= 0 && gamepadIndex < gamepads.length && gamepads[gamepadIndex]) {
            targetGamepad = gamepads[gamepadIndex];
        }
        
        if (!targetGamepad) {
            console.warn(`[DeviceConfig] Gamepad pour instance ${instance} non trouvé`);
            this.showDeviceNotFoundOverlay(instance, type, elementName);
            return;
        }

        console.log(`[DeviceConfig] Proposition de configuration pour device: ${targetGamepad.id}`);
        
        // Vérifier si ce device est déjà dans les devices inconnus
        const deviceKey = window.deviceAutoDetection.getDeviceKey(targetGamepad);
        const unknownDevices = window.deviceAutoDetection.getUnknownDevices();
        const existingDevice = unknownDevices.find(dev => 
            window.deviceAutoDetection.getDeviceKey(dev.gamepad) === deviceKey
        );

        if (existingDevice) {
            // Device déjà détecté comme inconnu, afficher directement la notification
            window.deviceSetupUI.showNewDeviceNotification(existingDevice);
        } else {
            // Déclencher la détection pour ce device spécifique
            window.deviceAutoDetection.handleGamepadConnected(targetGamepad);
            
            // La notification sera automatiquement affichée via le callback du système
            console.log(`[DeviceConfig] Détection déclenchée pour: ${targetGamepad.id}`);
        }
        
        // Afficher un overlay informatif temporaire
        this.showDeviceConfigProposalOverlay(instance, type, elementName, targetGamepad.id);
    }

    /**
     * Affiche un overlay informatif quand la proposition de configuration est déclenchée
     */
    showDeviceConfigProposalOverlay(instance, type, elementName, deviceName) {
        // Créer l'overlay s'il n'existe pas
        let overlay = document.querySelector('.gamepad-overlay-config-proposal');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'gamepad-overlay-config-proposal';
            overlay.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                background: rgba(23, 162, 184, 0.95);
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
                border-left: 4px solid #17a2b8;
                max-width: 300px;
            `;
            document.body.appendChild(overlay);
        }

        // Formatage de l'input pour l'affichage
        const inputDisplay = `js${instance}_${elementName}`;
        
        // Mettre à jour le contenu
        overlay.innerHTML = `
            <div style="color: #E6F7FF; margin-bottom: 5px; font-size: 14px;">
                <strong>${inputDisplay}</strong>
            </div>
            <div style="color: #FFF; font-size: 12px;">
                💡 Device non configuré<br>
                📝 Notification de configuration envoyée
            </div>
        `;
        
        // Afficher avec animation
        overlay.style.opacity = '1';
        
        // Masquer après 4 secondes
        clearTimeout(this.overlayTimeout);
        this.overlayTimeout = setTimeout(() => {
            if (overlay) {
                overlay.style.opacity = '0';
            }
        }, 4000);
    }

    /**
     * Affiche un overlay d'erreur quand le système de configuration n'est pas disponible
     */
    showDeviceConfigUnavailableOverlay(instance, type, elementName) {
        let overlay = document.querySelector('.gamepad-overlay-config-unavailable');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'gamepad-overlay-config-unavailable';
            overlay.style.cssText = `
                position: fixed;
                top: 70px;
                right: 20px;
                background: rgba(255, 152, 0, 0.95);
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
                border-left: 4px solid #ff9800;
                max-width: 300px;
            `;
            document.body.appendChild(overlay);
        }

        const inputDisplay = `js${instance}_${elementName}`;
        
        overlay.innerHTML = `
            <div style="color: #FFF3E0; margin-bottom: 5px; font-size: 14px;">
                <strong>${inputDisplay}</strong>
            </div>
            <div style="color: #FFF; font-size: 12px;">
                ⚠️ Device non configuré<br>
                🔧 Système de configuration indisponible
            </div>
        `;
        
        overlay.style.opacity = '1';
        
        clearTimeout(this.overlayTimeout);
        this.overlayTimeout = setTimeout(() => {
            if (overlay) {
                overlay.style.opacity = '0';
            }
        }, 3000);
    }

    /**
     * Affiche un overlay d'erreur quand le device physique n'est pas trouvé
     */
    showDeviceNotFoundOverlay(instance, type, elementName) {
        let overlay = document.querySelector('.gamepad-overlay-device-not-found');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'gamepad-overlay-device-not-found';
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
                max-width: 300px;
            `;
            document.body.appendChild(overlay);
        }

        const inputDisplay = `js${instance}_${elementName}`;
        
        overlay.innerHTML = `
            <div style="color: #FFE6E6; margin-bottom: 5px; font-size: 14px;">
                <strong>${inputDisplay}</strong>
            </div>
            <div style="color: #FFF; font-size: 12px;">
                ❌ Device physique non trouvé<br>
                🔌 Vérifiez la connexion
            </div>
        `;
        
        overlay.style.opacity = '1';
        
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
