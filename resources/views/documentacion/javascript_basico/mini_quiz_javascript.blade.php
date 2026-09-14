<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Mini Quiz con JavaScript</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
                    <p>Mini Quiz con JavaScript</p>
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
            <img src="https://img.icons8.com/nolan/48/code.png" alt="Logo" width="40" height="40">
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
                <h1><i class="fas fa-question-circle me-2"></i>Mini Quiz con JavaScript</h1>
                <p class="lead">Construye la logica basica de un juego de preguntas con opciones, puntaje y resultado.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-trophy me-2"></i>Un quiz es un juego de decisiones</h2>
                <p>Un mini quiz parece sencillo, pero junta varias ideas importantes: guardar preguntas, mostrar opciones, escuchar clics, comparar respuestas y sumar puntos.</p>
                <p>Este modulo te muestra la logica por partes, sin complicarlo con muchas pantallas. La meta es entender el cerebro del quiz.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-list me-2"></i>Guardar preguntas</h2>
                <p>Primero necesitamos datos. Una forma simple es guardar cada pregunta como un objeto con texto, opciones y respuesta correcta.</p>
                <div class="code-block">
                    <span class="selector">const</span> pregunta = {<br>
                    &nbsp;&nbsp;texto: <span class="value">'¿Que hace CSS?'</span>,<br>
                    &nbsp;&nbsp;opciones: [<span class="value">'Estructura'</span>, <span class="value">'Estilo'</span>, <span class="value">'Base de datos'</span>],<br>
                    &nbsp;&nbsp;correcta: <span class="value">1</span><br>
                    };
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_quiz_objeto_pregunta.svg') }}" alt="Objeto de pregunta con opciones y respuesta correcta" class="img-fluid doc-image">
                <p class="doc-caption">Una pregunta puede guardarse como datos organizados.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-layer-group me-2"></i>Usar un arreglo de preguntas</h2>
                <p>Un arreglo permite guardar muchas preguntas en una sola variable. Luego puedes mostrar una por una usando un indice.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_quiz_arreglo.svg') }}" alt="Arreglo de preguntas para un mini quiz" class="img-fluid doc-image">
                <p class="doc-caption">El indice indica que pregunta se esta mostrando.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">const</span> preguntas = [pregunta1, pregunta2, pregunta3];<br>
                    <span class="selector">let</span> actual = <span class="value">0</span>;<br><br>
                    <span class="property">console</span>.<span class="value">log</span>(preguntas[actual]);
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-desktop me-2"></i>Mostrar pregunta y opciones</h2>
                <p>JavaScript puede tomar los datos de la pregunta actual y colocarlos dentro del HTML usando <code>textContent</code>.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">function</span> mostrarPregunta() {<br>
                    &nbsp;&nbsp;<span class="selector">const</span> p = preguntas[actual];<br>
                    &nbsp;&nbsp;titulo.<span class="property">textContent</span> = p.texto;<br>
                    &nbsp;&nbsp;opcionA.<span class="property">textContent</span> = p.opciones[<span class="value">0</span>];<br>
                    &nbsp;&nbsp;opcionB.<span class="property">textContent</span> = p.opciones[<span class="value">1</span>];<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_quiz_mostrar.svg') }}" alt="JavaScript mostrando pregunta y opciones en pantalla" class="img-fluid doc-image">
                <p class="doc-caption">Los datos viven en JavaScript, pero se muestran en HTML.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-check-double me-2"></i>Comprobar una respuesta</h2>
                <p>Cuando el usuario elige una opcion, comparamos su opcion con la respuesta correcta. Si coincide, sumamos puntos.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_quiz_comparar.svg') }}" alt="Comparacion de respuesta elegida contra respuesta correcta" class="img-fluid doc-image">
                <p class="doc-caption">La comparacion decide si hay puntos o no.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">let</span> puntaje = <span class="value">0</span>;<br><br>
                    <span class="selector">function</span> responder(opcion) {<br>
                    &nbsp;&nbsp;<span class="selector">if</span> (opcion === preguntas[actual].correcta) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;puntaje = puntaje + <span class="value">10</span>;<br>
                    &nbsp;&nbsp;}<br>
                    }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-forward me-2"></i>Pasar a la siguiente pregunta</h2>
                <p>Despues de responder, aumentamos el indice. Si todavia hay preguntas, mostramos la siguiente. Si ya no hay, mostramos el resultado.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    actual = actual + <span class="value">1</span>;<br><br>
                    <span class="selector">if</span> (actual &lt; preguntas.length) {<br>
                    &nbsp;&nbsp;mostrarPregunta();<br>
                    } <span class="selector">else</span> {<br>
                    &nbsp;&nbsp;mostrarResultado();<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_quiz_flujo.svg') }}" alt="Flujo de un quiz desde pregunta hasta resultado" class="img-fluid doc-image">
                <p class="doc-caption">El quiz avanza con un ciclo simple: mostrar, responder, avanzar.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive"><table class="table table-dark table-striped"><thead><tr><th>Parte</th><th>Funcion</th><th>Ejemplo</th></tr></thead><tbody>
                    <tr><td>Objeto</td><td>Guarda una pregunta completa</td><td><code>{ texto, opciones, correcta }</code></td></tr>
                    <tr><td>Arreglo</td><td>Guarda varias preguntas</td><td><code>const preguntas = []</code></td></tr>
                    <tr><td>Indice</td><td>Indica la pregunta actual</td><td><code>let actual = 0</code></td></tr>
                    <tr><td>Comparacion</td><td>Revisa si la respuesta es correcta</td><td><code>opcion === correcta</code></td></tr>
                    <tr><td>Puntaje</td><td>Guarda los puntos ganados</td><td><code>puntaje += 10</code></td></tr>
                </tbody></table></div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea un quiz de una pregunta. Si el usuario responde bien, muestra un mensaje de victoria.</p>
                <div class="code-block">
                    <span class="selector">const</span> correcta = <span class="value">'b'</span>;<br>
                    <span class="selector">let</span> puntaje = <span class="value">0</span>;<br><br>
                    <span class="selector">function</span> revisar(respuesta) {<br>
                    &nbsp;&nbsp;<span class="selector">if</span> (respuesta === correcta) {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;puntaje += <span class="value">10</span>;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'¡Correcto!'</span>);<br>
                    &nbsp;&nbsp;} <span class="selector">else</span> {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'Sigue intentando'</span>);<br>
                    &nbsp;&nbsp;}<br>
                    }
                </div>
            </div>

            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')"><i class="fas fa-arrow-left me-2"></i>Volver a Documentacion</button>
                <button class="btn btn-lg btn-success" id="testButton" onclick="checkTestAvailability()"><i class="fas fa-check-circle me-2"></i>Hacer un Test</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
            fetch('{{ route("test.check", "js4") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "js4") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "js4") }}';
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






