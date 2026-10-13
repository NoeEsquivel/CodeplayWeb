<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creando Botones Interactivos - CODEPLAY</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
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

        .tutorial-content {
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .step-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--accent-color);
        }

        .step-number {
            background: var(--accent-color);
            color: #000;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }

        .tutorial-image {
            border: 2px solid var(--accent-color);
            border-radius: 10px;
            margin: 1rem 0;
            max-width: 100%;
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.3);
        }

        .code-block {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            border-left: 4px solid var(--accent-color);
            overflow-x: auto;
            margin: 1rem 0;
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

        .btn-complete {
            background: var(--accent-color);
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            transition: all 0.3s;
            margin-top: 2rem;
        }

        .btn-complete:hover {
            background: #00e68a;
            transform: scale(1.05);
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            margin: 2rem 0;
            border: 3px solid var(--accent-color);
        }

        .video-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .video-thumbnail:hover {
            transform: scale(1.02);
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .play-button:hover {
            background: rgba(255, 0, 0, 1);
            transform: translate(-50%, -50%) scale(1.1);
        }

        .completed-badge {
            background: var(--accent-color);
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: bold;
            margin-left: 1rem;
        }

        .btn-complete:hover {
            background: #00e68a;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
        }

        /* Estilos para los botones de los modales */
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }


    </style>
</head>
<body>
@include('partials.alumno-sidebar')
<!-- Navbar Personalizado -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid">
        <!-- Elementos de la izquierda -->
        <div class="d-flex align-items-center">
            <!-- Botón de Inicio -->
            <a class="btn btn-home me-2" href="{{ route('alumno.tutoriales') }}">
                <i class="fas fa-arrow-left text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
        </div>

        <!-- Título Centrado -->
        <div class="d-flex justify-content-center flex-grow-1 position-absolute start-50 translate-middle-x">
            <span class="game-title">CODEPLAY</span>
        </div>

        <!-- Usuario a la derecha -->
        <div class="ms-auto">
            <div class="player-name-display">
                <i class="fas fa-user me-2"></i>
                <span>{{ session('usuario_nombre') }}</span>
                <span class="badge bg-success ms-2">Alumno</span>
            </div>
        </div>
    </div>
</nav>

    <!-- Contenido del Tutorial -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="tutorial-content">
                    <!-- Header del Tutorial -->
                    <div class="text-center mb-5">
                        <h1 class="mb-3"><i class="fas fa-hand-pointer me-2"></i>Creando Botones Interactivos</h1>
                        <p class="lead">Aprende a crear botones que se vean bien y guíen al usuario</p>
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="badge bg-success me-2">Principiante</span>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i>15-20 minutos
                            </span>
                            <span class="badge bg-info ms-2">
                                <i class="fas fa-coins me-1"></i>+{{ $tutorial->puntos_otorgados }} puntos
                            </span>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">1</div>
                            <div class="flex-grow-1">
                                <h4>¿Qué es un botón?</h4>
                                <p>Un botón es una parte de la página que invita al usuario a realizar una acción. Puede servir para enviar un formulario, abrir una sección, iniciar un juego o mostrar un mensaje.</p>
                                <div class="alert alert-info">
                                    <strong>Idea fácil:</strong> si una página fuera un videojuego, los botones serían las opciones del menú principal.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">2</div>
                            <div class="flex-grow-1">
                                <h4>Crear un botón con HTML</h4>
                                <p>La forma más directa de crear un botón es usando la etiqueta <code>&lt;button&gt;</code>.</p>
                                <div class="code-block">
                                    <span class="tag">&lt;button&gt;</span>Comenzar<span class="tag">&lt;/button&gt;</span>
                                </div>
                                <p>Este botón ya aparece en pantalla, pero todavía necesita estilo para verse más atractivo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">3</div>
                            <div class="flex-grow-1">
                                <h4>Darle estilo al botón</h4>
                                <p>Con CSS podemos cambiar color, tamaño, bordes y espacio interno. Un botón debe verse como algo que se puede presionar.</p>
                                <div class="code-block">
                                    button {<br>
                                    &nbsp;&nbsp;background: #00ff9d;<br>
                                    &nbsp;&nbsp;color: black;<br>
                                    &nbsp;&nbsp;border: none;<br>
                                    &nbsp;&nbsp;border-radius: 25px;<br>
                                    &nbsp;&nbsp;padding: 12px 30px;<br>
                                    &nbsp;&nbsp;font-weight: bold;<br>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">4</div>
                            <div class="flex-grow-1">
                                <h4>Agregar efecto al pasar el mouse</h4>
                                <p>El efecto <code>:hover</code> se activa cuando el usuario pasa el mouse encima. Esto ayuda a que el botón se sienta vivo.</p>
                                <div class="code-block">
                                    button:hover {<br>
                                    &nbsp;&nbsp;background: #00cc7d;<br>
                                    &nbsp;&nbsp;transform: scale(1.05);<br>
                                    &nbsp;&nbsp;cursor: pointer;<br>
                                    }
                                </div>
                                <p>Ese pequeño cambio le dice al usuario: “sí, puedes presionarme”.</p>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">5</div>
                            <div class="flex-grow-1">
                                <h4>Botones con diferentes propósitos</h4>
                                <p>No todos los botones comunican lo mismo. El color y el texto ayudan a entender qué pasará al hacer clic.</p>
                                <ul>
                                    <li><strong>Botón principal:</strong> iniciar, guardar, continuar.</li>
                                    <li><strong>Botón secundario:</strong> volver, cancelar, ver más tarde.</li>
                                    <li><strong>Botón de advertencia:</strong> borrar, salir, reiniciar.</li>
                                </ul>
                                <div class="alert alert-warning">
                                    Evita textos confusos como “Click aquí”. Es mejor escribir acciones claras: “Comenzar juego”, “Guardar cambios” o “Ver instrucciones”.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number">6</div>
                            <div class="flex-grow-1">
                                <h4>Mini reto</h4>
                                <p>Crea tres botones para una pantalla de videojuego: <strong>Jugar</strong>, <strong>Instrucciones</strong> y <strong>Salir</strong>. Usa colores diferentes y agrega un efecto <code>:hover</code> para cada uno.</p>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Si tus botones se entienden sin explicación, vas por buen camino.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Tutorial -->
                    <div class="step-card">
                        <div class="d-flex align-items-start">
                            <div class="step-number"><i class="fas fa-video"></i></div>
                            <div class="flex-grow-1">
                                <h4>Video Tutorial</h4>
                                <p class="mb-3">Mira este video para ver el proceso completo:</p>

                                <div class="video-container" id="videoContainer">
                                    <!-- Miniatura con diseño atractivo -->
                                    <div id="videoThumbnail" style="width: 100%; height: 100%; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; position: relative;">
                                        <div style="font-size: 4rem; margin-bottom: 1rem;">🎬</div>
                                        <h5 style="color: white; margin-bottom: 0.5rem;">Ver Video Tutorial</h5>
                                        <p style="color: rgba(255,255,255,0.8); text-align: center; max-width: 300px;">
                                            Cómo crear botones atractivos y fáciles de usar
                                        </p>
                                        <div style="position: absolute; bottom: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem;">
                                            <i class="fas fa-play me-1"></i>Reproducir
                                        </div>
                                    </div>

                                    <button class="play-button" onclick="playVideo()">
                                        <i class="fas fa-play"></i>
                                    </button>

                                    <video id="tutorialVideo" style="display: none; width: 100%; height: 100%; border-radius: 12px;" controls poster="{{ asset('imagenes/video-poster.jpg') }}">
                                        <source src="{{ route('tutorial.video', ['video' => 'Botones_interactivos.mp4']) }}" type="video/mp4">
                                        Tu navegador no soporta el elemento video.
                                    </video>
                                </div>

                                <!-- Mensaje opcional si el video no carga -->
                                <div id="videoError" style="display: none; background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; border-radius: 8px; padding: 1rem; margin-top: 1rem; text-align: center;">
                                    <i class="fas fa-exclamation-triangle me-2" style="color: #dc3545;"></i>
                                    <span>El video no está disponible en este momento. Continúa con los pasos escritos.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Completar Tutorial -->
                    @if(!$progreso || $progreso->estado !== 'completado')
                    <div class="text-center">
                        <button class="btn btn-complete" onclick="completarTutorial()">
                            <i class="fas fa-check-circle me-2"></i>Completar Tutorial
                        </button>
                    </div>
                    @else
                    <div class="text-center">
                        <div class="alert alert-success">
                            <i class="fas fa-trophy me-2"></i>
                            <strong>¡Tutorial Completado!</strong> Ya ganaste +{{ $tutorial->puntos_otorgados }} puntos por este tutorial.
                        </div>
                        <a href="{{ route('alumno.tutoriales') }}" class="btn btn-complete">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Tutoriales
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <script>
    function playVideo() {
    const thumbnail = document.getElementById('videoThumbnail');
    const playButton = document.querySelector('.play-button');
    const video = document.getElementById('tutorialVideo');
    const videoError = document.getElementById('videoError');

    // Ocultar miniatura y botón de play
    thumbnail.style.display = 'none';
    playButton.style.display = 'none';

    // Mostrar el video
    video.style.display = 'block';

    // Intentar reproducir el video
    video.play().catch(error => {
        console.error('Error al reproducir el video:', error);
        // Si hay error, mostrar mensaje
        videoError.style.display = 'block';
        video.style.display = 'none';
    });
}

    function completarTutorial() {
        mostrarModalConfirmacion();
    }

    function mostrarModalConfirmacion() {
        // Crear el modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease-out;
        `;

        modal.innerHTML = `
            <div style="
                background: rgba(0, 0, 0, 0.9);
                border: 3px solid var(--accent-color);
                border-radius: 15px;
                padding: 2rem;
                max-width: 500px;
                width: 90%;
                text-align: center;
                animation: slideDown 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                backdrop-filter: blur(10px);
            ">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                <h3 style="color: var(--accent-color); margin-bottom: 1rem;">¿Completar Tutorial?</h3>
                <p style="color: #ddd; margin-bottom: 2rem;">
                    Al completar este tutorial ganarás <strong style="color: var(--accent-color);">{{ $tutorial->puntos_otorgados }} puntos</strong>
                    que se sumarán a tu total. ¡Solo puedes completarlo una vez!
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="cerrarModal()" style="
                        background: rgba(255, 255, 255, 0.1);
                        border: 2px solid #6c757d;
                        color: white;
                        padding: 0.75rem 1.5rem;
                        border-radius: 50px;
                        font-weight: bold;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        Cancelar
                    </button>
                    <button onclick="confirmarCompletado()" style="
                        background: var(--accent-color);
                        border: 2px solid var(--accent-color);
                        color: #000;
                        padding: 0.75rem 1.5rem;
                        border-radius: 50px;
                        font-weight: bold;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <i class="fas fa-check-circle me-2"></i>¡Sí, Completar!
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Agregar estilos de animación si no existen
        if (!document.getElementById('modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'modal-styles';
            styles.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideDown {
                    from {
                        transform: translateY(-50px) scale(0.8);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0) scale(1);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(styles);
        }
    }

function cerrarModal() {
    console.log('🔴 Intentando cerrar modal...');

    // Buscar el modal más reciente (último hijo del body)
    const bodyChildren = Array.from(document.body.children);
    const modals = bodyChildren.filter(child =>
        child.style.position === 'fixed' &&
        child.style.zIndex === '10000'
    );

    if (modals.length > 0) {
        const lastModal = modals[modals.length - 1];
        console.log('✅ Modal encontrado, eliminando...');
        lastModal.style.animation = 'fadeOut 0.3s ease-in forwards';
        setTimeout(() => {
            if (lastModal.parentElement) {
                lastModal.remove();
                console.log('✅ Modal eliminado exitosamente');
            }
        }, 300);
    } else {
        console.log('❌ No se encontraron modales');
        // Fallback: eliminar cualquier overlay
        const overlays = document.querySelectorAll('body > div');
        overlays.forEach(overlay => {
            if (overlay.style.background.includes('rgba(0, 0, 0, 0.8)')) {
                overlay.remove();
            }
        });
    }
}

    function confirmarCompletado() {
        // Cerrar el modal de confirmación
        cerrarModal();

        // Mostrar modal de carga
        mostrarModalCarga();

        fetch('{{ route("tutorial.completar", "botones-interactivos") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    mostrarModalExito(data.puntos_ganados, data.puntos_totales);
                }, 1000);
            } else {
                setTimeout(() => {
                    mostrarModalError(data.error);
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            setTimeout(() => {
                mostrarModalError('Error al completar el tutorial');
            }, 1000);
        });
    }

    function mostrarModalCarga() {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10001;
        `;

        modal.innerHTML = `
            <div style="
                background: rgba(0, 0, 0, 0.9);
                border: 3px solid var(--accent-color);
                border-radius: 15px;
                padding: 2rem;
                text-align: center;
                backdrop-filter: blur(10px);
            ">
                <div class="spinner-border text-success" style="width: 3rem; height: 3rem; margin-bottom: 1rem;"></div>
                <h4 style="color: var(--accent-color);">Procesando...</h4>
                <p style="color: #ddd;">Guardando tu progreso</p>
            </div>
        `;

        document.body.appendChild(modal);
    }

    function mostrarModalExito(puntosGanados, puntosTotales) {
        // Remover modal de carga
        const modalCarga = document.querySelector('div[style*="z-index: 10001;"]');
        if (modalCarga) modalCarga.remove();

        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease-out;
        `;

        modal.innerHTML = `
            <div style="
                background: rgba(0, 0, 0, 0.9);
                border: 3px solid var(--accent-color);
                border-radius: 15px;
                padding: 2rem;
                max-width: 500px;
                width: 90%;
                text-align: center;
                animation: celebrate 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                backdrop-filter: blur(10px);
            ">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🎊</div>
                <h3 style="color: var(--accent-color); margin-bottom: 1rem;">¡Tutorial Completado!</h3>
                <div style="
                    background: rgba(0, 255, 157, 0.1);
                    border: 2px solid var(--accent-color);
                    border-radius: 10px;
                    padding: 1rem;
                    margin: 1rem 0;
                ">
                    <div style="font-size: 2rem; font-weight: bold; color: var(--accent-color);">
                        +${puntosGanados} puntos
                    </div>
                    <div style="color: #ddd; font-size: 0.9rem;">
                        Puntos totales: ${puntosTotales}
                    </div>
                </div>
                <p style="color: #ddd; margin-bottom: 2rem;">
                    ¡Felicidades! Has completado exitosamente el tutorial.
                </p>
                <button onclick="recargarPagina()" style="
                    background: var(--accent-color);
                    border: 2px solid var(--accent-color);
                    color: #000;
                    padding: 0.75rem 2rem;
                    border-radius: 50px;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.3s;
                    font-size: 1.1rem;
                ">
                    <i class="fas fa-check me-2"></i>¡Continuar!
                </button>
            </div>
        `;

        document.body.appendChild(modal);

        // Agregar animación de celebración
        if (!document.querySelector('#celebration-styles')) {
            const styles = document.createElement('style');
            styles.id = 'celebration-styles';
            styles.textContent = `
                @keyframes celebrate {
                    0% {
                        transform: scale(0.5) rotate(-5deg);
                        opacity: 0;
                    }
                    50% {
                        transform: scale(1.1) rotate(2deg);
                    }
                    100% {
                        transform: scale(1) rotate(0);
                        opacity: 1;
                    }
                }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(styles);
        }
    }

    function mostrarModalError(mensaje) {
        // Remover modal de carga
        const modalCarga = document.querySelector('div[style*="z-index: 10001;"]');
        if (modalCarga) modalCarga.remove();

        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;

        modal.innerHTML = `
            <div style="
                background: rgba(0, 0, 0, 0.9);
                border: 3px solid #dc3545;
                border-radius: 15px;
                padding: 2rem;
                max-width: 500px;
                width: 90%;
                text-align: center;
                backdrop-filter: blur(10px);
            ">
                <div style="font-size: 3rem; margin-bottom: 1rem; color: #dc3545;">⚠️</div>
                <h3 style="color: #dc3545; margin-bottom: 1rem;">Error</h3>
                <p style="color: #ddd; margin-bottom: 2rem;">${mensaje}</p>
                <button onclick="cerrarModal()" style="
                    background: #dc3545;
                    border: 2px solid #dc3545;
                    color: white;
                    padding: 0.75rem 2rem;
                    border-radius: 50px;
                    font-weight: bold;
                    cursor: pointer;
                ">
                    Cerrar
                </button>
            </div>
        `;

        document.body.appendChild(modal);
    }

    function recargarPagina() {
        window.location.reload();
    }
</script>
@include('partials.tutorial-video-controls')
</body>
</html>


