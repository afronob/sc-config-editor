// Module d'initialisation pour l'éditeur de bindings
import { SCConfigEditor } from './scConfigEditor.js';

// Variable globale pour l'instance principale
let editorInstance = null;

// Fonction d'initialisation exportée
export function initialize(config = {}) {
    // Exposer les données globalement pour compatibility
    if (config.devicesData) {
        window.devicesDataJs = config.devicesData;
    }
    
    // Créer l'instance principale
    editorInstance = new SCConfigEditor(config);
    
    // Exposer globalement pour debugging
    window.scConfigEditor = editorInstance;
    
    return editorInstance;
}

// Fonction pour obtenir l'instance actuelle
export function getInstance() {
    return editorInstance;
}
