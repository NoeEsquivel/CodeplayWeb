<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Colores, Fuentes y Fondos</title>
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
                    <p>Colores, Fuentes y Fondos</p>
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
                <h1><i class="fas fa-palette me-2"></i>Colores, Fuentes y Fondos</h1>
                <p class="lead">Aprende a darle ambiente, personalidad y lectura clara a una pagina web.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-gamepad me-2"></i>Piensa como disenador de niveles</h2>
                <p>Una pagina web tambien tiene atmosfera. Un fondo oscuro puede sentirse como un videojuego, un color verde puede parecer tecnologia y una fuente redondeada puede sentirse amigable.</p>
                <p>En este modulo usaras tres herramientas basicas de CSS: <strong>colores</strong>, <strong>fuentes</strong> y <strong>fondos</strong>. Con ellas puedes convertir una pagina simple en algo con identidad propia.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-fill-drip me-2"></i>Colores en CSS</h2>
                <p>CSS permite escribir colores con nombres en ingles, codigos hexadecimales o valores RGB.</p>
                <div class="code-block">
                    <span class="selector">h1</span> { <span class="property">color</span>: <span class="value">red</span>; }<br>
                    <span class="selector">p</span> { <span class="property">color</span>: <span class="value">#00ff9d</span>; }<br>
                    <span class="selector">button</span> { <span class="property">background-color</span>: <span class="value">rgb(37, 117, 252)</span>; }
                </div>
                <p class="mt-3"><code>color</code> cambia el texto. <code>background-color</code> cambia el fondo de un elemento.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_colores_paleta.svg') }}" alt="Paleta visual con ejemplos de colores CSS" class="img-fluid doc-image">
                <p class="doc-caption">Los colores ayudan a crear emocion, jerarquia y personalidad.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-font me-2"></i>Fuentes: la voz de tu pagina</h2>
                <p>La fuente es el estilo de letra. Algunas fuentes se ven serias, otras modernas y otras parecen de videojuego. Elegir una buena fuente ayuda a que la pagina comunique mejor.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_fuentes_comparacion.svg') }}" alt="Comparacion de distintas fuentes aplicadas a tarjetas web" class="img-fluid doc-image">
                <p class="doc-caption">La misma frase puede sentirse distinta si cambias la fuente.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">body</span> {<br>
                    &nbsp;&nbsp;<span class="property">font-family</span>: <span class="value">Arial, sans-serif</span>;<br>
                    }<br><br>
                    <span class="selector">h1</span> {<br>
                    &nbsp;&nbsp;<span class="property">font-size</span>: <span class="value">36px</span>;<br>
                    &nbsp;&nbsp;<span class="property">font-weight</span>: <span class="value">bold</span>;<br>
                    }
                </div>
                <p class="mt-3"><code>font-family</code> elige el tipo de letra, <code>font-size</code> cambia el tamano y <code>font-weight</code> controla el grosor.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-image me-2"></i>Fondos con color, imagen o degradado</h2>
                <p>El fondo es una de las formas mas rapidas de cambiar el ambiente de una pagina. Puedes usar un color, una imagen o un degradado.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">body</span> {<br>
                    &nbsp;&nbsp;<span class="property">background-color</span>: <span class="value">#111827</span>;<br>
                    }<br><br>
                    <span class="selector">.hero</span> {<br>
                    &nbsp;&nbsp;<span class="property">background</span>: <span class="value">linear-gradient(45deg, #6a11cb, #2575fc)</span>;<br>
                    }<br><br>
                    <span class="selector">.banner</span> {<br>
                    &nbsp;&nbsp;<span class="property">background-image</span>: <span class="value">url('fondo.jpg')</span>;<br>
                    &nbsp;&nbsp;<span class="property">background-size</span>: <span class="value">cover</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_fondos_tipos.svg') }}" alt="Tres tarjetas mostrando fondo de color, degradado e imagen" class="img-fluid doc-image">
                <p class="doc-caption">Un fondo puede ser plano, mezclado o basado en una imagen.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-eye me-2"></i>Contraste: que se pueda leer</h2>
                <p>Un diseno bonito tambien debe ser facil de leer. Si el texto y el fondo se parecen demasiado, el usuario tendra que esforzarse. Una buena regla inicial es usar texto claro sobre fondo oscuro, o texto oscuro sobre fondo claro.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_contraste_legibilidad.svg') }}" alt="Comparacion entre texto con buen contraste y texto dificil de leer" class="img-fluid doc-image">
                <p class="doc-caption">El contraste decide si tu pagina se lee comodamente o se vuelve cansada.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="comment">/* Paleta pequena y ordenada */</span><br>
                    <span class="selector">.tarjeta</span> {<br>
                    &nbsp;&nbsp;<span class="property">background</span>: <span class="value">#111827</span>;<br>
                    &nbsp;&nbsp;<span class="property">color</span>: <span class="value">white</span>;<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">2px solid #00ff9d</span>;<br>
                    }
                </div>
                <p class="mt-3">Para empezar, usa pocos colores: uno principal, uno secundario, uno de acento y un fondo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr><th>Propiedad</th><th>Para que sirve</th><th>Ejemplo</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>color</code></td><td>Cambia el color del texto</td><td><code>color: white;</code></td></tr>
                            <tr><td><code>background-color</code></td><td>Cambia el color de fondo</td><td><code>background-color: #111827;</code></td></tr>
                            <tr><td><code>font-family</code></td><td>Cambia el tipo de letra</td><td><code>font-family: Arial;</code></td></tr>
                            <tr><td><code>font-size</code></td><td>Cambia el tamano del texto</td><td><code>font-size: 24px;</code></td></tr>
                            <tr><td><code>background-image</code></td><td>Coloca una imagen como fondo</td><td><code>background-image: url('fondo.jpg');</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea una tarjeta de perfil con fondo degradado, titulo grande, texto claro y un boton con color de acento.</p>
                <div class="code-block">
                    <span class="selector">.tarjeta</span> {<br>
                    &nbsp;&nbsp;<span class="property">background</span>: <span class="value">linear-gradient(135deg, #6a11cb, #2575fc)</span>;<br>
                    &nbsp;&nbsp;<span class="property">color</span>: <span class="value">white</span>;<br>
                    &nbsp;&nbsp;<span class="property">font-family</span>: <span class="value">Arial, sans-serif</span>;<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">20px</span>;<br>
                    &nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">16px</span>;<br>
                    }<br><br>
                    <span class="selector">.tarjeta button</span> {<br>
                    &nbsp;&nbsp;<span class="property">background-color</span>: <span class="value">#00ff9d</span>;<br>
                    &nbsp;&nbsp;<span class="property">color</span>: <span class="value">#111827</span>;<br>
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
            fetch('{{ route("test.check", "css2") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "css2") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "css2") }}';
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

