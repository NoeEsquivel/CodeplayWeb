<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Introduccion a CSS</title>
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
                    <p>Introduccion a CSS</p>
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
                <h1><i class="fas fa-paint-brush me-2"></i>Introduccion a CSS</h1>
                <p class="lead">CSS es el lenguaje que le da color, forma y personalidad a una pagina web</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-question-circle me-2"></i>¿Que es CSS?</h2>
                <p>HTML crea la estructura: titulos, parrafos, botones, imagenes y formularios. CSS decide como se ve todo eso: colores, tamanos, fondos, bordes, espacios y posicion.</p>
                <p>Si HTML fuera el esqueleto de una pagina, CSS seria su ropa, su estilo y su energia visual. Con el mismo HTML puedes crear una pagina sencilla, elegante, gamer, escolar o futurista.</p>
                <p>CSS significa <strong>Cascading Style Sheets</strong>, que podemos entender como hojas de estilo en cascada.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-magic me-2"></i>Antes y despues</h2>
                <p>El navegador ya muestra el HTML con un estilo basico. CSS te permite tomar control y decidir como quieres que se vea cada parte.</p>
                <div class="code-block">
                    <span class="selector">h1</span> {<br>
                    &nbsp;&nbsp;<span class="property">color</span>: <span class="value">purple</span>;<br>
                    &nbsp;&nbsp;<span class="property">font-size</span>: <span class="value">36px</span>;<br>
                    }<br><br>
                    <span class="selector">button</span> {<br>
                    &nbsp;&nbsp;<span class="property">background</span>: <span class="value">lime</span>;<br>
                    &nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">20px</span>;<br>
                    }
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_intro_antes_despues.svg') }}"
                     alt="Comparacion de una pagina sin CSS y la misma pagina con CSS"
                     class="img-fluid doc-image">
                <p class="doc-caption">El contenido es el mismo; CSS cambia la presentacion visual.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-code me-2"></i>Como se escribe una regla CSS</h2>
                <p>Una regla CSS tiene tres ideas: el <strong>selector</strong>, la <strong>propiedad</strong> y el <strong>valor</strong>. El selector dice a quien cambias, la propiedad dice que cambias, y el valor dice como quedara.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_intro_regla.svg') }}"
                     alt="Partes de una regla CSS: selector, propiedad y valor"
                     class="img-fluid doc-image">
                <p class="doc-caption">Selector: a quien. Propiedad: que parte. Valor: como se vera.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">p</span> {<br>
                    &nbsp;&nbsp;<span class="property">color</span>: <span class="value">white</span>;<br>
                    &nbsp;&nbsp;<span class="property">background-color</span>: <span class="value">black</span>;<br>
                    &nbsp;&nbsp;<span class="property">padding</span>: <span class="value">10px</span>;<br>
                    }
                </div>
                <p class="mt-3">Esta regla cambia todos los parrafos de la pagina.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-plug me-2"></i>Formas de conectar CSS</h2>
                <p>Para practicar puedes escribir CSS dentro de una etiqueta <code>&lt;style&gt;</code>. Para proyectos mas ordenados, lo mejor es usar un archivo externo, por ejemplo <code>style.css</code>.</p>
                <p>Tambien existe CSS en linea usando el atributo <code>style</code>, pero conviene usarlo solo en casos pequenos.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="comment">// CSS interno</span><br>
                    <span class="tag">&lt;style&gt;</span><br>
                    &nbsp;&nbsp;<span class="selector">body</span> {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">background</span>: <span class="value">black</span>;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">color</span>: <span class="value">white</span>;<br>
                    &nbsp;&nbsp;}<br>
                    <span class="tag">&lt;/style&gt;</span>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_intro_conectar.svg') }}"
                     alt="Tres formas de usar CSS: externo, interno y en linea"
                     class="img-fluid doc-image">
                <p class="doc-caption">Para aprender, style es comodo. Para proyectos, un archivo CSS separado es mas ordenado.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-crosshairs me-2"></i>Selectores basicos</h2>
                <p>Los selectores son como una mira: indican que elementos reciben el estilo. Puedes seleccionar por etiqueta, por clase o por id.</p>
                <ul>
                    <li><code>h1</code> selecciona todos los titulos h1.</li>
                    <li><code>.destacado</code> selecciona elementos con <code>class="destacado"</code>.</li>
                    <li><code>#principal</code> selecciona el elemento con <code>id="principal"</code>.</li>
                </ul>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/css_intro_selectores.svg') }}"
                     alt="Ejemplos de selectores CSS por etiqueta, clase e id"
                     class="img-fluid doc-image">
                <p class="doc-caption">Etiqueta, clase e id son las formas mas comunes de apuntar a elementos.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="selector">h1</span> { <span class="property">color</span>: <span class="value">purple</span>; }<br>
                    <span class="selector">.destacado</span> { <span class="property">border</span>: <span class="value">2px solid blue</span>; }<br>
                    <span class="selector">#principal</span> { <span class="property">font-size</span>: <span class="value">24px</span>; }
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-layer-group me-2"></i>La cascada</h2>
                <p>CSS se llama en cascada porque varias reglas pueden intentar cambiar el mismo elemento. Cuando eso pasa, el navegador decide cual gana usando orden, importancia y especificidad.</p>
                <p>Por ahora recuerda esta idea simple: una clase suele ser mas especifica que una etiqueta, y un id suele ser mas especifico que una clase.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/css_intro_cascada.svg') }}"
                     alt="Ejemplo visual de cascada CSS y especificidad"
                     class="img-fluid doc-image">
                <p class="doc-caption">Si dos reglas compiten, gana la que sea mas especifica o la que aparezca despues en igualdad de condiciones.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="selector">p</span> { <span class="property">color</span>: <span class="value">gray</span>; }<br>
                    <span class="selector">.alerta</span> { <span class="property">color</span>: <span class="value">red</span>; }<br>
                    <span class="selector">#mensaje</span> { <span class="property">color</span>: <span class="value">blue</span>; }
                </div>
                <p class="mt-3">Si un parrafo tiene clase <code>alerta</code> e id <code>mensaje</code>, el color azul del id puede ganar.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Que significa</th>
                                <th>Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CSS</td>
                                <td>Lenguaje para dar estilo a HTML</td>
                                <td><code>color: blue;</code></td>
                            </tr>
                            <tr>
                                <td>Selector</td>
                                <td>Indica que elemento se va a cambiar</td>
                                <td><code>h1</code>, <code>.card</code>, <code>#menu</code></td>
                            </tr>
                            <tr>
                                <td>Propiedad</td>
                                <td>La parte visual que cambia</td>
                                <td><code>color</code>, <code>font-size</code>, <code>background</code></td>
                            </tr>
                            <tr>
                                <td>Valor</td>
                                <td>El resultado que tendra la propiedad</td>
                                <td><code>red</code>, <code>20px</code>, <code>center</code></td>
                            </tr>
                            <tr>
                                <td>Cascada</td>
                                <td>Reglas que compiten y se ordenan por prioridad</td>
                                <td><code>#titulo</code> gana a <code>.titulo</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica Rapida</h2>
                <p>Crea una pagina simple y cambia su estilo con CSS interno: fondo oscuro, titulo colorido, parrafo con espacio y boton redondeado.</p>
                <div class="code-block">
                    <span class="tag">&lt;style&gt;</span><br>
                    &nbsp;&nbsp;<span class="selector">body</span> {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">background</span>: <span class="value">#111827</span>;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">color</span>: <span class="value">white</span>;<br>
                    &nbsp;&nbsp;}<br>
                    &nbsp;&nbsp;<span class="selector">h1</span> { <span class="property">color</span>: <span class="value">#00ff9d</span>; }<br>
                    &nbsp;&nbsp;<span class="selector">p</span> { <span class="property">padding</span>: <span class="value">12px</span>; }<br>
                    &nbsp;&nbsp;<span class="selector">button</span> {<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">border-radius</span>: <span class="value">20px</span>;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="property">background</span>: <span class="value">#6a11cb</span>;<br>
                    &nbsp;&nbsp;}<br>
                    <span class="tag">&lt;/style&gt;</span>
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
            fetch('{{ route("test.check", "css1") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "css1") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "css1") }}';
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
