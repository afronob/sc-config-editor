<?php
// Test simple des données de session
session_start();

echo "<h2>📊 Données de Session Step-by-Step</h2>";

if (!isset($_SESSION['stepByStep'])) {
    echo "<p>❌ Aucune session step-by-step trouvée</p>";
    echo "<a href='step_by_step_handler.php?step=1'>Démarrer l'éditeur</a>";
    exit;
}

$sessionData = $_SESSION['stepByStep'];

echo "<h3>🔍 Contenu de la session:</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
print_r($sessionData);
echo "</pre>";

echo "<h3>✅ Vérifications:</h3>";
echo "<ul>";
echo "<li>xmlName: " . (isset($sessionData['xmlName']) ? '✅ ' . htmlspecialchars($sessionData['xmlName']) : '❌ Non défini') . "</li>";
echo "<li>xmlData: " . (isset($sessionData['xmlData']) && !empty($sessionData['xmlData']) ? '✅ ' . strlen($sessionData['xmlData']) . ' bytes' : '❌ Non défini ou vide') . "</li>";
echo "<li>xmlStats: " . (isset($sessionData['xmlStats']) ? '✅ Présent' : '❌ Non défini') . "</li>";

if (isset($sessionData['xmlStats'])) {
    echo "<li>  - Total: " . $sessionData['xmlStats']['total'] . "</li>";
    echo "<li>  - Used: " . $sessionData['xmlStats']['used'] . "</li>";
}

echo "<li>currentStep: " . ($sessionData['currentStep'] ?? 'Non défini') . "</li>";
echo "<li>devices: " . (isset($sessionData['devices']) ? count($sessionData['devices']) . ' dispositifs' : 'Non défini') . "</li>";
echo "<li>modifications: " . (isset($sessionData['modifications']) ? count($sessionData['modifications']) . ' modifications' : 'Non défini') . "</li>";
echo "</ul>";

echo "<h3>🔧 Actions:</h3>";
echo "<a href='step_by_step_handler.php?action=restart' style='padding: 10px; background: #ff6b6b; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Reset Session</a>";
echo "<a href='step_by_step_handler.php?step=1' style='padding: 10px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Retour Étape 1</a>";
echo "<a href='javascript:window.close()' style='padding: 10px; background: #666; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Fermer</a>";
?>
