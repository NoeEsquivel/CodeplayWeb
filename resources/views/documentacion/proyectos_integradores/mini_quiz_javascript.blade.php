<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Mini Quiz con JavaScript</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700-900.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}">
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
                <i class="fas fa-question-circle"></i>
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
                    <h1 class="doc-title">Proyecto 21: Mini Quiz con JavaScript</h1>

                    <div class="lead-box">
                        <p class="mb-0">En este proyecto vas a crear un mini quiz: un pequeño juego de preguntas donde la persona elige una respuesta, recibe retroalimentacion y suma puntos. Es un proyecto ideal para entender como JavaScript puede tomar decisiones y hacer que una pagina se sienta como una actividad interactiva.</p>
                    </div>

                    <h2 class="doc-subtitle">Antes de escribir codigo: diseña el quiz</h2>
                    <p>Un quiz no empieza con codigo, empieza con una idea. Primero decide el tema, despues escribe preguntas claras y finalmente piensa que pasara cuando el estudiante responda.</p>
                    <div class="row g-3 my-3">
                        <div class="col-md-4"><div class="step-card"><div class="step-number">1</div><h5>Elige el tema</h5><p class="mb-0">Puede ser HTML, videojuegos, cultura general, animales, ciencia, musica o seguridad digital.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">2</div><h5>Crea preguntas</h5><p class="mb-0">Empieza con 3 a 5 preguntas. Deben ser cortas y tener opciones faciles de comparar.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">3</div><h5>Define el puntaje</h5><p class="mb-0">Cada respuesta correcta puede sumar 1 punto y al final se muestra el resultado.</p></div></div>
                    </div>

                    <div class="example-box"><strong>Ejemplo:</strong> si el tema es HTML, una pregunta podria ser: ¿Que etiqueta se usa para crear un enlace? Las opciones serian &lt;img&gt;, &lt;a&gt; y &lt;p&gt;. JavaScript revisa la respuesta y dice si fue correcta.</div>

                    <h2 class="doc-subtitle">Partes principales de un mini quiz</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Parte</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead><tbody>
                        <tr><td><strong>Pregunta</strong></td><td>Presenta lo que el jugador debe responder.</td><td>¿Que lenguaje da interaccion a una pagina?</td></tr>
                        <tr><td><strong>Opciones</strong></td><td>Muestran las posibles respuestas.</td><td>HTML, CSS, JavaScript.</td></tr>
                        <tr><td><strong>Respuesta correcta</strong></td><td>Permite comparar lo que eligio el usuario.</td><td>JavaScript.</td></tr>
                        <tr><td><strong>Retroalimentacion</strong></td><td>Explica si acerto o se equivoco.</td><td>Correcto: JavaScript permite reaccionar a clics.</td></tr>
                        <tr><td><strong>Puntaje</strong></td><td>Mide cuantos aciertos obtuvo.</td><td>3 de 5 respuestas correctas.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">Como funciona por dentro</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-list"></i><h5>Lista de preguntas</h5><p>El quiz guarda preguntas, opciones y respuestas correctas. Esa informacion puede estar dentro de JavaScript.</p><span class="tag-badge">Datos</span><span class="tag-badge">Preguntas</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-hand-pointer"></i><h5>Eleccion del usuario</h5><p>Cuando la persona hace clic en una opcion, el programa revisa si coincide con la respuesta correcta.</p><span class="tag-badge">Clic</span><span class="tag-badge">Decision</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-trophy"></i><h5>Puntos</h5><p>Si la respuesta es correcta, el puntaje aumenta. Si no, se puede mostrar una explicacion corta.</p><span class="tag-badge">Puntaje</span><span class="tag-badge">Resultado</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-sync-alt"></i><h5>Siguiente pregunta</h5><p>Despues de responder, el quiz muestra una nueva pregunta hasta llegar al resultado final.</p><span class="tag-badge">Flujo</span><span class="tag-badge">Juego</span></div></div>
                    </div>

                    <h2 class="doc-subtitle">Herramientas utiles</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Herramienta</th><th>Para que sirve</th><th>Cuando usarla</th></tr></thead><tbody>
                        <tr><td><strong>Bloc de notas</strong></td><td>Crear un archivo HTML y probarlo rapido.</td><td>Para practicar el ejemplo completo.</td></tr>
                        <tr><td><strong>VS Code o Cursor</strong></td><td>Escribir codigo con mas orden y ayuda visual.</td><td>Para modificar preguntas y mejorar estilos.</td></tr>
                        <tr><td><strong>Wix o Google Forms</strong></td><td>Crear cuestionarios sin programar.</td><td>Para planear preguntas antes de hacer tu version con codigo.</td></tr>
                        <tr><td><strong>CodePen</strong></td><td>Probar HTML, CSS y JavaScript en linea.</td><td>Para experimentar sin crear archivos locales.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">Guia de trabajo recomendada</h2>
                    <ol>
                        <li><strong>Elige un tema:</strong> piensa en algo que los demas puedan responder.</li>
                        <li><strong>Escribe preguntas cortas:</strong> evita preguntas confusas o con doble respuesta.</li>
                        <li><strong>Da tres opciones:</strong> una correcta y dos incorrectas pero creibles.</li>
                        <li><strong>Agrega retroalimentacion:</strong> no solo digas correcto o incorrecto; explica un poco.</li>
                        <li><strong>Muestra puntos:</strong> el resultado final hace que se sienta como juego.</li>
                        <li><strong>Prueba varias veces:</strong> responde bien, responde mal y revisa que el puntaje funcione.</li>
                    </ol>

                    <div class="warning-box"><strong>Consejo:</strong> un buen mini quiz no necesita muchas preguntas. Es mejor tener pocas preguntas claras que muchas preguntas aburridas o dificiles de entender.</div>
                    <h2 class="doc-subtitle">Errores comunes</h2>
                    <div class="danger-box"><ul class="mb-0"><li>Crear preguntas demasiado largas.</li><li>No explicar por que una respuesta es correcta.</li><li>Olvidar reiniciar el puntaje cuando se vuelve a jugar.</li><li>Poner botones muy pequeños o dificiles de presionar.</li><li>No probar todas las respuestas antes de compartirlo.</li></ul></div>

                    <h2 class="doc-subtitle">Resumen corto de lo aprendido</h2>
                    <p>Un mini quiz combina contenido, diseño e interaccion. HTML muestra la pregunta y los botones, CSS mejora la apariencia y JavaScript revisa respuestas, cambia preguntas y calcula el puntaje. Es uno de los proyectos mas utiles para practicar decisiones en programacion.</p>

                    <h2 class="doc-subtitle">Codigo completo para copiar y pegar</h2>
                    <p>Copia este codigo en Bloc de notas, guardalo como <strong>mini-quiz.html</strong> y abre el archivo en tu navegador. Puedes cambiar las preguntas para hacer tu propio quiz.</p>
                    <div class="code-block"><pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;es&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;meta name=&quot;viewport&quot; content=&quot;width=device-width, initial-scale=1.0&quot;&gt;
    &lt;title&gt;Mini Quiz&lt;/title&gt;
    &lt;style&gt;
        body { margin: 0; min-height: 100vh; font-family: Arial, sans-serif; color: white; background: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.8)), url(&quot;https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&amp;fit=crop&amp;w=1400&amp;q=80&quot;); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .quiz { width: 90%; max-width: 560px; background: rgba(10, 15, 35, .92); border: 3px solid #00ffcc; border-radius: 24px; padding: 30px; text-align: center; box-shadow: 0 0 35px rgba(0,255,204,.45); }
        h1 { color: #00ffcc; margin-top: 0; }
        .question { font-size: 22px; margin: 25px 0; line-height: 1.4; }
        .option { display: block; width: 100%; margin: 12px 0; padding: 14px; border: 2px solid #00ffcc; border-radius: 14px; background: transparent; color: white; font-size: 16px; cursor: pointer; transition: .3s; }
        .option:hover { background: #00ffcc; color: #071018; transform: scale(1.03); }
        .feedback { min-height: 45px; margin-top: 18px; font-weight: bold; }
        .score { color: #ffd166; font-size: 18px; }
        .restart { margin-top: 18px; background: #00ffcc; color: #071018; font-weight: bold; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;main class=&quot;quiz&quot;&gt;
        &lt;h1&gt;Mini Quiz CodePlay&lt;/h1&gt;
        &lt;p class=&quot;score&quot; id=&quot;score&quot;&gt;Puntos: 0&lt;/p&gt;
        &lt;h2 class=&quot;question&quot; id=&quot;question&quot;&gt;&lt;/h2&gt;
        &lt;div id=&quot;options&quot;&gt;&lt;/div&gt;
        &lt;p class=&quot;feedback&quot; id=&quot;feedback&quot;&gt;&lt;/p&gt;
        &lt;button class=&quot;option restart&quot; onclick=&quot;restartQuiz()&quot;&gt;Reiniciar quiz&lt;/button&gt;
    &lt;/main&gt;
    &lt;script&gt;
        const questions = [
            { text: '¿Que lenguaje se usa para dar estructura a una pagina web?', options: ['HTML', 'CSS', 'JavaScript'], answer: 'HTML', info: 'HTML crea la estructura de una pagina.' },
            { text: '¿Que lenguaje ayuda a cambiar colores, tamaños y estilos?', options: ['Python', 'CSS', 'PHP'], answer: 'CSS', info: 'CSS controla la apariencia visual.' },
            { text: '¿Que lenguaje permite reaccionar a clics?', options: ['JavaScript', 'HTML', 'SQL'], answer: 'JavaScript', info: 'JavaScript agrega interaccion.' }
        ];
        let current = 0;
        let points = 0;
        function showQuestion() {
            document.getElementById('feedback').textContent = '';
            document.getElementById('score').textContent = 'Puntos: ' + points;
            if (current &gt;= questions.length) {
                document.getElementById('question').textContent = 'Resultado final: ' + points + ' de ' + questions.length;
                document.getElementById('options').innerHTML = '&lt;p&gt;¡Buen trabajo! Cambia las preguntas para crear tu propio quiz.&lt;/p&gt;';
                return;
            }
            const q = questions[current];
            document.getElementById('question').textContent = q.text;
            document.getElementById('options').innerHTML = '';
            q.options.forEach(option =&gt; {
                const button = document.createElement('button');
                button.className = 'option';
                button.textContent = option;
                button.onclick = () =&gt; checkAnswer(option);
                document.getElementById('options').appendChild(button);
            });
        }
        function checkAnswer(option) {
            const q = questions[current];
            if (option === q.answer) {
                points++;
                document.getElementById('feedback').textContent = 'Correcto. ' + q.info;
            } else {
                document.getElementById('feedback').textContent = 'Incorrecto. ' + q.info;
            }
            current++;
            setTimeout(showQuestion, 1200);
        }
        function restartQuiz() { current = 0; points = 0; showQuestion(); }
        showQuestion();
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
    </script>
</body>
</html>



