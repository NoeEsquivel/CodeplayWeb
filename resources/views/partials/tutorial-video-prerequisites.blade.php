<style>
    .tutorial-prerequisites { padding: 1.25rem; border: 1px solid #e7b94d; border-radius: 12px; background: #15292e; color: #ecf7f4; }
    .tutorial-prerequisites h5 { color: #ffce62; }
    .tutorial-prerequisites ul { display: grid; gap: 1rem; list-style: none; margin: 1rem 0; padding: 0; }
    .tutorial-prerequisites li { padding: 1rem; border: 1px solid #48616a; border-radius: 8px; }
    .tutorial-prerequisites .requirement-status { display: flex; flex-wrap: wrap; gap: .5rem 1rem; margin: .5rem 0; font-size: .95rem; }
    .tutorial-prerequisites .requirement-done { color: #7ee6be; }
    .tutorial-prerequisites .requirement-pending { color: #ffce62; }
    .tutorial-prerequisites .requirement-links { display: flex; flex-wrap: wrap; gap: .5rem; }
</style>
<section class="tutorial-prerequisites" aria-labelledby="video-prerequisites-title">
    <h5 id="video-prerequisites-title"><i class="fas fa-lock me-2" aria-hidden="true"></i>Video bloqueado</h5>
    <p>Antes de ver este video, visita los siguientes temas de Documentación y realiza al menos una vez el test de cada uno. No necesitas una nota mínima.</p>
    <ul>
        @foreach ($videoAccess['requirements'] as $requirement)
            <li>
                <strong>{{ $requirement['title'] }}</strong>
                <div class="requirement-status">
                    <span class="{{ $requirement['visited'] ? 'requirement-done' : 'requirement-pending' }}">Lectura: {{ $requirement['visited'] ? 'visitada' : 'pendiente' }}</span>
                    <span class="{{ $requirement['attempted'] ? 'requirement-done' : 'requirement-pending' }}">Test: {{ $requirement['attempted'] ? 'realizado' : 'pendiente' }}</span>
                </div>
                <div class="requirement-links">
                    <a class="btn btn-outline-light btn-sm" href="{{ route('alumno.documentacion.show', $requirement['module']) }}">{{ $requirement['visited'] ? 'Volver a leer' : 'Visitar documentación' }}</a>
                    @unless ($requirement['attempted'])
                        <a class="btn btn-outline-warning btn-sm" href="{{ route('test.show', $requirement['module']) }}">Realizar test</a>
                    @endunless
                </div>
            </li>
        @endforeach
    </ul>
    <p>El progreso anterior también cuenta. Después de completar los requisitos, vuelve a este tutorial para ver el video.</p>
    <a class="btn btn-success" href="{{ route('tutorial.show', $tutorial->slug) }}">Comprobar acceso al video</a>
</section>
