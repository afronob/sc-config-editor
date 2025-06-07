// Module d'initialisation pour l'éditeur de bindings
import { SCConfigEditor } from './scConfigEditor.js';

// Variable globale pour l'instance principale
let editorInstance = null;

// Fonction d'initialisation exportée
export function initialize(config = {}) {
    console.log('🚀 Initialisation bindingEditor avec config:', config);
    
    // Exposer les données globalement pour compatibility
    if (config.devicesData) {
        window.devicesDataJs = config.devicesData;
        console.log('✅ devicesDataJs chargé:', config.devicesData.length, 'devices');
    } else {
        console.warn('⚠️ Aucune donnée devicesData fournie');
    }
    
    // Exposer les noms d'actions pour les modales
    if (config.actionNames) {
        window.actionNamesJs = config.actionNames;
    }
    
    // Créer l'instance principale avec forçage de la détection auto
    const editorConfig = {
        ...config,
        useSimplifiedAnchoring: true,
        enableAutoDetection: true
    };
    
    editorInstance = new SCConfigEditor(editorConfig);
    
    // Exposer globalement pour debugging
    window.scConfigEditor = editorInstance;
    
    console.log('✅ SCConfigEditor initialisé avec détection automatique');
    
    return editorInstance;
}

// Fonction pour obtenir l'instance actuelle
export function getInstance() {
    return editorInstance;
}
