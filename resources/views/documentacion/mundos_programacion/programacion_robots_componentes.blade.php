<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Programacion de Robots y Componentes</title>
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
                    <p>Programacion de Robots y Componentes</p>
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
                <h1><i class="fas fa-robot me-2"></i>Programacion de Robots y Componentes</h1>
                <p class="lead">Conoce placas, sensores y actuadores reales, y entiende como el codigo puede mover cosas fuera de la pantalla.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-microchip me-2"></i>Cuando el codigo controla el mundo real</h2>
                <p>Programar robots y componentes significa escribir instrucciones para que una placa electronica lea informacion del ambiente y controle piezas fisicas. En una pagina web el codigo puede cambiar un texto; en robotica puede prender una luz, mover un motor, medir distancia o reaccionar a un boton.</p>
                <p>La idea base es muy sencilla: <strong>entrada</strong>, <strong>proceso</strong> y <strong>salida</strong>. Un sensor entrega datos, la placa los interpreta y un componente responde.</p>
            </div>

            <div class="col-12">
                <img src="{{ asset('Imagenes/robotica_placas_reales.png') }}" alt="Placas reales y componentes de robotica educativa" class="img-fluid doc-image">
                <p class="doc-caption">Ejemplos visuales de placas y piezas usadas para aprender robotica y electronica. Las imagenes estan guardadas localmente en el proyecto.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-brain me-2"></i>Placas: el cerebro del proyecto</h2>
                <p>Una placa es una pequeña computadora preparada para conectarse con componentes. Puede recibir energia, ejecutar un programa y comunicarse con sensores o actuadores mediante pines.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Placa</th><th>Como funciona</th><th>Caracteristicas</th><th>Variantes o usos comunes</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Arduino UNO</strong></td>
                                <td>Ejecuta un programa sencillo una y otra vez para leer entradas y controlar salidas.</td>
                                <td>Muy usado para aprender electronica, conectar LEDs, botones, sensores y motores.</td>
                                <td>UNO R3 clasico; UNO R4 Minima para proyectos basicos; UNO R4 WiFi para proyectos conectados con WiFi/Bluetooth.</td>
                            </tr>
                            <tr>
                                <td><strong>Raspberry Pi Pico</strong></td>
                                <td>Es un microcontrolador: pequeño, barato y enfocado en controlar pines y componentes.</td>
                                <td>Sirve para sensores, botones, PWM, I2C, SPI y proyectos de bajo consumo.</td>
                                <td>Pico/Pico H; Pico W/WH con WiFi y Bluetooth; Pico 2 y Pico 2 W con hardware mas reciente.</td>
                            </tr>
                            <tr>
                                <td><strong>Raspberry Pi 5</strong></td>
                                <td>Es una computadora pequeña completa. Puede usar sistema operativo, pantalla, teclado, internet y programas.</td>
                                <td>Tiene procesador potente, memoria RAM, USB, red, salida de video y pines GPIO.</td>
                                <td>Disponible en variantes de RAM. Conviene para proyectos con camara, servidor, IA ligera o interfaz completa.</td>
                            </tr>
                            <tr>
                                <td><strong>micro:bit</strong></td>
                                <td>Placa educativa pensada para aprender programacion fisica de forma amigable.</td>
                                <td>Incluye matriz de LEDs, botones, sensores de movimiento, compass, pines y comunicacion inalambrica.</td>
                                <td>micro:bit V1 y V2. La V2 agrega mejoras como microfono, altavoz y logo tactil.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-exchange-alt me-2"></i>Microcontrolador vs computadora pequeña</h2>
                <p><strong>Arduino UNO</strong>, <strong>Raspberry Pi Pico</strong> y <strong>micro:bit</strong> son ideales para controlar componentes directamente. Se enfocan en leer pines y ejecutar tareas concretas.</p>
                <p><strong>Raspberry Pi 5</strong> se parece mas a una PC pequeña. Puede abrir aplicaciones, conectarse a internet, manejar camaras, ejecutar servidores y tambien usar pines GPIO.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/mundo_robot_ciclo_entrada_salida.svg') }}" alt="Ciclo entrada proceso salida en robotica" class="img-fluid doc-image">
                <p class="doc-caption">Aunque las placas sean distintas, muchas comparten este ciclo mental: leer, decidir y actuar.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-satellite-dish me-2"></i>Sensores: los sentidos del robot</h2>
                <p>Un sensor mide algo del ambiente y lo convierte en un dato. El programa usa ese dato para decidir. Un sensor no “piensa”; entrega señales que la placa puede interpretar.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/robotica_sensores_reales.png') }}" alt="Sensores reales usados en robotica educativa" class="img-fluid doc-image">
                <p class="doc-caption">Sensores reales: distancia, luz, temperatura y boton. Todos sirven como entradas.</p>
            </div>

            <div class="col-md-5">
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Sensor</th><th>Que detecta</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td>Ultrasonico</td><td>Distancia</td><td>Carrito que evita obstaculos</td></tr>
                            <tr><td>Fotoresistencia</td><td>Luz u oscuridad</td><td>Lampara automatica</td></tr>
                            <tr><td>Temperatura</td><td>Calor o frio</td><td>Ventilador automatico</td></tr>
                            <tr><td>Boton</td><td>Presionado o no</td><td>Iniciar una secuencia</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-cogs me-2"></i>Actuadores: las acciones del proyecto</h2>
                <p>Un actuador convierte una orden del codigo en una accion visible, sonora o fisica. Si los sensores son los sentidos, los actuadores son los musculos, la voz o las luces del proyecto.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/robotica_actuadores_reales.png') }}" alt="Actuadores reales como motores LEDs buzzer y pantalla" class="img-fluid doc-image">
                <p class="doc-caption">Actuadores reales: motores, servomotores, LEDs, buzzer y pantalla. Todos sirven como salidas.</p>
            </div>

            <div class="col-md-5">
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Actuador</th><th>Que hace</th><th>Uso comun</th></tr></thead>
                        <tbody>
                            <tr><td>LED</td><td>Emite luz</td><td>Indicadores, semaforos, avisos</td></tr>
                            <tr><td>Motor DC</td><td>Gira continuamente</td><td>Ruedas de carritos robot</td></tr>
                            <tr><td>Servomotor</td><td>Gira a posiciones controladas</td><td>Brazos, puertas, direccion</td></tr>
                            <tr><td>Buzzer</td><td>Produce sonido</td><td>Alarmas y señales</td></tr>
                            <tr><td>Pantalla</td><td>Muestra texto o datos</td><td>Temperatura, puntaje, estado</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-plug me-2"></i>Pines, protoboard y cables</h2>
                <p>Los pines son puntos de conexion de la placa. Algunos reciben datos, otros envian señales y otros entregan energia o tierra. La <strong>protoboard</strong> permite armar circuitos sin soldar, usando cables jumper para unir componentes.</p>
                <p>Una regla importante: no todo pin soporta cualquier cosa. Algunos son digitales, otros analogicos, algunos trabajan con 3.3V y otros con 5V. Por eso en proyectos reales siempre se revisa la documentacion de la placa antes de conectar componentes.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-car-side me-2"></i>Ejemplo: carrito que evita choques</h2>
                <p>Un carrito robot puede usar un sensor ultrasonico para medir distancia. Si detecta un objeto cerca, la placa puede detener motores, girar o retroceder. Ese comportamiento se parece a una decision de JavaScript: si pasa algo, haz una accion.</p>
                <p>Este proyecto combina entradas, salidas y decisiones: sensor de distancia, placa, motores y energia.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/robotica_proyectos_reales.png') }}" alt="Proyecto realista de carrito robot y semaforo con LEDs" class="img-fluid doc-image">
                <p class="doc-caption">Un carrito robot y un semaforo con LEDs muestran como el codigo controla piezas fisicas.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/mundo_robot_mapa_componentes.svg') }}" alt="Mapa de placa sensores actuadores entradas y salidas" class="img-fluid doc-image">
                <p class="doc-caption">El mapa mental sigue siendo simple: placa al centro, entradas de un lado y salidas del otro.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-traffic-light me-2"></i>Ejemplo: semaforo con LEDs</h2>
                <p>Un semaforo basico usa tres LEDs: rojo, amarillo y verde. La placa prende uno, espera un tiempo, lo apaga y prende el siguiente. Este ejemplo enseña secuencias, tiempos, salidas digitales y orden logico.</p>
                <p>Si agregas un boton, el semaforo puede cambiar de estado cuando alguien lo presiona. Si agregas un sensor de luz, podria activarse solo cuando oscurece.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-code me-2"></i>Lenguajes y entornos comunes</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Lenguaje o entorno</th><th>Donde aparece</th><th>Por que se usa</th></tr></thead>
                        <tbody>
                            <tr><td>Bloques visuales</td><td>micro:bit, Scratch, entornos educativos</td><td>Permiten aprender logica sin escribir tanto texto</td></tr>
                            <tr><td>C / C++</td><td>Arduino y muchos microcontroladores</td><td>Permiten controlar hardware con precision</td></tr>
                            <tr><td>MicroPython / Python</td><td>micro:bit, Pico, Raspberry Pi</td><td>Son mas faciles de leer para principiantes</td></tr>
                            <tr><td>JavaScript</td><td>Web conectada a dispositivos y algunos entornos educativos</td><td>Sirve para crear interfaces que controlen proyectos</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Comparacion rapida</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Concepto</th><th>Que significa</th><th>Ejemplo real</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Placa</strong></td><td>Cerebro que ejecuta el programa</td><td>Arduino UNO, Raspberry Pi Pico, micro:bit</td></tr>
                            <tr><td><strong>Sensor</strong></td><td>Entrada que mide algo</td><td>Distancia, luz, temperatura</td></tr>
                            <tr><td><strong>Actuador</strong></td><td>Salida que realiza una accion</td><td>LED, motor, buzzer, pantalla</td></tr>
                            <tr><td><strong>Microcontrolador</strong></td><td>Placa enfocada en tareas concretas y componentes</td><td>Arduino, Pico, micro:bit</td></tr>
                            <tr><td><strong>Computadora pequeña</strong></td><td>Placa capaz de usar sistema operativo y programas completos</td><td>Raspberry Pi 5</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que la robotica une codigo, electronica y componentes reales. Las placas como Arduino, Raspberry Pi Pico, Raspberry Pi 5 y micro:bit tienen distintos niveles de potencia y usos. Los sensores entregan datos del ambiente; los actuadores convierten decisiones del codigo en acciones fisicas.</p>
                <p>La pregunta clave para elegir componentes es: “¿que necesito detectar y que quiero que ocurra despues?”. Con esa idea puedes imaginar proyectos como semaforos, carritos, alarmas, estaciones de clima, medidores y robots sencillos.</p>
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
            fetch('{{ route("test.check", "mundo4") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo4") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo4") }}';
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











