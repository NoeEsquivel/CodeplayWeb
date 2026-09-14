<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Frontend, Backend y Bases de Datos</title>
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
                    <p>Frontend, Backend y Bases de Datos</p>
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
                <h1><i class="fas fa-network-wired me-2"></i>Frontend, Backend y Bases de Datos</h1>
                <p class="lead">Aprende que parte ve el usuario, que parte trabaja detras y donde se guarda la informacion.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-store me-2"></i>Una app es como un restaurante</h2>
                <p>Cuando entras a una aplicacion, normalmente solo ves botones, menus, textos e imagenes. Esa es la parte visible. Pero detras tambien hay reglas, validaciones, datos guardados y respuestas que el usuario no ve directamente.</p>
                <p>Una forma simple de imaginarlo es como un restaurante: el salon es el <strong>frontend</strong>, la cocina es el <strong>backend</strong> y la bodega o libreta de pedidos es la <strong>base de datos</strong>.</p>
            </div>

            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-desktop me-2"></i>Frontend: lo que el usuario ve y toca</h2>
                <p>El frontend es la parte visual de una pagina o aplicacion. Incluye botones, colores, formularios, tarjetas, menus, animaciones y todo lo que el usuario puede tocar o leer.</p>
                <p>En la web, el frontend suele usar <strong>HTML</strong> para la estructura, <strong>CSS</strong> para el diseno y <strong>JavaScript</strong> para la interaccion.</p>
                <ul>
                    <li>El boton de iniciar sesion.</li>
                    <li>El menu de documentacion.</li>
                    <li>Las tarjetas de modulos.</li>
                    <li>Los mensajes de error o exito.</li>
                </ul>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/mundo_frontend_pantalla.svg') }}" alt="Frontend como pantalla visible para el usuario" class="img-fluid doc-image">
                <p class="doc-caption">Frontend es la zona visible: donde el usuario mira, lee y hace clic.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-server me-2"></i>Backend: lo que trabaja detras</h2>
                <p>El backend es la parte que funciona en el servidor. No siempre se ve, pero toma decisiones importantes: revisar si una contrasena es correcta, guardar resultados, calcular puntos o decidir a que pantalla enviar al usuario.</p>
                <p>En un proyecto como CodePlayWeb, el backend puede recibir una solicitud del navegador, procesarla y responder con una vista, un mensaje o datos.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/mundo_backend_servidor.svg') }}" alt="Backend como servidor que procesa solicitudes" class="img-fluid doc-image">
                <p class="doc-caption">Backend es el motor silencioso que procesa reglas y respuestas.</p>
            </div>

            <div class="col-md-5">
                <h2 class="mt-3"><i class="fas fa-cogs me-2"></i>Ejemplos de backend</h2>
                <ul>
                    <li>Validar usuario y contrasena.</li>
                    <li>Calcular puntaje de un test.</li>
                    <li>Revisar si un alumno ya hizo un test hoy.</li>
                    <li>Guardar progreso de tutoriales.</li>
                    <li>Decidir si alguien entra como alumno, administrador o master.</li>
                </ul>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-database me-2"></i>Base de datos: donde vive la informacion</h2>
                <p>La base de datos guarda informacion para que no se pierda cuando cierras la pagina. Puede guardar usuarios, preguntas, resultados, puntos, inventario, progreso y muchas otras cosas.</p>
                <p>Sin base de datos, una aplicacion podria mostrar cosas, pero olvidaria casi todo despues de cerrar o recargar.</p>
            </div>

            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Dato</th><th>Ejemplo dentro de CodePlay</th></tr></thead>
                        <tbody>
                            <tr><td>Usuario</td><td>Nombre, rol, puntos y acceso</td></tr>
                            <tr><td>Test</td><td>Modulo, titulo y preguntas</td></tr>
                            <tr><td>Resultado</td><td>Puntaje obtenido por alumno</td></tr>
                            <tr><td>Progreso</td><td>Tutoriales o desafios completados</td></tr>
                            <tr><td>Inventario</td><td>Objetos ganados por el jugador</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/mundo_base_datos_tablas.svg') }}" alt="Base de datos organizada en tablas" class="img-fluid doc-image">
                <p class="doc-caption">Una base de datos organiza informacion en tablas relacionadas.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-route me-2"></i>¿Que pasa cuando haces clic?</h2>
                <p>Cuando presionas un boton, puede ocurrir un recorrido completo. El usuario toca el frontend, el navegador manda una solicitud, el backend la procesa, la base de datos puede guardar o consultar informacion, y finalmente vuelve una respuesta.</p>
            </div>

            <div class="col-12">
                <img src="{{ asset('Imagenes/mundo_flujo_solicitud_respuesta.svg') }}" alt="Flujo de frontend backend base de datos y respuesta" class="img-fluid doc-image">
                <p class="doc-caption">Una accion pequena puede viajar por varias partes antes de mostrar un resultado.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-sign-in-alt me-2"></i>Ejemplo: iniciar sesion</h2>
                <p>Cuando un alumno escribe su nombre y contrasena, el frontend muestra el formulario. El backend recibe esos datos y revisa si coinciden. La base de datos contiene la informacion del usuario. Si todo esta bien, el backend responde enviando al alumno a su pantalla principal.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/mundo_login_flujo.svg') }}" alt="Flujo de inicio de sesion entre frontend backend y base de datos" class="img-fluid doc-image">
                <p class="doc-caption">El login parece una pantalla simple, pero detras hay validacion y consulta de datos.</p>
            </div>

            <div class="col-md-6 mt-4">
                <img src="{{ asset('Imagenes/mundo_test_puntaje_flujo.svg') }}" alt="Flujo de test con calculo de puntaje y guardado en base de datos" class="img-fluid doc-image">
                <p class="doc-caption">Un test combina interfaz, reglas y datos guardados.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-star me-2"></i>Ejemplo: hacer un test</h2>
                <p>El frontend muestra preguntas y opciones. El backend revisa respuestas, calcula puntos y guarda el resultado. La base de datos conserva ese puntaje para que el progreso del alumno no se pierda.</p>
                <p>Este tipo de flujo aparece en plataformas educativas, videojuegos, tiendas en linea y redes sociales.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-balance-scale me-2"></i>Comparacion rapida</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Parte</th><th>Pregunta que responde</th><th>Ejemplo</th><th>Lenguajes comunes</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Frontend</strong></td><td>¿Como se ve y como interactua el usuario?</td><td>Menu, boton, formulario, tarjeta</td><td>HTML, CSS, JavaScript</td></tr>
                            <tr><td><strong>Backend</strong></td><td>¿Que reglas se ejecutan detras?</td><td>Validar login, calcular puntos, decidir acceso</td><td>PHP, Java, Python, JavaScript</td></tr>
                            <tr><td><strong>Base de datos</strong></td><td>¿Donde se guarda y consulta informacion?</td><td>Usuarios, tests, resultados, progreso</td><td>SQL, MySQL, PostgreSQL</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que una aplicacion tiene partes que trabajan juntas. El frontend es lo visible e interactivo. El backend procesa reglas, valida acciones y prepara respuestas. La base de datos guarda informacion para que el progreso, los usuarios y los resultados no se pierdan.</p>
                <p>Cuando entiendes estas tres piezas, empiezas a ver las aplicaciones como sistemas: cada clic tiene un camino y cada respuesta tiene una razon.</p>
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
            fetch('{{ route("test.check", "mundo2") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo2") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo2") }}';
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








