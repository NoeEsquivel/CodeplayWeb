<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Aprender Jugando</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --challenge-color: #FF5722;
            --adventure-color: #9C27B0;
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

        .game-option {
            height: 300px;
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
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .game-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }

        .game-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1;
        }

        .game-option-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
        }

        .game-option-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .game-option-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .game-option-description {
            font-size: 1rem;
            opacity: 0.9;
        }

        .challenge-option {
            background: linear-gradient(135deg, var(--challenge-color), #FF9800);
            border: 3px solid #FF9800;
        }

        .adventure-option {
            background: linear-gradient(135deg, var(--adventure-color), #E91E63);
            border: 3px solid #E91E63;
        }

        .section-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.8rem;
            color: var(--accent-color);
            margin-bottom: 3rem;
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

        .user-progress {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            border: 2px solid var(--accent-color);
        }

        .progress-text {
            color: var(--accent-color);
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .game-option {
                height: 250px;
            }

            .game-option-title {
                font-size: 1rem;
            }

            .game-option-icon {
                font-size: 3rem;
            }

            .section-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
@include('partials.alumno-sidebar')
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Elementos izquierda -->
            <div class="d-flex align-items-center">
                <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                    <i class="fas fa-arrow-left text-white"></i>
                </a>
                <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
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
            <div class="col-lg-10 text-center">
                <h1 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-gamepad me-3"></i>APRENDER JUGANDO
                </h1>

                <!-- Progreso del Usuario -->
                <div class="user-progress animate__animated animate__fadeIn animate__delay-1s">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-start">
                            <h5 class="progress-text">
                                <i class="fas fa-trophy me-2"></i>
                                Nivel: <span id="userLevel">1</span> |
                                Puntos: <span id="userPoints">{{ session('puntos_disponibles') ?? 0 }}</span>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="progress-text">
                                <i class="fas fa-star me-2"></i>
                                Desafíos completados:
                                <span id="completedChallenges">{{ $desafiosCompletados ?? 0 }}</span>/20
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- Opciones de Juego -->
                <div class="row mt-4">
                    <!-- Opción 1: Desafíos de Programación -->
                    <div class="col-md-6 mb-4 animate__animated animate__fadeInLeft animate__delay-2s">
                        <div class="game-option challenge-option" role="link" tabindex="0" onclick="navegarA('desafios-programacion')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('desafios-programacion')}">
                            <div class="game-option-content">
                                <div class="game-option-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h2 class="game-option-title">INICIAR DESAFÍOS DE PROGRAMACIÓN</h2>
                                <p class="game-option-description">
                                    Completa 20 niveles de retos de código y mejora tus habilidades de programación.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Opción 2: Aventura -->
                    <div class="col-md-6 mb-4 animate__animated animate__fadeInRight animate__delay-2s">
                        <div class="game-option adventure-option" role="link" tabindex="0" onclick="navegarA('aventura')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('aventura')}">
                            <div class="game-option-content">
                                <div class="game-option-icon">
                                    <i class="fas fa-dragon"></i>
                                </div>
                                <h2 class="game-option-title">INICIAR UNA AVENTURA</h2>
                                <p class="game-option-description">
                                    Embárcate en una aventura épica donde resolver problemas de código te ayudará a avanzar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción de las Opciones -->
                <div class="row mt-5 animate__animated animate__fadeIn animate__delay-3s">
                    <div class="col-md-6">
                        <div class="p-4 bg-dark rounded border border-warning">
                            <h4 class="text-warning"><i class="fas fa-laptop-code me-2"></i>Desafíos de Programación</h4>
                            <p>
                                Enfréntate a retos de código progresivos donde pondrás a prueba tus conocimientos.
                                Cada nivel completado te dará puntos para mejorar tu personaje.
                            </p>
                            <p class="text-muted">
                                <small><i class="fas fa-lock-open me-1"></i>Niveles disponibles: <span id="availableChallenges">1</span>/20</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 bg-dark rounded border border-info">
                            <h4 class="text-info"><i class="fas fa-dragon me-2"></i>Aventura</h4>
                            <p>
                                Explora un mundo lleno de desafíos donde tu personaje avanzará resolviendo problemas de programación.
                                Derrota enemigos y encuentra tesoros con tu código.
                            </p>
                            <p class="text-muted">
                                <small><i class="fas fa-map-marked-alt me-1"></i>Zonas disponibles: <span id="availableZones">1</span>/5</small>
                            </p>
                        </div>
                    </div>
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
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script>
            // Función para navegar con animación
            function navigateWithAnimation(url) {
                console.log('navigateWithAnimation llamada con URL:', url);

                const overlay = document.getElementById('loadingOverlay');

                if (!overlay) {
                    console.error('No se encontró el overlay de carga');
                    window.location.href = url;
                    return;
                }

                // Mostrar animación
                overlay.style.opacity = '1';
                overlay.style.pointerEvents = 'auto';

                console.log('Redirigiendo en 1.5 segundos a:', url);

                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    console.log('Redirigiendo ahora a:', url);
                    window.location.href = url;
                }, 1500);
            }

            // Función para navegar a diferentes secciones
            function navegarA(destino) {
                console.log('Destino:', destino);

                const rutas = {
                    'desafios-programacion': "{{ route('alumno.desafios.programacion') }}",
                    'aventura': "{{ route('alumno.aventura') }}"
                };

                console.log('Rutas disponibles:', rutas);
                console.log('Ruta para destino:', rutas[destino]);

                if (rutas[destino] && rutas[destino] !== '#') {
                    console.log('Navegando a:', rutas[destino]);
                    navigateWithAnimation(rutas[destino]);
                } else {
                    console.log('Mostrando mensaje de desarrollo para:', destino);
                    mostrarMensajeDesarrollo(destino);
                }
            }

        // Función para mostrar mensaje de desarrollo
        function mostrarMensajeDesarrollo(modulo) {
            const mensajes = {
                'desafios-programacion': {
                    titulo: '⚔️ Desafíos de Programación',
                    mensaje: 'Próximamente: Completa niveles de código y gana puntos',
                    color: '#FF5722'
                },
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

        // Función para mostrar notificaciones personalizadas (la misma que usas en inicio-alumno)
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

        // Cargar progreso del usuario
        function cargarProgresoUsuario() {
            const puntos = {{ session('puntos_disponibles') ?? 0 }};
            document.getElementById('userPoints').textContent = puntos;

            // Calcular nivel basado en puntos (100 puntos por nivel)
            const nivel = Math.floor(puntos / 100) + 1;
            document.getElementById('userLevel').textContent = nivel;

            // Por ahora, simular datos (luego vendrán de la base de datos)
            document.getElementById('completedChallenges').textContent = {{ $desafiosCompletados ?? 0 }};
            document.getElementById('availableChallenges').textContent = Math.min(nivel, 20);
            document.getElementById('availableZones').textContent = Math.min(Math.floor(nivel / 5) + 1, 5);
        }

        // Cargar progreso cuando la página se carga
        document.addEventListener('DOMContentLoaded', function() {
            cargarProgresoUsuario();
        });
    </script>
</body>
</html>
