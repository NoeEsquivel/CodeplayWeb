<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - ¿Por qué programar es importante para el futuro?</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet"><link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    @include('documentacion.introduccion.estilos')
</head>
<body>
@include('partials.alumno-sidebar')
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom"><div class="container-fluid"><a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}"><i class="fas fa-home text-white"></i></a><img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40"><span class="game-title">CODEPLAY</span><div class="ms-auto"><div class="player-name-display"><i class="fas fa-user me-2"></i><span>{{ session('usuario_nombre') ?? 'Usuario' }}</span><span class="badge bg-success ms-2">Alumno</span></div></div></div></nav>
    <main class="intro-shell">
        <header class="intro-hero"><div class="intro-icon"><i class="fas fa-rocket"></i></div><div><p class="eyebrow">Introducción · Mirando hacia adelante</p><h1>¿Por qué programar es importante para el futuro?</h1><p class="hero-copy">La tecnología cambia con rapidez. Comprender cómo se construye nos ayuda a usarla con criterio, participar en sus decisiones y adaptarnos a nuevas oportunidades.</p></div></header>

        <section class="intro-section"><h2>La tecnología ya forma parte de casi todo</h2><p>Los programas organizan información, automatizan tareas y conectan dispositivos. Están presentes en la educación, la salud, el transporte, las comunicaciones y el entretenimiento. También impulsan áreas que seguirán creciendo:</p><div class="future-grid">
            <article class="future-card"><i class="fas fa-brain"></i><div><h3>Inteligencia artificial</h3><p>Ayuda a reconocer patrones, crear contenido y apoyar decisiones que siempre debemos revisar con sentido crítico.</p></div></article>
            <article class="future-card"><i class="fas fa-robot"></i><div><h3>Automatización y robótica</h3><p>Permiten realizar tareas repetitivas o peligrosas y abren nuevas formas de trabajar junto a las máquinas.</p></div></article>
            <article class="future-card"><i class="fas fa-chart-bar"></i><div><h3>Datos y servicios digitales</h3><p>Transforman información en herramientas para aprender, investigar, administrar recursos y resolver problemas.</p></div></article>
            <article class="future-card"><i class="fas fa-shield-alt"></i><div><h3>Seguridad y responsabilidad</h3><p>Comprender los sistemas ayuda a proteger nuestra información y a reconocer los riesgos de una tecnología.</p></div></article>
        </div></section>

        <section class="intro-section"><h2>No tienes que ser un experto para beneficiarte</h2><p>Aprender los fundamentos de programación se parece a aprender cómo funciona un mapa: quizá no construyas todas las carreteras, pero podrás orientarte, elegir una ruta y explicar hacia dónde quieres ir.</p><div class="skill-strip"><div class="skill-pill"><i class="fas fa-puzzle-piece"></i><strong>Resolver problemas</strong><span>Dividir un reto grande en pasos pequeños.</span></div><div class="skill-pill"><i class="fas fa-search"></i><strong>Evaluar la tecnología</strong><span>Preguntar cómo funciona, qué datos usa y qué límites tiene.</span></div><div class="skill-pill"><i class="fas fa-comments"></i><strong>Crear con otros</strong><span>Comunicar ideas con claridad a equipos técnicos y creativos.</span></div></div></section>

        <section class="intro-section"><h2>Tu papel en ese futuro</h2><div class="note-box"><i class="fas fa-compass me-2"></i><strong>Programar también es una forma de alfabetización digital.</strong> Te permite pasar de consumir tecnología a comprenderla, cuestionarla y participar en su creación de manera responsable.</div><p>Tal vez decidas dedicarte profesionalmente a otra área. Aun así, saber qué es un algoritmo, cómo se representan los datos y por qué un programa puede equivocarse te ayudará a tomar mejores decisiones durante toda tu vida.</p></section>
        <div class="closing"><strong>El futuro no solo se utiliza: también se imagina y se construye.</strong><p>Cada concepto que aprendas te dará una herramienta más para comprender ese futuro y aportar tus propias ideas.</p></div>
    </main>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body></html>
