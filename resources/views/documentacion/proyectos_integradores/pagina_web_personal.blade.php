<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Pagina Web Personal</title>
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
    <div class="book-animation" id="bookAnimation">
        <div class="book">
            <div class="book-cover">
                <div class="book-content">
                    <h3>CODEPLAY</h3>
                    <p>Pagina Web Personal</p>
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
                <h1><i class="fas fa-id-card me-2"></i>Pagina Web Personal</h1>
                <p class="lead">Crea una pagina sencilla sobre ti, tus gustos, tus metas o un tema que quieras presentar al mundo.</p>
            </div>

            <div class="col-12">
                <h2><i class="fas fa-lightbulb me-2"></i>Antes de escribir codigo: decide que quieres contar</h2>
                <p>Una pagina web personal no empieza con colores ni etiquetas. Empieza con una idea. Preguntate: ¿que quiero mostrar?, ¿a quien se lo quiero enseñar?, ¿que quiero que la persona recuerde despues de verla?</p>
                <p>Puede ser una pagina sobre ti, tu videojuego favorito, tu mascota, tu banda musical, tu deporte, un proyecto escolar, tu portafolio de dibujos, una idea de negocio o una presentacion para clase.</p>
                <p>Si eliges una tematica clara, despues sera mas facil escoger imagenes, colores, fuentes y secciones. Una pagina de astronomia puede usar azul oscuro y estrellas; una pagina deportiva puede usar colores fuertes; una pagina artistica puede usar fondos suaves y tarjetas visuales.</p>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-map me-2"></i>Paso 1: elige el tipo de pagina</h2>
                <p>Estas son ideas sencillas para empezar. Elige una y evita querer hacerlo todo al mismo tiempo.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Tipo</th><th>Que puede incluir</th><th>Estilo recomendado</th></tr></thead>
                        <tbody>
                            <tr><td>Presentacion personal</td><td>Nombre, gustos, habilidades, metas</td><td>Colores que representen tu personalidad</td></tr>
                            <tr><td>Portafolio</td><td>Dibujos, proyectos, fotografias, tareas</td><td>Diseño limpio para que destaque el trabajo</td></tr>
                            <tr><td>Pagina gamer</td><td>Juego favorito, personajes, ranking, reseña</td><td>Oscuro, neon, botones llamativos</td></tr>
                            <tr><td>Pagina de tema escolar</td><td>Resumen, imagenes, datos importantes</td><td>Ordenado, facil de leer, colores suaves</td></tr>
                            <tr><td>Pagina de hobby</td><td>Musica, deporte, lectura, cocina, robotica</td><td>Visual segun el ambiente del hobby</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-palette me-2"></i>Paso 2: define una tematica visual</h2>
                <p>El diseño debe acompañar el tema. No se trata de decorar por decorar, sino de hacer que la pagina se sienta coherente.</p>
                <ul>
                    <li><strong>Color principal:</strong> el color mas importante de la pagina.</li>
                    <li><strong>Color secundario:</strong> acompaña sin competir.</li>
                    <li><strong>Color de acento:</strong> se usa en botones o detalles especiales.</li>
                    <li><strong>Fuente:</strong> debe ser facil de leer.</li>
                    <li><strong>Imagenes:</strong> deben apoyar el mensaje de la pagina.</li>
                </ul>
                <p>Un buen truco: usa pocos colores. Tres colores bien elegidos se ven mejor que diez colores peleando como jefes finales en pantalla.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-layer-group me-2"></i>Paso 3: organiza las secciones</h2>
                <p>Una pagina personal basica puede tener pocas secciones, pero cada una debe tener un objetivo. No llenes por llenar. Piensa en bloques de informacion.</p>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Seccion</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead>
                        <tbody>
                            <tr><td>Encabezado</td><td>Da la primera impresion</td><td>Nombre, frase corta e imagen principal</td></tr>
                            <tr><td>Sobre mi</td><td>Explica quien eres o de que trata la pagina</td><td>“Me gusta programar, dibujar y aprender robotica”</td></tr>
                            <tr><td>Intereses</td><td>Muestra temas importantes</td><td>Videojuegos, musica, deportes, tecnologia</td></tr>
                            <tr><td>Galeria o tarjetas</td><td>Organiza informacion visual</td><td>Tarjetas de proyectos, hobbies o metas</td></tr>
                            <tr><td>Contacto o cierre</td><td>Invita a seguir explorando</td><td>Mensaje final, correo ficticio o redes simuladas</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-tools me-2"></i>Herramientas para hacerlo desde cero</h2>
                <p>Si quieres aprender como funciona una pagina por dentro, puedes crearla desde cero usando herramientas sencillas:</p>
                <ul>
                    <li><strong>Bloc de notas:</strong> sirve para escribir HTML basico y guardar un archivo <code>.html</code>.</li>
                    <li><strong>Visual Studio Code:</strong> editor muy usado para HTML, CSS y JavaScript.</li>
                    <li><strong>Cursor:</strong> editor de codigo con ayuda de IA para explicar, corregir y mejorar.</li>
                    <li><strong>Navegador:</strong> Chrome, Edge o Firefox sirven para abrir y probar tu pagina.</li>
                </ul>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-magic me-2"></i>Herramientas opcionales sin empezar de cero</h2>
                <p>Tambien existen plataformas donde puedes crear paginas visualmente, sin escribir todo el codigo desde cero. Son utiles para prototipos, presentaciones o sitios rapidos.</p>
                <ul>
                    <li><strong>Wix:</strong> constructor visual con plantillas.</li>
                    <li><strong>Google Sites:</strong> sencillo para paginas escolares o informativas.</li>
                    <li><strong>Canva Websites:</strong> bueno para diseños visuales y paginas de presentacion.</li>
                    <li><strong>WordPress.com:</strong> usado para blogs y paginas con contenido frecuente.</li>
                    <li><strong>GitHub Pages:</strong> permite publicar paginas HTML gratis si ya usas GitHub.</li>
                </ul>
                <p>Usar estas herramientas no es “hacer trampa”. Sirven cuando quieres enfocarte en contenido o diseño. Pero si quieres aprender programacion web, crear una pagina con HTML y CSS te enseña que pasa debajo del capó.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-route me-2"></i>Guia de trabajo recomendada</h2>
                <ol>
                    <li><strong>Elige el tema:</strong> personal, gamer, escolar, artistico, deportivo o tecnologico.</li>
                    <li><strong>Escribe el contenido:</strong> titulo, descripcion, intereses y tres datos importantes.</li>
                    <li><strong>Busca imagenes:</strong> usa imagenes relacionadas con el tema y respeta licencias cuando publiques algo real.</li>
                    <li><strong>Crea la estructura HTML:</strong> secciones, titulos, parrafos, imagenes y botones.</li>
                    <li><strong>Agrega estilo CSS:</strong> colores, fuentes, fondos, tarjetas y espaciado.</li>
                    <li><strong>Prueba en navegador:</strong> abre el archivo y revisa si se lee bien.</li>
                    <li><strong>Mejora:</strong> cambia colores, corrige textos y ajusta secciones.</li>
                </ol>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-check-circle me-2"></i>Consejos para que se vea mejor</h2>
                <ul>
                    <li>Usa titulos cortos y claros.</li>
                    <li>No pongas parrafos enormes; separa ideas.</li>
                    <li>Usa contraste: texto claro sobre fondo oscuro o texto oscuro sobre fondo claro.</li>
                    <li>Deja espacio entre secciones para que la pagina respire.</li>
                    <li>Usa imagenes que realmente ayuden al tema.</li>
                    <li>Revisa que los botones parezcan botones.</li>
                </ul>
            </div>

            <div class="col-md-6 mt-4">
                <h2><i class="fas fa-exclamation-triangle me-2"></i>Errores comunes</h2>
                <ul>
                    <li>Querer poner demasiadas cosas en la primera version.</li>
                    <li>Usar colores con poco contraste.</li>
                    <li>Copiar imagenes sin revisar si cargan.</li>
                    <li>Olvidar cerrar etiquetas HTML.</li>
                    <li>Guardar el archivo como <code>.txt</code> en lugar de <code>.html</code>.</li>
                    <li>Usar nombres con espacios raros para archivos o carpetas.</li>
                </ul>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-flag-checkered me-2"></i>Resumen corto de lo aprendido</h2>
                <p>Aprendiste que una pagina web personal empieza con una idea clara. Luego eliges una tematica, organizas secciones, agregas contenido y aplicas estilo con CSS. Tambien viste que puedes hacerla desde cero con HTML o usar herramientas visuales como Wix, Google Sites, Canva Websites, WordPress.com o GitHub Pages.</p>
                <p>Lo importante no es que la primera pagina sea perfecta. Lo importante es terminar una version, verla en el navegador y mejorarla poco a poco.</p>
            </div>

            <div class="col-12 mt-4">
                <h2><i class="fas fa-copy me-2"></i>Codigo completo para copiar y pegar</h2>
                <p>Copia este codigo en Bloc de notas, guardalo como <code>pagina-personal.html</code> y abre el archivo en tu navegador. Usa imagenes con enlaces web para que funcione sin descargar archivos adicionales.</p>
                <div class="code-block">
<pre class="mb-0 text-white"><code>&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;es&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;meta name=&quot;viewport&quot; content=&quot;width=device-width, initial-scale=1.0&quot;&gt;
    &lt;title&gt;Mi Pagina Web Personal&lt;/title&gt;
    &lt;style&gt;
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: white;
        }

        header {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            background:
                linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.9)),
                url('https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&amp;fit=crop&amp;w=1400&amp;q=80');
            background-size: cover;
            background-position: center;
        }

        header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            color: #00ff9d;
        }

        header p {
            font-size: 20px;
            max-width: 650px;
            margin: 0 auto 25px;
        }

        .boton {
            display: inline-block;
            padding: 12px 24px;
            background: #00ff9d;
            color: #0f172a;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
        }

        section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 45px 20px;
        }

        h2 {
            color: #00ff9d;
            font-size: 32px;
        }

        .tarjetas {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .tarjeta {
            background: #1e293b;
            border: 2px solid #334155;
            border-radius: 18px;
            padding: 20px;
            flex: 1 1 250px;
        }

        .tarjeta img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 14px;
            margin-bottom: 12px;
        }

        .destacado {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
        }

        footer {
            text-align: center;
            padding: 25px;
            background: #020617;
            color: #94a3b8;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header&gt;
        &lt;div&gt;
            &lt;h1&gt;Hola, soy Alex&lt;/h1&gt;
            &lt;p&gt;Esta es mi primera pagina web personal. Me gustan los videojuegos, la tecnologia y aprender cosas nuevas creando proyectos.&lt;/p&gt;
            &lt;a href=&quot;#sobre-mi&quot; class=&quot;boton&quot;&gt;Conocer mas&lt;/a&gt;
        &lt;/div&gt;
    &lt;/header&gt;

    &lt;section id=&quot;sobre-mi&quot;&gt;
        &lt;h2&gt;Sobre mi&lt;/h2&gt;
        &lt;p&gt;Soy estudiante y estoy aprendiendo a crear paginas web con HTML y CSS. Esta pagina muestra algunos de mis intereses y metas.&lt;/p&gt;
    &lt;/section&gt;

    &lt;section&gt;
        &lt;h2&gt;Mis intereses&lt;/h2&gt;
        &lt;div class=&quot;tarjetas&quot;&gt;
            &lt;div class=&quot;tarjeta&quot;&gt;
                &lt;img src=&quot;https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&amp;fit=crop&amp;w=900&amp;q=80&quot; alt=&quot;Videojuegos&quot;&gt;
                &lt;h3&gt;Videojuegos&lt;/h3&gt;
                &lt;p&gt;Me gusta imaginar mundos, reglas, personajes y retos interactivos.&lt;/p&gt;
            &lt;/div&gt;

            &lt;div class=&quot;tarjeta&quot;&gt;
                &lt;img src=&quot;https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&amp;fit=crop&amp;w=900&amp;q=80&quot; alt=&quot;Tecnologia&quot;&gt;
                &lt;h3&gt;Tecnologia&lt;/h3&gt;
                &lt;p&gt;Quiero entender como funcionan las computadoras, los robots y las apps.&lt;/p&gt;
            &lt;/div&gt;

            &lt;div class=&quot;tarjeta&quot;&gt;
                &lt;img src=&quot;https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&amp;fit=crop&amp;w=900&amp;q=80&quot; alt=&quot;Creatividad&quot;&gt;
                &lt;h3&gt;Creatividad&lt;/h3&gt;
                &lt;p&gt;Me gusta crear ideas, dibujar, escribir y convertir pensamientos en proyectos.&lt;/p&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/section&gt;

    &lt;section&gt;
        &lt;div class=&quot;destacado&quot;&gt;
            &lt;h2&gt;Mi meta&lt;/h2&gt;
            &lt;p&gt;Quiero seguir aprendiendo programacion y crear proyectos que otras personas puedan usar, jugar o disfrutar.&lt;/p&gt;
        &lt;/div&gt;
    &lt;/section&gt;

    &lt;footer&gt;
        &lt;p&gt;Creado por Alex - Mi primera pagina web personal&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                </div>
            </div>

            <div class="col-12 text-center mt-4">
                <button class="btn btn-lg btn-success" onclick="navigateWithAnimation('{{ route('alumno.documentacion') }}')">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Documentacion
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
            fetch('{{ route("test.check", "mundo5") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.canTakeTest) {
                        window.location.href = '{{ route("test.show", "mundo5") }}';
                    } else {
                        showTestBlockedModal(data.nextAvailableTime);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '{{ route("test.show", "mundo5") }}';
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













