/**
 * Module pour la gestion des dispositifs et création de mappings JSON
 * Étape 1 : Création et gestion des configurations de dispositifs
 * Version 2.0 avec support Redis
 */

// Import du gestionnaire Redis
import { createRedisStorage } from './redisClientManager.js';

export class DeviceManager {
    constructor(config = {}) {
        this.devices = new Map();
        this.config = {
            useRedis: true,
            fallbackToLocalStorage: true,
            ...config
        };
        
        // Initialiser le stockage (Redis ou localStorage)
        this.initializeStorage();
        this.loadSavedDevices();
    }
    
    /**
     * Initialise le système de stockage
     */
    async initializeStorage() {
        if (this.config.useRedis) {
            try {
                this.storage = await createRedisStorage({
                    apiBase: '/api/redis',
                    fallbackToLocalStorage: this.config.fallbackToLocalStorage
                });
                
                console.log('✅ DeviceManager: Redis storage initialized');
            } catch (error) {
                console.warn('⚠️ DeviceManager: Redis unavailable, using localStorage:', error.message);
                this.storage = localStorage;
            }
        } else {
            this.storage = localStorage;
        }
    }

    /**
     * Initialise l'interface de gestion des dispositifs
     */
    initializeDeviceManagement(containerId = 'device-management-container') {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Container ${containerId} not found`);
            return;
        }

        container.innerHTML = this.generateDeviceManagementHTML();
        this.bindDeviceManagementEvents(container);
    }

    /**
     * Génère le HTML pour l'interface de gestion des dispositifs
     */
    generateDeviceManagementHTML() {
        return `
            <div class="device-manager">
                <div class="device-manager-header">
                    <h3>Gestion des Dispositifs</h3>
                    <button type="button" class="btn btn-primary" id="add-new-device">
                        <i class="fas fa-plus"></i> Ajouter un nouveau dispositif
                    </button>
                </div>
                
                <div class="devices-list">
                    <h4>Dispositifs configurés</h4>
                    <div id="saved-devices-list">
                        ${this.generateSavedDevicesList()}
                    </div>
                </div>

                <!-- Modal pour ajout/édition de dispositif -->
                <div id="device-config-modal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="device-modal-title">Configuration de dispositif</h5>
                            <button type="button" class="close" id="close-device-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="device-setup-wizard">
                                <!-- Le contenu du wizard sera injecté ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Génère la liste des dispositifs sauvegardés
     */
    generateSavedDevicesList() {
        if (this.devices.size === 0) {
            return '<p class="no-devices">Aucun dispositif configuré</p>';
        }

        let html = '<div class="devices-grid">';
        for (const [deviceId, device] of this.devices) {
            html += `
                <div class="device-card" data-device-id="${deviceId}">
                    <div class="device-info">
                        <h5>${this.escapeHtml(device.name)}</h5>
                        <p class="device-type">${this.escapeHtml(device.deviceType || 'Non spécifié')}</p>
                        <p class="device-stats">
                            <span class="stat">Axes: ${Object.keys(device.axes || {}).length}</span>
                            <span class="stat">Boutons: ${Object.keys(device.buttons || {}).length}</span>
                            <span class="stat">Chapeaux: ${Object.keys(device.hats || {}).length}</span>
                        </p>
                    </div>
                    <div class="device-actions">
                        <button type="button" class="btn btn-sm btn-secondary edit-device" 
                                data-device-id="${deviceId}">Éditer</button>
                        <button type="button" class="btn btn-sm btn-danger delete-device" 
                                data-device-id="${deviceId}">Supprimer</button>
                        <button type="button" class="btn btn-sm btn-info export-device" 
                                data-device-id="${deviceId}">Exporter JSON</button>
                    </div>
                </div>
            `;
        }
        html += '</div>';
        return html;
    }

    /**
     * Bind les événements pour la gestion des dispositifs
     */
    bindDeviceManagementEvents(container) {
        // Bouton d'ajout de nouveau dispositif
        const addButton = container.querySelector('#add-new-device');
        if (addButton) {
            addButton.addEventListener('click', () => this.openDeviceConfigModal());
        }

        // Boutons d'action sur les dispositifs
        container.addEventListener('click', (e) => {
            const deviceId = e.target.getAttribute('data-device-id');
            
            if (e.target.classList.contains('edit-device')) {
                this.editDevice(deviceId);
            } else if (e.target.classList.contains('delete-device')) {
                this.deleteDevice(deviceId);
            } else if (e.target.classList.contains('export-device')) {
                this.exportDeviceJSON(deviceId);
            }
        });

        // Fermeture du modal
        const closeModal = container.querySelector('#close-device-modal');
        if (closeModal) {
            closeModal.addEventListener('click', () => this.closeDeviceConfigModal());
        }

        // Fermeture au clic sur l'overlay
        const modal = container.querySelector('#device-config-modal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeDeviceConfigModal();
                }
            });
        }
    }

    /**
     * Ouvre le modal de configuration de dispositif
     */
    async openDeviceConfigModal(deviceId = null) {
        const modal = document.getElementById('device-config-modal');
        const title = document.getElementById('device-modal-title');
        const wizard = document.getElementById('device-setup-wizard');

        if (!modal || !wizard) return;

        // Importer dynamiquement le DeviceSetupUI si nécessaire
        const { DeviceSetupUI } = await import('./deviceSetupUI.js');
        
        // Créer une instance du wizard pour la gestion JSON uniquement
        this.deviceSetupUI = new DeviceSetupUI(wizard.id);
        
        // Configurer le wizard pour le mode JSON uniquement (sans étape XML)
        this.deviceSetupUI.setMode('json-only');
        
        if (deviceId) {
            title.textContent = 'Éditer le dispositif';
            const device = this.devices.get(deviceId);
            if (device) {
                this.deviceSetupUI.loadDeviceData(device);
            }
        } else {
            title.textContent = 'Nouveau dispositif';
        }

        // Configurer les callbacks
        this.deviceSetupUI.onComplete = (deviceData) => {
            this.saveDevice(deviceData, deviceId);
            this.closeDeviceConfigModal();
        };

        this.deviceSetupUI.onCancel = () => {
            this.closeDeviceConfigModal();
        };

        modal.style.display = 'block';
    }

    /**
     * Ferme le modal de configuration
     */
    closeDeviceConfigModal() {
        const modal = document.getElementById('device-config-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    /**
     * Édite un dispositif existant
     */
    editDevice(deviceId) {
        this.openDeviceConfigModal(deviceId);
    }

    /**
     * Supprime un dispositif
     */
    deleteDevice(deviceId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce dispositif ?')) {
            this.devices.delete(deviceId);
            this.saveDevicesToStorage();
            this.refreshDevicesList();
        }
    }

    /**
     * Exporte la configuration JSON d'un dispositif
     */
    exportDeviceJSON(deviceId) {
        const device = this.devices.get(deviceId);
        if (!device) return;

        const dataStr = JSON.stringify(device, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `device_${device.name.replace(/[^a-zA-Z0-9]/g, '_')}.json`;
        link.click();
    }

    /**
     * Sauvegarde un dispositif
     */
    async saveDevice(deviceData, deviceId = null) {
        const id = deviceId || this.generateDeviceId();
        deviceData.id = id;
        deviceData.lastModified = new Date().toISOString();
        
        this.devices.set(id, deviceData);
        await this.saveDevicesToStorage();
        this.refreshDevicesList();
    }

    /**
     * Rafraîchit la liste des dispositifs affichés
     */
    refreshDevicesList() {
        const container = document.getElementById('saved-devices-list');
        if (container) {
            container.innerHTML = this.generateSavedDevicesList();
        }
    }

    /**
     * Génère un ID unique pour un dispositif
     */
    generateDeviceId() {
        return 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Charge les dispositifs sauvegardés depuis le stockage (Redis/localStorage)
     */
    async loadSavedDevices() {
        try {
            // Attendre que le stockage soit initialisé
            if (!this.storage) {
                await this.initializeStorage();
            }
            
            const stored = await this.storage.getItem('sc_devices');
            if (stored) {
                const devices = JSON.parse(stored);
                this.devices = new Map(Object.entries(devices));
                console.log(`✅ Chargé ${this.devices.size} dispositifs depuis le stockage`);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des dispositifs:', error);
            
            // Fallback sur localStorage si Redis échoue
            if (this.config.fallbackToLocalStorage && this.storage !== localStorage) {
                try {
                    const fallbackStored = localStorage.getItem('sc_devices');
                    if (fallbackStored) {
                        const devices = JSON.parse(fallbackStored);
                        this.devices = new Map(Object.entries(devices));
                        console.log(`⚠️ Fallback: Chargé ${this.devices.size} dispositifs depuis localStorage`);
                    }
                } catch (fallbackError) {
                    console.error('Erreur fallback localStorage:', fallbackError);
                }
            }
        }
    }
    }

    /**
     * Sauvegarde les dispositifs dans le stockage (Redis/localStorage)
     */
    async saveDevicesToStorage() {
        try {
            const devicesObj = Object.fromEntries(this.devices);
            await this.storage.setItem('sc_devices', JSON.stringify(devicesObj));
            console.log(`✅ Sauvegardé ${this.devices.size} dispositifs dans le stockage`);
        } catch (error) {
            console.error('Erreur lors de la sauvegarde des dispositifs:', error);
            
            // Fallback sur localStorage si Redis échoue
            if (this.config.fallbackToLocalStorage && this.storage !== localStorage) {
                try {
                    const devicesObj = Object.fromEntries(this.devices);
                    localStorage.setItem('sc_devices', JSON.stringify(devicesObj));
                    console.log('⚠️ Fallback: Sauvegardé dans localStorage');
                } catch (fallbackError) {
                    console.error('Erreur fallback localStorage:', fallbackError);
                }
            }
        }
    }

    /**
     * Obtient tous les dispositifs configurés
     */
    getAllDevices() {
        return Array.from(this.devices.values());
    }

    /**
     * Obtient un dispositif par son ID
     */
    getDevice(deviceId) {
        return this.devices.get(deviceId);
    }

    /**
     * Importe des dispositifs depuis un fichier JSON
     */
    importDevicesFromJSON(jsonData) {
        try {
            const devices = JSON.parse(jsonData);
            if (Array.isArray(devices)) {
                devices.forEach(device => {
                    const id = this.generateDeviceId();
                    this.saveDevice(device, id);
                });
            } else if (typeof devices === 'object') {
                const id = this.generateDeviceId();
                this.saveDevice(devices, id);
            }
            this.refreshDevicesList();
        } catch (error) {
            console.error('Erreur lors de l\'importation:', error);
            alert('Erreur lors de l\'importation du fichier JSON');
        }
    }

    /**
     * Échappe le HTML pour éviter les injections
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// CSS pour le module
export const deviceManagerCSS = `
.device-manager {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.device-manager-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.devices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.device-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
    transition: box-shadow 0.2s;
}

.device-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.device-info h5 {
    margin: 0 0 10px 0;
    color: #495057;
}

.device-type {
    color: #6c757d;
    font-style: italic;
    margin: 5px 0;
}

.device-stats {
    margin: 10px 0;
}

.device-stats .stat {
    display: inline-block;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.85em;
    margin-right: 5px;
}

.device-actions {
    margin-top: 15px;
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875em;
    text-decoration: none;
    display: inline-block;
}

.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-danger { background: #dc3545; color: white; }
.btn-info { background: #17a2b8; color: white; }
.btn-sm { padding: 4px 8px; font-size: 0.8em; }

.btn:hover {
    opacity: 0.9;
}

.no-devices {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 40px;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h5 {
    margin: 0;
}

.close {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}
`;
