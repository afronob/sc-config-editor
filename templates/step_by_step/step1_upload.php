<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Star Citizen Config Editor - <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .step-container {
            max-width: 800px;
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
            position: relative;
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
        
        .upload-zone {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            transition: border-color 0.3s;
        }
        
        .upload-zone:hover {
            border-color: #667eea;
        }
        
        .upload-zone.dragover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .file-input {
            display: none;
        }
        
        .upload-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }
        
        .upload-button:hover {
            background: #5a6fd8;
        }
        
        .file-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
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
        
        .next-button:hover {
            background: #218838;
        }
        
        .reset-button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .reset-button:hover {
            background: #c82333;
        }
        
        /* Styles pour la section d'analyse terminée */
        .analysis-complete-section {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .btn-restart {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn-restart:hover {
            background: #c82333;
        }
        
        .btn-continue {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
            display: inline-block;
        }
        
        .btn-continue:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
        
        /* Styles pour les informations du fichier uploadé */
        .file-uploaded-info {
            background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
        }
        
        .file-uploaded-info h3 {
            color: #155724;
            margin-bottom: 20px;
            font-size: 1.4em;
        }
        
        .file-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .file-detail-item {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }
        
        .stat-highlight {
            font-weight: bold;
            color: #2c5aa0;
            font-size: 1.1em;
        }
        
        .next-step-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 15px;
        }
        
        .btn-continue {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 10px;
            transition: transform 0.2s ease;
        }
        
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
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
            <div class="step-item <?= $step >= 1 ? ($step == 1 ? 'active' : 'completed') : '' ?>">
                <strong>1</strong><br>Upload XML
            </div>
            <div class="step-item <?= $step >= 2 ? ($step == 2 ? 'active' : 'completed') : '' ?>">
                <strong>2</strong><br>Devices
            </div>
            <div class="step-item <?= $step >= 3 ? ($step == 3 ? 'active' : 'completed') : '' ?>">
                <strong>3</strong><br>Édition
            </div>
            <div class="step-item <?= $step >= 4 ? ($step == 4 ? 'active' : 'completed') : '' ?>">
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
            
            <!-- Informations du fichier uploadé -->
            <?php if (isset($xmlName) && isset($xmlStats)): ?>
                <div class="file-uploaded-info">
                    <h3>📄 Fichier chargé avec succès</h3>
                    <div class="file-details">
                        <div class="file-detail-item">
                            <strong>Nom du fichier:</strong> <?= htmlspecialchars($xmlName) ?>
                        </div>
                        <div class="file-detail-item">
                            <strong>Actions trouvées:</strong> <span class="stat-highlight"><?= $xmlStats['total'] ?></span>
                        </div>
                        <div class="file-detail-item">
                            <strong>Actions configurées:</strong> <span class="stat-highlight"><?= $xmlStats['used'] ?></span>
                        </div>
                        <?php if ($xmlStats['total'] > 0): ?>
                            <div class="file-detail-item">
                                <strong>Pourcentage configuré:</strong> 
                                <span class="stat-highlight"><?= round(($xmlStats['used'] / $xmlStats['total']) * 100, 1) ?>%</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informations sur les dispositifs -->
                        <?php if (isset($xmlDevices) && !empty($xmlDevices)): ?>
                            <div class="file-detail-item" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                                <strong>Dispositifs configurés:</strong> <span class="stat-highlight"><?= count($xmlDevices) ?></span>
                            </div>
                            <div class="devices-list" style="margin-top: 10px; font-size: 14px; color: #666;">
                                <?php foreach ($xmlDevices as $device): ?>
                                    <div style="margin: 5px 0; padding-left: 20px;">
                                        <strong><?= htmlspecialchars($device['instance']) ?>:</strong> <?= htmlspecialchars($device['product']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Contenu de l'étape 1: Upload -->
            <?php if (!isset($xmlName)): ?>
                <div class="upload-section">
                    <h3>🚀 Chargement de votre fichier de configuration Star Citizen</h3>
                    
                    <p>Sélectionnez le fichier XML de configuration que vous souhaitez modifier. Ce fichier se trouve généralement dans :</p>
                    <code>USER/game/data/controls/mappings/</code>
                    
                    <form method="post" enctype="multipart/form-data" action="?step=1&action=upload">
                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-content">
                                <h4>📁 Glissez-déposez votre fichier XML ici</h4>
                                <p>ou</p>
                                <button type="button" class="upload-button" onclick="document.getElementById('xmlfile').click()">
                                    Parcourir les fichiers
                                </button>
                                <input type="file" 
                                       id="xmlfile" 
                                       name="xmlfile" 
                                       accept=".xml" 
                                       required 
                                       class="file-input">
                            </div>
                        </div>
                        
                        <div id="fileInfo" class="file-info" style="display: none;">
                            <h4>📄 Fichier sélectionné:</h4>
                            <div id="fileName"></div>
                            <div id="fileSize"></div>
                        </div>
                        
                        <button type="submit" class="next-button" id="uploadSubmit" disabled>
                            📤 Uploader et analyser →
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Section d'analyse terminée avec boutons d'action -->
                <div class="analysis-complete-section">
                    <h3>🔍 Analyse terminée</h3>
                    <p>Votre fichier a été analysé avec succès. Voici les informations détectées :</p>
                    
                    <div class="action-buttons">
                        <button type="button" 
                                class="btn-restart" 
                                onclick="resetSession()" 
                                title="Charger un autre fichier XML">
                            🔄 Charger un autre fichier
                        </button>
                        <a href="step_by_step_handler.php?step=2" class="btn-continue">
                            Continuer vers l'étape 2 →
                        </a>                    </div>
                </div>
            <?php endif; ?>

            <div style="clear: both;"></div>
            
            <!-- Informations supplémentaires -->
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h4>ℹ️ Informations importantes</h4>
                <ul>
                    <li><strong>Format accepté:</strong> Fichiers XML de configuration Star Citizen uniquement</li>
                    <li><strong>Taille maximum:</strong> 10 MB</li>
                    <li><strong>Sécurité:</strong> Votre fichier est traité localement et n'est pas stocké sur nos serveurs</li>
                    <li><strong>Sauvegarde:</strong> Il est recommandé de faire une copie de votre fichier original</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Gestion du drag & drop
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('xmlfile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadSubmit = document.getElementById('uploadSubmit');

        // Prévenir les comportements par défaut
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Effets visuels pour le drag & drop
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadZone.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadZone.classList.remove('dragover');
        }

        // Gestion du drop
        uploadZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        }

        // Gestion de la sélection de fichier
        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = fileInput.files[0];
            
            if (file) {
                // Vérification du type de fichier
                if (!file.name.toLowerCase().endsWith('.xml')) {
                    alert('Veuillez sélectionner un fichier XML');
                    return;
                }
                
                // Affichage des informations du fichier
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.style.display = 'block';
                uploadSubmit.disabled = false;
                
                // Validation basique du contenu
                validateXMLFile(file);
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function validateXMLFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const content = e.target.result;
                
                // Vérification basique du contenu Star Citizen
                if (content.includes('<ActionMaps') || content.includes('actionmap')) {
                    fileInfo.style.background = '#d4edda';
                    fileInfo.style.borderColor = '#c3e6cb';
                } else {
                    fileInfo.style.background = '#fff3cd';
                    fileInfo.style.borderColor = '#ffeaa7';
                    fileName.innerHTML += ' <span style="color: #856404;">(⚠️ Format non reconnu)</span>';
                }
            };
            reader.readAsText(file);
        }
        
        // Fonction de réinitialisation
        function resetSession() {
            if (confirm('⚠️ Voulez-vous vraiment réinitialiser la session et charger un nouveau fichier ?\n\nCela supprimera toutes les données actuelles.')) {
                // Nettoyer les données côté client avant redirection
                try {
                    // Nettoyer localStorage
                    localStorage.removeItem('sc_devices');
                    localStorage.removeItem('sc_config');
                    
                    // Nettoyer autres données si présentes
                    const scKeys = [];
                    for (let i = 0; i < localStorage.length; i++) {
                        const key = localStorage.key(i);
                        if (key && (key.startsWith('sc_') || key.startsWith('device_'))) {
                            scKeys.push(key);
                        }
                    }
                    scKeys.forEach(key => localStorage.removeItem(key));
                    
                    console.log('🧹 Données côté client nettoyées avant reset session');
                } catch (error) {
                    console.warn('Erreur lors du nettoyage côté client:', error);
                }
                
                // Redirection vers reset serveur
                window.location.href = 'step_by_step_handler.php?action=restart';
            }
        }
    </script>
</body>
</html>
