<?php
/**
 * Gestionnaire pour le processus d'édition étape par étape
 * Point d'entrée pour toutes les requêtes du workflow en 4 étapes
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/StepByStepEditor.php';

use SCConfigEditor\StepByStepEditor;

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration pour l'éditeur
$config = [
    'files_dir' => __DIR__ . '/files',
    'templates_dir' => __DIR__ . '/templates',
    'external_dir' => __DIR__ . '/external'
];

// Créer l'instance de l'éditeur étape par étape
$stepByStepEditor = new StepByStepEditor($config);

// Gérer les requêtes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'save_bindings':
                $device = $_POST['device'] ?? '';
                $bindings = json_decode($_POST['bindings'] ?? '[]', true);
                
                if (empty($device)) {
                    throw new Exception('Dispositif non spécifié');
                }
                
                // Sauvegarder les bindings en session
                if (!isset($_SESSION['stepByStep']['bindings'])) {
                    $_SESSION['stepByStep']['bindings'] = [];
                }
                
                $_SESSION['stepByStep']['bindings'][$device] = $bindings;
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Mappings sauvegardés avec succès'
                ]);
                break;
                
            case 'validate_xml':
                $xmlContent = $_POST['xml_content'] ?? '';
                
                if (empty($xmlContent)) {
                    throw new Exception('Contenu XML vide');
                }
                
                // Valider le XML
                $validation = $stepByStepEditor->validateXMLContent($xmlContent);
                
                echo json_encode([
                    'success' => $validation['valid'],
                    'message' => $validation['message'],
                    'details' => $validation['details'] ?? null
                ]);
                break;
                
            case 'detect_devices':
                // Détecter les dispositifs connectés
                $devices = $stepByStepEditor->detectConnectedDevices();
                
                echo json_encode([
                    'success' => true,
                    'devices' => $devices
                ]);
                break;
                
            default:
                throw new Exception('Action non reconnue');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Gérer les requêtes de téléchargement
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    try {
        $type = $_GET['type'] ?? 'xml';
        
        switch ($type) {
            case 'xml':
                $stepByStepEditor->downloadXML();
                break;
                
            case 'mappings':
                $stepByStepEditor->downloadMappings();
                break;
                
            default:
                throw new Exception('Type de téléchargement non reconnu');
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: step_by_step_handler.php?step=4');
        exit;
    }
    exit;
}

// Gérer la réinitialisation
if (isset($_GET['action']) && $_GET['action'] === 'restart') {
    $stepByStepEditor->resetSession();
    header('Location: step_by_step_handler.php?step=1');
    exit;
}

// Gérer la navigation entre les étapes
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Valider l'étape
if ($currentStep < 1 || $currentStep > 4) {
    $currentStep = 1;
}

// Vérifier si l'utilisateur peut accéder à cette étape
if (!$stepByStepEditor->canAccessStep($currentStep)) {
    // Rediriger vers l'étape 1 si pas accessible
    header("Location: step_by_step_handler.php?step=1");
    exit;
}

// Traiter les données de l'étape actuelle
try {
    $stepByStepEditor->processStep($currentStep);
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    // En cas d'erreur, rester sur l'étape actuelle
}

// Utiliser la méthode run() pour obtenir le contenu
echo $stepByStepEditor->run();
?>
