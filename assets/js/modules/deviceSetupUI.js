// Interface utilisateur pour la configuration des nouveaux devices
export class DeviceSetupUI {
    constructor(autoDetection) {
        this.autoDetection = autoDetection;
        this.currentDeviceKey = null;
        this.setupSteps = ['info', 'axes', 'hats', 'confirm'];
        this.currentStep = 0;
        this.userConfig = {
            axesMapping: {},
            hatsConfig: {}
        };
        
        this.createUI();
        this.bindEvents();
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
        const deviceInfo = this.autoDetection.unknownDevices.get(deviceKey);
        
        if (!deviceInfo) {
            console.error('Device non trouvé pour la configuration:', deviceKey);
            return;
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
        const deviceInfo = this.autoDetection.unknownDevices.get(this.currentDeviceKey);
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
            
            if (this.currentStep === this.setupSteps.length - 1) {
                // Dernière étape - générer le résumé
                this.generateConfigSummary();
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
        const deviceInfo = this.autoDetection.unknownDevices.get(this.currentDeviceKey);
        
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

    async saveConfiguration() {
        try {
            document.getElementById('setupSaveBtn').disabled = true;
            document.getElementById('setupSaveBtn').textContent = 'Sauvegarde...';
            
            const result = await this.autoDetection.saveDeviceMapping(this.currentDeviceKey, this.userConfig);
            
            if (result.success) {
                alert(`Configuration sauvegardée avec succès!\nFichier: ${result.fileName}`);
                this.closeModal();
                
                // Rafraîchir les données globales si nécessaire
                if (window.scConfigEditor && window.scConfigEditor.loadDevicesData) {
                    window.scConfigEditor.loadDevicesData();
                }
            }
            
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
            alert(`Erreur lors de la sauvegarde: ${error.message}`);
        } finally {
            document.getElementById('setupSaveBtn').disabled = false;
            document.getElementById('setupSaveBtn').textContent = 'Sauvegarder';
        }
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
}

// Rendre la classe disponible globalement
window.DeviceSetupUI = DeviceSetupUI;