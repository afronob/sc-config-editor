<?php

declare(strict_types=1);

namespace SCConfigEditor;

use SCConfigEditor\DeviceManager;
use SCConfigEditor\XMLProcessor;

class Application {
    private $config;
    private $deviceManager;
    private $xmlProcessor;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->deviceManager = new DeviceManager($config["files_dir"]);
        $this->xmlProcessor = new XMLProcessor();
    }
    
    public function run() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            return $this->handlePostRequest();
        }
        return $this->showUploadForm();
    }
    
    private function handlePostRequest() {
        if (isset($_FILES["xmlfile"])) {
            return $this->handleFileUpload();
        } elseif (isset($_POST["save"])) {
            return $this->handleSave();
        }
        return $this->showError("Invalid request");
    }

    private function showError($message) {
        return render_template("error", ["message" => $message]);
    }

    private function getButtonNamesByInstance($devices) {
        $buttonNames = [];
        foreach ($devices as $device) {
            if (!empty($device["xml_instance"]) && isset($device["buttons"])) {
                $buttonNames[$device["xml_instance"]] = $device["buttons"];
            }
        }
        return $buttonNames;
    }
    
    private function handleFileUpload() {
        try {
            $xmlFile = $_FILES["xmlfile"]["tmp_name"];
            $xmlName = basename($_FILES["xmlfile"]["name"]);
            
            $this->xmlProcessor = new XMLProcessor($xmlFile, $xmlName);
            $joysticks = $this->xmlProcessor->getJoysticks();
            $devices = $this->deviceManager->matchDevicesToXML($joysticks);
            
            // Charger les noms d actions depuis le CSV
            $actionNames = $this->loadActionNames();
            
            // Préparer les données pour le template
            $data = [
                "xml" => $this->xmlProcessor->getXML(),
                "actionNames" => $actionNames,
                "actionmaps_root" => $this->xmlProcessor->getActionmapsRoot(),
                "devicesData" => $devices,
                "xmlName" => $xmlName,
                "buttonNamesByInstance" => $this->getButtonNamesByInstance($devices)
            ];
            
            return render_template("edit_form", $data);
        } catch (\Exception $e) {
            return $this->showError($e->getMessage());
        }
    }
    
    private function handleSave() {
        try {
            $xmlName = $_POST["xmlname"];
            $xmlData = $_POST["xmldata"];
            
            // Créer un fichier temporaire avec les données
            $xmlFile = tempnam(sys_get_temp_dir(), "xml");
            file_put_contents($xmlFile, $xmlData);
            
            $this->xmlProcessor = new XMLProcessor($xmlFile, $xmlName);
            $this->xmlProcessor->updateFromPost($_POST);
            
            // Générer le nouveau nom de fichier
            $now = date("Ymd_His");
            $downloadName = "modified_" . pathinfo($xmlName, PATHINFO_FILENAME) . "_" . $now . ".xml";
            
            return render_template("success", [
                "xmlContent" => $this->xmlProcessor->getXML()->asXML(),
                "downloadName" => $downloadName
            ]);
        } catch (\Exception $e) {
            return $this->showError($e->getMessage());
        }
    }
    
    private function showUploadForm() {
        return render_template("upload_form", [
            "buttonNamesByInstance" => [],
            "devicesData" => []
        ]);
    }
    
    private function loadActionNames() {
        $actionNames = [];
        $csvPath = $this->config["files_dir"] . "/actions_keybind.csv";
        
        if (file_exists($csvPath) && ($handle = fopen($csvPath, "r")) !== false) {
            // Skip header
            fgetcsv($handle, 1000, ",");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if (count($data) >= 2) {
                    $actionNames[$data[0]] = $data[1];
                }
            }
            fclose($handle);
        }
        
        return $actionNames;
    }
}
