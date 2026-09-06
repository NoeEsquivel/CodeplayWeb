<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Espacios, Bordes y Cajas</title>
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
                    <p>Espacios, Bordes y Cajas</p>
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
                <h1><i class="fas fa-box-open me-2"></i>Espacios, Bordes y Cajas</h1>
                <p class="lead">Aprende a controlar cuanto espacio ocupa cada elemento y como se separa de los demas.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-cube me-2"></i>Todo elemento es una caja</h2>
                <p>En CSS, casi todo lo que ves en una pagina puede imaginarse como una caja: un titulo, una imagen, un boton, una tarjeta o un menu.</p>
                <p>El <strong>modelo de caja</strong> ayuda a entender cuatro partes: el contenido, el espacio interno llamado <strong>padding</strong>, el borde llamado <strong>border</strong> y el espacio externo llamado <strong>margin</strong>.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-layer-group me-2"></i>El modelo de caja</h2>
                <p>Piensa en una carta dentro de un sobre. El texto es el contenido, el aire alrededor del texto es padding, la linea del sobre es border y la distancia con otros sobres es margin.</p>
                <div class="code-block">
                    <span class="selector">.tarjeta</span> {<br>
                    &nbsp;&nbsp;<span class="property">width</span>: <span class="value">300px</span>;<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">20px</span>;<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">3px solid #00ff9d</span>;<br>
                    &nbsp;&nbsp;<span class="property">margin</span>: <span class="value">25px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_box_model.svg') }}" alt="Modelo de caja CSS con contenido padding border y margin" class="img-fluid doc-image">
                <p class="doc-caption">Cada capa cambia como se ve y como respira un elemento.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-arrows-alt-h me-2"></i>Margin: espacio por fuera</h2>
                <p><code>margin</code> crea distancia entre un elemento y los elementos que tiene alrededor. Sirve para separar tarjetas, botones, titulos o secciones.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_margin_padding.svg') }}" alt="Comparacion entre margin y padding" class="img-fluid doc-image">
                <p class="doc-caption">Margin empuja desde afuera. Padding da aire por dentro.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">h2</span> {<br>
                    &nbsp;&nbsp;<span class="property">margin-bottom</span>: <span class="value">20px</span>;<br>
                    }<br><br>
                    <span class="selector">.boton</span> {<br>
                    &nbsp;&nbsp;<span class="property">margin-top</span>: <span class="value">15px</span>;<br>
                    }
                </div>
                <p class="mt-3">Puedes usar <code>margin-top</code>, <code>margin-right</code>, <code>margin-bottom</code> y <code>margin-left</code> para controlar cada lado.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-compress-arrows-alt me-2"></i>Padding: espacio por dentro</h2>
                <p><code>padding</code> crea espacio entre el contenido y el borde de la caja. Si un boton se siente apretado, probablemente necesita mas padding.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">button</span> {<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">12px 24px</span>;<br>
                    &nbsp;&nbsp;<span class="property">background-color</span>: <span class="value">#00ff9d</span>;<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">none</span>;<br>
                    }
                </div>
                <p class="mt-3">Cuando escribes dos valores, el primero controla arriba y abajo; el segundo controla izquierda y derecha.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_padding_button.svg') }}" alt="Boton sin padding comparado con boton con padding" class="img-fluid doc-image">
                <p class="doc-caption">El padding hace que un boton se vea mas comodo y facil de tocar.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-border-style me-2"></i>Border: el contorno de la caja</h2>
                <p><code>border</code> dibuja una linea alrededor del elemento. Puedes cambiar su grosor, estilo y color. Con <code>border-radius</code> puedes redondear las esquinas.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_bordes_radius.svg') }}" alt="Tarjetas con bordes rectos y redondeados" class="img-fluid doc-image">
                <p class="doc-caption">Los bordes ayudan a separar elementos y darles estilo visual.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">.avatar</span> {<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">4px solid #2575fc</span>;<br>
                    &nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">50%</span>;<br>
                    }<br><br>
                    <span class="selector">.card</span> {<br>
                    &nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">16px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-ruler-combined me-2"></i>Width, height y box-sizing</h2>
                <p><code>width</code> controla el ancho y <code>height</code> controla la altura. Pero hay un detalle: si agregas padding y border, la caja puede crecer mas de lo esperado.</p>
                <p>Por eso muchos proyectos usan <code>box-sizing: border-box;</code>. Con esa regla, el ancho total incluye contenido, padding y borde. Es como decirle a CSS: “mantente dentro de esta medida”.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">*</span> {<br>
                    &nbsp;&nbsp;<span class="property">box-sizing</span>: <span class="value">border-box</span>;<br>
                    }<br><br>
                    <span class="selector">.caja</span> {<br>
                    &nbsp;&nbsp;<span class="property">width</span>: <span class="value">250px</span>;<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">20px</span>;<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">5px solid white</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_box_sizing.svg') }}" alt="Diferencia visual entre content-box y border-box" class="img-fluid doc-image">
                <p class="doc-caption">Border-box ayuda a que las medidas sean mas faciles de controlar.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Propiedad</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td><code>margin</code></td><td>Espacio externo</td><td><code>margin: 20px;</code></td></tr>
                            <tr><td><code>padding</code></td><td>Espacio interno</td><td><code>padding: 12px;</code></td></tr>
                            <tr><td><code>border</code></td><td>Contorno de la caja</td><td><code>border: 2px solid red;</code></td></tr>
                            <tr><td><code>border-radius</code></td><td>Redondea esquinas</td><td><code>border-radius: 16px;</code></td></tr>
                            <tr><td><code>width</code> / <code>height</code></td><td>Ancho y alto</td><td><code>width: 300px;</code></td></tr>
                            <tr><td><code>box-sizing</code></td><td>Controla como se calcula el tamano</td><td><code>box-sizing: border-box;</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea tres tarjetas de logros para un jugador. Cada tarjeta debe tener espacio interno, borde brillante, esquinas redondeadas y separacion entre tarjetas.</p>
                <div class="code-block">
                    <span class="selector">.logro</span> {<br>
                    &nbsp;&nbsp;<span class="property">width</span>: <span class="value">260px</span>;<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">18px</span>;<br>
                    &nbsp;&nbsp;<span class="property">margin</span>: <span class="value">15px</span>;<br>
                    &nbsp;&nbsp;<span class="property">border</span>: <span class="value">2px solid #00ff9d</span>;<br>
                    &nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">18px</span>;<br>
                    &nbsp;&nbsp;<span class="property">box-sizing</span>: <span class="value">border-box</span>;<br>
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
            fetch('{{ route("test.check", "css3") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "css3") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "css3") }}';
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


