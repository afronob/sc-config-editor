<?php
session_start();

// Simuler une session avec des données XML uploadées
$_SESSION['step_by_step'] = [
    'xmlData' => file_get_contents('test_config.xml'),
    'xmlName' => 'test_config.xml',
    'xmlStats' => ['total' => 10, 'used' => 6]
];

// Inclure le nécessaire
require_once 'bootstrap.php';

// Créer l'éditeur avec la config
$config = require 'config.php';
$editor = new StepByStepEditor($config);

// Tester le rendu de l'étape 1 avec les statistiques
echo "=== TEST RENDU ÉTAPE 1 AVEC STATISTIQUES ===\n";

// Réfléchir la classe pour appeler la méthode privée renderStep1
$reflection = new ReflectionClass($editor);
$renderStep1Method = $reflection->getMethod('renderStep1');
$renderStep1Method->setAccessible(true);

// Rendre l'étape 1 avec succès
$result = $renderStep1Method->invoke($editor, ['success' => 'Fichier XML chargé avec succès']);

// Vérifier si les variables sont bien présentes dans le template
if (strpos($result, 'xmlName') !== false && strpos($result, 'xmlStats') !== false) {
    echo "✅ Les variables xmlName et xmlStats sont présentes dans le template\n";
} else {
    echo "❌ Les variables xmlName et xmlStats ne sont pas présentes dans le template\n";
}

// Vérifier si la section file-uploaded-info est visible
if (strpos($result, 'file-uploaded-info') !== false) {
    echo "✅ La section file-uploaded-info est présente\n";
} else {
    echo "❌ La section file-uploaded-info n'est pas présente\n";
}

// Vérifier les valeurs statistiques
if (strpos($result, '10') !== false && strpos($result, '6') !== false) {
    echo "✅ Les valeurs statistiques (10 total, 6 utilisées) sont affichées\n";
} else {
    echo "❌ Les valeurs statistiques ne sont pas affichées\n";
}

// Afficher un extrait du HTML généré pour déboguer
echo "\n=== EXTRAIT DU HTML GÉNÉRÉ ===\n";
$lines = explode("\n", $result);
$inStatsSection = false;
foreach ($lines as $line) {
    if (strpos($line, 'file-uploaded-info') !== false) {
        $inStatsSection = true;
    }
    if ($inStatsSection) {
        echo $line . "\n";
        if (strpos($line, '</div>') !== false && strpos($line, 'file-uploaded-info') === false) {
            $inStatsSection = false;
        }
    }
}

echo "\n=== FIN DU TEST ===\n";
?>
