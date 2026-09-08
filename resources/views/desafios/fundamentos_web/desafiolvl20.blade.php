<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CODEPLAY · Proyecto final: Mini Arcade</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <link rel="stylesheet" href="{{ asset('css/desafio20.css') }}?v={{ filemtime(public_path('css/desafio20.css')) }}">
</head>
<body style="--fondo: url('{{ asset('Imagenes/Pantalla_Inicio.jpg') }}')">
<nav class="topbar" aria-label="Navegación principal">
    <a class="back" href="{{ route('alumno.desafios.programacion') }}">← Volver a desafíos</a>
    <span class="brand">CODEPLAY</span>
    <span class="player">{{ session('usuario_nombre', 'Jugador') }}</span>
</nav>
<main id="arcadeChallenge" class="challenge"
      data-validate-url="{{ route('desafios.validar-proyecto-final') }}"
      data-reward-url="{{ route('desafios.procesar-recompensa') }}"
      data-checks-url="{{ asset('js/desafio20-checks.js') }}?v={{ filemtime(public_path('js/desafio20-checks.js')) }}"
      data-storage-key="codeplay.arcade20.{{ session('usuario_id', 'invitado') }}"
      data-can-claim="{{ $infoRecompensa['puede_reclamar'] ? '1' : '0' }}">
    <header class="hero">
        <div class="hero-copy">
            <p class="eyebrow">DESAFÍO 20 · PROYECTO FINAL</p>
            <h1>Tu sala.<br>Tus juegos.<br><span>Tu código.</span></h1>
            <p class="lead">Construye un Mini Arcade: un menú y dos juegos que funcionan gracias a las decisiones que tú programas.</p>
            <div class="badges"><span>MAESTRO <span aria-label="5 estrellas">★★★★★</span></span><span>{{ $infoRecompensa['puntos_base'] }} puntos de CodePlay</span></div>
        </div>
        <div class="arcade-map" aria-label="Las tres pantallas del proyecto">
            <div class="map-menu"><small>01 / MENÚ</small><strong>Elige tu aventura</strong><span>Dos puertas a tus juegos ↓</span></div>
            <div class="map-games">
                <div><small>02 / CULEBRITA</small><div class="snake-art" aria-hidden="true">▣ ▣ ▣<br>　　▣　●</div><b>+10 por comida</b></div>
                <div><small>03 / MEMORIA</small><div class="memory-art" aria-hidden="true">◆ ? ? ?<br>? ◆ ? ?<br>? ? ? ?</div><b>+20 por pareja</b></div>
            </div>
        </div>
    </header>

    <section class="intro" aria-labelledby="missionTitle">
        <h2 id="missionTitle">Tu misión, paso a paso</h2>
        <p>No tienes que empezar desde una página en blanco. Ya preparamos los tableros, los controles, la navegación y los botones de reinicio. Tu trabajo es dar identidad al menú y completar <strong>seis funciones pequeñas</strong>.</p>
        <ol class="steps">
            <li><b>1. Lee y encuentra</b><span>Elige una pestaña. Busca <code>TODO</code> en su código: ahí están las tareas.</span></li>
            <li><b>2. Cambia y prueba</b><span>Completa una función. Pulsa «Ejecutar vista» para ver tu cambio y jugar.</span></li>
            <li><b>3. Comprueba</b><span>«Comprobar proyecto» revisa las tres vistas. Lee el mensaje de cada objetivo pendiente.</span></li>
            <li><b>4. Entrega</b><span>Cuando todos los objetivos funcionen, podrás enviar tu proyecto final.</span></li>
        </ol>
        <p class="note">Los puntos de cada juego comienzan en 0 al entrar o reiniciar. Son parte del juego; la recompensa de CodePlay se obtiene al entregar el proyecto.</p>
    </section>

    <section class="help" aria-labelledby="guideTitle">
        <h2 id="guideTitle">Guía y pistas · abre solo la ayuda que necesites</h2>
        <details open>
            <summary>Antes de empezar: ¿qué es una función y qué hace return?</summary>
            <p>Una función recibe datos y devuelve una respuesta. Por ejemplo, <code>function doble(numero) { return numero + numero; }</code> recibe 3 y devuelve 6.</p>
            <p>En las seis funciones marcadas sustituye <code>return null;</code> por <strong>un único <code>return</code> con tu respuesta</strong>. Conserva sus nombres y parámetros. Usaremos sumas, comparaciones y <code>||</code> (significa «o»); aquí no necesitas bucles, llamadas a otras funciones ni un bloque <code>if</code>.</p>
            <p>El resto del código dibuja y conecta el juego: puedes estudiarlo y cambiar su apariencia. Conserva los identificadores de los tableros, los botones y el contador para que las conexiones sigan funcionando.</p>
        </details>
        <details>
            <summary>01 · Menú: ponle nombre a tu sala</summary>
            <p>Cambia el texto dentro de <code>&lt;h1&gt;</code> por un nombre propio de al menos 4 caracteres. Después escribe instrucciones de al menos 35 caracteres en el párrafo <code>id="descripcion"</code>: explica cómo elegir y jugar ambos juegos.</p>
            <p>Los dos botones con <code>data-vista="culebrita"</code> y <code>data-vista="memoria"</code> ya navegan. No tienes que crear enlaces, guardar datos ni conectar una base de datos.</p>
        </details>
        <details>
            <summary>02 · Culebrita: mover, detectar una pared y sumar puntos</summary>
            <p>El tablero tiene 12 columnas y 12 filas. <code>x</code> indica la columna y <code>y</code> la fila; las posiciones válidas van de 0 a 11. La comida vale <strong>10 puntos</strong>.</p>
            <ol>
                <li><code>siguienteCabeza(cabeza, direccion)</code>: devuelve una nueva posición con <code>x</code> e <code>y</code>. Suma a cada coordenada la dirección que recibe.</li>
                <li><code>chocaConPared(cabeza, limite)</code>: devuelve <code>true</code> si alguna coordenada es menor que 0 o mayor o igual al límite. Si no, devuelve <code>false</code>.</li>
                <li><code>puntosPorComida(puntos)</code>: devuelve la puntuación actual más 10.</li>
            </ol>
            <details><summary>Pista de movimiento</summary><p>Si la cabeza está en <code>{x: 4, y: 3}</code> y la dirección es <code>{x: 1, y: 0}</code>, debe quedar en <code>{x: 5, y: 3}</code>. Usa la forma <code>return { x: sumaHorizontal, y: sumaVertical };</code> reemplazando esas sumas por las propiedades que recibes.</p></details>
            <details><summary>Pista de colisión</summary><p>Una de las cuatro comparaciones es <code>cabeza.x &lt; 0</code>. Conecta con <code>||</code> la pared derecha y las dos paredes de <code>y</code>. Basta una condición verdadera para chocar.</p></details>
            <details><summary>Cómo probarlo tú</summary><p>Pulsa Iniciar. Usa las flechas o los controles del tablero. Come para crecer y sumar 10, choca para terminar y reinicia para volver a 0. El dibujo, el crecimiento y el choque con el cuerpo ya están preparados.</p></details>
        </details>
        <details>
            <summary>03 · Memoria: seis parejas en una matriz de 3 × 4</summary>
            <p>Hay <strong>3 filas y 4 columnas</strong>: 12 cartas que forman 6 parejas. Cada pareja encontrada vale <strong>20 puntos</strong>; al terminar debes tener 120.</p>
            <ol>
                <li><code>sonPareja(primera, segunda)</code>: compara los dos símbolos y devuelve si son iguales.</li>
                <li><code>puntosPorPareja(puntos)</code>: devuelve los puntos actuales más 20.</li>
                <li><code>partidaTerminada(parejas)</code>: devuelve si ya se encontraron las 6 parejas.</li>
            </ol>
            <details><summary>Pista de comparación y victoria</summary><p><code>===</code> compara si dos valores son iguales. Por ejemplo, <code>return color === 'verde';</code> responde verdadero solo para ese color. Aplica la misma idea a los símbolos y al número de parejas.</p></details>
            <details><summary>Cómo probarlo tú</summary><p>Destapa dos cartas distintas. Si coinciden, deben quedar visibles y sumar 20. Si no coinciden, vuelven a ocultarse. Encontrar seis parejas muestra la victoria; Reiniciar mezcla las cartas y pone los puntos en 0.</p></details>
        </details>
    </section>

    <section class="workshop" aria-labelledby="editorTitle">
        <div class="section-heading"><div><p class="eyebrow">MANOS AL CÓDIGO</p><h2 id="editorTitle">Tres vistas, un proyecto</h2></div><span id="saveStatus" class="muted" role="status">Tus cambios se guardan en esta pestaña.</span></div>
        <div class="tabs" role="tablist" aria-label="Archivos del proyecto">
            <button type="button" id="tab-menu" role="tab" aria-selected="true" aria-controls="panel-menu" data-editor="menu">01 · Menú</button>
            <button type="button" id="tab-culebrita" role="tab" aria-selected="false" aria-controls="panel-culebrita" data-editor="culebrita" tabindex="-1">02 · Culebrita</button>
            <button type="button" id="tab-memoria" role="tab" aria-selected="false" aria-controls="panel-memoria" data-editor="memoria" tabindex="-1">03 · Memoria</button>
        </div>
        <div class="work-grid">
            <div class="code-column">
                @foreach (['menu' => 'Menú', 'culebrita' => 'Culebrita', 'memoria' => 'Memoria'] as $clave => $titulo)
                    <div id="panel-{{ $clave }}" role="tabpanel" aria-labelledby="tab-{{ $clave }}" @if($clave !== 'menu') hidden @endif>
                        <label class="editor-label" for="editor-{{ $clave }}">{{ $titulo }} · HTML, CSS y JavaScript</label>
                        <textarea id="editor-{{ $clave }}" spellcheck="false">{{ $plantillas[$clave] }}</textarea>
                    </div>
                @endforeach
                <div class="editor-tools"><button type="button" id="nextTask" class="quiet">Buscar siguiente TODO ↓</button><button type="button" id="downloadProject" class="quiet">Descargar mi proyecto</button></div>
            </div>
            <div class="preview-column">
                <div class="preview-heading"><span>VISTA PREVIA · <b id="previewName">Menú</b></span><button type="button" id="previewHome" class="quiet">⌂ Menú</button></div>
                <div id="previewHost"><iframe id="previewFrame" title="Vista previa del Mini Arcade" sandbox="allow-scripts" referrerpolicy="no-referrer"></iframe></div>
                <p class="preview-note">Ejecuta para aplicar tus cambios. Usa los botones del juego para moverte entre pantallas. Al salir de un juego, se detiene.</p>
                <p id="previewError" class="inline-error" role="status" hidden></p>
            </div>
        </div>
    </section>

    <section class="progress-panel" aria-labelledby="progressTitle">
        <div class="section-heading"><h2 id="progressTitle">Progreso de tu proyecto</h2><strong id="progressCount">0 / 9</strong></div>
        <p class="muted">La comprobación prueba tus funciones con varios valores. Las respuestas no se dan por correctas solo por escribir sus nombres.</p>
        <progress id="progressBar" max="9" value="0" aria-label="Objetivos completados"></progress>
        <div id="checksGrid" class="checks-grid" aria-live="polite"></div>
    </section>
    <div class="actions">
        <button type="button" class="button run" id="runButton">▶ Ejecutar vista</button>
        <button type="button" class="button check" id="checkButton">✓ Comprobar proyecto</button>
        <button type="button" class="button submit" id="submitButton" disabled>Entregar proyecto</button>
        <span class="muted">Completa los objetivos de las tres vistas para entregar.</span>
    </div>
    <p id="feedback" class="feedback" role="status">Comienza por personalizar el menú y después resuelve un TODO a la vez.</p>
    <section id="finalAchievement" class="final-achievement" aria-labelledby="achievementTitle" hidden>
        <span class="achievement-icon" aria-hidden="true">🏆</span>
        <div>
            <p class="eyebrow">PROYECTO FINAL COMPLETADO</p>
            <h2 id="achievementTitle">Lo imaginaste. Lo programaste. ¡Lo lograste!</h2>
            <p>Tu Mini Arcade es una prueba de lo que puedes crear. Cada error que resolviste y cada intento te ayudaron a llegar hasta aquí. Sigue explorando: tu próxima gran idea puede empezar con una línea de código.</p>
            <strong id="achievementReward"></strong>
        </div>
    </section>
    @if (!$infoRecompensa['puede_reclamar'])
        <p class="note">Ya recibiste la recompensa de este desafío. Podrás reclamar nuevamente {{ $infoRecompensa['tiempo_restante'] }}. Puedes seguir practicando.</p>
    @endif
    <p class="muted closing-note">Guardamos los tres códigos en esta pestaña cuando el navegador lo permite. Al volver al menú se reinicia la partida, no tu código. Descarga tu proyecto para conservarlo.</p>
    <noscript>Activa JavaScript para usar los editores y probar los juegos.</noscript>
</main>
<dialog id="finalCelebration" class="final-celebration" aria-labelledby="celebrationTitle" aria-describedby="celebrationMessage">
    <div id="celebrationConfetti" class="celebration-confetti" aria-hidden="true"></div>
    <div class="celebration-content">
        <p class="eyebrow">CODEPLAY · UN GRAN LOGRO</p>
        <div class="celebration-trophy" aria-hidden="true">🏆</div>
        <p class="celebration-stars" aria-label="Cinco estrellas">★★★★★</p>
        <h2 id="celebrationTitle">¡Tu idea ahora<br>se puede jugar!</h2>
        <p class="celebration-subtitle">Has completado el proyecto final de CodePlay.</p>
        <p id="celebrationReward" class="celebration-reward"></p>
        <p id="celebrationMessage">Lo maravilloso de la programación es que te permite convertir una idea en algo que otras personas pueden usar, disfrutar y aprender. Hoy creaste tus propios juegos. Mañana podrías crear una herramienta que ayude a alguien o una aventura que todavía no existe.</p>
        <p class="celebration-motto">No necesitas saberlo todo para empezar.<br><strong>Solo curiosidad, práctica y ganas de seguir intentando.</strong></p>
        <button type="button" id="closeCelebration" class="button submit" autofocus>¡Voy a seguir creando! →</button>
        <p class="celebration-footer">Este es el final del desafío, y el comienzo de nuevas ideas.</p>
    </div>
</dialog>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="{{ asset('js/desafio20.js') }}?v={{ filemtime(public_path('js/desafio20.js')) }}"></script>
</body>
</html>
