// Interface utilisateur pour la configuration des nouveaux devices
import { XMLDeviceInstancer } from './xmlDeviceInstancer.js';

export class DeviceSetupUI {
    constructor(containerId = null, autoDetection = null) {
        this.autoDetection = autoDetection;
        this.containerId = containerId;
        this.currentDeviceKey = null;
        this.mode = 'full'; // 'full' ou 'json-only'
        this.setupSteps = ['info', 'axes', 'hats', 'confirm', 'xml'];
        this.currentStep = 0;
        this.userConfig = {
            axesMapping: {},
            hatsConfig: {}
        };
        
        // Callbacks externes
        this.onComplete = null;
        this.onCancel = null;
        
        // Initialiser le module d'instanciation XML (seulement en mode full)
        if (this.mode === 'full') {
            this.xmlInstancer = new XMLDeviceInstancer();
        }
        
        // Si un containerId est fourni, c'est probablement pour le mode json-only
        if (containerId) {
            this.mode = 'json-only';
            this.setupSteps = ['info', 'axes', 'hats', 'confirm']; // Sans l'étape XML
        } else {
            this.createUI();
            this.bindEvents();
        }
    }

    /**
     * Méthode helper pour récupérer de manière sécurisée les informations du device
     * @param {string} deviceKey - Clé du device (optionnel, utilise currentDeviceKey par défaut)
     * @returns {Object|null} - Informations du device ou null
     */
    getDeviceInfo(deviceKey = null) {
        const key = deviceKey || this.currentDeviceKey;
        
        if (!this.autoDetection) {
            console.error('❌ Système de détection non initialisé');
            return null;
        }
        
        if (!this.autoDetection.unknownDevices) {
            console.error('❌ Liste des dispositifs inconnus non disponible');
            return null;
        }
        
        const deviceInfo = this.autoDetection.unknownDevices.get(key);
        if (!deviceInfo) {
            console.error('❌ Device inconnu non trouvé:', key);
            return null;
        }
        
        return deviceInfo;
    }

    createUI() {
        // Créer le modal de configuration
        const modalHTML = `
            <div id="deviceSetupModal" class="modal" style="display: none;">
                <div class="modal-content device-setup-modal">
                    <div class="modal-header">
                        <h2>Configuration d'un nouveau device</h2>
                        <span class="close">&times;</span>
                    </div>
                    
                    <div class="setup-progress">
                        <div class="progress-steps">
                            <div class="step active" data-step="0">
                                <div class="step-number">1</div>
                                <div class="step-label">Informations</div>
                            </div>
                            <div class="step" data-step="1">
                                <div class="step-number">2</div>
                                <div class="step-label">Axes</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">3</div>
                                <div class="step-label">Hats/POV</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">4</div>
                                <div class="step-label">Confirmation</div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-number">5</div>
                                <div class="step-label">Intégration XML</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-body">
                        <!-- Étape 1: Informations du device -->
                        <div id="setupStep0" class="setup-step active">
                            <h3>Device détecté</h3>
                            <div class="device-info">
                                <div class="info-row">
                                    <label>Nom:</label>
                                    <span id="deviceName">-</span>
                                </div>
                                <div class="info-row">
                                    <label>Vendor ID:</label>
                                    <span id="deviceVendorId">-</span>
                                </div>
                                <div class="info-row">
                                    <label>Product ID:</label>
                                    <span id="deviceProductId">-</span>
                                </div>
                                <div class="info-row">
                                    <label>Boutons:</label>
                                    <span id="deviceButtons">-</span>
                                </div>
                                <div class="info-row">
                                    <label>Axes:</label>
                                    <span id="deviceAxes">-</span>
                                </div>
                            </div>
                            <p>Ce device n'est pas encore configuré. Voulez-vous le configurer maintenant ?</p>
                        </div>
                        
                        <!-- Étape 2: Configuration des axes -->
                        <div id="setupStep1" class="setup-step">
                            <h3>Configuration des axes</h3>
                            <p>Configurez le mapping des axes de votre device :</p>
                            <div id="axesConfiguration" class="axes-config">
                                <!-- Généré dynamiquement -->
                            </div>
                            <div class="axis-test">
                                <h4>Test en temps réel</h4>
                                <p>Bougez vos sticks/axes pour voir les valeurs :</p>
                                <div id="axesTestDisplay" class="test-display">
                                    <!-- Valeurs en temps réel -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Étape 3: Configuration des Hats -->
                        <div id="setupStep2" class="setup-step">
                            <h3>Configuration des Hats/POV</h3>
                            <p>Si votre device possède des hats (D-pad/POV), configurez-les ici :</p>
                            <div class="hat-config">
                                <label>
                                    <input type="checkbox" id="hasHats"> 
                                    Mon device possède des hats/POV
                                </label>
                                <div id="hatsConfiguration" class="hats-config" style="display: none;">
                                    <!-- Configuration des hats -->
                                </div>
                            </div>
                            <div class="hat-test" id="hatTestSection" style="display: none;">
                                <h4>Test des hats</h4>
                                <p>Utilisez votre D-pad/POV pour tester :</p>
                                <div id="hatTestDisplay" class="test-display">
                                    <!-- Test en temps réel -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Étape 4: Confirmation -->
                        <div id="setupStep3" class="setup-step">
                            <h3>Confirmation de la configuration</h3>
                            <div class="config-summary">
                                <h4>Résumé de la configuration :</h4>
                                <div id="configSummary">
                                    <!-- Résumé généré -->
                                </div>
                            </div>
                            <p>Voulez-vous sauvegarder cette configuration ?</p>
                        </div>
                        
                        <!-- Étape 5: Intégration XML -->
                        <div id="setupStep4" class="setup-step">
                            <h3>🎯 Intégration dans votre XML Star Citizen</h3>
                            <div class="xml-integration-section">
                                <div class="integration-info">
                                    <h4>✨ Instanciation automatique du device</h4>
                                    <p>Votre nouveau device peut être automatiquement ajouté à votre fichier XML Star Citizen :</p>
                                    
                                    <div class="xml-device-preview" id="xmlDevicePreview">
                                        <!-- Preview des modifications XML -->
                                    </div>
                                    
                                    <div class="xml-integration-options">
                                        <label class="xml-option">
                                            <input type="radio" name="xmlIntegration" value="download" checked>
                                            <strong>📥 Télécharger XML modifié</strong>
                                            <span class="option-description">Télécharge votre XML avec le nouveau device instancié automatiquement</span>
                                        </label>
                                        
                                        <label class="xml-option">
                                            <input type="radio" name="xmlIntegration" value="manual">
                                            <strong>✋ Configuration manuelle</strong>
                                            <span class="option-description">Affiche les instructions pour ajouter le device manuellement</span>
                                        </label>
                                        
                                        <label class="xml-option">
                                            <input type="radio" name="xmlIntegration" value="skip">
                                            <strong>⏭️ Ignorer pour l'instant</strong>
                                            <span class="option-description">Sauvegarde uniquement la configuration du device</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="xml-status" id="xmlStatus">
                                    <!-- Status de l'analyse XML -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button id="setupPrevBtn" class="btn btn-secondary" disabled>Précédent</button>
                        <button id="setupNextBtn" class="btn btn-primary">Suivant</button>
                        <button id="setupCancelBtn" class="btn btn-danger">Annuler</button>
                        <button id="setupSaveBtn" class="btn btn-success" style="display: none;">Sauvegarder</button>
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter le modal au body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Ajouter les styles CSS
        this.addStyles();
    }

    addStyles() {
        const styles = `
            <style>
                .device-setup-modal {
                    max-width: 800px;
                    min-height: 600px;
                }
                
                .setup-progress {
                    margin: 20px 0;
                    padding: 20px 0;
                    border-bottom: 1px solid #ddd;
                }
                
                .progress-steps {
                    display: flex;
                    justify-content: space-between;
                    max-width: 600px;
                    margin: 0 auto;
                }
                
                .step {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    opacity: 0.5;
                    transition: opacity 0.3s;
                }
                
                .step.active {
                    opacity: 1;
                    color: #007cba;
                }
                
                .step.completed {
                    opacity: 1;
                    color: #28a745;
                }
                
                .step-number {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: #ddd;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-bottom: 8px;
                }
                
                .step.active .step-number {
                    background: #007cba;
                    color: white;
                }
                
                .step.completed .step-number {
                    background: #28a745;
                    color: white;
                }
                
                .setup-step {
                    display: none;
                    min-height: 400px;
                    padding: 20px 0;
                }
                
                .setup-step.active {
                    display: block;
                }
                
                .device-info {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                
                .info-row {
                    display: flex;
                    margin-bottom: 10px;
                }
                
                .info-row label {
                    min-width: 120px;
                    font-weight: bold;
                }
                
                .axes-config, .hats-config {
                    margin: 20px 0;
                }
                
                .axis-config-row, .hat-config-row {
                    display: flex;
                    align-items: center;
                    margin-bottom: 15px;
                    padding: 10px;
                    background: #f8f9fa;
                    border-radius: 5px;
                }
                
                .axis-config-row label {
                    min-width: 80px;
                    margin-right: 15px;
                }
                
                .axis-config-row select, .hat-config-row select {
                    min-width: 150px;
                    margin-right: 15px;
                }
                
                .test-display {
                    background: #000;
                    color: #0f0;
                    padding: 15px;
                    border-radius: 5px;
                    font-family: monospace;
                    min-height: 100px;
                    margin: 15px 0;
                }
                
                .axis-test-value, .hat-test-value {
                    margin: 5px 0;
                }
                
                .config-summary {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                
                .summary-section {
                    margin-bottom: 15px;
                }
                
                .summary-section h5 {
                    margin-bottom: 8px;
                    color: #007cba;
                }
                
                .btn {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    margin: 0 5px;
                }
                
                .btn-primary {
                    background: #007cba;
                    color: white;
                }
                
                .btn-secondary {
                    background: #6c757d;
                    color: white;
                }
                
                .btn-danger {
                    background: #dc3545;
                    color: white;
                }
                
                .btn-success {
                    background: #28a745;
                    color: white;
                }
                
                .btn:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }
                
                .device-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border: 2px solid #007cba;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 10000;
                    max-width: 400px;
                    animation: slideIn 0.3s ease-out;
                }
                
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                .notification-content h4 {
                    margin: 0 0 10px 0;
                    color: #007cba;
                }
                
                .notification-content p {
                    margin: 5px 0;
                }
                
                .notification-actions {
                    margin-top: 15px;
                    text-align: right;
                }
                
                .xml-integration-section {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                
                .xml-device-preview {
                    background: #e9ecef;
                    padding: 15px;
                    border-radius: 5px;
                    font-family: monospace;
                    max-height: 200px;
                    overflow-y: auto;
                    margin: 15px 0;
                }
                
                .xml-integration-options {
                    margin: 15px 0;
                }
                
                .xml-option {
                    display: block;
                    margin-bottom: 10px;
                }
                
                .xml-option input {
                    margin-right: 10px;
                }
                
                .option-description {
                    font-size: 0.9em;
                    color: #666;
                }
                
                .xml-status {
                    margin-top: 15px;
                    padding: 10px;
                    border-radius: 5px;
                    display: none;
                }
                
                .xml-status.success {
                    background: #d4edda;
                    color: #155724;
                }
                
                .xml-status.error {
                    background: #f8d7da;
                    color: #721c24;
                }
                
                .xml-preview-content {
                    margin: 15px 0;
                }
                
                .xml-preview-content code {
                    display: block;
                    background: #f8f9fa;
                    padding: 8px 12px;
                    border-radius: 4px;
                    margin: 5px 0;
                    font-family: 'Courier New', monospace;
                    border-left: 3px solid #007cba;
                }
                
                .xml-preview-content h6 {
                    margin: 15px 0 5px 0;
                    color: #495057;
                    font-size: 0.9em;
                    font-weight: bold;
                }
                
                .xml-preview-content p {
                    margin: 8px 0;
                }
                
                .xml-preview-content .error {
                    color: #dc3545;
                    font-weight: bold;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    bindEvents() {
        // Événements des boutons
        document.getElementById('setupNextBtn').addEventListener('click', () => this.nextStep());
        document.getElementById('setupPrevBtn').addEventListener('click', () => this.prevStep());
        document.getElementById('setupCancelBtn').addEventListener('click', () => this.cancelSetup());
        document.getElementById('setupSaveBtn').addEventListener('click', () => this.saveConfiguration());
        
        // Fermer le modal
        document.querySelector('#deviceSetupModal .close').addEventListener('click', () => this.closeModal());
        
        // Checkbox pour les hats
        document.getElementById('hasHats').addEventListener('change', (e) => {
            const hatsConfig = document.getElementById('hatsConfiguration');
            const hatTest = document.getElementById('hatTestSection');
            if (e.target.checked) {
                hatsConfig.style.display = 'block';
                hatTest.style.display = 'block';
                this.generateHatsConfiguration();
            } else {
                hatsConfig.style.display = 'none';
                hatTest.style.display = 'none';
                this.userConfig.hatsConfig = {};
            }
        });
        
        // Event listeners pour l'intégration XML
        document.querySelectorAll('input[name="xmlIntegration"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleXMLIntegrationChange(e.target.value);
            });
        });
        
        // Écouter les détections de nouveaux devices
        console.log('🎯 DeviceSetupUI: Abonnement aux détections de nouveaux devices');
        this.autoDetection.onNewDeviceDetected((deviceInfo) => {
            console.log('🔔 DeviceSetupUI: Callback de détection appelé pour:', deviceInfo);
            this.showNewDeviceNotification(deviceInfo);
        });
    }

    showNewDeviceNotification(deviceInfo) {
        console.log('📢 DeviceSetupUI: Affichage de la notification pour:', deviceInfo);
        
        // Créer une notification
        const notification = document.createElement('div');
        notification.className = 'device-notification';
        
        // Générer un ID unique pour cette notification
        const notificationId = 'notification_' + Date.now();
        notification.id = notificationId;
        
        const deviceKey = this.autoDetection.getDeviceKey(deviceInfo.gamepad);
        
        notification.innerHTML = `
            <div class="notification-content">
                <h4>Nouveau device détecté</h4>
                <p><strong>${deviceInfo.id}</strong></p>
                <p>Ce device n'est pas encore configuré.</p>
                <div class="notification-actions">
                    <button class="btn btn-primary" data-device-key="${deviceKey}" data-notification-id="${notificationId}">
                        Configurer maintenant
                    </button>
                    <button class="btn btn-secondary" onclick="document.getElementById('${notificationId}').remove()">
                        Plus tard
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Ajouter le gestionnaire d'événement pour le bouton "Configurer maintenant"
        const configureButton = notification.querySelector('.btn-primary');
        configureButton.addEventListener('click', () => {
            const deviceKey = configureButton.getAttribute('data-device-key');
            const notificationId = configureButton.getAttribute('data-notification-id');
            
            // Supprimer la notification
            const notificationElement = document.getElementById(notificationId);
            if (notificationElement) {
                notificationElement.remove();
            }
            
            // Démarrer la configuration
            this.startSetup(deviceKey);
        });
        
        // Auto-remove après 10 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 10000);
    }

    startSetup(deviceKey) {
        this.currentDeviceKey = deviceKey;
        
        // Vérifier que l'autoDetection est disponible
        if (!this.autoDetection) {
            console.error('❌ Système de détection non initialisé');
            throw new Error('Système de détection non initialisé');
        }
        
        // Vérifier que unknownDevices est disponible
        if (!this.autoDetection.unknownDevices) {
            console.error('❌ Liste des dispositifs inconnus non disponible');
            throw new Error('Liste des dispositifs inconnus non disponible');
        }
        
        const deviceInfo = this.autoDetection.unknownDevices.get(deviceKey);
        
        if (!deviceInfo) {
            console.error('❌ Device inconnu non trouvé pour la configuration:', deviceKey);
            throw new Error(`Device inconnu non trouvé: ${deviceKey}`);
        }
        
        // Réinitialiser l'état du setup
        this.currentStep = 0;
        this.userConfig = {
            axesMapping: {},
            hatsConfig: {}
        };
        
        // Remplir les informations du device
        this.populateDeviceInfo(deviceInfo);
        
        // Générer la configuration des axes
        this.generateAxesConfiguration(deviceInfo);
        
        // Afficher le modal
        this.showModal();
        
        // Démarrer le monitoring en temps réel
        this.startRealTimeMonitoring(deviceInfo);
    }

    populateDeviceInfo(deviceInfo) {
        document.getElementById('deviceName').textContent = deviceInfo.id;
        document.getElementById('deviceVendorId').textContent = deviceInfo.vendor_id || 'N/A';
        document.getElementById('deviceProductId').textContent = deviceInfo.product_id || 'N/A';
        document.getElementById('deviceButtons').textContent = deviceInfo.buttons;
        document.getElementById('deviceAxes').textContent = deviceInfo.axes;
    }

    generateAxesConfiguration(deviceInfo) {
        const container = document.getElementById('axesConfiguration');
        container.innerHTML = '';
        
        const axesOptions = [
            { value: 'x', label: 'X (Horizontal gauche)' },
            { value: 'y', label: 'Y (Vertical gauche)' },
            { value: 'z', label: 'Z (Throttle/Gâchette)' },
            { value: 'rotx', label: 'Rot X (Horizontal droite)' },
            { value: 'roty', label: 'Rot Y (Vertical droite)' },
            { value: 'rotz', label: 'Rot Z (Rotation Z)' },
            { value: 'slider0', label: 'Slider 1' },
            { value: 'slider1', label: 'Slider 2' },
            { value: 'custom', label: 'Personnalisé' }
        ];
        
        for (let i = 0; i < deviceInfo.axes; i++) {
            const row = document.createElement('div');
            row.className = 'axis-config-row';
            
            const select = document.createElement('select');
            select.id = `axis${i}Select`;
            
            // Option par défaut
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Sélectionner...';
            select.appendChild(defaultOption);
            
            // Ajouter les options
            axesOptions.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                select.appendChild(opt);
            });
            
            // Input personnalisé
            const customInput = document.createElement('input');
            customInput.type = 'text';
            customInput.id = `axis${i}Custom`;
            customInput.placeholder = 'Nom personnalisé';
            customInput.style.display = 'none';
            
            // Gestion du changement
            select.addEventListener('change', (e) => {
                if (e.target.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.focus();
                } else {
                    customInput.style.display = 'none';
                    if (e.target.value) {
                        this.userConfig.axesMapping[i] = e.target.value;
                    } else {
                        delete this.userConfig.axesMapping[i];
                    }
                }
            });
            
            customInput.addEventListener('input', (e) => {
                if (e.target.value.trim()) {
                    this.userConfig.axesMapping[i] = e.target.value.trim();
                } else {
                    delete this.userConfig.axesMapping[i];
                }
            });
            
            row.innerHTML = `<label>Axe ${i}:</label>`;
            row.appendChild(select);
            row.appendChild(customInput);
            
            container.appendChild(row);
        }
    }

    generateHatsConfiguration() {
        const container = document.getElementById('hatsConfiguration');
        container.innerHTML = '';
        
        // Configuration simple pour un hat sur l'axe 0 et 1 (X/Y)
        const hatConfig = document.createElement('div');
        hatConfig.innerHTML = `
            <h5>Configuration du Hat principal</h5>
            <div class="hat-config-row">
                <label>Axe X du hat:</label>
                <select id="hatAxisXSelect">
                    <option value="">Sélectionner...</option>
                </select>
            </div>
            <div class="hat-config-row">
                <label>Axe Y du hat:</label>
                <select id="hatAxisYSelect">
                    <option value="">Sélectionner...</option>
                </select>
            </div>
        `;
        
        container.appendChild(hatConfig);
        
        // Remplir les options avec les axes disponibles
        const deviceInfo = this.getDeviceInfo();
        if (deviceInfo) {
            const selectX = document.getElementById('hatAxisXSelect');
            const selectY = document.getElementById('hatAxisYSelect');
            
            for (let i = 0; i < deviceInfo.axes; i++) {
                const optX = document.createElement('option');
                optX.value = i;
                optX.textContent = `Axe ${i}`;
                selectX.appendChild(optX);
                
                const optY = document.createElement('option');
                optY.value = i;
                optY.textContent = `Axe ${i}`;
                selectY.appendChild(optY);
            }
            
            // Événements de changement
            selectX.addEventListener('change', () => this.updateHatConfig());
            selectY.addEventListener('change', () => this.updateHatConfig());
        }
    }

    updateHatConfig() {
        const axisX = document.getElementById('hatAxisXSelect').value;
        const axisY = document.getElementById('hatAxisYSelect').value;
        
        if (axisX && axisY) {
            this.userConfig.hatsConfig = {
                "0": {
                    "directions": {
                        "up": { "axis": parseInt(axisY), "value_min": -1, "value_max": -0.5 },
                        "down": { "axis": parseInt(axisY), "value_min": 0.5, "value_max": 1 },
                        "left": { "axis": parseInt(axisX), "value_min": -1, "value_max": -0.5 },
                        "right": { "axis": parseInt(axisX), "value_min": 0.5, "value_max": 1 }
                    }
                }
            };
        } else {
            this.userConfig.hatsConfig = {};
        }
    }

    startRealTimeMonitoring(deviceInfo) {
        // Arrêter le monitoring précédent s'il existe
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
        }
        
        this.monitoringInterval = setInterval(() => {
            const gamepads = navigator.getGamepads();
            const gamepad = Array.from(gamepads).find(gp => 
                gp && this.autoDetection.getDeviceKey(gp) === this.currentDeviceKey
            );
            
            if (gamepad) {
                this.updateAxisTestDisplay(gamepad);
                this.updateHatTestDisplay(gamepad);
            }
        }, 100);
    }

    updateAxisTestDisplay(gamepad) {
        const display = document.getElementById('axesTestDisplay');
        if (!display || this.currentStep !== 1) return;
        
        let output = 'Valeurs des axes en temps réel:\n\n';
        for (let i = 0; i < gamepad.axes.length; i++) {
            const value = gamepad.axes[i].toFixed(3);
            const mapping = this.userConfig.axesMapping[i] || 'non configuré';
            output += `Axe ${i}: ${value.padStart(7)} -> ${mapping}\n`;
        }
        
        display.textContent = output;
    }

    updateHatTestDisplay(gamepad) {
        const display = document.getElementById('hatTestDisplay');
        if (!display || this.currentStep !== 2 || !document.getElementById('hasHats').checked) return;
        
        let output = 'Test des hats:\n\n';
        
        if (Object.keys(this.userConfig.hatsConfig).length > 0) {
            const hatConfig = this.userConfig.hatsConfig["0"];
            if (hatConfig && hatConfig.directions) {
                for (const [direction, config] of Object.entries(hatConfig.directions)) {
                    const axisValue = gamepad.axes[config.axis];
                    const isActive = axisValue >= config.value_min && axisValue <= config.value_max;
                    output += `${direction.toUpperCase()}: ${isActive ? '[ACTIF]' : '[inactif]'} (axe ${config.axis}: ${axisValue.toFixed(3)})\n`;
                }
            }
        } else {
            output += 'Configurez d\'abord les axes du hat.';
        }
        
        display.textContent = output;
    }

    nextStep() {
        if (this.currentStep < this.setupSteps.length - 1) {
            this.currentStep++;
            this.updateStepDisplay();
            
            // Si on arrive à l'étape de confirmation, générer le résumé
            if (this.currentStep === 3) { // Étape 4: Confirmation
                this.generateConfigSummary();
            }
            
            // Si on arrive à l'étape XML, initialiser l'intégration
            if (this.currentStep === 4) { // Étape 5: XML Integration
                this.initializeXMLIntegration();
            }
            
            // Si c'est la dernière étape, changer le bouton
            if (this.currentStep === this.setupSteps.length - 1) {
                document.getElementById('setupNextBtn').style.display = 'none';
                document.getElementById('setupSaveBtn').style.display = 'inline-block';
            }
        }
        
        this.updateButtons();
    }

    prevStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.updateStepDisplay();
            
            if (this.currentStep < this.setupSteps.length - 1) {
                document.getElementById('setupNextBtn').style.display = 'inline-block';
                document.getElementById('setupSaveBtn').style.display = 'none';
            }
        }
        
        this.updateButtons();
    }

    updateStepDisplay() {
        // Masquer toutes les étapes
        document.querySelectorAll('.setup-step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Afficher l'étape actuelle
        document.getElementById(`setupStep${this.currentStep}`).classList.add('active');
        
        // Mettre à jour la barre de progression
        document.querySelectorAll('.step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index === this.currentStep) {
                step.classList.add('active');
            } else if (index < this.currentStep) {
                step.classList.add('completed');
            }
        });
    }

    updateButtons() {
        document.getElementById('setupPrevBtn').disabled = (this.currentStep === 0);
    }

    generateConfigSummary() {
        const container = document.getElementById('configSummary');
        const deviceInfo = this.getDeviceInfo();
        
        if (!deviceInfo) {
            container.innerHTML = '<p class="text-danger">❌ Erreur: Informations du device non disponibles</p>';
            return;
        }
        
        let summary = `
            <div class="summary-section">
                <h5>Device</h5>
                <p><strong>Nom:</strong> ${deviceInfo.id}</p>
                <p><strong>Vendor/Product ID:</strong> ${deviceInfo.vendor_id || 'N/A'} / ${deviceInfo.product_id || 'N/A'}</p>
            </div>
            
            <div class="summary-section">
                <h5>Configuration des axes</h5>
        `;
        
        if (Object.keys(this.userConfig.axesMapping).length > 0) {
            for (const [axis, mapping] of Object.entries(this.userConfig.axesMapping)) {
                summary += `<p>Axe ${axis} → ${mapping}</p>`;
            }
        } else {
            summary += '<p>Aucun axe configuré</p>';
        }
        
        summary += '</div><div class="summary-section"><h5>Configuration des hats</h5>';
        
        if (Object.keys(this.userConfig.hatsConfig).length > 0) {
            summary += '<p>Hat configuré avec directions: up, down, left, right</p>';
        } else {
            summary += '<p>Aucun hat configuré</p>';
        }
        
        summary += '</div>';
        
        container.innerHTML = summary;
    }

    /**
     * Initialise l'intégration XML quand on arrive à l'étape XML
     */
    async initializeXMLIntegration() {
        console.log('🎯 Initialisation de l\'intégration XML');
        
        const statusDiv = document.getElementById('xmlStatus');
        const previewDiv = document.getElementById('xmlDevicePreview');
        
        try {
            // Vérifier si un XML est chargé dans l'éditeur
            const xmlContent = this.getCurrentXMLContent();
            
            if (!xmlContent) {
                this.showXMLStatus('Aucun fichier XML Star Citizen chargé. Vous devrez configurer manuellement.', 'error');
                this.disableXMLDownloadOption();
                return;
            }
            
            // Initialiser l'instancer avec le XML
            const initialized = this.xmlInstancer.initialize(xmlContent);
            
            if (!initialized) {
                this.showXMLStatus('Erreur lors de l\'analyse du XML. Configuration manuelle recommandée.', 'error');
                this.disableXMLDownloadOption();
                return;
            }
            
            // Générer l'aperçu des modifications
            await this.generateXMLPreview();
            this.showXMLStatus('XML analysé avec succès. Prêt pour l\'intégration automatique.', 'success');
            
        } catch (error) {
            console.error('Erreur lors de l\'initialisation XML:', error);
            this.showXMLStatus(`Erreur: ${error.message}`, 'error');
            this.disableXMLDownloadOption();
        }
    }

    /**
     * Récupère le contenu XML actuel depuis l'éditeur principal
     */
    getCurrentXMLContent() {
        // Essayer de récupérer le XML depuis l'éditeur principal
        if (window.scConfigEditor && window.scConfigEditor.currentXMLContent) {
            return window.scConfigEditor.currentXMLContent;
        }
        
        // Fallback: essayer de récupérer depuis localStorage ou autre source
        const storedXML = localStorage.getItem('currentXMLContent');
        if (storedXML) {
            return storedXML;
        }
        
        return null;
    }

    /**
     * Génère l'aperçu des modifications XML
     */
    async generateXMLPreview() {
        const deviceInfo = this.getDeviceInfo();
        const previewDiv = document.getElementById('xmlDevicePreview');
        
        if (!deviceInfo) {
            previewDiv.innerHTML = '<p class="text-danger">❌ Erreur: Informations du device non disponibles</p>';
            return;
        }
        
        try {
            // Obtenir la prochaine instance
            const nextInstance = this.xmlInstancer.getNextAvailableInstance();
            
            // Générer les informations du device pour XML
            const deviceXMLInfo = this.xmlInstancer.generateDeviceXMLInfo(deviceInfo, nextInstance);
            
            // Créer l'aperçu
            const previewHTML = `
                <h5>📋 Modifications qui seront apportées :</h5>
                <div class="xml-preview-content">
                    <p><strong>Instance assignée:</strong> ${nextInstance}</p>
                    <p><strong>Device ID XML:</strong> ${deviceXMLInfo.xmlId}</p>
                    
                    <h6>Déclaration dans &lt;devices&gt; :</h6>
                    <code>&lt;joystick instance="${nextInstance}"/&gt;</code>
                    
                    <h6>Section d'options :</h6>
                    <code>&lt;options type="joystick" instance="${nextInstance}" Product="${deviceXMLInfo.productName}"/&gt;</code>
                </div>
            `;
            
            previewDiv.innerHTML = previewHTML;
            
        } catch (error) {
            console.error('Erreur lors de la génération de l\'aperçu:', error);
            previewDiv.innerHTML = `<p class="error">Erreur lors de la génération de l'aperçu: ${error.message}</p>`;
        }
    }

    /**
     * Gère le changement d'option d'intégration XML
     */
    handleXMLIntegrationChange(option) {
        console.log('🔄 Option d\'intégration XML changée:', option);
        
        const statusDiv = document.getElementById('xmlStatus');
        
        switch (option) {
            case 'download':
                this.showXMLStatus('Téléchargement automatique sélectionné. Le XML modifié sera proposé au téléchargement.', 'success');
                break;
            case 'manual':
                this.showXMLStatus('Configuration manuelle sélectionnée. Les instructions seront affichées.', 'info');
                break;
            case 'skip':
                this.showXMLStatus('Intégration XML ignorée. Seule la configuration du device sera sauvegardée.', 'info');
                break;
        }
    }

    /**
     * Affiche le statut de l'intégration XML
     */
    showXMLStatus(message, type = 'info') {
        const statusDiv = document.getElementById('xmlStatus');
        statusDiv.textContent = message;
        statusDiv.className = `xml-status ${type}`;
        statusDiv.style.display = 'block';
    }

    /**
     * Désactive l'option de téléchargement XML si elle n'est pas disponible
     */
    disableXMLDownloadOption() {
        const downloadOption = document.querySelector('input[name="xmlIntegration"][value="download"]');
        const manualOption = document.querySelector('input[name="xmlIntegration"][value="manual"]');
        
        if (downloadOption) {
            downloadOption.disabled = true;
            // Sélectionner l'option manuelle par défaut
            if (manualOption) {
                manualOption.checked = true;
                this.handleXMLIntegrationChange('manual');
            }
        }
    }

    async saveConfiguration() {
        try {
            document.getElementById('setupSaveBtn').disabled = true;
            document.getElementById('setupSaveBtn').textContent = 'Sauvegarde...';
            
            // 1. Sauvegarder la configuration du device
            const result = await this.autoDetection.saveDeviceMapping(this.currentDeviceKey, this.userConfig);
            
            if (!result.success) {
                throw new Error('Échec de la sauvegarde de la configuration');
            }
            
            // 2. Traiter l'intégration XML selon l'option sélectionnée
            const xmlOption = document.querySelector('input[name="xmlIntegration"]:checked')?.value || 'skip';
            await this.processXMLIntegration(xmlOption);
            
            // 3. Succès complet
            alert(`Configuration sauvegardée avec succès!\nFichier: ${result.fileName}`);
            this.closeModal();
            
            // Rafraîchir les données globales si nécessaire
            if (window.scConfigEditor && window.scConfigEditor.loadDevicesData) {
                window.scConfigEditor.loadDevicesData();
            }
            
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
            alert(`Erreur lors de la sauvegarde: ${error.message}`);
        } finally {
            document.getElementById('setupSaveBtn').disabled = false;
            document.getElementById('setupSaveBtn').textContent = 'Sauvegarder';
        }
    }

    /**
     * Traite l'intégration XML selon l'option choisie
     */
    async processXMLIntegration(option) {
        console.log('🎯 Traitement de l\'intégration XML:', option);
        
        switch (option) {
            case 'download':
                await this.downloadModifiedXML();
                break;
            case 'manual':
                this.showManualInstructions();
                break;
            case 'skip':
                console.log('✅ Intégration XML ignorée par l\'utilisateur');
                break;
        }
    }

    /**
     * Génère et propose le téléchargement du XML modifié
     */
    async downloadModifiedXML() {
        try {
            const deviceInfo = this.getDeviceInfo();
            if (!deviceInfo) {
                throw new Error('Informations du device non disponibles');
            }
            const nextInstance = this.xmlInstancer.getNextAvailableInstance();
            const deviceXMLInfo = this.xmlInstancer.generateDeviceXMLInfo(deviceInfo, nextInstance);
            
            // Générer le XML modifié
            const modifiedXML = this.xmlInstancer.generateModifiedXML(deviceXMLInfo);
            const newFilename = this.xmlInstancer.generateModifiedFilename();
            
            // Valider le XML
            const isValid = this.xmlInstancer.validateModifiedXML(modifiedXML);
            if (!isValid) {
                throw new Error('Le XML généré n\'est pas valide');
            }
            
            // Créer et déclencher le téléchargement
            const blob = new Blob([modifiedXML], { type: 'application/xml' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = newFilename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            console.log('✅ XML modifié téléchargé:', newFilename);
            
            // Afficher un message de succès avec instructions
            setTimeout(() => {
                alert(`✅ XML modifié téléchargé avec succès!\n\nFichier: ${newFilename}\n\n📖 Instructions:\n1. Remplacez votre fichier XML Star Citizen actuel\n2. Redémarrez Star Citizen\n3. Votre nouveau device sera disponible en instance ${nextInstance}`);
            }, 500);
            
        } catch (error) {
            console.error('Erreur lors du téléchargement XML:', error);
            alert(`Erreur lors de la génération du XML: ${error.message}\n\nVeuillez utiliser la configuration manuelle.`);
        }
    }

    /**
     * Affiche les instructions de configuration manuelle
     */
    showManualInstructions() {
        const deviceInfo = this.getDeviceInfo();
        if (!deviceInfo) {
            alert('❌ Erreur: Informations du device non disponibles pour la configuration manuelle.');
            return;
        }
        const nextInstance = this.xmlInstancer.getNextAvailableInstance();
        const deviceXMLInfo = this.xmlInstancer.generateDeviceXMLInfo(deviceInfo, nextInstance);
        
        const instructions = `
📋 Instructions de Configuration Manuelle

Pour ajouter votre device "${deviceInfo.id}" à votre XML Star Citizen :

1️⃣ Ouvrez votre fichier XML Star Citizen dans un éditeur de texte

2️⃣ Dans la section <devices>, ajoutez :
   <joystick instance="${nextInstance}"/>

3️⃣ Ajoutez une nouvelle section d'options :
   <options type="joystick" instance="${nextInstance}" Product="${deviceXMLInfo.productName}">
   </options>

4️⃣ Sauvegardez le fichier et redémarrez Star Citizen

✅ Votre device sera disponible en instance ${nextInstance}
        `;
        
        alert(instructions);
    }

    cancelSetup() {
        if (confirm('Êtes-vous sûr de vouloir annuler la configuration ?')) {
            this.autoDetection.cancelDeviceSetup();
            this.closeModal();
        }
    }

    showModal() {
        document.getElementById('deviceSetupModal').style.display = 'block';
        this.updateStepDisplay();
        this.updateButtons();
    }

    closeModal() {
        document.getElementById('deviceSetupModal').style.display = 'none';
        
        // Arrêter le monitoring
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
            this.monitoringInterval = null;
        }
        
        // Nettoyer les notifications
        document.querySelectorAll('.device-notification').forEach(notification => {
            notification.remove();
        });
    }

    /**
     * Configure le mode d'opération
     */
    setMode(mode) {
        this.mode = mode;
        if (mode === 'json-only') {
            this.setupSteps = ['info', 'axes', 'hats', 'confirm'];
        } else {
            this.setupSteps = ['info', 'axes', 'hats', 'confirm', 'xml'];
            if (!this.xmlInstancer) {
                this.xmlInstancer = new XMLDeviceInstancer();
            }
        }
    }

    /**
     * Initialise le UI dans un conteneur spécifique (mode json-only)
     */
    initializeInContainer(containerId) {
        this.containerId = containerId;
        this.mode = 'json-only';
        this.setupSteps = ['info', 'axes', 'hats', 'confirm'];
        
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Container ${containerId} not found`);
            return;
        }

        // Créer l'interface dans le conteneur
        container.innerHTML = this.generateInlineSetupHTML();
        this.bindInlineEvents(container);
    }

    /**
     * Génère le HTML pour l'interface inline (sans modal)
     */
    generateInlineSetupHTML() {
        return `
            <div class="device-setup-inline">
                <div class="setup-progress">
                    <div class="progress-steps">
                        ${this.setupSteps.map((step, index) => `
                            <div class="step ${index === 0 ? 'active' : ''}" data-step="${index}">
                                <div class="step-number">${index + 1}</div>
                                <div class="step-label">${this.getStepLabel(step)}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="setup-content">
                    ${this.generateStepContent()}
                </div>
                
                <div class="setup-actions">
                    <button type="button" class="btn btn-secondary" id="cancelSetup">Annuler</button>
                    <button type="button" class="btn btn-secondary" id="prevStep" style="display: none;">Précédent</button>
                    <button type="button" class="btn btn-primary" id="nextStep">Suivant</button>
                    <button type="button" class="btn btn-success" id="completeSetup" style="display: none;">Terminer</button>
                </div>
            </div>
        `;
    }

    /**
     * Retourne le libellé d'une étape
     */
    getStepLabel(step) {
        const labels = {
            'info': 'Informations',
            'axes': 'Axes',
            'hats': 'Hats/POV',
            'confirm': 'Confirmation',
            'xml': 'Intégration XML'
        };
        return labels[step] || step;
    }

    /**
     * Charge des données de dispositif existantes (pour édition)
     */
    loadDeviceData(deviceData) {
        this.currentDeviceData = deviceData;
        
        // Remplir les champs du formulaire
        setTimeout(() => {
            const nameInput = document.getElementById('deviceNameInput');
            const typeInput = document.getElementById('deviceTypeInput');
            const descInput = document.getElementById('deviceDescInput');

            if (nameInput) nameInput.value = deviceData.name || '';
            if (typeInput) typeInput.value = deviceData.deviceType || '';
            if (descInput) descInput.value = deviceData.description || '';

            // Charger la configuration des axes et hats
            this.userConfig.axesMapping = deviceData.axes || {};
            this.userConfig.hatsConfig = deviceData.hats || {};
        }, 100);
    }
}

// Rendre la classe disponible globalement
window.DeviceSetupUI = DeviceSetupUI;