@extends('layouts.app')
@section('title', 'Calificaciones e historial - CodePlay')

@section('content')
<div class="teacher-page">
    <div class="container-fluid">
        <main class="teacher-frame">
            <header class="teacher-page-header">
                <div>
                    <span class="teacher-kicker"><i class="fas fa-chart-column"></i> Seguimiento de resultados</span>
                    <h1>Calificaciones e historial</h1>
                    <p>Consulta resultados de exámenes y trivias, filtra la información y prepara reportes para tus clases.</p>
                </div>
                <nav class="teacher-actions" aria-label="Navegación del Profesor">
                    <a class="teacher-btn" href="{{ route('inicio.profesor') }}" title="Regresa al menú principal del Profesor"><i class="fas fa-arrow-left"></i>Panel principal</a>
                    <a class="teacher-btn teacher-btn-primary" href="{{ route('profesor.tests.index') }}" title="Crea bancos de preguntas y abre nuevas actividades"><i class="fas fa-clipboard-question"></i>Tests y evaluaciones</a>
                </nav>
            </header>

            @if(session('success'))<div class="alert alert-success"><i class="fas fa-circle-check me-2"></i>{{ session('success') }}</div>@endif

            <section class="guide-strip" aria-label="Guía del reporte">
                <div class="guide-step"><b>1</b><div><strong>Selecciona filtros</strong><span>Acota los datos por clase, alumno, sesión, banco o fechas.</span></div></div>
                <div class="guide-step"><b>2</b><div><strong>Revisa los resultados</strong><span>Compara promedios y abre el detalle de cada intento.</span></div></div>
                <div class="guide-step"><b>3</b><div><strong>Exporta o imprime</strong><span>Descarga un archivo Excel o imprime el reporte de una clase.</span></div></div>
            </section>

            <section class="metric-grid" aria-label="Resumen de la consulta">
                <div class="metric-card"><small>Resultados mostrados</small><strong>{{ $results->count() }}</strong></div>
                <div class="metric-card"><small>Grupos del resumen</small><strong>{{ count($summary) }}</strong></div>
                <div class="metric-card"><small>Clases disponibles</small><strong>{{ $classes->count() }}</strong></div>
                <div class="metric-card"><small>Sesiones registradas</small><strong>{{ $sessions->count() }}</strong></div>
            </section>

            <section class="section-block">
                <div class="section-heading"><div><h2>Filtros del reporte</h2><p>Los filtros también se respetan al descargar Excel o imprimir una clase.</p></div><span class="section-tag">Hasta 1,000 resultados en pantalla</span></div>
                <form method="GET" class="filter-panel">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Agrupar resumen por</label><select class="form-select" name="dimension" title="Define cómo se organizará la tabla de resumen">@foreach(['clase'=>'Clase','alumno'=>'Alumno','sesion'=>'Sesión','banco'=>'Banco','profesor'=>'Profesor','colegio'=>'Colegio'] as $value=>$label)<option value="{{ $value }}" @selected($filters['dimension'] === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Clase</label><select class="form-select" name="clase_id"><option value="">Todas mis clases</option>@foreach($classes as $item)<option value="{{ $item->id }}" @selected(($filters['clase_id'] ?? '') == $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Alumno</label><select class="form-select" name="alumno_id"><option value="">Todos mis alumnos</option>@foreach($students as $item)<option value="{{ $item->id }}" @selected(($filters['alumno_id'] ?? '') == $item->id)>{{ $item->nombreParaReportes() }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Sesión</label><select class="form-select" name="sesion_id"><option value="">Todas las sesiones</option>@foreach($sessions as $item)<option value="{{ $item->id }}" @selected(($filters['sesion_id'] ?? '') == $item->id)>{{ $item->configuracion['titulo'] ?? $item->codigo_sala }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Banco</label><select class="form-select" name="banco_id"><option value="">Todos los bancos</option>@foreach($banks as $item)<option value="{{ $item->id }}" @selected(($filters['banco_id'] ?? '') == $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Modalidad</label><select class="form-select" name="modalidad"><option value="">Exámenes y trivias</option><option value="examen" @selected(($filters['modalidad'] ?? '') === 'examen')>Examen</option><option value="trivia" @selected(($filters['modalidad'] ?? '') === 'trivia')>Trivia</option></select></div>
                        <div class="col-md-3"><label class="form-label">Desde</label><input class="form-control" type="date" name="desde" value="{{ $filters['desde'] ?? '' }}"></div>
                        <div class="col-md-3"><label class="form-label">Hasta</label><input class="form-control" type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}"></div>
                        <div class="col-12 teacher-actions justify-content-start mt-4">
                            <button type="submit" class="teacher-btn teacher-btn-primary" title="Actualiza las tablas usando los filtros seleccionados"><i class="fas fa-filter"></i>Aplicar filtros</button>
                            <a class="teacher-btn" href="{{ route('profesor.calificaciones.index') }}" title="Quita todos los filtros y vuelve a mostrar el reporte general"><i class="fas fa-eraser"></i>Limpiar filtros</a>
                            <a class="teacher-btn teacher-btn-gold" href="{{ route('profesor.calificaciones.export', request()->query()) }}" title="Descarga en Excel únicamente los resultados que coinciden con los filtros"><i class="fas fa-file-excel"></i>Descargar Excel</a>
                            @if(!empty($filters['clase_id']))<a class="teacher-btn" target="_blank" href="{{ route('profesor.calificaciones.print-class', request()->query()) }}" title="Abre una versión lista para imprimir de la clase seleccionada"><i class="fas fa-print"></i>Imprimir clase</a>@endif
                        </div>
                    </div>
                </form>
            </section>

            <section class="section-block">
                <div class="section-heading"><div><h2>Resumen por {{ ucfirst($filters['dimension']) }}</h2><p>Vista consolidada de la consulta actual.</p></div></div>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead><tr><th>Grupo</th><th>Intentos</th><th>Promedio</th><th>Correctas</th><th>Incorrectas</th><th>Puntos de Test</th></tr></thead>
                        <tbody>@forelse($summary as $row)<tr><td><strong>{{ $row['nombre'] }}</strong></td><td>{{ $row['intentos'] }}</td><td><strong>{{ number_format($row['promedio'], 2) }}</strong></td><td>{{ $row['correctas'] }}</td><td>{{ $row['incorrectas'] }}</td><td>{{ $row['puntos'] }}</td></tr>@empty<tr><td colspan="6">No hay resultados para estos filtros.</td></tr>@endforelse</tbody>
                    </table>
                </div>
            </section>

            <section class="section-block">
                <div class="section-heading"><div><h2>Intentos registrados</h2><p>Abre un resultado para revisar respuestas e imprimir el examen individual.</p></div></div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead><tr><th>Alumno</th><th>Clase y actividad</th><th>Modalidad</th><th>Resultado</th><th>Tiempo</th><th>Fecha</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse($results as $result)
                                <tr>
                                    <td><strong>{{ $result->nombreAlumnoReporte() }}</strong></td>
                                    <td><strong>{{ $result->clase?->nombre }}</strong><br><small>{{ $result->sesion->configuracion['titulo'] ?? $result->sesion->codigo_sala }} · {{ $result->sesion->bancos->pluck('nombre')->join(' + ') }}</small></td>
                                    <td>{{ ucfirst($result->modalidad) }} #{{ $result->intento }}</td>
                                    <td><strong>{{ number_format((float) $result->calificacion, 2) }}</strong><br><small>{{ $result->correctas }} correctas / {{ $result->incorrectas }} incorrectas · {{ $result->puntos_test }} pts.</small></td>
                                    <td>{{ $result->duracion_segundos === null ? '—' : gmdate('H:i:s', $result->duracion_segundos) }}</td>
                                    <td>{{ optional($result->entregada_at)->format('d/m/Y H:i') }}</td>
                                    <td><a class="teacher-btn teacher-btn-small" target="_blank" href="{{ route('profesor.calificaciones.detail', $result) }}" title="Muestra las respuestas del alumno y permite imprimir su examen"><i class="fas fa-eye"></i>Ver detalle</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7">Todavía no hay intentos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</div>
@include('profesor.partials.panel-theme')
@endsection
