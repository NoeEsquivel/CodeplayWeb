<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Aventura</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --equip-color: #FF9800;
            --adventure-color: #4CAF50;
            --magic-color: #9C27B0;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
            position: relative;
        }
        
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        
        .navbar-custom {
            background: rgba(0, 0, 0, 0.8) !important;
            border-bottom: 3px solid var(--accent-color);
        }
        
        .game-title {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000;
            color: var(--accent-color);
            font-size: 1.5rem;
            margin: 0 auto;
        }

        .player-name-display {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid white;
            border-radius: 50px;
            padding: 8px 15px;
            color: white;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .player-name-display:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .btn-home {
            background: transparent;
            border: 2px solid white;
            border-radius: 50px;
            transition: all 0.3s;
            color: white !important;
        }
        
        .btn-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        .adventure-option {
            height: 200px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        
        .adventure-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        
        .adventure-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1;
        }
        
        .adventure-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
        }
        
        .adventure-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .adventure-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        
        .section-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.8rem;
            color: var(--accent-color);
            margin-bottom: 2rem;
            text-shadow: 3px 3px 0 #000;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        
        .loading-content {
            text-align: center;
            color: var(--accent-color);
        }
        
        .spinner-custom {
            width: 5rem;
            height: 5rem;
            border-width: 6px;
        }
        
        /* Estilos para el modal de equipamiento */
        .equipment-modal .modal-content {
            background: rgba(0, 0, 0, 0.9);
            border: 3px solid var(--equip-color);
            border-radius: 15px;
            color: white;
        }
        
        .equipment-modal .modal-header {
            border-bottom: 2px solid var(--equip-color);
            background: rgba(255, 152, 0, 0.1);
        }
        
        .equipment-modal .modal-footer {
            border-top: 2px solid var(--equip-color);
            background: rgba(255, 152, 0, 0.1);
        }
        
        .character-display {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .character {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .equipment-section {
            margin-bottom: 1.5rem;
        }
        
        .equipment-title {
            color: var(--accent-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.9rem;
        }
        
        .equipment-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 0.8rem;
            margin-bottom: 0.8rem;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .equipment-item:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent-color);
        }
        
        .equipment-item.selected {
            background: rgba(0, 255, 157, 0.2);
            border-color: var(--accent-color);
        }
        
        .btn-confirm {
            background: var(--accent-color);
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-confirm:hover {
            background: #00e68a;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
        }
        
        .user-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            color: var(--accent-color);
            font-weight: bold;
        }

        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #6c757d;
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10000;
            max-width: 350px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
        }

        /* Estilos específicos para magia */
        .magic-section .equipment-title {
            color: var(--magic-color);
            border-bottom: 2px solid var(--magic-color);
        }

        .magic-item {
            border-left: 3px solid var(--magic-color);
        }
    </style>
</head>
<body>
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Elementos izquierda -->
            <div class="d-flex align-items-center">
                <a class="btn btn-home me-2" href="{{ route('alumno.desafios') }}">
                    <i class="fas fa-arrow-left text-white"></i>
                </a>
                <img src="https://img.icons8.com/nolan/48/code.png" alt="Logo" width="40" height="40">
            </div>
            
            <!-- Título Centrado -->
            <span class="game-title position-absolute start-50 translate-middle-x">CODEPLAY</span>
            
            <!-- Usuario derecha -->
            <div class="player-name-display">
                <i class="fas fa-user me-2"></i>
                <span>{{ session('usuario_nombre') }}</span>
                <span class="badge bg-success ms-2">Alumno</span>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-dragon me-3"></i>MODO AVENTURA
                </h1>
                
                <!-- Estadísticas del Jugador -->
                <div class="user-stats animate__animated animate__fadeIn">
                    <div class="row text-center">
                        <div class="col-md-3 stat-item">
                            <div>Nivel</div>
                            <div class="stat-value" id="playerLevel">1</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div>Puntos</div>
                            <div class="stat-value" id="playerPoints">{{ session('puntos_disponibles') ?? 0 }}</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div>Salud</div>
                            <div class="stat-value" id="playerHealth">100/100</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div>Magia</div>
                            <div class="stat-value" id="playerMagic">50/50</div>
                        </div>
                    </div>
                </div>
                
                <!-- Opciones de Aventura -->
                <div class="row">
                    <!-- Opción 1: Equiparme -->
                    <div class="col-md-6 animate__animated animate__fadeInLeft">
                        <div class="adventure-option" style="background: linear-gradient(135deg, var(--equip-color), #FFC107); border: 3px solid #FFC107;" 
                             data-bs-toggle="modal" data-bs-target="#equipmentModal">
                            <div class="adventure-content">
                                <div class="adventure-icon">
                                    <i class="fas fa-tshirt"></i>
                                </div>
                                <h2 class="adventure-title">EQUIPARME</h2>
                                <p>Selecciona tu equipamiento y hechizos</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Opción 2: Iniciar Aventura -->
                    <div class="col-md-6 animate__animated animate__fadeInRight">
                        <div class="adventure-option" style="background: linear-gradient(135deg, var(--adventure-color), #81C784); border: 3px solid #81C784;" id="startAdventureBtn">
                            <div class="adventure-content">
                                <div class="adventure-icon">
                                    <i class="fas fa-play"></i>
                                </div>
                                <h2 class="adventure-title">INICIAR AVENTURA</h2>
                                <p>Comienza tu aventura épica</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Equipamiento -->
    <div class="modal fade equipment-modal" id="equipmentModal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="equipmentModalLabel">
                        <i class="fas fa-tshirt me-2"></i>EQUIPAMIENTO DEL AVENTURERO
                    </h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="character-display">
                        <div class="character" id="characterDisplay">🧙‍♂️</div>
                        <h4 id="characterName">{{ session('usuario_nombre') }}</h4>
                    </div>
                    
                    <div class="row">
                        <!-- Columna 1: Armadura -->
                        <div class="col-md-3 equipment-section">
                            <h4 class="equipment-title"><i class="fas fa-shield-alt me-2"></i>ARMADURA</h4>
                            <div class="equipment-item selected" data-type="armor" data-value="basic">
                                <h5>Armadura Básica</h5>
                                <p class="mb-0">+5 defensa</p>
                            </div>
                            <div class="equipment-item" data-type="armor" data-value="iron">
                                <h5>Armadura de Hierro</h5>
                                <p class="mb-0">+10 defensa (Requiere nivel 3)</p>
                            </div>
                            <div class="equipment-item" data-type="armor" data-value="magic">
                                <h5>Túnica Mágica</h5>
                                <p class="mb-0">+8 defensa, +5 magia (Requiere nivel 5)</p>
                            </div>
                        </div>
                        
                        <!-- Columna 2: Arma -->
                        <div class="col-md-3 equipment-section">
                            <h4 class="equipment-title"><i class="fas fa-sword me-2"></i>ARMA</h4>
                            <div class="equipment-item selected" data-type="weapon" data-value="sword">
                                <h5>Espada Básica</h5>
                                <p class="mb-0">+8 ataque</p>
                            </div>
                            <div class="equipment-item" data-type="weapon" data-value="axe">
                                <h5>Hacha de Guerra</h5>
                                <p class="mb-0">+12 ataque (Requiere nivel 4)</p>
                            </div>
                            <div class="equipment-item" data-type="weapon" data-value="staff">
                                <h5>Báculo Mágico</h5>
                                <p class="mb-0">+6 ataque, +10 magia (Requiere nivel 6)</p>
                            </div>
                        </div>
                        
                        <!-- Columna 3: Pociones -->
                        <div class="col-md-3 equipment-section">
                            <h4 class="equipment-title"><i class="fas fa-flask me-2"></i>POCIONES</h4>
                            <div class="equipment-item selected" data-type="potion" data-value="health">
                                <h5>Poción de Salud</h5>
                                <p class="mb-0">Restaura 30 HP</p>
                            </div>
                            <div class="equipment-item" data-type="potion" data-value="energy">
                                <h5>Poción de Energía</h5>
                                <p class="mb-0">Restaura 20 MP</p>
                            </div>
                            <div class="equipment-item" data-type="potion" data-value="double">
                                <h5>Poción Doble</h5>
                                <p class="mb-0">Restaura 15 HP y 15 MP (Requiere nivel 3)</p>
                            </div>
                        </div>

                        <!-- Columna 4: Magia (NUEVA) -->
                        <div class="col-md-3 equipment-section magic-section">
                            <h4 class="equipment-title"><i class="fas fa-magic me-2"></i>HECHIZOS</h4>
                            <div class="equipment-item magic-item selected" data-type="spell" data-value="fireball">
                                <h5>Bola de Fuego</h5>
                                <p class="mb-0">15 daño mágico</p>
                                <small class="text-warning">Costo: 10 MP</small>
                            </div>
                            <div class="equipment-item magic-item" data-type="spell" data-value="heal">
                                <h5>Curación</h5>
                                <p class="mb-0">Restaura 25 HP</p>
                                <small class="text-warning">Costo: 15 MP</small>
                            </div>
                            <div class="equipment-item magic-item" data-type="spell" data-value="shield">
                                <h5>Escudo Mágico</h5>
                                <p class="mb-0">+10 defensa por 3 turnos</p>
                                <small class="text-warning">Costo: 12 MP</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-confirm" id="confirmEquipment">
                        <i class="fas fa-check-circle me-2"></i>Confirmar Equipamiento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay de carga -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border spinner-custom text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <h3 class="mt-3">Cargando contenido...</h3>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para mostrar notificación
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} animate__animated animate__fadeInRight`;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>${message}`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate__fadeOutRight');
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        // Función para mostrar mensaje de desarrollo
        function mostrarMensajeDesarrollo(modulo) {
            const mensajes = {
                'aventura': {
                    titulo: '🐉 Aventura Épica',
                    mensaje: 'Próximamente: Embárcate en una aventura resolviendo problemas de código',
                    color: '#9C27B0'
                }
            };

            const info = mensajes[modulo] || {
                titulo: '🔧 Módulo en Desarrollo',
                mensaje: 'Esta funcionalidad estará disponible pronto',
                color: '#6c757d'
            };

            mostrarNotificacionPersonalizada(info.titulo, info.mensaje, info.color);
        }

        // Función para mostrar notificación personalizada
        function mostrarNotificacionPersonalizada(titulo, mensaje, color) {
            const notificacion = document.createElement('div');
            notificacion.className = 'custom-notification';
            notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${color};
                color: white;
                padding: 20px 25px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                z-index: 10000;
                max-width: 350px;
                transform: translateX(400px);
                opacity: 0;
                transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255,255,255,0.2);
            `;

            notificacion.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <div style="font-size: 1.8rem;">✨</div>
                    <div style="flex: 1;">
                        <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 5px;">${titulo}</div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">${mensaje}</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; padding: 0; margin: 0;">
                        ✕
                    </button>
                </div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: rgba(255,255,255,0.3); border-radius: 0 0 15px 15px;">
                    <div class="progress-bar" style="height: 100%; background: rgba(255,255,255,0.8); width: 100%; border-radius: 0 0 15px 15px; animation: progress 4s linear forwards;"></div>
                </div>
            `;

            document.body.appendChild(notificacion);

            // Animación de entrada
            setTimeout(() => {
                notificacion.style.transform = 'translateX(0)';
                notificacion.style.opacity = '1';
            }, 100);

            // Auto-eliminar después de 4 segundos
            setTimeout(() => {
                if (notificacion.parentElement) {
                    notificacion.style.transform = 'translateX(400px)';
                    notificacion.style.opacity = '0';
                    setTimeout(() => {
                        if (notificacion.parentElement) {
                            notificacion.remove();
                        }
                    }, 500);
                }
            }, 4000);
        }

        // Función para actualizar la visualización del personaje
        function updateCharacterDisplay(equipment) {
            const characterDisplay = document.getElementById('characterDisplay');
            let characterEmoji = '🧙‍♂️';
            
            if (equipment.weapon === 'staff') {
                characterEmoji = '🧙‍♂️';
            } else if (equipment.weapon === 'axe') {
                characterEmoji = '🪓';
            } else {
                characterEmoji = '⚔️';
            }
            
            if (equipment.armor === 'iron') {
                characterDisplay.style.color = '#aaa';
            } else if (equipment.armor === 'magic') {
                characterDisplay.style.color = '#9370DB';
            } else {
                characterDisplay.style.color = '#fff';
            }
            
            characterDisplay.textContent = characterEmoji;
        }

        // Función para cargar el progreso del usuario
        function loadUserProgress() {
            const userPoints = {{ session('puntos_disponibles') ?? 0 }};
            const userLevel = Math.floor(userPoints / 100) + 1;
            const equipment = JSON.parse(localStorage.getItem('player_equipment')) || {
                armor: 'basic',
                weapon: 'sword',
                potion: 'health',
                spell: 'fireball'
            };

            // Actualizar la UI
            document.getElementById('playerPoints').textContent = userPoints;
            document.getElementById('playerLevel').textContent = userLevel;

            // Configurar items de equipamiento
            document.querySelectorAll('.equipment-item').forEach(item => {
                const type = item.dataset.type;
                const value = item.dataset.value;
                const requiredLevel = parseInt(item.textContent.match(/Requiere nivel (\d+)/)?.[1]) || 0;

                // Seleccionar el item guardado
                if (equipment[type] === value) {
                    item.classList.add('selected');
                }

                // Deshabilitar items que requieren más nivel
                if (requiredLevel > userLevel) {
                    item.style.opacity = '0.5';
                    item.style.cursor = 'not-allowed';
                }
            });

            updateCharacterDisplay(equipment);
        }

        // Inicializar la aplicación cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar progreso inicial
            loadUserProgress();

            // Configurar botón de iniciar aventura
            document.getElementById('startAdventureBtn').addEventListener('click', function() {
                mostrarMensajeDesarrollo('aventura');
            });

            // Configurar selección de items de equipamiento en el modal
            document.querySelectorAll('.equipment-item').forEach(item => {
                item.addEventListener('click', function() {
                    const userLevel = parseInt(document.getElementById('playerLevel').textContent);
                    const requiredLevel = parseInt(this.textContent.match(/Requiere nivel (\d+)/)?.[1]) || 0;
                    
                    // Verificar si el item está disponible
                    if (requiredLevel > userLevel) {
                        showNotification('¡No tienes el nivel requerido para este item!', 'danger');
                        return;
                    }
                    
                    // Deseleccionar otros items del mismo tipo
                    const type = this.dataset.type;
                    document.querySelectorAll(`.equipment-item[data-type="${type}"]`).forEach(i => {
                        i.classList.remove('selected');
                    });
                    
                    // Seleccionar este item
                    this.classList.add('selected');
                });
            });

            // Configurar botón de confirmar equipamiento
            document.getElementById('confirmEquipment').addEventListener('click', function() {
                try {
                    const equipment = {
                        armor: document.querySelector('.equipment-item[data-type="armor"].selected')?.dataset.value || 'basic',
                        weapon: document.querySelector('.equipment-item[data-type="weapon"].selected')?.dataset.value || 'sword',
                        potion: document.querySelector('.equipment-item[data-type="potion"].selected')?.dataset.value || 'health',
                        spell: document.querySelector('.equipment-item[data-type="spell"].selected')?.dataset.value || 'fireball'
                    };
                    
                    localStorage.setItem('player_equipment', JSON.stringify(equipment));
                    updateCharacterDisplay(equipment);
                    
                    // Cerrar el modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('equipmentModal'));
                    modal.hide();
                    
                    showNotification('¡Equipamiento actualizado con éxito!');
                } catch (error) {
                    showNotification('Error al guardar el equipamiento', 'danger');
                    console.error(error);
                }
            });
        });
    </script>
</body>
</html>