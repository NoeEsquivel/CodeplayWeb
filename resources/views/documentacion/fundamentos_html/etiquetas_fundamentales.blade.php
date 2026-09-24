<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Etiquetas Fundamentales</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <!-- Animate.css -->
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
        
        /* Animación de libro abriéndose */
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
        
        /* Estilos del contenido */
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

        /* Estilos para el modal de test bloqueado */
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
    <!-- Animación inicial de libro -->
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-cover">
                <div class="book-content">
                    <h3>CODEPLAY</h3>
                    <p>Etiquetas Fundamentales</p>
                </div>
            </div>
        </div>
        <p class="text-white mt-4 animate__animated animate__fadeIn animate__delay-1s">Cargando documento...</p>
    </div>

    <!-- Modal para test bloqueado -->
    <div class="test-blocked-modal" id="testBlockedModal">
        <div class="test-blocked-content">
            <div class="emoticon">😴 ⏰ 🚫</div>
            <h3 class="text-warning mb-3">¡Test No Disponible!</h3>
            <p class="lead">Solo puedes realizar este Test cada 24 horas, regresa mañana e intentalo de nuevo 😊🎮</p>
            
            <div class="countdown-timer" id="countdownTimer">
                --:--:--
            </div>
            
            <p class="text-muted">Tiempo restante para poder realizar el test nuevamente</p>
            
            <button class="btn btn-success mt-3" onclick="closeTestBlockedModal()">
                <i class="fas fa-check me-2"></i>Entendido
            </button>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <!-- Botón de Inicio -->
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            
            <!-- Título Centrado -->
            <span class="game-title">CODEPLAY</span>
            
            <!-- Nombre del jugador - ACTUALIZADO -->
            <div class="ms-auto">
                <div class="player-name-display">
                    <i class="fas fa-user me-2"></i>
                    <span id="playerNameDisplay">{{ session('usuario_nombre') ?? 'Usuario' }}</span>
                    <span class="badge bg-success ms-2">Alumno</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido del documento -->
    <div class="container doc-container animate__animated animate__fadeIn" id="docContent" style="display: none;">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1><i class="fas fa-tags me-2"></i>Etiquetas Fundamentales</h1>
                <p class="lead">h1, p, div, span y más: los bloques con los que se construye casi toda página web</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-question-circle me-2"></i>¿Para qué sirven estas etiquetas?</h2>
                <p>En el módulo anterior viste la estructura general de un documento HTML (<code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, <code>&lt;body&gt;</code>). Aquí entramos al <strong>contenido visible</strong>: lo que el usuario lee dentro del <code>&lt;body&gt;</code>.</p>
                <p>Una etiqueta le dice al navegador <em>qué tipo de contenido</em> es cada parte. No es lo mismo un título que un párrafo, ni un recuadro de sección que una palabra destacada. Si usas la etiqueta correcta, la página se entiende mejor, se puede estilizar con CSS y también es más accesible (lectores de pantalla, SEO, etc.).</p>
                <p>La mayoría de estas etiquetas tienen <strong>apertura y cierre</strong>: <code>&lt;p&gt;texto&lt;/p&gt;</code>. Unas pocas van solas, como <code>&lt;br&gt;</code> y <code>&lt;hr&gt;</code>, porque no envuelven contenido.</p>
            </div>
            
            <div class="col-md-6">
                <h2 class="mt-3"><i class="fas fa-heading me-2"></i>Encabezados y párrafos</h2>
                <p>Los encabezados van de <code>&lt;h1&gt;</code> (el más importante) a <code>&lt;h6&gt;</code> (el menos importante). Marcan la jerarquía del documento, como los capítulos de un libro: el <code>h1</code> es el título de la obra y los demás, los apartados.</p>
                <p>El texto normal se escribe con <code>&lt;p&gt;</code>. Cada párrafo es una idea. No juntes varios temas en un solo <code>p</code> ni uses <code>&lt;br&gt;</code> para “inventar” párrafos: el navegador ya deja espacio entre un <code>p</code> y otro.</p>
                
                <h4><i class="fas fa-lightbulb me-2"></i>Reglas rápidas:</h4>
                <ul>
                    <li>Usa <strong>un solo h1</strong> por página, como título principal.</li>
                    <li>Sigue el orden: <strong>h2, luego h3</strong>… no saltes de h1 a h5.</li>
                    <li>El tamaño visual se puede cambiar con CSS; el número de la etiqueta indica <strong>importancia</strong>, no “qué tan grande se ve”.</li>
                    <li>Nunca uses un encabezado solo porque quieres letra grande: para eso está el CSS.</li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_encabezados.svg') }}"
                     alt="Vista de encabezados h1 a h6 y un párrafo tal como se ven en el navegador"
                     class="img-fluid doc-image">
                <p class="doc-caption">A la derecha de cada título aparece la etiqueta que lo genera. Observa cómo el tamaño baja de h1 a h6 y cómo el párrafo se lee como texto corrido.</p>
                
                <div class="code-block">
                    <span class="tag">&lt;h1&gt;</span>Título principal<span class="tag">&lt;/h1&gt;</span><br>
                    <span class="tag">&lt;h2&gt;</span>Subtítulo de sección<span class="tag">&lt;/h2&gt;</span><br>
                    <span class="tag">&lt;p&gt;</span>Este es un párrafo con la idea principal.<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;p&gt;</span>Este es otro párrafo, con una idea distinta.<span class="tag">&lt;/p&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-square me-2"></i>div y span: bloque vs. en línea</h2>
                <p><code>&lt;div&gt;</code> (division) agrupa varios elementos en una <strong>caja de bloque</strong>: ocupa todo el ancho y empuja lo que sigue hacia abajo. Sirve para armar secciones (cabecera, menú, tarjeta, pie).</p>
                <p><code>&lt;span&gt;</code> agrupa un <strong>trozo de texto en línea</strong>: no rompe el párrafo. Sirve para marcar una palabra, un nombre o un dato al que luego le pondrás color o negrita con CSS.</p>
                <p>Ninguna de las dos tiene significado semántico por sí sola (no dicen “esto es un título” ni “esto es un artículo”). Son contenedores genéricos. Úsalas cuando no exista una etiqueta más específica.</p>
            </div>

            <div class="col-md-6">
                <div class="code-block">
                    <span class="tag">&lt;div&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;h2&gt;</span>Sección de bienvenida<span class="tag">&lt;/h2&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;p&gt;</span>Hola, soy <span class="tag">&lt;span&gt;</span>CodePlay<span class="tag">&lt;/span&gt;</span>.<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;/div&gt;</span>
                </div>
                <p class="mt-3">En este ejemplo el <code>div</code> envuelve toda la sección. El <code>span</code> solo envuelve la palabra “CodePlay”, que sigue en la misma frase.</p>
            </div>

            <div class="col-md-6">
                <img src="{{ asset('Imagenes/html_div_span.svg') }}"
                     alt="Comparación visual: varios div apilados como cajas y un span resaltando una palabra dentro de un párrafo"
                     class="img-fluid doc-image">
                <p class="doc-caption">Izquierda: cada div es una caja completa. Derecha: el span destaca “Alex” sin sacar esa palabra del párrafo.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-list me-2"></i>Listas: ul, ol y li</h2>
                <p>Cuando tienes varios elementos del mismo tipo, no los separes con guiones a mano: usa una lista. El navegador pone viñetas o números y el contenido queda agrupado.</p>
                <ul>
                    <li><code>&lt;ul&gt;</code> (unordered list): lista <strong>sin orden</strong>. Menú de temas, ingredientes, características.</li>
                    <li><code>&lt;ol&gt;</code> (ordered list): lista <strong>numerada</strong>. Pasos de una receta, ranking, instrucciones.</li>
                    <li><code>&lt;li&gt;</code> (list item): cada renglón. Siempre va <strong>dentro</strong> de un <code>ul</code> o un <code>ol</code>.</li>
                </ul>
                <p>Puedes anidar listas: un <code>ul</code> dentro de un <code>li</code> crea un subapartado.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_listas.svg') }}"
                     alt="Dos columnas: lista con viñetas de lenguajes y lista numerada con pasos, cada una con su código HTML"
                     class="img-fluid doc-image">
                <p class="doc-caption">El resultado en el navegador (arriba) y el código que lo produce (abajo) para ul y ol.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;h3&gt;</span>Temas<span class="tag">&lt;/h3&gt;</span><br>
                    <span class="tag">&lt;ul&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>HTML<span class="tag">&lt;/li&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>CSS<span class="tag">&lt;/li&gt;</span><br>
                    <span class="tag">&lt;/ul&gt;</span><br><br>
                    <span class="tag">&lt;h3&gt;</span>Pasos<span class="tag">&lt;/h3&gt;</span><br>
                    <span class="tag">&lt;ol&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>Abrir el editor<span class="tag">&lt;/li&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>Escribir el HTML<span class="tag">&lt;/li&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>Guardar como .html<span class="tag">&lt;/li&gt;</span><br>
                    <span class="tag">&lt;/ol&gt;</span>
                </div>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-italic me-2"></i>Énfasis y saltos: strong, em, br y hr</h2>
                <p><code>&lt;strong&gt;</code> marca algo <strong>importante</strong> (el navegador lo muestra en negrita). <code>&lt;em&gt;</code> marca <em>énfasis</em> (cursiva). No las uses solo por estética: indican significado. Si solo quieres un estilo visual, más adelante lo harás con CSS.</p>
                <p><code>&lt;br&gt;</code> inserta un salto de línea dentro del mismo bloque (útil en una dirección o un poema). <code>&lt;hr&gt;</code> dibuja una línea para separar temas. Ambas son etiquetas vacías: no tienen cierre.</p>
            </div>

            <div class="col-md-7">
                <img src="{{ asset('Imagenes/html_formato_texto.svg') }}"
                     alt="Ejemplos visuales de strong en negrita, em en cursiva, saltos br en una dirección y una línea hr"
                     class="img-fluid doc-image">
                <p class="doc-caption">Así se ve cada etiqueta en la página: negrita, cursiva, varias líneas con br y un separador hr.</p>
            </div>

            <div class="col-md-5">
                <div class="code-block">
                    <span class="tag">&lt;p&gt;</span>Lee esto con <span class="tag">&lt;strong&gt;</span>atención<span class="tag">&lt;/strong&gt;</span>.<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;p&gt;</span>Quiero <span class="tag">&lt;em&gt;</span>enfatizar<span class="tag">&lt;/em&gt;</span> esta idea.<span class="tag">&lt;/p&gt;</span><br><br>
                    <span class="tag">&lt;p&gt;</span>Calle 123<span class="tag">&lt;br&gt;</span><br>
                    Ciudad CodePlay<span class="tag">&lt;/p&gt;</span><br>
                    <span class="tag">&lt;hr&gt;</span>
                </div>
            </div>
            
            <div class="col-12 mt-4">
                <h2><i class="fas fa-tags me-2"></i>Resumen de etiquetas</h2>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Etiqueta</th>
                                <th>Cuándo usarla</th>
                                <th>Ejemplo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>&lt;h1&gt; a &lt;h6&gt;</code></td>
                                <td>Títulos según jerarquía (h1 = el de la página)</td>
                                <td><code>&lt;h1&gt;Título principal&lt;/h1&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;p&gt;</code></td>
                                <td>Un párrafo = una idea</td>
                                <td><code>&lt;p&gt;Texto aquí&lt;/p&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;div&gt;</code></td>
                                <td>Agrupar una sección (bloque completo)</td>
                                <td><code>&lt;div&gt;Sección&lt;/div&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;span&gt;</code></td>
                                <td>Marcar una palabra o frase dentro del texto</td>
                                <td><code>&lt;span&gt;palabra&lt;/span&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;ul&gt; / &lt;ol&gt; / &lt;li&gt;</code></td>
                                <td>Listas: viñetas si el orden no importa, números si sí</td>
                                <td><code>&lt;ul&gt;&lt;li&gt;Ítem&lt;/li&gt;&lt;/ul&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;strong&gt; / &lt;em&gt;</code></td>
                                <td>Importancia o énfasis, no solo “se ve distinto”</td>
                                <td><code>&lt;strong&gt;Importante&lt;/strong&gt;</code></td>
                            </tr>
                            <tr>
                                <td><code>&lt;br&gt; / &lt;hr&gt;</code></td>
                                <td>Salto de línea puntual o línea que separa bloques</td>
                                <td><code>&lt;hr&gt;</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-12 mt-4">
                <h2><i class="fas fa-play-circle me-2"></i>Práctica Rápida</h2>
                <p>Junta todo en una sola página: un contenedor <code>div</code>, título, párrafos, un nombre con <code>span</code>, una lista y un poco de énfasis. Ábrela en el navegador y comprueba que cada etiqueta se vea como en las imágenes de este módulo.</p>
                <div class="code-block">
                    <span class="tag">&lt;!DOCTYPE html&gt;</span><br>
                    <span class="tag">&lt;html&gt;</span><br>
                    <span class="tag">&lt;head&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;title&gt;</span>Etiquetas Fundamentales<span class="tag">&lt;/title&gt;</span><br>
                    <span class="tag">&lt;/head&gt;</span><br>
                    <span class="tag">&lt;body&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;div&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;h1&gt;</span>Mi perfil<span class="tag">&lt;/h1&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;p&gt;</span>Hola, me llamo <span class="tag">&lt;span&gt;</span>Alex<span class="tag">&lt;/span&gt;</span>.<span class="tag">&lt;/p&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;p&gt;</span>Estoy aprendiendo HTML con <span class="tag">&lt;strong&gt;</span>CodePlay<span class="tag">&lt;/strong&gt;</span>.<span class="tag">&lt;/p&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;h2&gt;</span>Lenguajes que estudio<span class="tag">&lt;/h2&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;ul&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>HTML<span class="tag">&lt;/li&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;li&gt;</span>CSS<span class="tag">&lt;/li&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;/ul&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;hr&gt;</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="tag">&lt;p&gt;</span><span class="tag">&lt;em&gt;</span>Siguiente paso:<span class="tag">&lt;/em&gt;</span> practicar cada día.<span class="tag">&lt;/p&gt;</span><br>
                    &nbsp;&nbsp;<span class="tag">&lt;/div&gt;</span><br>
                    <span class="tag">&lt;/body&gt;</span><br>
                    <span class="tag">&lt;/html&gt;</span>
                </div>
            </div>
            
            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Documentación
                </button>
                <button class="btn btn-lg btn-success" id="testButton" onclick="checkTestAvailability()">
                    <i class="fas fa-check-circle me-2"></i>Hacer un Test
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        // Ocultar animación y mostrar contenido después de 3 segundos
        setTimeout(() => {
            document.getElementById('bookAnimation').style.display = 'none';
            document.getElementById('docContent').style.display = 'block';
        }, 3000);
        
        // Función para navegar con animación
        function navigateWithAnimation(url) {
            document.getElementById('bookAnimation').style.display = 'flex';
            document.getElementById('docContent').style.display = 'none';
            
            setTimeout(() => {
                window.location.href = url;
            }, 1500);
        }

        // Variable global para el temporizador
        let countdownInterval;

        // Función para verificar disponibilidad del test
        function checkTestAvailability() {
            fetch('{{ route("test.check", "html2") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "html2") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "html2") }}';
                });
        }

        // Función para mostrar el modal de test bloqueado
        function showTestBlockedModal(nextAvailableTime) {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'flex';
            
            startCountdown(nextAvailableTime);
        }

        // Función para cerrar el modal
        function closeTestBlockedModal() {
            const modal = document.getElementById('testBlockedModal');
            modal.style.display = 'none';
            
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }

        // Función para iniciar el temporizador
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
                
                const formattedTime = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
                
                countdownElement.innerHTML = formattedTime;
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
