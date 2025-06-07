// Module de détection automatique des nouveaux devices
export class DeviceAutoDetection {
    constructor() {
        this.unknownDevices = new Map();
        this.detectionCallbacks = [];
        this.isSetupMode = false;
        this.currentSetupDevice = null;
        
        // Configuration par défaut pour les nouveaux devices
        this.defaultAxesMap = {
            0: 'x',
            1: 'y',
            2: 'z',
            3: 'rotx',
            4: 'roty',
            5: 'rotz'
        };
        
        this.init();
    }

    init() {
        // Écouter les connexions/déconnexions de gamepad
        window.addEventListener('gamepadconnected', (e) => {
            this.handleGamepadConnected(e.gamepad);
        });
        
        window.addEventListener('gamepaddisconnected', (e) => {
            this.handleGamepadDisconnected(e.gamepad);
        });
        
        // Vérifier les gamepads déjà connectés au démarrage
        this.checkExistingGamepads();
    }

    checkExistingGamepads() {
        const gamepads = navigator.getGamepads();
        for (let i = 0; i < gamepads.length; i++) {
            if (gamepads[i]) {
                this.handleGamepadConnected(gamepads[i]);
            }
        }
    }

    handleGamepadConnected(gamepad) {
        console.log('Gamepad connecté:', gamepad.id);
        
        // Vérifier si ce device est déjà connu
        if (this.isDeviceKnown(gamepad)) {
            console.log('Device connu détecté:', gamepad.id);
            return;
        }
        
        // Nouveau device détecté
        console.log('Nouveau device détecté:', gamepad.id);
        this.registerUnknownDevice(gamepad);
        this.notifyNewDeviceDetected(gamepad);
    }

    handleGamepadDisconnected(gamepad) {
        console.log('Gamepad déconnecté:', gamepad.id);
        
        // Retirer de la liste des devices inconnus si présent
        const deviceKey = this.getDeviceKey(gamepad);
        if (this.unknownDevices.has(deviceKey)) {
            this.unknownDevices.delete(deviceKey);
        }
    }

    isDeviceKnown(gamepad) {
        if (!window.devicesDataJs || !Array.isArray(window.devicesDataJs)) {
            return false;
        }

        const ids = this.extractVendorProductIdFromGamepad(gamepad);
        
        // Vérifier par vendor/product ID
        if (ids.vendor && ids.product) {
            const found = window.devicesDataJs.find(dev => {
                return dev.vendor_id && dev.product_id &&
                       dev.vendor_id.replace(/^0x/, '').toLowerCase() === ids.vendor &&
                       dev.product_id.replace(/^0x/, '').toLowerCase() === ids.product;
            });
            
            if (found) return true;
        }
        
        // Vérifier par nom (fallback)
        const gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim();
        const foundByName = window.devicesDataJs.find(dev => {
            const devIdSimple = dev.id.replace(/\(Vendor:.*$/, '').trim();
            return gamepadIdSimple && devIdSimple && 
                   (gamepadIdSimple.indexOf(devIdSimple) !== -1 || 
                    devIdSimple.indexOf(gamepadIdSimple) !== -1);
        });
        
        return !!foundByName;
    }

    extractVendorProductIdFromGamepad(gamepad) {
        let vendor = null, product = null;
        let m = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/);
        if (m) vendor = m[1].toLowerCase();
        m = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/);
        if (m) product = m[1].toLowerCase();
        return { vendor, product };
    }

    getDeviceKey(gamepad) {
        const ids = this.extractVendorProductIdFromGamepad(gamepad);
        if (ids.vendor && ids.product) {
            return `${ids.vendor}_${ids.product}`;
        }
        // Fallback sur l'ID complet si pas de vendor/product
        return gamepad.id;
    }

    registerUnknownDevice(gamepad) {
        const deviceKey = this.getDeviceKey(gamepad);
        const ids = this.extractVendorProductIdFromGamepad(gamepad);
        
        const deviceInfo = {
            gamepad: gamepad,
            id: gamepad.id,
            vendor_id: ids.vendor ? `0x${ids.vendor}` : null,
            product_id: ids.product ? `0x${ids.product}` : null,
            buttons: gamepad.buttons.length,
            axes: gamepad.axes.length,
            timestamp: Date.now(),
            setupComplete: false
        };
        
        this.unknownDevices.set(deviceKey, deviceInfo);
        console.log('Device inconnu enregistré:', deviceInfo);
    }

    notifyNewDeviceDetected(gamepad) {
        const deviceKey = this.getDeviceKey(gamepad);
        const deviceInfo = this.unknownDevices.get(deviceKey);
        
        // Notifier tous les callbacks enregistrés
        this.detectionCallbacks.forEach(callback => {
            try {
                callback(deviceInfo);
            } catch (error) {
                console.error('Erreur dans callback de détection:', error);
            }
        });
        
        // Émettre un événement personnalisé
        const event = new CustomEvent('unknownDeviceDetected', {
            detail: deviceInfo
        });
        window.dispatchEvent(event);
    }

    onNewDeviceDetected(callback) {
        if (typeof callback === 'function') {
            this.detectionCallbacks.push(callback);
        }
    }

    getUnknownDevices() {
        return Array.from(this.unknownDevices.values());
    }

    startDeviceSetup(deviceKey) {
        const deviceInfo = this.unknownDevices.get(deviceKey);
        if (!deviceInfo) {
            throw new Error(`Device inconnu non trouvé: ${deviceKey}`);
        }
        
        this.isSetupMode = true;
        this.currentSetupDevice = deviceInfo;
        
        console.log('Démarrage de la configuration pour:', deviceInfo);
        return deviceInfo;
    }

    generateDeviceMapping(deviceKey, userConfig = {}) {
        const deviceInfo = this.unknownDevices.get(deviceKey);
        if (!deviceInfo) {
            throw new Error(`Device inconnu non trouvé: ${deviceKey}`);
        }
        
        // Générer un nom de fichier unique
        const fileName = this.generateFileName(deviceInfo);
        
        // Générer une instance XML unique
        const xmlInstance = this.generateXmlInstance(deviceInfo);
        
        // Créer le mapping de base
        const mapping = {
            id: deviceInfo.id,
            vendor_id: deviceInfo.vendor_id,
            product_id: deviceInfo.product_id,
            xml_instance: xmlInstance,
            axes_map: this.generateAxesMap(deviceInfo, userConfig.axesMapping),
            hats: this.generateHatsConfig(deviceInfo, userConfig.hatsConfig)
        };
        
        // Nettoyer les valeurs null/undefined
        Object.keys(mapping).forEach(key => {
            if (mapping[key] === null || mapping[key] === undefined) {
                delete mapping[key];
            }
        });
        
        return {
            fileName: fileName,
            mapping: mapping
        };
    }

    generateFileName(deviceInfo) {
        let baseName = 'unknown_device';
        
        if (deviceInfo.vendor_id && deviceInfo.product_id) {
            const vendor = deviceInfo.vendor_id.replace(/^0x/, '');
            const product = deviceInfo.product_id.replace(/^0x/, '');
            baseName = `device_${vendor}_${product}`;
        } else {
            // Utiliser le nom simplifié du device
            const simpleName = deviceInfo.id
                .replace(/\(Vendor:.*$/, '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
            
            if (simpleName) {
                baseName = simpleName;
            }
        }
        
        return `${baseName}_map.json`;
    }

    generateXmlInstance(deviceInfo) {
        if (deviceInfo.vendor_id && deviceInfo.product_id) {
            const vendor = deviceInfo.vendor_id.replace(/^0x/, '');
            const product = deviceInfo.product_id.replace(/^0x/, '');
            return `${vendor}_${product}`;
        }
        
        // Fallback: utiliser un hash du nom
        let hash = 0;
        for (let i = 0; i < deviceInfo.id.length; i++) {
            const char = deviceInfo.id.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        
        return `device_${Math.abs(hash).toString(16)}`;
    }

    generateAxesMap(deviceInfo, userAxesMapping = {}) {
        const axesMap = {};
        
        for (let i = 0; i < deviceInfo.axes; i++) {
            if (userAxesMapping[i]) {
                axesMap[i] = userAxesMapping[i];
            } else if (this.defaultAxesMap[i]) {
                axesMap[i] = this.defaultAxesMap[i];
            } else {
                axesMap[i] = `axis${i}`;
            }
        }
        
        return axesMap;
    }

    generateHatsConfig(deviceInfo, userHatsConfig = {}) {
        // Si l'utilisateur n'a pas configuré de hats, retourner null
        if (!userHatsConfig || Object.keys(userHatsConfig).length === 0) {
            return null;
        }
        
        return userHatsConfig;
    }

    async saveDeviceMapping(deviceKey, userConfig = {}) {
        try {
            const mappingData = this.generateDeviceMapping(deviceKey, userConfig);
            
            // Préparer les données pour l'envoi
            const formData = new FormData();
            formData.append('action', 'save_device_mapping');
            formData.append('fileName', mappingData.fileName);
            formData.append('mappingData', JSON.stringify(mappingData.mapping, null, 2));
            
            // Envoyer au serveur
            const response = await fetch('save_device_mapping.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                console.log('Mapping sauvegardé avec succès:', mappingData.fileName);
                
                // Marquer le device comme configuré
                const deviceInfo = this.unknownDevices.get(deviceKey);
                if (deviceInfo) {
                    deviceInfo.setupComplete = true;
                }
                
                // Ajouter le nouveau mapping aux données globales
                this.addToGlobalDevicesData(mappingData.mapping);
                
                return {
                    success: true,
                    fileName: mappingData.fileName,
                    mapping: mappingData.mapping
                };
            } else {
                throw new Error(result.error || 'Erreur inconnue lors de la sauvegarde');
            }
            
        } catch (error) {
            console.error('Erreur lors de la sauvegarde du mapping:', error);
            throw error;
        }
    }

    addToGlobalDevicesData(mapping) {
        if (!window.devicesDataJs) {
            window.devicesDataJs = [];
        }
        
        // Vérifier si le mapping n'existe pas déjà
        const exists = window.devicesDataJs.find(dev => 
            dev.xml_instance === mapping.xml_instance ||
            (dev.vendor_id === mapping.vendor_id && dev.product_id === mapping.product_id)
        );
        
        if (!exists) {
            window.devicesDataJs.push(mapping);
            console.log('Nouveau mapping ajouté aux données globales:', mapping);
        }
    }

    finishDeviceSetup(deviceKey) {
        const deviceInfo = this.unknownDevices.get(deviceKey);
        if (deviceInfo) {
            deviceInfo.setupComplete = true;
        }
        
        this.isSetupMode = false;
        this.currentSetupDevice = null;
    }

    cancelDeviceSetup() {
        this.isSetupMode = false;
        this.currentSetupDevice = null;
    }
}