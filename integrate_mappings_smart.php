#!/usr/bin/env php
<?php
/**
 * Script d'intégration intelligente des mappings avec sauvegarde
 */

echo "=== INTÉGRATION INTELLIGENTE DES MAPPINGS ===\n\n";

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
    die("Erreur: Impossible d'ouvrir $originalFile\n");
}

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) >= 2) {
        $existing_mappings[$row[0]] = $row[1];
    }
}
fclose($handle);

echo "Mappings existants chargés: " . count($existing_mappings) . "\n";

// Charger les suggestions
$suggestions = [];
$handle = fopen($suggestionsFile, 'r');
if (!$handle) {
    die("Erreur: Impossible d'ouvrir $suggestionsFile\n");
}

// Skip header
fgetcsv($handle);

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) >= 4) {
        $action = trim($row[0]);
        $suggested_label = trim($row[1]);
        $category = trim($row[2]);
        $confidence = floatval($row[3]);
        
        $suggestions[] = [
            'action' => $action,
            'suggested_label' => $suggested_label,
            'category' => $category,
            'confidence' => $confidence
        ];
    }
}
fclose($handle);

echo "Suggestions chargées: " . count($suggestions) . "\n\n";

// Filtrer par niveau de confiance
$high_confidence = array_filter($suggestions, function($s) { 
    return $s['confidence'] >= 80 && $s['suggested_label'] !== 'NON TROUVÉ'; 
});
$medium_confidence = array_filter($suggestions, function($s) { 
    return $s['confidence'] >= 60 && $s['confidence'] < 80 && $s['suggested_label'] !== 'NON TROUVÉ'; 
});
$low_confidence = array_filter($suggestions, function($s) { 
    return $s['confidence'] >= 30 && $s['confidence'] < 60 && $s['suggested_label'] !== 'NON TROUVÉ'; 
});

echo "=== RÉPARTITION PAR CONFIANCE ===\n";
echo "Haute confiance (≥80%): " . count($high_confidence) . "\n";
echo "Confiance moyenne (60-79%): " . count($medium_confidence) . "\n";
echo "Faible confiance (30-59%): " . count($low_confidence) . "\n\n";

// Traitement automatique des mappings haute confiance
echo "=== INTÉGRATION AUTOMATIQUE (HAUTE CONFIANCE) ===\n";
$integrated_count = 0;

foreach ($high_confidence as $mapping) {
    if (!isset($existing_mappings[$mapping['action']])) {
        $existing_mappings[$mapping['action']] = $mapping['suggested_label'];
        echo sprintf("✓ %-35s -> %-50s [%s%%]\n", 
            $mapping['action'], 
            $mapping['suggested_label'], 
            $mapping['confidence']
        );
        $integrated_count++;
    } else {
        echo sprintf("⚠ %-35s existe déjà\n", $mapping['action']);
    }
}

echo "\nMappings haute confiance intégrés: $integrated_count\n\n";

// Sauvegarder les mappings moyenne et faible confiance pour révision
if (!empty($medium_confidence)) {
    $handle = fopen('medium_confidence_for_review.csv', 'w');
    fputcsv($handle, ['action_key', 'suggested_label', 'category', 'confidence']);
    $medium_count = 0;
    foreach ($medium_confidence as $mapping) {
        if (!isset($existing_mappings[$mapping['action']])) {
            fputcsv($handle, [$mapping['action'], $mapping['suggested_label'], $mapping['category'], $mapping['confidence']]);
            $medium_count++;
        }
    }
    fclose($handle);
    echo "✓ $medium_count mappings moyenne confiance sauvegardés pour révision: medium_confidence_for_review.csv\n";
}

if (!empty($low_confidence)) {
    $handle = fopen('low_confidence_for_review.csv', 'w');
    fputcsv($handle, ['action_key', 'suggested_label', 'category', 'confidence']);
    $low_count = 0;
    foreach ($low_confidence as $mapping) {
        if (!isset($existing_mappings[$mapping['action']])) {
            fputcsv($handle, [$mapping['action'], $mapping['suggested_label'], $mapping['category'], $mapping['confidence']]);
            $low_count++;
        }
    }
    fclose($handle);
    echo "✓ $low_count mappings faible confiance sauvegardés pour révision: low_confidence_for_review.csv\n";
}

// Créer une sauvegarde datée
$timestamp = date('Y-m-d_H-i-s');
$backup_file = $originalFile . '.backup_' . $timestamp;
copy($originalFile, $backup_file);
echo "\n✓ Sauvegarde créée: $backup_file\n";

// Sauvegarder le fichier mis à jour
$handle = fopen($outputFile, 'w');
foreach ($existing_mappings as $action => $label) {
    fputcsv($handle, [$action, $label]);
}
fclose($handle);

echo "✓ Fichier mis à jour créé: $outputFile\n";

echo "\n=== RÉSUMÉ FINAL ===\n";
echo "Mappings originaux: " . (count($existing_mappings) - $integrated_count) . "\n";
echo "Nouveaux mappings intégrés (haute confiance): $integrated_count\n";
echo "Total final: " . count($existing_mappings) . "\n";
if (count($existing_mappings) - $integrated_count > 0) {
    echo "Amélioration: " . round(($integrated_count / (count($existing_mappings) - $integrated_count)) * 100, 1) . "%\n";
}

echo "\n=== ÉTAPES SUIVANTES ===\n";
echo "1. Réviser le fichier: $outputFile\n";
echo "2. Réviser les mappings moyenne confiance: medium_confidence_for_review.csv\n";
echo "3. Réviser les mappings faible confiance: low_confidence_for_review.csv\n";
echo "4. Si satisfait, remplacer le fichier original\n";
echo "5. Tester les nouveaux mappings dans l'application\n";

echo "\n✅ Intégration terminée avec succès !\n";
?>
