<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Diseños Flexibles</title>
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
                    <p>Diseños Flexibles</p>
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
                <h1><i class="fas fa-th-large me-2"></i>Diseños Flexibles</h1>
                <p class="lead">Aprende a acomodar tarjetas, menus y secciones usando Flexbox.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-map me-2"></i>Ordenar una pagina como un tablero</h2>
                <p>Cuando una pagina tiene varios elementos, necesitamos decidir donde aparece cada cosa: botones en fila, tarjetas en columnas, un menu centrado o un inventario que se acomoda solo.</p>
                <p><strong>Flexbox</strong> es una herramienta de CSS para organizar elementos de forma flexible. Es como decirle a una caja: “acomoda a tus hijos en fila, con espacio entre ellos y centrados”.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-boxes me-2"></i>Contenedor e hijos</h2>
                <p>Para usar Flexbox necesitas un contenedor padre y varios elementos hijos. Al padre le pones <code>display: flex;</code> y desde ahi controlas como se ordenan los hijos.</p>
                <div class="code-block">
                    <span class="selector">.inventario</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">gap</span>: <span class="value">16px</span>;<br>
                    }<br><br>
                    <span class="selector">.item</span> {<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">20px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_flex_contenedor_hijos.svg') }}" alt="Contenedor Flexbox con elementos hijos acomodados" class="img-fluid doc-image">
                <p class="doc-caption">El contenedor manda. Los hijos se acomodan dentro de el.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-arrows-alt-h me-2"></i>flex-direction: fila o columna</h2>
                <p><code>flex-direction</code> decide la direccion principal. Con <code>row</code> los elementos van de izquierda a derecha. Con <code>column</code> van de arriba hacia abajo.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_flex_direction.svg') }}" alt="Comparacion entre flex-direction row y column" class="img-fluid doc-image">
                <p class="doc-caption">Row sirve para menus horizontales. Column sirve para listas verticales.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">.menu</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">flex-direction</span>: <span class="value">row</span>;<br>
                    }<br><br>
                    <span class="selector">.panel</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">flex-direction</span>: <span class="value">column</span>;<br>
                    }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-align-center me-2"></i>justify-content y align-items</h2>
                <p><code>justify-content</code> acomoda los elementos sobre la direccion principal. <code>align-items</code> los acomoda sobre la direccion cruzada.</p>
                <p>En una fila, <code>justify-content</code> mueve de izquierda a derecha y <code>align-items</code> mueve de arriba a abajo.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">.zona</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">justify-content</span>: <span class="value">center</span>;<br>
                    &nbsp;&nbsp;<span class="property">align-items</span>: <span class="value">center</span>;<br>
                    &nbsp;&nbsp;<span class="property">height</span>: <span class="value">300px</span>;<br>
                    }
                </div>
                <p class="mt-3">Esta combinacion es muy usada para centrar un elemento en una pantalla o tarjeta.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_flex_justify_align.svg') }}" alt="Ejemplo visual de justify-content y align-items en Flexbox" class="img-fluid doc-image">
                <p class="doc-caption">Centrar ya no es magia oscura: Flexbox lo hace bastante directo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-grip-lines me-2"></i>gap: separacion sin sufrir</h2>
                <p>Antes se usaban muchos margenes para separar tarjetas. Con Flexbox puedes usar <code>gap</code>, que crea espacio uniforme entre los hijos del contenedor.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_flex_gap.svg') }}" alt="Tarjetas separadas usando gap en Flexbox" class="img-fluid doc-image">
                <p class="doc-caption">Gap es perfecto para separar botones, tarjetas, iconos o elementos de inventario.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">.tarjetas</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">gap</span>: <span class="value">20px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-sync-alt me-2"></i>flex-wrap: cuando no cabe todo</h2>
                <p>Si hay muchas tarjetas en una fila, pueden salirse de la pantalla. Con <code>flex-wrap: wrap;</code>, los elementos pueden bajar a la siguiente linea cuando ya no caben.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">.galeria</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">flex-wrap</span>: <span class="value">wrap</span>;<br>
                    &nbsp;&nbsp;<span class="property">gap</span>: <span class="value">18px</span>;<br>
                    }<br><br>
                    <span class="selector">.galeria .card</span> {<br>
                    &nbsp;&nbsp;<span class="property">width</span>: <span class="value">180px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_flex_wrap.svg') }}" alt="Flexbox usando wrap para mover tarjetas a otra linea" class="img-fluid doc-image">
                <p class="doc-caption">Wrap ayuda a que un diseno se adapte mejor a pantallas pequenas.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Propiedad</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td><code>display: flex</code></td><td>Activa Flexbox en el contenedor</td><td><code>display: flex;</code></td></tr>
                            <tr><td><code>flex-direction</code></td><td>Define fila o columna</td><td><code>flex-direction: row;</code></td></tr>
                            <tr><td><code>justify-content</code></td><td>Acomoda en la direccion principal</td><td><code>justify-content: center;</code></td></tr>
                            <tr><td><code>align-items</code></td><td>Acomoda en la direccion cruzada</td><td><code>align-items: center;</code></td></tr>
                            <tr><td><code>gap</code></td><td>Separa elementos hijos</td><td><code>gap: 20px;</code></td></tr>
                            <tr><td><code>flex-wrap</code></td><td>Permite saltar a otra linea</td><td><code>flex-wrap: wrap;</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica rapida</h2>
                <p>Crea un menu de videojuego con tres botones centrados y una galeria de personajes que se acomode automaticamente.</p>
                <div class="code-block">
                    <span class="selector">.menu-juego</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">justify-content</span>: <span class="value">center</span>;<br>
                    &nbsp;&nbsp;<span class="property">align-items</span>: <span class="value">center</span>;<br>
                    &nbsp;&nbsp;<span class="property">gap</span>: <span class="value">16px</span>;<br>
                    }<br><br>
                    <span class="selector">.personajes</span> {<br>
                    &nbsp;&nbsp;<span class="property">display</span>: <span class="value">flex</span>;<br>
                    &nbsp;&nbsp;<span class="property">flex-wrap</span>: <span class="value">wrap</span>;<br>
                    &nbsp;&nbsp;<span class="property">gap</span>: <span class="value">20px</span>;<br>
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
            fetch('{{ route("test.check", "css4") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "css4") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "css4") }}';
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



