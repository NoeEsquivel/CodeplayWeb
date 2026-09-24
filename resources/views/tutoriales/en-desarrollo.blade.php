<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial en Desarrollo - CODEPLAY</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .message-box {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h1>🚧 Tutorial en Desarrollo</h1>
        <p class="lead mt-3">El tutorial "{{ $slug }}" estará disponible pronto.</p>
        <p>¡Estamos trabajando para brindarte la mejor experiencia de aprendizaje!</p>
        <a href="{{ route('alumno.tutoriales') }}" class="btn btn-primary mt-3">
            Volver a Tutoriales
        </a>
    </div>
</body>
</html>
