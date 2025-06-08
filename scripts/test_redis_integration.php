#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/RedisManager.php';

use SCConfigEditor\RedisManager;

/**
 * Test d'intégration complet Redis
 * Valide toutes les fonctionnalités Redis du système
 */

class RedisIntegrationTest
{
    private RedisManager $redisManager;
    private array $config;
    private int $testsPassed = 0;
    private int $testsTotal = 0;
    private array $errors = [];
    
    public function __construct()
    {
        $this->config = include __DIR__ . '/../redis_config.php';
        $this->redisManager = new RedisManager($this->config['redis']);
    }
    
    public function runAllTests(): void
    {
        echo "🧪 Test d'intégration Redis complet\n";
        echo "=====================================\n\n";
        
        $this->testRedisConnection();
        $this->testDeviceOperations();
        $this->testSessionManagement();
        $this->testXMLCaching();
        $this->testDetectionCaching();
        $this->testSearchAndIndexing();
        $this->testAPIEndpoints();
        $this->testPerformance();
        $this->testCleanup();
        
        $this->displayResults();
    }
    
    private function testRedisConnection(): void
    {
        echo "🔌 Test de connexion Redis...\n";
        $this->testsTotal++;
        
        try {
            if ($this->redisManager->isConnected()) {
                echo "  ✅ Connexion Redis OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Connexion Redis échouée");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur: " . $e->getMessage() . "\n";
            $this->errors[] = "Connexion Redis: " . $e->getMessage();
        }
        echo "\n";
    }
    
    private function testDeviceOperations(): void
    {
        echo "🎮 Test des opérations sur les dispositifs...\n";
        
        // Test sauvegarde dispositif
        $this->testsTotal++;
        try {
            $deviceConfig = [
                'name' => 'Test Integration Device',
                'type' => 'joystick',
                'vendor_id' => '046d',
                'product_id' => 'c626',
                'mappings' => [
                    'button_0' => 'fire_primary',
                    'axis_x' => 'pitch',
                    'axis_y' => 'yaw'
                ]
            ];
            
            $result = $this->redisManager->saveDeviceConfig('integration_test_device', $deviceConfig);
            if ($result) {
                echo "  ✅ Sauvegarde dispositif OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Échec sauvegarde dispositif");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur sauvegarde: " . $e->getMessage() . "\n";
            $this->errors[] = "Sauvegarde dispositif: " . $e->getMessage();
        }
        
        // Test récupération dispositif
        $this->testsTotal++;
        try {
            $retrieved = $this->redisManager->getDeviceConfig('integration_test_device');
            if ($retrieved && $retrieved['name'] === 'Test Integration Device') {
                echo "  ✅ Récupération dispositif OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Dispositif non trouvé ou corrompu");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur récupération: " . $e->getMessage() . "\n";
            $this->errors[] = "Récupération dispositif: " . $e->getMessage();
        }
        
        // Test recherche dispositifs
        $this->testsTotal++;
        try {
            $devices = $this->redisManager->searchDevices(['device_type' => 'joystick']);
            if (count($devices) >= 1) {
                echo "  ✅ Recherche dispositifs OK (" . count($devices) . " trouvés)\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Aucun dispositif trouvé dans la recherche");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur recherche: " . $e->getMessage() . "\n";
            $this->errors[] = "Recherche dispositifs: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testSessionManagement(): void
    {
        echo "💾 Test de gestion des sessions...\n";
        
        $this->testsTotal++;
        try {
            $sessionData = [
                'step' => 'device_selection',
                'device_id' => 'integration_test_device',
                'progress' => 75,
                'mappings' => [
                    'button_0' => 'fire_primary'
                ]
            ];
            
            $sessionId = 'integration_test_session_' . time();
            
            // Test sauvegarde session
            $saved = $this->redisManager->saveStepByStepSession($sessionId, $sessionData);
            if (!$saved) {
                throw new \Exception("Échec sauvegarde session");
            }
            
            // Test récupération session
            $retrieved = $this->redisManager->getStepByStepSession($sessionId);
            if ($retrieved && $retrieved['step'] === 'device_selection') {
                echo "  ✅ Gestion sessions OK\n";
                $this->testsPassed++;
                
                // Nettoyer
                $this->redisManager->deleteStepByStepSession($sessionId);
            } else {
                throw new \Exception("Session non récupérée correctement");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur sessions: " . $e->getMessage() . "\n";
            $this->errors[] = "Gestion sessions: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testXMLCaching(): void
    {
        echo "📄 Test du cache XML...\n";
        
        $this->testsTotal++;
        try {
            $xmlContent = '<?xml version="1.0"?><test><device>Integration Test</device></test>';
            $metadata = [
                'file_name' => 'integration_test.xml',
                'file_size' => strlen($xmlContent),
                'device_type' => 'joystick'
            ];
            
            $hash = 'integration_test_xml_' . md5($xmlContent);
            
            // Test mise en cache
            $cached = $this->redisManager->cacheXMLContent($hash, $xmlContent, $metadata);
            if (!$cached) {
                throw new \Exception("Échec mise en cache XML");
            }
            
            // Test récupération cache
            $retrieved = $this->redisManager->getCachedXML($hash);
            if ($retrieved && $retrieved['content'] === $xmlContent) {
                echo "  ✅ Cache XML OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("XML non récupéré du cache");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur cache XML: " . $e->getMessage() . "\n";
            $this->errors[] = "Cache XML: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testDetectionCaching(): void
    {
        echo "🔍 Test du cache de détection...\n";
        
        $this->testsTotal++;
        try {
            $detectionData = [
                'device_type' => 'joystick',
                'vendor' => 'Logitech',
                'product' => 'Extreme 3D Pro',
                'confidence' => 95
            ];
            
            $userAgentHash = 'integration_test_ua_' . md5('test_user_agent');
            
            // Test mise en cache
            $cached = $this->redisManager->cacheDetectionData($userAgentHash, $detectionData);
            if (!$cached) {
                throw new \Exception("Échec cache détection");
            }
            
            // Test récupération cache
            $retrieved = $this->redisManager->getCachedDetectionData($userAgentHash);
            if ($retrieved && $retrieved['vendor'] === 'Logitech') {
                echo "  ✅ Cache détection OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Données détection non récupérées");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur cache détection: " . $e->getMessage() . "\n";
            $this->errors[] = "Cache détection: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testSearchAndIndexing(): void
    {
        echo "🔎 Test de recherche et indexation...\n";
        
        $this->testsTotal++;
        try {
            // Les index devraient déjà être créés par les opérations précédentes
            $allDevices = $this->redisManager->searchDevices();
            $joysticks = $this->redisManager->searchDevices(['device_type' => 'joystick']);
            
            if (count($allDevices) >= 1 && count($joysticks) >= 1) {
                echo "  ✅ Recherche et indexation OK\n";
                echo "    - Total dispositifs: " . count($allDevices) . "\n";
                echo "    - Joysticks: " . count($joysticks) . "\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Index ou recherche défaillante");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur recherche: " . $e->getMessage() . "\n";
            $this->errors[] = "Recherche et indexation: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testAPIEndpoints(): void
    {
        echo "🌐 Test des endpoints API...\n";
        
        // Test status endpoint
        $this->testsTotal++;
        try {
            $response = @file_get_contents('http://localhost:8080/api/redis.php/status');
            if ($response && strpos($response, '"connected":true') !== false) {
                echo "  ✅ API Status OK\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("API Status non accessible");
            }
        } catch (\Exception $e) {
            echo "  ⚠️  API Status: " . $e->getMessage() . " (serveur web requis)\n";
        }
        
        echo "\n";
    }
    
    private function testPerformance(): void
    {
        echo "⚡ Test de performance...\n";
        
        $this->testsTotal++;
        try {
            $startTime = microtime(true);
            
            // Test de 100 opérations
            for ($i = 0; $i < 100; $i++) {
                $this->redisManager->saveTempDeviceConfig(
                    'perf_test_' . $i,
                    ['test' => 'performance', 'iteration' => $i]
                );
            }
            
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // en ms
            
            if ($duration < 1000) { // Moins d'1 seconde pour 100 opérations
                echo "  ✅ Performance OK (" . number_format($duration, 2) . "ms pour 100 ops)\n";
                $this->testsPassed++;
            } else {
                throw new \Exception("Performance dégradée: " . number_format($duration, 2) . "ms");
            }
        } catch (\Exception $e) {
            echo "  ❌ Erreur performance: " . $e->getMessage() . "\n";
            $this->errors[] = "Performance: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function testCleanup(): void
    {
        echo "🧹 Test de nettoyage...\n";
        
        $this->testsTotal++;
        try {
            $cleaned = $this->redisManager->cleanup();
            echo "  ✅ Nettoyage OK ($cleaned éléments nettoyés)\n";
            $this->testsPassed++;
        } catch (\Exception $e) {
            echo "  ❌ Erreur nettoyage: " . $e->getMessage() . "\n";
            $this->errors[] = "Nettoyage: " . $e->getMessage();
        }
        
        echo "\n";
    }
    
    private function displayResults(): void
    {
        echo "📊 Résultats des tests\n";
        echo "=====================\n";
        echo "Tests réussis: {$this->testsPassed}/{$this->testsTotal}\n";
        echo "Taux de succès: " . round(($this->testsPassed / $this->testsTotal) * 100, 1) . "%\n\n";
        
        if (count($this->errors) > 0) {
            echo "❌ Erreurs rencontrées:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
            echo "\n";
        }
        
        // Statistiques Redis
        $stats = $this->redisManager->getStats();
        echo "📈 Statistiques Redis:\n";
        echo "  Dispositifs: " . $stats['devices'] . "\n";
        echo "  Sessions: " . $stats['sessions'] . "\n";
        echo "  Cache XML: " . $stats['xml_cache'] . "\n";
        echo "  Configurations temporaires: " . $stats['temp_configs'] . "\n";
        
        if ($this->testsPassed === $this->testsTotal) {
            echo "\n🎉 Tous les tests Redis ont réussi!\n";
            echo "✅ L'intégration Redis est complètement fonctionnelle\n\n";
            
            echo "🚀 Système prêt pour la production!\n";
            echo "===================================\n";
            echo "• Redis fonctionne correctement\n";
            echo "• API opérationnelle\n";
            echo "• Performances optimales\n";
            echo "• Cache et sessions fonctionnels\n";
            echo "• Recherche et indexation actives\n";
        } else {
            echo "\n⚠️  Certains tests ont échoué\n";
            echo "Veuillez corriger les problèmes avant la mise en production\n";
        }
    }
}

// Exécution des tests
try {
    $tester = new RedisIntegrationTest();
    $tester->runAllTests();
} catch (\Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    exit(1);
}
