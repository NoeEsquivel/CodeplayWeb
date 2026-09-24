<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Desafío 17: Formulario Interactivo</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700-900.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/codemirror/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/codemirror/dracula.min.css') }}">
    <script src="{{ asset('vendor/codemirror/codemirror.min.js') }}"></script>
    <script src="{{ asset('vendor/codemirror/htmlmixed.min.js') }}"></script>
    <script src="{{ asset('vendor/codemirror/xml.min.js') }}"></script>
    <script src="{{ asset('vendor/codemirror/javascript.min.js') }}"></script>
    <script src="{{ asset('vendor/codemirror/css.min.js') }}"></script>
    <script src="{{ asset('vendor/confetti/confetti.browser.min.js') }}"></script>
    <style>
        :root { --accent:#00ff9d; --purple:#b026ff; --blue:#2575fc; --dark:#07111f; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; color:#fff; font-family:Roboto,sans-serif; background:url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') center/cover fixed no-repeat; }
        body::before { content:''; position:fixed; inset:0; background:rgba(0,0,0,.72); z-index:-1; }
        .navbar-custom { background:rgba(0,0,0,.86); border-bottom:3px solid var(--accent); }
        .game-title { color:var(--accent); font:1.15rem 'Press Start 2P',cursive; }
        .btn-home,.player-name { color:#fff; border:2px solid #fff; border-radius:999px; padding:.5rem 1rem; text-decoration:none; background:rgba(255,255,255,.08); }
        .challenge-container { max-width:1300px; margin:2rem auto; padding:1.5rem; border-left:5px solid var(--purple); border-radius:18px; background:rgba(0,0,0,.78); box-shadow:0 0 28px rgba(176,38,255,.18); }
        .challenge-header { border-bottom:2px solid var(--purple); padding-bottom:1rem; }
        .challenge-title { color:var(--accent); font:1.25rem 'Press Start 2P',cursive; }
        .badge-level { background:var(--purple); padding:.4rem .75rem; border-radius:8px; }
        .objectives-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:.65rem; padding:0; list-style:none; }
        .objectives-list li { padding:.75rem; border-radius:12px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.16); }
        .objectives-list i { color:var(--accent); margin-right:.35rem; }
        .hint-box { padding:1rem; margin:1rem 0; border-left:4px solid #ffcc00; border-radius:0 10px 10px 0; background:rgba(255,204,0,.13); }
        .reference-card { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(260px,.85fr); gap:1rem; align-items:center; margin:1rem 0 1.5rem; padding:1rem; border:2px solid rgba(0,255,157,.45); border-radius:16px; background:rgba(15,23,42,.78); }
        .reference-card img { width:100%; border-radius:12px; background:#fff; }
        .reference-copy { color:rgba(255,255,255,.78); }
        .reference-copy h2 { color:var(--accent); font-size:1.1rem; }
        .editor-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .editor-panel,.preview-panel { min-height:510px; border-radius:12px; overflow:hidden; }
        .CodeMirror { height:510px !important; border:3px solid var(--accent); border-radius:12px; font-size:14px; }
        .preview-panel { background:#fff; border:3px solid var(--accent); }
        .preview-panel iframe { width:100%; height:100%; min-height:504px; border:0; background:#fff; }
        .action-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.75rem; margin:1.2rem 0; padding:.85rem; border:2px solid rgba(0,255,157,.5); border-radius:16px; background:rgba(2,6,23,.94); }
        .btn-run,.btn-submit { border:0; border-radius:999px; padding:.75rem 1.35rem; font-weight:700; }
        .btn-run { color:#fff; background:linear-gradient(45deg,#6a11cb,var(--blue)); }
        .btn-submit { color:#07131f; background:linear-gradient(45deg,#16a34a,var(--accent)); }
        .validation-panel { margin-top:1rem; padding:1rem; border-radius:14px; background:rgba(15,23,42,.92); border:1px solid rgba(255,255,255,.16); }
        .validation-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.5rem; }
        .validation-item { padding:.55rem .7rem; border-radius:10px; background:rgba(255,255,255,.08); }
        .validation-item.ok { color:#86efac; } .validation-item.missing { color:#fca5a5; }
        .feedback { display:none; margin-top:1rem; padding:1rem; border-radius:10px; }
        .feedback.show { display:block; } .feedback.success { background:rgba(40,167,69,.2); border-left:4px solid #28a745; } .feedback.error { background:rgba(220,53,69,.2); border-left:4px solid #dc3545; }
        @media (max-width:800px) { .editor-grid,.reference-card { grid-template-columns:1fr; } .game-title { font-size:.8rem; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom py-3">
        <div class="container-fluid d-flex align-items-center justify-content-between gap-2">
            <a class="btn-home" href="{{ route('alumno.desafios.programacion') }}"><i class="fas fa-arrow-left me-2"></i>Volver</a>
            <div class="game-title">CODEPLAY</div>
            <div class="player-name"><i class="fas fa-user me-2"></i>{{ session('usuario_nombre', 'Jugador') }}</div>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-5">
        <section class="challenge-container">
            <header class="challenge-header">
                <h1 class="challenge-title">Desafío 17: Formulario Interactivo</h1>
                <p class="text-white-50 mb-2">Construye una ficha de registro que valide sus datos sin recargar la página.</p>
                <span class="badge-level">NIVEL 17</span>
                <span class="badge bg-success ms-2">{{ $infoRecompensa['puntos_base'] ?? 400 }} puntos</span>
            </header>

            <div class="hint-box"><strong>Idea:</strong> crea una experiencia de registro amable: etiquetas claras, campos accesibles, estilos propios y un mensaje de confirmación generado con JavaScript.</div>

            <div class="reference-card">
                <img src="{{ asset('Imagenes/desafio17_formulario_interactivo_referencia.svg') }}" alt="Ejemplo visual de un formulario interactivo terminado">
                <div class="reference-copy">
                    <h2><i class="fas fa-image me-2"></i>Referencia visual</h2>
                    <p>Tu resultado puede tener otra temática y colores. Usa esta imagen para identificar la estructura esperada: una tarjeta, campos legibles, un selector y un botón claro.</p>
                    <p class="mb-0"><i class="fas fa-lightbulb text-warning me-2"></i>La apariencia es libre; lo importante es cumplir los objetivos de HTML, CSS y JavaScript.</p>
                </div>
            </div>

            <h2 class="h4 mt-4"><i class="fas fa-bullseye me-2"></i>Objetivos del proyecto</h2>
            <ul class="objectives-list mt-3">
                <li data-check="form"><i class="fas fa-circle-check"></i>Formulario con apertura y cierre correctos.</li>
                <li data-check="labels"><i class="fas fa-circle-check"></i>Etiquetas <code>&lt;label&gt;</code> conectadas mediante <code>for</code>.</li>
                <li data-check="fields"><i class="fas fa-circle-check"></i>Campos <code>name</code>, correo electrónico y <code>required</code>.</li>
                <li data-check="extra"><i class="fas fa-circle-check"></i>Incluye un <code>&lt;select&gt;</code> y un <code>&lt;textarea&gt;</code>.</li>
                <li data-check="style"><i class="fas fa-circle-check"></i>Estilo visual con fondo, bordes redondeados y Flexbox o Grid.</li>
                <li data-check="events"><i class="fas fa-circle-check"></i>Evento <code>submit</code> con <code>preventDefault()</code>.</li>
                <li data-check="validation"><i class="fas fa-circle-check"></i>Lee valores y muestra una respuesta usando <code>textContent</code>.</li>
            </ul>

            <div class="editor-grid mt-4">
                <div class="editor-panel">
                    <textarea id="codeEditor"><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; }
        .tarjeta { max-width: 420px; margin: auto; }
        /* Completa el estilo del formulario */
    </style>
</head>
<body>
    <div class="tarjeta">
        <h1>Únete al equipo</h1>
        <form id="registroForm">
            <!-- Agrega etiquetas, campos, un select y un textarea -->
            <button type="submit">Registrarme</button>
            <p id="mensaje"></p>
        </form>
    </div>
    <script>
        // Conecta el evento submit y valida los datos aquí.
    </script>
</body>
</html></textarea>
                </div>
                <div class="preview-panel"><iframe id="previewFrame" title="Vista previa del formulario" sandbox="allow-scripts allow-modals"></iframe></div>
            </div>

            <section class="validation-panel">
                <h2 class="h5"><i class="fas fa-list-check me-2"></i>Progreso del desafío</h2>
                <div id="validationGrid" class="validation-grid"></div>
            </section>

            <div class="action-toolbar">
                <button id="runButton" class="btn-run"><i class="fas fa-play me-2"></i>Ejecutar código</button>
                <button id="submitButton" class="btn-submit"><i class="fas fa-check me-2"></i>Entregar solución</button>
                <span class="text-white-50">Ejecuta la vista y entrega cuando todos los objetivos estén completos.</span>
            </div>
            <div id="feedback" class="feedback"></div>
        </section>
    </main>

    <script>
        const editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            mode: 'htmlmixed', theme: 'dracula', lineNumbers: true, lineWrapping: true,
            indentUnit: 4, tabSize: 4, autoCloseTags: true
        });
        const previewFrame = document.getElementById('previewFrame');
        const validationGrid = document.getElementById('validationGrid');
        const feedback = document.getElementById('feedback');
        const requirementLabels = {
            form: 'Formulario válido', labels: 'Etiquetas conectadas', fields: 'Campos y correo',
            extra: 'Select y textarea', style: 'Estilo y layout', events: 'Evento submit', validation: 'Respuesta dinámica'
        };

        function evaluateCode(code) {
            const lower = code.toLowerCase();
            const compact = lower.replace(/\s+/g, '');
            return {
                form: lower.includes('<form') && lower.includes('</form>'),
                labels: lower.includes('<label') && lower.includes('for='),
                fields: lower.includes('name=') && (lower.includes('type="email"') || lower.includes("type='email'")) && lower.includes('required'),
                extra: lower.includes('<select') && lower.includes('<textarea'),
                style: (lower.includes('background-color') || lower.includes('background:')) && lower.includes('border-radius') && (compact.includes('display:flex') || compact.includes('display:grid')),
                events: lower.includes('addeventlistener') && lower.includes('submit') && lower.includes('preventdefault'),
                validation: lower.includes('value') && lower.includes('textcontent')
            };
        }

        function renderValidation(showFeedback = false) {
            const checks = evaluateCode(editor.getValue());
            validationGrid.innerHTML = Object.entries(checks).map(([key, ok]) => `
                <div class="validation-item ${ok ? 'ok' : 'missing'}"><i class="fas ${ok ? 'fa-check' : 'fa-circle'} me-2"></i>${requirementLabels[key]}</div>
            `).join('');
            const completed = Object.values(checks).filter(Boolean).length;
            if (showFeedback && completed !== Object.keys(checks).length) {
                feedback.className = 'feedback error show';
                feedback.textContent = `Aún faltan objetivos (${completed}/${Object.keys(checks).length}). Revisa los elementos marcados.`;
            }
            return Object.values(checks).every(Boolean);
        }

        function runCode() {
            previewFrame.srcdoc = editor.getValue();
            renderValidation(false);
        }

        async function submitSolution() {
            runCode();
            if (!renderValidation(true)) return;
            const code = editor.getValue();
            try {
                const response = await fetch('{{ route("desafios.procesar-recompensa") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ nivel: 17, codigo: code })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    feedback.className = 'feedback error show';
                    feedback.textContent = data.message || data.error || 'No se pudo validar la solución.';
                    return;
                }
                feedback.className = 'feedback success show';
                feedback.textContent = `¡Desafío completado! Ganaste ${data.puntos_otorgados} puntos.`;
                if (typeof confetti === 'function') confetti({ particleCount: 160, spread: 85, origin: { y: .6 } });
            } catch (error) {
                feedback.className = 'feedback error show';
                feedback.textContent = 'No se pudo registrar la recompensa. Intenta de nuevo.';
            }
        }

        document.getElementById('runButton').addEventListener('click', runCode);
        document.getElementById('submitButton').addEventListener('click', submitSolution);
        window.addEventListener('load', () => {
            runCode();
            const info = @json($infoRecompensa);
            if (info.ya_completado && !info.puede_reclamar) {
                feedback.className = 'feedback show';
                feedback.textContent = `Ya completaste este desafío. Podrás reclamar nuevamente ${info.tiempo_restante}.`;
            }
        });
    </script>
</body>
</html>
