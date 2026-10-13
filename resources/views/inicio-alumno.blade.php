<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Menú Principal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
        }

        body {
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
            padding-bottom: 90px;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .adventure-trophy {
            box-sizing: border-box;
            width: min(440px, calc(100vw - 40px));
            margin: 12px auto 14px;
            padding: 12px 18px;
            border: 2px solid #e4c98f;
            border-radius: 16px;
            background: linear-gradient(120deg, #172d30, #352c1e);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .36);
            color: #fff;
            text-align: center;
            transition: top .55s ease, left .55s ease, right .55s ease, width .55s ease, padding .55s ease, border-radius .55s ease, transform .55s ease, box-shadow .3s ease;
        }

        .adventure-trophy.is-floating {
            position: fixed;
            z-index: 1001;
            margin: 0;
            transform: translateX(0);
        }

        .trophy-toggle { display: block; width: 100%; padding: 0; border: 0; background: transparent; color: inherit; cursor: pointer; }

        .trophy-icon {
            display: block;
            font-size: 32px;
            line-height: 1;
            transition: font-size .45s ease, transform .45s ease;
        }

        .trophy-message {
            display: grid;
            gap: 8px;
            max-height: 150px;
            margin-top: 6px;
            opacity: 1;
            overflow: hidden;
            transition: max-height .45s ease, margin .45s ease, opacity .24s ease;
        }

        .trophy-message h2 { margin: 0; font-size: 19px; color: #ffe1a0; }
        .trophy-message p { margin: 0; }
        .trophy-message a { color: #a6efcf; }

        .adventure-trophy.is-floating.is-collapsed {
            top: 14px;
            right: 16px;
            left: auto;
            width: 54px;
            padding: 7px;
            border-radius: 50%;
            box-shadow: 0 7px 18px rgba(0, 0, 0, .45);
            cursor: pointer;
            transform: translateX(0);
        }

        .adventure-trophy.is-floating.is-collapsed:hover { transform: translateY(-2px) scale(1.06); }
        .adventure-trophy.is-collapsed .trophy-icon { font-size: 32px; }
        .adventure-trophy.is-collapsed .trophy-message { max-height: 0; margin-top: 0; opacity: 0; }

        @media (max-width: 768px) {
            .adventure-trophy.is-floating.is-collapsed { top: 12px; right: 12px; left: auto; }
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
            min-height: 60vh;
            padding-top: 2rem;
            padding-bottom: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .menu-gap { height: 24px; }

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

        .points-animation {
            animation: pointsPop 0.6s ease-out;
        }

        @keyframes pointsPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
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
@include('partials.alumno-sidebar')
    <div class="container">
        <!-- Título CODEPLAY arriba -->
        <div class="row text-center">
            <div class="col-12">
                <h1 class="game-header display-4">CODEPLAY</h1>
                <p class="lead">Aprende programación jugando</p>
            </div>
        </div>

        @if(!empty($trofeoAventura))
        <aside id="adventureTrophy" class="adventure-trophy" aria-live="polite">
            <button type="button" class="trophy-toggle" aria-label="Mostrar el logro Conquistador de la Aventura">
                <span class="trophy-icon" aria-hidden="true">🏆</span>
            </button>
            <div class="trophy-message">
                <h2>Conquistador de la Aventura</h2>
                <p>Derrotaste al Dragón y completaste Aprender Jugando.</p>
                <a href="{{ route('alumno.aventura.final',['expedicion'=>$trofeoAventura->expedicion_id]) }}">Recordar el final de mi aventura →</a>
            </div>
        </aside>
        @endif

        <div class="menu-gap" aria-hidden="true"></div>

        <!-- Menú central mejorado con Grid -->
        <div class="row justify-content-center menu-container">
            <div class="col-12">
                <div class="menu-grid">
                    <!-- Fila 1 -->
                    <div class="menu-item" role="link" tabindex="0" onclick="navegarA('documentacion', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('documentacion',this)}">
                        <i class="menu-icon fas fa-book"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">Documentación</h4>
                            <p class="menu-description">Aprende los conceptos básicos de programación</p>
                        </div>
                    </div>

                    <div class="menu-item" role="link" tabindex="0" onclick="navegarA('tutoriales', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('tutoriales',this)}">
                        <i class="menu-icon fas fa-graduation-cap"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">Tutoriales Interactivos</h4>
                            <p class="menu-description">Sigue guías paso a paso con ejemplos prácticos</p>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="menu-item" role="link" tabindex="0" onclick="navegarA('desafios', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('desafios',this)}">
                        <i class="menu-icon fas fa-gamepad"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">Desafíos de Programación y Aprender Jugando</h4>
                            <p class="menu-description">Completa niveles y gana puntos</p>
                        </div>
                    </div>

                    <div class="menu-item" role="link" tabindex="0" onclick="navegarA('tests', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('tests',this)}">
                        <i class="menu-icon fas fa-clipboard-check"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">Tests y Evaluaciones</h4>
                            <p class="menu-description">Pon a prueba tus conocimientos</p>
                        </div>
                    </div>

                    <!-- Fila 3 - Centrado -->
                    <div class="menu-item" role="link" tabindex="0" onclick="navegarA('progreso', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navegarA('progreso',this)}" style="grid-column: 1 / -1; max-width: 500px; margin: 0 auto;">
                        <i class="menu-icon fas fa-chart-line"></i>
                        <div class="menu-content">
                            <h4 class="menu-title">Mi Progreso</h4>
                            <p class="menu-description">Revisa tus estadísticas y logros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script>
        // Variable para almacenar los puntos actuales
        let puntosActuales = {{ session('puntos_disponibles') ?? 0 }};

        // Función para actualizar los puntos con animación
        function actualizarPuntos(nuevosPuntos) {
            const pointsElement = document.getElementById('studentNavPointsValue');
            const pointsDisplay = pointsElement?.closest('.student-nav-points');

            if (pointsElement && nuevosPuntos !== puntosActuales) {
                // Animación de cambio
                pointsDisplay?.classList.add('points-animation');

                // Actualizar el valor
                pointsElement.textContent = nuevosPuntos;
                puntosActuales = nuevosPuntos;

                // Remover la clase de animación después de que termine
                setTimeout(() => {
                    pointsDisplay?.classList.remove('points-animation');
                }, 600);
            }
        }

        // Función para verificar actualizaciones de puntos
        function verificarActualizacionPuntos() {
            fetch('{{ route("usuario.puntos") }}', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Datos de puntos recibidos:', data); // Para debug

                if (data.success && data.puntos !== puntosActuales) {
                    const puntosAnteriores = puntosActuales;
                    actualizarPuntos(data.puntos);

                    // Mostrar notificación si ganó puntos
                    if (data.puntos > puntosAnteriores) {
                        const puntosGanados = data.puntos - puntosAnteriores;
                        mostrarNotificacionPuntos(puntosGanados);
                    }
                }
            })
            .catch(error => console.error('Error al verificar puntos:', error));
        }

        // Función para mostrar notificación de puntos ganados
        function mostrarNotificacionPuntos(puntos) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                left: 20px;
                background: linear-gradient(45deg, #FFD700, #FFA500);
                color: #000;
                padding: 10px 15px;
                border-radius: 10px;
                font-weight: bold;
                z-index: 10000;
                animation: slideInLeft 0.5s ease-out;
                box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
                border: 2px solid rgba(255, 255, 255, 0.3);
            `;

            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-star" style="color: #000;"></i>
                    <span>+${puntos} puntos!</span>
                    <i class="fas fa-arrow-up" style="color: #28a745;"></i>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOutLeft 0.5s ease-in forwards';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 500);
            }, 3000);
        }

        // Verificar puntos cada 30 segundos
        setInterval(verificarActualizacionPuntos, 30000);

        // Verificar puntos inmediatamente al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            verificarActualizacionPuntos();

            const trophy = document.getElementById('adventureTrophy');
            if (trophy) {
                const trophyToggle = trophy.querySelector('.trophy-toggle');
                let trophyTimer;
                const collapseTrophy = () => {
                    const position = trophy.getBoundingClientRect();
                    trophy.style.top = `${position.top}px`;
                    trophy.style.right = `${window.innerWidth - position.right}px`;
                    trophy.style.width = `${position.width}px`;
                    trophy.classList.add('is-floating');
                    window.requestAnimationFrame(() => {
                        trophy.classList.add('is-collapsed');
                        trophy.style.removeProperty('top');
                        trophy.style.removeProperty('right');
                        trophy.style.removeProperty('width');
                    });
                };
                const showTrophy = () => {
                    trophy.classList.remove('is-collapsed');
                    trophy.classList.remove('is-floating');
                    trophy.style.removeProperty('top');
                    trophy.style.removeProperty('left');
                    trophy.style.removeProperty('right');
                    trophy.style.removeProperty('width');
                    window.clearTimeout(trophyTimer);
                    trophyTimer = window.setTimeout(collapseTrophy, 5000);
                };

                trophyToggle?.addEventListener('click', () => {
                    if (trophy.classList.contains('is-collapsed')) showTrophy();
                });

                showTrophy();
            }

            // Mostrar puntos actuales en consola para debug
            console.log('Puntos disponibles actuales:', puntosActuales);
            console.log('Usuario:', '{{ session('usuario_nombre') ?? "No autenticado" }}');
        });

        function navegarA(destino, trigger) {
            // Agregar efecto de click
            trigger.style.transform = 'scale(0.95)';
            setTimeout(() => {
                trigger.style.transform = 'scale(1.05)';
            }, 150);

            // Definir las rutas para cada destino (SOLO documentación existe)
            const rutas = {
                'documentacion': "{{ route('alumno.documentacion') }}",
                'tutoriales': "{{ route('alumno.tutoriales') }}",
                'desafios': "{{ route('alumno.desafios') }}",
                'tests': "{{ route('alumno.tests') }}",
                'progreso': "{{ route('alumno.progreso') }}"
            };

            // Si es una ruta existente, redirigir directamente
            if (rutas[destino]) {
                setTimeout(() => {
                    window.location.href = rutas[destino];
                }, 300);
                return;
            }

            // Para otros destinos, mostrar notificación de desarrollo
            const mensajes = {

                'tests': {
                    titulo: '📝 Tests y Evaluaciones',
                    mensaje: 'Próximamente: Pon a prueba tus conocimientos',
                    color: '#ff6b6b'
                },
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
            notification.className = 'student-motivation';
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

        @keyframes slideInLeft {
            from {
                transform: translateX(-100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutLeft {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(-100px);
                opacity: 0;
            }
        }
    </style>
</body>
</html>
