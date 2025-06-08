// Module d'initialisation pour l'éditeur de bindings
import { SCConfigEditor } from './scConfigEditor.js';
import { BindingEditorIntegration } from './modules/bindingEditorIntegration.js';

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
    
    // Initialiser l'intégration pour la gestion des dispositifs
    if (!window.bindingEditorIntegration) {
        window.bindingEditorIntegration = new BindingEditorIntegration();
        
        // Essayer d'initialiser immédiatement
        window.bindingEditorIntegration.initialize();
        
        // Si ça n'a pas marché (pas encore de bindings-table), réessayer périodiquement
        if (!window.bindingEditorIntegration.isInitialized) {
            console.log('🔄 BindingEditorIntegration: Initialisation différée...');
            
            const retryInit = () => {
                const hasTable = document.getElementById('bindings-table');
                console.log(`🔍 Retry ${attempts + 1}/${maxAttempts}: table=${!!hasTable}, initialized=${window.bindingEditorIntegration.isInitialized}`);
                
                if (!window.bindingEditorIntegration.isInitialized && hasTable) {
                    console.log('🎯 BindingEditorIntegration: Tableau détecté, nouvelle tentative...');
                    window.bindingEditorIntegration.initialize();
                    
                    // Vérifier après 200ms si l'injection a réussi
                    setTimeout(() => {
                        const section = document.querySelector('.device-management-section');
                        if (section) {
                            console.log('✅ Section Gestion des dispositifs visible!');
                        } else {
                            console.warn('⚠️ Section non visible après initialisation');
                        }
                    }, 200);
                }
            };
            
            // Réessayer toutes les 300ms pendant 15 secondes max (plus agressif)
            let attempts = 0;
            const maxAttempts = 50;
            const interval = setInterval(() => {
                attempts++;
                retryInit();
                
                if (window.bindingEditorIntegration.isInitialized || attempts >= maxAttempts) {
                    clearInterval(interval);
                    if (attempts >= maxAttempts) {
                        console.warn('⚠️ BindingEditorIntegration: Timeout après', attempts, 'tentatives');
                        
                        // Debug final si échec
                        console.log('🔍 Debug final - DOM actuel:');
                        console.log('- bindings-table:', !!document.getElementById('bindings-table'));
                        console.log('- filter-nonempty:', !!document.getElementById('filter-nonempty'));
                        console.log('- device-management-section:', !!document.querySelector('.device-management-section'));
                        console.log('- Body innerHTML (100 chars):', document.body.innerHTML.substring(0, 100));
                    } else {
                        console.log(`✅ BindingEditorIntegration initialisé après ${attempts} tentative(s)`);
                    }
                }
            }, 300);
        }
        
        console.log('✅ BindingEditorIntegration configuré');
    }
    
    // Exposer globalement pour debugging
    window.scConfigEditor = editorInstance;
    
    console.log('✅ SCConfigEditor initialisé avec détection automatique');
    
    return editorInstance;
}

// Fonction pour obtenir l'instance actuelle
export function getInstance() {
    return editorInstance;
}
