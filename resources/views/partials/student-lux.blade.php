<aside id="codeplay-lux" class="lux-root"
     data-route="{{ $routeName }}"
     data-welcome-pending="{{ $welcomePending ? '1' : '0' }}"
     data-welcome-url="{{ route('alumno.lux.bienvenida-vista') }}"
     data-csrf="{{ csrf_token() }}"
     data-orbit-saludo="{{ asset('images/lux/orbit-saludo.png') }}"
     data-orbit-explica="{{ asset('images/lux/orbit-explica.png') }}"
     data-ranger-saludo="{{ asset('images/lux/ranger-saludo.png') }}"
     data-ranger-guia="{{ asset('images/lux/ranger-guia.png') }}"
     data-ranger-piensa="{{ asset('images/lux/ranger-piensa.png') }}"
     data-ranger-pelota="{{ asset('images/lux/ranger-pelota.png') }}"
     data-docs="{{ route('alumno.documentacion') }}"
     data-tutoriales="{{ route('alumno.tutoriales') }}"
     data-desafios="{{ route('alumno.desafios') }}"
     data-aventura="{{ route('alumno.aventura') }}"
     data-tests="{{ route('alumno.tests') }}"
     data-progreso="{{ route('alumno.progreso') }}">
    <div class="lux-tip" id="lux-tip" role="status" aria-live="off" hidden>
        <span class="lux-tip-title">✦ Dato curioso de Lux</span>
        <span id="lux-tip-text"></span>
    </div>
    <section class="lux-panel" id="lux-panel" role="dialog" aria-label="Asistente Lux" aria-hidden="true">
        <header class="lux-panel-header">
            <span class="lux-name"><span class="lux-status-dot"></span> LUX <small>Tu guía CodePlay</small></span>
            <button type="button" class="lux-close" id="lux-close" aria-label="Cerrar a Lux">×</button>
        </header>
        <div class="lux-panel-main">
            <div class="lux-portrait"><img id="lux-portrait" src="{{ asset('images/lux/orbit-saludo.png') }}" alt="Lux, robot guía de CodePlay"></div>
            <div class="lux-dialogue" aria-live="polite">
                <h2 id="lux-heading">¡Hola!</h2>
                <p id="lux-message">Estoy aquí para ayudarte a explorar CodePlay.</p>
            </div>
        </div>
        <div class="lux-actions" id="lux-actions"></div>
    </section>
    <button type="button" class="lux-launcher" id="lux-launcher" aria-label="Llamar a Lux, tu guía" aria-expanded="false" aria-controls="lux-panel" title="Pide ayuda a Lux">
        <img src="{{ asset('images/lux/orbita-pelota.png') }}" alt="">
        <span class="lux-launcher-label">¿Necesitas ayuda?</span>
    </button>
</aside>
<script src="{{ asset('js/student-lux.js') }}" defer></script>
