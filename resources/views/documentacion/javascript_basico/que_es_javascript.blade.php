<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - ¿Que es JavaScript?</title>
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
                    <p>¿Que es JavaScript?</p>
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
                <h1><i class="fab fa-js-square me-2"></i>¿Que es JavaScript?</h1>
                <p class="lead">JavaScript es el lenguaje que permite que una pagina web responda, cambie y se sienta viva.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-bolt me-2"></i>La parte interactiva de la web</h2>
                <p>Hasta ahora HTML te ayudo a crear la estructura y CSS te ayudo a darle estilo. JavaScript agrega accion: botones que reaccionan, mensajes que cambian, menus que se abren, juegos, contadores, validaciones y pequenas decisiones.</p>
                <p>Si una pagina fuera un personaje, <strong>HTML seria su cuerpo</strong>, <strong>CSS seria su apariencia</strong> y <strong>JavaScript seria su comportamiento</strong>.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-puzzle-piece me-2"></i>HTML, CSS y JavaScript juntos</h2>
                <p>Los tres trabajan en equipo. HTML coloca un boton, CSS lo vuelve bonito y JavaScript decide que pasa cuando alguien lo presiona.</p>
                <div class="code-block">
                    <span class="tag">&lt;button id="saludo"&gt;</span>Saludar<span class="tag">&lt;/button&gt;</span><br><br>
                    <span class="selector">#saludo</span> {<br>
                    &nbsp;&nbsp;<span class="property">background</span>: <span class="value">#00ff9d</span>;<br>
                    }<br><br>
                    <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'saludo'</span>)<br>
                    &nbsp;&nbsp;.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'¡Hola, jugador!'</span>);<br>
                    &nbsp;&nbsp;});
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_html_css_js_equipo.svg') }}" alt="HTML CSS y JavaScript trabajando juntos" class="img-fluid doc-image">
                <p class="doc-caption">HTML crea, CSS decora y JavaScript responde.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-mouse-pointer me-2"></i>Eventos: cuando algo sucede</h2>
                <p>Un evento es una accion que ocurre en la pagina: un clic, escribir en una caja, mover el mouse o cargar la pantalla. JavaScript puede escuchar esos eventos y ejecutar instrucciones.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_evento_click.svg') }}" alt="Un boton recibe clic y JavaScript cambia un mensaje" class="img-fluid doc-image">
                <p class="doc-caption">El usuario hace una accion y JavaScript responde.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="property">boton</span>.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;<span class="property">mensaje</span>.<span class="property">textContent</span> = <span class="value">'Ganaste 10 puntos'</span>;<br>
                    });
                </div>
                <p class="mt-3"><code>addEventListener</code> significa: “escucha este evento y cuando ocurra, haz esto”.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-edit me-2"></i>Cambiar contenido de la pagina</h2>
                <p>JavaScript puede buscar elementos del HTML y cambiar su texto, sus clases o sus estilos. Para encontrar un elemento se usa mucho <code>document.getElementById()</code>.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;p id="estado"&gt;</span>Esperando...<span class="tag">&lt;/p&gt;</span><br><br>
                    <span class="selector">const</span> estado = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'estado'</span>);<br>
                    estado.<span class="property">textContent</span> = <span class="value">'Listo para jugar'</span>;
                </div>
                <p class="mt-3"><code>textContent</code> cambia el texto que aparece dentro de un elemento.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_cambiar_texto.svg') }}" alt="JavaScript cambia el texto de un parrafo" class="img-fluid doc-image">
                <p class="doc-caption">Una pagina puede cambiar sin recargarse por completo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-code me-2"></i>¿Donde se escribe JavaScript?</h2>
                <p>Para practicar puedes escribir JavaScript dentro de una etiqueta <code>&lt;script&gt;</code>, normalmente antes de cerrar <code>&lt;/body&gt;</code>. En proyectos mas grandes se usa un archivo externo, por ejemplo <code>app.js</code>.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_script_ubicacion.svg') }}" alt="Ubicacion de la etiqueta script antes de cerrar body" class="img-fluid doc-image">
                <p class="doc-caption">Colocar el script al final ayuda a que el HTML exista antes de intentar modificarlo.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;body&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;h1 id="titulo"&gt;</span>Hola<span class="tag">&lt;/h1&gt;</span><br><br>
                    &nbsp;&nbsp;<span class="tag">&lt;script&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">console</span>.<span class="value">log</span>(<span class="value">'Pagina cargada'</span>);<br>
                    &nbsp;&nbsp;<span class="tag">&lt;/script&gt;</span><br>
                    <span class="tag">&lt;/body&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-terminal me-2"></i>La consola</h2>
                <p>La consola del navegador sirve para probar mensajes, ver errores y entender que esta pasando. Es como una pequena ventana de comunicacion entre tu codigo y tu mente de detective.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="property">console</span>.<span class="value">log</span>(<span class="value">'Probando mi codigo'</span>);<br>
                    <span class="property">console</span>.<span class="value">log</span>(<span class="value">5 + 3</span>);
                </div>
                <p class="mt-3">Usa <code>console.log()</code> para revisar valores mientras aprendes.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_console_log.svg') }}" alt="Consola del navegador mostrando mensajes de JavaScript" class="img-fluid doc-image">
                <p class="doc-caption">La consola ayuda a probar y corregir pequenos errores.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Concepto</th><th>Que significa</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td>JavaScript</td><td>Lenguaje que agrega interactividad</td><td><code>alert('Hola')</code></td></tr>
                            <tr><td>Evento</td><td>Algo que sucede en la pagina</td><td><code>click</code></td></tr>
                            <tr><td><code>getElementById</code></td><td>Busca un elemento por su id</td><td><code>document.getElementById('titulo')</code></td></tr>
                            <tr><td><code>textContent</code></td><td>Cambia o lee texto</td><td><code>mensaje.textContent = 'Listo'</code></td></tr>
                            <tr><td><code>console.log</code></td><td>Muestra mensajes en consola</td><td><code>console.log('OK')</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea un boton que cambie un mensaje cuando el usuario haga clic. Es un primer paso pequeno, pero ya es interactividad real.</p>
                <div class="code-block">
                    <span class="tag">&lt;p id="mensaje"&gt;</span>Presiona el boton<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;button id="boton"&gt;</span>Jugar<span class="tag">&lt;/button&gt;</span><br><br>
                    <span class="tag">&lt;script&gt;</span><br>
                    &nbsp;&nbsp;<span class="selector">const</span> boton = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'boton'</span>);<br>
                    &nbsp;&nbsp;<span class="selector">const</span> mensaje = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'mensaje'</span>);<br><br>
                    &nbsp;&nbsp;boton.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;mensaje.<span class="property">textContent</span> = <span class="value">'¡Comienza la aventura!'</span>;<br>
                    &nbsp;&nbsp;});<br>
                    <span class="tag">&lt;/script&gt;</span>
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
            fetch('{{ route("test.check", "js1") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "js1") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "js1") }}';
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




