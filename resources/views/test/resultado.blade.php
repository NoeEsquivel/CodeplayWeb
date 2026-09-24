<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Resultados Test</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
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
        
        .resultado-container {
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .puntaje-display {
            font-size: 3rem;
            font-weight: bold;
            color: var(--accent-color);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .respuesta-correcta {
            background: rgba(0, 255, 157, 0.1) !important;
            border-left: 4px solid #28a745 !important;
        }
        
        .respuesta-incorrecta {
            background: rgba(220, 53, 69, 0.1) !important;
            border-left: 4px solid #dc3545 !important;
        }
        
        .pregunta-resultado {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0, 0, 0, 0.8); border-bottom: 3px solid var(--accent-color);">
        <div class="container-fluid">
            <a class="btn btn-outline-light me-2" href="{{ route('alumno.documentacion') }}">
                <i class="fas fa-arrow-left"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            <span class="navbar-brand mb-0 h1" style="font-family: 'Press Start 2P', cursive; color: var(--accent-color);">
                CODEPLAY
            </span>
            <div class="ms-auto">
                <div class="text-white">
                    <i class="fas fa-user me-2"></i>
                    {{ session('usuario_nombre') }}
                    <span class="badge bg-success ms-2">Alumno</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="resultado-container">
                    <div class="text-center mb-4">
                        <h1><i class="fas fa-chart-bar me-2"></i>Resultados del Test</h1>
                        
                        <div class="puntaje-display">
                            {{ $puntaje }}/50
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-coins me-2"></i>
                            ¡Has ganado <strong>{{ $puntaje }} puntos</strong> que se han sumado a tu total!
                        </div>
                    </div>

                    @foreach($resultados as $index => $resultado)
                    <div class="pregunta-resultado {{ $resultado['es_correcta'] ? 'respuesta-correcta' : 'respuesta-incorrecta' }}">
                        <h5 class="mb-3">
                            <span class="badge {{ $resultado['es_correcta'] ? 'bg-success' : 'bg-danger' }} me-2">
                                {{ $index + 1 }}
                            </span>
                            {{ $resultado['pregunta'] }}
                        </h5>
                        
                        <div class="mb-2">
                            <strong>Tu respuesta:</strong> 
                            <span class="{{ $resultado['es_correcta'] ? 'text-success' : 'text-danger' }}">
                                {{ strtoupper($resultado['respuesta_usuario']) }}. 
                                {{ $resultado['opciones'][$resultado['respuesta_usuario']] }}
                                @if(!$resultado['es_correcta'])
                                <i class="fas fa-times ms-1"></i>
                                @endif
                            </span>
                        </div>
                        
                        @if(!$resultado['es_correcta'])
                        <div class="mb-2">
                            <strong>Respuesta correcta:</strong> 
                            <span class="text-success">
                                {{ strtoupper($resultado['respuesta_correcta']) }}. 
                                {{ $resultado['opciones'][$resultado['respuesta_correcta']] }}
                                <i class="fas fa-check ms-1"></i>
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    <div class="text-center mt-4">
                        <a href="{{ route('alumno.documentacion') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Volver a Documentación
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
