#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script d'activation Redis pour SC Config Editor
 * Met à jour les fichiers de configuration pour activer Redis
 */

$configUpdates = [
    'config.php' => [
        'search' => [
            "define('USE_REDIS', false);",
            "'redis_enabled' => false,",
            "'storage_backend' => 'localStorage'"
        ],
        'replace' => [
            "define('USE_REDIS', true);",
            "'redis_enabled' => true,",
            "'storage_backend' => 'redis'"
        ]
    ]
];

echo "🔧 Activation de Redis pour SC Config Editor\n";
echo "============================================\n\n";

// Vérifier que Redis fonctionne
echo "🔍 Vérification de Redis...\n";
exec('redis-cli ping 2>/dev/null', $output, $returnCode);
if ($returnCode !== 0 || !in_array('PONG', $output)) {
    echo "❌ Redis n'est pas disponible. Démarrez Redis avec: redis-server\n";
    exit(1);
}
echo "✅ Redis est disponible\n\n";

// Mise à jour des fichiers de configuration
foreach ($configUpdates as $file => $updates) {
    $filePath = __DIR__ . '/../' . $file;
    
    if (!file_exists($filePath)) {
        echo "⚠️  Fichier non trouvé: $file\n";
        continue;
    }
    
    echo "📝 Mise à jour de $file...\n";
    $content = file_get_contents($filePath);
    $modified = false;
    
    foreach ($updates['search'] as $index => $search) {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $updates['replace'][$index], $content);
            $modified = true;
            echo "  ✅ Activé: " . trim($updates['replace'][$index]) . "\n";
        }
    }
    
    if ($modified) {
        file_put_contents($filePath, $content);
        echo "  💾 $file mis à jour\n";
    } else {
        echo "  ℹ️  Aucune modification nécessaire pour $file\n";
    }
    echo "\n";
}

// Créer un fichier .env avec les variables Redis
echo "📄 Création du fichier .env...\n";
$envContent = <<<EOF
# Configuration Redis pour SC Config Editor
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0
REDIS_PASSWORD=
REDIS_PREFIX=sc_config:

# Environnement
APP_ENV=development
DEBUG=true
EOF;

file_put_contents(__DIR__ . '/../.env', $envContent);
echo "✅ Fichier .env créé\n\n";

// Mise à jour du package.json pour ajouter des scripts Redis
echo "📦 Mise à jour des scripts npm...\n";
$packageJsonPath = __DIR__ . '/../package.json';
if (file_exists($packageJsonPath)) {
    $packageJson = json_decode(file_get_contents($packageJsonPath), true);
    
    if (!isset($packageJson['scripts'])) {
        $packageJson['scripts'] = [];
    }
    
    $packageJson['scripts']['redis:start'] = 'redis-server --daemonize yes';
    $packageJson['scripts']['redis:stop'] = 'redis-cli shutdown';
    $packageJson['scripts']['redis:status'] = 'redis-cli ping';
    $packageJson['scripts']['redis:monitor'] = 'redis-cli monitor';
    $packageJson['scripts']['redis:flushall'] = 'redis-cli flushall';
    
    file_put_contents($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "✅ Scripts Redis ajoutés au package.json\n\n";
}

// Test final
echo "🧪 Test final de l'intégration...\n";
echo "================================\n";

// Test de connexion PHP
exec('cd ' . __DIR__ . '/.. && php test_redis_connection.php 2>&1', $testOutput, $testCode);
$testResult = implode("\n", $testOutput);
if (strpos($testResult, 'Tous les tests Redis ont réussi') !== false) {
    echo "✅ Test PHP Redis: OK\n";
} else {
    echo "❌ Test PHP Redis: ERREUR\n";
    echo "Détails: " . substr($testResult, 0, 200) . "...\n";
}

// Test de l'API
exec('curl -s http://localhost:8080/api/redis.php/status 2>/dev/null', $apiOutput, $apiCode);
$apiTest = implode("\n", $apiOutput);
if (strpos($apiTest, '"connected":true') !== false) {
    echo "✅ API Redis: OK\n";
} else {
    echo "⚠️  API Redis: Nécessite un serveur web actif\n";
    echo "   Démarrez avec: php -S localhost:8080\n";
}

echo "\n🎉 Configuration Redis activée avec succès!\n\n";

echo "📋 Prochaines étapes:\n";
echo "====================\n";
echo "1. Redémarrer le serveur web si nécessaire\n";
echo "2. Tester l'interface: http://localhost:8080/test_redis_integration.html\n";
echo "3. Vérifier les logs d'erreur en cas de problème\n";
echo "4. Utiliser 'npm run redis:status' pour vérifier Redis\n\n";

echo "📚 Commandes utiles:\n";
echo "===================\n";
echo "• Démarrer Redis: npm run redis:start\n";
echo "• Arrêter Redis: npm run redis:stop\n";
echo "• Statut Redis: npm run redis:status\n";
echo "• Surveiller Redis: npm run redis:monitor\n";
echo "• Vider Redis: npm run redis:flushall\n";
echo "• Test connexion: composer redis:test\n";
echo "• Migration données: composer redis:migrate\n\n";
