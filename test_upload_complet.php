<?php
echo "=== TEST UPLOAD ÉTAPE 1 AVEC SIMULATION ===\n";

// Simuler un upload de fichier
$testXMLPath = 'test_config.xml';

if (!file_exists($testXMLPath)) {
    echo "❌ Fichier test_config.xml non trouvé\n";
    exit;
}

// Créer un fichier temporaire pour simuler $_FILES
$tmpFile = tempnam(sys_get_temp_dir(), 'upload_test');
copy($testXMLPath, $tmpFile);

// Simuler $_FILES et $_GET
$_FILES = [
    'xmlfile' => [
        'tmp_name' => $tmpFile,
        'name' => 'test_config.xml',
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmpFile),
        'type' => 'text/xml'
    ]
];

$_GET = [
    'step' => '1',
    'action' => 'upload'
];

$_POST = ['action' => 'upload'];

session_start();

// Inclure le nécessaire
require_once 'bootstrap.php';

// Créer l'éditeur avec la config
$config = [
    'files_dir' => __DIR__ . '/files',
    'templates_dir' => __DIR__ . '/templates',
    'external_dir' => __DIR__ . '/external'
];

$editor = new \SCConfigEditor\StepByStepEditor($config);

echo "✅ Éditeur créé\n";

// Traiter l'étape 1 avec upload
echo "🔄 Traitement de l'upload...\n";

$result = $editor->run();

echo "✅ Upload traité\n";

// Vérifier si les statistiques sont dans la session
$sessionData = $_SESSION['stepByStep'] ?? [];

echo "\n=== VÉRIFICATION SESSION ===\n";
echo "xmlName: " . (isset($sessionData['xmlName']) ? '✅ ' . $sessionData['xmlName'] : '❌') . "\n";
echo "xmlData: " . (isset($sessionData['xmlData']) ? '✅ ' . strlen($sessionData['xmlData']) . ' bytes' : '❌') . "\n";
echo "xmlStats: " . (isset($sessionData['xmlStats']) ? '✅' : '❌') . "\n";

if (isset($sessionData['xmlStats'])) {
    echo "  - Total: " . $sessionData['xmlStats']['total'] . "\n";
    echo "  - Used: " . $sessionData['xmlStats']['used'] . "\n";
}

// Vérifier le HTML généré
echo "\n=== VÉRIFICATION HTML ===\n";
if (strpos($result, 'file-uploaded-info') !== false) {
    echo "✅ Section file-uploaded-info présente\n";
} else {
    echo "❌ Section file-uploaded-info manquante\n";
}

if (strpos($result, 'test_config.xml') !== false) {
    echo "✅ Nom du fichier affiché\n";
} else {
    echo "❌ Nom du fichier non affiché\n";
}

// Extraire les statistiques du HTML
if (preg_match('/Actions trouvées:.*?(\d+)/', $result, $matches)) {
    echo "✅ Actions trouvées: " . $matches[1] . "\n";
} else {
    echo "❌ Actions trouvées non affichées\n";
}

if (preg_match('/Actions configurées:.*?(\d+)/', $result, $matches)) {
    echo "✅ Actions configurées: " . $matches[1] . "\n";
} else {
    echo "❌ Actions configurées non affichées\n";
}

// Nettoyer
unlink($tmpFile);

echo "\n=== FIN DU TEST ===\n";
?>
