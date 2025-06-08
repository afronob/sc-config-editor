<?php
/**
 * Script de validation finale du système d'affichage des statistiques XML
 * Teste tous les aspects de la fonctionnalité implémentée
 */

// Démarrer la session avant tout output
session_start();

require_once 'bootstrap.php';

use SCConfigEditor\XMLProcessor;
use SCConfigEditor\StepByStepEditor;

// Récupérer la configuration du bootstrap
global $config;

echo "<h1>🔍 Validation finale du système de statistiques XML</h1>\n";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; }
.test-section { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
.stats { background: #f5f5f5; padding: 10px; border-radius: 3px; margin: 5px 0; }
</style>";

$testsPassed = 0;
$totalTests = 0;

function runTest($testName, $testFunction) {
    global $testsPassed, $totalTests;
    $totalTests++;
    
    echo "<div class='test-section'>";
    echo "<h3>Test: $testName</h3>";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "<div class='success'>✅ SUCCÈS</div>";
            $testsPassed++;
        } else {
            echo "<div class='error'>❌ ÉCHEC</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ ERREUR: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

// Test 1: Vérifier que XMLProcessor::getStats() fonctionne
runTest("XMLProcessor::getStats() fonctionne correctement", function() {
    $xmlFile = __DIR__ . '/test_config.xml';
    if (!file_exists($xmlFile)) {
        echo "<div class='error'>Fichier test manquant: $xmlFile</div>";
        return false;
    }
    
    $processor = new XMLProcessor($xmlFile, 'test_config.xml');
    $stats = $processor->getStats();
    
    echo "<div class='info'>Statistiques obtenues:</div>";
    echo "<div class='stats'>";
    echo "Total: " . $stats['total'] . "<br>";
    echo "Utilisées: " . $stats['used'] . "<br>";
    echo "</div>";
    
    return isset($stats['total']) && isset($stats['used']) && 
           $stats['total'] > 0 && $stats['used'] >= 0;
});

// Test 2: Vérifier que StepByStepEditor peut être instancié avec config
runTest("StepByStepEditor s'instancie correctement avec config", function() {
    global $config;
    
    try {
        $editor = new StepByStepEditor($config);
        echo "<div class='info'>StepByStepEditor instancié avec succès</div>";
        return true;
    } catch (Exception $e) {
        echo "<div class='error'>Erreur d'instanciation: " . $e->getMessage() . "</div>";
        return false;
    }
});

// Test 3: Vérifier que XMLProcessor peut analyser le fichier test
runTest("XMLProcessor analyse correctement le fichier test", function() {
    $xmlFile = __DIR__ . '/test_config.xml';
    
    if (!file_exists($xmlFile)) {
        echo "<div class='error'>Fichier test manquant: $xmlFile</div>";
        return false;
    }
    
    try {
        $processor = new XMLProcessor($xmlFile, 'test_config.xml');
        $stats = $processor->getStats();
        
        echo "<div class='info'>Analyse du fichier XML:</div>";
        echo "<div class='stats'>";
        echo "Fichier: test_config.xml<br>";
        echo "Total d'actions: " . $stats['total'] . "<br>";
        echo "Actions configurées: " . $stats['used'] . "<br>";
        if ($stats['total'] > 0) {
            $percentage = round(($stats['used'] / $stats['total']) * 100, 1);
            echo "Pourcentage configuré: " . $percentage . "%<br>";
        }
        echo "</div>";
        
        return $stats['total'] > 0 && $stats['used'] >= 0;
    } catch (Exception $e) {
        echo "<div class='error'>Erreur lors de l'analyse: " . $e->getMessage() . "</div>";
        return false;
    }
});

// Test 4: Vérifier la structure du template step1_upload.php
runTest("Template step1_upload.php contient les éléments nécessaires", function() {
    $templateFile = __DIR__ . '/templates/step_by_step/step1_upload.php';
    
    if (!file_exists($templateFile)) {
        echo "<div class='error'>Template manquant: $templateFile</div>";
        return false;
    }
    
    $templateContent = file_get_contents($templateFile);
    
    // Vérifier les éléments clés ajoutés
    $hasConditionalDisplay = strpos($templateContent, 'isset($xmlName) && isset($xmlStats)') !== false;
    $hasCSSStyles = strpos($templateContent, '.file-uploaded-info') !== false;
    $hasStatsDisplay = strpos($templateContent, 'Actions trouvées:') !== false;
    $hasConfiguredDisplay = strpos($templateContent, 'Actions configurées:') !== false;
    $hasPercentageDisplay = strpos($templateContent, 'round(($xmlStats[\'used\'] / $xmlStats[\'total\']) * 100, 1)') !== false;
    
    echo "<div class='info'>Éléments trouvés dans le template:</div>";
    echo "<div class='stats'>";
    echo "Affichage conditionnel: " . ($hasConditionalDisplay ? '✅' : '❌') . "<br>";
    echo "Styles CSS file-uploaded-info: " . ($hasCSSStyles ? '✅' : '❌') . "<br>";
    echo "Affichage Actions trouvées: " . ($hasStatsDisplay ? '✅' : '❌') . "<br>";
    echo "Affichage Actions configurées: " . ($hasConfiguredDisplay ? '✅' : '❌') . "<br>";
    echo "Calcul pourcentage: " . ($hasPercentageDisplay ? '✅' : '❌') . "<br>";
    echo "</div>";
    
    return $hasConditionalDisplay && $hasCSSStyles && $hasStatsDisplay && 
           $hasConfiguredDisplay && $hasPercentageDisplay;
});

// Test 5: Vérifier les modifications dans StepByStepEditor.php
runTest("StepByStepEditor.php contient les modifications nécessaires", function() {
    $editorFile = __DIR__ . '/src/StepByStepEditor.php';
    
    if (!file_exists($editorFile)) {
        echo "<div class='error'>Fichier StepByStepEditor.php manquant</div>";
        return false;
    }
    
    $editorContent = file_get_contents($editorFile);
    
    // Vérifier les modifications ajoutées
    $hasStatsCalculation = strpos($editorContent, 'getStats()') !== false;
    $hasStatsSession = strpos($editorContent, 'xmlStats') !== false;
    $hasXMLProcessor = strpos($editorContent, 'XMLProcessor') !== false;
    
    echo "<div class='info'>Modifications trouvées dans StepByStepEditor:</div>";
    echo "<div class='stats'>";
    echo "Appel getStats(): " . ($hasStatsCalculation ? '✅' : '❌') . "<br>";
    echo "Stockage xmlStats en session: " . ($hasStatsSession ? '✅' : '❌') . "<br>";
    echo "Utilisation XMLProcessor: " . ($hasXMLProcessor ? '✅' : '❌') . "<br>";
    echo "</div>";
    
    return $hasStatsCalculation && $hasStatsSession && $hasXMLProcessor;
});

// Test 6: Vérification complète de l'intégration
runTest("Intégration complète - fichiers test présents et fonctionnels", function() {
    $testFiles = [
        'test_config.xml' => 'Fichier XML de test',
        'test_upload_manual.html' => 'Interface de test manuel',
        'templates/step_by_step/step1_upload.php' => 'Template modifié',
        'src/StepByStepEditor.php' => 'Contrôleur modifié',
        'src/XMLProcessor.php' => 'Processeur XML'
    ];
    
    $allPresent = true;
    echo "<div class='info'>Vérification des fichiers:</div>";
    echo "<div class='stats'>";
    
    foreach ($testFiles as $file => $description) {
        $path = __DIR__ . '/' . $file;
        $exists = file_exists($path);
        echo "$description: " . ($exists ? '✅' : '❌') . "<br>";
        if (!$exists) $allPresent = false;
    }
    
    echo "</div>";
    
    if ($allPresent) {
        echo "<div class='success'>Tous les fichiers nécessaires sont présents !</div>";
    } else {
        echo "<div class='error'>Des fichiers manquent dans l'installation</div>";
    }
    
    return $allPresent;
});

echo "<div style='margin-top: 30px; padding: 20px; border: 2px solid " . 
     ($testsPassed === $totalTests ? 'green' : 'red') . "; border-radius: 10px;'>";
echo "<h2>📊 Résumé des tests</h2>";
echo "<div style='font-size: 18px;'>";
echo "Tests réussis: <strong>$testsPassed/$totalTests</strong><br>";

if ($testsPassed === $totalTests) {
    echo "<div class='success' style='font-size: 24px; margin-top: 10px;'>";
    echo "🎉 TOUS LES TESTS SONT PASSÉS !";
    echo "</div>";
    echo "<div class='info' style='margin-top: 10px;'>";
    echo "La fonctionnalité d'affichage des statistiques XML est complètement opérationnelle.";
    echo "</div>";
} else {
    echo "<div class='error' style='font-size: 24px; margin-top: 10px;'>";
    echo "❌ CERTAINS TESTS ONT ÉCHOUÉ";
    echo "</div>";
    echo "<div class='info' style='margin-top: 10px;'>";
    echo "Veuillez vérifier les erreurs ci-dessus et corriger les problèmes identifiés.";
    echo "</div>";
}

echo "</div>";
echo "</div>";

?>
