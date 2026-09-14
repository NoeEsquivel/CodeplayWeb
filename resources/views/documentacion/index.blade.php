<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Documentación</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
        
        .doc-btn {
            background: rgba(0, 0, 0, 0.7);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            transition: all 0.3s;
            margin-bottom: 1rem;
            text-align: left;
            padding: 1.5rem;
            color: white;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        
        .doc-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 255, 157, 0.3);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            text-decoration: none;
        }
        
        .badge-language {
            font-size: 0.7rem;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .html-badge { background-color: #e34c26; }
        .css-badge { background-color: #2965f1; }
        .js-badge { background-color: #f0db4f; color: #000; }
        .php-badge { background-color: #787cb5; }
        .bootstrap-badge { background-color: #7952b3; }
        .concept-badge { background-color: #00a1ff; }
        .python-badge { background-color: #3776ab; }
        .java-badge { background-color: #f89820; color: #000; }
        .robot-badge { background-color: #20c997; color: #000; }
        .game-badge { background-color: #ff4d6d; }
        
        .doc-title {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .section-title {
            color: var(--accent-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <!-- Botón de Inicio + Logo -->
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="https://img.icons8.com/nolan/48/code.png" alt="Logo" width="40" height="40">
            
            <!-- Título Centrado -->
            <span class="game-title">CODEPLAY</span>
            
            <!-- Nombre del jugador -->
            <div class="player-name-display">
                <i class="fas fa-user me-2"></i>
                <span id="playerNameDisplay">{{ session('usuario_nombre') }}</span>
                <span class="badge bg-success ms-2">Alumno</span>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="text-center mb-4 doc-title"><i class="fas fa-book me-2"></i>DOCUMENTACIÓN</h2>
                
                <!-- Sección: Fundamentos de HTML -->
                <h3 class="section-title"><i class="fas fa-code me-2"></i>Fundamentos de HTML</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'html1') }}" class="doc-btn w-100">
                            <h4 class="text-white">1. Introducción a HTML
                                <span class="badge html-badge badge-language">HTML</span>
                            </h4>
                            <p class="mb-0 text-white-50">Estructura básica de un documento HTML</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'html2') }}" class="doc-btn w-100">
                            <h4 class="text-white">2. Etiquetas Fundamentales
                                <span class="badge html-badge badge-language">HTML</span>
                            </h4>
                            <p class="mb-0 text-white-50">h1, p, div, span y más</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'html3') }}" class="doc-btn w-100">
                            <h4 class="text-white">3. Enlaces e Imágenes
                                <span class="badge html-badge badge-language">HTML</span>
                            </h4>
                            <p class="mb-0 text-white-50">Cómo insertar enlaces e imágenes en HTML</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'html4') }}" class="doc-btn w-100">
                            <h4 class="text-white">4. Formularios HTML
                                <span class="badge html-badge badge-language">HTML</span>
                            </h4>
                            <p class="mb-0 text-white-50">Creación de formularios básicos</p>
                        </a>
                    </div>
                </div>
                
                <!-- Sección: Estilos con CSS -->
                <h3 class="section-title"><i class="fas fa-paint-brush me-2"></i>Diseño Visual con CSS</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'css1') }}" class="doc-btn w-100">
                            <h4 class="text-white">5. Introducción a CSS
                                <span class="badge css-badge badge-language">CSS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Colores, estilos y apariencia de una página</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'css2') }}" class="doc-btn w-100">
                            <h4 class="text-white">6. Colores, Fuentes y Fondos
                                <span class="badge css-badge badge-language">CSS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Personalizar el estilo visual de un sitio</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'css3') }}" class="doc-btn w-100">
                            <h4 class="text-white">7. Espacios, Bordes y Cajas
                                <span class="badge css-badge badge-language">CSS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Margin, padding, border y tamaño de elementos</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'css4') }}" class="doc-btn w-100">
                            <h4 class="text-white">8. Diseños Flexibles
                                <span class="badge css-badge badge-language">CSS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Acomodar tarjetas, menús y secciones con Flexbox</p>
                        </a>
                    </div>
                </div>
                
                <!-- Sección: JavaScript Básico -->
                <h3 class="section-title"><i class="fab fa-js-square me-2"></i>JavaScript Básico</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'js1') }}" class="doc-btn w-100">
                            <h4 class="text-white">9. ¿Qué es JavaScript?
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">La parte que permite que una página responda</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'js2') }}" class="doc-btn w-100">
                            <h4 class="text-white">10. Variables y Decisiones
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Guardar datos y tomar decisiones simples</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'js3') }}" class="doc-btn w-100">
                            <h4 class="text-white">11. Botones y Eventos
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Hacer que una página reaccione a clics</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'js4') }}" class="doc-btn w-100">
                            <h4 class="text-white">12. Mini Quiz con JavaScript
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Preguntas, respuestas y puntaje en pantalla</p>
                        </a>
                    </div>
                </div>
                
                <!-- Sección: Mundos de la Programación -->
                <h3 class="section-title"><i class="fas fa-globe-americas me-2"></i>Mundos de la Programación</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'mundo1') }}" class="doc-btn w-100">
                            <h4 class="text-white">13. Tipos de Lenguajes
                                <span class="badge concept-badge badge-language">MAPA</span>
                                <span class="badge python-badge badge-language">PYTHON</span>
                                <span class="badge java-badge badge-language">JAVA</span>
                            </h4>
                            <p class="mb-0 text-white-50">HTML, CSS, JavaScript, Python, Java, PHP y más</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'mundo2') }}" class="doc-btn w-100">
                            <h4 class="text-white">14. Frontend, Backend y Bases de Datos
                                <span class="badge concept-badge badge-language">CONCEPTOS</span>
                                <span class="badge php-badge badge-language">BACKEND</span>
                            </h4>
                            <p class="mb-0 text-white-50">Qué ve el usuario y qué ocurre detrás</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'mundo3') }}" class="doc-btn w-100">
                            <h4 class="text-white">15. Programación de Software
                                <span class="badge concept-badge badge-language">APPS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Cómo se crean programas para computadora y celular</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'mundo4') }}" class="doc-btn w-100">
                            <h4 class="text-white">16. Programación de Robots y Componentes
                                <span class="badge robot-badge badge-language">ROBOTS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Sensores, motores, placas y código que mueve cosas reales</p>
                        </a>
                    </div>

                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'mundo5') }}" class="doc-btn w-100">
                            <h4 class="text-white">17. Videojuegos e Interactividad
                                <span class="badge game-badge badge-language">JUEGOS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Personajes, reglas, colisiones, controles y puntos</p>
                        </a>
                    </div>
                </div>
                
                <!-- Sección: Proyectos Integradores -->
                <h3 class="section-title"><i class="fas fa-rocket me-2"></i>Proyectos Integradores</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'proyecto1') }}" class="doc-btn w-100">
                            <h4 class="text-white">18. Página Web Personal
                                <span class="badge html-badge badge-language">HTML</span>
                                <span class="badge css-badge badge-language">CSS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Crear una página propia con estilo e identidad</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'proyecto2') }}" class="doc-btn w-100">
                            <h4 class="text-white">19. Menú de VideoJuego
                                <span class="badge html-badge badge-language">HTML</span>
                                <span class="badge css-badge badge-language">CSS</span>
                                <span class="badge game-badge badge-language">JUEGO</span>
                            </h4>
                            <p class="mb-0 text-white-50">Diseñar una pantalla inicial con opciones estilo gamer</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'proyecto3') }}" class="doc-btn w-100">
                            <h4 class="text-white">20. Tarjeta Interactiva
                                <span class="badge html-badge badge-language">HTML</span>
                                <span class="badge css-badge badge-language">CSS</span>
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Una tarjeta que cambia al presionar un botón</p>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'proyecto4') }}" class="doc-btn w-100">
                            <h4 class="text-white">21. Mini Quíz con JavaScript
                                <span class="badge js-badge badge-language">JS</span>
                            </h4>
                            <p class="mb-0 text-white-50">Preguntas, opciones, puntaje y resultado final</p>
                        </a>
                    </div>

                    <div class="col-md-6 mb-4">
                        <a href="{{ route('alumno.documentacion.show', 'proyecto5') }}" class="doc-btn w-100">
                            <h4 class="text-white">22. Creemos un juego "La culebrita"
                                <span class="badge html-badge badge-language">HTML</span>
                                <span class="badge js-badge badge-language">JS</span>
                                <span class="badge game-badge badge-language">JUEGO</span>
                            </h4>
                            <p class="mb-0 text-white-50">Crear una versión simple del clásico juego de la culebrita</p>
                        </a>
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

        // Agregar event listeners a los enlaces
        document.addEventListener('DOMContentLoaded', function() {
            const docLinks = document.querySelectorAll('.doc-btn');
            
            docLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    navigateWithAnimation(url);
                });
            });
        });
    </script>
</body>
</html>
