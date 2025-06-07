<?php
// Script pour analyser et mapper les actions manquantes avec leurs libellés

echo "=== ANALYSE DES ACTIONS MANQUANTES ===\n\n";

// Vérifier l'existence du fichier
if (!file_exists('missing_actions.txt')) {
    echo "ERREUR: Le fichier missing_actions.txt n'existe pas\n";
    exit(1);
}

// Charger les actions manquantes
$missingActions = array_filter(array_map('trim', file('missing_actions.txt')));
echo "Nombre d'actions manquantes: " . count($missingActions) . "\n\n";

// Afficher les premières actions pour debug
echo "Premières actions manquantes:\n";
for ($i = 0; $i < min(5, count($missingActions)); $i++) {
    echo "- " . $missingActions[$i] . "\n";
}
echo "\n";

// Charger le fichier des catégories et actions
$categoriesActions = [];
if (($handle = fopen('files/sc_categories_actions.csv', 'r')) !== false) {
    // Skip header
    fgetcsv($handle);
    
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) >= 2) {
            $category = trim($data[0], '"');
            $action = trim($data[1], '"');
            $categoriesActions[] = ['category' => $category, 'action' => $action];
        }
    }
    fclose($handle);
}

echo "Nombre de libellés disponibles: " . count($categoriesActions) . "\n\n";

// Fonction pour calculer la similarité entre deux chaînes
function similarity($str1, $str2) {
    // Normaliser les chaînes
    $str1 = strtolower(str_replace(['_', '-', ' '], '', $str1));
    $str2 = strtolower(str_replace(['_', '-', ' '], '', $str2));
    
    // Calculer la similarité
    similar_text($str1, $str2, $percent);
    return $percent;
}

// Analyser chaque action manquante
$mappingSuggestions = [];

foreach ($missingActions as $missingAction) {
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($categoriesActions as $item) {
        $score = similarity($missingAction, $item['action']);
        
        if ($score > $bestScore && $score > 30) { // Seuil de similarité
            $bestScore = $score;
            $bestMatch = $item;
        }
    }
    
    if ($bestMatch) {
        $mappingSuggestions[] = [
            'action' => $missingAction,
            'suggested_label' => $bestMatch['action'],
            'category' => $bestMatch['category'],
            'confidence' => round($bestScore, 1)
        ];
    } else {
        $mappingSuggestions[] = [
            'action' => $missingAction,
            'suggested_label' => 'NON TROUVÉ',
            'category' => '',
            'confidence' => 0
        ];
    }
}

// Trier par confiance décroissante
usort($mappingSuggestions, function($a, $b) {
    return $b['confidence'] <=> $a['confidence'];
});

// Afficher les résultats
echo "=== SUGGESTIONS DE MAPPING ===\n\n";

foreach ($mappingSuggestions as $suggestion) {
    echo sprintf("%-35s -> %-50s [%s%%]\n", 
        $suggestion['action'], 
        $suggestion['suggested_label'], 
        $suggestion['confidence']
    );
    if ($suggestion['category'] && $suggestion['confidence'] > 0) {
        echo sprintf("%-35s    Catégorie: %s\n", '', $suggestion['category']);
    }
    echo "\n";
}

// Compter les matches trouvés
$foundMatches = array_filter($mappingSuggestions, function($item) {
    return $item['confidence'] > 0;
});

echo "\n=== STATISTIQUES ===\n";
echo "Actions avec correspondances trouvées: " . count($foundMatches) . "/" . count($missingActions) . "\n";
echo "Actions sans correspondances: " . (count($missingActions) - count($foundMatches)) . "\n";

// Sauvegarder les suggestions dans un fichier CSV
$csvFile = fopen('mapping_suggestions.csv', 'w');
fputcsv($csvFile, ['action_key', 'suggested_label', 'category', 'confidence']);

foreach ($mappingSuggestions as $suggestion) {
    fputcsv($csvFile, [
        $suggestion['action'],
        $suggestion['suggested_label'],
        $suggestion['category'],
        $suggestion['confidence']
    ]);
}
fclose($csvFile);

echo "Suggestions sauvegardées dans mapping_suggestions.csv\n";
?>
