<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Videojuegos e Interactividad</title>
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
                    <p>Videojuegos e Interactividad</p>
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
                <h1><i class="fas fa-gamepad me-2"></i>Videojuegos e Interactividad</h1>
                <p class="lead">Desde un juego pequeño hecho en una tarde hasta mundos enormes creados por equipos completos.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-play me-2"></i>¿Que hace que algo sea un videojuego?</h2>
                <p>Un videojuego es un programa interactivo donde el jugador realiza acciones y el sistema responde con reglas, cambios visuales, sonido, puntos, retos o consecuencias. A diferencia de una pelicula, el jugador no solo mira: participa.</p>
                <p>La palabra clave es <strong>interactividad</strong>. Presionar una tecla, mover un personaje, elegir una respuesta, perder vida, ganar monedas o abrir una puerta son ejemplos de interaccion.</p>
            </div>

            <div class="col-12">
                <img src="{{ asset('Imagenes/videojuegos_juego_simple.png') }}" alt="Juego sencillo de plataformas creado como proyecto pequeño" class="img-fluid doc-image">
                <p class="doc-caption">Un juego pequeño puede tener personaje, monedas, obstaculos, vidas y una meta clara.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-clock me-2"></i>Juegos pequeños que puedes crear en una tarde</h2>
                <p>Un videojuego no necesita empezar enorme. Muchos primeros proyectos son muy pequeños y aun asi enseñan muchisimo. La meta no es crear el proximo gran lanzamiento, sino entender reglas, movimiento, colisiones y retroalimentacion.</p>
                <ul>
                    <li><strong>Atrapa monedas:</strong> un personaje recoge objetos antes de que termine el tiempo.</li>
                    <li><strong>Evita obstaculos:</strong> el jugador se mueve para no chocar.</li>
                    <li><strong>Memoria de colores:</strong> repetir una secuencia de botones.</li>
                    <li><strong>Mini quiz:</strong> responder preguntas y sumar puntos.</li>
                    <li><strong>Culebrita:</strong> moverse, comer y crecer sin chocar.</li>
                </ul>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-seedling me-2"></i>Por que empezar pequeño</h2>
                <p>Los juegos pequeños permiten terminar algo real. Eso importa mucho: terminar un proyecto enseña mas que empezar diez proyectos gigantes.</p>
                <p>Un juego sencillo puede hacerse con HTML, CSS y JavaScript si corre en navegador. Tambien puede hacerse con herramientas como Scratch, Godot, Construct, Unity o GameMaker, dependiendo del nivel y el tipo de juego.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-puzzle-piece me-2"></i>Piezas principales de un videojuego</h2>
                <p>Todo videojuego combina piezas. Algunas son visibles, como personajes y escenarios. Otras son invisibles, como reglas, fisica, colisiones y variables.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/videojuegos_piezas_juego.png') }}" alt="Partes de un videojuego como personaje enemigos puntaje controles y sonido" class="img-fluid doc-image">
                <p class="doc-caption">Personaje, enemigos, controles, puntos, sonidos, menu y resultado trabajan juntos.</p>
            </div>

            <div class="col-md-5">
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Pieza</th><th>Funcion</th></tr></thead>
                        <tbody>
                            <tr><td>Jugador</td><td>Elemento que controla la persona</td></tr>
                            <tr><td>Controles</td><td>Teclas, mouse, tactil o mando</td></tr>
                            <tr><td>Reglas</td><td>Que se puede hacer y que no</td></tr>
                            <tr><td>Colisiones</td><td>Detectar choques o contacto</td></tr>
                            <tr><td>Puntaje</td><td>Medir avance o recompensa</td></tr>
                            <tr><td>Estados</td><td>Menu, jugando, pausa, victoria o derrota</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-sync-alt me-2"></i>El ciclo del juego</h2>
                <p>Muchos juegos funcionan con un ciclo que se repite muchas veces por segundo. El juego lee entradas del jugador, actualiza posiciones y reglas, detecta colisiones, dibuja la pantalla y reproduce sonidos.</p>
                <p>Ese ciclo ocurre tan rapido que el jugador lo percibe como movimiento continuo. Por eso un personaje parece correr, saltar o chocar en tiempo real.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Paso</th><th>Que ocurre</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td>Leer entrada</td><td>El juego revisa que hizo el jugador</td><td>Presiono flecha derecha</td></tr>
                            <tr><td>Actualizar</td><td>Cambian posiciones, puntos o vida</td><td>El personaje avanza</td></tr>
                            <tr><td>Colisionar</td><td>Se revisa contacto entre objetos</td><td>Moneda tocada o enemigo golpeado</td></tr>
                            <tr><td>Dibujar</td><td>Se actualiza la pantalla</td><td>Nuevo cuadro del nivel</td></tr>
                            <tr><td>Sonar</td><td>Se reproducen efectos o musica</td><td>Sonido de moneda</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-ruler-combined me-2"></i>Colisiones y reglas</h2>
                <p>Una colision ocurre cuando dos objetos se tocan o se cruzan. Si el jugador toca una moneda, suma puntos. Si toca un enemigo, pierde vida. Si toca una puerta y tiene llave, cambia de nivel.</p>
                <p>Estas reglas se parecen a las decisiones que ya viste en JavaScript: <strong>si ocurre esto, entonces pasa aquello</strong>.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-heart me-2"></i>Retroalimentacion</h2>
                <p>La retroalimentacion es la forma en que el juego le dice al jugador que algo paso. Puede ser un sonido, una animacion, un cambio de color, una vibracion, una moneda que desaparece o una barra de vida que baja.</p>
                <p>Sin retroalimentacion, el jugador se siente perdido. Con buena retroalimentacion, cada accion se siente clara y satisfactoria.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-layer-group me-2"></i>Tipos de videojuegos segun su complejidad</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Nivel</th><th>Ejemplo</th><th>Que necesita</th><th>Tiempo aproximado</th></tr></thead>
                        <tbody>
                            <tr><td>Muy simple</td><td>Quiz, contador, piedra papel o tijera</td><td>Botones, variables y decisiones</td><td>Una tarde</td></tr>
                            <tr><td>Simple</td><td>Culebrita, Pong, memoria</td><td>Movimiento, reglas, puntaje y reinicio</td><td>1 a 3 dias</td></tr>
                            <tr><td>Intermedio</td><td>Plataformas 2D, shooter sencillo</td><td>Niveles, colisiones, enemigos, arte y sonido</td><td>Semanas</td></tr>
                            <tr><td>Grande</td><td>RPG, mundo abierto, juego online</td><td>Equipo, diseño, servidores, historia, muchas pruebas</td><td>Meses o años</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <img src="{{ asset('Imagenes/videojuegos_equipo_complejo.png') }}" alt="Equipo profesional trabajando en videojuego complejo" class="img-fluid doc-image">
                <p class="doc-caption">Un videojuego grande puede requerir programacion, arte, sonido, diseño, produccion, pruebas y coordinacion.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-users me-2"></i>Roles dentro de un equipo grande</h2>
                <p>En un juego pequeño una sola persona puede hacer casi todo. En un juego grande, el trabajo se divide porque hay demasiadas piezas.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Rol</th><th>Que hace</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td>Game designer</td><td>Diseña reglas, niveles, dificultad y experiencia</td><td>Decidir como gana o pierde el jugador</td></tr>
                            <tr><td>Programador</td><td>Convierte reglas en sistemas funcionales</td><td>Movimiento, inventario, enemigos, menus</td></tr>
                            <tr><td>Artista</td><td>Crea personajes, escenarios, objetos e interfaz</td><td>Sprites 2D, modelos 3D, iconos</td></tr>
                            <tr><td>Animador</td><td>Da movimiento a personajes y objetos</td><td>Correr, saltar, atacar, caer</td></tr>
                            <tr><td>Diseñador de sonido</td><td>Crea efectos y ambiente sonoro</td><td>Pasos, golpes, monedas, musica</td></tr>
                            <tr><td>Tester</td><td>Busca errores y problemas de experiencia</td><td>Encontrar bugs o partes injustas</td></tr>
                            <tr><td>Productor</td><td>Organiza tiempos, tareas y comunicacion</td><td>Coordinar entregas del equipo</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-tools me-2"></i>Motores y herramientas</h2>
                <p>Un motor de videojuegos es una herramienta que facilita crear juegos. Puede manejar escenas, fisica, audio, animaciones, camaras, entradas y exportacion.</p>
                <ul>
                    <li><strong>Scratch:</strong> ideal para aprender logica con bloques.</li>
                    <li><strong>Construct:</strong> util para juegos 2D con eventos visuales.</li>
                    <li><strong>Godot:</strong> motor gratuito muy usado para 2D y 3D.</li>
                    <li><strong>Unity:</strong> popular para 2D, 3D, movil e indie.</li>
                    <li><strong>Unreal Engine:</strong> conocido por graficos 3D avanzados.</li>
                    <li><strong>HTML, CSS y JavaScript:</strong> suficientes para juegos sencillos de navegador.</li>
                </ul>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Juego pequeño vs juego complejo</h2>
                <p>Un juego pequeño busca una idea clara y terminable: una pantalla, pocas reglas y una meta simple. Un juego complejo puede necesitar historia, mundos, personajes, inteligencia artificial, economia, servidores, guardado de partidas, rendimiento y muchas pruebas.</p>
                <p>La diferencia no es solo “mejores graficos”. La complejidad crece cuando hay mas sistemas conectados. Si cambias una regla, puede afectar enemigos, niveles, inventario, puntaje y experiencia.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-route me-2"></i>Flujo de desarrollo de un videojuego</h2>
                <p>Crear un videojuego suele pasar por varias etapas. Primero aparece la idea, luego se hace un prototipo para probar si es divertido. Despues se construyen niveles, se prueban errores, se mejora el arte y el sonido, y finalmente se publica o comparte.</p>
            </div>

            <div class="col-12">
                <img src="{{ asset('Imagenes/videojuegos_flujo_desarrollo.png') }}" alt="Flujo visual de desarrollo de un videojuego" class="img-fluid doc-image">
                <p class="doc-caption">Idea, prototipo, juego, pruebas, pulido y publicacion. En proyectos reales se vuelve a etapas anteriores muchas veces.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-vial me-2"></i>Prototipo: probar antes de adornar</h2>
                <p>Un prototipo es una version muy simple del juego. Puede verse feo, tener cuadrados en lugar de personajes y sonidos temporales. Su objetivo es responder una pregunta: ¿la idea funciona?</p>
                <p>Si el prototipo es divertido con graficos simples, puede valer la pena mejorarlo. Si no se siente bien, es mejor descubrirlo temprano.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-bug me-2"></i>Pruebas, bugs y balance</h2>
                <p>Un bug es un error: el jugador atraviesa una pared, el puntaje suma de mas, un enemigo no aparece o el juego se congela. Probar sirve para encontrar esos problemas.</p>
                <p>Balancear significa ajustar dificultad y recompensas. Un juego demasiado facil aburre; uno demasiado dificil frustra. Buen balance significa que el reto se siente justo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que un videojuego combina interaccion, reglas, controles, personajes, estados, sonido, arte y retroalimentacion. Un juego pequeño puede hacerse en una tarde si tiene una idea clara y pocas reglas. Un juego complejo puede requerir meses o años, porque conecta muchos sistemas y necesita un equipo con roles distintos.</p>
                <p>La mejor forma de empezar es crear algo pequeño, terminarlo, probarlo y mejorarlo. Cada mini juego terminado es una pieza mas de experiencia real.</p>
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
            fetch('{{ route("test.check", "mundo5") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo5") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo5") }}';
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












