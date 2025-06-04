<?php

declare(strict_types=1);

namespace SCConfigEditor;

/**
 * @phpstan-type DeviceData array{
 *   id: string,
 *   vendor_id?: string,
 *   product_id?: string,
 *   buttons?: array<string>,
 *   axes_map?: array<int, string>,
 *   hats?: array<int, array{
 *     directions: array<string, array{axis: int, value_min: float, value_max: float}>,
 *     rest?: array{value_min: float, value_max: float}
 *   }>,
 *   xml_instance?: ?string
 * }
 */
class DeviceManager {
    /** @var array<int, DeviceData> */
    private array $devices = [];
    private string $mappingPath;
    
    public function __construct(string $mappingPath) {
        $this->mappingPath = $mappingPath;
        $this->loadDevices();
    }
    
    private function loadDevices(): void {
        $deviceJsonFiles = glob($this->mappingPath . '/*.json');
        if ($deviceJsonFiles === false) {
            throw new \RuntimeException("Impossible de lire le dossier de mapping: {$this->mappingPath}");
        }

        foreach ($deviceJsonFiles as $jsonFile) {
            $json = file_get_contents($jsonFile);
            if ($json === false) {
                continue;
            }
            
            $data = json_decode($json, true);
            if ($data && isset($data['id'])) {
                $this->devices[] = $data;
            }
        }
    }
    
    /**
     * Fait correspondre les périphériques détectés avec les entrées du XML
     * @param array<int, array{instance: string, product: string, product_raw: string}> $joysticks
     * @return array<int, DeviceData>
     */
    public function matchDevicesToXML(array $joysticks): array {
        foreach ($this->devices as &$device) {
            $device['xml_instance'] = null;
            $found = false;
            
            // IDs JSON (mapping)
            $dev_vendor = isset($device['vendor_id']) ? strtolower(preg_replace('/^0x/', '', $device['vendor_id'])) : null;
            $dev_product = isset($device['product_id']) ? strtolower(preg_replace('/^0x/', '', $device['product_id'])) : null;
            
            // Nettoyage strict : on ne garde que les caractères hexadécimaux
            $dev_vendor_clean = preg_replace('/[^0-9a-f]/', '', $dev_vendor);
            $dev_product_clean = preg_replace('/[^0-9a-f]/', '', $dev_product);
            
            foreach ($joysticks as $joy) {
                $ids = $this->extractVendorProductId($joy['product_raw']);
                $xml_vendor = strtolower($ids['vendor_id']);
                $xml_product = strtolower($ids['product_id']);
                $xml_vendor_clean = preg_replace('/[^0-9a-f]/', '', $xml_vendor);
                $xml_product_clean = preg_replace('/[^0-9a-f]/', '', $xml_product);
                
                if ($xml_vendor_clean && $xml_product_clean && $dev_vendor_clean && $dev_product_clean) {
                    if ($xml_vendor_clean === $dev_vendor_clean && $xml_product_clean === $dev_product_clean) {
                        $device['xml_instance'] = $joy['instance'];
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found) {
                $this->matchByName($device, $joysticks);
            }
        }
        
        return $this->devices;
    }
    
    /**
     * Tente de faire correspondre un périphérique avec un joystick par son nom
     * @param DeviceData $device
     * @param array<int, array{instance: string, product: string, product_raw: string}> $joysticks
     */
    private function matchByName(array &$device, array $joysticks): void {
        if (!isset($device['id'])) {
            return;
        }

        $dev_id_simple = preg_replace('/\(Vendor:.*$/', '', $device['id']);
        $dev_id_simple = trim($dev_id_simple);
        
        foreach ($joysticks as $joy) {
            $prod_simple = preg_replace('/\{.*\}/', '', $joy['product']);
            $prod_simple = trim($prod_simple);
            
            if (stripos($dev_id_simple, $prod_simple) !== false || stripos($prod_simple, $dev_id_simple) !== false) {
                $device['xml_instance'] = $joy['instance'];
                return;
            }
        }
        
        // Si toujours pas de match, prendre la première instance disponible
        if (!empty($joysticks)) {
            $device['xml_instance'] = $joysticks[0]['instance'];
        }
    }
    
    /**
     * Extrait les identifiants Vendor/Product d'une chaîne au format SC
     * @param string $productString Ex: "VKB Gladiator NXT {231D0201-...}"
     * @return array{vendor_id: ?string, product_id: ?string}
     */
    private function extractVendorProductId(string $productString): array {
        if (preg_match('/\{([0-9A-Fa-f]{4})([0-9A-Fa-f]{4})-/', $productString, $m)) {
            return [
                'vendor_id' => strtolower($m[2]),
                'product_id' => strtolower($m[1])
            ];
        }
        return ['vendor_id' => null, 'product_id' => null];
    }
    
    /**
     * Retourne la liste des périphériques chargés
     * @return array<int, DeviceData>
     */
    public function getDevices(): array {
        return $this->devices;
    }
}
