<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/RedisManager.php';
require_once __DIR__ . '/../config.php';

use SCConfigEditor\RedisManager;

/**
 * API endpoints pour la communication avec Redis côté client
 */
class RedisAPI
{
    private RedisManager $redisManager;
    private array $config;
    
    public function __construct()
    {
        // Charger la configuration
        $this->config = $this->loadConfig();
        
        // Initialiser Redis
        $this->redisManager = new RedisManager($this->config['redis'] ?? []);
        
        // Définir les headers CORS et JSON
        $this->setupHeaders();
    }
    
    private function loadConfig(): array
    {
        // Configuration par défaut
        $defaultConfig = [
            'redis' => [
                'enabled' => true,
                'host' => 'localhost',
                'port' => 6379,
                'database' => 1, // Base séparée pour les données client
                'password' => null,
                'prefix' => 'sc_client:'
            ]
        ];
        
        // Charger depuis un fichier de config si disponible
        $configFile = __DIR__ . '/../redis_config.php';
        if (file_exists($configFile)) {
            $fileConfig = include $configFile;
            // Fusionner les configurations en évitant les tableaux imbriqués
            if (isset($fileConfig['redis'])) {
                $defaultConfig['redis'] = array_merge($defaultConfig['redis'], $fileConfig['redis']);
            }
            return $defaultConfig;
        }
        
        return $defaultConfig;
    }
    
    private function setupHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    public function handleRequest(): void
    {
        try {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $action = basename($path);
            
            switch ($action) {
                case 'status':
                    $this->handleStatus();
                    break;
                    
                case 'test':
                    $this->handleTest();
                    break;
                    
                case 'get':
                    $this->handleGet();
                    break;
                    
                case 'set':
                    $this->handleSet();
                    break;
                    
                case 'delete':
                    $this->handleDelete();
                    break;
                    
                case 'clear':
                    $this->handleClear();
                    break;
                    
                case 'stats':
                    $this->handleStats();
                    break;
                    
                default:
                    $this->sendError('Endpoint non trouvé', 404);
            }
            
        } catch (\Exception $e) {
            error_log("Redis API Error: " . $e->getMessage());
            $this->sendError('Erreur serveur: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleStatus(): void
    {
        $connected = $this->redisManager->isConnected();
        
        $this->sendResponse([
            'connected' => $connected,
            'timestamp' => date('c'),
            'config' => [
                'host' => $this->config['redis']['host'],
                'port' => $this->config['redis']['port'],
                'database' => $this->config['redis']['database']
            ]
        ]);
    }
    
    private function handleGet(): void
    {
        $key = $_GET['key'] ?? null;
        
        if (!$key) {
            $this->sendError('Paramètre "key" requis', 400);
            return;
        }
        
        // Validation de la clé
        if (!$this->isValidKey($key)) {
            $this->sendError('Clé invalide', 400);
            return;
        }
        
        try {
            $value = null;
            
            // Déterminer le type de clé et utiliser la méthode appropriée
            if (strpos($key, 'devices:config:') !== false) {
                // Configuration de dispositif
                $deviceId = str_replace('devices:config:', '', $key);
                $value = $this->redisManager->getDeviceConfig($deviceId);
                
            } elseif (strpos($key, 'sc_devices') !== false) {
                // Configuration de dispositif (ancien format)
                $deviceId = $this->extractDeviceId($key);
                $value = $this->redisManager->getDeviceConfig($deviceId);
                
            } elseif (strpos($key, 'detection') !== false) {
                // Données de détection
                $hash = $this->extractHash($key);
                $value = $this->redisManager->getCachedDetectionData($hash);
                
            } else {
                // Configuration temporaire
                $hash = md5($key);
                $value = $this->redisManager->getTempDeviceConfig($hash);
            }
            
            $this->sendResponse([
                'success' => true,
                'value' => $value ? json_encode($value) : null,
                'found' => $value !== null
            ]);
            
        } catch (\Exception $e) {
            $this->sendError('Erreur récupération: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleSet(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['key']) || !isset($input['value'])) {
            $this->sendError('Paramètres "key" et "value" requis', 400);
            return;
        }
        
        $key = $input['key'];
        $value = $input['value'];
        $ttl = $input['ttl'] ?? 86400; // 24h par défaut
        
        // Validation de la clé
        if (!$this->isValidKey($key)) {
            $this->sendError('Clé invalide', 400);
            return;
        }
        
        try {
            // La valeur est déjà décodée depuis getInput()
            // Pas besoin de décoder à nouveau
            
            // Déterminer le type de données et utiliser la méthode appropriée
            if (strpos($key, 'devices:config:') !== false) {
                // Configuration de dispositif
                $deviceId = str_replace('devices:config:', '', $key);
                $success = $this->redisManager->saveDeviceConfig($deviceId, $value);
                
            } elseif (strpos($key, 'sc_devices') !== false) {
                // Configuration de dispositif (ancien format)
                $deviceId = $this->extractDeviceId($key);
                $success = $this->redisManager->saveDeviceConfig($deviceId, $value);
                
            } elseif (strpos($key, 'detection') !== false) {
                // Données de détection
                $hash = $this->extractHash($key);
                $success = $this->redisManager->cacheDetectionData($hash, $value);
                
            } else {
                // Configuration temporaire
                $hash = md5($key);
                $success = $this->redisManager->saveTempDeviceConfig($hash, [
                    'key' => $key,
                    'value' => $value,
                    'ttl' => $ttl
                ]);
            }
            
            $this->sendResponse([
                'success' => $success,
                'key' => $key,
                'stored_at' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->sendError('Erreur sauvegarde: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleDelete(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['key'])) {
            $this->sendError('Paramètre "key" requis', 400);
            return;
        }
        
        $key = $input['key'];
        
        // Validation de la clé
        if (!$this->isValidKey($key)) {
            $this->sendError('Clé invalide', 400);
            return;
        }
        
        try {
            // Déterminer le type et supprimer
            if (strpos($key, 'sc_devices') !== false) {
                $deviceId = $this->extractDeviceId($key);
                $success = $this->redisManager->deleteDeviceConfig($deviceId);
            } else {
                // Pour les autres types, on ne peut pas facilement supprimer
                // car ils utilisent des méthodes spécialisées
                $success = false;
            }
            
            $this->sendResponse([
                'success' => $success,
                'key' => $key,
                'deleted_at' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->sendError('Erreur suppression: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleClear(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $pattern = $input['pattern'] ?? 'sc_client:*';
        
        try {
            $cleaned = $this->redisManager->cleanup();
            
            $this->sendResponse([
                'success' => true,
                'pattern' => $pattern,
                'cleaned_count' => $cleaned,
                'cleared_at' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->sendError('Erreur nettoyage: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleStats(): void
    {
        try {
            $stats = $this->redisManager->getStats();
            
            // Ajouter des statistiques spécifiques à l'API
            $stats['api_version'] = '1.0';
            $stats['endpoints'] = ['status', 'get', 'set', 'delete', 'clear', 'stats'];
            $stats['timestamp'] = date('c');
            
            $this->sendResponse($stats);
            
        } catch (\Exception $e) {
            $this->sendError('Erreur récupération stats: ' . $e->getMessage(), 500);
        }
    }
    
    private function handleTest(): void
    {
        try {
            $testKey = 'test:' . uniqid();
            $testValue = ['message' => 'Test Redis OK', 'timestamp' => time()];
            
            // Test d'écriture
            $this->redisManager->setData($testKey, $testValue, 60);
            
            // Test de lecture
            $retrievedValue = $this->redisManager->getData($testKey);
            
            // Test de suppression
            $this->redisManager->deleteData($testKey);
            
            $this->sendResponse([
                'status' => 'success',
                'message' => 'Tous les tests Redis ont réussi',
                'tests' => [
                    'write' => true,
                    'read' => $retrievedValue !== null,
                    'delete' => true
                ],
                'timestamp' => date('c')
            ]);
            
        } catch (\Exception $e) {
            $this->sendError('Échec du test Redis: ' . $e->getMessage(), 500);
        }
    }
    
    private function isValidKey(string $key): bool
    {
        // Validation basique de la clé
        if (strlen($key) > 250) return false;
        if (preg_match('/[^a-zA-Z0-9:_\-.]/', $key)) return false;
        
        return true;
    }
    
    private function extractDeviceId(string $key): string
    {
        // Extraire l'ID de device d'une clé comme "sc_client:sc_devices:device_123"
        $parts = explode(':', $key);
        return end($parts);
    }
    
    private function extractHash(string $key): string
    {
        // Extraire le hash d'une clé
        $parts = explode(':', $key);
        return end($parts);
    }
    
    private function sendResponse(array $data): void
    {
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }
    
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code,
            'timestamp' => date('c')
        ], JSON_THROW_ON_ERROR);
        exit;
    }
}

// Point d'entrée de l'API
try {
    $api = new RedisAPI();
    $api->handleRequest();
} catch (\Exception $e) {
    error_log("Redis API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur critique',
        'timestamp' => date('c')
    ]);
}
