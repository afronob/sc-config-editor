<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étape 4: Résumé et Téléchargement</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/device-management.css">
    <style>
        .step-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .step-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: 100%; /* Step 4 of 4 - Complete */
            transition: width 0.3s ease;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-icon {
            font-size: 1.5em;
            margin-right: 15px;
            color: #28a745;
        }
        
        .card-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .device-summary {
            margin-bottom: 20px;
        }
        
        .device-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .device-name {
            font-weight: bold;
            color: #333;
        }
        
        .device-type {
            color: #666;
            font-size: 0.9em;
        }
        
        .device-stats {
            text-align: right;
            color: #28a745;
            font-weight: bold;
        }
        
        .binding-summary {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .binding-group {
            margin-bottom: 20px;
        }
        
        .binding-group-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 6px;
        }
        
        .binding-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .binding-input {
            font-family: monospace;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .binding-action {
            color: #666;
            font-size: 0.9em;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .download-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            border: 1px solid #e0e0e0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .download-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .download-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .btn-nav {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-prev {
            background: #6c757d;
            color: white;
        }
        
        .btn-prev:hover {
            background: #5a6268;
        }
        
        .btn-restart {
            background: #17a2b8;
            color: white;
        }
        
        .btn-restart:hover {
            background: #138496;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        
        .file-info h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .file-details {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px 20px;
            font-size: 0.9em;
        }
        
        .file-label {
            font-weight: bold;
            color: #666;
        }
        
        .file-value {
            color: #333;
        }
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .warning-message {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .download-options {
                flex-direction: column;
                align-items: center;
            }
            
            .navigation {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="step-container">
        <div class="step-header">
            <h1>Étape 4: Configuration Terminée</h1>
            <p>Votre configuration Star Citizen est prête ! Consultez le résumé et téléchargez vos fichiers.</p>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>

        <!-- Messages de statut -->
        <?php if (isset($success) && $success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <strong>Configuration terminée avec succès !</strong> Votre fichier XML a été généré et est prêt au téléchargement.
            </div>
        <?php endif; ?>

        <?php if (isset($warnings) && !empty($warnings)): ?>
            <div class="warning-message">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention :</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <?php foreach ($warnings as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Statistiques générales -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-number"><?= count($devices ?? []) ?></div>
                <div class="stat-label">Dispositifs configurés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalBindings ?? 0 ?></div>
                <div class="stat-label">Mappings configurés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($modifiedActions ?? []) ?></div>
                <div class="stat-label">Actions modifiées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= isset($xmlSize) ? round($xmlSize / 1024, 1) : '0' ?>KB</div>
                <div class="stat-label">Taille du fichier</div>
            </div>
        </div>

        <!-- Résumé détaillé -->
        <div class="summary-grid">
            <!-- Dispositifs configurés -->
            <div class="summary-card">
                <div class="card-header">
                    <i class="fas fa-gamepad card-icon"></i>
                    <span class="card-title">Dispositifs Configurés</span>
                </div>
                
                <div class="device-summary">
                    <?php if (!empty($devices)): ?>
                        <?php foreach ($devices as $deviceId => $device): ?>
                            <?php $bindingCount = count($bindings[$deviceId] ?? []); ?>
                            <div class="device-item">
                                <div>
                                    <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                                    <div class="device-type"><?= htmlspecialchars($device['type'] ?? 'Dispositif générique') ?></div>
                                </div>
                                <div class="device-stats">
                                    <?= $bindingCount ?> mapping<?= $bindingCount > 1 ? 's' : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 20px;">
                            Aucun dispositif configuré
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mappings par catégorie -->
            <div class="summary-card">
                <div class="card-header">
                    <i class="fas fa-keyboard card-icon"></i>
                    <span class="card-title">Mappings par Catégorie</span>
                </div>
                
                <div class="binding-summary">
                    <?php if (!empty($bindingsByCategory)): ?>
                        <?php foreach ($bindingsByCategory as $category => $categoryBindings): ?>
                            <div class="binding-group">
                                <div class="binding-group-title">
                                    <?= htmlspecialchars($category) ?> (<?= count($categoryBindings) ?>)
                                </div>
                                <?php foreach ($categoryBindings as $binding): ?>
                                    <div class="binding-item">
                                        <span class="binding-input"><?= htmlspecialchars($binding['input']) ?></span>
                                        <span class="binding-action"><?= htmlspecialchars($binding['action']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 20px;">
                            Aucun mapping configuré
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Section de téléchargement -->
        <div class="download-section">
            <h2><i class="fas fa-download"></i> Téléchargement</h2>
            <p>Votre configuration Star Citizen est prête. Téléchargez les fichiers ci-dessous :</p>
            
            <div class="file-info">
                <h4>Informations sur le fichier XML :</h4>
                <div class="file-details">
                    <span class="file-label">Nom du fichier :</span>
                    <span class="file-value"><?= htmlspecialchars($xmlFilename ?? 'defaultProfile.xml') ?></span>
                    
                    <span class="file-label">Taille :</span>
                    <span class="file-value"><?= isset($xmlSize) ? round($xmlSize / 1024, 2) . ' KB' : 'N/A' ?></span>
                    
                    <span class="file-label">Dispositifs :</span>
                    <span class="file-value"><?= count($devices ?? []) ?> configuré(s)</span>
                    
                    <span class="file-label">Mappings :</span>
                    <span class="file-value"><?= $totalBindings ?? 0 ?> au total</span>
                    
                    <span class="file-label">Créé le :</span>
                    <span class="file-value"><?= date('d/m/Y à H:i:s') ?></span>
                </div>
            </div>
            
            <div class="download-options">
                <a href="step_by_step_handler.php?action=download&type=xml" class="download-btn btn-primary">
                    <i class="fas fa-file-code"></i>
                    Télécharger XML
                </a>
                
                <?php if (isset($hasNewDevices) && $hasNewDevices): ?>
                    <a href="step_by_step_handler.php?action=download&type=mappings" class="download-btn btn-secondary">
                        <i class="fas fa-file-export"></i>
                        Mappings dispositifs
                    </a>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px; text-align: left;">
                <h4><i class="fas fa-info-circle"></i> Instructions d'installation :</h4>
                <ol style="margin: 10px 0 0 20px; text-align: left;">
                    <li>Fermez complètement Star Citizen</li>
                    <li>Placez le fichier XML téléchargé dans : <code>%APPDATA%\Roberts Space Industries\StarCitizen\LIVE\Controls\Mappings\</code></li>
                    <li>Redémarrez Star Citizen</li>
                    <li>Dans les options de contrôle, sélectionnez votre configuration personnalisée</li>
                </ol>
            </div>
        </div>

        <!-- Navigation finale -->
        <div class="navigation">
            <a href="step_by_step_handler.php?step=3" class="btn-nav btn-prev">
                <i class="fas fa-arrow-left"></i>
                Retour à l'édition
            </a>
            
            <div style="text-align: center;">
                <span style="color: #28a745; font-weight: bold;">
                    <i class="fas fa-check-circle"></i>
                    Configuration terminée !
                </span>
            </div>
            
            <a href="step_by_step_handler.php?action=restart" class="btn-nav btn-restart">
                <i class="fas fa-redo"></i>
                Nouvelle configuration
            </a>
        </div>
    </div>

    <script>
        // Animation des statistiques au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = Math.ceil(finalValue / 20);
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = currentValue + (stat.textContent.includes('KB') ? 'KB' : '');
                }, 50);
            });
        });
        
        // Copier le chemin d'installation dans le presse-papier
        function copyInstallPath() {
            const path = '%APPDATA%\\Roberts Space Industries\\StarCitizen\\LIVE\\Controls\\Mappings\\';
            navigator.clipboard.writeText(path).then(() => {
                alert('Chemin copié dans le presse-papier !');
            });
        }
        
        // Confirmation avant redémarrage
        document.querySelector('.btn-restart').addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir commencer une nouvelle configuration ? Cela supprimera la session actuelle.')) {
                e.preventDefault();
            }
        });
        
        // Tracking des téléchargements
        document.querySelectorAll('.download-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.href.includes('type=xml') ? 'XML' : 'Mappings';
                console.log(`Téléchargement ${type} initié`);
                
                // Afficher un message de confirmation
                setTimeout(() => {
                    if (type === 'XML') {
                        alert('Téléchargement du fichier XML en cours...\n\nN\'oubliez pas de placer le fichier dans le dossier Star Citizen !');
                    }
                }, 100);
            });
        });
    </script>
</body>
</html>
