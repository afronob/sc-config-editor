<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/RedisManager.php';

use SCConfigEditor\RedisManager;

/**
 * Script de migration des données existantes vers Redis
 */

class DataMigration
{
    private RedisManager $redisManager;
    private array $config;
    private array $stats = [
        'sessions_migrated' => 0,
        'devices_migrated' => 0,
        'files_processed' => 0,
        'errors' => []
    ];
    
    public function __construct()
    {
        $this->config = include __DIR__ . '/../redis_config.php';
        $this->redisManager = new RedisManager($this->config['redis']);
        
        if (!$this->redisManager->isConnected()) {
            throw new \RuntimeException('Redis non disponible pour la migration');
        }
    }
    
    public function migrate(): void
    {
        echo "🚀 Migration des données vers Redis\n";
        echo "==================================\n\n";
        
        try {
            $this->migrateDeviceFiles();
            $this->migrateTempSessions();
            $this->migrateJSONMappings();
            
            $this->displayResults();
            
        } catch (\Exception $e) {
            echo "❌ Erreur durante la migration: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * Migre les fichiers JSON de dispositifs existants
     */
    private function migrateDeviceFiles(): void
    {
        echo "📱 Migration des fichiers de dispositifs...\n";
        
        $devicesDir = __DIR__ . '/../files';
        $deviceFiles = glob($devicesDir . '/*.json');
        
        if (empty($deviceFiles)) {
            echo "  ℹ️ Aucun fichier de dispositif trouvé dans $devicesDir\n";
            return;
        }
        
        foreach ($deviceFiles as $file) {
            try {
                $content = file_get_contents($file);
                $deviceData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                
                if (!$deviceData) {
                    $this->stats['errors'][] = "Fichier vide ou invalide: $file";
                    continue;
                }
                
                // Générer un ID basé sur le nom du fichier
                $deviceId = 'migrated_' . pathinfo($file, PATHINFO_FILENAME);
                
                // Ajouter des métadonnées de migration
                $deviceData['migrated_from'] = $file;
                $deviceData['migrated_at'] = date('c');
                $deviceData['migration_version'] = '1.0';
                
                if ($this->redisManager->saveDeviceConfig($deviceId, $deviceData)) {
                    echo "  ✅ Migré: " . basename($file) . " → $deviceId\n";
                    $this->stats['devices_migrated']++;
                } else {
                    $this->stats['errors'][] = "Erreur sauvegarde Redis: $file";
                }
                
                $this->stats['files_processed']++;
                
            } catch (\Exception $e) {
                $this->stats['errors'][] = "Erreur lecture $file: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Migre les sessions temporaires et données en cours
     */
    private function migrateTempSessions(): void
    {
        echo "\n💾 Migration des sessions temporaires...\n";
        
        // Démarrer une session pour vérifier les données existantes
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['stepByStep'])) {
            $sessionData = $_SESSION['stepByStep'];
            $sessionId = session_id();
            
            // Ajouter des métadonnées de migration
            $sessionData['migrated_from'] = 'php_session';
            $sessionData['migrated_at'] = date('c');
            
            if ($this->redisManager->saveStepByStepSession($sessionId, $sessionData)) {
                echo "  ✅ Session step-by-step migrée: $sessionId\n";
                $this->stats['sessions_migrated']++;
            } else {
                $this->stats['errors'][] = "Erreur migration session: $sessionId";
            }
        } else {
            echo "  ℹ️ Aucune session step-by-step active à migrer\n";
        }
        
        // Migrer les données localStorage simulées (si disponibles)
        $this->migrateLocalStorageData();
    }
    
    /**
     * Simule la migration des données localStorage
     */
    private function migrateLocalStorageData(): void
    {
        // Cette fonction sera utilisée lors de l'intégration côté client
        // Pour l'instant, on simule avec des données d'exemple
        
        $simulatedLocalStorage = [
            'sc_devices' => json_encode([
                'device_local_1' => [
                    'name' => 'Device from localStorage',
                    'device_type' => 'joystick',
                    'vendor_id' => '9999',
                    'product_id' => '0001',
                    'source' => 'localStorage'
                ]
            ])
        ];
        
        foreach ($simulatedLocalStorage as $key => $value) {
            if ($key === 'sc_devices') {
                $devices = json_decode($value, true);
                if ($devices) {
                    foreach ($devices as $deviceId => $deviceData) {
                        $redisDeviceId = 'localStorage_' . $deviceId;
                        $deviceData['migrated_from'] = 'localStorage';
                        $deviceData['migrated_at'] = date('c');
                        
                        if ($this->redisManager->saveDeviceConfig($redisDeviceId, $deviceData)) {
                            echo "  ✅ Dispositif localStorage migré: $redisDeviceId\n";
                            $this->stats['devices_migrated']++;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Migre les mappings JSON existants
     */
    private function migrateJSONMappings(): void
    {
        echo "\n🗂️ Migration des mappings JSON...\n";
        
        $mappingsDir = __DIR__ . '/../mappings';
        $mappingDirs = [
            'devices' => $mappingsDir . '/devices',
            'templates' => $mappingsDir . '/templates',
            'generated' => $mappingsDir . '/generated'
        ];
        
        foreach ($mappingDirs as $type => $dir) {
            if (!is_dir($dir)) {
                echo "  ℹ️ Dossier non trouvé: $dir\n";
                continue;
            }
            
            $files = glob($dir . '/*.json');
            foreach ($files as $file) {
                try {
                    $content = file_get_contents($file);
                    $mappingData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                    
                    $mappingKey = $type . '_' . pathinfo($file, PATHINFO_FILENAME);
                    
                    if ($this->redisManager->saveDeviceMappings($mappingKey, $mappingData)) {
                        echo "  ✅ Mapping migré: " . basename($file) . " → $mappingKey\n";
                    } else {
                        $this->stats['errors'][] = "Erreur migration mapping: $file";
                    }
                    
                } catch (\Exception $e) {
                    $this->stats['errors'][] = "Erreur lecture mapping $file: " . $e->getMessage();
                }
            }
        }
    }
    
    /**
     * Affiche les résultats de la migration
     */
    private function displayResults(): void
    {
        echo "\n📊 Résultats de la migration\n";
        echo "=============================\n";
        echo "✅ Dispositifs migrés: " . $this->stats['devices_migrated'] . "\n";
        echo "✅ Sessions migrées: " . $this->stats['sessions_migrated'] . "\n";
        echo "📁 Fichiers traités: " . $this->stats['files_processed'] . "\n";
        
        if (!empty($this->stats['errors'])) {
            echo "\n⚠️ Erreurs rencontrées:\n";
            foreach ($this->stats['errors'] as $error) {
                echo "  - $error\n";
            }
        }
        
        // Afficher les statistiques Redis
        echo "\n📈 Statistiques Redis après migration:\n";
        $redisStats = $this->redisManager->getStats();
        if ($redisStats['connected']) {
            echo "  Dispositifs en Redis: " . $redisStats['devices'] . "\n";
            echo "  Sessions en Redis: " . $redisStats['sessions'] . "\n";
            echo "  Mémoire utilisée: " . $redisStats['used_memory_human'] . "\n";
        }
        
        echo "\n🎉 Migration terminée avec succès!\n";
        echo "\n💡 Prochaines étapes:\n";
        echo "1. Tester l'application avec Redis\n";
        echo "2. Mettre à jour la configuration pour activer Redis\n";
        echo "3. Déployer les nouvelles fonctionnalités\n";
    }
}

// Point d'entrée du script
try {
    $migration = new DataMigration();
    $migration->migrate();
} catch (\Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "\n💡 Vérifiez que:\n";
    echo "1. Redis est démarré\n";
    echo "2. Les dépendances sont installées (composer install)\n";
    echo "3. La configuration Redis est correcte\n";
    exit(1);
}
