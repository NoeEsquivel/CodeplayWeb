<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Variables y Decisiones</title>
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
                    <p>Variables y Decisiones</p>
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
                <h1><i class="fas fa-code-branch me-2"></i>Variables y Decisiones</h1>
                <p class="lead">Aprende a guardar datos y hacer que tu pagina tome decisiones simples.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-backpack me-2"></i>Variables: mochilas para guardar datos</h2>
                <p>Una variable es como una mochila con nombre. Dentro puedes guardar un dato: el nombre del jugador, sus puntos, si gano una partida o el nivel actual.</p>
                <p>En JavaScript se usan mucho <strong>let</strong> y <strong>const</strong>. Usa <code>let</code> cuando el valor puede cambiar y <code>const</code> cuando quieres guardar algo que no deberia cambiar durante esa parte del programa.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-box me-2"></i>Guardar informacion</h2>
                <p>Primero creas la variable, luego puedes usarla en mensajes, calculos o decisiones.</p>
                <div class="code-block">
                    <span class="selector">let</span> puntos = <span class="value">0</span>;<br>
                    <span class="selector">const</span> nombre = <span class="value">'Noe'</span>;<br><br>
                    puntos = puntos + <span class="value">10</span>;<br><br>
                    <span class="property">console</span>.<span class="value">log</span>(nombre);<br>
                    <span class="property">console</span>.<span class="value">log</span>(puntos);
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_variables_mochilas.svg') }}" alt="Variables como mochilas que guardan datos" class="img-fluid doc-image">
                <p class="doc-caption">Cada variable tiene un nombre y guarda un valor.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-tags me-2"></i>Tipos de datos simples</h2>
                <p>No todos los datos son iguales. Algunos son texto, otros son numeros y otros son respuestas de verdadero o falso.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_tipos_datos.svg') }}" alt="Tipos de datos basicos en JavaScript: texto numero y booleano" class="img-fluid doc-image">
                <p class="doc-caption">Texto, numeros y booleanos son piezas basicas para construir logica.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">let</span> jugador = <span class="value">'Luna'</span>; <span class="comment">// texto</span><br>
                    <span class="selector">let</span> vida = <span class="value">100</span>; <span class="comment">// numero</span><br>
                    <span class="selector">let</span> tieneLlave = <span class="value">true</span>; <span class="comment">// booleano</span>
                </div>
                <p class="mt-3">Un booleano solo puede ser <code>true</code> o <code>false</code>. Es perfecto para preguntas de si/no.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-calculator me-2"></i>Cambiar valores</h2>
                <p>Las variables creadas con <code>let</code> pueden cambiar. Esto sirve para sumar puntos, restar vida o avanzar de nivel.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">let</span> monedas = <span class="value">3</span>;<br>
                    monedas = monedas + <span class="value">2</span>;<br><br>
                    <span class="selector">let</span> vida = <span class="value">100</span>;<br>
                    vida = vida - <span class="value">25</span>;<br><br>
                    <span class="property">console</span>.<span class="value">log</span>(monedas); <span class="comment">// 5</span><br>
                    <span class="property">console</span>.<span class="value">log</span>(vida); <span class="comment">// 75</span>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_cambiar_valores.svg') }}" alt="Variables cambiando puntos y vida de un jugador" class="img-fluid doc-image">
                <p class="doc-caption">Los juegos usan cambios de valores todo el tiempo: puntos, vida, energia y niveles.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Comparaciones</h2>
                <p>Para decidir, JavaScript necesita comparar. Puedes preguntar si un numero es mayor, menor o igual a otro. Tambien puedes comparar textos.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_comparaciones.svg') }}" alt="Comparaciones en JavaScript con mayor que menor que e igualdad" class="img-fluid doc-image">
                <p class="doc-caption">Comparar es hacer una pregunta que responde verdadero o falso.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    puntos &gt; <span class="value">50</span><br>
                    vida &lt;= <span class="value">0</span><br>
                    respuesta === <span class="value">'a'</span><br>
                    nombre !== <span class="value">''</span>
                </div>
                <p class="mt-3"><code>===</code> compara si dos valores son iguales. <code>!==</code> compara si son diferentes.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-road me-2"></i>if y else: elegir caminos</h2>
                <p><code>if</code> significa “si pasa esto, haz esto”. <code>else</code> significa “si no pasa, haz otra cosa”. Es una de las ideas mas importantes de la programacion.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">let</span> puntos = <span class="value">80</span>;<br><br>
                    <span class="selector">if</span> (puntos &gt;= <span class="value">60</span>) {<br>
                    &nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'¡Nivel superado!'</span>);<br>
                    } <span class="selector">else</span> {<br>
                    &nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'Intentalo de nuevo'</span>);<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_if_else_camino.svg') }}" alt="Diagrama if else con dos caminos posibles" class="img-fluid doc-image">
                <p class="doc-caption">Las decisiones hacen que el programa no siempre haga lo mismo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-question-circle me-2"></i>Ejemplo: revisar una respuesta</h2>
                <p>Un mini quiz necesita guardar la respuesta correcta, leer la respuesta del usuario y comparar. Si coinciden, suma puntos o muestra un mensaje positivo.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_decision_quiz.svg') }}" alt="Decision de quiz comparando respuesta de usuario contra respuesta correcta" class="img-fluid doc-image">
                <p class="doc-caption">Los tests y juegos usan decisiones todo el tiempo.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">const</span> correcta = <span class="value">'b'</span>;<br>
                    <span class="selector">let</span> respuesta = <span class="value">'b'</span>;<br><br>
                    <span class="selector">if</span> (respuesta === correcta) {<br>
                    &nbsp;&nbsp;<span class="property">console</span>.<span class="value">log</span>(<span class="value">'Correcto'</span>);<br>
                    } <span class="selector">else</span> {<br>
                    &nbsp;&nbsp;<span class="property">console</span>.<span class="value">log</span>(<span class="value">'Incorrecto'</span>);<br>
                    }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Concepto</th><th>Que significa</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td><code>let</code></td><td>Crea una variable que puede cambiar</td><td><code>let puntos = 0;</code></td></tr>
                            <tr><td><code>const</code></td><td>Crea una variable que no deberia cambiar</td><td><code>const nombre = 'Ana';</code></td></tr>
                            <tr><td>Booleano</td><td>Valor verdadero o falso</td><td><code>true</code> / <code>false</code></td></tr>
                            <tr><td><code>===</code></td><td>Compara si dos valores son iguales</td><td><code>respuesta === 'a'</code></td></tr>
                            <tr><td><code>if/else</code></td><td>Permite tomar una decision</td><td><code>if (vida > 0)</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea una pequena decision para un juego: si el jugador tiene 100 puntos o mas, desbloquea una recompensa; si no, muestra cuantos puntos le faltan.</p>
                <div class="code-block">
                    <span class="selector">let</span> puntos = <span class="value">85</span>;<br>
                    <span class="selector">const</span> meta = <span class="value">100</span>;<br><br>
                    <span class="selector">if</span> (puntos &gt;= meta) {<br>
                    &nbsp;&nbsp;<span class="property">console</span>.<span class="value">log</span>(<span class="value">'Recompensa desbloqueada'</span>);<br>
                    } <span class="selector">else</span> {<br>
                    &nbsp;&nbsp;<span class="property">console</span>.<span class="value">log</span>(<span class="value">'Te faltan ' + (meta - puntos) + ' puntos'</span>);<br>
                    }
                </div>
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
            fetch('{{ route("test.check", "js2") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "js2") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "js2") }}';
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





