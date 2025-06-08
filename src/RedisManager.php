<?php

declare(strict_types=1);

namespace SCConfigEditor;

use Predis\Client;
use Predis\Connection\ConnectionException;

/**
 * Gestionnaire Redis pour l'éditeur de configuration Star Citizen
 * 
 * Structure des clés:
 * - sessions:stepbystep:{session_id} - Sessions step-by-step 
 * - devices:config:{device_id} - Configurations des dispositifs
 * - devices:temp:{hash} - Configurations temporaires
 * - xml:cache:{hash} - Cache des fichiers XML traités
 * - mappings:json:{device_type} - Mappings par type de dispositif
 * - detection:cache:{user_agent_hash} - Cache détection navigateur
 */
class RedisManager
{
    private Client $redis;
    private array $config;
    
    // TTL par défaut (en secondes)
    private const DEFAULT_TTL = 3600; // 1 heure
    private const SESSION_TTL = 7200; // 2 heures
    private const TEMP_TTL = 1800; // 30 minutes
    private const XML_CACHE_TTL = 86400; // 24 heures
    
    public function __construct(array $redisConfig = [])
    {
        $this->config = array_merge([
            'scheme' => 'tcp',
            'host' => 'localhost',
            'port' => 6379,
            'database' => 0,
            'password' => null,
            'prefix' => 'sc_config:'
        ], $redisConfig);
        
        $this->initializeConnection();
    }
    
    private function initializeConnection(): void
    {
        try {
            // Configuration de connexion pour Predis
            $connectionParams = [
                'scheme' => 'tcp',
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
            ];
            
            // Ajouter le mot de passe seulement s'il est défini
            if (!empty($this->config['password'])) {
                $connectionParams['password'] = $this->config['password'];
            }
            
            $this->redis = new Client($connectionParams, [
                'prefix' => $this->config['prefix']
            ]);
            
            // Test de la connexion
            $this->redis->ping();
            
        } catch (ConnectionException $e) {
            throw new \RuntimeException(
                "Impossible de se connecter à Redis: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Vérifie si Redis est disponible
     */
    public function isConnected(): bool
    {
        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    // === GESTION DES SESSIONS STEP-BY-STEP ===
    
    /**
     * Sauvegarde les données de session step-by-step
     */
    public function saveStepByStepSession(string $sessionId, array $data): bool
    {
        try {
            $key = "sessions:stepbystep:$sessionId";
            $serialized = json_encode($data, JSON_THROW_ON_ERROR);
            
            $this->redis->setex($key, self::SESSION_TTL, $serialized);
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur sauvegarde session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les données de session step-by-step
     */
    public function getStepByStepSession(string $sessionId): ?array
    {
        try {
            $key = "sessions:stepbystep:$sessionId";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération session: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Supprime une session step-by-step
     */
    public function deleteStepByStepSession(string $sessionId): bool
    {
        try {
            $key = "sessions:stepbystep:$sessionId";
            return $this->redis->del($key) > 0;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur suppression session: " . $e->getMessage());
            return false;
        }
    }
    
    // === GESTION DES DISPOSITIFS ===
    
    /**
     * Sauvegarde la configuration d'un dispositif
     */
    public function saveDeviceConfig(string $deviceId, array $config): bool
    {
        try {
            $key = "devices:config:$deviceId";
            $data = array_merge($config, [
                'id' => $deviceId,
                'lastModified' => date('c'),
                'version' => '1.0'
            ]);
            
            $serialized = json_encode($data, JSON_THROW_ON_ERROR);
            $this->redis->set($key, $serialized);
            
            // Ajouter à l'index des dispositifs
            $this->addToDeviceIndex($deviceId, $config);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur sauvegarde dispositif: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère la configuration d'un dispositif
     */
    public function getDeviceConfig(string $deviceId): ?array
    {
        try {
            $key = "devices:config:$deviceId";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération dispositif: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupère toutes les configurations de dispositifs
     */
    public function getAllDeviceConfigs(): array
    {
        try {
            $pattern = "devices:config:*";
            $keys = $this->redis->keys($pattern);
            
            if (empty($keys)) {
                return [];
            }
            
            $devices = [];
            $values = $this->redis->mget($keys);
            
            foreach ($keys as $index => $key) {
                if ($values[$index] !== null) {
                    $deviceId = str_replace($this->config['prefix'] . 'devices:config:', '', $key);
                    $devices[$deviceId] = json_decode($values[$index], true, 512, JSON_THROW_ON_ERROR);
                }
            }
            
            return $devices;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération tous dispositifs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Supprime un dispositif
     */
    public function deleteDeviceConfig(string $deviceId): bool
    {
        try {
            $key = "devices:config:$deviceId";
            $this->removeFromDeviceIndex($deviceId);
            
            return $this->redis->del($key) > 0;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur suppression dispositif: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sauvegarde temporaire de dispositif (avec TTL)
     */
    public function saveTempDeviceConfig(string $hash, array $config): bool
    {
        try {
            $key = "devices:temp:$hash";
            $serialized = json_encode($config, JSON_THROW_ON_ERROR);
            
            $this->redis->setex($key, self::TEMP_TTL, $serialized);
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur sauvegarde temp dispositif: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère une configuration temporaire
     */
    public function getTempDeviceConfig(string $hash): ?array
    {
        try {
            $key = "devices:temp:$hash";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération temp dispositif: " . $e->getMessage());
            return null;
        }
    }
    
    // === GESTION DU CACHE XML ===
    
    /**
     * Met en cache un fichier XML traité
     */
    public function cacheXMLContent(string $hash, string $content, array $metadata = []): bool
    {
        try {
            $key = "xml:cache:$hash";
            $data = [
                'content' => $content,
                'metadata' => $metadata,
                'cached_at' => date('c')
            ];
            
            $serialized = json_encode($data, JSON_THROW_ON_ERROR);
            $this->redis->setex($key, self::XML_CACHE_TTL, $serialized);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur cache XML: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un XML mis en cache
     */
    public function getCachedXML(string $hash): ?array
    {
        try {
            $key = "xml:cache:$hash";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération cache XML: " . $e->getMessage());
            return null;
        }
    }
    
    // === GESTION DES MAPPINGS ===
    
    /**
     * Sauvegarde les mappings JSON par type de dispositif
     */
    public function saveDeviceMappings(string $deviceType, array $mappings): bool
    {
        try {
            $key = "mappings:json:$deviceType";
            $serialized = json_encode($mappings, JSON_THROW_ON_ERROR);
            
            $this->redis->set($key, $serialized);
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur sauvegarde mappings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les mappings pour un type de dispositif
     */
    public function getDeviceMappings(string $deviceType): ?array
    {
        try {
            $key = "mappings:json:$deviceType";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération mappings: " . $e->getMessage());
            return null;
        }
    }
    
    // === GESTION DU CACHE DE DÉTECTION ===
    
    /**
     * Met en cache les données de détection par navigateur
     */
    public function cacheDetectionData(string $userAgentHash, array $data): bool
    {
        try {
            $key = "detection:cache:$userAgentHash";
            $serialized = json_encode($data, JSON_THROW_ON_ERROR);
            
            $this->redis->setex($key, self::DEFAULT_TTL, $serialized);
            return true;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur cache détection: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les données de détection en cache
     */
    public function getCachedDetectionData(string $userAgentHash): ?array
    {
        try {
            $key = "detection:cache:$userAgentHash";
            $data = $this->redis->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération cache détection: " . $e->getMessage());
            return null;
        }
    }
    
    // === MÉTHODES UTILITAIRES ===
    
    /**
     * Ajoute un dispositif à l'index pour la recherche rapide
     */
    private function addToDeviceIndex(string $deviceId, array $config): void
    {
        try {
            // Index par type
            if (isset($config['device_type'])) {
                $this->redis->sadd("index:devices:type:" . $config['device_type'], $deviceId);
            }
            
            // Index par vendor
            if (isset($config['vendor_id'])) {
                $this->redis->sadd("index:devices:vendor:" . $config['vendor_id'], $deviceId);
            }
            
            // Index général
            $this->redis->sadd("index:devices:all", $deviceId);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur ajout index: " . $e->getMessage());
        }
    }
    
    /**
     * Supprime un dispositif de l'index
     */
    private function removeFromDeviceIndex(string $deviceId): void
    {
        try {
            // Récupérer la config avant suppression pour nettoyer les index
            $config = $this->getDeviceConfig($deviceId);
            
            if ($config) {
                if (isset($config['device_type'])) {
                    $this->redis->srem("index:devices:type:" . $config['device_type'], $deviceId);
                }
                
                if (isset($config['vendor_id'])) {
                    $this->redis->srem("index:devices:vendor:" . $config['vendor_id'], $deviceId);
                }
            }
            
            $this->redis->srem("index:devices:all", $deviceId);
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur suppression index: " . $e->getMessage());
        }
    }
    
    /**
     * Recherche des dispositifs par critères
     */
    public function searchDevices(array $criteria = []): array
    {
        try {
            $deviceIds = [];
            
            if (isset($criteria['device_type'])) {
                $deviceIds = $this->redis->smembers("index:devices:type:" . $criteria['device_type']);
            } elseif (isset($criteria['vendor_id'])) {
                $deviceIds = $this->redis->smembers("index:devices:vendor:" . $criteria['vendor_id']);
            } else {
                $deviceIds = $this->redis->smembers("index:devices:all");
            }
            
            $devices = [];
            foreach ($deviceIds as $deviceId) {
                $config = $this->getDeviceConfig($deviceId);
                if ($config) {
                    $devices[$deviceId] = $config;
                }
            }
            
            return $devices;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur recherche dispositifs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Nettoie les données expirées et optimise les index
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        
        try {
            // Nettoyer les sessions expirées
            $sessionKeys = $this->redis->keys("sessions:stepbystep:*");
            foreach ($sessionKeys as $key) {
                $ttl = $this->redis->ttl($key);
                if ($ttl === -1) { // Pas de TTL défini
                    $this->redis->expire($key, self::SESSION_TTL);
                }
            }
            
            // Nettoyer les index orphelins
            $allDevices = $this->redis->smembers("index:devices:all");
            foreach ($allDevices as $deviceId) {
                if (!$this->getDeviceConfig($deviceId)) {
                    $this->removeFromDeviceIndex($deviceId);
                    $cleaned++;
                }
            }
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur cleanup: " . $e->getMessage());
        }
        
        return $cleaned;
    }
    
    /**
     * Obtient des statistiques sur l'utilisation de Redis
     */
    public function getStats(): array
    {
        try {
            $info = $this->redis->info();
            
            // Compter les clés par type
            $deviceCount = count($this->redis->keys("devices:config:*"));
            $sessionCount = count($this->redis->keys("sessions:stepbystep:*"));
            $tempCount = count($this->redis->keys("devices:temp:*"));
            $xmlCacheCount = count($this->redis->keys("xml:cache:*"));
            
            return [
                'connected' => true,
                'redis_version' => $info['redis_version'] ?? 'Unknown',
                'used_memory_human' => $info['used_memory_human'] ?? 'Unknown',
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'devices' => $deviceCount,
                'sessions' => $sessionCount,
                'temp_configs' => $tempCount,
                'xml_cache' => $xmlCacheCount
            ];
            
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupère toutes les clés correspondant à un pattern
     */
    public function getKeysPattern(string $pattern): array
    {
        try {
            if (!$this->isConnected()) {
                return [];
            }
            
            // Ajouter le préfixe au pattern
            $fullPattern = $this->config['prefix'] . $pattern;
            $keys = $this->redis->keys($fullPattern);
            
            return is_array($keys) ? $keys : [];
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur récupération des clés pattern '$pattern': " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Supprime une clé spécifique
     */
    public function delete(string $key): bool
    {
        try {
            if (!$this->isConnected()) {
                return false;
            }
            
            $result = $this->redis->del($key);
            return $result > 0;
            
        } catch (\Exception $e) {
            error_log("Redis: Erreur suppression clé '$key': " . $e->getMessage());
            return false;
        }
    }
}
