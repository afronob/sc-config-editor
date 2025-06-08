<?php

declare(strict_types=1);

namespace SCConfigEditor;

use SCConfigEditor\DeviceManager;
use SCConfigEditor\XMLProcessor;

/**
 * Contrôleur pour le workflow d'édition XML en 4 étapes
 * 
 * Step 1: Upload du fichier XML de configuration SC
 * Step 2: Reconnaissance des devices connectés + Ajout de nouveaux devices
 * Step 3: Édition du fichier de configuration 
 * Step 4: Récapitulatif et téléchargement
 */
class StepByStepEditor {
    private $config;
    private $deviceManager;
    private $xmlProcessor;
    private $sessionData;
    private ?RedisManager $redisManager;
    private string $sessionId;
    private bool $useRedis;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->deviceManager = new DeviceManager($config["files_dir"]);
        $this->xmlProcessor = new XMLProcessor();
        
        // Initialiser Redis si disponible
        $this->initializeRedis();
        $this->initializeSession();
    }
    
    private function initializeRedis(): void
    {
        $this->useRedis = false;
        $this->redisManager = null;
        
        // Vérifier si Redis est configuré
        if (isset($this->config['redis']) && $this->config['redis']['enabled']) {
            try {
                $this->redisManager = new RedisManager($this->config['redis']);
                if ($this->redisManager->isConnected()) {
                    $this->useRedis = true;
                    error_log("StepByStepEditor: Redis activé et connecté");
                }
            } catch (\Exception $e) {
                error_log("StepByStepEditor: Erreur Redis - " . $e->getMessage());
                // Fallback sur les sessions PHP
            }
        }
    }
    
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->sessionId = session_id();
        
        if ($this->useRedis) {
            // Utiliser Redis pour le stockage des sessions
            $sessionData = $this->redisManager->getStepByStepSession($this->sessionId);
            
            if ($sessionData === null) {
                $sessionData = [
                    'currentStep' => 1,
                    'xmlData' => null,
                    'xmlName' => null,
                    'devices' => [],
                    'newDevices' => [],
                    'modifications' => [],
                    'completed' => false,
                    'created_at' => date('c'),
                    'last_activity' => date('c')
                ];
                
                $this->redisManager->saveStepByStepSession($this->sessionId, $sessionData);
            }
            
            $this->sessionData = $sessionData;
        } else {
            // Fallback sur les sessions PHP classiques
            if (!isset($_SESSION['stepByStep'])) {
                $_SESSION['stepByStep'] = [
                    'currentStep' => 1,
                    'xmlData' => null,
                    'xmlName' => null,
                    'devices' => [],
                    'newDevices' => [],
                    'modifications' => [],
                    'completed' => false
                ];
            }
            
            $this->sessionData = &$_SESSION['stepByStep'];
        }
    }
    
    /**
     * Vérifie si l'utilisateur peut accéder à une étape donnée
     */
    public function canAccessStep(int $step): bool {
        switch ($step) {
            case 1:
                return true; // L'étape 1 est toujours accessible
            case 2:
                return isset($this->sessionData['xmlData']) && !empty($this->sessionData['xmlData']);
            case 3:
                return $this->canAccessStep(2) && 
                       isset($this->sessionData['devices']) && 
                       !empty($this->sessionData['devices']);
            case 4:
                return $this->canAccessStep(3) && 
                       isset($this->sessionData['mappings']) && 
                       !empty($this->sessionData['mappings']);
            default:
                return false;
        }
    }
        
    /**
     * Traite les données soumises pour une étape donnée
     */
    public function processStep(int $step): void {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'show';
        
        switch ($step) {
            case 1:
                $this->processStep1($action);
                break;
            case 2:
                $this->processStep2($action);
                break;
            case 3:
                $this->processStep3($action);
                break;
            case 4:
                $this->processStep4($action);
                break;
        }
    }
    
    private function processStep1(string $action): void {
        // Traitement pour l'étape 1 - Upload XML
        if ($action === 'upload' && isset($_FILES['xml_file'])) {
            $this->handleFileUpload($_FILES['xml_file']);
        }
    }
    
    private function processStep2(string $action): void {
        // Traitement pour l'étape 2 - Validation XML
        if ($action === 'validate' && isset($_POST['xml_content'])) {
            $result = $this->validateXMLContent($_POST['xml_content']);
            $this->sessionData['validation_result'] = $result;
        }
    }
    
    private function processStep3(string $action): void {
        // Traitement pour l'étape 3 - Périphériques
        if ($action === 'detect') {
            $this->sessionData['devices'] = $this->detectConnectedDevices();
        } elseif ($action === 'add_device' && isset($_POST['device_name'])) {
            $this->addDevice($_POST['device_name']);
        }
    }
    
    private function processStep4(string $action): void {
        // Traitement pour l'étape 4 - Génération mappings
        if ($action === 'generate') {
            $this->generateMappings();
        } elseif ($action === 'download') {
            $this->downloadMappings();
        }
    }
        
    public function run() {
        $step = $this->getCurrentStep();
        $action = $_GET['action'] ?? 'show';
        
        switch ($step) {
            case 1:
                return $this->handleStep1($action);
            case 2:
                return $this->handleStep2($action);
            case 3:
                return $this->handleStep3($action);
            case 4:
                return $this->handleStep4($action);
            default:
                return $this->resetAndShowStep1();
        }
    }
    
    private function getCurrentStep(): int {
        return (int)($_GET['step'] ?? $this->sessionData['currentStep']);
    }
    
    /**
     * STEP 1: Upload du fichier XML de configuration Star Citizen
     */
    private function handleStep1(string $action) {
        if ($action === 'upload' && $_SERVER["REQUEST_METHOD"] === "POST") {
            return $this->processXMLUpload();
        }
        
        return $this->renderStep1();
    }
    
    private function processXMLUpload() {
        try {
            if (!isset($_FILES["xmlfile"]) || $_FILES["xmlfile"]["error"] !== UPLOAD_ERR_OK) {
                throw new \Exception("Erreur lors de l'upload du fichier XML");
            }
            
            $xmlFile = $_FILES["xmlfile"]["tmp_name"];
            $xmlName = basename($_FILES["xmlfile"]["name"]);
            
            // Validation du fichier XML
            if (!$this->isValidStarCitizenXML($xmlFile)) {
                throw new \Exception("Le fichier uploadé n'est pas un fichier de configuration Star Citizen valide");
            }
            
            // Sauvegarder dans la session
            $this->sessionData['xmlData'] = file_get_contents($xmlFile);
            $this->sessionData['xmlName'] = $xmlName;
            
            // Initialiser le processeur XML et récupérer les statistiques
            $this->xmlProcessor = new XMLProcessor($xmlFile, $xmlName);
            $stats = $this->xmlProcessor->getStats();
            $devices = $this->xmlProcessor->getJoysticks();
            $this->sessionData['xmlStats'] = $stats;
            $this->sessionData['xmlDevices'] = $devices;
            
            // Rester sur l'étape 1 pour afficher les statistiques
            return $this->renderStep1(['success' => 'Fichier XML chargé avec succès']);
            
        } catch (\Exception $e) {
            return $this->renderStep1(['error' => $e->getMessage()]);
        }
    }
    
    private function isValidStarCitizenXML(string $filePath): bool {
        $content = file_get_contents($filePath);
        return strpos($content, '<ActionMaps') !== false && 
               strpos($content, 'version=') !== false;
    }
    
    private function renderStep1(array $data = []) {
        $data['step'] = 1;
        $data['title'] = 'Étape 1: Upload du fichier XML';
        $data['description'] = 'Chargez votre fichier de configuration Star Citizen (USER/game/data/controls/mappings/)';
        
        // Ajouter les statistiques d'actions si disponibles
        if (isset($this->sessionData['xmlStats'])) {
            $data['xmlStats'] = $this->sessionData['xmlStats'];
        }
        if (isset($this->sessionData['xmlDevices'])) {
            $data['xmlDevices'] = $this->sessionData['xmlDevices'];
        }
        if (isset($this->sessionData['xmlName'])) {
            $data['xmlName'] = $this->sessionData['xmlName'];
        }
        
        return render_template("step_by_step/step1_upload", $data);
    }
    
    /**
     * STEP 2: Reconnaissance des devices + Gestion des nouveaux devices
     */
    private function handleStep2(string $action) {
        if (!$this->sessionData['xmlData']) {
            return $this->redirectToStep(1, 'error', 'Aucun fichier XML chargé');
        }
        
        switch ($action) {
            case 'detect':
                return $this->detectDevices();
            case 'add_device':
                return $this->addNewDevice();
            case 'next':
                return $this->proceedToStep3();
            default:
                return $this->renderStep2();
        }
    }
    
    private function detectDevices() {
        try {
            // Créer un fichier temporaire pour le XML
            $xmlFile = tempnam(sys_get_temp_dir(), "xml_step2");
            file_put_contents($xmlFile, $this->sessionData['xmlData']);
            
            $this->xmlProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
            $joysticks = $this->xmlProcessor->getJoysticks();
            
            // Détecter les devices connectés
            $connectedDevices = $this->detectConnectedDevices();
            $knownDevices = $this->deviceManager->matchDevicesToXML($joysticks);
            
            // Identifier les nouveaux devices
            $newDevices = $this->identifyNewDevices($connectedDevices, $knownDevices);
            
            $this->sessionData['devices'] = $knownDevices;
            $this->sessionData['newDevices'] = $newDevices;
            
            unlink($xmlFile);
            
            return $this->renderStep2([
                'detected' => true,
                'devices' => $knownDevices,
                'newDevices' => $newDevices,
                'connectedDevices' => $connectedDevices
            ]);
            
        } catch (\Exception $e) {
            return $this->renderStep2(['error' => $e->getMessage()]);
        }
    }
    
    private function identifyNewDevices(array $connected, array $known): array {
        $newDevices = [];
        
        foreach ($connected as $device) {
            $isKnown = false;
            foreach ($known as $knownDevice) {
                if ($device['name'] === $knownDevice['name'] || 
                    $device['guid'] === $knownDevice['guid']) {
                    $isKnown = true;
                    break;
                }
            }
            
            if (!$isKnown) {
                $newDevices[] = $device;
            }
        }
        
        return $newDevices;
    }
    
    private function addNewDevice() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $deviceData = $_POST['device'];
                
                // Étape 1: Créer le mapping pour le nouveau device si demandé
                $mappingResult = null;
                if (isset($_POST['create_mapping']) && $_POST['create_mapping'] === '1') {
                    $mappingResult = $this->createDeviceMapping($deviceData);
                }
                
                // Étape 2: Ajouter au XML si demandé
                if (isset($_POST['add_to_xml']) && $_POST['add_to_xml'] === '1') {
                    $this->addDeviceToXML($deviceData);
                }
                
                $successMessage = 'Device ajouté avec succès';
                if ($mappingResult) {
                    $successMessage .= ' - Mapping créé : ' . $mappingResult['filename'];
                }
                
                return $this->renderStep2([
                    'success' => $successMessage,
                    'newMapping' => $mappingResult
                ]);
                
            } catch (\Exception $e) {
                return $this->renderStep2(['error' => $e->getMessage()]);
            }
        }
        
        return $this->renderStep2();
    }
    
    private function addDeviceToXML(array $deviceData) {
        // Créer un fichier temporaire pour le XML
        $xmlFile = tempnam(sys_get_temp_dir(), "xml_add_device");
        file_put_contents($xmlFile, $this->sessionData['xmlData']);
        
        $this->xmlProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
        
        // Préparer les données du dispositif avec toutes les informations nécessaires
        $deviceXmlData = [
            'instance' => $deviceData['instance'] ?? $deviceData['xml_instance'] ?? '3',
            'name' => $deviceData['name'],
            'guid' => $deviceData['guid'] ?? '',
            'product' => $deviceData['name'],
            'mapping_template' => $deviceData['mapping_template'] ?? 'generic',
            'buttons' => isset($deviceData['buttons']) ? (int)$deviceData['buttons'] : 8,
            'axes' => isset($deviceData['axes']) ? (int)$deviceData['axes'] : 4,
            'hats' => isset($deviceData['hats']) ? (int)$deviceData['hats'] : 0
        ];
        
        // Ajouter le dispositif au XML avec toutes les balises nécessaires
        $this->xmlProcessor->addCompleteDevice($deviceXmlData);
        
        // Mettre à jour le XML en session
        $this->sessionData['xmlData'] = $this->xmlProcessor->getXML()->asXML();
        
        // Mettre à jour les statistiques XML
        $this->updateXmlStats();
        
        unlink($xmlFile);
    }
    
    private function updateXmlStats() {
        // Recalculer les statistiques XML après modification
        $xmlFile = tempnam(sys_get_temp_dir(), "xml_stats");
        file_put_contents($xmlFile, $this->sessionData['xmlData']);
        
        $tempProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
        $stats = $tempProcessor->getStats();
        
        $this->sessionData['xmlStats'] = [
            'total' => $stats['total'],
            'used' => $stats['used']
        ];
        
        // Mettre à jour aussi les dispositifs XML
        $joysticks = $tempProcessor->getJoysticks();
        $this->sessionData['xmlDevices'] = array_map(function($joystick) {
            return [
                'instance' => $joystick['instance'],
                'product' => $joystick['product'] ?: 'Unknown'
            ];
        }, $joysticks);
        
        unlink($xmlFile);
    }
    
    private function proceedToStep3() {
        $this->sessionData['currentStep'] = 3;
        return $this->redirectToStep(3, 'info', 'Prêt pour l\'édition de la configuration');
    }
    
    private function renderStep2(array $data = []) {
        $data['step'] = 2;
        $data['title'] = 'Étape 2: Reconnaissance des Dispositifs';
        $data['description'] = 'Détection automatique des manettes connectées et gestion des nouveaux dispositifs';
        $data['devices'] = $this->sessionData['devices'] ?? [];
        $data['newDevices'] = $this->sessionData['newDevices'] ?? [];
        
        // Ajouter les données XML pour l'encart contextuel
        if (isset($this->sessionData['xmlStats'])) {
            $data['xmlStats'] = $this->sessionData['xmlStats'];
        }
        if (isset($this->sessionData['xmlDevices'])) {
            $data['xmlDevices'] = $this->sessionData['xmlDevices'];
        }
        if (isset($this->sessionData['xmlName'])) {
            $data['xmlName'] = $this->sessionData['xmlName'];
        }
        
        return render_template("step_by_step/step2_devices", $data);
    }
    
    /**
     * STEP 3: Édition du fichier de configuration
     */
    private function handleStep3(string $action) {
        if (!$this->sessionData['xmlData']) {
            return $this->redirectToStep(1, 'error', 'Session expirée - Rechargez votre fichier XML');
        }
        
        switch ($action) {
            case 'save_binding':
                return $this->saveBinding();
            case 'next':
                return $this->proceedToStep4();
            default:
                return $this->renderStep3();
        }
    }
    
    private function saveBinding() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                // Sauvegarder la modification en session
                $modification = [
                    'timestamp' => time(),
                    'action' => $_POST['action'] ?? '',
                    'device' => $_POST['device'] ?? '',
                    'input' => $_POST['input'] ?? '',
                    'previous_value' => $_POST['previous_value'] ?? ''
                ];
                
                $this->sessionData['modifications'][] = $modification;
                
                // Appliquer la modification au XML
                $this->applyModificationToXML($modification);
                
                return $this->renderStep3([
                    'success' => 'Binding sauvegardé',
                    'modification' => $modification
                ]);
                
            } catch (\Exception $e) {
                return $this->renderStep3(['error' => $e->getMessage()]);
            }
        }
        
        return $this->renderStep3();
    }
    
    private function applyModificationToXML(array $modification) {
        $xmlFile = tempnam(sys_get_temp_dir(), "xml_modify");
        file_put_contents($xmlFile, $this->sessionData['xmlData']);
        
        $this->xmlProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
        $this->xmlProcessor->updateBinding($modification);
        
        $this->sessionData['xmlData'] = $this->xmlProcessor->getXML()->asXML();
        
        unlink($xmlFile);
    }
    
    private function proceedToStep4() {
        $this->sessionData['currentStep'] = 4;
        $this->sessionData['completed'] = true;
        return $this->redirectToStep(4, 'success', 'Configuration terminée');
    }
    
    private function renderStep3(array $data = []) {
        // Préparer les données pour l'éditeur
        $xmlFile = tempnam(sys_get_temp_dir(), "xml_edit");
        file_put_contents($xmlFile, $this->sessionData['xmlData']);
        
        $this->xmlProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
        $joysticks = $this->xmlProcessor->getJoysticks();
        $actionNames = $this->loadActionNames();
        
        $data['step'] = 3;
        $data['title'] = 'Étape 3: Édition de la Configuration';
        $data['description'] = 'Configurez vos bindings et assignez les actions aux boutons/axes';
        $data['xml'] = $this->xmlProcessor->getXML();
        $data['actionNames'] = $actionNames;
        $data['devices'] = $this->sessionData['devices'];
        $data['modifications'] = $this->sessionData['modifications'];
        $data['joysticks'] = $joysticks;
        
        unlink($xmlFile);
        
        return render_template("step_by_step/step3_edit", $data);
    }
    
    /**
     * STEP 4: Récapitulatif et téléchargement
     */
    private function handleStep4(string $action) {
        if (!$this->sessionData['completed']) {
            return $this->redirectToStep(3, 'warning', 'Veuillez terminer l\'édition avant de continuer');
        }
        
        switch ($action) {
            case 'download':
                return $this->downloadModifiedXML();
            case 'reset':
                return $this->resetSession();
            default:
                return $this->renderStep4();
        }
    }
    
    private function downloadModifiedXML() {
        $now = date("Ymd_His");
        $originalName = pathinfo($this->sessionData['xmlName'], PATHINFO_FILENAME);
        $downloadName = "modified_" . $originalName . "_" . $now . ".xml";
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . strlen($this->sessionData['xmlData']));
        
        echo $this->sessionData['xmlData'];
        exit;
    }
    
    private function renderStep4(array $data = []) {
        // Générer le récapitulatif
        $summary = $this->generateConfigurationSummary();
        
        $data['step'] = 4;
        $data['title'] = 'Étape 4: Récapitulatif et Téléchargement';
        $data['description'] = 'Résumé de votre configuration et téléchargement du fichier modifié';
        $data['summary'] = $summary;
        $data['modifications'] = $this->sessionData['modifications'];
        $data['xmlName'] = $this->sessionData['xmlName'];
        $data['downloadReady'] = true;
        
        return render_template("step_by_step/step4_summary", $data);
    }
    
    private function generateConfigurationSummary(): array {
        $xmlFile = tempnam(sys_get_temp_dir(), "xml_summary");
        file_put_contents($xmlFile, $this->sessionData['xmlData']);
        
        $this->xmlProcessor = new XMLProcessor($xmlFile, $this->sessionData['xmlName']);
        $joysticks = $this->xmlProcessor->getJoysticks();
        
        $summary = [
            'devices' => count($this->sessionData['devices']),
            'newDevicesAdded' => count($this->sessionData['newDevices']),
            'modificationsCount' => count($this->sessionData['modifications']),
            'joysticksByEvent' => $this->categorizeJoysticksByEvent($joysticks),
            'actionsList' => $this->getActionsList($joysticks)
        ];
        
        unlink($xmlFile);
        
        return $summary;
    }
    
    private function categorizeJoysticksByEvent(array $joysticks): array {
        $categories = [];
        
        foreach ($joysticks as $joystick) {
            foreach ($joystick['bindings'] as $binding) {
                $event = $binding['event'] ?? 'unknown';
                
                if (!isset($categories[$event])) {
                    $categories[$event] = [];
                }
                
                $categories[$event][] = [
                    'joystick' => $joystick['name'],
                    'action' => $binding['action'],
                    'input' => $binding['input']
                ];
            }
        }
        
        return $categories;
    }
    
    private function getActionsList(array $joysticks): array {
        $actions = [];
        
        foreach ($joysticks as $joystick) {
            foreach ($joystick['bindings'] as $binding) {
                $actions[] = [
                    'action' => $binding['action'],
                    'device' => $joystick['name'],
                    'input' => $binding['input'],
                    'event' => $binding['event'] ?? 'unknown'
                ];
            }
        }
        
        return $actions;
    }

    // Méthodes utilitaires
    
    private function redirectToStep(int $step, string $messageType = '', string $message = ''): string {
        $url = "?step=" . $step;
        
        if ($message) {
            $url .= "&" . $messageType . "=" . urlencode($message);
        }
        
        header("Location: " . $url);
        exit;
    }
    
    private function loadActionNames(): array {
        // Charger les noms d'actions depuis le CSV ou la base de données
        $csvFile = $this->config['files_dir'] . '/action_names.csv';
        
        if (!file_exists($csvFile)) {
            return [];
        }
        
        $actionNames = [];
        $handle = fopen($csvFile, 'r');
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 2) {
                $actionNames[$data[0]] = $data[1];
            }
        }
        
        fclose($handle);
        
        return $actionNames;
    }
    
    /**
     * Valider le contenu XML
     */
    public function validateXMLContent(string $xmlContent): array {
        try {
            // Vérifier si c'est un XML valide
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                return [
                    'valid' => false,
                    'message' => 'XML invalide',
                    'details' => 'Le format XML est incorrect'
                ];
            }
            
            // Vérifier si c'est un XML Star Citizen
            if (!isset($xml->ActionMaps)) {
                return [
                    'valid' => false,
                    'message' => 'Ce n\'est pas un fichier Star Citizen valide',
                    'details' => 'Balise ActionMaps manquante'
                ];
            }
            
            return [
                'valid' => true,
                'message' => 'XML Star Citizen valide',
                'details' => 'Fichier prêt pour traitement'
            ];
            
        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Erreur de validation',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Détecter les dispositifs connectés
     * NOTE: Cette méthode détecte les dispositifs configurés dans le XML.
     * La détection des dispositifs physiquement connectés se fait côté client avec l'API Gamepad.
     */
    public function detectConnectedDevices(): array {
        $devices = [];
        
        if (!empty($this->sessionData['xmlData'])) {
            try {
                $xml = simplexml_load_string($this->sessionData['xmlData']);
                if ($xml && isset($xml->ActionMaps->actionmap)) {
                    foreach ($xml->ActionMaps->actionmap as $actionmap) {
                        $deviceName = (string)$actionmap['name'];
                        if (!empty($deviceName)) {
                            // Créer un objet device plus complet
                            $deviceData = [
                                'name' => $deviceName,
                                'source' => 'xml',
                                'type' => 'configured',
                                'xml_instance' => $deviceName
                            ];
                            
                            // Vérifier si on n'a pas déjà ce device
                            $exists = false;
                            foreach ($devices as $existingDevice) {
                                if ($existingDevice['name'] === $deviceName) {
                                    $exists = true;
                                    break;
                                }
                            }
                            
                            if (!$exists) {
                                $devices[] = $deviceData;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur détection devices XML: " . $e->getMessage());
            }
        }
        
        return $devices;
    }
    
    /**
     * Télécharger le XML modifié
     */
    public function downloadXML(): void {
        if (empty($this->sessionData['xmlData'])) {
            throw new Exception('Aucun XML à télécharger');
        }
        
        $filename = $this->sessionData['xmlName'] ?? 'configuration_modifiee.xml';
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($this->sessionData['xmlData']));
        
        echo $this->sessionData['xmlData'];
    }
    
    /**
     * Télécharger les mappings
     */
    public function downloadMappings(): void {
        $mappings = $this->sessionData['modifications'] ?? [];
        
        if (empty($mappings)) {
            throw new Exception('Aucun mapping à télécharger');
        }
        
        $jsonData = json_encode($mappings, JSON_PRETTY_PRINT);
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="mappings.json"');
        header('Content-Length: ' . strlen($jsonData));
        
        echo $jsonData;
    }
    
    /**
     * Réinitialiser la session
     */
    public function resetSession(): void {
        $this->clearSession();
    }
    
    private function handleFileUpload(array $file): void {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Erreur lors du téléchargement du fichier');
        }
        
        $xmlContent = file_get_contents($file['tmp_name']);
        if ($xmlContent === false) {
            throw new \Exception('Impossible de lire le fichier XML');
        }
        
        $this->sessionData['xmlData'] = $xmlContent;
        $this->sessionData['xmlFileName'] = $file['name'];
    }
    
    private function addDevice(string $deviceName): void {
        if (!isset($this->sessionData['devices'])) {
            $this->sessionData['devices'] = [];
        }
        
        $this->sessionData['devices'][] = [
            'name' => $deviceName,
            'type' => 'manual',
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function generateMappings(): void {
        if (!isset($this->sessionData['devices']) || !isset($this->sessionData['xmlData'])) {
            throw new \Exception('Données insuffisantes pour générer les mappings');
        }
        
        // Logique de génération des mappings basée sur les périphériques et le XML
        $mappings = [];
        foreach ($this->sessionData['devices'] as $device) {
            $mappings[$device['name']] = [
                'config' => $this->generateDeviceConfig($device),
                'bindings' => $this->generateDeviceBindings($device)
            ];
        }
        
        $this->sessionData['mappings'] = $mappings;
    }
    
    private function generateDeviceConfig(array $device): array {
        // Génération de configuration pour un périphérique
        return [
            'name' => $device['name'],
            'type' => $device['type'],
            'settings' => []
        ];
    }
    
    private function generateDeviceBindings(array $device): array {
        // Génération des liaisons pour un périphérique
        return [
            'buttons' => [],
            'axes' => []
        ];
    }
    
    private function createDeviceMapping(array $deviceData) {
        // Extraire les IDs vendor/product depuis le GUID ou utiliser des valeurs par défaut
        $vendorId = '0000';
        $productId = '0000';
        
        if (isset($deviceData['guid'])) {
            // Essayer d'extraire depuis le GUID
            if (preg_match('/\{([0-9A-Fa-f]{4})([0-9A-Fa-f]{4})-/', $deviceData['guid'], $matches)) {
                $productId = strtolower($matches[1]);
                $vendorId = strtolower($matches[2]);
            }
        }
        
        // Générer le nom de fichier
        $filename = $vendorId . '_' . $productId . '_map.json';
        $filepath = __DIR__ . '/../mappings/devices/' . $filename;
        
        // Vérifier si le fichier existe déjà
        if (file_exists($filepath)) {
            return [
                'filename' => $filename,
                'filepath' => $filepath,
                'status' => 'exists',
                'message' => 'Le fichier de mapping existe déjà'
            ];
        }
        
        // Créer la structure de mapping de base
        $mapping = [
            'id' => $deviceData['name'] ?? 'Unknown Device',
            'product' => $deviceData['name'] ?? 'Unknown Device',
            'vendor_id' => '0x' . $vendorId,
            'product_id' => '0x' . $productId,
            'xml_instance' => $deviceData['xml_instance'] ?? null,
            'axes_map' => $this->generateDefaultAxesMap($deviceData),
            'buttons' => $this->generateDefaultButtonsMap($deviceData),
        ];
        
        // Ajouter les HATs si spécifiés
        if (isset($deviceData['hats']) && $deviceData['hats'] > 0) {
            $mapping['hats'] = $this->generateDefaultHatsMap($deviceData);
        }
        
        // Créer le dossier s'il n'existe pas
        $mappingsDir = dirname($filepath);
        if (!is_dir($mappingsDir)) {
            mkdir($mappingsDir, 0755, true);
        }
        
        // Sauvegarder le fichier JSON
        $jsonContent = json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($filepath, $jsonContent) === false) {
            throw new \Exception('Impossible de créer le fichier de mapping : ' . $filename);
        }
        
        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'status' => 'created',
            'mapping' => $mapping,
            'message' => 'Fichier de mapping créé avec succès'
        ];
    }
    
    private function generateDefaultAxesMap(array $deviceData) {
        $axesMap = [];
        $axes = isset($deviceData['axes']) ? (int)$deviceData['axes'] : 4;
        
        // Mapping par défaut pour les axes les plus communs
        $defaultAxes = ['x', 'y', 'z', 'rotz', 'slider1', 'slider2'];
        
        for ($i = 0; $i < min($axes, count($defaultAxes)); $i++) {
            $axesMap[$i] = $defaultAxes[$i];
        }
        
        return $axesMap;
    }
    
    private function generateDefaultButtonsMap(array $deviceData) {
        $buttonsMap = [];
        $buttons = isset($deviceData['buttons']) ? (int)$deviceData['buttons'] : 8;
        
        for ($i = 0; $i < $buttons; $i++) {
            $buttonsMap['button_' . ($i + 1)] = 'Button ' . ($i + 1);
        }
        
        return $buttonsMap;
    }
    
    private function generateDefaultHatsMap(array $deviceData) {
        return [
            '1' => [
                'directions' => [
                    'up' => ['axis' => 9, 'value' => -1, 'value_min' => -1.05, 'value_max' => -0.95],
                    'down' => ['axis' => 9, 'value' => 0.13, 'value_min' => 0.08, 'value_max' => 0.18],
                    'left' => ['axis' => 9, 'value' => 0.71, 'value_min' => 0.66, 'value_max' => 0.76],
                    'right' => ['axis' => 9, 'value' => -0.43, 'value_min' => -0.48, 'value_max' => -0.38]
                ],
                'rest' => ['axis' => 9, 'value' => 1.28, 'value_min' => 1.23, 'value_max' => 1.33]
            ]
        ];
    }

    // Met à jour les données de session (compatible Redis/PHP)
    private function updateSessionData(array $data): void
    {
        if ($this->useRedis) {
            // Mettre à jour les données avec la dernière activité
            $data['last_activity'] = date('c');
            
            // Sauvegarder dans Redis
            $this->redisManager->saveStepByStepSession($this->sessionId, $data);
            $this->sessionData = $data;
        } else {
            // Fallback sur les sessions PHP
            $_SESSION['stepByStep'] = array_merge($_SESSION['stepByStep'], $data);
            $this->sessionData = &$_SESSION['stepByStep'];
        }
    }
    
    /**
     * Récupère les données de session actuelles
     */
    private function getSessionData(): array
    {
        if ($this->useRedis) {
            // Rafraîchir depuis Redis
            $data = $this->redisManager->getStepByStepSession($this->sessionId);
            if ($data !== null) {
                $this->sessionData = $data;
            }
        }
        
        return $this->sessionData;
    }
    
    /**
     * Nettoie la session actuelle
     */
    public function clearSession(): void
    {
        if ($this->useRedis) {
            $this->redisManager->deleteStepByStepSession($this->sessionId);
            
            // Nettoyer aussi toutes les données de dispositifs liées à cette session
            $this->clearDeviceData();
        } else {
            unset($_SESSION['stepByStep']);
        }
        
        $this->initializeSession();
    }
    
    /**
     * Nettoie toutes les données de dispositifs dans Redis
     */
    private function clearDeviceData(): void
    {
        if (!$this->useRedis || !$this->redisManager) {
            return;
        }
        
        try {
            // Nettoyer les patterns de clés liés aux dispositifs
            $patterns = [
                'sc_config:devices:config:*',
                'sc_config:mappings:json:devices_*',
                'sc_config:index:devices:*'
            ];
            
            foreach ($patterns as $pattern) {
                $keys = $this->redisManager->getKeysPattern($pattern);
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        $this->redisManager->delete($key);
                    }
                }
            }
            
            error_log("StepByStepEditor: Données de dispositifs nettoyées du cache Redis");
        } catch (\Exception $e) {
            error_log("StepByStepEditor: Erreur lors du nettoyage des données dispositifs - " . $e->getMessage());
        }
    }
    
    /**
     * Met à jour l'étape actuelle
     */
    public function setCurrentStep(int $step): void
    {
        $data = $this->getSessionData();
        $data['currentStep'] = $step;
        $this->updateSessionData($data);
    }
    
    /**
     * Met à jour les données XML
     */
    public function setXMLData(string $xmlData, string $xmlName): void
    {
        $data = $this->getSessionData();
        $data['xmlData'] = $xmlData;
        $data['xmlName'] = $xmlName;
        $this->updateSessionData($data);
    }
    
    /**
     * Ajoute un dispositif détecté
     */
    public function addDetectedDevice(array $deviceData): void
    {
        $data = $this->getSessionData();
        if (!isset($data['devices'])) {
            $data['devices'] = [];
        }
        $data['devices'][] = $deviceData;
        $this->updateSessionData($data);
    }
    
    /**
     * Met à jour les modifications apportées
     */
    public function updateModifications(array $modifications): void
    {
        $data = $this->getSessionData();
        $data['modifications'] = $modifications;
        $this->updateSessionData($data);
    }
}
