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
                            <form id="device-config-form">
                                <div class="form-group">
                                    <label for="device-name">Nom du dispositif</label>
                                    <input type="text" id="device-name" name="name" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="device-type">Type de dispositif</label>
                                    <select id="device-type" name="device_type" class="form-control">
                                        <option value="joystick">Joystick</option>
                                        <option value="gamepad">Gamepad</option>
                                        <option value="throttle">Throttle</option>
                                        <option value="rudder">Rudder</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="vendor-id">Vendor ID</label>
                                    <input type="text" id="vendor-id" name="vendor_id" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="product-id">Product ID</label>
                                    <input type="text" id="product-id" name="product_id" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="device-buttons">Boutons (séparés par des virgules)</label>
                                    <textarea id="device-buttons" name="buttons" class="form-control" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="device-axes">Mapping des axes (JSON)</label>
                                    <textarea id="device-axes" name="axes_map" class="form-control" rows="4">{}</textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="cancel-device-config">Annuler</button>
                            <button type="button" class="btn btn-primary" id="save-device-config">Sauvegarder</button>
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
        
        for (const [id, device] of this.devices) {
            html += `
                <div class="device-card" data-device-id="${id}">
                    <div class="device-header">
                        <h5>${device.name || 'Device sans nom'}</h5>
                        <span class="device-type">${device.device_type || 'unknown'}</span>
                    </div>
                    <div class="device-info">
                        <p><strong>ID:</strong> ${device.vendor_id || 'N/A'} / ${device.product_id || 'N/A'}</p>
                        <p><strong>Boutons:</strong> ${device.buttons ? device.buttons.length : 0}</p>
                        <p><strong>Axes:</strong> ${device.axes_map ? Object.keys(device.axes_map).length : 0}</p>
                        <p><small>Modifié: ${device.lastModified ? new Date(device.lastModified).toLocaleDateString() : 'N/A'}</small></p>
                    </div>
                    <div class="device-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary edit-device" data-device-id="${id}">
                            <i class="fas fa-edit"></i> Éditer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success export-device" data-device-id="${id}">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-device" data-device-id="${id}">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        return html;
    }

    /**
     * Lie les événements pour la gestion des dispositifs
     */
    bindDeviceManagementEvents(container) {
        // Bouton ajouter nouveau dispositif
        container.querySelector('#add-new-device').addEventListener('click', () => {
            this.showDeviceModal();
        });

        // Fermer modal
        container.querySelector('#close-device-modal').addEventListener('click', () => {
            this.hideDeviceModal();
        });

        container.querySelector('#cancel-device-config').addEventListener('click', () => {
            this.hideDeviceModal();
        });

        // Sauvegarder dispositif
        container.querySelector('#save-device-config').addEventListener('click', () => {
            this.saveDeviceFromModal();
        });

        // Actions sur les dispositifs
        container.addEventListener('click', (e) => {
            const deviceId = e.target.dataset.deviceId;
            
            if (e.target.classList.contains('edit-device')) {
                this.editDevice(deviceId);
            } else if (e.target.classList.contains('export-device')) {
                this.exportDeviceJSON(deviceId);
            } else if (e.target.classList.contains('delete-device')) {
                this.deleteDevice(deviceId);
            }
        });
    }

    /**
     * Affiche le modal de configuration
     */
    showDeviceModal(deviceData = null) {
        const modal = document.getElementById('device-config-modal');
        const title = document.getElementById('device-modal-title');
        
        if (deviceData) {
            title.textContent = 'Éditer le dispositif';
            this.populateDeviceForm(deviceData);
        } else {
            title.textContent = 'Ajouter un nouveau dispositif';
            this.clearDeviceForm();
        }
        
        modal.style.display = 'block';
    }

    /**
     * Cache le modal de configuration
     */
    hideDeviceModal() {
        document.getElementById('device-config-modal').style.display = 'none';
    }

    /**
     * Remplit le formulaire avec les données d'un dispositif
     */
    populateDeviceForm(deviceData) {
        document.getElementById('device-name').value = deviceData.name || '';
        document.getElementById('device-type').value = deviceData.device_type || 'joystick';
        document.getElementById('vendor-id').value = deviceData.vendor_id || '';
        document.getElementById('product-id').value = deviceData.product_id || '';
        document.getElementById('device-buttons').value = deviceData.buttons ? deviceData.buttons.join(', ') : '';
        document.getElementById('device-axes').value = JSON.stringify(deviceData.axes_map || {}, null, 2);
    }

    /**
     * Vide le formulaire
     */
    clearDeviceForm() {
        document.getElementById('device-config-form').reset();
        document.getElementById('device-axes').value = '{}';
    }

    /**
     * Sauvegarde le dispositif depuis le modal
     */
    async saveDeviceFromModal() {
        try {
            const formData = new FormData(document.getElementById('device-config-form'));
            const deviceData = {};
            
            for (const [key, value] of formData.entries()) {
                if (key === 'buttons') {
                    deviceData[key] = value.split(',').map(b => b.trim()).filter(b => b.length > 0);
                } else if (key === 'axes_map') {
                    try {
                        deviceData[key] = JSON.parse(value);
                    } catch (e) {
                        alert('Format JSON invalide pour les axes');
                        return;
                    }
                } else {
                    deviceData[key] = value;
                }
            }
            
            await this.saveDevice(deviceData);
            this.hideDeviceModal();
            
        } catch (error) {
            console.error('Erreur sauvegarde dispositif:', error);
            alert('Erreur lors de la sauvegarde du dispositif');
        }
    }

    /**
     * Édite un dispositif existant
     */
    editDevice(deviceId) {
        const device = this.devices.get(deviceId);
        if (device) {
            this.currentEditingId = deviceId;
            this.showDeviceModal(device);
        }
    }

    /**
     * Supprime un dispositif
     */
    async deleteDevice(deviceId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce dispositif ?')) {
            this.devices.delete(deviceId);
            await this.saveDevicesToStorage();
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
        const id = deviceId || this.currentEditingId || this.generateDeviceId();
        deviceData.id = id;
        deviceData.lastModified = new Date().toISOString();
        
        this.devices.set(id, deviceData);
        await this.saveDevicesToStorage();
        this.refreshDevicesList();
        
        // Réinitialiser l'ID d'édition
        this.currentEditingId = null;
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
    async importDevicesFromJSON(jsonData) {
        try {
            const devices = JSON.parse(jsonData);
            if (Array.isArray(devices)) {
                for (const device of devices) {
                    const id = this.generateDeviceId();
                    await this.saveDevice(device, id);
                }
            } else if (typeof devices === 'object') {
                const id = this.generateDeviceId();
                await this.saveDevice(devices, id);
            }
            this.refreshDevicesList();
        } catch (error) {
            console.error('Erreur lors de l\'importation:', error);
            alert('Erreur lors de l\'importation du fichier JSON');
        }
    }

    /**
     * Recherche des dispositifs par critères
     */
    searchDevices(criteria = {}) {
        const devices = Array.from(this.devices.values());
        
        return devices.filter(device => {
            if (criteria.device_type && device.device_type !== criteria.device_type) {
                return false;
            }
            if (criteria.vendor_id && device.vendor_id !== criteria.vendor_id) {
                return false;
            }
            if (criteria.name && !device.name.toLowerCase().includes(criteria.name.toLowerCase())) {
                return false;
            }
            return true;
        });
    }

    /**
     * Obtient des statistiques sur les dispositifs
     */
    async getStats() {
        const deviceTypes = {};
        const vendors = {};
        
        for (const device of this.devices.values()) {
            // Compter par type
            const type = device.device_type || 'unknown';
            deviceTypes[type] = (deviceTypes[type] || 0) + 1;
            
            // Compter par vendor
            const vendor = device.vendor_id || 'unknown';
            vendors[vendor] = (vendors[vendor] || 0) + 1;
        }
        
        const storageStats = this.storage && this.storage.getStats 
            ? await this.storage.getStats() 
            : { cache_size: 0, fallback_active: false };
        
        return {
            total_devices: this.devices.size,
            device_types: deviceTypes,
            vendors: vendors,
            storage: storageStats,
            last_updated: new Date().toISOString()
        };
    }

    /**
     * Synchronise les données avec le stockage distant
     */
    async syncWithStorage() {
        if (this.storage && this.storage.sync) {
            try {
                await this.storage.sync();
                console.log('✅ Synchronisation terminée');
            } catch (error) {
                console.error('Erreur synchronisation:', error);
            }
        }
    }
}
