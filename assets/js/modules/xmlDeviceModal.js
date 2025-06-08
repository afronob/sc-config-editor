/**
 * Modal pour l'ajout de dispositifs au XML depuis le formulaire de bindings
 * Étape 2 : Intégration des dispositifs dans le XML existant
 */

export class XMLDeviceModal {
    constructor(xmlDeviceInstancer) {
        this.xmlInstancer = xmlDeviceInstancer;
        this.availableDevices = [];
        this.currentXMLContent = null;
        this.modalElement = null;
        this.loadAvailableDevices();
    }

    /**
     * Initialise la modal et l'ajoute à la page
     */
    initialize() {
        this.createModalHTML();
        this.bindEvents();
        this.addModalTriggerButton();
    }

    /**
     * Crée le HTML de la modal
     */
    createModalHTML() {
        // Supprimer la modal existante si elle existe
        const existingModal = document.getElementById('xml-device-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalHTML = `
            <div id="xml-device-modal" class="xml-modal" style="display: none;">
                <div class="xml-modal-content">
                    <div class="xml-modal-header">
                        <h5>Ajouter un dispositif au XML</h5>
                        <button type="button" class="xml-close" id="close-xml-modal">&times;</button>
                    </div>
                    <div class="xml-modal-body">
                        <div id="xml-modal-step1" class="xml-modal-step">
                            <h6>Sélectionner un dispositif</h6>
                            <div id="available-devices-list">
                                ${this.generateAvailableDevicesList()}
                            </div>
                            <div class="xml-modal-actions">
                                <button type="button" class="btn btn-secondary" id="cancel-xml-modal">Annuler</button>
                                <button type="button" class="btn btn-primary" id="next-to-preview" disabled>Continuer</button>
                            </div>
                        </div>
                        
                        <div id="xml-modal-step2" class="xml-modal-step" style="display: none;">
                            <h6>Aperçu de l'intégration XML</h6>
                            <div id="xml-integration-info"></div>
                            <div id="xml-preview-container">
                                <h6>Modifications XML à apporter :</h6>
                                <pre id="xml-preview-content"></pre>
                            </div>
                            <div class="xml-modal-actions">
                                <button type="button" class="btn btn-secondary" id="back-to-selection">Retour</button>
                                <button type="button" class="btn btn-success" id="download-modified-xml">
                                    <i class="fas fa-download"></i> Télécharger XML modifié
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modalElement = document.getElementById('xml-device-modal');
    }

    /**
     * Ajoute le bouton déclencheur de la modal près du tableau des bindings
     */
    addModalTriggerButton() {
        // Chercher le formulaire de bindings
        const bindingsTable = document.getElementById('bindings-table');
        if (!bindingsTable) {
            console.error('Table des bindings non trouvée');
            return;
        }

        // Chercher le bouton de soumission existant
        const submitButton = bindingsTable.closest('form')?.querySelector('button[type="submit"]');
        if (!submitButton) {
            console.error('Bouton de soumission non trouvé');
            return;
        }

        // Créer un conteneur pour les boutons
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'xml-actions-container';
        buttonContainer.style.cssText = 'margin: 15px 0; display: flex; gap: 10px; align-items: center;';

        // Créer le bouton d'ajout de dispositif
        const addDeviceButton = document.createElement('button');
        addDeviceButton.type = 'button';
        addDeviceButton.className = 'btn btn-info';
        addDeviceButton.id = 'add-device-to-xml';
        addDeviceButton.innerHTML = '<i class="fas fa-plus"></i> Ajouter un dispositif au XML';

        // Insérer le conteneur avant le bouton de soumission
        submitButton.parentNode.insertBefore(buttonContainer, submitButton);
        buttonContainer.appendChild(addDeviceButton);
        buttonContainer.appendChild(submitButton);

        // Bind l'événement
        addDeviceButton.addEventListener('click', () => this.openModal());
    }

    /**
     * Génère la liste des dispositifs disponibles
     */
    generateAvailableDevicesList() {
        if (this.availableDevices.length === 0) {
            return `
                <div class="no-devices-available">
                    <p>Aucun dispositif configuré disponible.</p>
                    <p>Vous devez d'abord créer des configurations de dispositifs dans la section de gestion des dispositifs.</p>
                    <button type="button" class="btn btn-primary" id="goto-device-manager">
                        Aller à la gestion des dispositifs
                    </button>
                </div>
            `;
        }

        let html = '<div class="devices-selection-grid">';
        this.availableDevices.forEach((device, index) => {
            html += `
                <div class="device-selection-card" data-device-index="${index}">
                    <div class="device-selection-info">
                        <h6>${this.escapeHtml(device.name)}</h6>
                        <p class="device-type">${this.escapeHtml(device.deviceType || 'Non spécifié')}</p>
                        <div class="device-stats">
                            <span class="stat">Axes: ${Object.keys(device.axes || {}).length}</span>
                            <span class="stat">Boutons: ${Object.keys(device.buttons || {}).length}</span>
                            <span class="stat">Chapeaux: ${Object.keys(device.hats || {}).length}</span>
                        </div>
                    </div>
                    <div class="device-selection-radio">
                        <input type="radio" name="selected-device" value="${index}" id="device-${index}">
                        <label for="device-${index}">Sélectionner</label>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    /**
     * Bind les événements de la modal
     */
    bindEvents() {
        if (!this.modalElement) return;

        // Fermeture de la modal
        const closeButton = this.modalElement.querySelector('#close-xml-modal');
        const cancelButton = this.modalElement.querySelector('#cancel-xml-modal');
        
        if (closeButton) {
            closeButton.addEventListener('click', () => this.closeModal());
        }
        if (cancelButton) {
            cancelButton.addEventListener('click', () => this.closeModal());
        }

        // Fermeture au clic sur l'overlay
        this.modalElement.addEventListener('click', (e) => {
            if (e.target === this.modalElement) {
                this.closeModal();
            }
        });

        // Navigation entre les étapes
        const nextButton = this.modalElement.querySelector('#next-to-preview');
        const backButton = this.modalElement.querySelector('#back-to-selection');
        
        if (nextButton) {
            nextButton.addEventListener('click', () => this.goToPreviewStep());
        }
        if (backButton) {
            backButton.addEventListener('click', () => this.goToSelectionStep());
        }

        // Sélection de dispositif
        this.modalElement.addEventListener('change', (e) => {
            if (e.target.name === 'selected-device') {
                const nextBtn = this.modalElement.querySelector('#next-to-preview');
                if (nextBtn) {
                    nextBtn.disabled = false;
                }
            }
        });

        // Téléchargement du XML modifié
        const downloadButton = this.modalElement.querySelector('#download-modified-xml');
        if (downloadButton) {
            downloadButton.addEventListener('click', () => this.downloadModifiedXML());
        }

        // Lien vers la gestion des dispositifs
        this.modalElement.addEventListener('click', (e) => {
            if (e.target.id === 'goto-device-manager') {
                // Ici, vous pourriez rediriger vers une page de gestion des dispositifs
                // ou ouvrir un onglet/fenêtre séparée
                alert('Fonctionnalité de redirection vers la gestion des dispositifs à implémenter');
            }
        });
    }

    /**
     * Ouvre la modal
     */
    openModal() {
        // Récupérer le contenu XML actuel
        this.currentXMLContent = this.getCurrentXMLContent();
        
        if (!this.currentXMLContent) {
            alert('Impossible de récupérer le contenu XML actuel');
            return;
        }

        // Initialiser l'instancier XML
        try {
            this.xmlInstancer.initialize(this.currentXMLContent);
        } catch (error) {
            console.error('Erreur lors de l\'initialisation de l\'instancier XML:', error);
            alert('Erreur lors de l\'analyse du XML actuel');
            return;
        }

        // Réinitialiser la modal à l'étape 1
        this.goToSelectionStep();
        
        // Afficher la modal
        this.modalElement.style.display = 'block';
    }

    /**
     * Ferme la modal
     */
    closeModal() {
        if (this.modalElement) {
            this.modalElement.style.display = 'none';
        }
    }

    /**
     * Passe à l'étape de prévisualisation
     */
    goToPreviewStep() {
        const selectedDevice = this.getSelectedDevice();
        if (!selectedDevice) {
            alert('Veuillez sélectionner un dispositif');
            return;
        }

        try {
            // Générer les informations XML pour le dispositif
            const deviceXMLInfo = this.xmlInstancer.generateDeviceXMLInfo(selectedDevice);
            
            // Générer le XML modifié
            const modifiedXML = this.xmlInstancer.generateModifiedXML(selectedDevice);
            
            // Afficher les informations dans l'étape 2
            this.displayXMLPreview(selectedDevice, deviceXMLInfo, modifiedXML);
            
            // Changer d'étape
            this.modalElement.querySelector('#xml-modal-step1').style.display = 'none';
            this.modalElement.querySelector('#xml-modal-step2').style.display = 'block';
            
        } catch (error) {
            console.error('Erreur lors de la génération du XML:', error);
            alert('Erreur lors de la génération du XML modifié: ' + error.message);
        }
    }

    /**
     * Revient à l'étape de sélection
     */
    goToSelectionStep() {
        this.modalElement.querySelector('#xml-modal-step1').style.display = 'block';
        this.modalElement.querySelector('#xml-modal-step2').style.display = 'none';
    }

    /**
     * Affiche l'aperçu XML
     */
    displayXMLPreview(device, deviceXMLInfo, modifiedXML) {
        const infoContainer = this.modalElement.querySelector('#xml-integration-info');
        const previewContainer = this.modalElement.querySelector('#xml-preview-content');

        // Informations sur l'intégration
        infoContainer.innerHTML = `
            <div class="integration-info">
                <h6>Dispositif sélectionné : ${this.escapeHtml(device.name)}</h6>
                <p><strong>Instance XML assignée :</strong> js${deviceXMLInfo.instance}</p>
                <p><strong>GUID généré :</strong> ${deviceXMLInfo.guid}</p>
                <p><strong>Modifications :</strong></p>
                <ul>
                    <li>Ajout de la déclaration du dispositif dans la section &lt;devices&gt;</li>
                    <li>Ajout des options dans les sections &lt;options&gt;</li>
                </ul>
            </div>
        `;

        // Aperçu du XML (afficher seulement les parties modifiées)
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(modifiedXML, 'text/xml');
        
        // Extraire les sections modifiées pour l'aperçu
        const deviceDeclaration = xmlDoc.querySelector(`joystick[instance="${deviceXMLInfo.instance}"]`);
        const optionsSections = Array.from(xmlDoc.querySelectorAll('options')).filter(section => 
            section.querySelector(`option[js="${deviceXMLInfo.instance}"]`)
        );

        let previewContent = '<!-- Déclaration du dispositif -->\n';
        if (deviceDeclaration) {
            previewContent += this.formatXMLForDisplay(deviceDeclaration.outerHTML) + '\n\n';
        }

        previewContent += '<!-- Sections d\'options modifiées -->\n';
        optionsSections.forEach(section => {
            previewContent += this.formatXMLForDisplay(section.outerHTML) + '\n\n';
        });

        previewContainer.textContent = previewContent;
    }

    /**
     * Télécharge le XML modifié
     */
    downloadModifiedXML() {
        const selectedDevice = this.getSelectedDevice();
        if (!selectedDevice) return;

        try {
            const modifiedXML = this.xmlInstancer.generateModifiedXML(selectedDevice);
            const filename = this.xmlInstancer.generateModifiedFilename();
            
            // Créer le blob et télécharger
            const blob = new Blob([modifiedXML], { type: 'application/xml' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();
            
            URL.revokeObjectURL(url);
            
            // Fermer la modal après téléchargement
            this.closeModal();
            
            // Afficher un message de succès
            this.showSuccessMessage(`XML modifié téléchargé : ${filename}`);
            
        } catch (error) {
            console.error('Erreur lors du téléchargement:', error);
            alert('Erreur lors du téléchargement du XML modifié');
        }
    }

    /**
     * Récupère le dispositif sélectionné
     */
    getSelectedDevice() {
        const selectedRadio = this.modalElement.querySelector('input[name="selected-device"]:checked');
        if (!selectedRadio) return null;
        
        const deviceIndex = parseInt(selectedRadio.value);
        return this.availableDevices[deviceIndex];
    }

    /**
     * Récupère le contenu XML actuel depuis le formulaire
     */
    getCurrentXMLContent() {
        const xmlDataInput = document.querySelector('input[name="xmldata"]');
        if (!xmlDataInput) {
            console.error('Champ xmldata non trouvé');
            return null;
        }
        return xmlDataInput.value;
    }

    /**
     * Charge les dispositifs disponibles depuis le localStorage
     */
    loadAvailableDevices() {
        try {
            const stored = localStorage.getItem('sc_devices');
            if (stored) {
                const devicesMap = JSON.parse(stored);
                this.availableDevices = Object.values(devicesMap);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des dispositifs:', error);
        }
    }

    /**
     * Formate le XML pour l'affichage
     */
    formatXMLForDisplay(xmlString) {
        // Simple formatage pour l'affichage
        return xmlString
            .replace(/></g, '>\n<')
            .replace(/^\s*\n/gm, '')
            .trim();
    }

    /**
     * Affiche un message de succès
     */
    showSuccessMessage(message) {
        // Créer un message de succès temporaire
        const successDiv = document.createElement('div');
        successDiv.className = 'xml-success-message';
        successDiv.textContent = message;
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 2000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(successDiv);
        
        // Supprimer le message après 3 secondes
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 3000);
    }

    /**
     * Échappe le HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// CSS pour la modal XML
export const xmlModalCSS = `
.xml-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1500;
    display: flex;
    align-items: center;
    justify-content: center;
}

.xml-modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.xml-modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.xml-modal-header h5 {
    margin: 0;
    color: #495057;
}

.xml-close {
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

.xml-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.xml-modal-step h6 {
    margin-bottom: 15px;
    color: #495057;
}

.devices-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.device-selection-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
    transition: all 0.2s;
    cursor: pointer;
}

.device-selection-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.device-selection-card input[type="radio"]:checked + label,
.device-selection-card:has(input[type="radio"]:checked) {
    border-color: #007bff;
    background: #e7f3ff;
}

.device-selection-info h6 {
    margin: 0 0 5px 0;
    color: #495057;
}

.device-type {
    color: #6c757d;
    font-style: italic;
    margin: 5px 0;
    font-size: 0.9em;
}

.device-stats {
    margin: 10px 0;
}

.device-stats .stat {
    display: inline-block;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8em;
    margin-right: 5px;
}

.device-selection-radio {
    margin-top: 10px;
    text-align: right;
}

.xml-modal-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.no-devices-available {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.integration-info {
    background: #e7f3ff;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}

.integration-info h6 {
    margin-top: 0;
    color: #004085;
}

.integration-info ul {
    margin-bottom: 0;
}

#xml-preview-container {
    margin-top: 20px;
}

#xml-preview-content {
    background: #f8f9fa;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    max-height: 300px;
    overflow-y: auto;
    white-space: pre-wrap;
    color: #495057;
}

.xml-actions-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9em;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-info { background: #17a2b8; color: white; }
.btn-success { background: #28a745; color: white; }

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.fas {
    margin-right: 5px;
}
`;
