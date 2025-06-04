<?php
spl_autoload_register(function ($class) {
    // Conversion du namespace en chemin de fichier
    $prefix = 'SCConfigEditor\\';
    $base_dir = __DIR__ . '/src/';
    
    // Vérifier si la classe utilise le namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Récupérer le nom de fichier relatif
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Charger le fichier s'il existe
    if (file_exists($file)) {
        require $file;
    }
});

// Configuration de base
$config = [
    'templates_dir' => __DIR__ . '/templates',
    'files_dir' => __DIR__ . '/files',
    'external_dir' => __DIR__ . '/external',
    'debug' => true
];

// Fonction d'aide pour le rendu des templates
function render_template($name, $data = []) {
    global $config;
    
    // Si c'est une page complète (avec layout)
    if (!isset($data['partial']) || $data['partial'] !== true) {
        extract($data);
        ob_start();
        include $config['templates_dir'] . "/{$name}.php";
        $content = ob_get_clean();
        
        ob_start();
        include $config['templates_dir'] . '/layout.php';
        return ob_get_clean();
    }
    
    // Sinon, rendu partiel sans layout
    extract($data);
    ob_start();
    include $config['templates_dir'] . "/{$name}.php";
    return ob_get_clean();
}

// Fonction d'aide pour les erreurs
function show_error($message) {
    return render_template('error', ['message' => $message]);
}
