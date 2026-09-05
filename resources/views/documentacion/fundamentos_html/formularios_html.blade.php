<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Formularios HTML</title>
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
        .attribute { color: #9cdcfe; }
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
                    <p>Formularios HTML</p>
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
                <h1><i class="fas fa-keyboard me-2"></i>Formularios HTML</h1>
                <p class="lead">Los formularios permiten que una pagina reciba respuestas, datos y decisiones del usuario</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-question-circle me-2"></i>¿Para que sirven?</h2>
                <p>Un formulario es la parte de una pagina donde el usuario puede escribir, elegir opciones y enviar informacion. Lo ves en inicios de sesion, registros, buscadores, encuestas, comentarios y juegos de preguntas.</p>
                <p>La etiqueta principal es <code>&lt;form&gt;</code>. Dentro de ella colocamos controles como <code>&lt;input&gt;</code>, <code>&lt;label&gt;</code>, <code>&lt;select&gt;</code>, <code>&lt;textarea&gt;</code> y <code>&lt;button&gt;</code>.</p>
                <p>En este modulo no necesitas procesar datos en servidor todavia. Lo importante es entender como se ve cada parte y para que sirve.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-list-alt me-2"></i>La estructura basica</h2>
                <p><code>&lt;form&gt;</code> agrupa todos los campos. <code>&lt;label&gt;</code> nombra cada campo. <code>&lt;input&gt;</code> recibe datos cortos. <code>&lt;button&gt;</code> permite enviar o activar una accion.</p>
                <div class="code-block">
                    <span class="tag">&lt;form&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;label</span> <span class="attribute">for</span>=<span class="value">"nombre"</span><span class="tag">&gt;</span>Nombre<span class="tag">&lt;/label&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;input</span> <span class="attribute">id</span>=<span class="value">"nombre"</span> <span class="attribute">name</span>=<span class="value">"nombre"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;button&gt;</span>Enviar<span class="tag">&lt;/button&gt;</span><br>
                    <span class="tag">&lt;/form&gt;</span>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_formulario_partes.svg') }}"
                     alt="Partes basicas de un formulario HTML con form, label, input y button"
                     class="img-fluid doc-image">
                <p class="doc-caption">El formulario es la caja completa; dentro viven las etiquetas, campos y botones.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-i-cursor me-2"></i>Inputs: campos para escribir o elegir</h2>
                <p><code>&lt;input&gt;</code> cambia segun su atributo <code>type</code>. No es lo mismo pedir texto que correo, contrasena, edad o una opcion marcada.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_inputs_basicos.svg') }}"
                     alt="Tipos comunes de input como text, email, password, number, radio y checkbox"
                     class="img-fluid doc-image">
                <p class="doc-caption">Cada tipo de input le dice al navegador que clase de respuesta esperas del usuario.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"text"</span><span class="tag">&gt;</span><br>
                    <span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"email"</span><span class="tag">&gt;</span><br>
                    <span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"password"</span><span class="tag">&gt;</span><br>
                    <span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"number"</span><span class="tag">&gt;</span><br>
                    <span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"checkbox"</span><span class="tag">&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-tag me-2"></i>label, id y name</h2>
                <p><code>&lt;label&gt;</code> es el texto que explica que debe escribir el usuario. Si su atributo <code>for</code> coincide con el <code>id</code> del input, al tocar el texto se activa el campo.</p>
                <p><code>name</code> es el nombre del dato cuando el formulario se envia. Piensalo como la etiqueta de una caja: dice que informacion va dentro.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;label</span> <span class="attribute">for</span>=<span class="value">"jugador"</span><span class="tag">&gt;</span>Nombre de jugador<span class="tag">&lt;/label&gt;</span><br>
                    <span class="tag">&lt;input</span><br>
                    &nbsp;&nbsp;<span class="attribute">id</span>=<span class="value">"jugador"</span><br>
                    &nbsp;&nbsp;<span class="attribute">name</span>=<span class="value">"jugador"</span><br>
                    &nbsp;&nbsp;<span class="attribute">type</span>=<span class="value">"text"</span><br>
                    <span class="tag">&gt;</span>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_label_name.svg') }}"
                     alt="Relacion entre label, id y name en un campo de formulario"
                     class="img-fluid doc-image">
                <p class="doc-caption">for e id conectan el texto con el campo. name identifica el dato que viaja.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-clipboard-list me-2"></i>select y textarea</h2>
                <p><code>&lt;select&gt;</code> sirve para elegir una opcion de una lista. Cada opcion vive dentro de una etiqueta <code>&lt;option&gt;</code>.</p>
                <p><code>&lt;textarea&gt;</code> sirve para textos largos, como mensajes, comentarios o respuestas abiertas.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_select_textarea.svg') }}"
                     alt="Ejemplo visual de select y textarea en un formulario"
                     class="img-fluid doc-image">
                <p class="doc-caption">select muestra opciones preparadas. textarea deja escribir una respuesta mas larga.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;select</span> <span class="attribute">name</span>=<span class="value">"nivel"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;option&gt;</span>Principiante<span class="tag">&lt;/option&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;option&gt;</span>Intermedio<span class="tag">&lt;/option&gt;</span><br>
                    <span class="tag">&lt;/select&gt;</span><br><br>
                    <span class="tag">&lt;textarea</span> <span class="attribute">name</span>=<span class="value">"mensaje"</span><span class="tag">&gt;&lt;/textarea&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-paper-plane me-2"></i>action, method y required</h2>
                <p><code>action</code> indica a donde se envian los datos. <code>method</code> indica como se envian. Para empezar, solo recuerda que <code>GET</code> se usa mas para busquedas y <code>POST</code> para enviar informacion.</p>
                <p><code>required</code> hace que un campo sea obligatorio antes de enviar. Es una validacion basica que el navegador puede revisar por ti.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_formulario_envio.svg') }}"
                     alt="Flujo de envio de un formulario desde la pagina hasta el servidor"
                     class="img-fluid doc-image">
                <p class="doc-caption">Al presionar enviar, el formulario manda los datos al destino definido en action.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;form</span> <span class="attribute">action</span>=<span class="value">"/registro"</span> <span class="attribute">method</span>=<span class="value">"POST"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;input</span> <span class="attribute">type</span>=<span class="value">"email"</span> <span class="attribute">name</span>=<span class="value">"correo"</span> <span class="attribute">required</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;button</span> <span class="attribute">type</span>=<span class="value">"submit"</span><span class="tag">&gt;</span>Crear cuenta<span class="tag">&lt;/button&gt;</span><br>
                    <span class="tag">&lt;/form&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-table me-2"></i>Resumen rapido</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Elemento</th>
                                <th>Uso</th>
                                <th>Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>&lt;form&gt;</code></td>
                                <td>Agrupa los campos del formulario</td>
                                <td><code>&lt;form&gt;...&lt;/form&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;label&gt;</code></td>
                                <td>Describe que debe llenar el usuario</td>
                                <td><code>&lt;label for="email"&gt;Correo&lt;/label&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;input&gt;</code></td>
                                <td>Campo para escribir o marcar datos</td>
                                <td><code>&lt;input type="text"&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;select&gt;</code></td>
                                <td>Lista desplegable</td>
                                <td><code>&lt;select&gt;&lt;option&gt;A&lt;/option&gt;&lt;/select&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;textarea&gt;</code></td>
                                <td>Campo para texto largo</td>
                                <td><code>&lt;textarea&gt;&lt;/textarea&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;button&gt;</code></td>
                                <td>Boton para enviar o ejecutar una accion</td>
                                <td><code>&lt;button type="submit"&gt;Enviar&lt;/button&gt;</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica Rapida</h2>
                <p>Crea un formulario de registro para un juego. Pide nombre de jugador, correo, nivel favorito y un mensaje corto.</p>
                <div class="code-block">
                    <span class="tag">&lt;form</span> <span class="attribute">action</span>=<span class="value">"/registro"</span> <span class="attribute">method</span>=<span class="value">"POST"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;label</span> <span class="attribute">for</span>=<span class="value">"jugador"</span><span class="tag">&gt;</span>Jugador<span class="tag">&lt;/label&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;input</span> <span class="attribute">id</span>=<span class="value">"jugador"</span> <span class="attribute">name</span>=<span class="value">"jugador"</span> <span class="attribute">type</span>=<span class="value">"text"</span> <span class="attribute">required</span><span class="tag">&gt;</span><br><br>
                    &nbsp;&nbsp;<span class="tag">&lt;label</span> <span class="attribute">for</span>=<span class="value">"correo"</span><span class="tag">&gt;</span>Correo<span class="tag">&lt;/label&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;input</span> <span class="attribute">id</span>=<span class="value">"correo"</span> <span class="attribute">name</span>=<span class="value">"correo"</span> <span class="attribute">type</span>=<span class="value">"email"</span> <span class="attribute">required</span><span class="tag">&gt;</span><br><br>
                    &nbsp;&nbsp;<span class="tag">&lt;select</span> <span class="attribute">name</span>=<span class="value">"nivel"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;option&gt;</span>Principiante<span class="tag">&lt;/option&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;option&gt;</span>Explorador<span class="tag">&lt;/option&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/select&gt;</span><br><br>
                    &nbsp;&nbsp;<span class="tag">&lt;textarea</span> <span class="attribute">name</span>=<span class="value">"mensaje"</span><span class="tag">&gt;&lt;/textarea&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;button</span> <span class="attribute">type</span>=<span class="value">"submit"</span><span class="tag">&gt;</span>Unirme<span class="tag">&lt;/button&gt;</span><br>
                    <span class="tag">&lt;/form&gt;</span>
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
            fetch('{{ route("test.check", "html4") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "html4") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "html4") }}';
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
