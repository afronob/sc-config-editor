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
    // Affichage du formulaire d'édition via template
    $templateVars = [
        'xmlName' => $xmlName,
        'xml' => $xml,
        'actionNames' => $actionNames,
        'actionmaps_root' => $actionmaps_root,
        'joysticks' => $joysticks,
        'actionsInfo' => $actionsInfo
    ];
    extract($templateVars);
    include __DIR__ . '/templates/edit_form.php';
    exit;
}
// Formulaire d'upload
include __DIR__ . '/templates/upload_form.php';
exit;
?>
