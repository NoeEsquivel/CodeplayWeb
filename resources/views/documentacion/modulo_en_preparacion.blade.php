<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - {{ $modulo['titulo'] }}</title>
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
            inset: 0;
            background-color: rgba(0, 0, 0, 0.65);
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

        .module-card {
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 3rem;
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.2);
        }

        .module-number {
            color: var(--accent-color);
            font-family: 'Press Start 2P', cursive;
            font-size: 0.9rem;
        }

        .btn-success {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
@include('partials.alumno-sidebar')
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}">
                <i class="fas fa-home text-white"></i>
            </a>
            <img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40">
            <span class="game-title">CODEPLAY</span>
            <div class="ms-auto text-white">
                <i class="fas fa-user me-2"></i>
                {{ session('usuario_nombre') ?? 'Usuario' }}
                <span class="badge bg-success ms-2">Alumno</span>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="module-card text-center">
                    <p class="module-number mb-3">Modulo {{ $modulo['numero'] }}</p>
                    <h1 class="mb-3">{{ $modulo['titulo'] }}</h1>
                    <p class="lead text-white-50 mb-4">{{ $modulo['descripcion'] }}</p>
                    <p>Este contenido sera desarrollado como una leccion para principiantes, con explicaciones visuales, ejemplos cortos y practica guiada.</p>
                    <a class="btn btn-success mt-3" href="{{ route('alumno.documentacion') }}">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Documentacion
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
