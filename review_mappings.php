<?php
/**
 * Script de révision et validation des suggestions de mapping
 * 
 * Ce script permet de :
 * 1. Réviser les suggestions par catégorie de confiance
 * 2. Valider ou rejeter les mappings
 * 3. Générer le fichier final de mappings approuvés
 */

// Charger les suggestions
$suggestions = [];
$handle = fopen('mapping_suggestions.csv', 'r');
$header = fgetcsv($handle); // Ignorer l'en-tête

while (($row = fgetcsv($handle)) !== FALSE) {
    $suggestions[] = [
        'action_key' => $row[0],
        'suggested_label' => trim($row[1], '"'),
        'category' => trim($row[2], '"'),
        'confidence' => floatval($row[3])
    ];
}
fclose($handle);

// Trier par score de confiance (décroissant)
usort($suggestions, function($a, $b) {
    return $b['confidence'] <=> $a['confidence'];
});

// Fonctions utilitaires
function getConfidenceLevel($confidence) {
    if ($confidence >= 90) return 'HIGH';
    if ($confidence >= 70) return 'MEDIUM';
    if ($confidence >= 50) return 'LOW';
    return 'VERY LOW';
}

function displaySuggestion($suggestion, $index) {
    $level = getConfidenceLevel($suggestion['confidence']);
    $color = match($level) {
        'HIGH' => "\033[32m",      // Vert
        'MEDIUM' => "\033[33m",    // Jaune
        'LOW' => "\033[36m",       // Cyan
        'VERY LOW' => "\033[31m"   // Rouge
    };
    $reset = "\033[0m";
    
    echo sprintf(
        "%s[%d] %s (%.1f%%) %s%s\n" .
        "    Action: %s\n" .
        "    Label:  %s\n" .
        "    Cat:    %s\n\n",
        $color, $index + 1, $level, $suggestion['confidence'], $reset,
        $suggestion['action_key'],
        $suggestion['action_key'],
        $suggestion['suggested_label'],
        $suggestion['category']
    );
}

// Mode interactif ou batch
$mode = $argv[1] ?? 'interactive';

if ($mode === 'interactive') {
    echo "=== RÉVISION INTERACTIVE DES MAPPINGS ===\n\n";
    echo "Commandes disponibles :\n";
    echo "  'y' - Approuver le mapping\n";
    echo "  'n' - Rejeter le mapping\n";
    echo "  's' - Ignorer (skip) ce mapping\n";
    echo "  'q' - Quitter\n";
    echo "  'batch' - Passer en mode batch automatique\n\n";
    
    $approved = [];
    $rejected = [];
    $skipped = [];
    
    foreach ($suggestions as $index => $suggestion) {
        displaySuggestion($suggestion, $index);
        
        echo "Décision [y/n/s/q/batch] : ";
        $input = trim(fgets(STDIN));
        
        switch (strtolower($input)) {
            case 'y':
                $approved[] = $suggestion;
                echo "✓ Approuvé\n\n";
                break;
            case 'n':
                $rejected[] = $suggestion;
                echo "✗ Rejeté\n\n";
                break;
            case 's':
                $skipped[] = $suggestion;
                echo "⏭ Ignoré\n\n";
                break;
            case 'q':
                echo "Arrêt de la révision.\n";
                exit;
            case 'batch':
                echo "Passage en mode batch...\n";
                $mode = 'batch';
                break;
        }
        
        if ($mode === 'batch') break;
    }
}

if ($mode === 'batch' || $mode === 'auto') {
    echo "=== MODE BATCH - APPROBATION AUTOMATIQUE ===\n\n";
    
    $approved = [];
    $needsReview = [];
    
    foreach ($suggestions as $suggestion) {
        if ($suggestion['confidence'] >= 90) {
            $approved[] = $suggestion;
            echo "✓ AUTO-APPROUVÉ: {$suggestion['action_key']} -> {$suggestion['suggested_label']} ({$suggestion['confidence']}%)\n";
        } elseif ($suggestion['confidence'] >= 70) {
            $approved[] = $suggestion;
            echo "✓ APPROUVÉ: {$suggestion['action_key']} -> {$suggestion['suggested_label']} ({$suggestion['confidence']}%)\n";
        } else {
            $needsReview[] = $suggestion;
            echo "⚠ RÉVISION NÉCESSAIRE: {$suggestion['action_key']} -> {$suggestion['suggested_label']} ({$suggestion['confidence']}%)\n";
        }
    }
    
    echo "\n=== RÉSUMÉ ===\n";
    echo "Auto-approuvés (90%+): " . count(array_filter($approved, fn($s) => $s['confidence'] >= 90)) . "\n";
    echo "Approuvés (70%+): " . count(array_filter($approved, fn($s) => $s['confidence'] >= 70 && $s['confidence'] < 90)) . "\n";
    echo "Nécessitent révision: " . count($needsReview) . "\n";
    
    // Sauvegarder les mappings approuvés
    if (!empty($approved)) {
        $handle = fopen('approved_mappings.csv', 'w');
        fputcsv($handle, ['action_key', 'label', 'category', 'confidence']);
        
        foreach ($approved as $mapping) {
            fputcsv($handle, [
                $mapping['action_key'],
                $mapping['suggested_label'],
                $mapping['category'],
                $mapping['confidence']
            ]);
        }
        fclose($handle);
        echo "\n✓ Mappings approuvés sauvegardés dans 'approved_mappings.csv'\n";
    }
    
    // Sauvegarder ceux qui nécessitent une révision
    if (!empty($needsReview)) {
        $handle = fopen('needs_review_mappings.csv', 'w');
        fputcsv($handle, ['action_key', 'suggested_label', 'category', 'confidence']);
        
        foreach ($needsReview as $mapping) {
            fputcsv($handle, [
                $mapping['action_key'],
                $mapping['suggested_label'],
                $mapping['category'],
                $mapping['confidence']
            ]);
        }
        fclose($handle);
        echo "⚠ Mappings nécessitant révision sauvegardés dans 'needs_review_mappings.csv'\n";
    }
}

echo "\n=== STATISTIQUES FINALES ===\n";
echo "Total suggestions analysées: " . count($suggestions) . "\n";
if (isset($approved)) {
    echo "Mappings approuvés: " . count($approved) . "\n";
}
if (isset($needsReview)) {
    echo "Mappings nécessitant révision: " . count($needsReview) . "\n";
}

?>
