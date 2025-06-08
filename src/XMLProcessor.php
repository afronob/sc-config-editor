<?php

declare(strict_types=1);

namespace SCConfigEditor;

use SimpleXMLElement;

class XMLProcessor {
    private ?SimpleXMLElement $xml = null;
    private ?string $xmlName = null;
    private ?SimpleXMLElement $actionmaps_root = null;
    
    public function __construct(?string $xmlFile = null, ?string $xmlName = null) {
        if ($xmlFile !== null && $xmlName !== null) {
            $this->loadXML($xmlFile, $xmlName);
        }
    }
    
    private function loadXML(string $xmlFile, string $xmlName): void {
        $this->xml = \simplexml_load_file($xmlFile);
        if (!$this->xml) {
            throw new \Exception('Erreur lors du chargement du fichier XML.');
        }
        
        $this->xmlName = $xmlName;
        $this->detectActionmapsRoot();
    }
    
    private function detectActionmapsRoot(): void {
        if (!$this->xml) {
            throw new \Exception('XML non chargé.');
        }

        if (isset($this->xml->ActionProfiles)) {
            $this->actionmaps_root = $this->xml->ActionProfiles;
        } elseif (isset($this->xml->actionmap)) {
            $this->actionmaps_root = $this->xml;
        } else {
            throw new \Exception('Format XML non reconnu.');
        }
    }

    public function getXML(): ?SimpleXMLElement {
        return $this->xml;
    }

    public function getActionmapsRoot(): ?SimpleXMLElement {
        return $this->actionmaps_root;
    }
    
    public function getStats() {
        $totalActions = 0;
        $usedActions = 0;
        
        foreach ($this->actionmaps_root->actionmap as $actionmap) {
            foreach ($actionmap->action as $action) {
                $totalActions++;
                $isUsed = false;
                foreach ($action->rebind as $rebind) {
                    $input = trim((string)$rebind['input']);
                    if ($input !== '' && !preg_match('/^(js|kb|mo)[0-9]+_$/', $input)) {
                        $isUsed = true;
                        break;
                    }
                }
                if ($isUsed) {
                    $usedActions++;
                }
            }
        }
        
        return [
            'total' => $totalActions,
            'used' => $usedActions
        ];
    }
    
    /**
     * @return array<int, array{
     *   instance: string,
     *   product: string,
     *   file_title: string,
     *   product_raw: string
     * }>
     */
    public function getJoysticks(): array {
        if (!$this->xml) {
            throw new \Exception('XML non chargé.');
        }

        $joysticks = [];
        foreach ($this->xml->xpath('//options[@type="joystick"]') as $opt) {
            $instance = (string)$opt['instance'];
            $product = (string)$opt['Product'];
            $product_clean = trim(preg_replace('/\s*\{[^}]+\}\s*$/', '', $product));
            $file_title = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $product_clean)) . '.drawio';
            
            $joysticks[] = [
                'instance' => $instance,
                'product' => $product_clean,
                'file_title' => $file_title,
                'product_raw' => $product
            ];
        }
        return $joysticks;
    }
    
    /**
     * Met à jour le XML avec les données du formulaire
     * @param array{
     *   input: array<string, array<string, array<int, string>>>,
     *   opts: array<string, array<string, array<int, string>>>,
     *   value: array<string, array<string, array<int, string>>>
     * } $postData Les données POST du formulaire
     * @throws \Exception Si le XML n'est pas chargé ou si les données sont invalides
     */
    public function updateFromPost(array $postData): void {
        if (!$this->xml) {
            throw new \Exception('XML non chargé.');
        }

        if (!isset($postData['input']) || !is_array($postData['input'])) {
            throw new \Exception('Données de formulaire invalides.');
        }

        foreach ($postData['input'] as $cat => $actions) {
            if (!is_array($actions)) continue;
            
            foreach ($actions as $act => $rebinds) {
                if (!is_array($rebinds)) continue;
                
                foreach ($rebinds as $idx => $input) {
                    $opts = $postData['opts'][$cat][$act][$idx] ?? '';
                    $value = $postData['value'][$cat][$act][$idx] ?? '';
                    
                    foreach ($this->xml->xpath("//actionmap[@name='$cat']/action[@name='$act']/rebind") as $i => $rebind) {
                        if ($i == $idx) {
                            $rebind['input'] = $input;
                            if ($opts) {
                                $rebind[$opts] = $value;
                            } else {
                                foreach ($rebind->attributes() as $k => $v) {
                                    if ($k != 'input') unset($rebind[$k]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (isset($postData['profileName'])) {
            $this->xml['profileName'] = $postData['profileName'];
        }
        
        if (isset($postData['customLabel']) && isset($this->xml->CustomisationUIHeader)) {
            $this->xml->CustomisationUIHeader['label'] = $postData['customLabel'];
        }
    }
    
    public function getName(): ?string {
        return $this->xmlName;
    }
    
    /**
     * Ajoute un périphérique complet au XML avec toutes les balises nécessaires
     * @param array{guid: string, product: string, instance?: string, deviceID?: string, file_title?: string} $deviceData
     * @throws \Exception Si le XML n'est pas chargé
     */
    public function addCompleteDevice(array $deviceData): void {
        if (!$this->xml) {
            throw new \Exception('XML non chargé.');
        }
        
        $guid = $deviceData['guid'] ?? '';
        $product = $deviceData['product'] ?? '';
        $instance = $deviceData['instance'] ?? $this->getNextAvailableInstance();
        $deviceID = $deviceData['deviceID'] ?? $instance;
        $file_title = $deviceData['file_title'] ?? $product;
        
        if (empty($guid) || empty($product)) {
            throw new \Exception('GUID et nom du produit sont requis.');
        }
        
        // Vérifier si le périphérique existe déjà par GUID
        $existingOptions = $this->xml->xpath("//options[@type='joystick' and contains(@Product, '{$guid}')]");
        if (!empty($existingOptions)) {
            throw new \Exception('Ce périphérique est déjà configuré dans le XML.');
        }
        
        // Ajouter la section options joystick
        $this->addJoystickOptionsSection($instance, $guid, $product);
        
        // Ajouter des exemples de rebind si demandé
        $this->addSampleRebinds($instance);
    }
    
    /**
     * Détermine la prochaine instance disponible
     */
    private function getNextAvailableInstance(): string {
        $existingOptions = $this->xml->xpath('//options[@type="joystick"]/@instance');
        $usedInstances = [];
        
        foreach ($existingOptions as $instance) {
            $instanceNumber = (int)((string)$instance);
            if ($instanceNumber > 0) {
                $usedInstances[] = $instanceNumber;
            }
        }
        
        $nextInstance = 1;
        while (in_array($nextInstance, $usedInstances)) {
            $nextInstance++;
        }
        
        return (string)$nextInstance;
    }
    
    /**
     * Ajoute la déclaration joystick principale
     */
    private function addJoystickDeclaration(string $guid, string $product, string $deviceID, string $file_title): void {
        // Chercher où insérer la déclaration
        $joysticks = $this->xml->xpath('//joystick');
        
        // Créer l'élément joystick
        $joystickXML = '<joystick instance="*" instanceGUID="' . htmlspecialchars($guid) . '" Product="' . htmlspecialchars($product) . '" DeviceID="' . htmlspecialchars($deviceID) . '" file_title="' . htmlspecialchars($file_title) . '" />';
        
        if (!empty($joysticks)) {
            // Insérer après le dernier joystick existant
            $lastJoystick = end($joysticks);
            $this->insertXMLAfter($lastJoystick, $joystickXML);
        } else {
            // Insérer au début du fichier après les informations générales
            $this->insertJoystickAtBeginning($joystickXML);
        }
    }
    
    /**
     * Ajoute la section options pour le joystick avec format standard Star Citizen
     */
    private function addJoystickOptionsSection(string $instance, string $guid, string $product): void {
        // Chercher s'il y a déjà des sections options
        $optionsElements = $this->xml->xpath('//options[@type="joystick"]');
        
        // Construire le nom du produit avec GUID au format Star Citizen
        $productWithGuid = $product . ' ' . $guid;
        
        $optionsXML = '<options type="joystick" instance="' . htmlspecialchars($instance) . '" Product="' . htmlspecialchars($productWithGuid) . '">';
        $optionsXML .= '<flight_move_freelook type="1" value="0" />';
        $optionsXML .= '<flight_move_roll_scale type="1" value="50" />';
        $optionsXML .= '<flight_move_pitch_scale type="1" value="50" />';
        $optionsXML .= '<flight_move_yaw_scale type="1" value="50" />';
        $optionsXML .= '</options>';
        
        if (!empty($optionsElements)) {
            // Insérer après la dernière section options
            $lastOptions = end($optionsElements);
            $this->insertXMLAfter($lastOptions, $optionsXML);
        } else {
            // Insérer après CustomisationUIHeader si présent
            $header = $this->xml->xpath('//CustomisationUIHeader');
            if (!empty($header)) {
                $this->insertXMLAfter($header[0], $optionsXML);
            } else {
                // Insérer au début après la racine
                $this->insertJoystickAtBeginning($optionsXML);
            }
        }
    }
    
    /**
     * Ajoute des exemples de rebind pour le nouveau périphérique
     */
    private function addSampleRebinds(string $instance): void {
        // Chercher des actionmaps communes pour ajouter des exemples
        $actionmaps = $this->xml->xpath('//actionmap[@name="spaceship_movement"]');
        
        if (!empty($actionmaps)) {
            $actionmap = $actionmaps[0];
            
            // Ajouter un exemple de rebind pour le pitch
            $actions = $actionmap->xpath('.//action[@name="v_pitch"]');
            if (!empty($actions)) {
                $action = $actions[0];
                $rebindXML = '<rebind input="js' . htmlspecialchars($instance) . '_x" />';
                $this->insertXMLIntoAction($action, $rebindXML);
            }
            
            // Ajouter un exemple de rebind pour le yaw
            $actions = $actionmap->xpath('.//action[@name="v_yaw"]');
            if (!empty($actions)) {
                $action = $actions[0];
                $rebindXML = '<rebind input="js' . htmlspecialchars($instance) . '_y" />';
                $this->insertXMLIntoAction($action, $rebindXML);
            }
        }
    }
    
    /**
     * Insère un élément XML après un élément donné
     */
    private function insertXMLAfter(SimpleXMLElement $afterElement, string $xmlString): void {
        $dom = dom_import_simplexml($afterElement);
        $newElement = $dom->ownerDocument->createDocumentFragment();
        $newElement->appendXML($xmlString);
        $dom->parentNode->insertBefore($newElement, $dom->nextSibling);
    }
    
    /**
     * Insère un joystick au début du fichier
     */
    private function insertJoystickAtBeginning(string $joystickXML): void {
        $dom = dom_import_simplexml($this->xml);
        $newElement = $dom->ownerDocument->createDocumentFragment();
        $newElement->appendXML($joystickXML);
        $dom->insertBefore($newElement, $dom->firstChild);
    }
    
    /**
     * Insère un rebind dans une action
     */
    private function insertXMLIntoAction(SimpleXMLElement $action, string $rebindXML): void {
        $dom = dom_import_simplexml($action);
        $newElement = $dom->ownerDocument->createDocumentFragment();
        $newElement->appendXML($rebindXML);
        $dom->appendChild($newElement);
    }
    
    /**
     * Recalcule les statistiques du XML après modification
     * @return array{total_actionmaps: int, total_actions: int, total_rebinds: int, total_joysticks: int}
     */
    public function recalculateStats(): array {
        if (!$this->xml) {
            throw new \Exception('XML non chargé.');
        }
        
        $actionmaps = $this->xml->xpath('//actionmap') ?: [];
        $actions = $this->xml->xpath('//action') ?: [];
        $rebinds = $this->xml->xpath('//rebind') ?: [];
        $joysticks = $this->xml->xpath('//joystick') ?: [];
        
        return [
            'total_actionmaps' => count($actionmaps),
            'total_actions' => count($actions),
            'total_rebinds' => count($rebinds),
            'total_joysticks' => count($joysticks)
        ];
    }
}
