<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Enlaces e Imagenes</title>
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

        .tag {
            color: #569cd6;
        }

        .attribute {
            color: #9cdcfe;
        }

        .value {
            color: #ce9178;
        }

        .comment {
            color: #6a9955;
        }

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
                    <p>Enlaces e Imagenes</p>
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

            <div class="countdown-timer" id="countdownTimer">
                --:--:--
            </div>

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
                <h1><i class="fas fa-link me-2"></i>Enlaces e Imagenes</h1>
                <p class="lead">Aprende a conectar paginas y mostrar imagenes usando rutas locales y atributos claros</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-question-circle me-2"></i>¿Por que son importantes?</h2>
                <p>Los enlaces convierten una pagina aislada en un sitio navegable. Con la etiqueta <code>&lt;a&gt;</code> puedes llevar al usuario a otra pagina, una seccion, un archivo o un sitio externo.</p>
                <p>Las imagenes ayudan a explicar ideas visuales. Con <code>&lt;img&gt;</code> muestras un archivo grafico y con <code>alt</code> das una descripcion cuando la imagen no carga o cuando alguien usa un lector de pantalla.</p>
                <p>En CodePlay vamos a trabajar con imagenes locales dentro de <code>public/Imagenes</code>, para que el proyecto no dependa de enlaces externos.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-link me-2"></i>Crear un enlace</h2>
                <p>Un enlace se crea con la etiqueta <code>&lt;a&gt;</code>. Su atributo mas importante es <code>href</code>, porque indica el destino al que se ira cuando el usuario haga clic.</p>
                <p>El texto entre <code>&lt;a&gt;</code> y <code>&lt;/a&gt;</code> es lo que aparece en la pantalla. Por eso debe ser claro: mejor <strong>Ver guia de HTML</strong> que <strong>haz clic aqui</strong>.</p>

                <div class="code-block">
                    <span class="tag">&lt;a</span> <span class="attribute">href</span>=<span class="value">"practica.html"</span><span class="tag">&gt;</span>Ver practica<span class="tag">&lt;/a&gt;</span>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_enlaces_basicos.svg') }}"
                     alt="Ejemplo visual de un enlace HTML y el resultado que ve el usuario"
                     class="img-fluid doc-image">
                <p class="doc-caption">El enlace se ve como texto subrayado y al hacer clic abre la direccion indicada en href.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-compass me-2"></i>Atributos utiles: href y target</h2>
                <p><code>href</code> es obligatorio si quieres que el enlace navegue a algun destino. Puede apuntar a una pagina local, una ruta del sitio o una URL completa.</p>
                <p><code>target="_blank"</code> abre el enlace en una nueva pestana. Es util para sitios externos, porque el usuario conserva tu pagina abierta.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_enlaces_atributos.svg') }}"
                     alt="Diagrama de los atributos href y target en un enlace"
                     class="img-fluid doc-image">
                <p class="doc-caption">href marca el destino. target decide si se abre en la misma pestana o en una nueva.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;a</span> <span class="attribute">href</span>=<span class="value">"https://developer.mozilla.org"</span><br>
                    &nbsp;&nbsp;&nbsp;<span class="attribute">target</span>=<span class="value">"_blank"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;Abrir documentacion<br>
                    <span class="tag">&lt;/a&gt;</span>
                </div>
                <p class="mt-3">Cuando uses enlaces externos, procura que el texto explique el destino antes de que el usuario lo abra.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-image me-2"></i>Mostrar imagenes con img</h2>
                <p>La etiqueta <code>&lt;img&gt;</code> no necesita cierre. Usa <code>src</code> para indicar donde esta el archivo y <code>alt</code> para describir lo que aparece en la imagen.</p>
                <p>El atributo <code>alt</code> no es decoracion: ayuda si la imagen no se carga y mejora la accesibilidad de tu pagina.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;img</span><br>
                    &nbsp;&nbsp;<span class="attribute">src</span>=<span class="value">"Imagenes/logo.png"</span><br>
                    &nbsp;&nbsp;<span class="attribute">alt</span>=<span class="value">"Logo de CodePlay"</span><br>
                    <span class="tag">&gt;</span>
                </div>
                <p class="mt-3">En Laravel, dentro de una vista Blade, usamos <code>asset('Imagenes/logo.png')</code> para generar la ruta publica correcta.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_imagen_src_alt.svg') }}"
                     alt="Explicacion visual de los atributos src y alt en una imagen HTML"
                     class="img-fluid doc-image">
                <p class="doc-caption">src apunta al archivo que se dibuja. alt describe la imagen con texto.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-folder-open me-2"></i>Rutas locales y externas</h2>
                <p>Una ruta relativa busca archivos dentro de tu proyecto. Por ejemplo, <code>Imagenes/foto.png</code> significa: entra a la carpeta Imagenes y busca el archivo foto.png.</p>
                <p>Una URL externa empieza con <code>http://</code> o <code>https://</code>. Funciona si tienes internet y si el sitio externo permite cargar esa imagen.</p>
                <p>Para este proyecto es mejor guardar recursos propios en <code>public/Imagenes</code>, porque asi se mantienen junto con el codigo.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_rutas_imagenes.svg') }}"
                     alt="Comparacion visual entre rutas locales y URLs externas para imagenes"
                     class="img-fluid doc-image">
                <p class="doc-caption">Las rutas locales viven dentro del proyecto. Las externas dependen de otro servidor.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="comment">// HTML normal</span><br>
                    <span class="tag">&lt;img</span> <span class="attribute">src</span>=<span class="value">"Imagenes/foto.png"</span> <span class="attribute">alt</span>=<span class="value">"Foto"</span><span class="tag">&gt;</span><br><br>
                    <span class="comment">// En Blade</span><br>
                    <span class="tag">&lt;img</span> <span class="attribute">src</span>=<span class="value">"&#123;&#123; asset('Imagenes/foto.png') &#125;&#125;"</span> <span class="attribute">alt</span>=<span class="value">"Foto"</span><span class="tag">&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-images me-2"></i>Imagen como enlace</h2>
                <p>Tambien puedes poner una imagen dentro de un enlace. Esto hace que la imagen completa sea clicable, como un boton visual o una tarjeta de galeria.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_imagen_enlace.svg') }}"
                     alt="Ejemplo visual de una imagen HTML dentro de un enlace"
                     class="img-fluid doc-image">
                <p class="doc-caption">El enlace envuelve a la imagen, por eso el clic sobre la imagen abre el destino.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;a</span> <span class="attribute">href</span>=<span class="value">"galeria.html"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;img</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="attribute">src</span>=<span class="value">"Imagenes/foto.png"</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="attribute">alt</span>=<span class="value">"Abrir galeria"</span><br>
                    &nbsp;&nbsp;<span class="tag">&gt;</span><br>
                    <span class="tag">&lt;/a&gt;</span>
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
                                <td><code>&lt;a&gt;</code></td>
                                <td>Crea enlaces navegables</td>
                                <td><code>&lt;a href="pagina.html"&gt;Abrir&lt;/a&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>href</code></td>
                                <td>Define el destino del enlace</td>
                                <td><code>href="contacto.html"</code></td>
                            </tr>
                            <tr>
                                <td><code>target="_blank"</code></td>
                                <td>Abre un enlace en nueva pestana</td>
                                <td><code>&lt;a target="_blank"&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;img&gt;</code></td>
                                <td>Muestra una imagen</td>
                                <td><code>&lt;img src="foto.png" alt="Foto"&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>src</code></td>
                                <td>Indica la ruta de la imagen</td>
                                <td><code>src="Imagenes/logo.png"</code></td>
                            </tr>
                            <tr>
                                <td><code>alt</code></td>
                                <td>Describe la imagen con texto</td>
                                <td><code>alt="Logo de CodePlay"</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Practica Rapida</h2>
                <p>Crea una pagina con un titulo, una imagen local, un enlace a otra pagina y una imagen que tambien funcione como enlace.</p>
                <div class="code-block">
                    <span class="tag">&lt;!DOCTYPE html&gt;</span><br>
                    <span class="tag">&lt;html&gt;</span><br>
                    <span class="tag">&lt;head&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;title&gt;</span>Mi galeria<span class="tag">&lt;/title&gt;</span><br>
                    <span class="tag">&lt;/head&gt;</span><br>
                    <span class="tag">&lt;body&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;h1&gt;</span>Mi primera galeria<span class="tag">&lt;/h1&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;img</span> <span class="attribute">src</span>=<span class="value">"Imagenes/foto.png"</span> <span class="attribute">alt</span>=<span class="value">"Foto de practica"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;p&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;a</span> <span class="attribute">href</span>=<span class="value">"contacto.html"</span><span class="tag">&gt;</span>Ir a contacto<span class="tag">&lt;/a&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/p&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;a</span> <span class="attribute">href</span>=<span class="value">"galeria.html"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;img</span> <span class="attribute">src</span>=<span class="value">"Imagenes/miniatura.png"</span> <span class="attribute">alt</span>=<span class="value">"Abrir galeria"</span><span class="tag">&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/a&gt;</span><br>
                    <span class="tag">&lt;/body&gt;</span><br>
                    <span class="tag">&lt;/html&gt;</span>
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
            fetch('{{ route("test.check", "html3") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "html3") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "html3") }}';
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
