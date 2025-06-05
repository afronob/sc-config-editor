// Gestion des manettes de jeu et de leurs événements
export class GamepadHandler {
    constructor() {
        // Constantes pour la détection des modes
        this.DOUBLE_TAP_DELAY = 300; // 300ms entre deux appuis pour un double tap
        this.HOLD_DELAY = 500;       // 500ms d'appui pour un hold

        // États des périphériques
        this.lastButtonStates = {};
        this.lastAxesStates = {};
        this.buttonNamesByInstance = {};
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
        this.lastPressTime = {};  // Pour le double tap
        this.pressStartTime = {}; // Pour le hold
        
        // États des hats pour la détection des modes
        this.lastHatStates = {};     // État des directions de hat (pressées/relâchées)
        this.hatPressTime = {};      // Temps de début d'appui pour chaque direction
        this.hatLastReleaseTime = {}; // Temps du dernier relâchement pour double tap
        
        // Binding des méthodes
        this.handleInput = this.handleInput.bind(this);
        this.init = this.init.bind(this);
    }

    init() {
        // Initialisation immédiate des états
        let gamepads = navigator.getGamepads();
        for (let i = 0; i < gamepads.length; i++) {
            let gp = gamepads[i];
            if (!gp) continue;
            let instance = this.getInstanceFromGamepad(gp);
            if (instance) {
                this.lastAxesStates[instance] = new Array(gp.axes.length).fill(0);
                this.lastButtonStates[instance] = new Array(gp.buttons.length).fill(false);
                this.lastHatStates[instance] = {};
                this.hatPressTime[instance] = {};
                this.hatLastReleaseTime[instance] = {};
            }
        }
        requestAnimationFrame(this.handleInput);
    }

    handleInput() {
        let gamepads = navigator.getGamepads ? navigator.getGamepads() : [];
        for (let gp of gamepads) {
            if (!gp) continue;
            this.processGamepad(gp);
        }
        requestAnimationFrame(this.handleInput);
    }

    processGamepad(gp) {
        let instance = this.getInstanceFromGamepad(gp);
        
        if (!instance) {
            return;
        }
        
        this.processButtons(gp, instance);
        this.processAxes(gp, instance);
    }

    // Méthodes existantes à copier depuis edit_form.php
    getInstanceFromGamepad(gamepad) {
        let found = null;
        if (window.devicesDataJs) {
            // 1. Matching par VendorID/ProductID
            let ids = this.extractVendorProductIdFromIdString(gamepad.id);
            
            window.devicesDataJs.forEach(function(dev) {
                if (
                    dev.vendor_id && dev.product_id &&
                    ids.vendor && ids.product &&
                    dev.vendor_id.replace(/^0x/, '').toLowerCase() === ids.vendor &&
                    dev.product_id.replace(/^0x/, '').toLowerCase() === ids.product &&
                    dev.xml_instance
                ) {
                    found = dev.xml_instance;
                }
            });
            
            // 2. Fallback sur le nom
            if (!found) {
                let gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim();
                
                window.devicesDataJs.forEach(function(dev) {
                    let devIdSimple = dev.id.replace(/\(Vendor:.*$/, '').trim();
                    if ((gamepadIdSimple && devIdSimple && 
                        (gamepadIdSimple.indexOf(devIdSimple) !== -1 || 
                         devIdSimple.indexOf(gamepadIdSimple) !== -1)) && 
                        dev.xml_instance) {
                        found = dev.xml_instance;
                    }
                });
            }
        }
        
        return found;
    }

    extractVendorProductIdFromIdString(idString) {
        let vendor = null, product = null;
        let m = idString.match(/Vendor:\s*([0-9a-fA-F]{4})/);
        if (m) vendor = m[1].toLowerCase();
        m = idString.match(/Product:\s*([0-9a-fA-F]{4})/);
        if (m) product = m[1].toLowerCase();
        return { vendor, product };
    }

    processButtons(gp, instance) {
        if (!Array.isArray(this.lastButtonStates[instance])) {
            this.lastButtonStates[instance] = [];
            this.lastPressTime[instance] = {};
            this.pressStartTime[instance] = {};
        }
        
        const now = Date.now();

        for (let b = 0; b < gp.buttons.length; b++) {
            let pressed = gp.buttons[b].pressed;
            let btnName = `js${instance}_button${b+1}`;
            
            // Début d'appui
            if (pressed && !this.lastButtonStates[instance][b]) {
                this.pressStartTime[instance][b] = now;
            }
            // Fin d'appui - Traitement de TOUS les modes ici
            else if (!pressed && this.lastButtonStates[instance][b]) {
                const pressDuration = now - (this.pressStartTime[instance][b] || 0);
                const lastReleaseTime = this.lastPressTime[instance][b] || 0;
                const timeSinceLastRelease = now - lastReleaseTime;
                
                // Détecter le type d'appui
                if (pressDuration >= this.HOLD_DELAY) {
                    // HOLD détecté
                    this.emit('buttonPressed', {
                        instance,
                        buttonName: btnName,
                        mode: 'hold'
                    });
                    this.lastPressTime[instance][b] = 0; // Reset pour éviter confusion
                } 
                else if (lastReleaseTime > 0 && timeSinceLastRelease <= this.DOUBLE_TAP_DELAY) {
                    // DOUBLE TAP détecté (deuxième relâchement dans le délai)
                    this.emit('buttonPressed', {
                        instance,
                        buttonName: btnName,
                        mode: 'double_tap'
                    });
                    this.lastPressTime[instance][b] = 0; // Reset
                } 
                else {
                    // Premier relâchement - attendre pour voir si double tap
                    this.lastPressTime[instance][b] = now;
                    
                    const checkForDoubleTap = () => {
                        // Si aucun second appui n'a été détecté, émettre simple press
                        if (this.lastPressTime[instance][b] === now) {
                            this.emit('buttonPressed', {
                                instance,
                                buttonName: btnName,
                                mode: ''
                            });
                            this.lastPressTime[instance][b] = 0;
                        }
                    };
                    setTimeout(checkForDoubleTap, this.DOUBLE_TAP_DELAY + 50);
                }
                
                this.pressStartTime[instance][b] = 0;
            }

            this.lastButtonStates[instance][b] = pressed;
        }
    }

    processAxes(gp, instance) {
        if (!Array.isArray(this.lastAxesStates[instance])) this.lastAxesStates[instance] = [];
        
        let deviceMap = window.devicesDataJs.find(dev => dev.xml_instance == instance);
        if (!deviceMap) return;

        for (let a = 0; a < gp.axes.length; a++) {
            let val = gp.axes[a];
            let hatDetected = false;

            // Gestion des hats - vérifier avec l'index comme chaîne ET comme nombre
            let hatConfig = null;
            if (deviceMap.hats) {
                // Essayer d'abord avec l'index comme chaîne
                hatConfig = deviceMap.hats[a.toString()] || deviceMap.hats[a];
            }
            
            if (hatConfig) {
                hatDetected = this.processHat(hatConfig, val, instance, a);
            }

            // Gestion des axes classiques
            if (!hatDetected && deviceMap.axes_map && deviceMap.axes_map.hasOwnProperty(a)) {
                this.processAxis(deviceMap, val, instance, a);
            }

            this.lastAxesStates[instance][a] = val;
        }
    }

    processHat(hat, val, instance, axisIndex) {
        if (!this.lastHatStates[instance]) {
            this.lastHatStates[instance] = {};
            this.hatPressTime[instance] = {};
            this.hatLastReleaseTime[instance] = {};
        }

        const now = Date.now();
        let hatDetected = false;

        // Vérifier chaque direction du hat
        for (const dir in hat.directions) {
            const d = hat.directions[dir];
            const hatName = `js${instance}_hat1_${dir}`;
            
            // Vérifier si cette direction est actuellement active
            let isActive = (parseInt(d.axis) === axisIndex && val >= d.value_min && val <= d.value_max);
            let wasActive = this.lastHatStates[instance][hatName] || false;
            
            // Début d'activation de direction
            if (isActive && !wasActive) {
                this.hatPressTime[instance][hatName] = now;
                hatDetected = true;
            }
            // Fin d'activation de direction - Traitement de TOUS les modes ici
            else if (!isActive && wasActive) {
                const pressDuration = now - (this.hatPressTime[instance][hatName] || 0);
                const lastReleaseTime = this.hatLastReleaseTime[instance][hatName] || 0;
                const timeSinceLastRelease = now - lastReleaseTime;
                
                // Détecter le type d'activation
                if (pressDuration >= this.HOLD_DELAY) {
                    // HOLD détecté
                    this.emit('hatMoved', {
                        instance,
                        hatName,
                        direction: dir,
                        mode: 'hold'
                    });
                    this.hatLastReleaseTime[instance][hatName] = 0; // Reset
                } 
                else if (lastReleaseTime > 0 && timeSinceLastRelease <= this.DOUBLE_TAP_DELAY) {
                    // DOUBLE TAP détecté (deuxième relâchement dans le délai)
                    this.emit('hatMoved', {
                        instance,
                        hatName,
                        direction: dir,
                        mode: 'double_tap'
                    });
                    this.hatLastReleaseTime[instance][hatName] = 0; // Reset
                } 
                else {
                    // Premier relâchement - attendre pour voir si double tap
                    this.hatLastReleaseTime[instance][hatName] = now;
                    
                    const checkForDoubleTap = () => {
                        // Si aucun second appui n'a été détecté, émettre activation simple
                        if (this.hatLastReleaseTime[instance][hatName] === now) {
                            this.emit('hatMoved', {
                                instance,
                                hatName,
                                direction: dir,
                                mode: ''
                            });
                            this.hatLastReleaseTime[instance][hatName] = 0;
                        }
                    };
                    setTimeout(checkForDoubleTap, this.DOUBLE_TAP_DELAY + 50);
                }
                
                this.hatPressTime[instance][hatName] = 0;
                hatDetected = true;
            }
            
            // Mettre à jour l'état
            this.lastHatStates[instance][hatName] = isActive;
        }

        return hatDetected;
    }

    processAxis(deviceMap, val, instance, axisIndex) {
        let axisName = `js${instance}_${deviceMap.axes_map[axisIndex]}`;
        let lastValue = this.lastAxesStates[instance][axisIndex];
        
        // Si c'est la première lecture, initialiser sans émettre d'événement
        if (lastValue === undefined) {
            this.lastAxesStates[instance][axisIndex] = val;
            return;
        }
        
        // Définir des seuils pour éviter les oscillations
        const THRESHOLD = 0.5;          // Seuil de détection d'activation
        const DEADZONE = 0.15;          // Zone morte pour éviter les petites variations
        const CHANGE_THRESHOLD = 0.25;  // Seuil de changement significatif

        // Détecter les changements significatifs de l'axe
        let wasActive = Math.abs(lastValue) > THRESHOLD;
        let isActive = Math.abs(val) > THRESHOLD;
        let hasChangedSignificantly = Math.abs(val - lastValue) > CHANGE_THRESHOLD;

        // Changement d'état : inactif -> actif ou changement significatif quand actif
        if ((!wasActive && isActive) || (isActive && hasChangedSignificantly)) {
            // Ignorer les petites variations autour de zéro
            if (Math.abs(val) > DEADZONE) {
                this.emit('axisMoved', {
                    instance,
                    axisName,
                    value: val
                });
            }
        }
    }

    emit(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        window.dispatchEvent(event);
    }
}
