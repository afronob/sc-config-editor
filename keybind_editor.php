<?php
// Simple PHP script to upload, display, edit, and download a Star Citizen XML keybind file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_FILES['xmlfile']) || isset($_POST['save']))) {
    if (isset($_FILES['xmlfile'])) {
        $xmlFile = $_FILES['xmlfile']['tmp_name'];
        $xmlName = basename($_FILES['xmlfile']['name']);
    } else {
        // On vient d'un POST de modification, on doit recharger le XML depuis le champ caché
        $xmlName = $_POST['xmlname'];
        $xmlFile = tempnam(sys_get_temp_dir(), 'xml');
        file_put_contents($xmlFile, $_POST['xmldata']);
    }
    $xml = simplexml_load_file($xmlFile);
    if (!$xml) {
        $errorMsg = 'Erreur lors du chargement du fichier XML.';
        include __DIR__ . '/templates/error.php';
        exit;
    }
    // Détection de la racine et du chemin vers les actionmaps
    $actionmaps_root = null;
    if (isset($xml->ActionProfiles)) {
        // Cas 1 : <ActionMaps><ActionProfiles>...</ActionProfiles></ActionMaps>
        $actionmaps_root = $xml->ActionProfiles;
    } elseif (isset($xml->actionmap)) {
        // Cas 2 : <ActionMaps><actionmap>...</actionmap></ActionMaps>
        $actionmaps_root = $xml;
    } else {
        $errorMsg = 'Format XML non reconnu.';
        include __DIR__ . '/templates/error.php';
        exit;
    }
    // Compter toutes les actions et celles utilisées (input non vide et différent de js1_ ou js2_)
    $totalActions = 0;
    $usedActions = 0;
    foreach ($actionmaps_root->actionmap as $actionmap) {
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
    $actionsInfo = '<div style="margin-bottom:1em;"><b>Actions utilisées :</b> ' . $usedActions . ' / ' . $totalActions . ' (' . $usedActions . ' utilisé(s) sur un total de ' . $totalActions . ')</div>';
    // Affichage des joysticks détectés
    $joysticks = [];
    foreach ($xml->xpath('//options[@type="joystick"]') as $opt) {
        $instance = (string)$opt['instance'];
        $product = (string)$opt['Product'];
        $product_clean = trim(preg_replace('/\s*\{[^}]+\}\s*$/', '', $product));
        // Générer le nom du fichier pour diagrams.net (ex: t16000m.drawio)
        $file_title = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $product_clean)) . '.drawio';
        $iframe_url = 'https://viewer.diagrams.net/?tags=%7B%7D&lightbox=1&highlight=0000ff&edit=_blank&layers=1&nav=1&title=' . rawurlencode($file_title) . '&dark=auto#Uhttps%3A%2F%2Fraw.githubusercontent.com%2Fafronob%2Fsc-vkb-config%2Fmain%2Fjoy%2F' . rawurlencode($file_title);
        $joysticks[] = '<a href="#" class="jslink" data-url="' . htmlspecialchars($iframe_url) . '" data-title="' . htmlspecialchars($file_title) . '">js' . $instance . ' : ' . htmlspecialchars($product_clean) . '</a>';
    }
    // Si l'utilisateur a soumis des modifications
    if (isset($_POST['save'])) {
        foreach ($_POST['input'] as $cat => $actions) {
            foreach ($actions as $act => $rebinds) {
                foreach ($rebinds as $idx => $input) {
                    $opts = $_POST['opts'][$cat][$act][$idx];
                    $value = $_POST['value'][$cat][$act][$idx];
                    foreach ($xml->xpath("//actionmap[@name='$cat']/action[@name='$act']/rebind") as $i => $rebind) {
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
        // Mettre à jour les attributs profileName et label si fournis
        if (isset($_POST['profileName'])) {
            $xml['profileName'] = $_POST['profileName'];
        }
        if (isset($_POST['customLabel']) && isset($xml->CustomisationUIHeader)) {
            $xml->CustomisationUIHeader['label'] = $_POST['customLabel'];
        }
        // Afficher un lien de téléchargement au lieu de forcer le download
        $xmlStr = $xml->asXML();
        $tmpFile = tempnam(sys_get_temp_dir(), 'xml');
        file_put_contents($tmpFile, $xmlStr);
        $now = date('Ymd_His');
        $downloadName = 'modified_' . pathinfo($xmlName, PATHINFO_FILENAME) . '_' . $now . '.xml';
        include __DIR__ . '/templates/success.php';
        exit;
    }
    // Charger le mapping action => name depuis le CSV
    $actionNames = [];
    if (($handle = fopen(__DIR__ . '/files/actions_keybind.csv', 'r')) !== false) {
        // Lire l'en-tête
        $header = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($data) >= 2) {
                $actionNames[$data[0]] = $data[1];
            }
        }
        fclose($handle);
    }
    // Chargement des fichiers JSON de devices
    $deviceJsonFiles = glob(__DIR__ . '/files/*.json');
    $devicesData = [];
    $debugDeviceLoad = [];
    foreach ($deviceJsonFiles as $jsonFile) {
        $json = file_get_contents($jsonFile);
        $data = json_decode($json, true);
        $debugDeviceLoad[] = [
            'file' => basename($jsonFile),
            'json_valid' => $data !== null,
            'has_id' => $data && isset($data['id']),
            'id' => $data['id'] ?? null
        ];
        if ($data && isset($data['id'])) {
            $devicesData[] = $data;
        }
    }
    if (empty($devicesData)) {
        error_log('Aucun device JSON valide trouvé. Debug: ' . print_r($debugDeviceLoad, true));
    }
    // Création du mapping device id (ou product) => instance XML (jsX)
    $deviceInstanceMap = [];
    foreach ($xml->xpath('//options[@type="joystick"]') as $opt) {
        $product = trim((string)$opt['Product']);
        $instance = (string)$opt['instance'];
        // On simplifie le nom pour matcher plus facilement (on retire les accolades et espaces)
        $product_simple = preg_replace('/\{.*\}/', '', $product);
        $product_simple = trim($product_simple);
        $deviceInstanceMap[$product_simple] = $instance;
    }
    // Pour chaque device JSON, on tente de retrouver l'instance XML correspondante
    foreach ($devicesData as &$device) {
        $device['xml_instance'] = null;
        foreach ($deviceInstanceMap as $product => $instance) {
            // On simplifie aussi le nom du device détecté
            $dev_id_simple = preg_replace('/\(Vendor:.*$/', '', $device['id']);
            $dev_id_simple = trim($dev_id_simple);
            if (stripos($dev_id_simple, $product) !== false || stripos($product, $dev_id_simple) !== false) {
                $device['xml_instance'] = $instance;
                break;
            }
        }
    }
    unset($device);
    // Mapping par correspondance de nom (plus fiable que l'ordre)
    $xmlJoysticks = [];
    foreach ($xml->xpath('//options[@type="joystick"]') as $opt) {
        $product = (string)$opt['Product'];
        $instance = (string)$opt['instance'];
        $xmlJoysticks[] = [
            'product' => $product,
            'instance' => $instance,
            'matched' => false
        ];
    }
    // Pour chaque device détecté, on cherche le meilleur match par nom (id ou product)
    foreach ($devicesData as &$device) {
        $dev_id_simple = preg_replace('/\(Vendor:.*$/', '', $device['id']);
        $dev_id_simple = trim($dev_id_simple);
        $found = false;
        foreach ($xmlJoysticks as &$joy) {
            $prod_simple = preg_replace('/\{.*\}/', '', $joy['product']);
            $prod_simple = trim($prod_simple);
            if (!$joy['matched'] && (stripos($dev_id_simple, $prod_simple) !== false || stripos($prod_simple, $dev_id_simple) !== false)) {
                $device['xml_instance'] = $joy['instance'];
                $joy['matched'] = true;
                $found = true;
                break;
            }
        }
        unset($joy);
        // Si aucun match, on prend la première instance XML non utilisée
        if (!$found) {
            foreach ($xmlJoysticks as &$joy) {
                if (!$joy['matched']) {
                    $device['xml_instance'] = $joy['instance'];
                    $joy['matched'] = true;
                    break;
                }
            }
            unset($joy);
        }
    }
    unset($device);
    // --- Ajout : fonction pour extraire VendorID/ProductID du champ Product du XML ---
    function extractVendorProductId($productString) {
        if (preg_match('/\{([0-9A-Fa-f]{4})([0-9A-Fa-f]{4})-/', $productString, $m)) {
            // $m[1] = ProductID, $m[2] = VendorID (ordre Star Citizen)
            // Correction : on retourne bien vendor_id = $m[2], product_id = $m[1]
            return [
                'vendor_id' => strtolower($m[2]),
                'product_id' => strtolower($m[1])
            ];
        }
        return [ 'product_id' => null, 'vendor_id' => null ];
    }
    // --- Matching unique par VendorID/ProductID, fallback nom, fallback libre ---
    // Réinitialise matched pour tous les joysticks
    foreach ($xmlJoysticks as &$joy) {
        $joy['matched'] = false;
    }
    unset($joy);
    foreach ($devicesData as &$device) {
        $device['xml_instance'] = null;
        $found = false;
        // IDs JSON (mapping)
        $dev_vendor = isset($device['vendor_id']) ? strtolower(preg_replace('/^0x/', '', $device['vendor_id'])) : null;
        $dev_product = isset($device['product_id']) ? strtolower(preg_replace('/^0x/', '', $device['product_id'])) : null;
        // Nettoyage strict : on ne garde que les caractères hexadécimaux
        $dev_vendor_clean = preg_replace('/[^0-9a-f]/', '', $dev_vendor);
        $dev_product_clean = preg_replace('/[^0-9a-f]/', '', $dev_product);
        foreach ($xmlJoysticks as &$joy) {
            $ids = extractVendorProductId($joy['product']);
            $xml_vendor = strtolower($ids['vendor_id']);
            $xml_product = strtolower($ids['product_id']);
            $xml_vendor_clean = preg_replace('/[^0-9a-f]/', '', $xml_vendor);
            $xml_product_clean = preg_replace('/[^0-9a-f]/', '', $xml_product);
            error_log("[SC-MATCH-DEBUG] dev_vendor='" . $dev_vendor_clean . "' (len=" . strlen($dev_vendor_clean) . ", hex=" . bin2hex($dev_vendor_clean) . ") xml_vendor='" . $xml_vendor_clean . "' (len=" . strlen($xml_vendor_clean) . ", hex=" . bin2hex($xml_vendor_clean) . ") dev_product='" . $dev_product_clean . "' (len=" . strlen($dev_product_clean) . ", hex=" . bin2hex($dev_product_clean) . ") xml_product='" . $xml_product_clean . "' (len=" . strlen($xml_product_clean) . ", hex=" . bin2hex($xml_product_clean) . ")");
            if ($xml_vendor_clean && $xml_product_clean && $dev_vendor_clean && $dev_product_clean) {
                if ($xml_vendor_clean === $dev_vendor_clean && $xml_product_clean === $dev_product_clean && !$joy['matched']) {
                    $device['xml_instance'] = $joy['instance'];
                    $joy['matched'] = true;
                    $found = true;
                    error_log("[SC-MATCH] Match OK: device {$device['id']} (v:$dev_vendor_clean p:$dev_product_clean) <-> XML {$joy['product']} (v:$xml_vendor_clean p:$xml_product_clean) (js{$joy['instance']})");
                    break;
                } else {
                    error_log("[SC-MATCH] No match: device {$device['id']} (v:$dev_vendor_clean p:$dev_product_clean) <-> XML {$joy['product']} (v:$xml_vendor_clean p:$xml_product_clean)");
                }
            }
        }
        unset($joy);
        // Si aucun match par ID, fallback sur le nom
        if (!$found) {
            $dev_id_simple = preg_replace('/\(Vendor:.*$/', '', $device['id']);
            $dev_id_simple = trim($dev_id_simple);
            foreach ($xmlJoysticks as &$joy) {
                $prod_simple = preg_replace('/\{.*\}/', '', $joy['product']);
                $prod_simple = trim($prod_simple);
                if (!$joy['matched'] && (stripos($dev_id_simple, $prod_simple) !== false || stripos($prod_simple, $dev_id_simple) !== false)) {
                    $device['xml_instance'] = $joy['instance'];
                    $joy['matched'] = true;
                    error_log("[SC-MATCH] Fallback nom: device {$device['id']} <-> XML {$joy['product']} (js{$joy['instance']})");
                    break;
                }
            }
            unset($joy);
        }
        // Si toujours rien, on prend la première instance XML non utilisée
        if (!$device['xml_instance']) {
            foreach ($xmlJoysticks as &$joy) {
                if (!$joy['matched']) {
                    $device['xml_instance'] = $joy['instance'];
                    $joy['matched'] = true;
                    error_log("[SC-MATCH] Fallback libre: device {$device['id']} <-> XML {$joy['product']} (js{$joy['instance']})");
                    break;
                }
            }
            unset($joy);
        }
    }
    unset($device);
    // Fonction utilitaire pour convertir un index de bouton JSON/Gamepad API en nom XML (jsX_buttonY)
    function getXmlButtonName($xmlInstance, $buttonIndex) {
        // Star Citizen indexe les boutons à partir de 1 (js1_button1), alors que la Gamepad API/JSON commence à 0
        return 'js' . $xmlInstance . '_button' . ($buttonIndex + 1);
    }
    // Affichage du formulaire d'édition via template
    $templateVars = [
        'xmlName' => $xmlName,
        'xml' => $xml,
        'actionNames' => $actionNames,
        'actionmaps_root' => $actionmaps_root,
        'joysticks' => $joysticks,
        'actionsInfo' => $actionsInfo,
        'devicesData' => $devicesData
    ];
    extract($templateVars);
    include __DIR__ . '/templates/edit_form.php';
    // Stop toute sortie PHP après le template pour éviter d'injecter du HTML dans le JS
    exit;
}
// Formulaire d'upload
include __DIR__ . '/templates/upload_form.php';
exit;
// --- Sécurité : stoppe toute sortie accidentelle après ce point ---
