/**
 * Module d'intégration pour la gestion des dispositifs dans l'éditeur de bindings
 * Orchestre les 2 étapes : DeviceManager + XMLDeviceModal
 */

import { DeviceManager } from './deviceManager.js';
import { XMLDeviceModal } from './xmlDeviceModal.js';
import { XMLDeviceInstancer } from './xmlDeviceInstancer.js';
import logger from './globalLogger.js';

export class BindingEditorIntegration {
    constructor() {
        this.deviceManager = null;
        this.xmlDeviceModal = null;
        this.xmlInstancer = new XMLDeviceInstancer();
        this.isInitialized = false;
        this.mutationObserver = null;
        
        // Démarrer l'observation des mutations du DOM
        this.startDOMObserver();
    }

    /**
     * Initialise l'intégration dans l'éditeur de bindings
     */
    initialize() {
        if (this.isInitialized) {
            console.log('BindingEditorIntegration: Déjà initialisé, abandon');
            return;
        }

        try {
            logger.info('Début initialisation...', 'BindingEditorIntegration');
            
            // Vérifier qu'on est dans le bon contexte (page d'édition)
            const hasBindingsContext = this.isInBindingEditor();
            logger.info(`Context bindings: ${hasBindingsContext}`, 'BindingEditorIntegration');
            
            if (!hasBindingsContext) {
                logger.info('Non applicable dans ce contexte', 'BindingEditorIntegration');
                return;
            }

            // Initialiser la modal XML pour l'ajout de dispositifs
            logger.info('Initialisation modal XML...', 'BindingEditorIntegration');
            this.initializeXMLModal();

            // Ajouter le CSS nécessaire
            logger.info('Injection CSS...', 'BindingEditorIntegration');
            this.injectCSS();

            // Ajouter les liens de gestion des dispositifs
            logger.info('Ajout liens gestion dispositifs...', 'BindingEditorIntegration');
            this.addDeviceManagementLinks();

            this.isInitialized = true;
            logger.success('✅ Initialisé avec succès', 'BindingEditorIntegration');

            // Arrêter l'observation une fois l'intégration réussie
            if (this.mutationObserver) {
                this.mutationObserver.disconnect();
                this.mutationObserver = null;
                logger.info('MutationObserver arrêté (intégration réussie)', 'BindingEditorIntegration');
            }

        } catch (error) {
            logger.error(`Erreur lors de l'initialisation: ${error.message}`, 'BindingEditorIntegration');
            logger.error(`Stack trace: ${error.stack}`, 'BindingEditorIntegration');
        }
    }

    /**
     * Vérifie si on est dans l'éditeur de bindings
     */
    isInBindingEditor() {
        const hasTable = document.getElementById('bindings-table') !== null;
        logger.debug(`Vérification contexte - bindings-table trouvé: ${hasTable}`, 'BindingEditorIntegration');
        
        if (hasTable) {
            // Afficher des infos sur la structure trouvée
            const table = document.getElementById('bindings-table');
            logger.debug(`Table trouvée: ${table}`, 'BindingEditorIntegration');
            logger.debug(`Lignes dans la table: ${table.rows.length}`, 'BindingEditorIntegration');
        }
        
        return hasTable;
    }

    /**
     * Initialise la modal XML
     */
    initializeXMLModal() {
        this.xmlDeviceModal = new XMLDeviceModal(this.xmlInstancer);
        this.xmlDeviceModal.initialize();
    }

    /**
     * Ajoute les liens de gestion des dispositifs
     */
    addDeviceManagementLinks() {
        logger.info('Recherche emplacement pour injection...', 'BindingEditorIntegration');
        
        // Éviter la double injection
        if (document.querySelector('.device-management-section')) {
            logger.info('Section déjà présente, abandon', 'BindingEditorIntegration');
            return;
        }
        
        let targetElement = null;
        let insertionMethod = null;
        
        // Stratégie 1: Chercher l'élément filter-nonempty et son parent
        const filterElement = document.getElementById('filter-nonempty');
        if (filterElement && filterElement.parentElement) {
            targetElement = filterElement.parentElement;
            insertionMethod = 'after-filters';
            logger.info('✅ Méthode 1: Trouvé filter-nonempty parent', 'BindingEditorIntegration');
        }
        
        // Stratégie 2: Chercher le texte "Filtres" dans le DOM (méthode plus simple)
        if (!targetElement) {
            const allElements = document.querySelectorAll('*');
            for (let element of allElements) {
                // Vérifier si l'élément contient directement le texte "Filtres"
                if (element.textContent && element.textContent.includes('Filtres') && 
                    element.children.length > 0) { // S'assurer que c'est un conteneur
                    targetElement = element;
                    insertionMethod = 'after-filters-text';
                    logger.info('✅ Méthode 2: Trouvé élément contenant "Filtres"', 'BindingEditorIntegration');
                    break;
                }
            }
        }
        
        // Stratégie 3: Insérer avant le tableau bindings
        if (!targetElement) {
            const bindingsTable = document.getElementById('bindings-table');
            if (bindingsTable) {
                targetElement = bindingsTable;
                insertionMethod = 'before-table';
                logger.info('✅ Méthode 3: Insertion avant le tableau', 'BindingEditorIntegration');
            }
        }
        
        // Stratégie 4: Insérer après le formulaire ou dans le body
        if (!targetElement) {
            const form = document.querySelector('form');
            if (form) {
                targetElement = form;
                insertionMethod = 'before-form';
                logger.info('✅ Méthode 4: Insertion avant le formulaire', 'BindingEditorIntegration');
            } else {
                // Stratégie 5: Fallback absolu - créer un conteneur après le body
                const bodyFirstChild = document.body.firstElementChild;
                if (bodyFirstChild) {
                    targetElement = bodyFirstChild;
                    insertionMethod = 'before-first-child';
                    logger.info('⚠️ Méthode 5: Fallback avant premier élément du body', 'BindingEditorIntegration');
                } else {
                    targetElement = document.body;
                    insertionMethod = 'in-body';
                    logger.info('⚠️ Méthode 6: Fallback ultime dans body', 'BindingEditorIntegration');
                }
            }
        }
        
        if (!targetElement) {
            logger.error('❌ Impossible de trouver un emplacement pour injection', 'BindingEditorIntegration');
            this.debugDOMStructure();
            return;
        }

        logger.info(`Emplacement trouvé: ${targetElement.tagName}${targetElement.id ? '#' + targetElement.id : ''}${targetElement.className ? '.' + targetElement.className : ''} (${insertionMethod})`, 'BindingEditorIntegration');

        // Créer la section de gestion des dispositifs
        const deviceManagementSection = document.createElement('div');
        deviceManagementSection.className = 'device-management-section';
        deviceManagementSection.innerHTML = `
            <div class="device-management-header">
                <h4>🎮 Gestion des Dispositifs</h4>
                <div class="device-management-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="open-device-manager">
                        <i class="fas fa-cog"></i> Gérer les dispositifs
                    </button>
                    <button type="button" class="btn btn-sm btn-info" id="import-device-json">
                        <i class="fas fa-upload"></i> Importer JSON
                    </button>
                    <span class="device-count" id="device-count">0 dispositifs configurés</span>
                </div>
            </div>
        `;

        logger.info('Section de gestion créée', 'BindingEditorIntegration');

        // Insérer selon la méthode déterminée
        switch (insertionMethod) {
            case 'after-filters':
            case 'after-filters-text':
                targetElement.parentNode.insertBefore(deviceManagementSection, targetElement.nextSibling);
                break;
            case 'before-table':
            case 'before-form':
            case 'before-first-child':
                targetElement.parentNode.insertBefore(deviceManagementSection, targetElement);
                break;
            case 'in-body':
                targetElement.appendChild(deviceManagementSection);
                break;
            default:
                // Fallback par défaut - ajouter au début du body
                document.body.insertBefore(deviceManagementSection, document.body.firstChild);
                logger.info('⚠️ Utilisation fallback par défaut', 'BindingEditorIntegration');
        }

        logger.success('✅ Section insérée dans le DOM', 'BindingEditorIntegration');

        // Bind les événements
        this.bindDeviceManagementEvents(deviceManagementSection);

        // Mettre à jour le compteur de dispositifs
        this.updateDeviceCount();

        logger.success('✅ Liens de gestion ajoutés avec succès', 'BindingEditorIntegration');
    }

    /**
     * Debug la structure DOM pour diagnostiquer les problèmes d'injection
     */
    debugDOMStructure() {
        logger.info('=== DEBUG STRUCTURE DOM ===', 'BindingEditorIntegration');
        
        // Éléments avec ID
        const elementsWithId = document.querySelectorAll('[id]');
        logger.info(`Éléments avec ID: ${Array.from(elementsWithId).map(el => el.id).join(', ')}`, 'BindingEditorIntegration');
        
        // Tables
        const tables = document.querySelectorAll('table');
        logger.info(`Tables trouvées: ${tables.length}`, 'BindingEditorIntegration');
        
        // Formulaires
        const forms = document.querySelectorAll('form');
        logger.info(`Formulaires trouvés: ${forms.length}`, 'BindingEditorIntegration');
        
        // Divs contenant "Filtres"
        const divs = document.querySelectorAll('div');
        let filtersCount = 0;
        divs.forEach(div => {
            if (div.textContent && div.textContent.includes('Filtres')) {
                filtersCount++;
                logger.info(`Div avec "Filtres": ${div.tagName}${div.id ? '#' + div.id : ''}${div.className ? '.' + div.className : ''}`, 'BindingEditorIntegration');
            }
        });
        logger.info(`Divs contenant "Filtres": ${filtersCount}`, 'BindingEditorIntegration');
        
        // Structure body
        logger.info(`Body innerHTML (200 premiers caractères): ${document.body.innerHTML.substring(0, 200)}...`, 'BindingEditorIntegration');
    }

    /**
     * Bind les événements de gestion des dispositifs
     */
    bindDeviceManagementEvents(container) {
        const openManagerBtn = container.querySelector('#open-device-manager');
        const importJsonBtn = container.querySelector('#import-device-json');

        if (openManagerBtn) {
            openManagerBtn.addEventListener('click', () => this.openDeviceManager());
        }

        if (importJsonBtn) {
            importJsonBtn.addEventListener('click', () => this.importDeviceJSON());
        }
    }

    /**
     * Ouvre le gestionnaire de dispositifs dans une nouvelle fenêtre/onglet
     */
    openDeviceManager() {
        // Créer une modal temporaire pour le gestionnaire de dispositifs
        const modalHTML = `
            <div id="device-manager-modal" class="device-modal" style="display: block;">
                <div class="device-modal-content">
                    <div class="device-modal-header">
                        <h5>Gestionnaire de Dispositifs</h5>
                        <button type="button" class="device-close" id="close-device-manager">&times;</button>
                    </div>
                    <div class="device-modal-body">
                        <div id="device-management-container"></div>
                    </div>
                </div>
            </div>
        `;

        // Supprimer une modal existante
        const existingModal = document.getElementById('device-manager-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Ajouter la nouvelle modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Initialiser le gestionnaire de dispositifs
        this.deviceManager = new DeviceManager();
        this.deviceManager.initializeDeviceManagement('device-management-container');

        // Bind la fermeture
        const closeBtn = document.getElementById('close-device-manager');
        const modal = document.getElementById('device-manager-modal');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeDeviceManager());
        }
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeDeviceManager();
                }
            });
        }

        // Mettre à jour le compteur quand la modal se ferme
        this.setupDeviceCountUpdater();
    }

    /**
     * Ferme le gestionnaire de dispositifs
     */
    closeDeviceManager() {
        const modal = document.getElementById('device-manager-modal');
        if (modal) {
            modal.remove();
        }
        this.updateDeviceCount();
    }

    /**
     * Importe un fichier JSON de dispositif
     */
    importDeviceJSON() {
        // Créer un input file temporaire
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.json';
        fileInput.style.display = 'none';

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    const jsonData = event.target.result;
                    
                    // Utiliser le DeviceManager pour importer
                    if (!this.deviceManager) {
                        this.deviceManager = new DeviceManager();
                    }
                    
                    this.deviceManager.importDevicesFromJSON(jsonData);
                    this.updateDeviceCount();
                    
                    // Afficher un message de succès
                    this.showMessage('Dispositif importé avec succès!', 'success');
                    
                } catch (error) {
                    console.error('Erreur lors de l\'importation:', error);
                    this.showMessage('Erreur lors de l\'importation du fichier JSON', 'error');
                }
            };
            reader.readAsText(file);
        });

        document.body.appendChild(fileInput);
        fileInput.click();
        document.body.removeChild(fileInput);
    }

    /**
     * Met à jour le compteur de dispositifs configurés
     */
    updateDeviceCount() {
        const countElement = document.getElementById('device-count');
        if (!countElement) return;

        try {
            const stored = localStorage.getItem('sc_devices');
            const devicesCount = stored ? Object.keys(JSON.parse(stored)).length : 0;
            countElement.textContent = `${devicesCount} dispositif${devicesCount !== 1 ? 's' : ''} configuré${devicesCount !== 1 ? 's' : ''}`;
        } catch (error) {
            countElement.textContent = '0 dispositifs configurés';
        }
    }

    /**
     * Configure un observateur pour mettre à jour le compteur
     */
    setupDeviceCountUpdater() {
        // Vérifier périodiquement les changements dans le localStorage
        const originalSetItem = localStorage.setItem;
        localStorage.setItem = function(key, value) {
            originalSetItem.apply(this, arguments);
            if (key === 'sc_devices') {
                setTimeout(() => {
                    const integration = window.bindingEditorIntegration;
                    if (integration) {
                        integration.updateDeviceCount();
                    }
                }, 100);
            }
        };
    }

    /**
     * Affiche un message temporaire à l'utilisateur
     */
    showMessage(message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `integration-message message-${type}`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 2000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            ${type === 'success' ? 'background: #28a745; color: white;' : ''}
            ${type === 'error' ? 'background: #dc3545; color: white;' : ''}
            ${type === 'info' ? 'background: #17a2b8; color: white;' : ''}
        `;

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 3000);
    }

    /**
     * Injecte le CSS nécessaire
     */
    injectCSS() {
        // Vérifier si le CSS n'est pas déjà injecté
        if (document.getElementById('binding-editor-integration-css')) {
            return;
        }

        const css = `
            .device-management-section {
                margin: 15px 0;
                padding: 15px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 5px;
            }

            .device-management-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }

            .device-management-header h4 {
                margin: 0;
                color: #495057;
                font-size: 1.1em;
            }

            .device-management-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .device-count {
                font-size: 0.9em;
                color: #6c757d;
                font-style: italic;
            }

            .device-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1600;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .device-modal-content {
                background: white;
                border-radius: 8px;
                width: 95%;
                max-width: 1200px;
                max-height: 90vh;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            .device-modal-header {
                padding: 20px;
                border-bottom: 1px solid #dee2e6;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f8f9fa;
            }

            .device-modal-header h5 {
                margin: 0;
                color: #495057;
            }

            .device-close {
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

            .device-modal-body {
                padding: 20px;
                overflow-y: auto;
                flex: 1;
            }

            .btn {
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.875em;
                text-decoration: none;
                display: inline-block;
                transition: all 0.2s;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8em;
            }

            .btn-primary { background: #007bff; color: white; }
            .btn-info { background: #17a2b8; color: white; }

            .btn:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }

            .fas {
                margin-right: 5px;
            }

            @media (max-width: 768px) {
                .device-management-header {
                    flex-direction: column;
                    align-items: stretch;
                }

                .device-management-actions {
                    justify-content: center;
                }

                .device-modal-content {
                    width: 98%;
                    margin: 10px;
                }
            }
        `;

        const style = document.createElement('style');
        style.id = 'binding-editor-integration-css';
        style.textContent = css;
        document.head.appendChild(style);
    }

    /**
     * Démarre l'observation des mutations DOM pour détecter l'apparition du tableau
     */
    startDOMObserver() {
        if (!window.MutationObserver) {
            console.warn('MutationObserver non supporté, fallback sur polling');
            return;
        }

        this.mutationObserver = new MutationObserver((mutations) => {
            // Vérifier si le tableau de bindings est apparu
            if (!this.isInitialized && this.isInBindingEditor()) {
                console.log('🎯 BindingEditorIntegration: Tableau détecté via MutationObserver');
                this.initialize();
            }
        });

        // Observer les changements dans le body
        this.mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });

        logger.info('MutationObserver démarré', 'BindingEditorIntegration');
    }

    /**
     * Nettoie les ressources utilisées
     */
    cleanup() {
        if (this.mutationObserver) {
            this.mutationObserver.disconnect();
            this.mutationObserver = null;
            console.log('🧹 BindingEditorIntegration: MutationObserver arrêté');
        }
    }
}

// Auto-initialisation si on est dans l'éditeur de bindings
document.addEventListener('DOMContentLoaded', () => {
    window.bindingEditorIntegration = new BindingEditorIntegration();
    window.bindingEditorIntegration.initialize();
});
