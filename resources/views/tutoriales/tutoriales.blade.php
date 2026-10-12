<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Tutoriales Interactivos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="{{ asset('vendor/animate/animate.min.css') }}">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #00ff9d;
            --beginner-color: #4CAF50;
            --intermediate-color: #FFC107;
            --advanced-color: #F44336;
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
        
        .tutorial-card {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            transition: all 0.3s;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 2px solid transparent;
        }
        
        .tutorial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 255, 157, 0.3);
            border-color: var(--accent-color);
        }
        
        .tutorial-header {
            padding: 1rem;
            border-bottom: 3px solid;
        }
        
        .beginner {
            border-color: var(--beginner-color);
        }
        
        .intermediate {
            border-color: var(--intermediate-color);
        }
        
        .advanced {
            border-color: var(--advanced-color);
        }
        
        .tutorial-body {
            padding: 1.5rem;
        }
        
        .tutorial-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }
        
        .tutorial-description {
            color: #ddd;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .badge-difficulty {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .badge-beginner {
            background-color: var(--beginner-color);
        }
        
        .badge-intermediate {
            background-color: var(--intermediate-color);
            color: #000;
        }
        
        .badge-advanced {
            background-color: var(--advanced-color);
        }
        
        .btn-tutorial {
            background: var(--accent-color);
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-tutorial:hover {
            background: #00e68a;
            transform: scale(1.05);
        }
        
        .section-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.2rem;
            color: var(--accent-color);
            margin: 2rem 0 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
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
        
        .progress-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 20px;
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            background: var(--accent-color);
            border-radius: 10px;
            transition: width 0.5s ease-in-out;
        }
        
        .tutorial-points {
            font-size: 0.9rem;
            color: var(--accent-color);
            font-weight: bold;
        }
        
        .tutorial-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }
        
        .tutorial-time {
            font-size: 0.8rem;
            color: #ccc;
            margin-top: 0.5rem;
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 20px;
            margin-bottom: 0.5rem;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(45deg, var(--accent-color), #00e68a);
            border-radius: 10px;
            height: 100%;
            transition: width 0.5s ease-in-out, background 0.5s ease;
            position: relative;
        }

        /* Efecto de brillo en la barra de progreso */
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                transparent
            );
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Quitar animación de brillo cuando esté al 100% */
        .progress-bar[style*="width: 100%"]::after {
            animation: none;
        }
    </style>
</head>
<body>
@include('partials.alumno-sidebar')
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <!-- Botón de Inicio -->
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            
            <!-- Título Centrado -->
            <span class="game-title">CODEPLAY</span>
            
            <!-- Usuario -->
            <div class="ms-auto">
                <div class="player-name-display">
                    <i class="fas fa-user me-2"></i>
                    <span>{{ session('usuario_nombre') }}</span>
                    <span class="badge bg-success ms-2">Alumno</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="text-center mb-4 animate__animated animate__fadeIn">
                    <i class="fas fa-graduation-cap me-2"></i>TUTORIALES INTERACTIVOS
                </h2>
                        
                        <!-- Barra de progreso del usuario -->
            <div class="mb-4 animate__animated animate__fadeInUp">
                <div class="d-flex justify-content-between mb-2">
                    <span>Tu Progreso en Tutoriales</span>
                    <span id="progressPercentage">0%</span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" id="tutorialProgress" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-white" id="tutorialsCount">0/10 tutoriales completados</small>
                    <div class="text-end tutorial-points">
                        <i class="fas fa-coins me-1"></i>
                        <span id="totalPoints">{{ session('puntos_disponibles') ?? 0 }}</span> Tus puntos
                    </div>
                </div>
            </div>
                
                <!-- Tutoriales para Principiantes -->
                <h3 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-seedling me-2"></i>NIVEL PRINCIPIANTE
                </h3>
                <div class="row">
                    <!-- Tutorial 1: Primeras Páginas Web -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header beginner">
                                <span class="badge badge-difficulty badge-beginner">Principiante</span>
                                <span class="float-end tutorial-points">+25 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-file-code"></i>
                                </div>
                                <h4 class="tutorial-title">Tu Primera Página Web</h4>
                                <p class="tutorial-description">Aprende a crear tu primera página HTML desde cero. Estructura básica, etiquetas esenciales y tu primer "Hola Mundo".</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>5-10 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'primer-pagina-web') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 2: Estilos Básicos -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header beginner">
                                <span class="badge badge-difficulty badge-beginner">Principiante</span>
                                <span class="float-end tutorial-points">+30 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-paint-brush"></i>
                                </div>
                                <h4 class="tutorial-title">Añadiendo Estilos CSS</h4>
                                <p class="tutorial-description">Dale vida a tu página web con colores, fuentes y diseños básicos usando CSS.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>20-25 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'estilos-css') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 3: Crear Botones -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header beginner">
                                <span class="badge badge-difficulty badge-beginner">Principiante</span>
                                <span class="float-end tutorial-points">+25 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-hand-pointer"></i>
                                </div>
                                <h4 class="tutorial-title">Creando Botones Interactivos</h4>
                                <p class="tutorial-description">Aprende a crear botones atractivos y funcionales con HTML y CSS.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>15-20 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'botones-interactivos') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 4: Navegación entre Páginas -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header beginner">
                                <span class="badge badge-difficulty badge-beginner">Principiante</span>
                                <span class="float-end tutorial-points">+25 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <h4 class="tutorial-title">Navegación entre Páginas</h4>
                                <p class="tutorial-description">Crea menús de navegación y enlaces para conectar múltiples páginas web.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>20-25 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'navegacion-paginas') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tutoriales Intermedios -->
                <h3 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-rocket me-2"></i>NIVEL INTERMEDIO
                </h3>
                <div class="row">
                    <!-- Tutorial 5: Introducción a Bootstrap -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header intermediate">
                                <span class="badge badge-difficulty badge-intermediate">Intermedio</span>
                                <span class="float-end tutorial-points">+50 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fab fa-bootstrap"></i>
                                </div>
                                <h4 class="tutorial-title">Introducción a Bootstrap</h4>
                                <p class="tutorial-description">Aprende a usar Bootstrap para crear diseños profesionales rápidamente.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>30-35 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'introduccion-bootstrap') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 6: Formularios Básicos -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header intermediate">
                                <span class="badge badge-difficulty badge-intermediate">Intermedio</span>
                                <span class="float-end tutorial-points">+45 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <h4 class="tutorial-title">Creando Formularios Web</h4>
                                <p class="tutorial-description">Diseña formularios para contacto, registro y más con HTML y CSS.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>25-30 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'formularios-web') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 7: JavaScript Básico -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header intermediate">
                                <span class="badge badge-difficulty badge-intermediate">Intermedio</span>
                                <span class="float-end tutorial-points">+60 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fab fa-js-square"></i>
                                </div>
                                <h4 class="tutorial-title">JavaScript para Principiantes</h4>
                                <p class="tutorial-description">Introducción a variables, funciones y eventos básicos en JavaScript.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>35-40 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'javascript-basico') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 8: Entorno de Desarrollo -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header intermediate">
                                <span class="badge badge-difficulty badge-intermediate">Intermedio</span>
                                <span class="float-end tutorial-points">+40 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h4 class="tutorial-title">Configurar Entorno de Desarrollo</h4>
                                <p class="tutorial-description">Aprende a instalar y configurar VS Code y herramientas esenciales.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>20-25 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'entorno-desarrollo') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tutoriales Avanzados -->
                <h3 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-crown me-2"></i>NIVEL AVANZADO
                </h3>
                <div class="row">
                    <!-- Tutorial 9: Diseño Responsivo -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header advanced">
                                <span class="badge badge-difficulty badge-advanced">Avanzado</span>
                                <span class="float-end tutorial-points">+80 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <h4 class="tutorial-title">Diseño Web Responsivo</h4>
                                <p class="tutorial-description">Aprende a crear sitios que se adapten a móviles, tablets y desktop.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>40-45 minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'diseno-responsivo') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tutorial 10: Proyecto Completo -->
                    <div class="col-md-6">
                        <div class="tutorial-card h-100">
                            <div class="tutorial-header advanced">
                                <span class="badge badge-difficulty badge-advanced">Avanzado</span>
                                <span class="float-end tutorial-points">+100 puntos</span>
                            </div>
                            <div class="tutorial-body text-center">
                                <div class="tutorial-icon">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <h4 class="tutorial-title">Tu Primer Proyecto Completo</h4>
                                <p class="tutorial-description">Desarrolla un portafolio personal aplicando todo lo aprendido.</p>
                                <div class="tutorial-time">
                                    <i class="fas fa-clock me-1"></i>60+ minutos
                                </div>
                                <button class="btn btn-tutorial mt-3" onclick="navigateWithAnimation('{{ route('tutorial.show', 'proyecto-completo') }}')">
                                    <i class="fas fa-play me-2"></i>Comenzar Tutorial
                                </button>
                            </div>
                        </div>
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
            <h3 class="mt-3">Cargando tutorial...</h3>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
<script>
    // Función para cargar el progreso real del usuario
    function cargarProgreso() {
        fetch('{{ route("tutoriales.progreso") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarBarraProgreso(data.progreso, data.tutorialesCompletados, data.totalTutoriales, data.puntos);
            } else {
                // Si hay error, mantener el progreso simulado
                const progresoSimulado = 0;
                actualizarBarraProgreso(progresoSimulado, 0, 10, {{ session('puntos_disponibles') ?? 0 }});
            }
        })
        .catch(error => {
            console.error('Error al cargar progreso:', error);
            // En caso de error, usar progreso simulado
            const progresoSimulado = 0;
            actualizarBarraProgreso(progresoSimulado, 0, 10, {{ session('puntos_disponibles') ?? 0 }});
        });
    }

    // Función para actualizar la barra de progreso con animación
    function actualizarBarraProgreso(progreso, completados, total, puntos) {
        const progressBar = document.getElementById('tutorialProgress');
        const progressPercentage = document.getElementById('progressPercentage');
        const tutorialsCount = document.getElementById('tutorialsCount');
        const totalPoints = document.getElementById('totalPoints');
        
        // Animar la barra de progreso
        let currentProgress = 0;
        const animation = setInterval(() => {
            if (currentProgress >= progreso) {
                clearInterval(animation);
                progressBar.style.width = `${progreso}%`;
                progressPercentage.textContent = `${progreso}%`;
            } else {
                currentProgress++;
                progressBar.style.width = `${currentProgress}%`;
                progressPercentage.textContent = `${currentProgress}%`;
            }
        }, 20);

        // Actualizar contadores
        tutorialsCount.textContent = `${completados}/${total} tutoriales completados`;
        totalPoints.textContent = puntos;

        // Cambiar color según el progreso
        setTimeout(() => {
            if (progreso >= 100) {
                progressBar.style.background = 'linear-gradient(45deg, #FFD700, #FFA500)';
            } else if (progreso >= 70) {
                progressBar.style.background = 'linear-gradient(45deg, #4CAF50, #8BC34A)';
            } else if (progreso >= 40) {
                progressBar.style.background = 'linear-gradient(45deg, #2196F3, #03A9F4)';
            } else {
                progressBar.style.background = 'linear-gradient(45deg, var(--accent-color), #00e68a)';
            }
        }, 600);
    }

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

    // Cargar progreso cuando la página se carga
    document.addEventListener('DOMContentLoaded', function() {
        cargarProgreso();
        
        // Actualizar progreso cada 30 segundos (por si completan un tutorial en otra pestaña)
        setInterval(cargarProgreso, 30000);
    });
</script>
</body>
</html>
