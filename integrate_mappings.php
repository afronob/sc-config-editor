#!/usr/bin/env php
<?php
/**
 * Script pour intégrer les nouveaux mappings dans le fichier principal de manière interactive
 */

echo "=== INTÉGRATION DES MAPPINGS AVEC SAUVEGARDES ===\n\n";

// Configuration
$originalFile = 'files/actions_keybind.csv';
$suggestionsFile = 'mapping_suggestions.csv';
$outputFile = 'files/actions_keybind_updated.csv';

// Vérifier l'existence des fichiers
if (!file_exists($originalFile)) {
    echo "ERREUR: Le fichier $originalFile n'existe pas\n";
    exit(1);
}

if (!file_exists($suggestionsFile)) {
    echo "ERREUR: Le fichier $suggestionsFile n'existe pas\n";
    exit(1);
}

// Charger les mappings existants
$existing_mappings = [];
$handle = fopen($originalFile, 'r');
if (!$handle) {
    die("Erreur: Impossible d'ouvrir files/actions_keybind.csv\n");
}

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) >= 2) {
        $existing_mappings[$row[0]] = $row[1];
    }
}
fclose($handle);

echo "Mappings existants chargés: " . count($existing_mappings) . "\n";

// Fonction pour intégrer un fichier de mappings
function integrate_mappings($filename, $confidence_level) {
    global $existing_mappings;
    
    if (!file_exists($filename)) {
        echo "⚠ Fichier $filename non trouvé\n";
        return 0;
    }
    
    $handle = fopen($filename, 'r');
    $header = fgetcsv($handle); // Ignorer l'en-tête
    
    $new_mappings = 0;
    $updated_mappings = 0;
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        $action_key = $row[0];
        $suggested_label = trim($row[1], '"');
        $confidence = floatval($row[3]);
        
        if (isset($existing_mappings[$action_key])) {
            echo "⚠ Action '$action_key' existe déjà avec le label '{$existing_mappings[$action_key]}'\n";
            echo "   Suggestion ($confidence%): '$suggested_label'\n";
            $updated_mappings++;
        } else {
            $existing_mappings[$action_key] = $suggested_label;
            echo "✓ Nouveau mapping: $action_key -> $suggested_label ($confidence%)\n";
            $new_mappings++;
        }
    }
    fclose($handle);
    
    echo "\n$confidence_level: $new_mappings nouveaux, $updated_mappings existants\n\n";
    return $new_mappings;
}

// Intégrer les mappings par ordre de confiance
$total_new = 0;

echo "=== INTÉGRATION DES MAPPINGS HAUTE CONFIANCE (90%+) ===\n";
$total_new += integrate_mappings('high_confidence_mappings.csv', 'HAUTE CONFIANCE');

echo "=== INTÉGRATION DES MAPPINGS MOYENNE CONFIANCE (70-89%) ===\n";
$total_new += integrate_mappings('medium_confidence_mappings.csv', 'MOYENNE CONFIANCE');

// Pour les mappings de faible confiance, juste un résumé
if (file_exists('low_confidence_mappings.csv')) {
    $handle = fopen('low_confidence_mappings.csv', 'r');
    $header = fgetcsv($handle);
    $low_count = 0;
    while (($row = fgetcsv($handle)) !== FALSE) {
        $low_count++;
    }
    fclose($handle);
    echo "=== MAPPINGS FAIBLE CONFIANCE (<70%) ===\n";
    echo "⚠ $low_count mappings nécessitent une révision manuelle\n";
    echo "   Voir le fichier 'low_confidence_mappings.csv'\n\n";
}

// Sauvegarder le fichier mis à jour
$backup_file = 'files/actions_keybind_backup_' . date('Y-m-d_H-i-s') . '.csv';
copy('files/actions_keybind.csv', $backup_file);
echo "✓ Sauvegarde créée: $backup_file\n";

// Écrire le nouveau fichier
$handle = fopen('files/actions_keybind_updated.csv', 'w');
foreach ($existing_mappings as $action => $label) {
    fputcsv($handle, [$action, $label]);
}
fclose($handle);

echo "✓ Nouveau fichier créé: files/actions_keybind_updated.csv\n";
echo "\n=== RÉSUMÉ ===\n";
echo "Total de nouveaux mappings intégrés: $total_new\n";
echo "Total de mappings dans le fichier: " . count($existing_mappings) . "\n";

// Statistiques détaillées
echo "\n=== ACTIONS RECOMMANDÉES ===\n";
echo "1. Réviser files/actions_keybind_updated.csv\n";
echo "2. Si satisfait, remplacer files/actions_keybind.csv par la version mise à jour\n";
echo "3. Réviser manuellement low_confidence_mappings.csv\n";
echo "4. Tester les nouveaux mappings dans l'application\n";

?>
