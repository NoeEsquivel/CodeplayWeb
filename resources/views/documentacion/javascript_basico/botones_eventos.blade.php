<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Botones y Eventos</title>
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
                    <p>Botones y Eventos</p>
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
                <h1><i class="fas fa-mouse-pointer me-2"></i>Botones y Eventos</h1>
                <p class="lead">Aprende a hacer que una pagina responda cuando el usuario presiona, escribe o interactua.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-bell me-2"></i>Un evento es algo que ocurre</h2>
                <p>En una pagina web pasan muchas cosas: un usuario hace clic, escribe en un campo, mueve el mouse o carga la pagina. JavaScript puede escuchar esas acciones y responder con codigo.</p>
                <p>Los botones son una forma excelente de aprender eventos porque el resultado se entiende rapido: presiono algo y la pagina cambia. Pequeño clic, pequeño hechizo digital.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-hand-pointer me-2"></i>Escuchar un clic</h2>
                <p>Primero buscamos el boton con JavaScript. Luego usamos <code>addEventListener</code> para decir que debe pasar cuando ocurra el evento <code>click</code>.</p>
                <div class="code-block">
                    <span class="tag">&lt;button id="boton"&gt;</span>Presioname<span class="tag">&lt;/button&gt;</span><br><br>
                    <span class="selector">const</span> boton = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'boton'</span>);<br><br>
                    boton.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;<span class="value">alert</span>(<span class="value">'¡Hiciste clic!'</span>);<br>
                    });
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_boton_evento_click.svg') }}" alt="Boton que dispara un evento click" class="img-fluid doc-image">
                <p class="doc-caption">El evento click permite responder cuando se presiona un boton.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-search me-2"></i>Seleccionar elementos</h2>
                <p>Para cambiar algo, JavaScript primero necesita encontrarlo. Puedes usar <code>getElementById</code> si el elemento tiene un id, o <code>querySelector</code> para buscar con selectores parecidos a CSS.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_seleccionar_elementos.svg') }}" alt="JavaScript seleccionando elementos por id y clase" class="img-fluid doc-image">
                <p class="doc-caption">Seleccionar es apuntar al elemento que quieres controlar.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">const</span> titulo = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'titulo'</span>);<br>
                    <span class="selector">const</span> tarjeta = <span class="property">document</span>.<span class="value">querySelector</span>(<span class="value">'.tarjeta'</span>);
                </div>
                <p class="mt-3"><code>#titulo</code> busca un id. <code>.tarjeta</code> busca una clase.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-exchange-alt me-2"></i>Cambiar texto, estilos y clases</h2>
                <p>Cuando un evento ocurre, puedes cambiar el contenido de la pagina. Por ejemplo, actualizar un mensaje, modificar un color o activar una clase CSS.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">const</span> mensaje = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'mensaje'</span>);<br><br>
                    mensaje.<span class="property">textContent</span> = <span class="value">'Mision completada'</span>;<br>
                    mensaje.<span class="property">style</span>.<span class="property">color</span> = <span class="value">'#00ff9d'</span>;<br>
                    mensaje.<span class="property">classList</span>.<span class="value">add</span>(<span class="value">'brillante'</span>);
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_cambiar_clases.svg') }}" alt="JavaScript cambiando texto estilo y clase" class="img-fluid doc-image">
                <p class="doc-caption">Un evento puede cambiar varias partes de la interfaz.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-keyboard me-2"></i>Eventos de teclado y campos</h2>
                <p>No todo es clic. Tambien puedes reaccionar cuando el usuario escribe. El evento <code>input</code> ocurre cuando cambia el contenido de un campo.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/js_evento_input.svg') }}" alt="Campo de texto que actualiza un mensaje con evento input" class="img-fluid doc-image">
                <p class="doc-caption">Input sirve para responder mientras el usuario escribe.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">const</span> nombre = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'nombre'</span>);<br>
                    <span class="selector">const</span> saludo = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'saludo'</span>);<br><br>
                    nombre.<span class="value">addEventListener</span>(<span class="value">'input'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;saludo.<span class="property">textContent</span> = <span class="value">'Hola ' + nombre.value</span>;<br>
                    });
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-toggle-on me-2"></i>Alternar estados con toggle</h2>
                <p><code>classList.toggle()</code> agrega una clase si no existe, y la quita si ya existe. Es muy util para abrir menus, activar modo oscuro o marcar tarjetas seleccionadas.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    boton.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;tarjeta.<span class="property">classList</span>.<span class="value">toggle</span>(<span class="value">'activa'</span>);<br>
                    });
                </div>
                <p class="mt-3">Toggle es como un interruptor: prende y apaga una clase.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/js_toggle_estado.svg') }}" alt="Toggle cambiando una tarjeta de apagada a activa" class="img-fluid doc-image">
                <p class="doc-caption">Muchos menus y tarjetas interactivas funcionan con clases que se alternan.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive"><table class="table table-dark table-striped"><thead><tr><th>Concepto</th><th>Que significa</th><th>Ejemplo</th></tr></thead><tbody>
                    <tr><td>Evento</td><td>Accion que ocurre en la pagina</td><td><code>click</code>, <code>input</code></td></tr>
                    <tr><td><code>addEventListener</code></td><td>Escucha un evento</td><td><code>boton.addEventListener('click', ...)</code></td></tr>
                    <tr><td><code>querySelector</code></td><td>Busca un elemento con selector CSS</td><td><code>document.querySelector('.card')</code></td></tr>
                    <tr><td><code>classList.add</code></td><td>Agrega una clase CSS</td><td><code>card.classList.add('activa')</code></td></tr>
                    <tr><td><code>classList.toggle</code></td><td>Activa o desactiva una clase</td><td><code>menu.classList.toggle('abierto')</code></td></tr>
                </tbody></table></div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea una tarjeta que cambie de estado cuando el usuario presione un boton.</p>
                <div class="code-block">
                    <span class="tag">&lt;div id="tarjeta"&gt;</span>Modo normal<span class="tag">&lt;/div&gt;</span><br>
                    <span class="tag">&lt;button id="cambiar"&gt;</span>Cambiar<span class="tag">&lt;/button&gt;</span><br><br>
                    <span class="tag">&lt;script&gt;</span><br>
                    &nbsp;&nbsp;<span class="selector">const</span> tarjeta = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'tarjeta'</span>);<br>
                    &nbsp;&nbsp;<span class="selector">const</span> cambiar = <span class="property">document</span>.<span class="value">getElementById</span>(<span class="value">'cambiar'</span>);<br><br>
                    &nbsp;&nbsp;cambiar.<span class="value">addEventListener</span>(<span class="value">'click'</span>, <span class="selector">function</span>() {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;tarjeta.<span class="property">classList</span>.<span class="value">toggle</span>(<span class="value">'activa'</span>);<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;tarjeta.<span class="property">textContent</span> = <span class="value">'Estado cambiado'</span>;<br>
                    &nbsp;&nbsp;});<br>
                    <span class="tag">&lt;/script&gt;</span>
                </div>
            </div>

            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')"><i class="fas fa-arrow-left me-2"></i>Volver a Documentacion</button>
                <button class="btn btn-lg btn-success" id="testButton" onclick="checkTestAvailability()"><i class="fas fa-check-circle me-2"></i>Hacer un Test</button>
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
            fetch('{{ route("test.check", "js3") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "js3") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "js3") }}';
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






