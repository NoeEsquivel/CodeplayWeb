<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Menu de VideoJuego</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --warning-color: #ffd166;
            --danger-color: #ff4d6d;
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
            background-color: rgba(0, 0, 0, 0.68);
            z-index: -1;
        }

        .navbar-custom {
            background: rgba(0, 0, 0, 0.85) !important;
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
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
        }

        .book {
            width: 200px;
            height: 150px;
            perspective: 1000px;
            position: relative;
        }

        .book-cover {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: 4px solid var(--accent-color);
            border-radius: 8px;
            position: absolute;
            transform-origin: left center;
            animation: openBook 3s ease-in-out forwards;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--accent-color);
            box-shadow: 0 0 30px rgba(0, 255, 157, 0.5);
        }

        .book-pages {
            width: 95%;
            height: 90%;
            background: #f8f9fa;
            border-radius: 5px;
            position: absolute;
            top: 5%;
            left: 2.5%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 2rem;
        }

        @keyframes openBook {
            0% { transform: rotateY(0deg); }
            50% { transform: rotateY(-120deg); }
            100% { transform: rotateY(-180deg); opacity: 0; }
        }

        .loading-text {
            margin-top: 30px;
            font-family: 'Press Start 2P', cursive;
            color: var(--accent-color);
            font-size: 1rem;
            animation: pulse 1.5s infinite;
        }

        .doc-container {
            display: none;
            padding: 100px 0 50px;
        }

        .doc-card {
            background: rgba(0, 0, 0, 0.82);
            border: 2px solid var(--accent-color);
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 0 25px rgba(0, 255, 157, 0.25);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .doc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 35px rgba(0, 255, 157, 0.4);
        }

        .doc-title {
            font-family: 'Press Start 2P', cursive;
            color: var(--accent-color);
            text-shadow: 2px 2px 0 #000;
            margin-bottom: 25px;
            text-align: center;
            font-size: 2rem;
            line-height: 1.5;
        }

        .doc-subtitle {
            color: var(--accent-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin: 30px 0 20px;
            font-family: 'Press Start 2P', cursive;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .lead-box {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.28), rgba(37, 117, 252, 0.28));
            border: 1px solid rgba(0, 255, 157, 0.6);
            border-radius: 18px;
            padding: 24px;
            font-size: 1.08rem;
        }

        .step-card {
            height: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 16px;
            padding: 22px;
        }

        .step-number {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-color);
            color: #05140d;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .example-box {
            background: rgba(0, 255, 157, 0.1);
            border-left: 4px solid var(--accent-color);
            padding: 18px;
            margin: 18px 0;
            border-radius: 0 12px 12px 0;
        }

        .warning-box {
            background: rgba(255, 209, 102, 0.12);
            border-left: 4px solid var(--warning-color);
            padding: 18px;
            margin: 18px 0;
            border-radius: 0 12px 12px 0;
        }

        .danger-box {
            background: rgba(255, 77, 109, 0.12);
            border-left: 4px solid var(--danger-color);
            padding: 18px;
            margin: 18px 0;
            border-radius: 0 12px 12px 0;
        }

        .tool-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(0, 255, 157, 0.32);
            border-radius: 16px;
            padding: 20px;
            height: 100%;
        }

        .tool-card i {
            color: var(--accent-color);
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 14px;
        }

        .comparison-table th,
        .comparison-table td {
            border: 1px solid rgba(255, 255, 255, 0.16);
            padding: 14px;
            vertical-align: top;
        }

        .comparison-table th {
            background: rgba(0, 255, 157, 0.18);
            color: var(--accent-color);
        }

        .comparison-table tr:nth-child(even) td {
            background: rgba(255, 255, 255, 0.04);
        }

        .code-block {
            background: #071018;
            color: #e6edf3;
            border: 1px solid rgba(0, 255, 157, 0.35);
            border-radius: 14px;
            padding: 20px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.92rem;
            line-height: 1.55;
            max-height: 720px;
        }

        .code-block code {
            color: #e6edf3;
            white-space: pre;
        }

        .tag-badge {
            display: inline-block;
            background: rgba(0, 255, 157, 0.14);
            border: 1px solid rgba(0, 255, 157, 0.45);
            color: var(--accent-color);
            border-radius: 999px;
            padding: 7px 12px;
            margin: 4px;
            font-weight: 700;
        }

        .btn-success {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: #05140d;
            font-weight: bold;
        }

        .btn-success:hover {
            background: #00cc7d;
            border-color: #00cc7d;
            color: #05140d;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .doc-title { font-size: 1.4rem; }
            .game-title { font-size: 1rem; }
            .doc-card { padding: 22px; }
        }
    </style>
</head>
<body>
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-pages">
                <i class="fas fa-gamepad"></i>
            </div>
            <div class="book-cover">
                <i class="fas fa-book-open"></i>
            </div>
        </div>
        <div class="loading-text">Cargando proyecto...</div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <button class="btn btn-home me-3" onclick="navigateWithAnimation('{{ route('inicio.alumno') }}')">
                <i class="fas fa-home"></i>
            </button>
            <div class="navbar-brand game-title">CODEPLAY</div>
            <div class="player-name-display ms-auto">
                <i class="fas fa-user me-2"></i>
                <span>{{ Auth::user()->name ?? 'Jugador' }}</span>
            </div>
        </div>
    </nav>

    <div class="container doc-container" id="docContent">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="doc-card animate__animated animate__fadeInUp">
                    <h1 class="doc-title">Proyecto 19: Menu de VideoJuego</h1>

                    <div class="lead-box">
                        <p class="mb-0">
                            En este proyecto vas a diseñar una pantalla inicial para un videojuego. No importa si el juego todavia no existe: muchos proyectos empiezan por su menu principal, porque ahi se define la primera impresion, el estilo visual y la emocion que queremos provocar en quien juega.
                        </p>
                    </div>

                    <h2 class="doc-subtitle">Antes de escribir codigo: imagina el juego</h2>
                    <p>
                        Un menu de videojuego no es solamente una lista de botones. Es la puerta de entrada al mundo del juego. Antes de abrir el editor, conviene responder tres preguntas sencillas:
                    </p>
                    <div class="row g-3 my-3">
                        <div class="col-md-4">
                            <div class="step-card">
                                <div class="step-number">1</div>
                                <h5>¿De que trata?</h5>
                                <p class="mb-0">Puede ser aventura, carreras, terror, fantasia, deportes, ciencia ficcion o rompecabezas.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-card">
                                <div class="step-number">2</div>
                                <h5>¿Que emocion debe dar?</h5>
                                <p class="mb-0">Puede sentirse divertido, misterioso, rapido, competitivo, tranquilo o peligroso.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="step-card">
                                <div class="step-number">3</div>
                                <h5>¿Que opciones necesita?</h5>
                                <p class="mb-0">Por ejemplo: jugar, instrucciones, personajes, configuracion, creditos o salir.</p>
                            </div>
                        </div>
                    </div>

                    <div class="example-box">
                        <strong>Ejemplo:</strong> si tu juego trata de una nave que viaja por el espacio, el menu podria tener fondo de galaxia, colores morados o azules, letras brillantes y botones con sonido futurista. Si tu juego es de terror, probablemente funcionen mejor colores oscuros, niebla, sombras y musica lenta.
                    </div>

                    <h2 class="doc-subtitle">Partes principales de un menu de videojuego</h2>
                    <div class="table-responsive">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Parte</th>
                                    <th>Para que sirve</th>
                                    <th>Ejemplo en un juego</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Titulo</strong></td>
                                    <td>Presenta el nombre del juego y debe ser facil de recordar.</td>
                                    <td>Galaxy Runner, Dragon Quest, Pixel Race.</td>
                                </tr>
                                <tr>
                                    <td><strong>Fondo</strong></td>
                                    <td>Ayuda a mostrar la tematica antes de leer cualquier texto.</td>
                                    <td>Una ciudad futurista, una selva, una pista de carreras o un castillo.</td>
                                </tr>
                                <tr>
                                    <td><strong>Botones</strong></td>
                                    <td>Permiten elegir que hacer dentro del juego.</td>
                                    <td>Jugar, Instrucciones, Opciones, Creditos.</td>
                                </tr>
                                <tr>
                                    <td><strong>Texto de apoyo</strong></td>
                                    <td>Da una frase corta que explique el objetivo o ambiente del juego.</td>
                                    <td>Sobrevive a la noche, Corre hasta la meta, Explora nuevos mundos.</td>
                                </tr>
                                <tr>
                                    <td><strong>Detalles visuales</strong></td>
                                    <td>Hacen que el menu se sienta vivo y atractivo.</td>
                                    <td>Brillos, bordes, sombras, animaciones, iconos o efectos al pasar el mouse.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="doc-subtitle">Como elegir el estilo del menu</h2>
                    <p>
                        El estilo depende del tipo de juego que quieres crear. La idea no es decorar por decorar, sino tomar decisiones que tengan sentido con el mundo del juego.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="tool-card">
                                <i class="fas fa-rocket"></i>
                                <h5>Ciencia ficcion</h5>
                                <p>Usa colores neon, fondos espaciales, botones con bordes brillantes y tipografias tecnologicas.</p>
                                <span class="tag-badge">Azul</span><span class="tag-badge">Morado</span><span class="tag-badge">Verde neon</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tool-card">
                                <i class="fas fa-hat-wizard"></i>
                                <h5>Fantasia</h5>
                                <p>Funcionan bien los castillos, bosques, pergaminos, colores dorados y letras con estilo antiguo.</p>
                                <span class="tag-badge">Dorado</span><span class="tag-badge">Verde</span><span class="tag-badge">Cafe</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tool-card">
                                <i class="fas fa-ghost"></i>
                                <h5>Terror</h5>
                                <p>Conviene usar fondos oscuros, niebla, rojo, sombras fuertes y botones mas sobrios.</p>
                                <span class="tag-badge">Negro</span><span class="tag-badge">Rojo</span><span class="tag-badge">Gris</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="tool-card">
                                <i class="fas fa-car-side"></i>
                                <h5>Carreras o accion</h5>
                                <p>Puede usar lineas diagonales, colores intensos, sensacion de velocidad y botones grandes.</p>
                                <span class="tag-badge">Rojo</span><span class="tag-badge">Amarillo</span><span class="tag-badge">Naranja</span>
                            </div>
                        </div>
                    </div>

                    <h2 class="doc-subtitle">Herramientas para crearlo desde cero</h2>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="tool-card text-center">
                                <i class="fas fa-sticky-note"></i>
                                <h5>Bloc de notas</h5>
                                <p class="mb-0">Sirve para crear un archivo HTML simple y entender lo basico.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tool-card text-center">
                                <i class="fas fa-code"></i>
                                <h5>Visual Studio Code</h5>
                                <p class="mb-0">Es comodo para escribir, ordenar y revisar codigo.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tool-card text-center">
                                <i class="fas fa-terminal"></i>
                                <h5>Cursor</h5>
                                <p class="mb-0">Ayuda a programar con apoyo de inteligencia artificial.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="tool-card text-center">
                                <i class="fas fa-palette"></i>
                                <h5>Canva o Figma</h5>
                                <p class="mb-0">Pueden servir para imaginar primero la pantalla antes de programarla.</p>
                            </div>
                        </div>
                    </div>

                    <h2 class="doc-subtitle">Herramientas opcionales si no quieres empezar desde cero</h2>
                    <p>
                        Algunas herramientas permiten crear pantallas, paginas o prototipos sin escribir todo manualmente. No reemplazan aprender codigo, pero pueden ayudar a planear ideas y ver resultados rapidamente.
                    </p>
                    <div class="table-responsive">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Herramienta</th>
                                    <th>Para que puede servir</th>
                                    <th>Cuando usarla</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Wix</strong></td>
                                    <td>Crear una pagina promocional para tu videojuego.</td>
                                    <td>Cuando quieres mostrar informacion del juego sin programar todo desde cero.</td>
                                </tr>
                                <tr>
                                    <td><strong>Canva Websites</strong></td>
                                    <td>Diseñar una presentacion visual del juego.</td>
                                    <td>Cuando quieres preparar una idea bonita para exponerla.</td>
                                </tr>
                                <tr>
                                    <td><strong>Figma</strong></td>
                                    <td>Diseñar la interfaz antes de hacer codigo.</td>
                                    <td>Cuando quieres probar colores, botones y pantallas.</td>
                                </tr>
                                <tr>
                                    <td><strong>Scratch</strong></td>
                                    <td>Crear juegos sencillos con bloques.</td>
                                    <td>Cuando quieres practicar logica e interactividad antes de usar codigo escrito.</td>
                                </tr>
                                <tr>
                                    <td><strong>Construct</strong></td>
                                    <td>Crear videojuegos 2D con herramientas visuales.</td>
                                    <td>Cuando quieres hacer un prototipo jugable sin empezar con motores complejos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="doc-subtitle">Guia de trabajo recomendada</h2>
                    <ol>
                        <li><strong>Elige el genero:</strong> aventura, carreras, terror, fantasia, deportes, plataformas o ciencia ficcion.</li>
                        <li><strong>Inventa el nombre:</strong> procura que sea corto, facil de recordar y relacionado con la historia.</li>
                        <li><strong>Define el ambiente:</strong> piensa en colores, fondo, musica imaginaria y tipo de letra.</li>
                        <li><strong>Decide las opciones:</strong> no necesitas muchos botones. Empieza con Jugar, Instrucciones y Creditos.</li>
                        <li><strong>Haz una version simple:</strong> primero que se vea bien; despues podrias agregar sonido, animaciones o cambios de pantalla.</li>
                        <li><strong>Pruebalo:</strong> abre el archivo en el navegador y pregunta si se entiende que tipo de juego seria.</li>
                    </ol>

                    <div class="warning-box">
                        <strong>Consejo:</strong> un menu sencillo pero claro es mejor que uno lleno de botones que nadie entiende. En videojuegos, la primera pantalla debe invitar a jugar, no confundir.
                    </div>

                    <h2 class="doc-subtitle">Errores comunes al crear un menu</h2>
                    <div class="danger-box">
                        <ul class="mb-0">
                            <li>Usar colores que no combinan con la tematica del juego.</li>
                            <li>Poner demasiados botones desde el inicio.</li>
                            <li>Usar texto muy pequeno o dificil de leer.</li>
                            <li>Elegir un fondo tan cargado que no deja ver el menu.</li>
                            <li>Copiar menus de juegos famosos sin intentar una idea propia.</li>
                        </ul>
                    </div>

                    <h2 class="doc-subtitle">Resumen corto de lo aprendido</h2>
                    <p>
                        Un menu de videojuego sirve para presentar la idea del juego y permitir que el jugador elija que hacer. Para construirlo, primero se piensa en el genero y la emocion que queremos transmitir. Luego se eligen colores, fondo, titulo, botones y pequenos detalles visuales. Con HTML y CSS podemos crear una pantalla inicial sencilla que parezca parte de un juego real.
                    </p>

                    <h2 class="doc-subtitle">Codigo completo para copiar y pegar</h2>
                    <p>
                        Copia este codigo en Bloc de notas, guardalo con el nombre <strong>menu-videojuego.html</strong> y abre el archivo en tu navegador. Puedes cambiar el titulo, los textos, colores e imagen de fondo para convertirlo en tu propio menu.
                    </p>
                    <div class="code-block"><pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;es&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;meta name=&quot;viewport&quot; content=&quot;width=device-width, initial-scale=1.0&quot;&gt;
    &lt;title&gt;Menu de Videojuego&lt;/title&gt;
    &lt;style&gt;
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: white;
            background:
                linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.75)),
                url(&quot;https://images.unsplash.com/photo-1614728263952-84ea256f9679?auto=format&amp;fit=crop&amp;w=1400&amp;q=80&quot;);
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu {
            width: 90%;
            max-width: 520px;
            padding: 35px;
            text-align: center;
            border: 3px solid #00ffcc;
            border-radius: 24px;
            background: rgba(5, 8, 25, 0.82);
            box-shadow: 0 0 35px rgba(0, 255, 204, 0.45);
        }

        .logo {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #00ffcc;
            margin-bottom: 15px;
        }

        h1 {
            margin: 10px 0;
            font-size: 42px;
            letter-spacing: 3px;
            color: #00ffcc;
            text-shadow: 0 0 15px #00ffcc;
        }

        .subtitle {
            color: #d7e8ff;
            margin-bottom: 28px;
        }

        .button {
            display: block;
            width: 100%;
            margin: 14px 0;
            padding: 15px;
            border: 2px solid #00ffcc;
            border-radius: 14px;
            background: transparent;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .button:hover {
            background: #00ffcc;
            color: #071018;
            transform: scale(1.04);
        }

        .instructions {
            margin-top: 25px;
            padding: 15px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 14px;
            line-height: 1.6;
        }

        footer {
            margin-top: 22px;
            color: #9fb7c9;
            font-size: 13px;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;main class=&quot;menu&quot;&gt;
        &lt;img class=&quot;logo&quot; src=&quot;https://images.unsplash.com/photo-1605901309584-818e25960a8f?auto=format&amp;fit=crop&amp;w=500&amp;q=80&quot; alt=&quot;Control de videojuego&quot;&gt;

        &lt;h1&gt;GALAXY RUNNER&lt;/h1&gt;
        &lt;p class=&quot;subtitle&quot;&gt;Escapa de los meteoritos y llega al portal final.&lt;/p&gt;

        &lt;button class=&quot;button&quot; onclick=&quot;alert('Aqui empezaria el juego')&quot;&gt;Jugar&lt;/button&gt;
        &lt;button class=&quot;button&quot; onclick=&quot;alert('Usa las flechas para moverte y evita los obstaculos')&quot;&gt;Instrucciones&lt;/button&gt;
        &lt;button class=&quot;button&quot; onclick=&quot;alert('Volumen, dificultad y controles')&quot;&gt;Opciones&lt;/button&gt;
        &lt;button class=&quot;button&quot; onclick=&quot;alert('Creado por estudiantes de CodePlay')&quot;&gt;Creditos&lt;/button&gt;

        &lt;section class=&quot;instructions&quot;&gt;
            &lt;strong&gt;Idea del juego:&lt;/strong&gt;
            eres una nave pequena viajando por el espacio. Tu objetivo es esquivar meteoritos,
            juntar energia y llegar al portal antes de que termine el tiempo.
        &lt;/section&gt;

        &lt;footer&gt;Mi primer menu de videojuego con HTML, CSS y un poco de JavaScript&lt;/footer&gt;
    &lt;/main&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre></div>
                </div>
            </div>

            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Documentacion
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
    </script>
</body>
</html>

