<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - ¿Qué es la programación?</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet"><link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    @include('documentacion.introduccion.estilos')
</head>
<body>
@include('partials.alumno-sidebar')
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom"><div class="container-fluid"><a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}"><i class="fas fa-home text-white"></i></a><img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40"><span class="game-title">CODEPLAY</span><div class="ms-auto"><div class="player-name-display"><i class="fas fa-user me-2"></i><span>{{ session('usuario_nombre') ?? 'Usuario' }}</span><span class="badge bg-success ms-2">Alumno</span></div></div></div></nav>
    <main class="intro-shell">
        <header class="intro-hero"><div class="intro-icon"><i class="fas fa-code"></i></div><div><p class="eyebrow">Introducción</p><h1>¿Qué es la programación?</h1><p class="hero-copy">Programar es convertir una idea en instrucciones claras que una computadora puede seguir para resolver un problema o realizar una tarea.</p></div></header>
        <section class="intro-section"><h2>Una conversación con la computadora</h2><p>Las computadoras trabajan con enorme rapidez, pero necesitan instrucciones precisas. Un programa es una secuencia ordenada de esas instrucciones. Al escribirlas usamos un lenguaje de programación, que funciona como un puente entre nuestras ideas y la máquina.</p><div class="concept-grid"><article class="concept-card"><i class="fas fa-lightbulb"></i><h3>Idea</h3><p>Decidimos qué queremos crear o qué problema necesitamos resolver.</p></article><article class="concept-card"><i class="fas fa-list-ol"></i><h3>Algoritmo</h3><p>Organizamos la solución en pasos claros, ordenados y posibles de repetir.</p></article><article class="concept-card"><i class="fas fa-laptop-code"></i><h3>Código</h3><p>Escribimos esos pasos en un lenguaje que la computadora pueda ejecutar.</p></article></div></section>
        <section class="intro-section"><h2>Un ejemplo cotidiano</h2><div class="example-flow"><div class="flow-card"><div><strong>Entrada</strong><span>El usuario presiona “Jugar”</span></div></div><div class="flow-arrow"><i class="fas fa-arrow-right"></i></div><div class="flow-card"><div><strong>Proceso</strong><span>El programa prepara el nivel</span></div></div><div class="flow-arrow"><i class="fas fa-arrow-right"></i></div><div class="flow-card"><div><strong>Resultado</strong><span>La partida aparece en pantalla</span></div></div></div></section>
        <section class="intro-section"><h2>¿Dónde encontramos programación?</h2><p>Está presente en videojuegos, páginas web, aplicaciones móviles, robots, semáforos, automóviles, hospitales y muchas herramientas que utilizamos todos los días.</p><div class="note-box"><i class="fas fa-star me-2"></i><strong>No necesitas memorizar todo para comenzar.</strong> Aprender a programar consiste en practicar, equivocarse, observar lo que ocurrió y mejorar la solución paso a paso.</div></section>
        <div class="closing"><strong>Programar es aprender a pensar con orden y creatividad.</strong><p>En los siguientes módulos comenzarás a transformar tus propias ideas en proyectos.</p></div>
    </main>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body></html>
