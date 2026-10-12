<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Introducción a HTML</title>
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
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
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

        .game-title {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000;
            color: var(--accent-color);
            font-size: 1.5rem;
            margin: 0 auto;
        }
        
        .btn-home, .btn-user {
            background: transparent;
            border: 2px solid white;
            border-radius: 50px;
            transition: all 0.3s;
            color: white !important;
        }
        
        .btn-home:hover, .btn-user:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        /* Animación de libro abriéndose */
        .book-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #111;
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .book {
            width: 200px;
            height: 300px;
            position: relative;
            transform-style: preserve-3d;
            transform: perspective(1000px) rotateY(0deg);
            animation: openBook 2s forwards;
        }
        
        .book-cover {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #1a1a2e, #16213e);
            border: 5px solid var(--accent-color);
            backface-visibility: hidden;
            transform-origin: left;
            transform: rotateY(0deg);
            animation: openCover 2s forwards;
            animation-delay: 0.5s;
        }
        
        .book-content {
            position: absolute;
            width: 95%;
            height: 95%;
            background: white;
            left: 5%;
            color: black;
            padding: 20px;
            box-sizing: border-box;
            opacity: 0;
            animation: fadeInContent 1s forwards;
            animation-delay: 2s;
        }
        
        @keyframes openBook {
            0% { transform: perspective(1000px) rotateY(0deg); }
            100% { transform: perspective(1000px) rotateY(30deg); }
        }
        
        @keyframes openCover {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(-160deg); }
        }
        
        @keyframes fadeInContent {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        /* Estilos del contenido */
        .doc-container {
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            animation: fadeIn 1s;
        }
        
        .doc-image {
            border: 2px solid var(--accent-color);
            border-radius: 10px;
            margin: 1rem 0;
            box-shadow: 0 0 15px rgba(0, 255, 157, 0.3);
            transition: transform 0.3s;
        }
        
        .doc-image:hover {
            transform: scale(1.02);
        }
        
        .code-block {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            border-left: 4px solid var(--accent-color);
            overflow-x: auto;
        }
        
        .tag {
            color: #569cd6;
        }
        
        .attribute {
            color: #9cdcfe;
        }
        
        .value {
            color: #ce9178;
        }
        
        .btn-success {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            margin: 0 10px;
        }
        
        .btn-success:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }

        /* Estilos para el modal de test bloqueado */
        .test-blocked-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .test-blocked-content {
            background: rgba(0, 0, 0, 0.9);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            text-align: center;
            animation: bounceIn 0.8s;
        }

        .countdown-timer {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
        }

        .emoticon {
            font-size: 3rem;
            margin: 0.5rem;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
@include('partials.alumno-sidebar')
    <!-- Animación inicial de libro -->
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-cover">
                <div class="book-content">
                    <h3>CODEPLAY</h3>
                    <p>Introducción a HTML</p>
                </div>
            </div>
        </div>
        <p class="text-white mt-4 animate__animated animate__fadeIn animate__delay-1s">Cargando documento...</p>
    </div>

    <!-- Modal para test bloqueado -->
    <div class="test-blocked-modal" id="testBlockedModal">
        <div class="test-blocked-content">
            <div class="emoticon">😴 ⏰ 🚫</div>
            <h3 class="text-warning mb-3">¡Test No Disponible!</h3>
            <p class="lead">Solo puedes realizar este Test cada 24 horas, regresa mañana e intentalo de nuevo 😊🎮</p>
            
            <div class="countdown-timer" id="countdownTimer">
                --:--:--
            </div>
            
            <p class="text-muted">Tiempo restante para poder realizar el test nuevamente</p>
            
            <button class="btn btn-success mt-3" onclick="closeTestBlockedModal()">
                <i class="fas fa-check me-2"></i>Entendido
            </button>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <!-- Botón de Inicio -->
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            
            <!-- Título Centrado -->
            <span class="game-title">CODEPLAY</span>
            
            <!-- Nombre del jugador - ACTUALIZADO -->
            <div class="ms-auto">
                <div class="player-name-display">
                    <i class="fas fa-user me-2"></i>
                    <span id="playerNameDisplay">{{ session('usuario_nombre') ?? 'Usuario' }}</span>
                    <span class="badge bg-success ms-2">Alumno</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido del documento -->
    <div class="container doc-container animate__animated animate__fadeIn" id="docContent" style="display: none;">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1><i class="fas fa-file-code me-2"></i>Introducción a HTML</h1>
                <p class="lead">El lenguaje básico para crear páginas web</p>
            </div>
            
            <div class="col-md-6">
                <h2><i class="fas fa-question-circle me-2"></i>¿Qué es HTML?</h2>
                <p>HTML (HyperText Markup Language) es el lenguaje estándar para crear páginas web. No es un lenguaje de programación, sino un lenguaje de marcado que define la estructura de tu contenido.</p>
                
                <img src="{{ asset('images/local/web-design.jpg') }}"
                     alt="Estructura HTML" class="img-fluid doc-image">
                
                <h3 class="mt-4"><i class="fas fa-cubes me-2"></i>Elementos Básicos</h3>
                <p>HTML consiste en una serie de elementos que encierran diferentes partes del contenido para que aparezcan o actúen de cierta manera.</p>
                
                <h4><i class="fas fa-lightbulb me-2"></i>Partes de un elemento HTML:</h4>
                <ul>
                    <li><strong>Etiqueta de apertura:</strong> &lt;nombre&gt;</li>
                    <li><strong>Contenido:</strong> El texto o elementos internos</li>
                    <li><strong>Etiqueta de cierre:</strong> &lt;/nombre&gt;</li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h2><i class="fas fa-code me-2"></i>Estructura Básica</h2>
                <p>Todos los documentos HTML siguen una estructura básica:</p>
                
                <div class="code-block">
                    <span class="tag">&lt;!DOCTYPE html&gt;</span><br>
                    <span class="tag">&lt;html&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;head&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;title&gt;</span>Título de la página<span class="tag">&lt;/title&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/head&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;body&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;h1&gt;</span>Mi primer encabezado<span class="tag">&lt;/h1&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;p&gt;</span>Mi primer párrafo.<span class="tag">&lt;/p&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/body&gt;</span><br>
                    <span class="tag">&lt;/html&gt;</span>
                </div>
                
                <img src="{{ asset('images/local/developer.jpg') }}"
                     alt="Código HTML" class="img-fluid doc-image">
            </div>
            
            <div class="col-12 mt-4">
                <h2><i class="fas fa-tags me-2"></i>Etiquetas Comunes</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Etiqueta</th>
                                <th>Descripción</th>
                                <th>Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>&lt;h1&gt; a &lt;h6&gt;</code></td>
                                <td>Encabezados de diferentes niveles</td>
                                <td><code>&lt;h1&gt;Título&lt;/h1&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;p&gt;</code></td>
                                <td>Párrafos de texto</td>
                                <td><code>&lt;p&gt;Texto aquí&lt;/p&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;a&gt;</code></td>
                                <td>Enlaces (links)</td>
                                <td><code>&lt;a href="#"&gt;Enlace&lt;/a&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;img&gt;</code></td>
                                <td>Imágenes</td>
                                <td><code>&lt;img src="imagen.jpg"&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;div&gt;</code></td>
                                <td>Contenedor genérico</td>
                                <td><code>&lt;div&gt;Contenido&lt;/div&gt;</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Práctica Rápida</h2>
                <p>Intenta crear tu primer documento HTML:</p>
                <div class="code-block">
                    <span class="tag">&lt;!DOCTYPE html&gt;</span><br>
                    <span class="tag">&lt;html&gt;</span><br>
                    <span class="tag">&lt;head&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;title&gt;</span>Mi Primera Página<span class="tag">&lt;/title&gt;</span><br>
                    <span class="tag">&lt;/head&gt;</span><br>
                    <span class="tag">&lt;body&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;h1&gt;</span>¡Hola Mundo!<span class="tag">&lt;/h1&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;p&gt;</span>Esta es mi primera página web.<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;/body&gt;</span><br>
                    <span class="tag">&lt;/html&gt;</span>
                </div>
            </div>
            
            <div class="col-12 text-center mt-4">
                <!-- MODIFICADO: Usando route de Laravel -->
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Documentación
                </button>
                <button class="btn btn-lg btn-success" id="testButton" onclick="checkTestAvailability()">
                    <i class="fas fa-check-circle me-2"></i>Hacer un Test
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        // Ocultar animación y mostrar contenido después de 3 segundos
        setTimeout(() => {
            document.getElementById('bookAnimation').style.display = 'none';
            document.getElementById('docContent').style.display = 'block';
        }, 3000);
        
        // Función para navegar con animación
        function navigateWithAnimation(url) {
            document.getElementById('bookAnimation').style.display = 'flex';
            document.getElementById('docContent').style.display = 'none';
            
            setTimeout(() => {
                window.location.href = url;
            }, 1500);
        }

        // Variable global para el temporizador
        let countdownInterval;

        // Función para verificar disponibilidad del test
        function checkTestAvailability() {
            // Hacer una petición al servidor para verificar si puede hacer el test
            fetch('{{ route("test.check", "html1") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        // Puede hacer el test, redirigir
                        window.location.href = '{{ route("test.show", "html1") }}';
                    } else {
                        // No puede hacer el test, mostrar modal con temporizador
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // En caso de error, intentar redirigir directamente
                    window.location.href = '{{ route("test.show", "html1") }}';
                });
        }

        // Función para mostrar el modal de test bloqueado
        function showTestBlockedModal(nextAvailableTime) {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'flex';
            
            // Iniciar el temporizador
            startCountdown(nextAvailableTime);
        }

        // Función para cerrar el modal
        function closeTestBlockedModal() {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'none';
            
            // Limpiar el intervalo del temporizador
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }

        // Función para iniciar el temporizador
        function startCountdown(nextAvailableTime) {
            const countdownElement = document.getElementById('countdownTimer');
            
            function updateCountdown() {
                const now = new Date().getTime();
                const targetTime = new Date(nextAvailableTime).getTime();
                const timeLeft = targetTime - now;
                
                if (timeLeft <= 0) {
                    countdownElement.innerHTML = '00:00:00';
                    clearInterval(countdownInterval);
                    
                    // Actualizar el botón para permitir hacer el test
                    setTimeout(() => {
                        closeTestBlockedModal();
                        // Aquí podrías recargar la página o actualizar el estado del botón
                    }, 1000);
                    return;
                }
                
                // Calcular horas, minutos y segundos
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                // Formatear el tiempo
                const formattedTime = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
                
                countdownElement.innerHTML = formattedTime;
            }
            
            // Actualizar inmediatamente y luego cada segundo
            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        // Cerrar modal si se hace clic fuera del contenido
        document.getElementById('testBlockedModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTestBlockedModal();
            }
        });
    </script>
</body>
</html>
