<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Desafío 16: Página Web Personal</title>
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
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}">
    <script src="{{ asset('vendor/confetti/confetti.browser.min.js') }}"></script>
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --challenge-color: #b026ff;
            --warning-color: #ffcc00;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
            min-height: 100vh;
            position: relative;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.65);
            z-index: -1;
        }
        .navbar-custom {
            background: rgba(0, 0, 0, 0.82) !important;
            border-bottom: 3px solid var(--accent-color);
        }
        .game-title {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000;
            color: var(--accent-color);
            font-size: 1.25rem;
            margin: 0 auto;
        }
        .player-name-display, .btn-home {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid white;
            border-radius: 50px;
            padding: 8px 15px;
            color: white !important;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
        }
        .player-name-display:hover, .btn-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        .challenge-container {
            background: rgba(0, 0, 0, 0.73);
            border-radius: 18px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 5px solid var(--challenge-color);
            box-shadow: 0 0 25px rgba(176, 38, 255, 0.18);
        }
        .challenge-header {
            border-bottom: 2px solid var(--challenge-color);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .challenge-title {
            font-family: 'Press Start 2P', cursive;
            color: var(--accent-color);
            font-size: 1.35rem;
        }
        .badge-level {
            background-color: var(--challenge-color);
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
        }
        .reference-card {
            background: rgba(15, 23, 42, 0.92);
            border: 2px solid rgba(0, 255, 157, 0.65);
            border-radius: 16px;
            padding: 1rem;
            height: 100%;
        }
        .reference-card img {
            width: 100%;
            border-radius: 12px;
            background: white;
        }
        .objectives-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 0.75rem;
            padding-left: 0;
            list-style: none;
        }
        .objectives-list li {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 0.75rem;
        }
        .objectives-list i { color: var(--accent-color); margin-right: 0.35rem; }
        .editor-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 2rem;
        }
        .code-editor, .result-panel {
            flex: 1;
            min-width: 320px;
            height: 520px;
            border-radius: 10px;
            overflow: hidden;
        }
        .CodeMirror {
            height: 100% !important;
            font-size: 14px;
            border: 3px solid var(--accent-color);
            border-radius: 10px;
        }
        .result-iframe {
            width: 100%;
            height: 100%;
            background: white;
            border: 3px solid var(--accent-color);
            border-radius: 10px;
        }
        .action-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            background: rgba(2, 6, 23, 0.92);
            border: 2px solid rgba(0, 255, 157, 0.45);
            border-radius: 16px;
            padding: 0.8rem;
            margin: 1.5rem 0;
        }
        .btn-run, .btn-submit-challenge {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-submit-challenge {
            background: linear-gradient(45deg, #16a34a, var(--accent-color));
            color: #07131f;
        }
        .btn-run:hover, .btn-submit-challenge:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.35);
        }
        .hint-box {
            background: rgba(255, 204, 0, 0.13);
            border-left: 4px solid var(--warning-color);
            padding: 1rem;
            border-radius: 0 10px 10px 0;
            margin: 1rem 0;
        }
        .validation-panel {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .validation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.5rem;
        }
        .validation-item {
            padding: 0.55rem 0.7rem;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            font-size: 0.95rem;
        }
        .validation-item.ok { color: #86efac; }
        .validation-item.missing { color: #fca5a5; }
        .feedback {
            min-height: 50px;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            display: none;
        }
        .success-feedback {
            background: rgba(40, 167, 69, 0.2);
            border-left: 4px solid #28a745;
            display: block;
        }
        .error-feedback {
            background: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
            display: block;
        }
        .modal-content {
            background: rgba(0, 0, 0, 0.92);
            color: white;
            border: 3px solid var(--accent-color);
            border-radius: 15px;
        }
        .modal-header { border-bottom: 2px solid var(--accent-color); }
        .modal-footer { border-top: 2px solid var(--accent-color); }
        .btn-close { filter: invert(1); }
        @media (max-width: 768px) {
            .game-title { font-size: 0.9rem; }
            .challenge-title { font-size: 1rem; }
            .challenge-container { padding: 1rem; }
            .code-editor, .result-panel { height: 430px; min-width: 100%; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand btn-home" href="{{ route('alumno.desafios.programacion') }}">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
            <div class="game-title">CODEPLAY</div>
            <div class="player-name-display">
                <i class="fas fa-user me-2"></i>
                {{ session('usuario_nombre', 'Jugador') }}
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="challenge-container animate__animated animate__fadeIn">
            <div class="challenge-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="challenge-title mb-2">Desafío 16: Página Web Personal</h1>
                        <p class="mb-0 text-white-50">Proyecto integrado: combina estructura, diseño e interacción en una sola página.</p>
                    </div>
                    <div>
                        <span class="badge badge-level">NIVEL 16</span>
                        <span class="badge bg-success ms-2">400 puntos</span>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <h3><i class="fas fa-bullseye me-2"></i>Objetivo</h3>
                    <p>
                        Crea una página web personal, como si fuera tu mini portafolio. Debe tener una presentación,
                        navegación, secciones claras, una imagen, tarjetas de proyectos, formulario de contacto,
                        estilo propio y una pequeña acción con JavaScript. Aquí ya no se trata de escribir una sola etiqueta:
                        se trata de armar una idea completa.
                    </p>
                    <div class="hint-box">
                        <strong>Consejo:</strong> piensa primero en el tema de tu página. Puede ser sobre ti, un personaje inventado,
                        un gamer, un artista, un creador de robots o alguien que quiere mostrar sus proyectos.
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="reference-card">
                        <h5 class="mb-3"><i class="fas fa-image me-2"></i>Referencia visual</h5>
                        <img src="{{ asset('Imagenes/desafio16_pagina_personal_referencia.svg') }}" alt="Ejemplo de página web personal">
                        <small class="text-white-50 d-block mt-2">No tiene que quedar idéntica, pero sí debe sentirse como una página completa.</small>
                    </div>
                </div>
            </div>

            <h3 class="mb-3"><i class="fas fa-list-check me-2"></i>Requisitos para completar el desafío</h3>
            <ul class="objectives-list mb-4">
                <li><i class="fas fa-check"></i>Estructura HTML completa.</li>
                <li><i class="fas fa-check"></i>Menú <code>&lt;nav&gt;</code> con enlaces internos.</li>
                <li><i class="fas fa-check"></i>Sección principal tipo presentación o hero.</li>
                <li><i class="fas fa-check"></i>Una imagen usando <code>&lt;img&gt;</code>.</li>
                <li><i class="fas fa-check"></i>Sección “Sobre mí”.</li>
                <li><i class="fas fa-check"></i>Lista de habilidades con viñetas.</li>
                <li><i class="fas fa-check"></i>Mínimo dos tarjetas de proyecto.</li>
                <li><i class="fas fa-check"></i>Formulario con nombre, mensaje y botón.</li>
                <li><i class="fas fa-check"></i>Colores, espacios y bordes redondeados con CSS.</li>
                <li><i class="fas fa-check"></i>Diseño con Flexbox o Grid.</li>
                <li><i class="fas fa-check"></i>Adaptación sencilla para pantallas pequeñas.</li>
                <li><i class="fas fa-check"></i>Una interacción con JavaScript.</li>
            </ul>


            <div class="editor-container">
                <div class="code-editor">
                    <textarea id="codeEditor"><!DOCTYPE html>
<html>
<head>
    <title>Mi Página Web Personal</title>
    <style>
        /* Diseña aquí tu página */
    </style>
</head>
<body>
    <!-- Construye aquí tu página personal -->

    <script>
        // Agrega aquí una pequeña interacción
    </script>
</body>
</html></textarea>
                </div>
                <div class="result-panel">
                    <iframe id="resultFrame" class="result-iframe" sandbox="allow-scripts allow-modals"></iframe>
                </div>
            </div>

            <div class="validation-panel">
                <h5 class="mb-3"><i class="fas fa-clipboard-check me-2"></i>Progreso de objetivos</h5>
                <div id="validationGrid" class="validation-grid"></div>
            </div>

            <div class="action-toolbar">
                <button type="button" class="btn btn-run" onclick="runCode()">
                    <i class="fas fa-play me-2"></i>Ejecutar Código
                </button>
                <button type="button" class="btn btn-submit-challenge" onclick="checkChallenge()">
                    <i class="fas fa-check-circle me-2"></i>Enviar Solución
                </button>
                <span class="text-white-50 small">Primero ejecuta tu código y luego envía la solución cuando el progreso esté completo.</span>
            </div>

            <div id="feedback" class="feedback"></div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trophy me-2 text-warning"></i>¡Proyecto completado!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-medal fa-5x text-warning mb-3"></i>
                        <h3>¡Tu página personal está lista!</h3>
                        <p>Combinaste HTML, CSS y JavaScript en un proyecto completo.</p>
                        <div class="alert alert-success">
                            <i class="fas fa-coins me-2"></i>
                            Recompensa: <strong>{{ $infoRecompensa['puntos_base'] ?? 400 }} puntos</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>Continuar
                    </button>
                    <a href="{{ route('alumno.desafios.programacion') }}" class="btn btn-primary">
                        <i class="fas fa-map me-2"></i>Volver a desafíos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script>
        const editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            mode: 'htmlmixed',
            theme: 'dracula',
            lineNumbers: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true
        });

        let lastPreparedPreviewCode = '';
        let restoringPreview = false;

        editor.on('change', () => updateValidation(false));

        function preparePreviewCode(code) {
            const baseTag = '<base href="about:blank" target="_self">';
            const protectionScript = `
                <script>
                    document.documentElement.setAttribute('data-codeplay-preview', 'nivel16');

                    function protegerVistaPrevia() {
                        document.querySelectorAll('form').forEach(function(form) {
                            form.setAttribute('action', 'javascript:void(0)');
                        });
                    }

                    document.addEventListener('DOMContentLoaded', protegerVistaPrevia);

                    document.addEventListener('submit', function(event) {
                        event.preventDefault();
                        protegerVistaPrevia();
                    }, true);

                    document.addEventListener('click', function(event) {
                        const enlace = event.target.closest('a');
                        if (enlace) {
                            const href = enlace.getAttribute('href') || '';
                            if (href.trim() === '' || href.startsWith('http') || href.startsWith('/') || href.startsWith('alumno')) {
                                event.preventDefault();
                                return;
                            }
                        }

                        const boton = event.target.closest('button');
                        if (boton && boton.closest('form')) {
                            protegerVistaPrevia();
                        }
                    }, true);
                <\/script>
            `;

            let previewCode = code;

            if (previewCode.toLowerCase().includes('<head>')) {
                previewCode = previewCode.replace(/<head>/i, '<head>' + baseTag);
            } else {
                previewCode = baseTag + previewCode;
            }

            if (previewCode.toLowerCase().includes('</body>')) {
                return previewCode.replace(/<\/body>/i, protectionScript + '</body>');
            }

            return previewCode + protectionScript;
        }

        function runCode() {
            const code = editor.getValue();
            const resultFrame = document.getElementById('resultFrame');
            lastPreparedPreviewCode = preparePreviewCode(code);
            resultFrame.srcdoc = lastPreparedPreviewCode;
            updateValidation(false);
        }

        function restorePreviewIfNeeded() {
            const resultFrame = document.getElementById('resultFrame');

            if (!lastPreparedPreviewCode || restoringPreview) {
                return;
            }

            setTimeout(() => {
                try {
                    const previewDocument = resultFrame.contentDocument;
                    const isPreview = previewDocument && previewDocument.documentElement && previewDocument.documentElement.dataset.codeplayPreview === 'nivel16';

                    if (!isPreview) {
                        restoringPreview = true;
                        resultFrame.srcdoc = lastPreparedPreviewCode;
                        setTimeout(() => restoringPreview = false, 150);
                    }
                } catch (error) {
                    restoringPreview = true;
                    resultFrame.srcdoc = lastPreparedPreviewCode;
                    setTimeout(() => restoringPreview = false, 150);
                }
            }, 80);
        }

        function countMatches(text, regex) {
            const matches = text.match(regex);
            return matches ? matches.length : 0;
        }

        function evaluateCode() {
            const code = editor.getValue();
            const lowerCode = code.toLowerCase();
            const projectCards = countMatches(lowerCode, /class\s*=\s*["'][^"']*(proyecto|project|card|tarjeta)[^"']*["']/g);

            return [
                {
                    label: 'Menú de navegación',
                    ok: lowerCode.includes('<nav') && lowerCode.includes('<a') && lowerCode.includes('href=')
                },
                {
                    label: 'Presentación principal',
                    ok: lowerCode.includes('<header') || lowerCode.includes('class="hero') || lowerCode.includes("class='hero") || lowerCode.includes('id="inicio') || lowerCode.includes("id='inicio")
                },
                {
                    label: 'Imagen visible',
                    ok: lowerCode.includes('<img') && lowerCode.includes('src=') && lowerCode.includes('alt=')
                },
                {
                    label: 'Sección Sobre mí',
                    ok: lowerCode.includes('sobre mí') || lowerCode.includes('sobre mi') || lowerCode.includes('about')
                },
                {
                    label: 'Lista de habilidades',
                    ok: lowerCode.includes('<ul') && lowerCode.includes('<li') && countMatches(lowerCode, /<li[\s>]/g) >= 3
                },
                {
                    label: 'Dos tarjetas de proyecto',
                    ok: projectCards >= 2 || countMatches(lowerCode, /<article[\s>]/g) >= 2
                },
                {
                    label: 'Formulario de contacto',
                    ok: lowerCode.includes('<form') && lowerCode.includes('<input') && lowerCode.includes('<textarea') && lowerCode.includes('<button')
                },
                {
                    label: 'CSS visual completo',
                    ok: lowerCode.includes('background-color') && lowerCode.includes('color:') && lowerCode.includes('padding') && lowerCode.includes('border-radius')
                },
                {
                    label: 'Diseño con Flexbox o Grid',
                    ok: lowerCode.includes('display: flex') || lowerCode.includes('display:flex') || lowerCode.includes('display: grid') || lowerCode.includes('display:grid')
                },
                {
                    label: 'Separación entre elementos',
                    ok: lowerCode.includes('gap') || lowerCode.includes('margin')
                },
                {
                    label: 'Adaptación responsive',
                    ok: lowerCode.includes('@media') || lowerCode.includes('flex-wrap') || lowerCode.includes('grid-template-columns')
                },
                {
                    label: 'Interacción con JavaScript',
                    ok: lowerCode.includes('addeventlistener') || lowerCode.includes('onclick=') || lowerCode.includes('alert(') || lowerCode.includes('textcontent')
                }
            ];
        }

        function updateValidation(showMessage = true) {
            const checks = evaluateCode();
            const grid = document.getElementById('validationGrid');
            grid.innerHTML = checks.map(check => `
                <div class="validation-item ${check.ok ? 'ok' : 'missing'}">
                    <i class="fas ${check.ok ? 'fa-check-circle' : 'fa-circle-xmark'} me-2"></i>${check.label}
                </div>
            `).join('');

            if (showMessage) {
                const completed = checks.filter(check => check.ok).length;
                return { allOk: completed === checks.length, completed, total: checks.length };
            }

            return { allOk: checks.every(check => check.ok), completed: checks.filter(check => check.ok).length, total: checks.length };
        }

        async function checkChallenge() {
            runCode();
            const code = editor.getValue();
            const result = updateValidation(true);
            const feedback = document.getElementById('feedback');

            if (!result.allOk) {
                feedback.className = 'feedback error-feedback';
                feedback.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Todavía faltan objetivos. Llevas <strong>${result.completed}/${result.total}</strong>. Revisa la lista de progreso y completa los puntos marcados en rojo.
                `;
                return;
            }

            try {
                const response = await fetch('{{ route("desafios.procesar-recompensa") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ nivel: 16, codigo: code })
                });

                const data = await response.json();

                if (data.success) {
                    feedback.className = 'feedback success-feedback';
                    feedback.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        ¡Excelente! Completaste el proyecto integrado y ganaste ${data.puntos_otorgados || 400} puntos.
                    `;
                    confetti({ particleCount: 180, spread: 80, origin: { y: 0.6 } });
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    feedback.className = 'feedback error-feedback';
                    feedback.innerHTML = `<i class="fas fa-info-circle me-2"></i>${data.message || 'Este desafío ya fue completado anteriormente.'}`;
                }
            } catch (error) {
                feedback.className = 'feedback error-feedback';
                feedback.innerHTML = '<i class="fas fa-wifi me-2"></i>No se pudo registrar la recompensa. Intenta de nuevo.';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const resultFrame = document.getElementById('resultFrame');
            resultFrame.addEventListener('load', restorePreviewIfNeeded);
            runCode();
        });
    </script>
</body>
</html>
