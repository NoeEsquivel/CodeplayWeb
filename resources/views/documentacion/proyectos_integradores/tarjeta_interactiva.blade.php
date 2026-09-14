<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Tarjeta Interactiva</title>
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
                <i class="fas fa-id-card"></i>
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
                    <h1 class="doc-title">Proyecto 20: Tarjeta Interactiva</h1>

                    <div class="lead-box">
                        <p class="mb-0">En este proyecto vas a crear una tarjeta interactiva: una pequeña ficha visual que puede cambiar cuando el usuario hace clic. Puede representar un personaje, una mascota, un producto, una carta de videojuego o una tarjeta personal. Aqui aprenderas a unir estructura, diseño e interaccion en una sola pieza.</p>
                    </div>

                    <h2 class="doc-subtitle">Antes de escribir codigo: piensa en la tarjeta</h2>
                    <p>Una tarjeta funciona mejor cuando tiene una idea clara. Antes de programarla, decide que va a representar y que cambio ocurrira cuando alguien interactue con ella.</p>
                    <div class="row g-3 my-3">
                        <div class="col-md-4"><div class="step-card"><div class="step-number">1</div><h5>Elige el tema</h5><p class="mb-0">Personaje, perfil, mascota, producto, logro, carta coleccionable o recomendacion.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">2</div><h5>Elige la informacion</h5><p class="mb-0">Imagen, nombre, descripcion, datos importantes y un boton de accion.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">3</div><h5>Elige el cambio</h5><p class="mb-0">Puede cambiar el texto, los puntos, el color, la imagen o mostrar una habilidad especial.</p></div></div>
                    </div>

                    <div class="example-box"><strong>Ejemplo:</strong> si haces una tarjeta de personaje, puedes mostrar su nombre e imagen. Al presionar un boton, la tarjeta puede revelar su habilidad especial, subir sus puntos y cambiar de color.</div>

                    <h2 class="doc-subtitle">Partes principales de una tarjeta interactiva</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Parte</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead><tbody>
                        <tr><td><strong>Contenedor</strong></td><td>Es la caja principal donde vive toda la tarjeta.</td><td>Un rectangulo con fondo, borde y sombra.</td></tr>
                        <tr><td><strong>Imagen</strong></td><td>Ayuda a reconocer rapidamente el tema.</td><td>Foto de personaje, mascota, producto o paisaje.</td></tr>
                        <tr><td><strong>Titulo</strong></td><td>Muestra el nombre principal.</td><td>Nova, Robot X, Mi perfil, Audifonos Gamer.</td></tr>
                        <tr><td><strong>Descripcion</strong></td><td>Explica en pocas palabras de que trata.</td><td>Exploradora espacial con habilidad de velocidad.</td></tr>
                        <tr><td><strong>Boton</strong></td><td>Permite que el usuario provoque un cambio.</td><td>Activar habilidad, Ver detalles, Cambiar estilo.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">¿Que la hace interactiva?</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-mouse-pointer"></i><h5>Hover</h5><p>Cuando el mouse pasa encima, la tarjeta puede crecer, iluminarse o levantar una sombra.</p><span class="tag-badge">CSS</span><span class="tag-badge">Visual</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-hand-pointer"></i><h5>Clic</h5><p>Al presionar un boton, JavaScript puede cambiar texto, color, imagen o valores.</p><span class="tag-badge">JavaScript</span><span class="tag-badge">Accion</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-sync-alt"></i><h5>Cambio de estado</h5><p>La tarjeta puede tener dos versiones: normal y habilidad activada.</p><span class="tag-badge">Antes</span><span class="tag-badge">Despues</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-star"></i><h5>Reaccion visual</h5><p>Un brillo, color o movimiento pequeño hace que la pagina se sienta viva.</p><span class="tag-badge">Animacion</span><span class="tag-badge">Experiencia</span></div></div>
                    </div>

                    <h2 class="doc-subtitle">Herramientas utiles</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Herramienta</th><th>Para que sirve</th><th>Cuándo usarla</th></tr></thead><tbody>
                        <tr><td><strong>Bloc de notas</strong></td><td>Crear un archivo HTML simple.</td><td>Para practicar lo basico sin instalar nada extra.</td></tr>
                        <tr><td><strong>VS Code o Cursor</strong></td><td>Escribir codigo con mas comodidad.</td><td>Para trabajar proyectos mas largos y ordenados.</td></tr>
                        <tr><td><strong>Canva o Figma</strong></td><td>Diseñar primero como se vera la tarjeta.</td><td>Para probar colores, imagenes y distribucion.</td></tr>
                        <tr><td><strong>Wix o CodePen</strong></td><td>Probar ideas visuales o ejemplos rapidamente.</td><td>Para inspirarte antes de hacerlo desde cero.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">Guia de trabajo recomendada</h2>
                    <ol>
                        <li><strong>Elige el tipo de tarjeta:</strong> personaje, perfil, producto, mascota, logro o carta coleccionable.</li>
                        <li><strong>Busca una imagen:</strong> debe ayudar a entender el tema de inmediato.</li>
                        <li><strong>Escribe poco texto:</strong> una tarjeta debe ser rapida de leer.</li>
                        <li><strong>Agrega estilo:</strong> usa color, bordes, sombra y espacio para que se vea ordenada.</li>
                        <li><strong>Agrega interaccion:</strong> un boton puede cambiar texto, puntos y color.</li>
                        <li><strong>Pruebala:</strong> abre el archivo, haz clic y revisa si el cambio se entiende.</li>
                    </ol>

                    <div class="warning-box"><strong>Consejo:</strong> no necesitas una interaccion complicada. Un cambio claro ya enseña una idea importante: una pagina puede reaccionar a lo que hace el usuario.</div>

                    <h2 class="doc-subtitle">Errores comunes</h2>
                    <div class="danger-box"><ul class="mb-0"><li>Poner demasiado texto dentro de la tarjeta.</li><li>Usar colores con poco contraste.</li><li>Crear un boton que no indique lo que hace.</li><li>Elegir una imagen que no combina con el tema.</li><li>Olvidar probar el archivo despues de guardarlo.</li></ul></div>

                    <h2 class="doc-subtitle">Resumen corto de lo aprendido</h2>
                    <p>Una tarjeta interactiva combina HTML, CSS y JavaScript. HTML crea las partes, CSS define como se ven y JavaScript permite que algo cambie cuando el usuario hace clic. Este tipo de componente aparece en perfiles, tiendas en linea, videojuegos, aplicaciones y paneles de informacion.</p>

                    <h2 class="doc-subtitle">Codigo completo para copiar y pegar</h2>
                    <p>Copia este codigo en Bloc de notas, guardalo como <strong>tarjeta-interactiva.html</strong> y abre el archivo en tu navegador. Despues cambia la imagen, nombre, colores o textos para crear tu propia version.</p>
                    <div class="code-block"><pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;es&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;meta name=&quot;viewport&quot; content=&quot;width=device-width, initial-scale=1.0&quot;&gt;
    &lt;title&gt;Tarjeta Interactiva&lt;/title&gt;
    &lt;style&gt;
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.75)), url(&quot;https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&amp;fit=crop&amp;w=1400&amp;q=80&quot;);
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            width: 320px;
            overflow: hidden;
            border-radius: 24px;
            background: rgba(10, 15, 35, 0.92);
            border: 3px solid #00ffcc;
            box-shadow: 0 0 35px rgba(0, 255, 204, 0.45);
            text-align: center;
            transition: transform .3s, box-shadow .3s;
        }
        .card:hover { transform: translateY(-8px) scale(1.02); }
        .card.power-mode { border-color: #ffd166; box-shadow: 0 0 50px rgba(255,209,102,.75); }
        .card img { width: 100%; height: 210px; object-fit: cover; }
        .content { padding: 24px; }
        h1 { margin: 0 0 10px; color: #00ffcc; }
        .role { color: #b8c7ff; font-weight: bold; }
        .description { line-height: 1.5; }
        .stats { display: flex; gap: 10px; margin: 20px 0; }
        .stat { flex: 1; padding: 10px; border-radius: 12px; background: rgba(255,255,255,.1); }
        .stat strong { display: block; color: #00ffcc; font-size: 20px; }
        button { width: 100%; padding: 14px; border: 2px solid #00ffcc; border-radius: 14px; background: transparent; color: white; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #00ffcc; color: #071018; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;section class=&quot;card&quot; id=&quot;characterCard&quot;&gt;
        &lt;img src=&quot;https://images.unsplash.com/photo-1605901309584-818e25960a8f?auto=format&amp;fit=crop&amp;w=700&amp;q=80&quot; alt=&quot;Personaje gamer&quot;&gt;
        &lt;div class=&quot;content&quot;&gt;
            &lt;h1 id=&quot;characterName&quot;&gt;Nova&lt;/h1&gt;
            &lt;p class=&quot;role&quot; id=&quot;characterRole&quot;&gt;Exploradora espacial&lt;/p&gt;
            &lt;p class=&quot;description&quot; id=&quot;characterDescription&quot;&gt;Nova viaja por planetas desconocidos buscando cristales de energia.&lt;/p&gt;
            &lt;div class=&quot;stats&quot;&gt;
                &lt;div class=&quot;stat&quot;&gt;&lt;strong id=&quot;speed&quot;&gt;85&lt;/strong&gt;Velocidad&lt;/div&gt;
                &lt;div class=&quot;stat&quot;&gt;&lt;strong id=&quot;power&quot;&gt;70&lt;/strong&gt;Poder&lt;/div&gt;
                &lt;div class=&quot;stat&quot;&gt;&lt;strong id=&quot;energy&quot;&gt;95&lt;/strong&gt;Energia&lt;/div&gt;
            &lt;/div&gt;
            &lt;button onclick=&quot;activatePowerMode()&quot;&gt;Activar habilidad especial&lt;/button&gt;
        &lt;/div&gt;
    &lt;/section&gt;
    &lt;script&gt;
        let powerActivated = false;
        function activatePowerMode() {
            const card = document.getElementById('characterCard');
            if (powerActivated === false) {
                card.classList.add('power-mode');
                document.getElementById('characterName').textContent = 'Nova Prime';
                document.getElementById('characterRole').textContent = 'Modo energia total';
                document.getElementById('characterDescription').textContent = 'Nova activa su traje especial y aumenta todas sus habilidades.';
                document.getElementById('speed').textContent = '100';
                document.getElementById('power').textContent = '95';
                document.getElementById('energy').textContent = '100';
                powerActivated = true;
            } else {
                card.classList.remove('power-mode');
                document.getElementById('characterName').textContent = 'Nova';
                document.getElementById('characterRole').textContent = 'Exploradora espacial';
                document.getElementById('characterDescription').textContent = 'Nova viaja por planetas desconocidos buscando cristales de energia.';
                document.getElementById('speed').textContent = '85';
                document.getElementById('power').textContent = '70';
                document.getElementById('energy').textContent = '95';
                powerActivated = false;
            }
        }
    &lt;/script&gt;
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


