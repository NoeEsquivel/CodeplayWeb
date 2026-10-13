<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Programacion de Software</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
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

        .doc-caption {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.9rem;
            margin-top: -0.5rem;
            margin-bottom: 1.25rem;
        }

        .code-block {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            border-left: 4px solid var(--accent-color);
            overflow-x: auto;
        }

        .tag { color: #569cd6; }
        .selector { color: #dcdcaa; }
        .property { color: #9cdcfe; }
        .value { color: #ce9178; }
        .comment { color: #6a9955; }

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
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-cover">
                <div class="book-content">
                    <h3>CODEPLAY</h3>
                    <p>Programacion de Software</p>
                </div>
            </div>
        </div>
        <p class="text-white mt-4 animate__animated animate__fadeIn animate__delay-1s">Cargando documento...</p>
    </div>

    <div class="test-blocked-modal" id="testBlockedModal">
        <div class="test-blocked-content">
            <div class="emoticon">😴 ⏰ 🚫</div>
            <h3 class="text-warning mb-3">¡Test no disponible!</h3>
            <p class="lead">Solo puedes realizar este test cada {{ \App\Services\TestAvailabilityService::COOLDOWN_HOURS }} horas. Podrás intentarlo nuevamente cuando termine el contador. 😊🎮</p>
            <div class="countdown-timer" id="countdownTimer">--:--:--</div>
            <p class="text-muted">Tiempo restante para poder realizar el test nuevamente</p>
            <button class="btn btn-success mt-3" onclick="closeTestBlockedModal()">
                <i class="fas fa-check me-2"></i>Entendido
            </button>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            <span class="game-title">CODEPLAY</span>
            <div class="ms-auto">
                <div class="player-name-display">
                    <i class="fas fa-user me-2"></i>
                    <span id="playerNameDisplay">{{ session('usuario_nombre') ?? 'Usuario' }}</span>
                    <span class="badge bg-success ms-2">Alumno</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container doc-container animate__animated animate__fadeIn" id="docContent" style="display: none;">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1><i class="fas fa-laptop-code me-2"></i>Programacion de Software</h1>
                <p class="lead">Descubre como nacen los programas que usamos en la web, la computadora y el celular.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-lightbulb me-2"></i>¿Que es programar software?</h2>
                <p>Programar software significa crear instrucciones para que una computadora, celular, servidor o dispositivo haga algo util. Puede ser una calculadora, una app de tareas, un videojuego, una tienda en linea o una plataforma educativa como CodePlay.</p>
                <p>Un programa no aparece completo de golpe. Normalmente empieza con una idea, despues se disena una solucion, se escribe codigo, se prueba, se corrigen errores y se mejora con el tiempo.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-globe me-2"></i>Programas web</h2>
                <p>Un programa web funciona desde el navegador. No necesitas instalarlo como una app tradicional; entras con una direccion, inicias sesion y usas sus funciones.</p>
                <p>Ejemplos comunes son plataformas educativas, correos electronicos, redes sociales, tiendas en linea, paneles administrativos y juegos sencillos de navegador.</p>
                <ul>
                    <li>Se actualizan en el servidor.</li>
                    <li>Pueden usarse desde distintas computadoras.</li>
                    <li>Normalmente necesitan internet.</li>
                    <li>Usan frontend, backend y base de datos.</li>
                </ul>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/mundo_software_web_app.svg') }}" alt="Aplicacion web funcionando desde un navegador" class="img-fluid doc-image">
                <p class="doc-caption">Una app web vive en un servidor y se usa desde el navegador.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-desktop me-2"></i>Programas instalados en PC</h2>
                <p>Un programa de escritorio se instala en la computadora. Puede funcionar sin navegador y muchas veces aprovecha mejor los archivos, ventanas y recursos del sistema.</p>
                <p>Ejemplos conocidos son editores de imagen, reproductores de musica, videojuegos instalados, programas de oficina, herramientas de diseno y entornos de desarrollo.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/mundo_software_pc_instalado.svg') }}" alt="Programas instalados en una computadora personal" class="img-fluid doc-image">
                <p class="doc-caption">Un programa instalado vive en tu computadora y puede trabajar con sus archivos y recursos.</p>
            </div>

            <div class="col-md-5">
                <h2 class="mt-3"><i class="fas fa-mobile-alt me-2"></i>Apps moviles</h2>
                <p>Tambien existen aplicaciones para celular. Suelen instalarse desde tiendas como Play Store o App Store y estan pensadas para pantallas tactiles, notificaciones, camara, ubicacion y uso rapido.</p>
                <p>Para Android se usan mucho Kotlin y Java. Para iPhone se usa Swift. Tambien existen herramientas que permiten crear apps para varias plataformas.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-tools me-2"></i>Editores de codigo e IDEs</h2>
                <p>Para crear software se usan programas especiales para escribir, organizar y revisar codigo. Algunos son ligeros y otros incluyen muchas herramientas integradas.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Herramienta</th><th>Tipo</th><th>Uso comun</th><th>Idea simple</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Visual Studio Code</strong></td><td>Editor de codigo</td><td>Web, JavaScript, Python y muchos lenguajes</td><td>Ligero, flexible y con extensiones</td></tr>
                            <tr><td><strong>Cursor</strong></td><td>Editor con apoyo de IA</td><td>Proyectos de codigo con ayuda inteligente</td><td>Como VS Code, pero pensado para trabajar junto a IA</td></tr>
                            <tr><td><strong>Visual Studio</strong></td><td>IDE completo</td><td>C#, .NET, apps de escritorio y proyectos grandes</td><td>Un taller completo con muchas herramientas</td></tr>
                            <tr><td><strong>IntelliJ IDEA</strong></td><td>IDE</td><td>Java y proyectos grandes</td><td>Muy usado en desarrollo profesional con Java</td></tr>
                            <tr><td><strong>PyCharm</strong></td><td>IDE</td><td>Python</td><td>Especializado para proyectos Python</td></tr>
                            <tr><td><strong>Android Studio</strong></td><td>IDE</td><td>Apps Android</td><td>Herramienta comun para crear apps moviles Android</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-pencil-ruler me-2"></i>Del problema a la solucion</h2>
                <p>Crear software no es solo escribir codigo. Antes hay que entender que problema se quiere resolver. Por ejemplo: “quiero que un alumno aprenda HTML”, “quiero guardar su puntaje” o “quiero que un boton muestre una pista”.</p>
                <p>Despues se divide el problema en piezas pequenas. Esa es una habilidad clave: convertir una idea grande en pasos mas faciles.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/mundo_software_proceso.svg') }}" alt="Proceso de crear software desde idea hasta mejora" class="img-fluid doc-image">
                <p class="doc-caption">Idea, diseno, codigo, pruebas y mejoras: el ciclo nunca es tan lineal, pero ayuda a orientarse.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-bug me-2"></i>Probar y corregir errores</h2>
                <p>Los errores son parte normal de programar. A veces una pantalla no carga, un boton no responde o un dato no se guarda. Probar sirve para descubrir esos problemas antes de que el usuario los encuentre.</p>
                <p>Corregir errores tambien se llama depurar. No es fallar; es investigar. Un buen programador aprende a leer mensajes de error, probar cambios pequenos y confirmar que la solucion funciona.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/mundo_software_debug.svg') }}" alt="Depuracion de software encontrando y corrigiendo errores" class="img-fluid doc-image">
                <p class="doc-caption">Depurar es buscar pistas hasta encontrar por que algo no funciona.</p>
            </div>

            <div class="col-md-5">
                <h2 class="mt-3"><i class="fas fa-code-branch me-2"></i>Versiones y colaboracion</h2>
                <p>Cuando un proyecto crece, se necesita guardar versiones. Herramientas como Git y GitHub ayudan a registrar cambios, volver atras si algo sale mal y trabajar con otras personas.</p>
                <p>Cada commit es como una foto del proyecto en un momento importante.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Comparacion rapida</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Tipo de software</th><th>Donde se usa</th><th>Ventaja</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Web</strong></td><td>Navegador</td><td>Acceso desde muchos dispositivos</td><td>CodePlay, correo, tienda en linea</td></tr>
                            <tr><td><strong>Escritorio</strong></td><td>PC instalada</td><td>Puede integrarse bien con el sistema</td><td>Editor de imagen, videojuego instalado</td></tr>
                            <tr><td><strong>Movil</strong></td><td>Celular o tablet</td><td>Usa camara, tactil, ubicacion y notificaciones</td><td>App de tareas, red social, juego movil</td></tr>
                            <tr><td><strong>Servidor</strong></td><td>Computadora remota</td><td>Atiende usuarios y procesa informacion</td><td>Login, API, guardado de datos</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <img src="{{ asset('Imagenes/mundo_software_tipos.svg') }}" alt="Tipos de software: web escritorio movil y servidor" class="img-fluid doc-image">
                <p class="doc-caption">El software puede vivir en muchos lugares: navegador, PC, celular o servidor.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que programar software es convertir ideas en instrucciones que una maquina puede ejecutar. Existen programas web, programas instalados en PC, apps moviles y sistemas que trabajan en servidores. Tambien viste que los programadores usan editores e IDEs para escribir codigo, probarlo, corregir errores y guardar versiones.</p>
                <p>La programacion de software no empieza con saberlo todo. Empieza con una pregunta: “¿que quiero que haga mi programa?” Luego se avanza paso a paso.</p>
            </div>

            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Documentacion
                </button>
                <button class="btn btn-lg btn-success" id="testButton" onclick="checkTestAvailability()">
                    <i class="fas fa-check-circle me-2"></i>Hacer un Test
                </button>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script>
        setTimeout(() => {
            document.getElementById('bookAnimation').style.display = 'none';
            document.getElementById('docContent').style.display = 'block';
        }, 3000);

        function navigateWithAnimation(url) {
            document.getElementById('bookAnimation').style.display = 'flex';
            document.getElementById('docContent').style.display = 'none';

            setTimeout(() => {
                window.location.href = url;
            }, 1500);
        }

        let countdownInterval;

        function checkTestAvailability() {
            fetch('{{ route("test.check", "mundo3") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo3") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo3") }}';
                });
        }

        function showTestBlockedModal(nextAvailableTime) {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'flex';
            startCountdown(nextAvailableTime);
        }

        function closeTestBlockedModal() {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'none';

            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }

        function startCountdown(nextAvailableTime) {
            const countdownElement = document.getElementById('countdownTimer');

            function updateCountdown() {
                const now = new Date().getTime();
                const targetTime = new Date(nextAvailableTime).getTime();
                const timeLeft = targetTime - now;

                if (timeLeft <= 0) {
                    countdownElement.innerHTML = '00:00:00';
                    clearInterval(countdownInterval);

                    setTimeout(() => {
                        closeTestBlockedModal();
                    }, 1000);
                    return;
                }

                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                countdownElement.innerHTML =
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
            }

            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        document.getElementById('testBlockedModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTestBlockedModal();
            }
        });
    </script>
</body>
</html>









