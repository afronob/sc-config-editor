<?php
/**
 * Configuration Redis pour SC Config Editor
 */

return [
    'redis' => [
        'enabled' => true,
        'host' => getenv('REDIS_HOST') ?: 'localhost',
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
        'database' => (int)(getenv('REDIS_DB') ?: 0),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'prefix' => 'sc_config:',
        
        // Options de connexion
        'timeout' => 5.0,
        'read_timeout' => 2.0,
        'persistent' => false,
        
        // Configuration des TTL par défaut
        'ttl' => [
            'sessions' => 7200,      // 2 heures
            'devices' => 86400,      // 24 heures
            'temp_data' => 1800,     // 30 minutes
            'xml_cache' => 86400,    // 24 heures
            'detection' => 3600      // 1 heure
        ]
    ],
    
    // Configuration client (côté JavaScript)
    'client' => [
        'redis' => [
            'enabled' => true,
            'host' => 'localhost',
            'port' => 6379,
            'database' => 1, // Base séparée pour les données client
            'password' => null,
            'prefix' => 'sc_client:'
        ]
    ]
];
