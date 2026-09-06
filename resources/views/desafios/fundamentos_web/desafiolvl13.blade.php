<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Desafío 13: Introducción a PHP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- CodeMirror (editor de código) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --challenge-color: #FF5722;
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
        
        .game-title {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000;
            color: var(--accent-color);
            font-size: 1.5rem;
            margin: 0 auto;
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

        .btn-home {
            background: transparent;
            border: 2px solid white;
            border-radius: 50px;
            transition: all 0.3s;
            color: white !important;
        }
        
        .btn-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        .challenge-container {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 5px solid var(--challenge-color);
        }
        
        .challenge-header {
            border-bottom: 2px solid var(--challenge-color);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .challenge-title {
            font-family: 'Press Start 2P', cursive;
            color: var(--accent-color);
            font-size: 1.5rem;
        }
        
        .badge-level {
            background-color: var(--challenge-color);
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
        }
        
        .editor-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .code-editor, .result-panel {
            flex: 1;
            min-width: 300px;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .CodeMirror {
            height: 100% !important;
            font-size: 14px;
            border: 3px solid var(--accent-color);
            border-radius: 10px;
        }
        
        .result-panel {
            background: white;
            border: 3px solid var(--accent-color);
            border-radius: 10px;
            padding: 15px;
            color: black;
            overflow-y: auto;
        }
        
        .btn-run, .btn-submit {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-top: 1rem;
            font-weight: bold;
        }
        
        .btn-run {
            background: #4CAF50;
            border: none;
            color: white;
        }
        
        .btn-run:hover {
            background: #3e8e41;
            transform: scale(1.05);
        }
        
        .btn-submit {
            background: var(--accent-color);
            border: none;
            color: #000;
        }
        
        .btn-submit:hover {
            background: #00e68a;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        
        .loading-content {
            text-align: center;
            color: var(--accent-color);
        }
        
        .spinner-custom {
            width: 5rem;
            height: 5rem;
            border-width: 6px;
        }
        
        .section-title {
            color: var(--accent-color);
            border-left: 4px solid var(--accent-color);
            padding-left: 1rem;
            margin: 1.5rem 0 1rem;
        }
        
        .objective-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        .objective-list li {
            padding: 0.5rem 0;
            padding-left: 2rem;
            position: relative;
        }
        
        .objective-list li::before {
            content: '✓';
            color: var(--accent-color);
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .hint-box {
            background: rgba(0, 0, 0, 0.5);
            border-left: 4px solid #FFC107;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 5px 5px 0;
        }
        
        .hint-title {
            color: #FFC107;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        /* Notificación personalizada */
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10000;
            max-width: 350px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
        }

        .notification-success {
            background: #198754 !important;
        }

        .notification-warning {
            background: #ffc107 !important;
            color: #000 !important;
        }

        .notification-info {
            background: #0dcaf0 !important;
            color: #000 !important;
        }
    </style>
</head>
<body>
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Elementos izquierda -->
            <div class="d-flex align-items-center">
                <a class="btn btn-home me-2" href="{{ route('alumno.desafios.programacion') }}">
                    <i class="fas fa-arrow-left text-white"></i>
                </a>
                <img src="https://img.icons8.com/nolan/48/code.png" alt="Logo" width="40" height="40">
            </div>
            
            <!-- Título Centrado -->
            <span class="game-title position-absolute start-50 translate-middle-x">CODEPLAY</span>
            
            <!-- Usuario derecha -->
            <div class="player-name-display">
                <i class="fas fa-user me-2"></i>
                <span>{{ session('usuario_nombre') }}</span>
                <span class="badge bg-success ms-2">Alumno</span>
            </div>
        </div>
    </nav>

    <!-- Contenido del Desafío -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="challenge-container animate__animated animate__fadeIn">
                    <!-- Encabezado del Desafío -->
                    <div class="challenge-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="challenge-title">Desafío 13: Introducción a PHP</h1>
                            <span class="badge badge-level">NIVEL 13</span>
                        </div>
                        <p class="text-white-50 mt-2"><i class="fas fa-coins me-2"></i>Recompensa: 200 puntos</p>
                    </div>
                    
                    <!-- Descripción del Desafío -->
                    <h3 class="section-title">Objetivo</h3>
                    <p>Escribe un ejemplo pequeño de PHP usando una variable y <code>echo</code>. La meta no es crear una aplicación completa, sino entender que PHP se ejecuta en el servidor y puede generar contenido para el navegador.</p>
                    
                    <ul class="objective-list">
                        <li>Abre el bloque con <code>&lt;?php</code></li>
                        <li>Crea una variable usando el símbolo <code>$</code></li>
                        <li>Guarda texto dentro de esa variable</li>
                        <li>Usa <code>echo</code> para mostrar un mensaje</li>
                        <li>Cierra el bloque con <code>?&gt;</code></li>
                    </ul>
                    
                    <div class="hint-box">
                        <div class="hint-title"><i class="fas fa-image me-2"></i>Referencia visual</div>
                        <p>Usa la imagen como guía del resultado o del concepto. No tiene que quedar idéntico; lo importante es aplicar las piezas correctas.</p>
                        <div class="text-center mt-3">
                            <img src="{{ asset('Imagenes/desafio13_intro_php_referencia.svg') }}" alt="Referencia visual del desafío 13" class="img-fluid rounded" style="max-height: 360px; border: 2px solid var(--accent-color); box-shadow: 0 0 18px rgba(0, 255, 136, 0.25);">
                        </div>
                    </div>
                    
                    <div class="hint-box">
                        <div class="hint-title"><i class="fas fa-lightbulb me-2"></i>Pista</div>
                        <p class="mb-0">Un ejemplo válido puede tener una variable como <code>$nombre</code> y después usar <code>echo</code> para mostrar un saludo. PHP sería procesado por el servidor antes de llegar al navegador.</p>
                    </div>
                    
                    <!-- Editor y Resultado -->
                    <h3 class="section-title">Tu Código</h3>
                    <div class="editor-container">
                        <div class="code-editor">
                            <textarea id="html-code"><?php
// Crea una variable y muestra un mensaje con echo



?></textarea>
                        </div>
                        <div class="result-panel" id="result">
                            <!-- Aquí se mostrará el resultado -->
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="text-center">
                        <button class="btn btn-run me-3" id="runBtn">
                            <i class="fas fa-play me-2"></i>Probar Código
                        </button>
                        <button class="btn btn-submit" id="submitBtn">
                            <i class="fas fa-check-circle me-2"></i>Enviar Solución
                        </button>
                    </div>
                    
                    <!-- Mensaje de Éxito (oculto inicialmente) -->
                    <div class="alert alert-success mt-3 text-center animate__animated animate__fadeIn" id="successMessage" style="display: none;">
                        <h4><i class="fas fa-check-circle me-2"></i>¡Desafío Completado!</h4>
                        <p class="mb-0">Has ganado 200 puntos. <a href="{{ route('alumno.desafios.programacion') }}" class="alert-link" id="nextLevelLink">Volver al mapa de desafíos</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay de carga -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border spinner-custom text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <h3 class="mt-3">Cargando contenido...</h3>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Inicializar el editor de código
        const editor = CodeMirror.fromTextArea(document.getElementById('html-code'), {
            mode: 'htmlmixed',
            theme: 'dracula',
            lineNumbers: true,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true,
            autoCloseTags: true
        });
        
        function escapeHtml(value) {
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function simularSalidaPhp(code) {
            const variables = {};
            const cleanCode = code
                .replace(/<\?php/gi, '')
                .replace(/\?>/g, '');
            const lines = cleanCode.split(/\r?\n/).map(line => line.trim()).filter(Boolean);

            lines.forEach(line => {
                const doubleQuoteVariable = line.match(/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*"([^"]*)"\s*;?$/);
                const singleQuoteVariable = line.match(/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*'([^']*)'\s*;?$/);
                const variableMatch = doubleQuoteVariable || singleQuoteVariable;

                if (variableMatch) {
                    variables[variableMatch[1]] = variableMatch[2];
                }
            });

            for (const line of lines) {
                const echoDoubleText = line.match(/^echo\s+"([^"]*)"\s*;?$/i);
                if (echoDoubleText) {
                    return echoDoubleText[1];
                }

                const echoSingleText = line.match(/^echo\s+'([^']*)'\s*;?$/i);
                if (echoSingleText) {
                    return echoSingleText[1];
                }

                const echoVariable = line.match(/^echo\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*;?$/i);
                if (echoVariable && Object.prototype.hasOwnProperty.call(variables, echoVariable[1])) {
                    return variables[echoVariable[1]];
                }
            }

            return 'Aquí aparecería el resultado generado por PHP en el servidor.';
        }

        // Función para mostrar el código PHP de forma segura
        function runCode() {
            const code = editor.getValue();
            const resultFrame = document.getElementById('result');
            const simulatedOutput = simularSalidaPhp(code);

            resultFrame.innerHTML = `
                <div style="font-family: Arial, sans-serif; color: #1d2433; padding: 10px;">
                    <h3 style="color: #6f42c1; font-weight: 800;">Vista de práctica PHP</h3>
                    <p>PHP no se ejecuta directamente dentro del navegador. Primero lo interpreta el servidor y después envía HTML como respuesta.</p>
                    <div style="background: #101827; color: #d7ffe9; border-radius: 12px; padding: 14px; white-space: pre-wrap; font-family: Consolas, monospace; font-size: 14px;">${escapeHtml(code)}</div>
                    <div style="margin-top: 14px; padding: 12px; border-radius: 12px; background: #e9fff4; border: 2px solid #00b76f;">
                        <strong>Salida simulada:</strong> ${escapeHtml(simulatedOutput)}
                    </div>
                </div>
            `;
        }

        // Función para mostrar notificación bonita
        function mostrarNotificacion(titulo, mensaje, tipo = 'info') {
            const notificacion = document.createElement('div');
            notificacion.className = `custom-notification notification-${tipo}`;
            
            notificacion.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <div style="font-size: 1.8rem;">
                        ${tipo === 'success' ? '🎉' : tipo === 'warning' ? '⚠️' : tipo === 'danger' ? '❌' : 'ℹ️'}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 5px;">${titulo}</div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">${mensaje}</div>
                    </div>
                </div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: rgba(255,255,255,0.3); border-radius: 0 0 15px 15px;">
                    <div class="progress-bar" style="height: 100%; background: rgba(255,255,255,0.8); width: 100%; border-radius: 0 0 15px 15px; animation: progress 5s linear forwards;"></div>
                </div>
            `;

            document.body.appendChild(notificacion);

            // Animación de entrada
            setTimeout(() => {
                notificacion.style.transform = 'translateX(0)';
                notificacion.style.opacity = '1';
            }, 100);

            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                if (notificacion.parentElement) {
                    notificacion.style.transform = 'translateX(400px)';
                    notificacion.style.opacity = '0';
                    setTimeout(() => {
                        if (notificacion.parentElement) {
                            notificacion.remove();
                        }
                    }, 500);
                }
            }, 5000);
        }

        // Función para lanzar confeti
        function lanzarConfeti() {
            confetti({
                particleCount: 200,
                spread: 100,
                origin: { y: 0.6 },
                colors: ['#00ff9d', '#6a11cb', '#2575fc', '#FF5722', '#FFC107', '#9C27B0'],
                startVelocity: 30,
                gravity: 0.8,
                scalar: 1.2
            });

            // Lanzar confeti adicional después de un delay
            setTimeout(() => {
                confetti({
                    particleCount: 100,
                    angle: 60,
                    spread: 80,
                    origin: { x: 0, y: 0.6 },
                    colors: ['#00ff9d', '#6a11cb', '#2575fc']
                });
            }, 250);

            setTimeout(() => {
                confetti({
                    particleCount: 100,
                    angle: 120,
                    spread: 80,
                    origin: { x: 1, y: 0.6 },
                    colors: ['#FF5722', '#FFC107', '#9C27B0']
                });
            }, 500);
        }

// Función para verificar el desafío (versión con botón manual)
async function checkChallenge() {
    const code = editor.getValue();
    const lowerCode = code.toLowerCase();
    const compactCode = lowerCode.replace(/\s+/g, '');
    const simulatedOutput = simularSalidaPhp(code).trim();
    const fallbackOutput = 'Aquí aparecería el resultado generado por PHP en el servidor.';
    const requiredChecks = [
        { label: '<?php', ok: lowerCode.includes('<?php') },
        { label: 'variable con $', ok: code.includes('$') },
        { label: 'echo', ok: lowerCode.includes('echo') },
        { label: 'texto entre comillas', ok: /["'][^"']+["']/.test(code) },
        { label: '?>', ok: lowerCode.includes('?>') },
        { label: 'una salida simulada válida', ok: simulatedOutput !== '' && simulatedOutput !== fallbackOutput }
    ];
    const missingTags = requiredChecks.filter(check => !check.ok).map(check => check.label);

    if (missingTags.length === 0) {
        // Desafío completado - procesar recompensa
        try {
            const response = await fetch('{{ route("desafios.procesar-recompensa") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    nivel: 13
                })
            });

            const data = await response.json();

            if (data.success) {
                lanzarConfeti();
                const successMessage = document.getElementById('successMessage');
                const puntosMsg = data.completado_primera_vez ? 
                    `¡Primera vez completado! Has ganado ${data.puntos_otorgados} puntos.` :
                    `¡Desafío recompletado! Has ganado ${data.puntos_otorgados} puntos.`;
                
                successMessage.innerHTML = `
                    <h4><i class="fas fa-check-circle me-2"></i>¡Desafío Completado!</h4>
                    <p class="mb-0">${puntosMsg} Puntos totales: ${data.puntos_totales}</p>
                    <div class="mt-3">
                        <button class="btn btn-success me-2" onclick="window.location.href='{{ route('alumno.desafios.programacion') }}'">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Desafíos
                        </button>
                        <button class="btn btn-outline-light" onclick="document.getElementById('successMessage').style.display='none'">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                    </div>
                `;
                successMessage.style.display = 'block';
                mostrarNotificacion('¡Éxito!', puntosMsg + ' Puntos totales: ' + data.puntos_totales, 'success');
                if (document.getElementById('userPoints')) {
                    document.getElementById('userPoints').textContent = data.puntos_disponibles;
                }
            } else {
                if (data.error === 'recompensa_cooldown') {
                    const successMessage = document.getElementById('successMessage');
                    successMessage.innerHTML = `
                        <h4><i class="fas fa-check-circle me-2"></i>¡Desafío Completado!</h4>
                        <p class="mb-0">¡Bien hecho! Pero ya reclamaste la recompensa hoy.</p>
                        <p class="mb-0">Podrás reclamar nuevamente: ${data.tiempo_restante}</p>
                        <div class="mt-3">
                            <button class="btn btn-success me-2" onclick="window.location.href='{{ route('alumno.desafios.programacion') }}'">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Desafíos
                            </button>
                            <button class="btn btn-outline-light" onclick="document.getElementById('successMessage').style.display='none'">
                                <i class="fas fa-times me-2"></i>Cerrar
                            </button>
                        </div>
                    `;
                    successMessage.style.display = 'block';
                    lanzarConfeti();
                    mostrarNotificacion('Recompensa en espera', `¡Desafío completado! Podrás reclamar recompensa ${data.tiempo_restante}.`, 'warning');
                } else {
                    throw new Error(data.error);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('Error', 'Error al procesar recompensa: ' + error.message, 'danger');
        }
    } else {
        let errorMessage = 'Para completar el desafío necesitas usar: ';
        errorMessage += missingTags.map(tag => `<strong>${tag}</strong>`).join(', ');
        if (missingTags.includes('una salida simulada válida')) {
            errorMessage += '<br><br>Revisa que el nombre de la variable que muestras con <strong>echo</strong> sea el mismo que declaraste arriba.';
        }
        mostrarNotificacion('Faltan detalles', errorMessage, 'warning');
    }
}

        // Función para mostrar mensaje de desarrollo
        function mostrarMensajeDesarrollo(mensaje) {
            mostrarNotificacion('En Desarrollo', mensaje, 'info');
        }

        // Mostrar advertencia si ya completó el desafío
        window.addEventListener('load', function() {
            const infoRecompensa = @json($infoRecompensa);
            
            if (infoRecompensa.ya_completado) {
                let mensaje = '';
                let tipo = 'warning';
                
                if (!infoRecompensa.puede_reclamar) {
                    mensaje = `Ya completaste este desafío. Puedes practicar nuevamente, pero la próxima recompensa estará disponible ${infoRecompensa.tiempo_restante}.`;
                    tipo = 'info';
                } else {
                    mensaje = `Ya completaste este desafío. Puedes practicar nuevamente y ganar ${infoRecompensa.puntos_otorgar} puntos (mitad de la recompensa original).`;
                }
                
                mostrarNotificacion('Desafío Ya Completado', mensaje, tipo);
            }
        });

        // Event Listeners
        document.getElementById('runBtn').addEventListener('click', runCode);
        document.getElementById('submitBtn').addEventListener('click', checkChallenge);
        
        // Ejecutar el código al cargar la página para mostrar el ejemplo inicial
        window.addEventListener('load', runCode);
        
        // Función para navegar con animación
        function navigateWithAnimation(url) {
            const overlay = document.getElementById('loadingOverlay');
            
            // Mostrar animación
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
            
            // Redirigir después de 1.5 segundos
            setTimeout(() => {
                window.location.href = url;
            }, 1500);
        }

        // Agregar estilos CSS para la animación de progreso
        const style = document.createElement('style');
        style.textContent = `
            @keyframes progress {
                0% { width: 100%; }
                100% { width: 0%; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>


