@extends('layouts.app')
@section('title', 'Sesión '.$sesion->codigo_sala.' - CodePlay')
@section('content')
<div class="teacher-page"><div class="container"><main class="teacher-frame">
    <header class="teacher-page-header">
        <div>
            <span class="teacher-kicker"><i class="fas fa-tower-broadcast"></i> Sesión de clase</span>
            <h1>{{ $sesion->configuracion['titulo'] ?? ($sesion->modalidad === 'examen' ? 'Examen' : 'Trivia') }}</h1>
            <p>{{ $sesion->clase->nombre }} · Código para los alumnos: <strong class="text-warning">{{ $sesion->codigo_sala }}</strong></p>
        </div>
        <div class="teacher-actions"><span class="status-badge {{ in_array($sesion->estado, ['activa','espera'], true) ? 'status-success' : 'status-muted' }}" id="estado">{{ ucfirst($sesion->estado) }}</span><a class="teacher-btn" href="{{ route('profesor.tests.index') }}" title="Regresa a la administración de Tests"><i class="fas fa-arrow-left"></i>Tests y evaluaciones</a></div>
    </header>
    @if(session('success'))<div class="alert alert-success"><i class="fas fa-circle-check me-2"></i>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}</div>@endif

    <section class="metric-grid">
        <div class="metric-card"><small>Modalidad</small><strong style="font-size:20px">{{ ucfirst($sesion->modalidad) }}</strong></div>
        <div class="metric-card"><small>Participantes</small><strong>{{ $sesion->participantes->where('es_anfitrion', false)->count() }}</strong></div>
        <div class="metric-card"><small>Inicio</small><strong style="font-size:16px" @if($sesion->disponible_desde)data-local-datetime="{{ $sesion->disponible_desde->toIso8601String() }}"@endif>{{ $sesion->disponible_desde?->format('d/m/Y H:i') ?: 'Inmediato' }}</strong></div>
        <div class="metric-card"><small>Cierre</small><strong style="font-size:16px" @if($sesion->disponible_hasta)data-local-datetime="{{ $sesion->disponible_hasta->toIso8601String() }}"@endif>{{ $sesion->disponible_hasta?->format('d/m/Y H:i') ?: 'Sin programar' }}</strong></div>
    </section>

    @if($sesion->modalidad === 'examen')
        <div class="help-note">
            <i class="fas fa-eye"></i>
            <span>
                @if($sesion->estado === 'cerrada')
                    Los alumnos pueden consultar sus respuestas hasta <strong>{{ $resumenHasta?->format('d/m/Y H:i') }}</strong>.
                @else
                    Las respuestas correctas permanecerán ocultas para los alumnos hasta que cierres el examen. Después tendrán <strong>{{ $sesion->configuracion['resumen_disponible_minutos'] ?? 60 }} minutos</strong> para consultar su resumen.
                @endif
            </span>
        </div>
    @endif

    <div class="help-note"><i class="fas fa-circle-info"></i><span>La sala se actualiza automáticamente cada 10 segundos. Solo pueden entrar alumnos vinculados a la clase <strong>{{ $sesion->clase->nombre }}</strong>.</span></div>
    <div class="teacher-actions justify-content-start mb-4">
        @if(in_array($sesion->estado, ['configuracion','espera']))<form method="POST" action="{{ route('profesor.tests.sessions.action', [$sesion,'start']) }}">@csrf<button class="teacher-btn teacher-btn-primary" title="Comienza la actividad para todos los alumnos conectados"><i class="fas fa-play"></i>Iniciar {{ $sesion->modalidad }}</button></form>@endif
        @if($sesion->modalidad === 'trivia' && $sesion->estado === 'activa')<form method="POST" action="{{ route('profesor.tests.sessions.action', [$sesion,'pause']) }}">@csrf<button class="teacher-btn teacher-btn-gold" title="Detiene temporalmente la trivia sin cerrar la sala"><i class="fas fa-pause"></i>Pausar</button></form>@endif
        @if($sesion->modalidad === 'trivia' && $sesion->estado === 'pausada')<form method="POST" action="{{ route('profesor.tests.sessions.action', [$sesion,'resume']) }}">@csrf<button class="teacher-btn teacher-btn-primary" title="Continúa la trivia desde el punto en que fue pausada"><i class="fas fa-play"></i>Reanudar</button></form>@endif
        @if(!in_array($sesion->estado, ['finalizada','cerrada']))<button type="button" class="teacher-btn teacher-btn-danger" id="open-finish-session" title="Finaliza la actividad y cierra la recepción de respuestas"><i class="fas fa-stop"></i>Terminar sesión</button>@endif
    </div>

    <section class="section-block">
        <div class="section-heading"><div><h2>Participantes y progreso</h2><p>En los exámenes, la calificación permanece oculta hasta que el alumno entrega.</p></div><span class="section-tag">{{ $sesion->bancos->pluck('nombre')->join(', ') }}</span></div>
        <div class="table-responsive"><table class="table table-dark table-striped"><thead><tr><th>Alumno</th><th>Estado</th><th>Respuestas</th><th>Correctas</th><th>{{ $sesion->modalidad === 'examen' ? 'Calificación' : 'Puntos Test' }}</th></tr></thead><tbody id="participantes">
        @forelse($sesion->participantes->where('es_anfitrion', false) as $participant)
            @php $result = $sesion->resultados->firstWhere('participante_id', $participant->id); @endphp
            <tr><td><strong>{{ $participant->nombre_visible }}</strong></td><td>{{ $result?->estado ?? ucfirst($participant->estado) }}</td><td>{{ $participant->respuestas->count() }}</td><td>{{ $participant->respuestas->where('correcta', true)->count() }}</td><td>{{ $sesion->modalidad === 'examen' ? ($result?->estado === 'entregado' ? number_format((float) $result->calificacion, 2) : 'Oculta hasta entregar') : $participant->puntos_test }}</td></tr>
        @empty<tr><td colspan="5">Esperando alumnos de {{ $sesion->clase->nombre }}.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</main></div></div>
<dialog class="teacher-confirm-dialog" id="finish-session-dialog">
    <div class="teacher-confirm-visual"><span><i class="fas fa-flag-checkered"></i></span><div><small>CONFIRMACIÓN</small><h2>¿Cerrar esta sesión?</h2></div></div>
    <p>
        @if($sesion->modalidad === 'examen')
            Las entregas pendientes se cerrarán y las calificaciones quedarán disponibles para los alumnos durante el plazo configurado.
        @else
            La trivia terminará para todos los participantes y se guardará su historial.
        @endif
    </p>
    <div class="teacher-confirm-actions"><button type="button" class="teacher-btn" data-close-finish><i class="fas fa-arrow-left"></i>Continuar con la sesión</button><form method="POST" action="{{ route('profesor.tests.sessions.action', [$sesion,'finish']) }}">@csrf<button class="teacher-btn teacher-btn-danger"><i class="fas fa-stop"></i>Sí, terminar sesión</button></form></div>
</dialog>
@include('profesor.partials.panel-theme')
<script>
(function(){
    const dialog=document.getElementById('finish-session-dialog');
    document.querySelectorAll('[data-local-datetime]').forEach(element=>{const value=new Date(element.dataset.localDatetime);if(!Number.isNaN(value.getTime()))element.textContent=value.toLocaleString([], {dateStyle:'short',timeStyle:'short'});});
    document.getElementById('open-finish-session')?.addEventListener('click',()=>dialog.showModal());
    dialog?.querySelector('[data-close-finish]')?.addEventListener('click',()=>dialog.close());
    dialog?.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});
    setTimeout(function refresh(){if(document.querySelector('dialog[open]')){setTimeout(refresh,3000);return;}location.reload();},10000);
})();
</script>
@endsection
