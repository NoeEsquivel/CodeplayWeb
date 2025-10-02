<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Menú Principal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            overflow: hidden;
            background: url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
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
        
        .game-header {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;
            color: var(--accent-color);
            margin-top: 2rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .menu-container {
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .menu-option {
            background: rgba(0, 0, 0, 0.7);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            transition: all 0.3s;
            margin-bottom: 1.5rem;
            cursor: pointer;
            backdrop-filter: blur(5px);
            padding: 20px;
        }
        
        .menu-option:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.5);
            background: rgba(0, 0, 0, 0.9);
            border-color: #00ff9d;
        }
        
        .menu-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .player-name-display {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 20px;
            border: 2px solid var(--accent-color);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }
        
        .points-display {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 20px;
            border: 2px solid var(--accent-color);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(220, 53, 69, 0.8);
            border: 2px solid #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 1);
            transform: scale(1.05);
        }
        
        .menu-title {
            color: var(--accent-color);
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.3rem;
        }
        
        .menu-description {
            color: #ccc;
            font-size: 1rem;
            margin-bottom: 0;
            line-height: 1.4;
        }

        /* Nuevos estilos para mejor distribución */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .menu-item {
            background: rgba(0, 0, 0, 0.7);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            transition: all 0.3s;
            cursor: pointer;
            backdrop-filter: blur(5px);
            padding: 25px;
            display: flex;
            align-items: center;
            min-height: 140px;
        }

        .menu-item:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.5);
            background: rgba(0, 0, 0, 0.9);
            border-color: #00ff9d;
        }

        .menu-content {
            flex: 1;
        }

        /* Estilos para las notificaciones personalizadas */
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }

        @keyframes vibrate {
            0% { transform: translateX(400px) rotate(0deg); }
            25% { transform: translateX(-5px) rotate(1deg); }
            50% { transform: translateX(5px) rotate(-1deg); }
            75% { transform: translateX(-2px) rotate(0deg); }
            100% { transform: translateX(0) rotate(0deg); }
        }

        .custom-notification {
            font-family: 'Roboto', sans-serif;
            animation: vibrate 0.5s ease-out;
        }

        /* Ajustes responsive */
        @media (max-width: 992px) {
            .menu-grid {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
            
            .menu-item {
                min-height: 120px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .menu-item {
                min-height: 110px;
                padding: 15px;
            }
            
            .menu-icon {
                font-size: 2rem;
                margin-right: 15px;
            }
            
            .menu-title {
                font-size: 1.1rem;
            }
            
            .menu-description {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Puntos del jugador -->
    <div class="points-display">
        <i class="fas fa-star me-2 text-warning"></i>
        <span id="pointsDisplay">{{ Auth::user()->puntos_totales ?? 0 }} puntos</span>
    </div>

    <!-- Nombre del jugador -->
    <div class="player-name-display">
        <i class="fas fa-user me-2"></i>
        <span id="playerNameDisplay">{{ session('usuario_nombre') }}</span>
        <span class="badge bg-success ms-2">Alumno</span>
    </div>

    <div class="container">
        <!-- Título CODEPLAY arriba -->
        <div class="row text-center">
            <div class="col-12">
                <h1 class="game-header display-4">CODEPLAY</h1>
                <p class="lead">Aprende programación jugando</p>
            </div>
        </div>

        <br><br><br><br>

        <!-- Menú central mejorado con Grid -->
        <div class="row justify-content-center menu-container">
            <div class="col-12">
                <div class="menu-grid">
                    <!-- Fila 1 -->
                    <div class="menu-item" onclick="navegarA('documentacion')">
                        <i class="menu-icon fas fa-book"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">📚 Documentación</h4>
                            <p class="menu-description">Aprende los conceptos básicos de programación</p>
                        </div>
                    </div>
                    
                    <div class="menu-item" onclick="navegarA('tutoriales')">
                        <i class="menu-icon fas fa-graduation-cap"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">🎓 Tutoriales Interactivos</h4>
                            <p class="menu-description">Sigue guías paso a paso con ejemplos prácticos</p>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="menu-item" onclick="navegarA('desafios')">
                        <i class="menu-icon fas fa-gamepad"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">⚔️ Desafíos de Programación</h4>
                            <p class="menu-description">Completa niveles y gana puntos</p>
                        </div>
                    </div>

                    <div class="menu-item" onclick="navegarA('tests')">
                        <i class="menu-icon fas fa-clipboard-check"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">📝 Tests y Evaluaciones</h4>
                            <p class="menu-description">Pon a prueba tus conocimientos</p>
                        </div>
                    </div>

                    <!-- Fila 3 - Centrado -->
                    <div class="menu-item" onclick="navegarA('progreso')" style="grid-column: 1 / -1; max-width: 500px; margin: 0 auto;">
                        <i class="menu-icon fas fa-chart-line"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">📊 Mi Progreso</h4>
                            <p class="menu-description">Revisa tus estadísticas y logros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de cerrar sesión -->
    <form action="{{ route('logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="logout-btn">
            <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
        </button>
    </form>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function navegarA(destino) {
            // Agregar efecto de click
            event.currentTarget.style.transform = 'scale(0.95)';
            setTimeout(() => {
                event.currentTarget.style.transform = 'scale(1.05)';
            }, 150);
            
            // Mostrar notificación personalizada según el destino
            const mensajes = {
                'documentacion': {
                    titulo: '🚀 Documentación',
                    mensaje: 'Próximamente: Aprende conceptos básicos de programación',
                    color: '#6a11cb'
                },
                'tutoriales': {
                    titulo: '🎯 Tutoriales Interactivos',
                    mensaje: 'Próximamente: Guías paso a paso con ejemplos prácticos',
                    color: '#2575fc'
                },
                'desafios': {
                    titulo: '⚔️ Desafíos de Programación',
                    mensaje: 'Próximamente: Completa niveles y gana puntos',
                    color: '#00ff9d'
                },
                'tests': {
                    titulo: '📝 Tests y Evaluaciones',
                    mensaje: 'Próximamente: Pon a prueba tus conocimientos',
                    color: '#ff6b6b'
                },
                'progreso': {
                    titulo: '📊 Mi Progreso',
                    mensaje: 'Próximamente: Revisa tus estadísticas y logros',
                    color: '#ffd93d'
                }
            };

            const info = mensajes[destino] || {
                titulo: '🔧 Módulo en Desarrollo',
                mensaje: 'Esta funcionalidad estará disponible pronto',
                color: '#6c757d'
            };

            mostrarNotificacionPersonalizada(info.titulo, info.mensaje, info.color);
        }

        function mostrarNotificacionPersonalizada(titulo, mensaje, color) {
            // Crear elemento de notificación
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

        // Agregar event listeners para efectos
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });

        // Mostrar notificación de bienvenida
        setTimeout(() => {
            const welcomeMessages = [
                "¡Bienvenido a CODEPLAY! 🎮",
                "¡Prepárate para aprender programación! 💻",
                "¡Completa desafíos y gana puntos! ⭐",
                "¡Tu aventura en programación comienza ahora! 🚀",
                "¡Conviértete en un maestro del código! 👨‍💻",
                "¡Aprende, practica y domina! 💪"
            ];
            const randomMessage = welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
            
            // Crear notificación bonita
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20%;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 255, 157, 0.9);
                color: #000;
                padding: 15px 30px;
                border-radius: 25px;
                font-weight: bold;
                z-index: 10000;
                animation: slideDown 0.5s ease-out;
            `;
            notification.textContent = randomMessage;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }, 1000);
    </script>

    <style>
        @keyframes slideDown {
            from {
                top: -100px;
                opacity: 0;
            }
            to {
                top: 20%;
                opacity: 1;
            }
        }
    </style>
</body>
</html>