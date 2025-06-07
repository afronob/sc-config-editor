// Module utilitaire pour formater les noms d'actions avec traductions et préfixes
export class ActionFormatter {
    
    /**
     * Formate un nom d'action avec traduction française et préfixes de mode
     * @param {string} actionName - Nom technique de l'action (ex: "v_yaw")
     * @param {string} opts - Options du binding (ex: "activationmode")
     * @param {string} value - Valeur du binding (ex: "hold", "double_tap")
     * @returns {string} - Nom formaté avec préfixe et traduction
     */
    static formatActionName(actionName, opts = '', value = '') {
        // Obtenir le nom français si disponible
        const frenchName = (window.actionNamesJs && window.actionNamesJs[actionName]) || actionName;
        
        // Ajouter le préfixe selon le mode, en passant le nom de l'action
        const prefix = this.getActionPrefix(opts, value, actionName);
        
        return prefix + frenchName;
    }
    
    /**
     * Détermine le préfixe à ajouter selon les options et valeurs, ou le nom de l'action
     * @param {string} opts - Options du binding
     * @param {string} value - Valeur du binding
     * @param {string} actionName - Nom de l'action (optionnel)
     * @returns {string} - Préfixe à ajouter
     */
    static getActionPrefix(opts = '', value = '', actionName = '') {
        const optsLower = opts.toLowerCase();
        const valueLower = value.toLowerCase();
        
        // Mode Double Tap
        if ((optsLower === 'activationmode' && valueLower === 'double_tap') ||
            (optsLower === 'multitap' && value === '2')) {
            return '[DT] ';
        }
        
        // Mode Hold - vérifier "hold" dans le nom de l'action ET le libellé français
        if (actionName) {
            // Vérifier dans le nom de l'action (clé)
            const hasHoldInKey = actionName.toLowerCase().includes('hold');
            
            // Vérifier dans le libellé français (depuis le CSV)
            let hasHoldInLabel = false;
            if (typeof window !== 'undefined' && window.actionNamesJs && window.actionNamesJs[actionName]) {
                const frenchLabel = window.actionNamesJs[actionName];
                hasHoldInLabel = frenchLabel.toLowerCase().includes('hold');
            }
            
            // Si "hold" est présent dans la clé OU dans le libellé français
            if (hasHoldInKey || hasHoldInLabel) {
                return '[H] ';
            }
        }
        
        return '';
    }
    
    /**
     * Formate un nom d'action basé sur le mode spécifié directement
     * @param {string} actionName - Nom technique de l'action
     * @param {string} mode - Mode direct ('hold', 'double_tap', '')
     * @returns {string} - Nom formaté avec préfixe et traduction
     */
    static formatActionNameByMode(actionName, mode = '') {
        // Obtenir le nom français si disponible
        const frenchName = (window.actionNamesJs && window.actionNamesJs[actionName]) || actionName;
        
        // Ajouter le préfixe selon le mode ou la détection de "hold"
        let prefix = '';
        if (mode === 'double_tap') {
            prefix = '[DT] ';
        } else if (mode === 'hold') {
            // Mode explicite hold
            prefix = '[H] ';
        } else if (actionName) {
            // Vérifier "hold" dans le nom de l'action ET le libellé français
            const hasHoldInKey = actionName.toLowerCase().includes('hold');
            
            let hasHoldInLabel = false;
            if (typeof window !== 'undefined' && window.actionNamesJs && window.actionNamesJs[actionName]) {
                const frenchLabel = window.actionNamesJs[actionName];
                hasHoldInLabel = frenchLabel.toLowerCase().includes('hold');
            }
            
            if (hasHoldInKey || hasHoldInLabel) {
                prefix = '[H] ';
            }
        }
        
        return prefix + frenchName;
    }
}
