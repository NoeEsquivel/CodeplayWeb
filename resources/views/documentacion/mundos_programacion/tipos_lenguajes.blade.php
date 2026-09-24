<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Tipos de Lenguajes</title>
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
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-cover">
                <div class="book-content">
                    <h3>CODEPLAY</h3>
                    <p>Tipos de Lenguajes</p>
                </div>
            </div>
        </div>
        <p class="text-white mt-4 animate__animated animate__fadeIn animate__delay-1s">Cargando documento...</p>
    </div>

    <div class="test-blocked-modal" id="testBlockedModal">
        <div class="test-blocked-content">
            <div class="emoticon">😴 ⏰ 🚫</div>
            <h3 class="text-warning mb-3">¡Test No Disponible!</h3>
            <p class="lead">Solo puedes realizar este Test cada 24 horas, regresa manana e intentalo de nuevo 😊🎮</p>
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
                <h1><i class="fas fa-globe-americas me-2"></i>Tipos de Lenguajes</h1>
                <p class="lead">No todos los lenguajes sirven para lo mismo. Cada uno tiene su terreno, sus fortalezas y su estilo.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-map-signs me-2"></i>¿Por que existen tantos lenguajes?</h2>
                <p>La programacion no es una sola herramienta. Es mas parecida a una caja de herramientas: algunas piezas sirven para construir paginas web, otras para analizar datos, crear videojuegos, mover robots, guardar informacion o crear aplicaciones grandes.</p>
                <p>Para empezar no necesitas dominar todos los lenguajes. Lo importante es entender <strong>para que sirve cada uno</strong>, en que ambiente se usa y que tipo de problema ayuda a resolver.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-users-cog me-2"></i>Una comparacion rapida</h2>
                <p>Imagina que estas creando una pagina web para un torneo escolar. Diferentes lenguajes pueden participar en distintas partes del proyecto.</p>
                <ul>
                    <li><strong>HTML</strong> coloca titulos, parrafos, botones e imagenes.</li>
                    <li><strong>CSS</strong> decide colores, fuentes, espacios y diseno.</li>
                    <li><strong>JavaScript</strong> hace que la pagina responda a clics y acciones.</li>
                    <li><strong>PHP</strong> puede trabajar en el servidor para guardar datos o iniciar sesion.</li>
                    <li><strong>SQL</strong> consulta informacion en una base de datos.</li>
                </ul>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/mundo_lenguajes_equipo_web.svg') }}" alt="Comparacion de lenguajes trabajando en una pagina web" class="img-fluid doc-image">
                <p class="doc-caption">En un proyecto real, varios lenguajes pueden trabajar juntos.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-desktop me-2"></i>Lenguajes y tecnologias de la web</h2>
                <p>Estos son los primeros que has visto en CodePlay porque permiten crear resultados visuales rapido. Son buenos para principiantes porque puedes ver el resultado en el navegador.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Lenguaje o tecnologia</th><th>Ambito</th><th>Para que sirve</th><th>Ejemplo de uso</th></tr></thead>
                        <tbody>
                            <tr><td><strong>HTML</strong></td><td>Estructura web</td><td>Organiza el contenido de una pagina</td><td>Crear titulos, textos, imagenes, enlaces y formularios</td></tr>
                            <tr><td><strong>CSS</strong></td><td>Diseno visual</td><td>Controla apariencia, colores y distribucion</td><td>Diseñar una tarjeta, un menu o una pagina con estilo gamer</td></tr>
                            <tr><td><strong>JavaScript</strong></td><td>Interactividad</td><td>Hace que la pagina responda a acciones</td><td>Botones, quizzes, juegos, validaciones y animaciones</td></tr>
                            <tr><td><strong>PHP</strong></td><td>Backend web</td><td>Trabaja en el servidor y genera respuestas</td><td>Iniciar sesion, guardar usuarios o conectar con una base de datos</td></tr>
                            <tr><td><strong>SQL</strong></td><td>Bases de datos</td><td>Consulta, guarda y ordena informacion</td><td>Buscar puntajes, usuarios, resultados o inventario</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/mundo_web_front_backend_db.svg') }}" alt="Frontend backend y base de datos comparados con lenguajes web" class="img-fluid doc-image">
                <p class="doc-caption">La web suele dividirse en lo que ve el usuario, lo que trabaja detras y donde se guardan los datos.</p>
            </div>

            <div class="col-md-5">
                <h2 class="mt-3"><i class="fas fa-layer-group me-2"></i>Frontend, backend y datos</h2>
                <p><strong>Frontend</strong> es lo que el usuario ve y toca: HTML, CSS y JavaScript aparecen mucho aqui.</p>
                <p><strong>Backend</strong> es lo que ocurre en el servidor: validar usuarios, calcular resultados o preparar informacion.</p>
                <p><strong>Base de datos</strong> es donde vive la informacion: cuentas, progreso, preguntas y puntajes.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-brain me-2"></i>Lenguajes para resolver problemas, datos e inteligencia artificial</h2>
                <p>Algunos lenguajes destacan porque permiten escribir ideas de forma clara, trabajar con datos o crear programas que automatizan tareas.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Lenguaje</th><th>Ambiente comun</th><th>Destaca en</th><th>Ejemplo llamativo</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Python</strong></td><td>Computadora, servidores, ciencia de datos</td><td>Automatizacion, datos, inteligencia artificial y aprendizaje sencillo</td><td>Analizar calificaciones, crear un bot o reconocer imagenes</td></tr>
                            <tr><td><strong>R</strong></td><td>Analisis estadistico</td><td>Graficas, estadistica y datos cientificos</td><td>Analizar encuestas o resultados de experimentos</td></tr>
                            <tr><td><strong>MATLAB</strong></td><td>Ingenieria y universidades</td><td>Matematicas, señales, simulaciones y calculos tecnicos</td><td>Simular movimiento, sonido o datos de sensores</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-robot me-2"></i>Robots, placas y componentes</h2>
                <p>Cuando el codigo controla luces, sensores, motores o robots, normalmente se usan lenguajes cercanos al hardware.</p>
                <p><strong>C y C++</strong> aparecen mucho en placas como Arduino, videojuegos, sistemas rapidos y programas que necesitan controlar recursos con precision.</p>
                <p><strong>Python</strong> tambien puede usarse en robotica educativa porque es facil de leer y permite probar ideas con rapidez.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/mundo_robotica_lenguajes.svg') }}" alt="Lenguajes usados en robotica y componentes" class="img-fluid doc-image">
                <p class="doc-caption">En robotica, el codigo puede terminar moviendo algo en el mundo real.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-mobile-alt me-2"></i>Aplicaciones, videojuegos y programas grandes</h2>
                <p>Otros lenguajes son populares porque ayudan a crear aplicaciones completas para computadora, celular o sistemas empresariales.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Lenguaje</th><th>Ambito</th><th>Fortaleza</th><th>Ejemplo de proyecto</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Java</strong></td><td>Aplicaciones grandes, Android, backend</td><td>Orden, estabilidad y proyectos de largo plazo</td><td>App escolar, sistema bancario o aplicacion Android</td></tr>
                            <tr><td><strong>C#</strong></td><td>Windows, videojuegos, Unity</td><td>Aplicaciones visuales y videojuegos con Unity</td><td>Juego 2D, app de escritorio o herramienta interna</td></tr>
                            <tr><td><strong>C++</strong></td><td>Videojuegos, motores graficos, sistemas rapidos</td><td>Velocidad y control</td><td>Motor de videojuego, simulador o software de alto rendimiento</td></tr>
                            <tr><td><strong>Swift</strong></td><td>iPhone, iPad y ecosistema Apple</td><td>Apps para dispositivos Apple</td><td>Aplicacion movil para iOS</td></tr>
                            <tr><td><strong>Kotlin</strong></td><td>Android</td><td>Apps modernas para celulares Android</td><td>App movil de tareas, mapas o aprendizaje</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/mundo_apps_videojuegos.svg') }}" alt="Lenguajes usados para aplicaciones moviles y videojuegos" class="img-fluid doc-image">
                <p class="doc-caption">El lenguaje ideal depende mucho del lugar donde correra el programa.</p>
            </div>

            <div class="col-md-5">
                <h2 class="mt-3"><i class="fas fa-compass me-2"></i>¿Cual aprender primero?</h2>
                <p>Para crear paginas y ver resultados rapido, empieza con <strong>HTML, CSS y JavaScript</strong>.</p>
                <p>Para resolver problemas, automatizar tareas o entrar a inteligencia artificial, <strong>Python</strong> es una excelente puerta.</p>
                <p>Para entender apps grandes o Android, mira <strong>Java</strong> o <strong>Kotlin</strong>. Para videojuegos con Unity, <strong>C#</strong> es muy atractivo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Comparaciones utiles</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Comparacion</th><th>Diferencia principal</th><th>Forma simple de recordarlo</th></tr></thead>
                        <tbody>
                            <tr><td>HTML vs JavaScript</td><td>HTML muestra elementos; JavaScript los hace reaccionar</td><td>HTML pone el boton, JavaScript decide que hace</td></tr>
                            <tr><td>CSS vs JavaScript</td><td>CSS cambia apariencia; JavaScript cambia comportamiento</td><td>CSS pinta, JavaScript actua</td></tr>
                            <tr><td>Python vs Java</td><td>Python suele ser mas directo para aprender; Java es comun en sistemas grandes y Android</td><td>Python es libreta rapida, Java es arquitectura ordenada</td></tr>
                            <tr><td>PHP vs JavaScript</td><td>PHP trabaja normalmente en servidor; JavaScript puede trabajar en navegador</td><td>PHP prepara detras, JS responde delante</td></tr>
                            <tr><td>SQL vs lenguajes generales</td><td>SQL se especializa en consultar datos; otros lenguajes controlan logica general</td><td>SQL pregunta a la base de datos</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <img src="{{ asset('Imagenes/mundo_mapa_lenguajes.svg') }}" alt="Mapa visual de lenguajes por area de uso" class="img-fluid doc-image">
                <p class="doc-caption">No hay un unico camino. Hay rutas segun lo que quieras construir.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que cada lenguaje tiene una especialidad. HTML estructura contenido, CSS lo presenta visualmente y JavaScript agrega interaccion. Python destaca por su facilidad y por su uso en automatizacion, datos e inteligencia artificial. Java, C#, C++ y Kotlin se usan mucho en aplicaciones, videojuegos, sistemas grandes o celulares. PHP trabaja bastante en backend web y SQL se enfoca en consultar bases de datos.</p>
                <p>La mejor pregunta no es “¿cual lenguaje es el mejor?”, sino “¿que quiero construir?”. Desde ahi eliges la herramienta.</p>
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
            fetch('{{ route("test.check", "mundo1") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo1") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo1") }}';
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







