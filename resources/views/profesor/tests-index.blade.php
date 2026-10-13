@extends('layouts.app')
@section('title', 'Tests y evaluaciones - CodePlay')

@section('content')
@php
    $teacherPublishedBanks = $banks->where('estado', 'publicado');
    $availableBanks = $teacherPublishedBanks->concat($systemBanks);
@endphp
<div class="teacher-page">
    <div class="container">
        <main class="teacher-frame">
            <header class="teacher-page-header">
                <div>
                    <span class="teacher-kicker"><i class="fas fa-clipboard-question"></i> Evaluación y práctica</span>
                    <h1>Tests y evaluaciones</h1>
                    <p>Elige la tarea que deseas realizar. Cada sección se abre por separado para mantener el espacio ordenado.</p>
                </div>
                <nav class="teacher-actions" aria-label="Acciones principales">
                    <a class="teacher-btn" href="{{ route('inicio.profesor') }}"><i class="fas fa-arrow-left"></i>Panel principal</a>
                    <a class="teacher-btn" href="{{ route('profesor.calificaciones.index') }}"><i class="fas fa-chart-column"></i>Calificaciones</a>
                </nav>
            </header>

            @if(session('success'))<div class="alert alert-success"><i class="fas fa-circle-check me-2"></i>{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i>{{ $errors->first() }}</div>@endif

            <section class="test-section-menu" aria-label="Secciones de Tests y evaluaciones">
                <button type="button" class="test-section-card" data-open-test-panel="mis-bancos">
                    <span class="test-section-icon"><i class="fas fa-folder-open"></i></span><span><small>{{ $banks->count() }} disponibles</small><strong>Mis bancos</strong><em>Crea, edita, publica y asigna tus preguntas.</em></span><i class="fas fa-chevron-right"></i>
                </button>
                <button type="button" class="test-section-card" data-open-test-panel="bancos-sistema">
                    <span class="test-section-icon"><i class="fas fa-database"></i></span><span><small>{{ $systemBanks->count() }} disponibles</small><strong>Bancos del sistema</strong><em>Utiliza el contenido oficial preparado por CodePlay.</em></span><i class="fas fa-chevron-right"></i>
                </button>
                <button type="button" class="test-section-card" data-open-test-panel="abrir-sesion">
                    <span class="test-section-icon"><i class="fas fa-door-open"></i></span><span><small>{{ $classes->count() }} clases activas</small><strong>Abrir una sesión en clase</strong><em>Elige entre una trivia sincronizada o un examen.</em></span><i class="fas fa-chevron-right"></i>
                </button>
                <button type="button" class="test-section-card" data-open-test-panel="sesiones-recientes">
                    <span class="test-section-icon"><i class="fas fa-clock-rotate-left"></i></span><span><small>{{ $sessions->count() }} registradas</small><strong>Sesiones recientes</strong><em>Consulta participantes, estados y progreso.</em></span><i class="fas fa-chevron-right"></i>
                </button>
            </section>

            <div class="test-panel-placeholder" id="test-panel-placeholder">
                <i class="fas fa-hand-pointer"></i><div><strong>Selecciona una sección</strong><span>Solo mostraremos las herramientas que necesitas en este momento.</span></div>
            </div>

            <section class="test-content-panel" id="mis-bancos" data-test-panel hidden>
                <div class="section-heading"><div><h2>Mis bancos</h2><p>Contenido creado por ti. Puedes editarlo, publicarlo y asignarlo a tus clases.</p></div><button type="button" class="teacher-btn teacher-btn-small" data-close-test-panel><i class="fas fa-times"></i>Cerrar sección</button></div>
                <div class="teacher-actions justify-content-start mb-3"><a class="teacher-btn teacher-btn-primary" href="{{ route('profesor.tests.banks.create') }}"><i class="fas fa-plus"></i>Crear nuevo banco</a></div>
                <div class="row g-3">
                    @forelse($banks as $bank)
                        <div class="col-lg-6"><article class="teacher-card">
                            <div class="bank-card-head"><div><h3>{{ $bank->nombre }}</h3><div class="mt-2"><span class="status-badge {{ $bank->estado === 'publicado' ? 'status-success' : 'status-muted' }}">{{ ucfirst($bank->estado) }}</span><span class="status-badge status-gold">{{ $bank->preguntas_count }} preguntas</span></div></div><a class="teacher-btn teacher-btn-small" href="{{ route('profesor.tests.banks.edit', $bank) }}"><i class="fas fa-pen"></i>Editar</a></div>
                            <p class="bank-meta"><strong>{{ $bank->materia }}</strong><br>{{ $bank->descripcion ?: 'Este banco todavía no tiene descripción.' }}</p>
                            @include('profesor.tests-partials.assignment', ['bank' => $bank])
                        </article></div>
                    @empty
                        <div class="col-12"><div class="empty-state"><i class="fas fa-folder-open fa-2x mb-2"></i><p class="mb-3">Todavía no has creado bancos de preguntas.</p><a class="teacher-btn teacher-btn-primary" href="{{ route('profesor.tests.banks.create') }}">Crear mi primer banco</a></div></div>
                    @endforelse
                </div>
            </section>

            <section class="test-content-panel" id="bancos-sistema" data-test-panel hidden>
                <div class="section-heading"><div><h2>Bancos del sistema</h2><p>Contenido oficial de CodePlay que puedes asignar sin modificar sus preguntas originales.</p></div><button type="button" class="teacher-btn teacher-btn-small" data-close-test-panel><i class="fas fa-times"></i>Cerrar sección</button></div>
                <div class="row g-3">
                    @forelse($systemBanks as $bank)
                        <div class="col-lg-6"><article class="teacher-card">
                            <div class="bank-card-head"><div><h3>{{ $bank->nombre }}</h3><div class="mt-2"><span class="status-badge status-success">Disponible</span><span class="status-badge status-gold">{{ $bank->preguntas_count }} preguntas</span></div></div></div>
                            <p class="bank-meta"><strong>{{ $bank->materia }}</strong><br>Banco oficial disponible para tus clases.</p>
                            @include('profesor.tests-partials.assignment', ['bank' => $bank])
                        </article></div>
                    @empty
                        <div class="col-12"><div class="empty-state">No hay bancos del sistema publicados.</div></div>
                    @endforelse
                </div>
            </section>

            <section class="test-content-panel" id="abrir-sesion" data-test-panel hidden data-initial-mode="{{ $errors->any() ? old('modalidad') : '' }}">
                <div class="section-heading"><div><h2>Abrir una sesión en clase</h2><p>Primero selecciona cómo quieres trabajar con tus alumnos.</p></div><button type="button" class="teacher-btn teacher-btn-small" data-close-test-panel><i class="fas fa-times"></i>Cerrar sección</button></div>

                <div class="session-mode-choice" id="session-mode-choice">
                    <button type="button" class="session-mode-card" data-session-mode="trivia"><i class="fas fa-bolt"></i><span><strong>Crear una trivia</strong><em>Todos responden la misma pregunta, con tiempo y clasificación por velocidad.</em></span><i class="fas fa-arrow-right"></i></button>
                    <button type="button" class="session-mode-card" data-session-mode="examen"><i class="fas fa-file-pen"></i><span><strong>Crear un examen</strong><em>Cada alumno avanza a su ritmo y recibe una calificación al entregar.</em></span><i class="fas fa-arrow-right"></i></button>
                </div>

                <form class="teacher-card session-builder" id="teacher-session-form" method="POST" action="{{ route('profesor.tests.sessions.store') }}" hidden>
                    @csrf
                    <input type="hidden" name="modalidad" id="session-modalidad" value="{{ old('modalidad') }}">
                    <input type="hidden" name="zona_horaria_profesor" id="teacher-timezone" value="{{ old('zona_horaria_profesor') }}">
                    <div class="session-builder-head"><div><small>Configurando</small><h3 id="session-mode-title">Nueva sesión</h3></div><button class="teacher-btn teacher-btn-small" type="button" id="change-session-mode"><i class="fas fa-repeat"></i>Cambiar modalidad</button></div>
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label" for="session-title">Nombre de la actividad</label><input class="form-control" id="session-title" name="titulo" maxlength="160" value="{{ old('titulo') }}" placeholder="Ej. Evaluación de Fundamentos HTML" required></div>
                        <div class="col-md-6"><label class="form-label" for="session-class">Clase</label><select class="form-select" id="session-class" name="clase_id" required><option value="">Selecciona una clase</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('clase_id') == $class->id)>{{ $class->nombre }} · {{ $class->alumnos_count }} alumnos</option>@endforeach</select></div>
                        <div class="col-md-6 d-flex align-items-end"><label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="orden_aleatorio" value="1" @checked(old('orden_aleatorio', true))> Mezclar el orden de las preguntas</label></div>

                        <div class="col-md-6 mode-trivia"><label class="form-label" for="question-time">Tiempo por pregunta</label><input class="form-control" id="question-time" type="number" name="tiempo_pregunta" min="5" max="300" value="{{ old('tiempo_pregunta', 20) }}"><small class="action-explanation">Entre 5 y 300 segundos.</small></div>
                        <div class="col-md-6 mode-trivia"><label class="form-label" for="target-score">Meta de puntos para ganar</label><input class="form-control" id="target-score" type="number" name="puntaje_objetivo" min="50" max="100000" value="{{ old('puntaje_objetivo', 500) }}"></div>
                        <div class="col-md-6 mode-examen"><label class="form-label" for="exam-duration">Duración general del examen</label><input class="form-control" id="exam-duration" type="number" name="duracion_examen_minutos" min="1" max="1440" value="{{ old('duracion_examen_minutos') }}" placeholder="Déjalo vacío para no establecer límite"><small class="action-explanation">Tiempo individual desde que cada alumno comienza.</small></div>
                        <div class="col-md-6 mode-examen"><label class="form-label" for="summary-duration">Tiempo disponible para consultar el resumen</label><select class="form-select" id="summary-duration" name="resumen_disponible_minutos" required><option value="30" @selected(old('resumen_disponible_minutos')==30)>30 minutos</option><option value="60" @selected(old('resumen_disponible_minutos',60)==60)>1 hora</option><option value="360" @selected(old('resumen_disponible_minutos')==360)>6 horas</option><option value="1440" @selected(old('resumen_disponible_minutos')==1440)>24 horas</option><option value="4320" @selected(old('resumen_disponible_minutos')==4320)>3 días</option><option value="10080" @selected(old('resumen_disponible_minutos')==10080)>7 días</option><option value="43200" @selected(old('resumen_disponible_minutos')==43200)>30 días</option></select><small class="action-explanation">El plazo comienza cuando el examen se cierre. Antes de ese momento nadie verá respuestas correctas.</small></div>

                        <div class="col-md-6"><label class="form-label" for="session-available-from">Disponible desde</label><input class="form-control" id="session-available-from" type="datetime-local" name="disponible_desde" value="{{ old('disponible_desde') }}"><small class="action-explanation">Se interpretará usando la hora local de este equipo.</small></div>
                        <div class="col-md-6"><label class="form-label" for="session-available-until">Disponible hasta</label><input class="form-control" id="session-available-until" type="datetime-local" name="disponible_hasta" value="{{ old('disponible_hasta') }}"><small class="action-explanation">Zona detectada: <strong id="teacher-timezone-label">hora local del navegador</strong>.</small></div>
                        <div class="col-12">
                            <span class="form-label" id="teacher-bank-selector-label">Bancos publicados y asignados</span>
                            <p class="action-explanation mb-2">Bancos creados por ti</p>
                            <div class="bank-checks" id="teacher-bank-selector" aria-labelledby="teacher-bank-selector-label">
                                @forelse($teacherPublishedBanks as $bank)
                                    <label class="form-check"><input class="form-check-input session-bank-checkbox" type="checkbox" name="banco_ids[]" value="{{ $bank->id }}" data-bank-id="{{ $bank->id }}" data-bank-origin="profesor" @checked(in_array($bank->id, old('banco_ids', [])))> {{ $bank->nombre }} <small>(Propio)</small></label>
                                @empty
                                    <span>No tienes bancos propios publicados. Puedes publicar uno o utilizar un banco del sistema.</span>
                                @endforelse
                            </div>
                            @if($systemBanks->isNotEmpty())
                                <button class="teacher-btn teacher-btn-small mt-3" type="button" id="toggle-system-banks" aria-expanded="false"><i class="fas fa-database"></i><span>Desplegar bancos del sistema</span></button>
                                <div class="bank-checks mt-2" id="system-bank-selector" hidden>
                                    @foreach($systemBanks as $bank)
                                        <label class="form-check"><input class="form-check-input session-bank-checkbox" type="checkbox" name="banco_ids[]" value="{{ $bank->id }}" data-bank-id="{{ $bank->id }}" data-bank-origin="sistema" @checked(in_array($bank->id, old('banco_ids', [])))> {{ $bank->nombre }} <small>(Sistema)</small></label>
                                    @endforeach
                                </div>
                            @endif
                            <div class="help-note"><i class="fas fa-lightbulb"></i><span>Al crear la sesión, los bancos seleccionados quedarán asignados automáticamente a la clase elegida.</span></div>
                        </div>
                        <div class="col-12 mode-examen">
                            <details>
                                <summary><i class="fas fa-sliders me-2"></i>Configurar puntos por pregunta</summary>
                                <div class="details-body"><p class="action-explanation">Solo se muestran las preguntas de los bancos seleccionados.</p>
                                    <div id="exam-points-empty" class="empty-state">Selecciona uno o más bancos para configurar el valor de sus preguntas.</div>
                                    @foreach($availableBanks as $bank)
                                        <section class="exam-bank-points" data-points-bank-id="{{ $bank->id }}" hidden>
                                            <h3 class="h6 mt-3">{{ $bank->nombre }}</h3>
                                            @forelse($bank->preguntas as $question)
                                                <label class="exam-point-row"><span>{{ $question->enunciado }}</span><input class="form-control" type="number" name="puntos_pregunta[{{ $question->id }}]" min="1" max="1000" value="{{ old('puntos_pregunta.'.$question->id, $question->puntos ?? 1) }}"></label>
                                            @empty
                                                <p class="action-explanation">Este banco no tiene preguntas activas.</p>
                                            @endforelse
                                        </section>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    </div>
                    <button type="submit" class="teacher-btn teacher-btn-gold mt-3" id="create-session-submit"><i class="fas fa-door-open"></i>Crear y abrir sala</button>
                </form>
            </section>

            <section class="test-content-panel" id="sesiones-recientes" data-test-panel hidden>
                <div class="section-heading"><div><h2>Sesiones recientes</h2><p>Consulta participantes, progreso y controles de tus últimas actividades.</p></div><button type="button" class="teacher-btn teacher-btn-small" data-close-test-panel><i class="fas fa-times"></i>Cerrar sección</button></div>
                <div class="table-responsive"><table class="table table-dark table-striped align-middle">
                    <thead><tr><th>Código</th><th>Clase</th><th>Modalidad</th><th>Estado</th><th>Participantes</th><th>Acciones</th></tr></thead>
                    <tbody>@forelse($sessions as $session)<tr><td><strong>{{ $session->codigo_sala }}</strong></td><td>{{ $session->clase?->nombre }}</td><td>{{ ucfirst($session->modalidad) }}</td><td><span class="status-badge {{ in_array($session->estado, ['activa','espera'], true) ? 'status-success' : 'status-muted' }}">{{ ucfirst($session->estado) }}</span></td><td>{{ $session->participantes_count }}</td><td><div class="session-history-actions"><a class="teacher-btn teacher-btn-small" href="{{ route('profesor.tests.sessions.show', $session) }}"><i class="fas fa-eye"></i>Ver progreso</a>@if(in_array($session->estado, ['finalizada','cerrada'], true))<button type="button" class="teacher-btn teacher-btn-small teacher-btn-danger" data-delete-session-history data-action="{{ route('profesor.tests.sessions.destroy-history', $session) }}" data-activity="{{ $session->modalidad === 'examen' ? 'el examen' : 'la trivia' }} {{ $session->codigo_sala }}"><i class="fas fa-trash-alt"></i>Eliminar historial</button>@endif</div></td></tr>@empty<tr><td colspan="6">No hay sesiones creadas.</td></tr>@endforelse</tbody>
                </table></div>
            </section>
        </main>
    </div>
</div>
<dialog class="teacher-confirm-dialog delete-history-dialog" id="delete-session-history-dialog" aria-labelledby="delete-session-history-title">
    <div class="teacher-confirm-visual">
        <span><i class="fas fa-trash-alt"></i></span>
        <div><small>ELIMINACIÓN DEFINITIVA</small><h2 id="delete-session-history-title">¿Eliminar este historial?</h2></div>
    </div>
    <p>Vas a eliminar <strong id="delete-session-history-name">esta actividad</strong>. Se borrarán permanentemente sus respuestas, calificaciones y registros de participación. Después de confirmar ya no podrás consultar ni imprimir un resumen de la actividad.</p>
    <div class="teacher-confirm-actions">
        <button type="button" class="teacher-btn" id="cancel-delete-session-history"><i class="fas fa-arrow-left"></i>Conservar historial</button>
        <form method="POST" id="delete-session-history-form">
            @csrf @method('DELETE')
            <button type="submit" class="teacher-btn teacher-btn-danger"><i class="fas fa-trash-alt"></i>Sí, eliminar definitivamente</button>
        </form>
    </div>
</dialog>
@include('profesor.partials.panel-theme')
<style>
.test-section-menu{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.test-section-card{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:15px;min-height:128px;padding:20px;border:1px solid #3d6067;border-radius:15px;background:linear-gradient(145deg,#102b34,#0a2029);color:#edf9f6;text-align:left;transition:.2s}.test-section-card:hover,.test-section-card:focus-visible,.test-section-card.is-active{transform:translateY(-3px);border-color:#69e4bd;box-shadow:0 15px 30px #0006;outline:0}.test-section-icon{width:52px;height:52px;display:grid;place-items:center;border:1px solid #55c7a870;border-radius:13px;background:#071b23;color:#6de8c0;font-size:21px}.test-section-card small,.test-section-card strong,.test-section-card em{display:block}.test-section-card small{color:#f2c867;font-size:14px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.test-section-card strong{margin:4px 0;font-family:Georgia,serif;font-size:23px;font-weight:500}.test-section-card em{color:#adc1c2;font-size:15px;font-style:normal}.test-panel-placeholder{display:flex;align-items:center;justify-content:center;gap:15px;margin-top:22px;padding:22px;border:1px dashed #486a70;border-radius:13px;background:#081d257a;color:#abc0c1}.test-panel-placeholder>i{color:#f2c867;font-size:23px}.test-panel-placeholder strong,.test-panel-placeholder span{display:block}.test-content-panel{margin-top:30px;padding-top:24px;border-top:1px solid #405e65}.session-mode-choice{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.session-mode-card{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:17px;padding:25px;border:1px solid #44676e;border-radius:15px;background:#0d2831;color:#eefaf7;text-align:left;transition:.2s}.session-mode-card:hover,.session-mode-card:focus-visible{border-color:#70e8c1;background:#12343d;transform:translateY(-2px);outline:0}.session-mode-card>i:first-child{width:52px;height:52px;display:grid;place-items:center;border-radius:13px;background:#00efa418;color:#6be9c0;font-size:22px}.session-mode-card strong,.session-mode-card em{display:block}.session-mode-card strong{font-family:Georgia,serif;font-size:24px;font-weight:500}.session-mode-card em{margin-top:6px;color:#afc1c3;font-size:15px;font-style:normal;line-height:1.5}.session-builder{margin-top:17px}.session-builder-head{display:flex;align-items:center;justify-content:space-between;gap:15px;padding-bottom:15px;margin-bottom:19px;border-bottom:1px solid #36565d}.session-builder-head small{color:#f2c867;font-size:14px;font-weight:900;text-transform:uppercase}.session-builder-head h3{margin:3px 0 0;font-family:Georgia,serif;font-size:27px}.exam-point-row{display:grid;grid-template-columns:minmax(0,1fr) 120px;align-items:center;gap:12px;margin-bottom:10px}.exam-point-row .form-control{min-width:0}.session-history-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.delete-history-dialog .teacher-confirm-visual>span{background:linear-gradient(145deg,#ffcb67,#ff6673)}@media(max-width:800px){.test-section-menu,.session-mode-choice{grid-template-columns:1fr}}@media(max-width:560px){.test-section-card,.session-mode-card{grid-template-columns:auto 1fr}.test-section-card>i:last-child,.session-mode-card>i:last-child{display:none}.exam-point-row{grid-template-columns:1fr}}
</style>
@endsection

@push('scripts')
<script>
(function(){
    var panels=Array.from(document.querySelectorAll('[data-test-panel]')),placeholder=document.getElementById('test-panel-placeholder'),form=document.getElementById('teacher-session-form'),choice=document.getElementById('session-mode-choice'),modeInput=document.getElementById('session-modalidad'),modeTitle=document.getElementById('session-mode-title'),submit=document.getElementById('create-session-submit'),systemToggle=document.getElementById('toggle-system-banks'),systemSelector=document.getElementById('system-bank-selector'),pointsEmpty=document.getElementById('exam-points-empty');
    var currentMode='';
    var detectedTimezone=Intl.DateTimeFormat().resolvedOptions().timeZone||'';
    var timezoneInput=document.getElementById('teacher-timezone'),timezoneLabel=document.getElementById('teacher-timezone-label');
    if(timezoneInput&&detectedTimezone)timezoneInput.value=detectedTimezone;
    if(timezoneLabel&&detectedTimezone)timezoneLabel.textContent=detectedTimezone;
    function setHash(value){history.replaceState(null,'',location.pathname+location.search+(value?'#'+value:''));}
    function showPanel(id,updateHash){
        var panel=document.getElementById(id);panels.forEach(function(item){item.hidden=item!==panel;});placeholder.hidden=!!panel;document.querySelectorAll('[data-open-test-panel]').forEach(function(button){button.classList.toggle('is-active',button.dataset.openTestPanel===id);});
        if(updateHash!==false)setHash(id);if(panel)panel.scrollIntoView({behavior:'smooth',block:'start'});
        if(id==='abrir-sesion'&&!form.hidden)return;
    }
    function setSystemBanksExpanded(expanded){
        if(!systemSelector||!systemToggle)return;systemSelector.hidden=!expanded;systemToggle.setAttribute('aria-expanded',expanded?'true':'false');systemToggle.querySelector('span').textContent=expanded?'Ocultar bancos del sistema':'Desplegar bancos del sistema';systemToggle.querySelector('i').className=expanded?'fas fa-eye-slash':'fas fa-database';
    }
    function updateSelectedBankPoints(){
        var selected=new Set(Array.from(document.querySelectorAll('.session-bank-checkbox:checked')).map(function(input){return input.dataset.bankId;})),visible=0;
        document.querySelectorAll('[data-points-bank-id]').forEach(function(section){var show=currentMode==='examen'&&selected.has(section.dataset.pointsBankId);section.hidden=!show;section.querySelectorAll('input,select,textarea').forEach(function(input){input.disabled=!show;});if(show)visible++;});
        if(pointsEmpty)pointsEmpty.hidden=visible>0;
    }
    function chooseMode(mode,updateHash){
        currentMode=mode;showPanel('abrir-sesion',false);choice.hidden=true;form.hidden=false;modeInput.value=mode;modeTitle.textContent=mode==='trivia'?'Nueva trivia sincronizada':'Nuevo examen individual';submit.innerHTML=mode==='trivia'?'<i class="fas fa-bolt"></i>Crear y abrir trivia':'<i class="fas fa-file-pen"></i>Crear y abrir examen';
        document.querySelectorAll('.mode-trivia').forEach(function(item){item.hidden=mode!=='trivia';item.querySelectorAll('input,select,textarea').forEach(function(input){input.disabled=mode!=='trivia';});});
        document.querySelectorAll('.mode-examen').forEach(function(item){item.hidden=mode!=='examen';item.querySelectorAll('input,select,textarea').forEach(function(input){input.disabled=mode!=='examen';});});
        if(systemToggle&&systemSelector){if(mode==='trivia'){systemToggle.hidden=true;setSystemBanksExpanded(true);}else{systemToggle.hidden=false;var selectedSystem=!!systemSelector.querySelector('.session-bank-checkbox:checked');setSystemBanksExpanded(selectedSystem);}}
        updateSelectedBankPoints();if(updateHash!==false)setHash(mode==='trivia'?'crear-trivia':'crear-examen');form.scrollIntoView({behavior:'smooth',block:'start'});
    }
    function resetMode(updateHash){currentMode='';form.hidden=true;choice.hidden=false;modeInput.value='';if(systemToggle)systemToggle.hidden=false;if(systemSelector)setSystemBanksExpanded(false);if(updateHash!==false)setHash('abrir-sesion');choice.scrollIntoView({behavior:'smooth',block:'start'});}
    function openFromHash(){var hash=location.hash.slice(1);if(hash==='crear-trivia'||hash==='crear-examen'){chooseMode(hash==='crear-trivia'?'trivia':'examen',false);return;}if(hash==='abrir-sesion'){showPanel(hash,false);resetMode(false);return;}if(['mis-bancos','bancos-sistema','sesiones-recientes'].includes(hash)){showPanel(hash,false);return;}showPanel(null,false);}
    document.querySelectorAll('[data-open-test-panel]').forEach(function(button){button.addEventListener('click',function(){showPanel(button.dataset.openTestPanel);if(button.dataset.openTestPanel==='abrir-sesion')resetMode();});});
    document.querySelectorAll('[data-close-test-panel]').forEach(function(button){button.addEventListener('click',function(){showPanel(null);window.scrollTo({top:0,behavior:'smooth'});});});
    document.querySelectorAll('[data-session-mode]').forEach(function(button){button.addEventListener('click',function(){chooseMode(button.dataset.sessionMode);});});
    document.querySelectorAll('.session-bank-checkbox').forEach(function(input){input.addEventListener('change',updateSelectedBankPoints);});
    systemToggle?.addEventListener('click',function(){setSystemBanksExpanded(systemSelector.hidden);});
    document.getElementById('change-session-mode')?.addEventListener('click',resetMode);window.addEventListener('hashchange',openFromHash);
    var deleteDialog=document.getElementById('delete-session-history-dialog'),deleteForm=document.getElementById('delete-session-history-form'),deleteName=document.getElementById('delete-session-history-name');
    document.querySelectorAll('[data-delete-session-history]').forEach(function(button){button.addEventListener('click',function(){if(!deleteDialog||!deleteForm)return;deleteForm.action=button.dataset.action;deleteName.textContent=button.dataset.activity||'esta actividad';deleteDialog.showModal();});});
    document.getElementById('cancel-delete-session-history')?.addEventListener('click',function(){deleteDialog?.close();});
    deleteDialog?.addEventListener('click',function(event){if(event.target===deleteDialog)deleteDialog.close();});
    var initialMode=document.getElementById('abrir-sesion')?.dataset.initialMode;if(initialMode){chooseMode(initialMode,false);}else{openFromHash();}
})();
</script>
@endpush
