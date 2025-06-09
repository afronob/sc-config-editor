<!DOCTYPE html>
<html lang="fr">
<head>
    <meta char        .devices-grid {
            display: none; /* Masqué par défaut jusqu'à la détection */
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .devices-grid.visible {
            display: grid; /* Affiché après détection */
        }tf-8">
    <title>Star Citizen Config Editor - <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .step-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .step-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 0;
        }
        
        .step-item {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            margin: 0 2px;
            border-radius: 5px;
        }
        
        .step-item.active {
            background: #667eea;
            color: white;
        }
        
        .step-item.completed {
            background: #27ae60;
            color: white;
        }
        
        .step-content {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .devices-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .devices-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        
        .device-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .device-info {
            flex-grow: 1;
        }
        
        .device-name {
            font-weight: bold;
            color: #333;
        }
        
        .device-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 3px;
            margin-left: 10px;
        }
        
        .status-connected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-new {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-known {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .detect-button {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
            width: 100%;
        }
        
        .detect-button:hover {
            background: #138496;
        }
        
        .add-device-form {
            background: #fff;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .form-group {
            margin: 15px 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .next-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            float: right;
            margin-top: 20px;
        }
        
        .device-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Styles pour l'encart contextuel */
        .xml-context-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin: 15px 0;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .xml-context-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .xml-context-icon {
            background: #667eea;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 12px;
        }
        
        .xml-context-title {
            font-weight: bold;
            color: #495057;
            font-size: 14px;
        }
        
        .xml-context-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }
        
        .xml-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .xml-stat-item:last-child {
            border-bottom: none;
        }
        
        .xml-stat-label {
            font-size: 13px;
            color: #6c757d;
        }
        
        .xml-stat-value {
            font-weight: bold;
            color: #495057;
        }
        
        .xml-devices-list {
            max-height: 80px;
            overflow-y: auto;
            font-size: 12px;
        }
        
        .xml-device-item {
            padding: 4px 0;
            color: #6c757d;
            border-bottom: 1px dotted #dee2e6;
        }
        
        .xml-device-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="step-container">
        <!-- En-tête avec progression -->
        <div class="step-header">
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($description) ?></p>
        </div>
        
        <!-- Barre de progression -->
        <div class="step-progress">
            <div class="step-item completed">
                <strong>1</strong><br>Upload XML
            </div>
            <div class="step-item active">
                <strong>2</strong><br>Devices
            </div>
            <div class="step-item">
                <strong>3</strong><br>Édition
            </div>
            <div class="step-item">
                <strong>4</strong><br>Téléchargement
            </div>
        </div>
        
        <div class="step-content">
            <!-- Messages d'alerte -->
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <strong>Erreur:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <strong>Succès:</strong> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($info)): ?>
                <div class="alert alert-info">
                    <strong>Info:</strong> <?= htmlspecialchars($info) ?>
                </div>
            <?php endif; ?>
            
            <!-- Encart contextuel XML -->
            <?php if ((isset($xmlStats) && !empty($xmlStats)) || (isset($xmlDevices) && !empty($xmlDevices))): ?>
                <div class="xml-context-card">
                    <div class="xml-context-header">
                        <div class="xml-context-icon">📄</div>
                        <div class="xml-context-title">
                            Configuration XML chargée<?= isset($xmlName) ? ' : ' . htmlspecialchars($xmlName) : '' ?>
                        </div>
                    </div>
                    
                    <div class="xml-context-content">
                        <!-- Statistiques des actions -->
                        <?php if (isset($xmlStats) && !empty($xmlStats)): ?>
                            <div>
                                <div class="xml-stat-item">
                                    <span class="xml-stat-label">Actions totales :</span>
                                    <span class="xml-stat-value"><?= $xmlStats['total'] ?></span>
                                </div>
                                <div class="xml-stat-item">
                                    <span class="xml-stat-label">Actions configurées :</span>
                                    <span class="xml-stat-value"><?= $xmlStats['used'] ?></span>
                                </div>
                                <?php if ($xmlStats['total'] > 0): ?>
                                    <div class="xml-stat-item">
                                        <span class="xml-stat-label">Pourcentage :</span>
                                        <span class="xml-stat-value"><?= round(($xmlStats['used'] / $xmlStats['total']) * 100, 1) ?>%</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Liste des dispositifs -->
                        <?php if (isset($xmlDevices) && !empty($xmlDevices)): ?>
                            <div>
                                <div class="xml-stat-item">
                                    <span class="xml-stat-label">Dispositifs XML :</span>
                                    <span class="xml-stat-value"><?= count($xmlDevices) ?></span>
                                </div>
                                <div class="xml-devices-list">
                                    <?php foreach ($xmlDevices as $device): ?>
                                        <div class="xml-device-item">
                                            <strong><?= htmlspecialchars($device['instance']) ?>:</strong> <?= htmlspecialchars($device['product']) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Section de détection des devices -->
            <div class="detection-section">
                <h3>🎮 Détection automatique des dispositifs</h3>
                <p>Connectez vos manettes et cliquez sur "Détecter" pour identifier automatiquement vos dispositifs.</p>
                
                <button class="detect-button" onclick="detectDevices()">
                    🔍 Détecter les dispositifs connectés
                </button>
            </div>

            <!-- Section d'information sur l'édition manuelle -->
            <div class="manual-edit-info" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border: 1px solid #81c784; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 15px;">✏️</div>
                <h4 style="margin-bottom: 15px; color: #2e7d32;">Édition manuelle disponible</h4>
                <p style="margin-bottom: 15px; color: #424242;">
                    Vous pouvez également passer directement à l'<strong>édition manuelle</strong> de votre fichier XML 
                    sans détecter de dispositifs. Cette option est utile pour :
                </p>
                <ul style="text-align: left; max-width: 600px; margin: 0 auto 20px auto; color: #424242;">
                    <li>Éditer manuellement les mappings existants</li>
                    <li>Ajouter des dispositifs qui ne sont pas connectés actuellement</li>
                    <li>Modifier des paramètres avancés dans le XML</li>
                    <li>Corriger des erreurs de configuration</li>
                </ul>
                <div class="action-buttons" style="justify-content: center; margin-top: 20px;">
                    <a href="?step=3" class="btn btn-success" style="background: #4caf50; padding: 12px 25px; font-size: 16px; text-decoration: none; border-radius: 6px;">
                        🚀 Passer à l'édition manuelle
                    </a>
                </div>
                <p style="font-size: 14px; color: #666; margin-top: 15px; font-style: italic;">
                    💡 Vous pourrez toujours revenir au Step 2 pour détecter des dispositifs si nécessaire
                </p>
            </div>
            
            <!-- Sections des dispositifs - masquées par défaut -->
            <div class="devices-grid">
                <!-- Dispositifs connus -->
                <div class="devices-section">
                    <h4>✅ Dispositifs reconnus (<?= isset($devices) ? count($devices) : 0 ?>)</h4>
                    <?php if (isset($devices) && !empty($devices)): ?>
                        <?php foreach ($devices as $device): ?>
                            <div class="device-item">
                                <div class="device-info">
                                    <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                                    <div class="device-details">
                                        Instance: <?= htmlspecialchars($device['xml_instance'] ?? 'N/A') ?><br>
                                        GUID: <?= htmlspecialchars(substr($device['guid'] ?? 'N/A', 0, 16)) ?>...
                                    </div>
                                </div>
                                <span class="device-status status-known">Configuré</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><em>Aucun dispositif reconnu dans le fichier XML</em></p>
                    <?php endif; ?>
                </div>
                
                <!-- Nouveaux dispositifs -->
                <div class="devices-section">
                    <h4>🆕 Nouveaux dispositifs (<?= isset($newDevices) ? count($newDevices) : 0 ?>)</h4>
                    <?php if (isset($newDevices) && !empty($newDevices)): ?>
                        <?php foreach ($newDevices as $index => $device): ?>
                            <div class="device-item">
                                <div class="device-info">
                                    <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                                    <div class="device-details">
                                        GUID: <?= htmlspecialchars(substr($device['guid'], 0, 16)) ?>...<br>
                                        Boutons: <?= $device['buttons'] ?? 'N/A' ?>, Axes: <?= $device['axes'] ?? 'N/A' ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="device-status status-new">Nouveau</span>
                                    <button class="btn btn-warning" onclick="showAddDeviceForm(<?= $index ?>)">
                                        Configurer
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Formulaire d'ajout (caché par défaut) -->
                            <div id="addForm_<?= $index ?>" class="add-device-form" style="display: none;">
                                <h5>⚙️ Configuration de: <?= htmlspecialchars($device['name']) ?></h5>
                                <form method="post" action="?step=2&action=add_device">
                                    <input type="hidden" name="device[name]" value="<?= htmlspecialchars($device['name']) ?>">
                                    <input type="hidden" name="device[guid]" value="<?= htmlspecialchars($device['guid']) ?>">
                                    <input type="hidden" name="device[buttons]" value="<?= htmlspecialchars($device['buttons'] ?? '') ?>">
                                    <input type="hidden" name="device[axes]" value="<?= htmlspecialchars($device['axes'] ?? '') ?>">
                                    
                                    <div class="form-group">
                                        <label for="instance_<?= $index ?>">Instance XML:</label>
                                        <select name="device[xml_instance]" id="instance_<?= $index ?>" required>
                                            <option value="">Sélectionner une instance...</option>
                                            <option value="js1">js1 (Joystick principal)</option>
                                            <option value="js2">js2 (Joystick secondaire)</option>
                                            <option value="js3">js3 (Joystick tertiaire)</option>
                                            <option value="js4">js4 (Joystick quaternaire)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mapping_<?= $index ?>">Template de mapping:</label>
                                        <select name="device[mapping_template]" id="mapping_<?= $index ?>">
                                            <option value="generic">Générique</option>
                                            <option value="hotas">HOTAS</option>
                                            <option value="gamepad">Gamepad</option>
                                            <option value="throttle">Throttle</option>
                                        </select>
                                    </div>
                                    
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="create_mapping" value="1" id="createMapping_<?= $index ?>" checked>
                                        <label for="createMapping_<?= $index ?>">Créer le fichier de mapping JSON</label>
                                    </div>
                                    
                                    <div class="checkbox-group">
                                        <input type="checkbox" name="add_to_xml" value="1" id="addToXml_<?= $index ?>" checked>
                                        <label for="addToXml_<?= $index ?>">Ajouter automatiquement au fichier XML</label>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button type="submit" class="btn btn-success">Créer le mapping</button>
                                        <button type="button" class="btn btn-primary" onclick="hideAddDeviceForm(<?= $index ?>)">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><em>Cliquez sur "Détecter" pour identifier les nouveaux dispositifs</em></p>
                    <?php endif; ?>
                </div>
            </div>
                
            <!-- Section de progression - masquée par défaut jusqu'à la détection -->
            <div id="progressSection" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; display: none;">
                <h4>📊 Récapitulatif de la détection</h4>
                <div id="detectionSummary">
                    <!-- Le contenu sera généré par JavaScript après détection -->
                </div>
                
                <div id="completionMessage" style="display: none;">
                    <!-- Message de complétion affiché seulement si aucun nouveau dispositif -->
                </div>
            </div>
            
            <!-- Bouton suivant - masqué par défaut après détection -->
            <div id="nextButtonSection" style="display: none;">
                <a href="?step=2&action=next" class="next-button">
                    Continuer vers l'édition →
                </a>
            </div>

            <!-- Section de navigation toujours visible -->
            <div class="navigation-section" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee; background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h4 style="margin-bottom: 20px; color: #495057;">🧭 Navigation</h4>
                <div style="display: flex; gap: 15px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <p style="margin: 0; color: #6c757d; font-size: 14px;">
                            <strong>Avec dispositifs détectés :</strong> Configurez automatiquement vos mappings
                        </p>
                        <button onclick="detectDevices()" class="btn btn-primary" style="margin-top: 8px; background: #007bff;">
                            🔍 Détecter et configurer
                        </button>
                    </div>
                    <div style="border-left: 1px solid #dee2e6; height: 60px; margin: 0 10px;"></div>
                    <div style="flex: 1; min-width: 250px; text-align: right;">
                        <p style="margin: 0; color: #6c757d; font-size: 14px;">
                            <strong>Édition directe :</strong> Modifiez le XML manuellement
                        </p>
                        <a href="?step=3" class="btn btn-success" style="margin-top: 8px; background: #28a745; text-decoration: none; display: inline-block;">
                            ✏️ Édition manuelle
                        </a>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <p style="margin: 0; font-size: 13px; color: #6c757d; font-style: italic;">
                        💡 L'édition manuelle est recommandée si vous connaissez les GUID de vos dispositifs ou si vous souhaitez des configurations avancées
                    </p>
                </div>
            </div>
            
            <div style="clear: both;"></div>
        </div>
    </div>

    <script type="module">
        // Importer et initialiser le système de détection automatique
        import { DeviceAutoDetection } from '../assets/js/modules/deviceAutoDetection.js';
        import { DeviceSetupUI } from '../assets/js/modules/deviceSetupUI.js';
        
        let deviceAutoDetection = null;
        let deviceSetupUI = null;
        let detectedDevices = new Map();
        
        // Initialiser le système de détection
        async function initDeviceDetection() {
            try {
                // Nettoyer les données précédentes au cas où
                if (deviceAutoDetection) {
                    try {
                        deviceAutoDetection.clearUnknownDevices();
                    } catch (e) {
                        console.warn('Nettoyage des données précédentes:', e.message);
                    }
                }
                detectedDevices.clear();
                
                // Charger les données des dispositifs
                const response = await fetch('get_devices_data.php');
                const devicesData = await response.json();
                window.devicesDataJs = devicesData;
                
                // Initialiser le système de détection
                deviceAutoDetection = new DeviceAutoDetection();
                
                // Initialiser l'interface utilisateur de configuration
                deviceSetupUI = new DeviceSetupUI(null, deviceAutoDetection);
                
                // Rendre disponible globalement pour l'accès depuis les boutons
                window.deviceSetupUI = deviceSetupUI;
                
                // Écouter les nouveaux dispositifs détectés
                deviceAutoDetection.onNewDeviceDetected((deviceInfo) => {
                    console.log('Nouveau dispositif détecté:', deviceInfo);
                    addDeviceToNewDevicesSection(deviceInfo);
                });
                
                console.log('✅ Système de détection et interface utilisateur initialisés');
                
            } catch (error) {
                console.error('❌ Erreur d\'initialisation du système de détection:', error);
            }
        }
        
        // Fonction de détection globale accessible depuis HTML
        window.detectDevices = async function() {
            // Afficher un indicateur de chargement
            const button = document.querySelector('.detect-button');
            const originalText = button.textContent;
            button.textContent = '🔄 Détection en cours...';
            button.disabled = true;
            
            // S'assurer que les données des dispositifs sont chargées
            if (!window.devicesDataJs) {
                console.log('📥 Chargement des données des dispositifs...');
                try {
                    const response = await fetch('get_devices_data.php');
                    const devicesData = await response.json();
                    window.devicesDataJs = devicesData;
                    console.log(`✅ Données chargées: ${devicesData.length} dispositifs`);
                } catch (error) {
                    console.error('❌ Erreur chargement des données:', error);
                }
            }
            
            // Fonction de détection simplifiée qui fonctionne toujours
            console.log('🔍 Début de la détection des dispositifs');
            
            // Réinitialiser l'affichage
            clearDynamicDevices();
            
            // Vérifier les gamepads connectés via l'API Gamepad
            const gamepads = navigator.getGamepads();
            let devicesFound = 0;
            let unknownDevicesFound = 0;
            
            console.log(`📊 ${gamepads.length} slots de gamepad vérifiés`);
            
            for (let i = 0; i < gamepads.length; i++) {
                if (gamepads[i]) {
                    devicesFound++;
                    console.log(`🎮 Dispositif trouvé: ${gamepads[i].id}`);
                    console.log(`   - Index: ${i}`);
                    console.log(`   - Boutons: ${gamepads[i].buttons.length}`);
                    console.log(`   - Axes: ${gamepads[i].axes.length}`);
                    
                    // Pour l'instant, considérons tous les dispositifs comme nouveaux
                    // (nous améliorerons la logique de comparaison plus tard)
                    const isKnown = checkIfDeviceIsKnown(gamepads[i]);
                    
                    if (isKnown) {
                        addDeviceToKnownDevicesSection(gamepads[i]);
                    } else {
                        unknownDevicesFound++;
                        addDeviceToNewDevicesSection(gamepads[i]);
                    }
                }
            }
            
            // Mettre à jour l'interface
            updateDetectionResults(devicesFound, unknownDevicesFound);
            
            // Afficher la grille des dispositifs après la détection
            const devicesGrid = document.querySelector('.devices-grid');
            const detectionInvitation = document.querySelector('.detection-invitation');
            
            if (devicesGrid) {
                devicesGrid.classList.add('visible');
            }
            
            if (detectionInvitation) {
                detectionInvitation.style.display = 'none';
            }
            
            // Restaurer le bouton
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 1000);
            
            console.log(`✅ Détection terminée: ${devicesFound} dispositifs trouvés, ${unknownDevicesFound} nouveaux`);
        }
        
        function checkIfDeviceIsKnown(gamepad) {
            // Logique corrigée : utiliser la même méthode que deviceAutoDetection.js
            if (!window.devicesDataJs || !Array.isArray(window.devicesDataJs)) {
                console.log('⚠️ Données des dispositifs non disponibles, tous considérés comme nouveaux');
                return false;
            }
            
            // Extraire vendor_id et product_id du gamepad (même logique que deviceAutoDetection.js)
            const vendorMatch = gamepad.id.match(/Vendor:\s*([0-9a-fA-F]{4})/i);
            const productMatch = gamepad.id.match(/Product:\s*([0-9a-fA-F]{4})/i);
            
            if (vendorMatch && productMatch) {
                const gamepadVendor = `0x${vendorMatch[1].toLowerCase()}`;
                const gamepadProduct = `0x${productMatch[1].toLowerCase()}`;
                
                console.log(`🔍 Recherche dispositif - Vendor: ${gamepadVendor}, Product: ${gamepadProduct}`);
                
                // Vérifier par vendor/product ID (méthode principale)
                const found = window.devicesDataJs.find(device => {
                    return device.vendor_id && device.product_id &&
                           device.vendor_id.toLowerCase() === gamepadVendor &&
                           device.product_id.toLowerCase() === gamepadProduct;
                });
                
                if (found) {
                    console.log(`✅ Dispositif reconnu par ID: ${gamepad.id} -> ${found.id}`);
                    return true;
                }
            }
            
            // Fallback : recherche par nom (utiliser device.id au lieu de device.name)
            const gamepadIdSimple = gamepad.id.replace(/\(Vendor:.*$/, '').trim().toLowerCase();
            const foundByName = window.devicesDataJs.find(device => {
                if (!device.id) return false;
                
                const deviceIdSimple = device.id.replace(/\(Vendor:.*$/, '').trim().toLowerCase();
                return gamepadIdSimple.includes(deviceIdSimple) || deviceIdSimple.includes(gamepadIdSimple);
            });
            
            if (foundByName) {
                console.log(`✅ Dispositif reconnu par nom: ${gamepad.id} -> ${foundByName.id}`);
                return true;
            }
            
            console.log(`❓ Dispositif non reconnu: ${gamepad.id}`);
            return false;
        }
        
        function clearDynamicDevices() {
            // Supprimer tous les dispositifs dynamiquement ajoutés
            const dynamicDevices = document.querySelectorAll('.device-item.dynamic-device');
            dynamicDevices.forEach(item => item.remove());
            
            // Remettre à zéro les messages de détection
            const messages = document.querySelectorAll('.detection-result-message');
            messages.forEach(msg => msg.remove());
            
            // Masquer la grille des dispositifs lors du nettoyage
            const devicesGrid = document.querySelector('.devices-grid');
            const detectionInvitation = document.querySelector('.detection-invitation');
            
            if (devicesGrid) {
                devicesGrid.classList.remove('visible');
            }
            
            if (detectionInvitation) {
                detectionInvitation.style.display = 'block';
            }
        }
        
        function clearNewDevicesSection() {
            const newDevicesSection = document.querySelector('.devices-section:last-child');
            if (newDevicesSection) {
                const existingDevices = newDevicesSection.querySelectorAll('.device-item:not(.static-device)');
                existingDevices.forEach(item => item.remove());
            }
        }
        
        function addDeviceToKnownDevicesSection(gamepad) {
            const knownDevicesSection = document.querySelector('.devices-section:first-child');
            if (!knownDevicesSection) return;
            
            const deviceHtml = `
                <div class="device-item dynamic-device">
                    <div class="device-info">
                        <div class="device-name">${gamepad.id}</div>
                        <div class="device-details">
                            Connecté physiquement<br>
                            Boutons: ${gamepad.buttons.length}, Axes: ${gamepad.axes.length}
                        </div>
                    </div>
                    <span class="device-status status-known">Reconnu</span>
                </div>
            `;
            
            knownDevicesSection.insertAdjacentHTML('beforeend', deviceHtml);
        }
        
        function addDeviceToNewDevicesSection(gamepad) {
            const newDevicesSection = document.querySelector('.devices-section:last-child');
            if (!newDevicesSection) return;
            
            // Extraire les IDs vendor/product directement du gamepad
            const extractVendorProductId = (gamepadId) => {
                const vendor = gamepadId.match(/Vendor:\s*([0-9a-fA-F]{4})/);
                const product = gamepadId.match(/Product:\s*([0-9a-fA-F]{4})/);
                return {
                    vendor: vendor ? vendor[1] : null,
                    product: product ? product[1] : null
                };
            };
            
            const ids = extractVendorProductId(gamepad.id);
            const deviceKey = ids.vendor && ids.product ? `${ids.vendor}_${ids.product}` : gamepad.id;
            
            // CORRECTION: Ajouter le dispositif au module DeviceAutoDetection
            if (deviceAutoDetection) {
                const deviceInfo = {
                    id: gamepad.id,
                    name: gamepad.id,
                    vendor_id: ids.vendor ? `0x${ids.vendor.toLowerCase()}` : null,
                    product_id: ids.product ? `0x${ids.product.toLowerCase()}` : null,
                    buttons: gamepad.buttons.length,
                    axes: gamepad.axes.length,
                    gamepad: gamepad
                };
                
                // Ajouter le dispositif à la map des dispositifs inconnus
                deviceAutoDetection.unknownDevices.set(deviceKey, deviceInfo);
                console.log(`📝 Dispositif ajouté au module: ${deviceKey}`, deviceInfo);
            }
            
            const deviceHtml = `
                <div class="device-item dynamic-device" data-device-key="${deviceKey}">
                    <div class="device-info">
                        <div class="device-name">${gamepad.id}</div>
                        <div class="device-details">
                            Vendor ID: ${ids.vendor ? `0x${ids.vendor}` : 'N/A'}<br>
                            Product ID: ${ids.product ? `0x${ids.product}` : 'N/A'}<br>
                            Boutons: ${gamepad.buttons.length}, Axes: ${gamepad.axes.length}
                        </div>
                    </div>
                    <div>
                        <span class="device-status status-new">Nouveau</span>
                        <button class="btn btn-warning" onclick="startDeviceSetup('${deviceKey}')">
                            Configurer
                        </button>
                    </div>
                </div>
            `;
            
            newDevicesSection.insertAdjacentHTML('beforeend', deviceHtml);
        }
        
        function updateDetectionResults(totalDevices, unknownDevices) {
            // Mettre à jour les compteurs dans les titres
            const knownSection = document.querySelector('.devices-section:first-child h4');
            const newSection = document.querySelector('.devices-section:last-child h4');
            
            const knownCount = totalDevices - unknownDevices;
            
            if (knownSection) {
                knownSection.textContent = `✅ Dispositifs reconnus (${knownCount})`;
            }
            
            if (newSection) {
                newSection.textContent = `🆕 Nouveaux dispositifs (${unknownDevices})`;
            }
            
            // Afficher la section de progression avec le récapitulatif
            showProgressSection(knownCount, unknownDevices, totalDevices);
            
            // Afficher un message de résultat
            showDetectionMessage(totalDevices, unknownDevices);
        }
        
        function showProgressSection(knownCount, unknownDevices, totalDevices) {
            const progressSection = document.getElementById('progressSection');
            const detectionSummary = document.getElementById('detectionSummary');
            const completionMessage = document.getElementById('completionMessage');
            const nextButtonSection = document.getElementById('nextButtonSection');
            
            // Afficher la section de progression
            progressSection.style.display = 'block';
            
            // Générer le récapitulatif
            detectionSummary.innerHTML = `
                <ul>
                    <li><strong>Dispositifs détectés:</strong> ${totalDevices}</li>
                    <li><strong>Dispositifs reconnus:</strong> ${knownCount}</li>
                    <li><strong>Nouveaux dispositifs:</strong> ${unknownDevices}</li>
                    <li><strong>Configuration requise:</strong> ${unknownDevices > 0 ? 'Oui' : 'Non'}</li>
                </ul>
            `;
            
            // Afficher le message de complétion et le bouton suivant seulement si aucun nouveau dispositif
            if (unknownDevices === 0 && totalDevices > 0) {
                completionMessage.innerHTML = `
                    <div class="alert alert-success">
                        ✅ Tous vos dispositifs sont déjà configurés ! Vous pouvez passer à l'étape suivante.
                    </div>
                `;
                completionMessage.style.display = 'block';
                nextButtonSection.style.display = 'block';
            } else {
                completionMessage.style.display = 'none';
                if (unknownDevices > 0) {
                    nextButtonSection.style.display = 'none'; // Masquer le bouton tant qu'il y a des dispositifs à configurer
                } else if (totalDevices === 0) {
                    // Aucun dispositif détecté
                    nextButtonSection.style.display = 'none';
                }
            }
        }
        
        function showDetectionMessage(totalDevices, unknownDevices) {
            // Supprimer les anciens messages
            const existingMessages = document.querySelectorAll('.detection-result-message');
            existingMessages.forEach(msg => msg.remove());
            
            let message = '';
            let messageClass = '';
            
            if (totalDevices === 0) {
                message = '❌ Aucun dispositif de jeu détecté. Vérifiez que vos manettes sont bien connectées.';
                messageClass = 'alert-warning';
            } else if (unknownDevices === 0) {
                message = `✅ ${totalDevices} dispositif(s) détecté(s), tous sont déjà configurés !`;
                messageClass = 'alert-success';
            } else {
                message = `🔍 ${totalDevices} dispositif(s) détecté(s), dont ${unknownDevices} nouveau(x) nécessitant une configuration.`;
                messageClass = 'alert-info';
            }
            
            const messageHtml = `
                <div class="alert ${messageClass} detection-result-message" style="margin: 15px 0;">
                    ${message}
                </div>
            `;
            
            const detectionSection = document.querySelector('.detection-section');
            detectionSection.insertAdjacentHTML('afterend', messageHtml);
        }
        
        // Initialiser au chargement de la page
        window.addEventListener('load', initDeviceDetection);
        
        // Fonction globale pour la configuration des dispositifs
        window.startDeviceSetup = function(deviceKey) {
            if (!deviceAutoDetection) {
                alert('Système de détection non initialisé');
                return;
            }
            
            if (!deviceSetupUI) {
                alert('Interface de configuration non initialisée');
                return;
            }
            
            try {
                console.log('🔧 Démarrage de la configuration pour:', deviceKey);
                
                // Utiliser deviceSetupUI.startSetup directement - il gère la vérification en interne
                deviceSetupUI.startSetup(deviceKey);
                console.log('✅ Interface de configuration lancée');
                
            } catch (error) {
                console.error('Erreur lors du démarrage de la configuration:', error);
                
                // Gestion améliorée des erreurs
                if (error.message.includes('Device inconnu non trouvé') || error.message.includes('Device non trouvé')) {
                    console.warn('Tentative de configuration d\'un dispositif supprimé:', deviceKey);
                    if (confirm('Ce dispositif n\'est plus disponible. Voulez-vous actualiser la page pour mettre à jour la liste ?')) {
                        window.location.reload();
                    }
                } else {
                    alert('Erreur lors du démarrage de la configuration: ' + error.message);
                }
            }
        };
    </script>
    
    <script>
        function detectDevicesLegacy() {
            // Méthode de fallback - redirection vers le backend
            window.location.href = '?step=2&action=detect';
        }
        
        function showAddDeviceForm(index) {
            const form = document.getElementById('addForm_' + index);
            form.style.display = 'block';
            
            // Scroll vers le formulaire
            form.scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideAddDeviceForm(index) {
            const form = document.getElementById('addForm_' + index);
            form.style.display = 'none';
        }
        
        // Fonction de détection simplifiée accessible globalement
        function detectDevices() {
            console.log('🔍 Début de la détection des dispositifs (version simplifiée)');
            
            // Afficher un indicateur de chargement
            const button = document.querySelector('.detect-button');
            if (button) {
                const originalText = button.textContent;
                button.textContent = '🔄 Détection en cours...';
                button.disabled = true;
                
                // Restaurer le bouton après 2 secondes
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 2000);
            }
            
            // Vérifier l'API Gamepad
            if (!navigator.getGamepads) {
                alert('❌ L\'API Gamepad n\'est pas supportée par ce navigateur');
                return;
            }
            
            const gamepads = navigator.getGamepads();
            let devicesFound = 0;
            let devicesList = [];
            
            // Parcourir tous les slots de gamepad
            for (let i = 0; i < gamepads.length; i++) {
                if (gamepads[i]) {
                    devicesFound++;
                    devicesList.push({
                        id: gamepads[i].id,
                        index: i,
                        buttons: gamepads[i].buttons.length,
                        axes: gamepads[i].axes.length
                    });
                    console.log(`🎮 Dispositif ${i}: ${gamepads[i].id}`);
                }
            }
            
            // Afficher les résultats
            let message = '';
            if (devicesFound === 0) {
                message = '❌ Aucun dispositif de jeu détecté. Vérifiez que vos manettes sont bien connectées et appuyez sur un bouton pour les activer.';
            } else {
                message = `🎮 ${devicesFound} dispositif(s) détecté(s) :\n\n`;
                devicesList.forEach((device, index) => {
                    message += `${index + 1}. ${device.id}\n`;
                    message += `   • Boutons: ${device.buttons}, Axes: ${device.axes}\n\n`;
                });
                message += 'Ces dispositifs sont maintenant détectés ! La fonctionnalité de configuration automatique sera bientôt disponible.';
            }
            
            alert(message);
            
            console.log(`✅ Détection terminée: ${devicesFound} dispositifs trouvés`);
        }
        
        // Auto-détection au chargement si pas encore fait
        <?php if (!isset($detected)): ?>
        window.addEventListener('load', function() {
            // Optionnel: démarrer la détection automatiquement
            // detectDevices();
        });
        <?php endif; ?>
    </script>
</body>
</html>
