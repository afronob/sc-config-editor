#!/usr/bin/env php
<?php

echo "=== ANALYSE DES SUGGESTIONS DE MAPPING ===\n\n";

// Lire le fichier CSV
$suggestions = [];
$handle = fopen('mapping_suggestions.csv', 'r');

if (!$handle) {
    die("Erreur: Impossible d'ouvrir mapping_suggestions.csv\n");
}

// Ignorer l'en-tête
$header = fgetcsv($handle);
echo "En-tête trouvé: " . implode(', ', $header) . "\n\n";

// Lire les données
while (($row = fgetcsv($handle)) !== FALSE) {
    $suggestions[] = [
        'action_key' => $row[0],
        'suggested_label' => trim($row[1], '"'),
        'category' => trim($row[2], '"'),
        'confidence' => floatval($row[3])
    ];
}
fclose($handle);

echo "Total de suggestions chargées: " . count($suggestions) . "\n\n";

// Analyser par niveau de confiance
$high_confidence = [];  // 90%+
$medium_confidence = []; // 70-89%
$low_confidence = [];   // 50-69%
$very_low_confidence = []; // <50%

foreach ($suggestions as $s) {
    if ($s['confidence'] >= 90) {
        $high_confidence[] = $s;
    } elseif ($s['confidence'] >= 70) {
        $medium_confidence[] = $s;
    } elseif ($s['confidence'] >= 50) {
        $low_confidence[] = $s;
    } else {
        $very_low_confidence[] = $s;
    }
}

echo "=== RÉPARTITION DES SUGGESTIONS ===\n";
echo "HIGH (90%+):       " . count($high_confidence) . " suggestions\n";
echo "MEDIUM (70-89%):   " . count($medium_confidence) . " suggestions\n";
echo "LOW (50-69%):      " . count($low_confidence) . " suggestions\n";
echo "VERY LOW (<50%):   " . count($very_low_confidence) . " suggestions\n\n";

// Sauvegarder les mappings de haute confiance (auto-approuvés)
if (!empty($high_confidence)) {
    $handle = fopen('auto_approved_mappings.csv', 'w');
    fputcsv($handle, ['action_key', 'label', 'category', 'confidence']);
    
    foreach ($high_confidence as $mapping) {
        fputcsv($handle, [
            $mapping['action_key'],
            $mapping['suggested_label'],
            $mapping['category'],
            $mapping['confidence']
        ]);
    }
    fclose($handle);
    echo "✓ " . count($high_confidence) . " mappings de haute confiance sauvegardés dans 'auto_approved_mappings.csv'\n";
}

// Sauvegarder les mappings de confiance moyenne
if (!empty($medium_confidence)) {
    $handle = fopen('medium_confidence_mappings.csv', 'w');
    fputcsv($handle, ['action_key', 'suggested_label', 'category', 'confidence']);
    
    foreach ($medium_confidence as $mapping) {
        fputcsv($handle, [
            $mapping['action_key'],
            $mapping['suggested_label'],
            $mapping['category'],
            $mapping['confidence']
        ]);
    }
    fclose($handle);
    echo "⚠ " . count($medium_confidence) . " mappings de confiance moyenne sauvegardés dans 'medium_confidence_mappings.csv'\n";
}

// Afficher quelques exemples de chaque catégorie
echo "\n=== EXEMPLES DE MAPPINGS HAUTE CONFIANCE ===\n";
foreach (array_slice($high_confidence, 0, 5) as $mapping) {
    echo sprintf("%.1f%% | %s -> %s\n", 
        $mapping['confidence'], 
        $mapping['action_key'], 
        $mapping['suggested_label']
    );
}

echo "\n=== EXEMPLES DE MAPPINGS MOYENNE CONFIANCE ===\n";
foreach (array_slice($medium_confidence, 0, 5) as $mapping) {
    echo sprintf("%.1f%% | %s -> %s\n", 
        $mapping['confidence'], 
        $mapping['action_key'], 
        $mapping['suggested_label']
    );
}

echo "\n=== MAPPINGS NÉCESSITANT RÉVISION (< 70%) ===\n";
$needs_review = array_merge($low_confidence, $very_low_confidence);
foreach (array_slice($needs_review, 0, 10) as $mapping) {
    echo sprintf("%.1f%% | %s -> %s (%s)\n", 
        $mapping['confidence'], 
        $mapping['action_key'], 
        $mapping['suggested_label'],
        $mapping['category']
    );
}

echo "\n=== PROCHAINES ÉTAPES ===\n";
echo "1. Réviser auto_approved_mappings.csv (peut être intégré directement)\n";
echo "2. Réviser medium_confidence_mappings.csv (vérification recommandée)\n";
echo "3. Réviser manuellement les " . count($needs_review) . " mappings de faible confiance\n";

?>
