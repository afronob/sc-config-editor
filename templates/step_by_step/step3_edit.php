<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étape 3: Édition de la Configuration</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 75%; /* Step 3 of 4 */
            transition: width 0.3s ease;
        }
        
        .config-editor {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .device-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .device-item {
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .device-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .device-item.active {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .device-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .device-type {
            color: #666;
            font-size: 0.9em;
        }
        
        .binding-editor {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }
        
        .binding-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .binding-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .binding-info {
            flex: 1;
        }
        
        .binding-input {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .binding-action {
            color: #666;
            font-size: 0.9em;
        }
        
        .binding-controls {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            margin-bottom: 20px;
        }
        
        .btn-add:hover {
            background: #218838;
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
        }
        
        .btn-prev {
            background: #6c757d;
            color: white;
        }
        
        .btn-prev:hover {
            background: #5a6268;
        }
        
        .btn-next {
            background: #667eea;
            color: white;
        }
        
        .btn-next:hover {
            background: #5a67d8;
        }
        
        .no-device-selected {
            text-align: center;
            color: #666;
            padding: 60px 20px;
        }
        
        .search-box {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9em;
        }
        
        .binding-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="step-container">
        <div class="step-header">
            <h1>Étape 3: Édition de la Configuration</h1>
            <p>Configurez les mappings de vos dispositifs et personnalisez vos contrôles</p>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>

        <div class="config-editor">
            <!-- Liste des dispositifs -->
            <div class="device-list">
                <h3>Dispositifs Configurés</h3>
                <input type="text" class="search-box" placeholder="Rechercher un dispositif..." id="deviceSearch">
                
                <div id="deviceList">
                    <?php if (!empty($devices)): ?>
                        <?php foreach ($devices as $deviceId => $device): ?>
                            <div class="device-item" data-device-id="<?= htmlspecialchars($deviceId) ?>" onclick="selectDevice('<?= htmlspecialchars($deviceId) ?>')">
                                <div class="device-name"><?= htmlspecialchars($device['name']) ?></div>
                                <div class="device-type"><?= htmlspecialchars($device['type'] ?? 'Dispositif générique') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-device-selected">
                            <p>Aucun dispositif configuré</p>
                            <p><small>Retournez à l'étape 2 pour ajouter des dispositifs</small></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Éditeur de bindings -->
            <div class="binding-editor">
                <div id="bindingHeader" style="display: none;">
                    <h3 id="selectedDeviceName">Sélectionnez un dispositif</h3>
                    <button class="btn btn-add" onclick="addBinding()">
                        <i class="fas fa-plus"></i> Ajouter un mapping
                    </button>
                </div>

                <div id="bindingContent">
                    <div class="no-device-selected">
                        <i class="fas fa-mouse-pointer" style="font-size: 3em; color: #ddd; margin-bottom: 20px;"></i>
                        <h3>Sélectionnez un dispositif</h3>
                        <p>Choisissez un dispositif dans la liste de gauche pour configurer ses mappings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="navigation">
            <button class="btn-nav btn-prev" onclick="previousStep()">
                <i class="fas fa-arrow-left"></i> Précédent
            </button>
            
            <div>
                <span>Étape 3 sur 4</span>
            </div>
            
            <button class="btn-nav btn-next" onclick="nextStep()">
                Suivant <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- Modal pour éditer/ajouter un binding -->
    <div class="binding-modal" id="bindingModal">
        <div class="modal-content">
            <h3 id="modalTitle">Ajouter un mapping</h3>
            
            <form id="bindingForm">
                <div class="form-group">
                    <label class="form-label" for="inputName">Nom de l'entrée:</label>
                    <input type="text" class="form-control" id="inputName" placeholder="ex: js1_button1, js1_x" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="actionName">Action associée:</label>
                    <select class="form-control" id="actionName" required>
                        <option value="">Sélectionnez une action...</option>
                        <optgroup label="Mouvement">
                            <option value="v_pitch">Tangage (Pitch)</option>
                            <option value="v_yaw">Lacet (Yaw)</option>
                            <option value="v_roll">Roulis (Roll)</option>
                            <option value="v_strafe_vertical">Déplacement vertical</option>
                            <option value="v_strafe_lateral">Déplacement latéral</option>
                            <option value="v_strafe_longitudinal">Déplacement longitudinal</option>
                        </optgroup>
                        <optgroup label="Contrôles">
                            <option value="v_throttle">Accélération</option>
                            <option value="v_brake">Freinage</option>
                            <option value="v_weapon_trigger">Tir principal</option>
                            <option value="v_weapon_trigger_secondary">Tir secondaire</option>
                            <option value="v_target_nearest_hostile">Cibler ennemi proche</option>
                            <option value="v_shield_raise_level_forward">Boucliers avant</option>
                            <option value="v_shield_raise_level_back">Boucliers arrière</option>
                        </optgroup>
                        <optgroup label="Interface">
                            <option value="v_toggle_chat_window">Chat</option>
                            <option value="v_toggle_starmap">Carte stellaire</option>
                            <option value="v_exit_seat">Quitter siège</option>
                            <option value="v_quantum_travel_target">Voyage quantique</option>
                        </optgroup>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="bindingType">Type de mapping:</label>
                    <select class="form-control" id="bindingType">
                        <option value="single">Simple</option>
                        <option value="modifier">Avec modificateur</option>
                        <option value="sequence">Séquence</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-delete" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-add">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentDevice = null;
        let bindings = <?= json_encode($bindings ?? []) ?>;
        
        function selectDevice(deviceId) {
            // Retirer la sélection précédente
            document.querySelectorAll('.device-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Sélectionner le nouveau dispositif
            const deviceElement = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (deviceElement) {
                deviceElement.classList.add('active');
                currentDevice = deviceId;
                loadDeviceBindings(deviceId);
            }
        }
        
        function loadDeviceBindings(deviceId) {
            const headerElement = document.getElementById('bindingHeader');
            const contentElement = document.getElementById('bindingContent');
            const deviceNameElement = document.getElementById('selectedDeviceName');
            
            // Afficher l'en-tête
            headerElement.style.display = 'block';
            
            // Mettre à jour le nom du dispositif
            const deviceData = <?= json_encode($devices ?? []) ?>;
            if (deviceData[deviceId]) {
                deviceNameElement.textContent = `Mappings pour: ${deviceData[deviceId].name}`;
            }
            
            // Charger les bindings pour ce dispositif
            const deviceBindings = bindings[deviceId] || [];
            
            let bindingHTML = '<div class="binding-list">';
            
            if (deviceBindings.length === 0) {
                bindingHTML += `
                    <div class="no-device-selected">
                        <i class="fas fa-gamepad" style="font-size: 2em; color: #ddd; margin-bottom: 15px;"></i>
                        <p>Aucun mapping configuré pour ce dispositif</p>
                        <p><small>Cliquez sur "Ajouter un mapping" pour commencer</small></p>
                    </div>
                `;
            } else {
                deviceBindings.forEach((binding, index) => {
                    bindingHTML += `
                        <div class="binding-item">
                            <div class="binding-info">
                                <div class="binding-input">${binding.input}</div>
                                <div class="binding-action">${binding.action}</div>
                            </div>
                            <div class="binding-controls">
                                <button class="btn btn-edit" onclick="editBinding(${index})">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button class="btn btn-delete" onclick="deleteBinding(${index})">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            
            bindingHTML += '</div>';
            contentElement.innerHTML = bindingHTML;
        }
        
        function addBinding() {
            if (!currentDevice) {
                alert('Veuillez sélectionner un dispositif');
                return;
            }
            
            document.getElementById('modalTitle').textContent = 'Ajouter un mapping';
            document.getElementById('bindingForm').reset();
            document.getElementById('bindingModal').style.display = 'block';
        }
        
        function editBinding(index) {
            const deviceBindings = bindings[currentDevice] || [];
            const binding = deviceBindings[index];
            
            if (binding) {
                document.getElementById('modalTitle').textContent = 'Modifier le mapping';
                document.getElementById('inputName').value = binding.input;
                document.getElementById('actionName').value = binding.action;
                document.getElementById('bindingType').value = binding.type || 'single';
                document.getElementById('bindingModal').style.display = 'block';
                
                // Stocker l'index pour la modification
                document.getElementById('bindingForm').dataset.editIndex = index;
            }
        }
        
        function deleteBinding(index) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce mapping ?')) {
                if (!bindings[currentDevice]) return;
                
                bindings[currentDevice].splice(index, 1);
                saveBindings();
                loadDeviceBindings(currentDevice);
            }
        }
        
        function closeModal() {
            document.getElementById('bindingModal').style.display = 'none';
            delete document.getElementById('bindingForm').dataset.editIndex;
        }
        
        // Gestion du formulaire de binding
        document.getElementById('bindingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const binding = {
                input: document.getElementById('inputName').value,
                action: document.getElementById('actionName').value,
                type: document.getElementById('bindingType').value
            };
            
            if (!bindings[currentDevice]) {
                bindings[currentDevice] = [];
            }
            
            const editIndex = this.dataset.editIndex;
            if (editIndex !== undefined) {
                // Modification
                bindings[currentDevice][parseInt(editIndex)] = binding;
            } else {
                // Ajout
                bindings[currentDevice].push(binding);
            }
            
            saveBindings();
            loadDeviceBindings(currentDevice);
            closeModal();
        });
        
        function saveBindings() {
            // Sauvegarder en session
            fetch('step_by_step_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'save_bindings',
                    device: currentDevice,
                    bindings: bindings[currentDevice] || []
                })
            });
        }
        
        // Recherche de dispositifs
        document.getElementById('deviceSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const deviceItems = document.querySelectorAll('.device-item');
            
            deviceItems.forEach(item => {
                const deviceName = item.querySelector('.device-name').textContent.toLowerCase();
                const deviceType = item.querySelector('.device-type').textContent.toLowerCase();
                
                if (deviceName.includes(searchTerm) || deviceType.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        function previousStep() {
            window.location.href = 'step_by_step_handler.php?step=2';
        }
        
        function nextStep() {
            // Vérifier qu'au moins un dispositif a des mappings
            const hasBindings = Object.values(bindings).some(deviceBindings => deviceBindings.length > 0);
            
            if (!hasBindings) {
                if (confirm('Aucun mapping n\'a été configuré. Voulez-vous vraiment continuer ?')) {
                    window.location.href = 'step_by_step_handler.php?step=4';
                }
            } else {
                window.location.href = 'step_by_step_handler.php?step=4';
            }
        }
        
        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('bindingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
