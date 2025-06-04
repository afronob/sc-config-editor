<?php
// Configuration globale de l'application

class Config {
    // Chemins des dossiers
    const TEMPLATES_DIR = __DIR__ . '/templates';
    const FILES_DIR = __DIR__ . '/files';
    const EXTERNAL_DIR = __DIR__ . '/external';
    
    // Extensions autorisées
    const ALLOWED_EXTENSIONS = ['xml'];
    
    // Configuration des joysticks
    const JOYSTICK_MAPPING_FILES = [
        'vkb_231d_0201_map.json',
        'vkb_231d_0120_map.json',
        'vjoy_1234_bead_map.json'
    ];
    
    // Noms des fichiers de debug
    const DEBUG_ACTIONS_LOG = 'debug_actions.log';
    const DEBUG_INPUTS_LOG = 'debug_inputs.log';
    
    // Obtenir les chemins complets des fichiers de mapping
    public static function getJoystickMappingPaths() {
        return array_map(function($file) {
            return self::FILES_DIR . '/' . $file;
        }, self::JOYSTICK_MAPPING_FILES);
    }
    
    // Charger un fichier de mapping
    public static function loadJoystickMapping($file) {
        $path = self::FILES_DIR . '/' . $file;
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return null;
    }
}
