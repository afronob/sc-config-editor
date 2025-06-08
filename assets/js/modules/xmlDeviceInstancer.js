/**
 * 🎯 XML Device Instancer - Module d'instanciation automatique de devices dans XML
 * 
 * Ce module analyse un XML Star Citizen chargé et propose l'ajout automatique
 * d'un nouveau device avec une instance incrémentée.
 */

export class XMLDeviceInstancer {
    constructor() {
        this.currentXMLContent = null;
        this.parsedXML = null;
    }

    /**
     * Initialise l'instancer avec le contenu XML actuel
     * @param {string} xmlContent - Contenu XML sous forme de string
     */
    initialize(xmlContent) {
        this.currentXMLContent = xmlContent;
        try {
            // Parse le XML pour analyse
            const parser = new DOMParser();
            this.parsedXML = parser.parseFromString(xmlContent, 'text/xml');
            
            console.log('[XMLInstancer] XML initialisé et parsé avec succès');
            return true;
        } catch (error) {
            console.error('[XMLInstancer] Erreur lors du parsing XML:', error);
            return false;
        }
    }

    /**
     * Analyse les instances joystick existantes et détermine la prochaine instance
     * @returns {number} - Prochaine instance disponible
     */
    getNextAvailableInstance() {
        if (!this.parsedXML) {
            console.warn('[XMLInstancer] XML non initialisé');
            return 1;
        }

        const usedInstances = new Set();

        // 1. Analyser les déclarations dans <devices>
        const deviceJoysticks = this.parsedXML.querySelectorAll('devices joystick');
        deviceJoysticks.forEach(joystick => {
            const instance = parseInt(joystick.getAttribute('instance'));
            if (!isNaN(instance)) {
                usedInstances.add(instance);
            }
        });

        // 2. Analyser les <options> joystick
        const optionJoysticks = this.parsedXML.querySelectorAll('options[type="joystick"]');
        optionJoysticks.forEach(option => {
            const instance = parseInt(option.getAttribute('instance'));
            if (!isNaN(instance)) {
                usedInstances.add(instance);
            }
        });

        // 3. Trouver la prochaine instance disponible
        let nextInstance = 1;
        while (usedInstances.has(nextInstance)) {
            nextInstance++;
        }

        console.log(`[XMLInstancer] Instances existantes: [${Array.from(usedInstances).sort().join(', ')}]`);
        console.log(`[XMLInstancer] Prochaine instance disponible: ${nextInstance}`);

        return nextInstance;
    }

    /**
     * Génère les informations du device pour l'instanciation
     * @param {Object} deviceInfo - Informations du device détecté
     * @param {number} instanceNumber - Numéro d'instance à assigner
     * @returns {Object} - Informations formatées pour XML
     */
    generateDeviceXMLInfo(deviceInfo, instanceNumber) {
        // Extraire vendor/product ID depuis le gamepad ID
        const vendorProductMatch = deviceInfo.id.match(/Vendor:\s*([0-9a-fA-F]{4}).*Product:\s*([0-9a-fA-F]{4})/i);
        
        let productString;
        if (vendorProductMatch) {
            const vendorId = vendorProductMatch[1].toUpperCase();
            const productId = vendorProductMatch[2].toUpperCase();
            // Format Star Citizen: " DeviceName    {PPPPVVVV-0000-0000-0000-504944564944}"
            productString = ` ${deviceInfo.id.split('(')[0].trim()}    {${productId}${vendorId}-0000-0000-0000-504944564944}`;
        } else {
            // Fallback si pas de vendor/product dans l'ID
            productString = ` ${deviceInfo.id}    {UNKNOWN-0000-0000-0000-504944564944}`;
        }

        return {
            instance: instanceNumber,
            productString: productString,
            deviceName: deviceInfo.id.split('(')[0].trim(),
            vendorId: vendorProductMatch ? vendorProductMatch[1] : 'UNKN',
            productId: vendorProductMatch ? vendorProductMatch[2] : 'UNKN'
        };
    }

    /**
     * Génère le XML modifié avec le nouveau device instancié
     * @param {Object} deviceInfo - Informations du device détecté
     * @param {Object} userConfig - Configuration utilisateur du device
     * @returns {string} - XML modifié
     */
    generateModifiedXML(deviceInfo, userConfig) {
        if (!this.currentXMLContent) {
            throw new Error('XML non initialisé');
        }

        const nextInstance = this.getNextAvailableInstance();
        const xmlDeviceInfo = this.generateDeviceXMLInfo(deviceInfo, nextInstance);

        let modifiedXML = this.currentXMLContent;

        // 1. Ajouter la déclaration dans <devices>
        const devicesEndTag = '</devices>';
        const deviceDeclaration = `   <joystick instance="${xmlDeviceInfo.instance}"/>`;
        
        if (modifiedXML.includes(devicesEndTag)) {
            modifiedXML = modifiedXML.replace(
                devicesEndTag,
                `${deviceDeclaration}\n  ${devicesEndTag}`
            );
        }

        // 2. Ajouter les options après les autres options joystick
        const lastJoystickOptionsRegex = /(<options type="joystick"[^>]*\/?>(?:[^<]|<(?!\/options>))*<\/options>)/g;
        const matches = Array.from(modifiedXML.matchAll(lastJoystickOptionsRegex));
        
        if (matches.length > 0) {
            const lastMatch = matches[matches.length - 1];
            const insertIndex = lastMatch.index + lastMatch[0].length;
            
            const optionsDeclaration = `\n <options type="joystick" instance="${xmlDeviceInfo.instance}" Product="${xmlDeviceInfo.productString}"/>`;
            
            modifiedXML = modifiedXML.slice(0, insertIndex) + 
                         optionsDeclaration + 
                         modifiedXML.slice(insertIndex);
        }

        console.log(`[XMLInstancer] XML modifié avec device instance ${xmlDeviceInfo.instance}`);
        return {
            xmlContent: modifiedXML,
            deviceInfo: xmlDeviceInfo,
            userConfig: userConfig
        };
    }

    /**
     * Valide que le XML modifié est bien formé
     * @param {string} xmlContent - Contenu XML à valider
     * @returns {boolean} - true si valide
     */
    validateModifiedXML(xmlContent) {
        try {
            const parser = new DOMParser();
            const testXML = parser.parseFromString(xmlContent, 'text/xml');
            
            // Vérifier qu'il n'y a pas d'erreur de parsing
            const parseError = testXML.querySelector('parsererror');
            if (parseError) {
                console.error('[XMLInstancer] Erreur de validation XML:', parseError.textContent);
                return false;
            }

            console.log('[XMLInstancer] XML modifié validé avec succès');
            return true;
        } catch (error) {
            console.error('[XMLInstancer] Erreur lors de la validation:', error);
            return false;
        }
    }

    /**
     * Génère un nom de fichier pour le XML modifié
     * @param {string} originalFilename - Nom de fichier original
     * @param {Object} deviceInfo - Informations du device ajouté
     * @returns {string} - Nouveau nom de fichier
     */
    generateModifiedFilename(originalFilename, deviceInfo) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        const deviceName = deviceInfo.deviceName.replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
        
        // Enlever l'extension .xml
        const baseName = originalFilename.replace(/\.xml$/i, '');
        
        return `${baseName}_with_${deviceName}_${timestamp}.xml`;
    }

    /**
     * Crée les métadonnées de l'instanciation pour documentation
     * @param {Object} deviceInfo - Informations du device
     * @param {Object} xmlDeviceInfo - Informations XML générées
     * @param {Object} userConfig - Configuration utilisateur
     * @returns {Object} - Métadonnées complètes
     */
    generateInstanciationMetadata(deviceInfo, xmlDeviceInfo, userConfig) {
        return {
            timestamp: new Date().toISOString(),
            device: {
                originalName: deviceInfo.id,
                cleanName: xmlDeviceInfo.deviceName,
                vendorId: xmlDeviceInfo.vendorId,
                productId: xmlDeviceInfo.productId,
                buttons: deviceInfo.buttons,
                axes: deviceInfo.axes
            },
            xmlInstance: {
                instance: xmlDeviceInfo.instance,
                productString: xmlDeviceInfo.productString
            },
            userConfiguration: {
                axesMapping: userConfig.axesMapping || {},
                hatsConfig: userConfig.hatsConfig || {}
            },
            mappingFile: `${xmlDeviceInfo.vendorId.toLowerCase()}_${xmlDeviceInfo.productId.toLowerCase()}_map.json`
        };
    }
}

// Rendre disponible globalement
window.XMLDeviceInstancer = XMLDeviceInstancer;
