<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Juego La Culebrita</title>
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
                <i class="fas fa-dragon"></i>
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
                    <h1 class="doc-title">Proyecto 22: Creemos un juego La culebrita</h1>

                    <div class="lead-box">
                        <p class="mb-0">En este proyecto vas a crear una version sencilla del juego de la culebrita. La idea no es hacer un juego enorme desde el primer intento, sino entender como un programa puede mover un personaje, detectar comida, sumar puntos y terminar cuando ocurre una colision.</p>
                    </div>

                    <h2 class="doc-subtitle">Antes de escribir codigo: entiende el juego</h2>
                    <p>La culebrita parece un juego simple, pero enseña ideas muy importantes de programacion: movimiento, repeticion, coordenadas, condiciones, puntaje y reglas. Antes de programar, conviene imaginar sus reglas basicas.</p>
                    <div class="row g-3 my-3">
                        <div class="col-md-4"><div class="step-card"><div class="step-number">1</div><h5>El jugador se mueve</h5><p class="mb-0">La serpiente avanza en una direccion y el jugador la guia con el teclado.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">2</div><h5>Debe comer</h5><p class="mb-0">Cuando toca la comida, suma puntos y la serpiente crece.</p></div></div>
                        <div class="col-md-4"><div class="step-card"><div class="step-number">3</div><h5>Debe evitar chocar</h5><p class="mb-0">Si toca una pared o su propio cuerpo, el juego termina.</p></div></div>
                    </div>

                    <div class="example-box"><strong>Ejemplo:</strong> imagina el tablero como una hoja cuadriculada. La serpiente ocupa algunos cuadritos y la comida ocupa otro. Cada cierto tiempo, el programa mueve la serpiente al siguiente cuadrito.</div>

                    <h2 class="doc-subtitle">Partes principales del juego</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Parte</th><th>Para que sirve</th><th>Ejemplo</th></tr></thead><tbody>
                        <tr><td><strong>Tablero</strong></td><td>Es el area donde ocurre el juego.</td><td>Un cuadro negro dividido en posiciones invisibles.</td></tr>
                        <tr><td><strong>Serpiente</strong></td><td>Es el personaje que controla el jugador.</td><td>Una lista de cuadritos verdes.</td></tr>
                        <tr><td><strong>Comida</strong></td><td>Hace que el jugador gane puntos y la serpiente crezca.</td><td>Un cuadrito rojo que aparece al azar.</td></tr>
                        <tr><td><strong>Direccion</strong></td><td>Indica hacia donde se movera la serpiente.</td><td>Arriba, abajo, izquierda o derecha.</td></tr>
                        <tr><td><strong>Colisiones</strong></td><td>Permiten saber cuando se gana comida o cuando termina el juego.</td><td>Chocar con comida, pared o cuerpo.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">Conceptos de programacion que se practican</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-border-all"></i><h5>Coordenadas</h5><p>El juego usa posiciones X e Y para saber donde esta cada parte de la serpiente y la comida.</p><span class="tag-badge">X</span><span class="tag-badge">Y</span><span class="tag-badge">Tablero</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-sync-alt"></i><h5>Bucle de juego</h5><p>El programa repite acciones: limpiar pantalla, mover serpiente, dibujar, revisar choques y actualizar puntos.</p><span class="tag-badge">Repeticion</span><span class="tag-badge">Movimiento</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-keyboard"></i><h5>Eventos del teclado</h5><p>JavaScript escucha las flechas del teclado para cambiar la direccion de la serpiente.</p><span class="tag-badge">Teclas</span><span class="tag-badge">Control</span></div></div>
                        <div class="col-md-6"><div class="tool-card"><i class="fas fa-exclamation-triangle"></i><h5>Condiciones</h5><p>El juego pregunta si la serpiente comio, si choco con pared o si se encontro con su propio cuerpo.</p><span class="tag-badge">if</span><span class="tag-badge">Reglas</span></div></div>
                    </div>

                    <h2 class="doc-subtitle">Herramientas utiles</h2>
                    <div class="table-responsive"><table class="comparison-table"><thead><tr><th>Herramienta</th><th>Para que sirve</th><th>Cuando usarla</th></tr></thead><tbody>
                        <tr><td><strong>Bloc de notas</strong></td><td>Crear el archivo HTML y probar el juego simple.</td><td>Cuando quieres entender el codigo desde cero.</td></tr>
                        <tr><td><strong>VS Code o Cursor</strong></td><td>Editar el juego con mas comodidad.</td><td>Cuando quieres agregar niveles, sonidos o mejoras.</td></tr>
                        <tr><td><strong>CodePen</strong></td><td>Probar HTML, CSS y JavaScript en linea.</td><td>Cuando quieres experimentar rapido.</td></tr>
                        <tr><td><strong>Scratch</strong></td><td>Crear una version visual con bloques.</td><td>Cuando quieres practicar la logica antes de escribir codigo.</td></tr>
                        <tr><td><strong>Construct o GDevelop</strong></td><td>Crear videojuegos 2D con herramientas visuales.</td><td>Cuando quieres avanzar hacia juegos mas completos.</td></tr>
                    </tbody></table></div>

                    <h2 class="doc-subtitle">Guia de trabajo recomendada</h2>
                    <ol>
                        <li><strong>Dibuja el tablero:</strong> piensa en una cuadricula donde cada casilla puede estar vacia, tener comida o tener parte de la serpiente.</li>
                        <li><strong>Crea la serpiente:</strong> empieza con una lista pequeña de posiciones.</li>
                        <li><strong>Haz que se mueva:</strong> cada cierto tiempo debe avanzar una casilla.</li>
                        <li><strong>Escucha el teclado:</strong> las flechas cambian la direccion.</li>
                        <li><strong>Agrega comida:</strong> debe aparecer en una posicion al azar.</li>
                        <li><strong>Suma puntos:</strong> cuando la serpiente come, aumenta el marcador.</li>
                        <li><strong>Detecta choques:</strong> si toca la pared o su cuerpo, muestra mensaje de fin.</li>
                    </ol>

                    <div class="warning-box"><strong>Consejo:</strong> no intentes crear el juego perfecto en el primer intento. Primero haz que la serpiente se mueva. Despues agrega comida. Luego puntos. Al final agrega detalles como colores, sonidos o dificultad.</div>
                    <h2 class="doc-subtitle">Errores comunes</h2>
                    <div class="danger-box"><ul class="mb-0"><li>Intentar agregar demasiadas funciones antes de que el movimiento funcione.</li><li>No revisar si la serpiente choca con los bordes.</li><li>Permitir que la serpiente se mueva en direccion contraria de golpe y choque consigo misma.</li><li>Olvidar reiniciar el puntaje al volver a jugar.</li><li>No probar el juego varias veces con diferentes movimientos.</li></ul></div>

                    <h2 class="doc-subtitle">Resumen corto de lo aprendido</h2>
                    <p>El juego de la culebrita enseña como un programa puede repetir acciones, mover elementos, escuchar teclas, usar coordenadas y tomar decisiones. Aunque se ve sencillo, contiene muchas ideas importantes que tambien aparecen en juegos mas grandes.</p>

                    <h2 class="doc-subtitle">Codigo completo para copiar y pegar</h2>
                    <p>Copia este codigo en Bloc de notas, guardalo como <strong>culebrita.html</strong> y abre el archivo en tu navegador. Usa las flechas del teclado para jugar. Si quieres personalizarlo, cambia colores, velocidad o tamaño del tablero.</p>
                    <div class="code-block"><pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang=&quot;es&quot;&gt;
&lt;head&gt;
    &lt;meta charset=&quot;UTF-8&quot;&gt;
    &lt;meta name=&quot;viewport&quot; content=&quot;width=device-width, initial-scale=1.0&quot;&gt;
    &lt;title&gt;Juego La Culebrita&lt;/title&gt;
    &lt;style&gt;
        body { margin: 0; min-height: 100vh; font-family: Arial, sans-serif; color: white; background: linear-gradient(rgba(0,0,0,.5), rgba(0,0,0,.85)), url(&quot;https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&amp;fit=crop&amp;w=1400&amp;q=80&quot;); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .game-box { text-align: center; background: rgba(5,12,20,.92); border: 3px solid #00ff99; border-radius: 24px; padding: 25px; box-shadow: 0 0 35px rgba(0,255,153,.45); }
        h1 { color: #00ff99; margin-top: 0; }
        canvas { background: #071018; border: 3px solid #00ff99; border-radius: 12px; max-width: 100%; }
        .score { color: #ffd166; font-size: 20px; font-weight: bold; }
        button { margin-top: 15px; padding: 12px 20px; border: 2px solid #00ff99; border-radius: 12px; background: transparent; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background: #00ff99; color: #071018; }
        .help { max-width: 420px; color: #d6e6f2; line-height: 1.5; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;main class=&quot;game-box&quot;&gt;
        &lt;h1&gt;La Culebrita&lt;/h1&gt;
        &lt;p class=&quot;score&quot; id=&quot;score&quot;&gt;Puntos: 0&lt;/p&gt;
        &lt;canvas id=&quot;game&quot; width=&quot;400&quot; height=&quot;400&quot;&gt;&lt;/canvas&gt;
        &lt;p class=&quot;help&quot;&gt;Usa las flechas del teclado para mover la serpiente. Come la comida roja y evita chocar con las paredes o contigo mismo.&lt;/p&gt;
        &lt;button onclick=&quot;restartGame()&quot;&gt;Reiniciar juego&lt;/button&gt;
    &lt;/main&gt;
    &lt;script&gt;
        const canvas = document.getElementById('game');
        const ctx = canvas.getContext('2d');
        const scoreText = document.getElementById('score');
        const box = 20;
        let snake = [{ x: 200, y: 200 }];
        let food = { x: 100, y: 100 };
        let dx = box;
        let dy = 0;
        let score = 0;
        let gameOver = false;
        document.addEventListener('keydown', changeDirection);
        setInterval(updateGame, 130);
        function updateGame() {
            if (gameOver) return;
            const head = { x: snake[0].x + dx, y: snake[0].y + dy };
            if (head.x &lt; 0 || head.x &gt;= canvas.width || head.y &lt; 0 || head.y &gt;= canvas.height || hitsBody(head)) { endGame(); return; }
            snake.unshift(head);
            if (head.x === food.x &amp;&amp; head.y === food.y) { score++; scoreText.textContent = 'Puntos: ' + score; moveFood(); } else { snake.pop(); }
            drawGame();
        }
        function drawGame() {
            ctx.fillStyle = '#071018'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#ff4d6d'; ctx.fillRect(food.x, food.y, box, box);
            ctx.fillStyle = '#00ff99'; snake.forEach(part =&gt; ctx.fillRect(part.x, part.y, box - 2, box - 2));
        }
        function changeDirection(event) {
            if (event.key === 'ArrowUp' &amp;&amp; dy === 0) { dx = 0; dy = -box; }
            if (event.key === 'ArrowDown' &amp;&amp; dy === 0) { dx = 0; dy = box; }
            if (event.key === 'ArrowLeft' &amp;&amp; dx === 0) { dx = -box; dy = 0; }
            if (event.key === 'ArrowRight' &amp;&amp; dx === 0) { dx = box; dy = 0; }
        }
        function moveFood() { food.x = Math.floor(Math.random() * (canvas.width / box)) * box; food.y = Math.floor(Math.random() * (canvas.height / box)) * box; }
        function hitsBody(head) { return snake.some(part =&gt; part.x === head.x &amp;&amp; part.y === head.y); }
        function endGame() { gameOver = true; ctx.fillStyle = 'rgba(0,0,0,.7)'; ctx.fillRect(0, 0, canvas.width, canvas.height); ctx.fillStyle = 'white'; ctx.font = '28px Arial'; ctx.textAlign = 'center'; ctx.fillText('Juego terminado', canvas.width / 2, canvas.height / 2); ctx.font = '18px Arial'; ctx.fillText('Puntos: ' + score, canvas.width / 2, canvas.height / 2 + 35); }
        function restartGame() { snake = [{ x: 200, y: 200 }]; food = { x: 100, y: 100 }; dx = box; dy = 0; score = 0; gameOver = false; scoreText.textContent = 'Puntos: 0'; drawGame(); }
        drawGame();
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




