<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Aprende Programación Jugando</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts -->
    <link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
        }

        body {
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            overflow: hidden;
            /* IMPORTANTE: Usamos asset() para la ruta correcta */
            background: url('{{ asset("Imagenes/Pantalla_Inicio.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: white;
            position: relative;
        }

        /* Capa oscura para mejorar legibilidad */
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

        .game-title {
            font-family: 'Press Start 2P', cursive;
            text-shadow: 3px 3px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;
            animation: pulse 2s infinite;
            color: #00ff9d;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .login-box {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 20px;
            border: 4px solid #00ff9d;
            box-shadow: 0 0 20px rgba(0, 255, 157, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1;
        }

        .btn-play {
            background: linear-gradient(45deg, #00ff9d, #00a1ff);
            border: none;
            font-weight: bold;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .btn-play:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 255, 157, 0.4);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #00ff9d;
            color: white;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(0, 255, 157, 0.25);
        }

        .pixel-char {
            width: 100px;
            filter: drop-shadow(2px 2px 0 #000);
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-box p-4 text-center">
                    <!-- Logo/Título -->
                    <img src="{{ asset('images/local/code-icon-96.png') }}" alt="CODEPLAY Logo" class="mb-3 pixel-char">
                    <h1 class="game-title mb-4">CODEPLAY</h1>

                    <!-- Mensajes de error -->
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <!-- Formulario de ingreso -->
                    <form method="POST" action="{{ route('login.verify') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="nombre" class="form-label h5">¿CON QUÉ NOMBRE DESEAS JUGAR?</label>
                            <input type="text" class="form-control form-control-lg text-center mt-2"
                                   id="nombre" name="nombre" placeholder="Ej: Coder123" required
                                   value="{{ old('nombre') }}">
                        </div>

                        <div class="mb-4">
                            <label for="clave" class="form-label h5">CONTRASEÑA</label>
                            <input type="password" class="form-control form-control-lg text-center mt-2"
                                   id="clave" name="clave" placeholder="Ingresa tu contraseña" required>
                        </div>

                        <button type="submit" class="btn btn-play btn-lg px-5 py-3">
                            <i class="fas fa-gamepad me-2"></i> INGRESAR
                        </button>
                    </form>
                    <a href="{{ route('recovery.form') }}" class="d-inline-block mt-3 text-info">¿Olvidaste tu usuario o contraseña?</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle + Popper -->
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

    <!-- Script para animación de carga -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> CARGANDO...';
            button.disabled = true;
        });
    </script>
</body>
</html>
