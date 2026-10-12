<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CODEPLAY - Grandes programas que surgieron con una idea</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet"><link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"><link href="{{ asset('vendor/fonts/pixel-roboto-400-700.css') }}" rel="stylesheet">
    @include('documentacion.introduccion.estilos')
</head>
<body>
@include('partials.alumno-sidebar')
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom"><div class="container-fluid"><a class="btn btn-home me-2" href="{{ route('inicio.alumno') }}"><i class="fas fa-home text-white"></i></a><img src="{{ asset('images/local/code-icon-48.png') }}" alt="Logo" width="40" height="40"><span class="game-title">CODEPLAY</span><div class="ms-auto"><div class="player-name-display"><i class="fas fa-user me-2"></i><span>{{ session('usuario_nombre') ?? 'Usuario' }}</span><span class="badge bg-success ms-2">Alumno</span></div></div></div></nav>
    <main class="intro-shell">
        <header class="intro-hero"><div class="intro-icon"><i class="fas fa-lightbulb"></i></div><div><p class="eyebrow">Introducción · Historias que inspiran</p><h1>Grandes programas que surgieron con una idea</h1><p class="hero-copy">Antes de convertirse en herramientas usadas por millones de personas, muchos proyectos comenzaron con una pregunta, una necesidad y una primera versión imperfecta.</p></div></header>

        <section class="intro-section"><h2>Tres ideas que cambiaron el mundo</h2><p>Estas historias muestran distintas formas de usar la programación: conectar personas, llevar seres humanos a la Luna y compartir conocimiento en todo el planeta.</p>
            <div class="people-grid">
                <article class="person-card">
                    <img class="person-photo" src="{{ asset('Imagenes/documentacion/introduccion/mark-zuckerberg.jpg') }}" alt="Mark Zuckerberg trabajando frente a una computadora">
                    <div class="person-content"><span class="person-tag"><i class="fas fa-users"></i> Facebook</span><h3>Mark Zuckerberg</h3><p>En 2004, mientras estudiaba en Harvard, Zuckerberg creó Facebook junto con otros estudiantes. La primera versión conectaba a personas de la universidad. El proyecto se amplió a otras instituciones y después al público general, hasta convertirse en una plataforma mundial y dar origen a la empresa que hoy se llama Meta.</p></div>
                    <div class="photo-credit">Foto: Elaine Chan y Priscilla Chan, <a href="https://commons.wikimedia.org/wiki/File:MarkZuckerberg.jpg" target="_blank" rel="noopener">Wikimedia Commons</a>, CC BY 2.5.</div>
                </article>
                <article class="person-card">
                    <img class="person-photo" src="{{ asset('Imagenes/documentacion/introduccion/margaret-hamilton.jpg') }}" alt="Margaret Hamilton dentro del módulo de mando Apollo">
                    <div class="person-content"><span class="person-tag"><i class="fas fa-moon"></i> Apollo</span><h3>Margaret Hamilton</h3><p>Hamilton dirigió la división de ingeniería de software del laboratorio del MIT que desarrolló el sistema de navegación de las misiones Apollo. Su equipo diseñó software capaz de detectar problemas y priorizar las tareas críticas. Ese trabajo contribuyó al alunizaje del Apollo 11 y ayudó a consolidar la ingeniería de software como disciplina.</p></div>
                    <div class="photo-credit">Foto: NASA, <a href="https://commons.wikimedia.org/wiki/File:Margaret_Hamilton_in_action.jpg" target="_blank" rel="noopener">Wikimedia Commons</a>, dominio público.</div>
                </article>
                <article class="person-card">
                    <img class="person-photo" src="{{ asset('Imagenes/documentacion/introduccion/tim-berners-lee.jpg') }}" alt="Tim Berners-Lee durante una presentación">
                    <div class="person-content"><span class="person-tag"><i class="fas fa-globe"></i> World Wide Web</span><h3>Tim Berners-Lee</h3><p>En 1989, mientras trabajaba en el CERN, Berners-Lee propuso una forma sencilla de enlazar y compartir documentos entre científicos. Después construyó el primer servidor y el primer navegador web. Al mantener abiertas sus tecnologías fundamentales, permitió que personas y organizaciones de todo el mundo construyeran sobre esa idea.</p></div>
                    <div class="photo-credit">Foto: Internet Archive, <a href="https://commons.wikimedia.org/wiki/File:Tim_Berners-Lee_2016.jpg" target="_blank" rel="noopener">Wikimedia Commons</a>, dominio público.</div>
                </article>
            </div>
        </section>

        <section class="intro-section"><h2>La idea es el inicio, no el final</h2><div class="example-flow"><div class="flow-card"><div><strong>Observa</strong><span>Encuentra una necesidad real</span></div></div><div class="flow-arrow"><i class="fas fa-arrow-right"></i></div><div class="flow-card"><div><strong>Construye</strong><span>Crea una primera versión</span></div></div><div class="flow-arrow"><i class="fas fa-arrow-right"></i></div><div class="flow-card"><div><strong>Mejora</strong><span>Prueba, aprende y trabaja en equipo</span></div></div></div></section>
        <div class="closing"><strong>Los grandes proyectos también comenzaron pequeños.</strong><p>No necesitas tener todas las respuestas para empezar: necesitas una idea, curiosidad y disposición para mejorarla.</p></div>
        <section class="intro-section"><h2>Fuentes para seguir aprendiendo</h2><ul class="source-list"><li><a href="https://about.fb.com/ltam/company--info/" target="_blank" rel="noopener">Historia de la compañía Meta</a></li><li><a href="https://science.nasa.gov/people/margaret-hamilton/" target="_blank" rel="noopener">Margaret Hamilton y el código de Apollo — NASA</a></li><li><a href="https://home.cern/science/computing/the-birth-of-the-web/short-history-web/" target="_blank" rel="noopener">Breve historia de la Web — CERN</a></li></ul></section>
    </main>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body></html>
