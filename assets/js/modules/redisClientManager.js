/**
 * Gestionnaire Redis côté client pour remplacer localStorage
 * Fournit une API compatible avec localStorage mais utilise Redis via des endpoints
 */
export class RedisClientManager {
    constructor(config = {}) {
        this.config = {
            apiBase: '/api/redis',
            fallbackToLocalStorage: true,
            cacheTimeout: 5 * 60 * 1000, // 5 minutes en ms
            ...config
        };
        
        this.cache = new Map();
        this.cacheTimestamps = new Map();
        this.isOnline = navigator.onLine;
        
        // Écouter les changements de connectivité
        this.setupConnectivityListeners();
        
        // Vérifier la disponibilité de Redis
        this.checkRedisAvailability();
    }
    
    setupConnectivityListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.syncPendingChanges();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
        });
    }
    
    /**
     * Vérifie si Redis est disponible
     */
    async checkRedisAvailability() {
        try {
            const response = await fetch(`${this.config.apiBase}/status`);
            if (response.ok) {
                const data = await response.json();
                this.redisAvailable = data.connected === true;
                
                if (this.redisAvailable) {
                    console.log('✅ Redis disponible via API');
                } else {
                    console.warn('⚠️ Redis non disponible, fallback localStorage');
                }
            }
        } catch (error) {
            console.warn('⚠️ API Redis non accessible:', error.message);
            this.redisAvailable = false;
        }
        
        // Si Redis n'est pas disponible, utiliser localStorage
        if (!this.redisAvailable && !this.config.fallbackToLocalStorage) {
            throw new Error('Redis non disponible et fallback désactivé');
        }
    }
    
    /**
     * Génère une clé avec préfixe pour éviter les conflisions
     */
    prefixKey(key) {
        return `sc_client:${key}`;
    }
    
    /**
     * Vérifie si une valeur en cache est encore valide
     */
    isCacheValid(key) {
        const timestamp = this.cacheTimestamps.get(key);
        if (!timestamp) return false;
        
        return (Date.now() - timestamp) < this.config.cacheTimeout;
    }
    
    /**
     * Met à jour le cache local
     */
    updateCache(key, value) {
        this.cache.set(key, value);
        this.cacheTimestamps.set(key, Date.now());
    }
    
    /**
     * Sauvegarde une valeur (compatible localStorage)
     * @param {string} key - Clé de stockage
     * @param {string} value - Valeur à stocker (doit être une string)
     */
    async setItem(key, value) {
        const prefixedKey = this.prefixKey(key);
        
        // Mettre à jour le cache immédiatement
        this.updateCache(key, value);
        
        if (this.redisAvailable && this.isOnline) {
            try {
                const response = await fetch(`${this.config.apiBase}/set`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        key: prefixedKey,
                        value: value,
                        ttl: 86400 // 24 heures par défaut
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || 'Erreur serveur');
                }
                
                console.log(`✅ Données sauvegardées dans Redis: ${key}`);
                
            } catch (error) {
                console.warn(`⚠️ Erreur sauvegarde Redis pour ${key}:`, error.message);
                
                if (this.config.fallbackToLocalStorage) {
                    localStorage.setItem(prefixedKey, value);
                    console.log(`💾 Fallback localStorage pour: ${key}`);
                }
            }
        } else if (this.config.fallbackToLocalStorage) {
            localStorage.setItem(prefixedKey, value);
        }
    }
    
    /**
     * Récupère une valeur (compatible localStorage)
     * @param {string} key - Clé de stockage
     * @returns {string|null} - Valeur stockée ou null
     */
    async getItem(key) {
        const prefixedKey = this.prefixKey(key);
        
        // Vérifier le cache local d'abord
        if (this.isCacheValid(key)) {
            return this.cache.get(key);
        }
        
        if (this.redisAvailable && this.isOnline) {
            try {
                const response = await fetch(`${this.config.apiBase}/get?key=${encodeURIComponent(prefixedKey)}`);
                
                if (response.ok) {
                    const result = await response.json();
                    
                    if (result.success && result.value !== null) {
                        this.updateCache(key, result.value);
                        return result.value;
                    }
                }
                
            } catch (error) {
                console.warn(`⚠️ Erreur récupération Redis pour ${key}:`, error.message);
            }
        }
        
        // Fallback sur localStorage
        if (this.config.fallbackToLocalStorage) {
            const value = localStorage.getItem(prefixedKey);
            if (value !== null) {
                this.updateCache(key, value);
            }
            return value;
        }
        
        return null;
    }
    
    /**
     * Supprime une valeur (compatible localStorage)
     * @param {string} key - Clé à supprimer
     */
    async removeItem(key) {
        const prefixedKey = this.prefixKey(key);
        
        // Supprimer du cache
        this.cache.delete(key);
        this.cacheTimestamps.delete(key);
        
        if (this.redisAvailable && this.isOnline) {
            try {
                const response = await fetch(`${this.config.apiBase}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ key: prefixedKey })
                });
                
                if (response.ok) {
                    console.log(`🗑️ Données supprimées de Redis: ${key}`);
                }
                
            } catch (error) {
                console.warn(`⚠️ Erreur suppression Redis pour ${key}:`, error.message);
            }
        }
        
        // Supprimer aussi de localStorage
        if (this.config.fallbackToLocalStorage) {
            localStorage.removeItem(prefixedKey);
        }
    }
    
    /**
     * Vide tout le stockage pour ce préfixe
     */
    async clear() {
        // Vider le cache
        this.cache.clear();
        this.cacheTimestamps.clear();
        
        if (this.redisAvailable && this.isOnline) {
            try {
                const response = await fetch(`${this.config.apiBase}/clear`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ pattern: 'sc_client:*' })
                });
                
                if (response.ok) {
                    console.log('🧹 Cache Redis nettoyé');
                }
                
            } catch (error) {
                console.warn('⚠️ Erreur nettoyage Redis:', error.message);
            }
        }
        
        // Nettoyer localStorage aussi
        if (this.config.fallbackToLocalStorage) {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('sc_client:')) {
                    localStorage.removeItem(key);
                }
            });
        }
    }
    
    /**
     * Synchronise les changements en attente (quand connexion revient)
     */
    async syncPendingChanges() {
        if (!this.redisAvailable || !this.isOnline) return;
        
        console.log('🔄 Synchronisation des changements en attente...');
        
        // Synchroniser toutes les données du cache
        for (const [key, value] of this.cache.entries()) {
            try {
                await this.setItem(key, value);
            } catch (error) {
                console.warn(`⚠️ Erreur sync pour ${key}:`, error.message);
            }
        }
    }
    
    /**
     * Obtient des statistiques sur l'utilisation
     */
    async getStats() {
        if (this.redisAvailable && this.isOnline) {
            try {
                const response = await fetch(`${this.config.apiBase}/stats`);
                if (response.ok) {
                    return await response.json();
                }
            } catch (error) {
                console.warn('⚠️ Erreur récupération stats:', error.message);
            }
        }
        
        return {
            connected: false,
            cache_size: this.cache.size,
            fallback_active: !this.redisAvailable
        };
    }
    
    /**
     * Méthode pour compatibilité avec l'API localStorage existante
     */
    length() {
        return this.cache.size;
    }
    
    /**
     * Récupère une clé par index (pour compatibilité localStorage)
     */
    key(index) {
        const keys = Array.from(this.cache.keys());
        return keys[index] || null;
    }
}

/**
 * Factory pour créer une instance compatible avec l'API localStorage
 */
export function createRedisStorage(config = {}) {
    const manager = new RedisClientManager(config);
    
    // Retourner un objet avec l'API localStorage
    return {
        async setItem(key, value) {
            return await manager.setItem(key, value);
        },
        
        async getItem(key) {
            return await manager.getItem(key);
        },
        
        async removeItem(key) {
            return await manager.removeItem(key);
        },
        
        async clear() {
            return await manager.clear();
        },
        
        get length() {
            return manager.length();
        },
        
        key(index) {
            return manager.key(index);
        },
        
        // Méthodes supplémentaires spécifiques à Redis
        async getStats() {
            return await manager.getStats();
        },
        
        async sync() {
            return await manager.syncPendingChanges();
        }
    };
}
