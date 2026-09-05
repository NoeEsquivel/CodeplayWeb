<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - {{ $test->titulo ?? 'Test HTML' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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
        
        .test-container {
            background: rgba(0, 0, 0, 0.8);
            border: 3px solid var(--accent-color);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .pregunta-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-color);
        }
        
        .opcion-label {
            display: block;
            padding: 12px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .opcion-label:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .opcion-input:checked + .opcion-label {
            background: rgba(0, 255, 157, 0.2);
            border: 2px solid var(--accent-color);
        }
        
        .btn-submit {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        
        .btn-submit:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
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
            <img src="https://img.icons8.com/nolan/48/code.png" alt="Logo" width="40" height="40">
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
                <div class="test-container">
                    <div class="text-center mb-4">
                        <h1><i class="fas fa-file-alt me-2"></i>{{ $test->titulo ?? 'Test de HTML' }}</h1>
                        <p class="lead">Responde las siguientes 5 preguntas sobre {{ $test->titulo ?? 'HTML' }}</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cada respuesta correcta vale 10 puntos. Solo puedes realizar este test una vez cada 24 horas.
                        </div>
                    </div>

                    <form action="{{ route('test.evaluar', $modulo) }}" method="POST" id="testForm">
                        @csrf
                        
                        @foreach($preguntas as $index => $pregunta)
                        <div class="pregunta-card">
                            <h5 class="mb-3">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                {{ $pregunta->pregunta }}
                            </h5>
                            
                            <div class="opciones">
                                @foreach(['a', 'b', 'c', 'd'] as $letra)
                                <div class="form-check">
                                    <input class="form-check-input opcion-input" type="radio" 
                                           name="respuestas[{{ $pregunta->id }}]" 
                                           id="p{{ $pregunta->id }}{{ $letra }}" 
                                           value="{{ $letra }}" required>
                                    <label class="form-check-label opcion-label" for="p{{ $pregunta->id }}{{ $letra }}">
                                        <strong>{{ strtoupper($letra) }}.</strong> 
                                        {{ $pregunta->{'opcion_' . $letra} }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="text-center">
                            <button type="submit" class="btn btn-submit btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Respuestas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación para asegurar que todas las preguntas estén respondidas
        document.getElementById('testForm').addEventListener('submit', function(e) {
            const radios = document.querySelectorAll('input[type="radio"]:checked');
            if (radios.length !== {{ count($preguntas) }}) {
                e.preventDefault();
                alert('Por favor responde todas las preguntas antes de enviar el test.');
            }
        });
    </script>
</body>
</html>
