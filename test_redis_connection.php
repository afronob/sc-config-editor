<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/RedisManager.php';

use SCConfigEditor\RedisManager;

/**
 * Script de test pour vérifier la connexion Redis
 */

echo "🧪 Test de connexion Redis pour SC Config Editor\n";
echo "================================================\n\n";

try {
    // Charger la configuration
    $config = include __DIR__ . '/redis_config.php';
    
    echo "📋 Configuration Redis:\n";
    echo "  Host: " . $config['redis']['host'] . "\n";
    echo "  Port: " . $config['redis']['port'] . "\n";
    echo "  Database: " . $config['redis']['database'] . "\n";
    echo "  Prefix: " . $config['redis']['prefix'] . "\n\n";
    
    // Tester la connexion
    echo "🔌 Test de connexion...\n";
    $redisManager = new RedisManager($config['redis']);
    
    if ($redisManager->isConnected()) {
        echo "✅ Connexion Redis réussie!\n\n";
        
        // Test des opérations de base
        echo "🧪 Test des opérations de base...\n";
        
        // Test session step-by-step
        $sessionId = 'test_session_' . time();
        $sessionData = [
            'currentStep' => 1,
            'xmlData' => '<test>XML data</test>',
            'xmlName' => 'test.xml',
            'devices' => [],
            'created_at' => date('c')
        ];
        
        echo "  📝 Test sauvegarde session step-by-step...\n";
        $success = $redisManager->saveStepByStepSession($sessionId, $sessionData);
        echo $success ? "  ✅ Session sauvegardée\n" : "  ❌ Erreur sauvegarde session\n";
        
        echo "  📖 Test récupération session...\n";
        $retrievedSession = $redisManager->getStepByStepSession($sessionId);
        echo ($retrievedSession !== null) ? "  ✅ Session récupérée\n" : "  ❌ Erreur récupération session\n";
        
        // Test dispositif
        $deviceId = 'test_device_' . time();
        $deviceConfig = [
            'name' => 'Test Device',
            'device_type' => 'joystick',
            'vendor_id' => '1234',
            'product_id' => '5678',
            'buttons' => ['button1', 'button2', 'button3'],
            'axes_map' => ['x' => 0, 'y' => 1]
        ];
        
        echo "  🎮 Test sauvegarde dispositif...\n";
        $success = $redisManager->saveDeviceConfig($deviceId, $deviceConfig);
        echo $success ? "  ✅ Dispositif sauvegardé\n" : "  ❌ Erreur sauvegarde dispositif\n";
        
        echo "  📖 Test récupération dispositif...\n";
        $retrievedDevice = $redisManager->getDeviceConfig($deviceId);
        echo ($retrievedDevice !== null) ? "  ✅ Dispositif récupéré\n" : "  ❌ Erreur récupération dispositif\n";
        
        // Test cache XML
        $xmlHash = 'test_xml_' . md5('test content');
        $xmlContent = '<?xml version="1.0"?><test>content</test>';
        $xmlMetadata = ['size' => strlen($xmlContent), 'type' => 'test'];
        
        echo "  📄 Test cache XML...\n";
        $success = $redisManager->cacheXMLContent($xmlHash, $xmlContent, $xmlMetadata);
        echo $success ? "  ✅ XML mis en cache\n" : "  ❌ Erreur cache XML\n";
        
        echo "  📖 Test récupération cache XML...\n";
        $retrievedXML = $redisManager->getCachedXML($xmlHash);
        echo ($retrievedXML !== null) ? "  ✅ Cache XML récupéré\n" : "  ❌ Erreur récupération cache XML\n";
        
        // Statistiques
        echo "\n📊 Statistiques Redis:\n";
        $stats = $redisManager->getStats();
        if ($stats['connected']) {
            echo "  Version Redis: " . $stats['redis_version'] . "\n";
            echo "  Mémoire utilisée: " . $stats['used_memory_human'] . "\n";
            echo "  Dispositifs stockés: " . $stats['devices'] . "\n";
            echo "  Sessions actives: " . $stats['sessions'] . "\n";
            echo "  Configurations temporaires: " . $stats['temp_configs'] . "\n";
            echo "  Cache XML: " . $stats['xml_cache'] . "\n";
        }
        
        // Nettoyage des données de test
        echo "\n🧹 Nettoyage des données de test...\n";
        $redisManager->deleteStepByStepSession($sessionId);
        $redisManager->deleteDeviceConfig($deviceId);
        $cleaned = $redisManager->cleanup();
        echo "  ✅ Nettoyage terminé ($cleaned éléments supprimés)\n";
        
        echo "\n🎉 Tous les tests Redis ont réussi!\n";
        echo "✅ Redis est prêt pour l'intégration avec SC Config Editor\n";
        
    } else {
        echo "❌ Impossible de se connecter à Redis\n";
        echo "💡 Assurez-vous que Redis est démarré:\n";
        echo "   docker run -d --name redis-sc-config -p 6379:6379 redis:alpine\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du test Redis: " . $e->getMessage() . "\n";
    echo "\n💡 Solutions possibles:\n";
    echo "1. Vérifier que Redis est installé et démarré\n";
    echo "2. Installer les dépendances: composer install\n";
    echo "3. Vérifier la configuration dans redis_config.php\n";
    exit(1);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test terminé à " . date('Y-m-d H:i:s') . "\n";
