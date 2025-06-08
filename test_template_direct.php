<?php
// Test direct du template avec les statistiques

// Préparer les données pour le template
$data = [
    'step' => 1,
    'title' => 'Étape 1: Upload du fichier XML',
    'description' => 'Test avec statistiques',
    'success' => 'Fichier XML chargé avec succès',
    'xmlName' => 'test_config.xml',
    'xmlStats' => ['total' => 10, 'used' => 6]
];

// Fonction de rendu simplifiée
function render_test_template($name, $data = []) {
    extract($data);
    ob_start();
    include __DIR__ . "/templates/{$name}.php";
    return ob_get_clean();
}

echo "=== TEST DIRECT DU TEMPLATE AVEC STATISTIQUES ===\n";

// Rendre le template
$result = render_test_template('step_by_step/step1_upload', $data);

// Vérifications
echo "1. Template rendu: ";
echo !empty($result) ? "✅\n" : "❌\n";

echo "2. Variables xmlName et xmlStats présentes: ";
echo (strpos($result, 'test_config.xml') !== false && strpos($result, '10') !== false && strpos($result, '6') !== false) ? "✅\n" : "❌\n";

echo "3. Section file-uploaded-info présente: ";
echo strpos($result, 'file-uploaded-info') !== false ? "✅\n" : "❌\n";

echo "4. Message de succès présent: ";
echo strpos($result, 'Fichier chargé avec succès') !== false ? "✅\n" : "❌\n";

echo "5. Lien vers étape 2 présent: ";
echo strpos($result, 'step=2') !== false ? "✅\n" : "❌\n";

// Extraire et afficher la section des statistiques
echo "\n=== SECTION DES STATISTIQUES ===\n";
if (preg_match('/<div class="file-uploaded-info">.*?<\/div>\s*<\/div>/s', $result, $matches)) {
    echo "Section trouvée :\n";
    echo $matches[0] . "\n";
} else {
    echo "❌ Section file-uploaded-info non trouvée\n";
}

echo "\n=== RECHERCHE DES CONDITIONS PHP ===\n";
echo "Vérification des conditions dans le template...\n";

// Lire le template et vérifier les conditions
$templateContent = file_get_contents(__DIR__ . '/templates/step_by_step/step1_upload.php');

echo "Condition xmlName et xmlStats: ";
echo strpos($templateContent, '<?php if (isset($xmlName) && isset($xmlStats)): ?>') !== false ? "✅\n" : "❌\n";

echo "Affichage du nom: ";
echo strpos($templateContent, '<?= htmlspecialchars($xmlName) ?>') !== false ? "✅\n" : "❌\n";

echo "Affichage des stats total: ";
echo strpos($templateContent, '$xmlStats[\'total\']') !== false ? "✅\n" : "❌\n";

echo "Affichage des stats used: ";
echo strpos($templateContent, '$xmlStats[\'used\']') !== false ? "✅\n" : "❌\n";

echo "\n=== FIN DU TEST ===\n";
?>
