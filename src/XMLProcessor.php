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
}
