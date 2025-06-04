// Gestion des manettes de jeu et de leurs événements
export class GamepadHandler {
    constructor() {
        this.lastButtonStates = {};
        this.lastAxesStates = {};
        this.buttonNamesByInstance = {};
        this.currentButtonIndex = {};
        this.currentAxisIndex = {};
        this.currentHatIndex = {};
        
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
        if (!instance || !this.buttonNamesByInstance[instance]) return;

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
        if (!Array.isArray(this.lastButtonStates[instance])) this.lastButtonStates[instance] = [];
        
        for (let b = 0; b < gp.buttons.length; b++) {
            let pressed = gp.buttons[b].pressed;
            if (pressed && !this.lastButtonStates[instance][b]) {
                let btnName = `js${instance}_button${b+1}`;
                let mode = '';
                this.emit('buttonPressed', {
                    instance,
                    buttonName: btnName,
                    mode
                });
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

            // Gestion des hats
            if (deviceMap.hats && deviceMap.hats[a]) {
                hatDetected = this.processHat(deviceMap.hats[a], val, instance, a);
            }

            // Gestion des axes classiques
            if (!hatDetected && deviceMap.axes_map && deviceMap.axes_map.hasOwnProperty(a)) {
                this.processAxis(deviceMap, val, instance, a);
            }

            this.lastAxesStates[instance][a] = val;
        }
    }

    processHat(hat, val, instance, axisIndex) {
        for (const dir in hat.directions) {
            const d = hat.directions[dir];
            if (parseInt(d.axis) === axisIndex && val >= d.value_min && val <= d.value_max) {
                const xmlName = `js${instance}_hat1_${dir}`;
                this.emit('hatMoved', {
                    instance,
                    hatName: xmlName,
                    direction: dir
                });
                return true;
            }
        }
        return false;
    }

    processAxis(deviceMap, val, instance, axisIndex) {
        let axisName = `js${instance}_${deviceMap.axes_map[axisIndex]}`;
        let lastValue = this.lastAxesStates[instance] ? this.lastAxesStates[instance][axisIndex] : 0;

        if (Math.abs(val) > 0.5 && Math.abs(lastValue) < 0.5) {
            this.emit('axisMoved', {
                instance,
                axisName: axisName,
                value: val
            });
        }
    }

    emit(eventName, data) {
        const event = new CustomEvent(eventName, { detail: data });
        window.dispatchEvent(event);
    }
}
