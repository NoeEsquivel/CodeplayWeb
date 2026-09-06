<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Desafíos de Programación</title>
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
            --easy-color: #4CAF50;
            --medium-color: #FFC107;
            --hard-color: #F44336;
            --master-color: #9C27B0;
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
        
        .level-card {
            height: 120px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .level-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }
        
        .level-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 1;
        }
        
        .level-card-content {
            position: relative;
            z-index: 2;
            padding: 1rem;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .level-number {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .level-title {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .level-stars {
            font-size: 0.8rem;
            color: gold;
        }
        
        .level-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-weight: bold;
        }
        
        /* Colores por dificultad */
        .level-easy {
            background: linear-gradient(135deg, var(--easy-color), #81C784);
            border: 2px solid #81C784;
        }
        
        .level-medium {
            background: linear-gradient(135deg, var(--medium-color), #FFD54F);
            border: 2px solid #FFD54F;
        }
        
        .level-hard {
            background: linear-gradient(135deg, var(--hard-color), #E57373);
            border: 2px solid #E57373;
        }
        
        .level-master {
            background: linear-gradient(135deg, var(--master-color), #BA68C8);
            border: 2px solid #BA68C8;
        }
        
        .badge-easy {
            background-color: var(--easy-color);
        }
        
        .badge-medium {
            background-color: var(--medium-color);
            color: #000;
        }
        
        .badge-hard {
            background-color: var(--hard-color);
        }
        
        .badge-master {
            background-color: var(--master-color);
        }
        
        .section-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.8rem;
            color: var(--accent-color);
            margin-bottom: 2rem;
            text-shadow: 3px 3px 0 #000;
        }
        
        .difficulty-section {
            margin-bottom: 3rem;
        }
        
        .difficulty-title {
            font-family: 'Press Start 2P', cursive;
            font-size: 1.2rem;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 1.5rem;
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
        
        .user-progress {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .progress-text {
            color: var(--accent-color);
            font-weight: bold;
        }
        
        .locked-level {
            filter: brightness(0.5);
            position: relative;
        }
        
        .locked-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: white;
            z-index: 3;
        }

        .level-card.completed {
            position: relative;
            border: 3px solid #00ff9d !important;
            box-shadow: 
                0 0 20px rgba(0, 255, 157, 0.5),
                0 0 40px rgba(0, 255, 157, 0.3),
                inset 0 0 20px rgba(0, 255, 157, 0.2);
            animation: glow 2s ease-in-out infinite alternate;
        }

        .level-card.completed::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 18px;
            background: linear-gradient(45deg, #00ff9d, #6a11cb, #2575fc, #00ff9d);
            background-size: 400% 400%;
            animation: gradientGlow 3s ease infinite;
            z-index: -1;
            opacity: 0.7;
        }

        @keyframes glow {
            from {
                box-shadow: 
                    0 0 20px rgba(0, 255, 157, 0.5),
                    0 0 40px rgba(0, 255, 157, 0.3),
                    inset 0 0 20px rgba(0, 255, 157, 0.2);
            }
            to {
                box-shadow: 
                    0 0 30px rgba(0, 255, 157, 0.8),
                    0 0 60px rgba(0, 255, 157, 0.5),
                    inset 0 0 30px rgba(0, 255, 157, 0.3);
            }
        }

        @keyframes gradientGlow {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .completed-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #00ff9d;
            color: #000;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-weight: bold;
            z-index: 3;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 255, 157, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(0, 255, 157, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 255, 157, 0);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Personalizado -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Elementos izquierda -->
            <div class="d-flex align-items-center">
                <a class="btn btn-home me-2" href="{{ route('alumno.desafios') }}">
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

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <h1 class="section-title animate__animated animate__fadeIn">
                    <i class="fas fa-laptop-code me-3"></i>DESAFÍOS DE PROGRAMACIÓN
                </h1>
                
                <!-- Progreso del Usuario -->
                <div class="user-progress animate__animated animate__fadeIn animate__delay-1s">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-start">
                            <h5 class="progress-text">
                                <i class="fas fa-trophy me-2"></i>
                                Nivel: <span id="userLevel">1</span> | 
                                Puntos: <span id="userPoints">{{ session('puntos_disponibles') ?? 0 }}</span>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="progress-text">
                                <i class="fas fa-star me-2"></i>
                                Desafíos completados: 
                                <span id="completedChallenges">{{ $desafiosCompletados ?? 0 }}</span>/20
                            </h5>
                        </div>
                    </div>
                </div>
                
                <!-- Niveles Fáciles (1-5) - Fundamentos HTML/CSS -->
                <div class="difficulty-section animate__animated animate__fadeIn">
                    <h2 class="difficulty-title bg-success">FUNDAMENTOS WEB</h2>
                    <div class="row">
                        <!-- Nivel 1: Hola Mundo -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-easy {{ in_array(1, $nivelesCompletados ?? []) ? 'completed' : '' }}" 
                                onclick="navigateToLevel(1)">
                                <div class="level-card-content">
                                    @if(in_array(1, $nivelesCompletados ?? []))
                                        <div class="completed-badge">
                                            <i class="fas fa-check me-1"></i>
                                        </div>
                                    @endif
                                    <div class="level-number">01</div>
                                    <div class="level-title">Hola Mundo</div>
                                    <div class="level-stars">50 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-easy">FÁCIL</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 2: Etiquetas HTML -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-easy {{ in_array(2, $nivelesCompletados ?? []) ? 'completed' : '' }}" onclick="navigateToLevel(2)">
                                <div class="level-card-content">
                                    @if(in_array(2, $nivelesCompletados ?? []))
                                        <div class="completed-badge">
                                            <i class="fas fa-check me-1"></i>
                                        </div>
                                    @endif
                                    <div class="level-number">02</div>
                                    <div class="level-title">Etiquetas Fundamentales</div>
                                    <div class="level-stars">50 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-easy">FÁCIL</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 3: Enlaces e Imágenes -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-easy {{ in_array(3, $nivelesCompletados ?? []) ? 'completed' : '' }}" onclick="navigateToLevel(3)">
                                <div class="level-card-content">
                                    @if(in_array(3, $nivelesCompletados ?? []))
                                        <div class="completed-badge">
                                            <i class="fas fa-check me-1"></i>
                                        </div>
                                    @endif
                                    <div class="level-number">03</div>
                                    <div class="level-title">Enlaces e Imágenes</div>
                                    <div class="level-stars">50 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-easy">FÁCIL</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 4: CSS Básico -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-easy {{ in_array(4, $nivelesCompletados ?? []) ? 'completed' : '' }}" onclick="navigateToLevel(4)">
                                <div class="level-card-content">
                                    @if(in_array(4, $nivelesCompletados ?? []))
                                        <div class="completed-badge">
                                            <i class="fas fa-check me-1"></i>
                                        </div>
                                    @endif
                                    <div class="level-number">04</div>
                                    <div class="level-title">Estilos CSS Básicos</div>
                                    <div class="level-stars">50 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-easy">FÁCIL</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 5: Modelo de Caja -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-easy {{ in_array(5, $nivelesCompletados ?? []) ? 'completed' : '' }}" onclick="navigateToLevel(5)">
                                <div class="level-card-content">
                                    @if(in_array(5, $nivelesCompletados ?? []))
                                        <div class="completed-badge">
                                            <i class="fas fa-check me-1"></i>
                                        </div>
                                    @endif
                                    <div class="level-number">05</div>
                                    <div class="level-title">Modelo de Caja CSS</div>
                                    <div class="level-stars">100 puntos+item R
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-easy">FÁCIL</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Niveles Intermedios (6-10) - CSS Avanzado y JS Básico -->
                <div class="difficulty-section animate__animated animate__fadeIn">
                    <h2 class="difficulty-title bg-warning text-dark">CSS Y JAVASCRIPT</h2>
                    <div class="row">
                        <!-- Nivel 6: Flexbox -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-medium" onclick="navigateToLevel(6)">
                                <div class="level-card-content">
                                    <div class="level-number">06</div>
                                    <div class="level-title">Diseño con Flexbox</div>
                                    <div class="level-stars">100 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-medium">MEDIO</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 7: CSS Grid -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-medium" onclick="navigateToLevel(7)">
                                <div class="level-card-content">
                                    <div class="level-number">07</div>
                                    <div class="level-title">CSS Grid Layout</div>
                                    <div class="level-stars">100 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-medium">MEDIO</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 8: JavaScript Básico -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-medium" onclick="navigateToLevel(8)">
                                <div class="level-card-content">
                                    <div class="level-number">08</div>
                                    <div class="level-title">JavaScript Básico</div>
                                    <div class="level-stars">100 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-medium">MEDIO</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 9: Eventos DOM -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-medium" onclick="navigateToLevel(9)">
                                <div class="level-card-content">
                                    <div class="level-number">09</div>
                                    <div class="level-title">Eventos del DOM</div>
                                    <div class="level-stars">100 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-medium">MEDIO</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 10: Formularios JS -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-medium" onclick="navigateToLevel(10)">
                                <div class="level-card-content">
                                    <div class="level-number">10</div>
                                    <div class="level-title">Formularios Interactivos</div>
                                    <div class="level-stars">150 puntos+Item R
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-medium">MEDIO</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Niveles Difíciles (11-15) - Bootstrap y PHP -->
                <div class="difficulty-section animate__animated animate__fadeIn">
                    <h2 class="difficulty-title bg-danger">FRAMEWORKS Y PHP</h2>
                    <div class="row">
                        <!-- Nivel 11: Bootstrap Grid -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-hard locked-level" id="level11">
                                <div class="level-card-content">
                                    <div class="level-number">11</div>
                                    <div class="level-title">Bootstrap Grid System</div>
                                    <div class="level-stars">200 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-hard">DIFÍCIL</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 12: Componentes Bootstrap -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-hard locked-level" id="level12">
                                <div class="level-card-content">
                                    <div class="level-number">12</div>
                                    <div class="level-title">Componentes Bootstrap</div>
                                    <div class="level-stars">200 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-hard">DIFÍCIL</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 13: Introducción PHP -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-hard locked-level" id="level13">
                                <div class="level-card-content">
                                    <div class="level-number">13</div>
                                    <div class="level-title">Introducción a PHP</div>
                                    <div class="level-stars">200 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-hard">DIFÍCIL</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 14: Formularios PHP -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-hard locked-level" id="level14">
                                <div class="level-card-content">
                                    <div class="level-number">14</div>
                                    <div class="level-title">Formularios con PHP</div>
                                    <div class="level-stars">200 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-hard">DIFÍCIL</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 15: Sesiones PHP -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-hard locked-level" id="level15">
                                <div class="level-card-content">
                                    <div class="level-number">15</div>
                                    <div class="level-title">Sesiones y Cookies</div>
                                    <div class="level-stars">300 puntos+Item SR
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-hard">DIFÍCIL</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Niveles Maestros (16-20) - Proyectos Integradores -->
                <div class="difficulty-section animate__animated animate__fadeIn">
                    <h2 class="difficulty-title bg-purple">PROYECTOS INTEGRADORES</h2>
                    <div class="row">
                        <!-- Nivel 16: Página Personal -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-master locked-level" id="level16">
                                <div class="level-card-content">
                                    <div class="level-number">16</div>
                                    <div class="level-title">Página Web Personal</div>
                                    <div class="level-stars">400 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-master">MAESTRO</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 17: Formulario Completo -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-master locked-level" id="level17">
                                <div class="level-card-content">
                                    <div class="level-number">17</div>
                                    <div class="level-title">Formulario Interactivo</div>
                                    <div class="level-stars">400 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-master">MAESTRO</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 18: Dashboard -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-master locked-level" id="level18">
                                <div class="level-card-content">
                                    <div class="level-number">18</div>
                                    <div class="level-title">Dashboard Responsivo</div>
                                    <div class="level-stars">400 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-master">MAESTRO</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 19: E-commerce -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-master locked-level" id="level19">
                                <div class="level-card-content">
                                    <div class="level-number">19</div>
                                    <div class="level-title">Tienda Online Básica</div>
                                    <div class="level-stars">400 puntos
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-master">MAESTRO</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nivel 20: Proyecto Final -->
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="level-card level-master locked-level" id="level20">
                                <div class="level-card-content">
                                    <div class="level-number">20</div>
                                    <div class="level-title">Proyecto Final</div>
                                    <div class="level-stars">1000 puntos+Item Unico
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="level-badge badge-master">MAESTRO</span>
                                    <div class="locked-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
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
            <h3 class="mt-3">Cargando desafío...</h3>
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

// Función para navegar a un nivel específico
function navigateToLevel(levelNumber) {
    const rutas = {
        1: "{{ route('alumno.desafios.nivel1') }}",
        2: "{{ route('alumno.desafios.nivel2') }}",
        3: "{{ route('alumno.desafios.nivel3') }}",
        4: "{{ route('alumno.desafios.nivel4') }}",
        5: "{{ route('alumno.desafios.nivel5') }}"
        // Agregar más niveles aquí cuando los crees
    };

    if (rutas[levelNumber]) {
        navigateWithAnimation(rutas[levelNumber]);
    } else {
        mostrarMensajeDesarrollo(levelNumber);
    }
}
        // Función para mostrar mensaje de desarrollo
        function mostrarMensajeDesarrollo(nivel) {
            const mensajes = {
                1: { titulo: '🏗️ Hola Mundo', mensaje: 'Próximamente: Crea tu primera estructura HTML válida', color: '#4CAF50' },
                2: { titulo: '📝 Etiquetas Fundamentales', mensaje: 'Próximamente: Domina las etiquetas HTML esenciales', color: '#4CAF50' },
                3: { titulo: '🔗 Enlaces e Imágenes', mensaje: 'Próximamente: Aprende a enlazar páginas y agregar imágenes', color: '#4CAF50' },
                4: { titulo: '🎨 Estilos CSS Básicos', mensaje: 'Próximamente: Da estilo a tus páginas con CSS', color: '#4CAF50' },
                5: { titulo: '📦 Modelo de Caja CSS', mensaje: 'Próximamente: Domina margin, padding y border', color: '#4CAF50' },
                6: { titulo: '⚡ Diseño con Flexbox', mensaje: 'Próximamente: Crea layouts flexibles con CSS', color: '#FFC107' },
                7: { titulo: '🔲 CSS Grid Layout', mensaje: 'Próximamente: Diseña con el sistema de grid moderno', color: '#FFC107' },
                8: { titulo: '⚙️ JavaScript Básico', mensaje: 'Próximamente: Variables, funciones y operadores', color: '#FFC107' },
                9: { titulo: '🎯 Eventos del DOM', mensaje: 'Próximamente: Interactúa con elementos HTML', color: '#FFC107' },
                10: { titulo: '📋 Formularios Interactivos', mensaje: 'Próximamente: Valida formularios con JavaScript', color: '#FFC107' },
                11: { titulo: '📱 Bootstrap Grid System', mensaje: 'Próximamente: Diseño responsivo con Bootstrap', color: '#F44336' },
                12: { titulo: '🧩 Componentes Bootstrap', mensaje: 'Próximamente: Botones, cards y navbar profesionales', color: '#F44336' },
                13: { titulo: '🐘 Introducción a PHP', mensaje: 'Próximamente: Primeros pasos con PHP', color: '#F44336' },
                14: { titulo: '📝 Formularios con PHP', mensaje: 'Próximamente: Procesa datos de formularios', color: '#F44336' },
                15: { titulo: '🔐 Sesiones y Cookies', mensaje: 'Próximamente: Manejo de estado en PHP', color: '#F44336' },
                16: { titulo: '👤 Página Web Personal', mensaje: 'Próximamente: Crea tu portafolio online', color: '#9C27B0' },
                17: { titulo: '📞 Formulario Interactivo', mensaje: 'Próximamente: Formulario con validación completa', color: '#9C27B0' },
                18: { titulo: '📊 Dashboard Responsivo', mensaje: 'Próximamente: Panel de control adaptable', color: '#9C27B0' },
                19: { titulo: '🛒 Tienda Online Básica', mensaje: 'Próximamente: E-commerce simple funcional', color: '#9C27B0' },
                20: { titulo: '🏆 Proyecto Final Integrador', mensaje: 'Próximamente: Demuestra todo lo aprendido', color: '#9C27B0' }
            };

            const info = mensajes[nivel] || {
                titulo: '🔧 Desafío en Desarrollo',
                mensaje: 'Esta funcionalidad estará disponible pronto',
                color: '#6c757d'
            };

            mostrarNotificacionPersonalizada(info.titulo, info.mensaje, info.color);
        }

        // Función para mostrar notificaciones personalizadas
        function mostrarNotificacionPersonalizada(titulo, mensaje, color) {
            const notificacion = document.createElement('div');
            notificacion.className = 'custom-notification';
            notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${color};
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
            `;

            notificacion.innerHTML = `
                <div style="display: flex; align-items: flex-start; gap: 15px;">
                    <div style="font-size: 1.8rem;">✨</div>
                    <div style="flex: 1;">
                        <div style="font-weight: bold; font-size: 1.1rem; margin-bottom: 5px;">${titulo}</div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">${mensaje}</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; padding: 0; margin: 0;">
                        ✕
                    </button>
                </div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background: rgba(255,255,255,0.3); border-radius: 0 0 15px 15px;">
                    <div class="progress-bar" style="height: 100%; background: rgba(255,255,255,0.8); width: 100%; border-radius: 0 0 15px 15px; animation: progress 4s linear forwards;"></div>
                </div>
            `;

            document.body.appendChild(notificacion);

            // Animación de entrada
            setTimeout(() => {
                notificacion.style.transform = 'translateX(0)';
                notificacion.style.opacity = '1';
            }, 100);

            // Auto-eliminar después de 4 segundos
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
            }, 4000);
        }

        // Cargar progreso del usuario
        function cargarProgresoUsuario() {
            const puntos = {{ session('puntos_disponibles') ?? 0 }};
            document.getElementById('userPoints').textContent = puntos;
            
            // Calcular nivel basado en puntos (100 puntos por nivel)
            const nivel = Math.floor(puntos / 100) + 1;
            document.getElementById('userLevel').textContent = nivel;
            
            // Desbloquear niveles según progreso
            const maxUnlockedLevel = Math.min(nivel + 2, 20);
            
            for (let i = 1; i <= maxUnlockedLevel; i++) {
                const levelElement = document.getElementById(`level${i}`);
                if (levelElement) {
                    levelElement.classList.remove('locked-level');
                    levelElement.querySelector('.locked-icon').style.display = 'none';
                    levelElement.onclick = function() { navigateToLevel(i); };
                }
            }
        }

        // Cargar progreso cuando la página se carga
        document.addEventListener('DOMContentLoaded', function() {
            cargarProgresoUsuario();
        });
    </script>
</body>
</html>