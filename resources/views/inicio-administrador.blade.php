@extends('layouts.app')

@section('title', 'Panel del Profesor - CodePlay')

@section('content')
<div class="teacher-portal">
    <div class="container teacher-container">
        <section class="teacher-shell">
            <header class="teacher-hero">
                <div class="teacher-hero-copy">
                    <span class="teacher-eyebrow"><i class="fas fa-shield-alt"></i> Espacio académico seguro</span>
                    <h1><span class="teacher-title-icon"><i class="fas fa-chalkboard-teacher"></i></span>Panel del Profesor</h1>
                    <p>Gestiona tus clases, acompaña el progreso y prepara evaluaciones desde un solo lugar.</p>
                    <div class="institution-label"><i class="fas fa-school"></i><span>{{ session('usuario_colegio_nombre') }}</span></div>
                </div>
                <div class="teacher-hero-actions">
                    <div class="session-label"><span class="status-dot"></span><span>Sesión activa como <strong>{{ session('usuario_nombre') }}</strong></span></div>

                </div>
            </header>

            <div id="mensaje-profesor" class="teacher-message"></div>

            <div id="menu-profesor" class="row g-4 teacher-menu">
                <div class="col-md-6">
                    <button class="teacher-option w-100 h-100" onclick="mostrarAlumnos()">
                        <span class="option-number">01</span><span class="option-icon"><i class="fas fa-user-graduate"></i></span>
                        <span class="option-copy"><small>Gestión de estudiantes</small><strong>Mis alumnos</strong><span>Registra alumnos, asígnalos a tus clases y restablece sus contraseñas.</span></span>
                        <span class="option-link">Abrir módulo <i class="fas fa-arrow-right"></i></span>
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="teacher-option w-100 h-100" onclick="mostrarProgreso()">
                        <span class="option-number">02</span><span class="option-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="option-copy"><small>Seguimiento académico</small><strong>Progreso de mis clases</strong><span>Consulta y exporta avances por alumno, clase y módulo.</span></span>
                        <span class="option-link">Revisar progreso <i class="fas fa-arrow-right"></i></span>
                    </button>
                </div>
                <div class="col-md-6">
                    <a class="teacher-option w-100 h-100" href="{{ route('profesor.tests.index') }}">
                        <span class="option-number">03</span><span class="option-icon"><i class="fas fa-clipboard-question"></i></span>
                        <span class="option-copy"><small>Contenido y sesiones</small><strong>Tests y evaluaciones</strong><span>Crea bancos, asígnalos a tus clases y abre trivias o exámenes.</span></span>
                        <span class="option-link">Gestionar evaluaciones <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a class="teacher-option w-100 h-100" href="{{ route('profesor.calificaciones.index') }}">
                        <span class="option-number">04</span><span class="option-icon"><i class="fas fa-file-signature"></i></span>
                        <span class="option-copy"><small>Resultados e informes</small><strong>Calificaciones e historial</strong><span>Filtra, imprime y exporta los resultados de tus estudiantes.</span></span>
                        <span class="option-link">Consultar resultados <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            </div>

            <section id="contenido-profesor" class="teacher-workspace" style="display:none"></section>
        </section>
    </div>
</div>

<style>
body{background:#06131b url("{{ asset('Imagenes/fondos/panel-profesor-codeplay.png') }}") center/cover fixed no-repeat!important;color:#effbf8}body::before{content:"";position:fixed;inset:0;z-index:-1;background:linear-gradient(180deg,#0310188a,#041016df),radial-gradient(circle at 50% 18%,transparent,#020a0fd9 88%)}.codeplay-navbar{background:#07151ce8!important;border-bottom:1px solid #79dabb32;backdrop-filter:blur(14px);box-shadow:0 10px 35px #0006}.teacher-portal{min-height:calc(100vh - 70px);padding:clamp(18px,3vw,42px) 0 64px}.teacher-container{max-width:1400px}.teacher-shell{font-size:17px;line-height:1.6;padding:clamp(22px,3.5vw,48px);border:1px solid #78cdb666;border-radius:24px;background:linear-gradient(145deg,#071922f2,#0b2028e8);box-shadow:0 30px 80px #000b,inset 0 1px #d8fff31c;backdrop-filter:blur(18px)}
.teacher-hero{display:flex;justify-content:space-between;align-items:flex-end;gap:30px;padding-bottom:30px;margin-bottom:30px;border-bottom:1px solid #48636a}.teacher-eyebrow{display:inline-flex;align-items:center;gap:8px;margin-bottom:13px;color:#f2c867;font-size:14px;font-weight:900;letter-spacing:.19em;text-transform:uppercase}.teacher-hero h1{display:flex;align-items:center;gap:15px;margin:0;font-family:Georgia,serif;font-size:clamp(32px,4vw,52px);font-weight:500}.teacher-title-icon{width:55px;height:55px;display:grid;place-items:center;border:1px solid #77e7c270;border-radius:16px;background:#00efa414;color:#60e7bb;font-size:24px}.teacher-hero-copy>p{max-width:710px;margin:13px 0 17px;color:#c0d0d1;font-size:17px}.institution-label{display:inline-flex;align-items:center;gap:9px;padding:8px 12px;border:1px solid #506b70;border-radius:9px;background:#10262d;color:#d9e9e6;font-size:15px;font-weight:700}.institution-label i{color:#f2c867}.teacher-hero-actions{display:grid;justify-items:end;gap:12px;min-width:245px}.session-label{display:flex;align-items:center;gap:8px;color:#bdcdce;font-size:15px}.session-label strong{color:#fff}.status-dot{width:8px;height:8px;border-radius:50%;background:#55e6a9;box-shadow:0 0 0 4px #55e6a91c,0 0 13px #55e6a9}
.teacher-menu{counter-reset:teacher-card}.teacher-option{position:relative;display:grid;grid-template-columns:auto 1fr;grid-template-rows:auto 1fr auto;gap:0 18px;min-height:218px;padding:24px;text-align:left;text-decoration:none;color:#effbf8;background:linear-gradient(145deg,#102a35f2,#0b2029f2);border:1px solid #3e656c;border-radius:17px;overflow:hidden;transition:transform .22s,border-color .22s,box-shadow .22s,background .22s}.teacher-option:hover,.teacher-option:focus-visible{transform:translateY(-4px);color:#fff;border-color:#67e1bb;background:linear-gradient(145deg,#143540fa,#0d2630fa);box-shadow:0 18px 34px #0007,0 0 0 3px #00efa410;outline:0}.option-number{position:absolute;right:18px;top:13px;color:#9eb8b922;font:700 54px/1 Georgia,serif}.option-icon{grid-row:1/3;width:54px;height:54px;display:grid;place-items:center;border:1px solid #57c8a873;border-radius:14px;background:#0a1f27;color:#63e5bc;font-size:22px}.option-copy{display:grid;align-content:start;gap:7px;padding-right:30px}.option-copy small{color:#f2c867;font-size:14px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.option-copy strong{font-family:Georgia,serif;font-size:25px;font-weight:500}.option-copy>span{color:#c1d0d1;font-size:16px;line-height:1.55}.option-link{grid-column:1/3;display:flex;align-items:center;justify-content:flex-end;gap:9px;margin-top:20px;padding-top:15px;border-top:1px solid #345159;color:#82eac8;font-size:14px;font-weight:900;letter-spacing:.04em}.teacher-option:hover .option-link i{transform:translateX(4px)}.option-link i{transition:transform .2s}
.teacher-workspace{padding-top:2px}.teacher-workspace>div:first-child{padding-bottom:17px;border-bottom:1px solid #49636a}.teacher-workspace h2{font-family:Georgia,serif;font-size:28px!important;font-weight:500}.teacher-card{padding:1.3rem;border:1px solid #3d6067;border-radius:14px;background:#0b2129e8}.teacher-message .alert{border:1px solid currentColor;border-radius:12px;background:#0b2028f5;color:#fff}.teacher-shell .table-responsive{border:1px solid #3d5b61;border-radius:13px;overflow:hidden}.teacher-shell .table{margin:0;font-size:15px;--bs-table-bg:#0c222a;--bs-table-striped-bg:#112b34;--bs-table-color:#e9f7f4;--bs-table-border-color:#355159}.teacher-shell .table thead{--bs-table-bg:#15313a;color:#87e7c8}.teacher-shell .table th{font-size:14px;letter-spacing:.04em;text-transform:uppercase}.teacher-shell .table small,.teacher-shell small{font-size:14px}.teacher-shell .badge{font-size:14px}.teacher-shell .form-label{font-size:15px}.teacher-shell .form-control,.teacher-shell .form-select{min-height:48px;font-size:16px;border:1px solid #42686c;border-radius:9px;background-color:#071a21;color:#effbf8}.teacher-shell .form-control::placeholder{color:#9ab4b5;opacity:1}.teacher-shell .form-control:focus,.teacher-shell .form-select:focus{border-color:#60dfb8;box-shadow:0 0 0 3px #00efa418}.teacher-shell .form-select option{background:#071a21}.teacher-shell .text-muted,.teacher-shell .text-secondary{color:#98afb0!important}
.prof-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:43px;padding:10px 15px;border:1px solid transparent;border-radius:10px;text-decoration:none;font-size:15px;font-weight:850;line-height:1;transition:transform .2s,filter .2s,border-color .2s,background .2s}.prof-btn:hover{transform:translateY(-2px);filter:brightness(1.08)}.prof-btn-primary{border-color:#90f5d2;background:linear-gradient(110deg,#00d995,#18b989);color:#032018}.prof-btn-secondary{border-color:#628087;background:#102931;color:#e9faf5}.prof-btn-security{border-color:#dabb6c;background:#2e2a1c;color:#ffe69d}.prof-btn-warning{border-color:#efc86d;background:#e3b84f;color:#241c08}.prof-btn-small{min-height:42px;padding:9px 12px;font-size:14px}.progress{height:1.15rem;background:#152d34}.progress-bar{color:#071a20;font-weight:700}
@media(max-width:767px){.teacher-hero{align-items:flex-start;flex-direction:column}.teacher-hero-actions{justify-items:start;min-width:0}.teacher-option{min-height:205px}.teacher-shell{border-radius:16px}.teacher-portal{padding-top:8px}.teacher-workspace .prof-btn{min-height:46px}}
@media(max-width:470px){.teacher-title-icon{width:46px;height:46px}.teacher-hero h1{font-size:30px}.teacher-option{grid-template-columns:44px 1fr;padding:19px}.option-icon{width:44px;height:44px}.option-copy strong{font-size:21px}.option-number{font-size:43px}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style>
<script>
const csrf='{{ csrf_token() }}';
let clasesProfesor=[];
let alumnosProfesor=[];
let clasesPorAlumnoProfesor={};

function area(){return document.getElementById('contenido-profesor');}
function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
function cabecera(titulo,icono){return '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h2 class="h4 mb-0"><i class="fas '+icono+' me-2 text-warning"></i>'+titulo+'</h2><button class="prof-btn prof-btn-secondary" onclick="volverMenuProfesor()"><i class="fas fa-arrow-left"></i><span>Volver al menú principal</span></button></div>';}
function abrir(html){document.getElementById('menu-profesor').style.display='none';area().style.display='block';area().innerHTML=html;}
function volverMenuProfesor(){area().style.display='none';area().innerHTML='';document.getElementById('menu-profesor').style.display='';if(location.hash)history.replaceState(null,'',location.pathname+location.search);}
function mensaje(tipo,texto){var c=document.getElementById('mensaje-profesor');c.innerHTML='<div class="alert alert-'+tipo+'">'+esc(texto)+'</div>';window.scrollTo({top:0,behavior:'smooth'});setTimeout(function(){c.innerHTML='';},5000);}
async function api(url,opciones){opciones=opciones||{};opciones.headers=Object.assign({},opciones.headers||{},{'Accept':'application/json','X-CSRF-TOKEN':csrf});if(opciones.body)opciones.headers['Content-Type']='application/json';var r=await fetch(url,opciones);var d=await r.json().catch(function(){return {};});if(!r.ok){var error=new Error(d.message||Object.values(d.errors||{})[0]?.[0]||'No fue posible completar la operación.');error.data=d;throw error;}return d;}


function activarSugerenciaUsuario(formulario, endpoint){
    var completo=formulario.querySelector('[name="nombre_completo"]'), usuario=formulario.querySelector('[name="nombre"]'), ayuda=formulario.querySelector('[data-sugerencia-usuario]');
    if(!completo||!usuario)return;
    var editado=false,temporizador;
    usuario.addEventListener('input',function(evento){if(evento.isTrusted)editado=true;});
    completo.addEventListener('input',function(){
        clearTimeout(temporizador);var valor=completo.value.trim();if(valor.length<3){if(ayuda)ayuda.textContent='Escribe el nombre completo para recibir una recomendación.';return;}
        temporizador=setTimeout(async function(){
            try{var respuesta=await fetch(endpoint,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({nombre_completo:valor})});var data=await respuesta.json();if(!respuesta.ok)throw new Error(data.message||'No se pudo generar la recomendación.');if(!editado||!usuario.value.trim())usuario.value=data.suggested_username;if(ayuda)ayuda.textContent='Recomendación disponible: '+data.suggested_username+'. Puedes modificarla.';}catch(error){if(ayuda)ayuda.textContent=error.message;}
        },300);
    });
}

async function cargarDatosBase(){
    var datos=await Promise.all([api('/api/profesor/clases'),api('/api/usuarios-por-rol/Alumno')]);
    clasesProfesor=datos[0];alumnosProfesor=datos[1];
}

function normalizarBusqueda(valor){
    return String(valor||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim();
}
function renderAlumnosGestion(lista){
    var contenedor=document.getElementById('lista-alumnos-profesor');if(!contenedor)return;
    var filas=lista.map(function(a){return '<tr><td><strong>'+esc(a.nombre_completo||a.nombre)+'</strong><br><small>Usuario: '+esc(a.nombre)+'</small></td><td>'+esc((clasesPorAlumnoProfesor[a.id]||[]).join(', '))+'</td><td>'+a.puntos_totales+'</td><td>'+(a.cambio_clave_requerido?'<span class="badge bg-warning text-dark">Cambio pendiente</span>':'<span class="badge bg-success">Actualizada</span>')+'</td><td><button class="prof-btn prof-btn-warning prof-btn-small" onclick="restablecer('+a.id+')" title="Permite reiniciar la contraseña del alumno si la ha olvidado." aria-label="Restablecer la contraseña olvidada del alumno"><i class="fas fa-key me-1"></i>Restablecer</button></td></tr>';}).join('');
    var total='<p class="mb-3 text-secondary" aria-live="polite">'+lista.length+' alumno'+(lista.length===1?' encontrado':'s encontrados')+'.</p>';
    contenedor.innerHTML=total+(filas?'<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Alumno</th><th>Mis clases</th><th>Puntos</th><th>Contraseña</th><th>Acciones</th></tr></thead><tbody>'+filas+'</tbody></table></div>':'<div class="teacher-card text-center"><i class="fas fa-user-slash text-warning me-2"></i>No se encontraron alumnos con ese nombre o usuario.</div>');
}
function filtrarAlumnosGestion(valor){
    var termino=normalizarBusqueda(valor);
    var filtrados=!termino?alumnosProfesor:alumnosProfesor.filter(function(a){return normalizarBusqueda((a.nombre_completo||'')+' '+a.nombre).includes(termino);});
    renderAlumnosGestion(filtrados);
}
async function mostrarAlumnos(){
    abrir(cabecera('Mis alumnos','fa-user-graduate')+'<p>Cargando alumnos...</p>');
    try{
        await cargarDatosBase();
        clasesPorAlumnoProfesor={};
        clasesProfesor.forEach(function(c){c.alumnos.forEach(function(a){if(!clasesPorAlumnoProfesor[a.id])clasesPorAlumnoProfesor[a.id]=[];clasesPorAlumnoProfesor[a.id].push(c.nombre);});});
        abrir(cabecera('Mis alumnos','fa-user-graduate')+'<div class="d-flex justify-content-end mb-3"><button class="prof-btn prof-btn-primary" onclick="formularioAlumno()"><i class="fas fa-user-plus"></i>Registrar nuevo alumno</button></div><div id="form-alumno"></div><div class="teacher-card mb-4"><label class="form-label" for="buscar-alumno-gestion"><i class="fas fa-search me-2 text-info"></i>Buscar alumno</label><input id="buscar-alumno-gestion" class="form-control" type="search" autocomplete="off" placeholder="Escribe el nombre completo o nombre de usuario" oninput="filtrarAlumnosGestion(this.value)"><small class="text-secondary d-block mt-2">La lista se filtra automáticamente mientras escribes.</small></div><div id="lista-alumnos-profesor"></div>');
        renderAlumnosGestion(alumnosProfesor);
        document.getElementById('buscar-alumno-gestion')?.focus();
    }catch(e){mensaje('danger',e.message);}
}

function formularioAlumno(){
    var opciones=clasesProfesor.filter(function(c){return c.activa;}).map(function(c){return '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';}).join('');
    document.getElementById('form-alumno').innerHTML='<div class="teacher-card mb-4"><h3 class="h5">Registrar alumno</h3>'+(opciones?'<form onsubmit="crearAlumno(event)"><div class="row g-3"><div class="col-md-6"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre_completo" maxlength="160" required placeholder="Ej. Elvin Noe Esquivel Cabrera"></div><div class="col-md-6"><label class="form-label">Nombre de usuario</label><input class="form-control" name="nombre" maxlength="100" required><small class="text-info" data-sugerencia-usuario>Se recomendará uno a partir del nombre completo.</small></div><div class="col-md-6"><label class="form-label">Clase inicial</label><select class="form-select" name="clase_id" required><option value="">Selecciona una clase</option>'+opciones+'</select></div></div><p class="text-info small mt-3">Contraseña temporal: <strong>Ined1234</strong>.</p><button class="prof-btn prof-btn-primary" type="submit"><i class="fas fa-check"></i>Crear y asignar</button></form>':'<p class="text-warning">Necesitas tener al menos una clase activa asignada para registrar alumnos.</p>')+'</div>';var form=document.querySelector('#form-alumno form');if(form){activarSugerenciaUsuario(form,'/api/usuarios/sugerir-nombre');form.querySelector('[name="nombre_completo"]').focus();}
}
async function crearAlumno(evento){
    evento.preventDefault();var f=new FormData(evento.target);
    try{var d=await api('/api/usuarios',{method:'POST',body:JSON.stringify({nombre_completo:f.get('nombre_completo'),nombre:f.get('nombre'),rol:'Alumno',clase_id:f.get('clase_id'),clave:'Ined1234'})});mensaje('success',d.message);await mostrarAlumnos();}catch(e){if(e.data?.code==='USERNAME_TAKEN'&&e.data.suggested_username){var input=evento.target.querySelector('[name="nombre"]');input.value=e.data.suggested_username;input.focus();input.select();}mensaje('danger',e.message);}
}
function restablecer(id){
    if(!confirm('La contraseña se restablecerá a Ined1234. ¿Continuar?'))return;
    api('/api/usuarios/'+id+'/reset-password',{method:'POST',body:JSON.stringify({nueva_clave:'Ined1234'})}).then(function(d){mensaje('success',d.message);mostrarAlumnos();}).catch(function(e){mensaje('danger',e.message);});
}

async function mostrarProgreso(){
    abrir(cabecera('Progreso de mis clases','fa-chart-bar')+'<p>Cargando filtros...</p>');
    try{
        await cargarDatosBase();
        var clases='<option value="">Todas mis clases</option>'+clasesProfesor.map(function(c){return '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';}).join('');
        var alumnos='<option value="">Todos mis alumnos</option>'+alumnosProfesor.map(function(a){return '<option value="'+a.id+'">'+esc(a.nombre_completo||a.nombre)+' ('+esc(a.nombre)+')</option>';}).join('');
        abrir(cabecera('Progreso de mis clases','fa-chart-bar')+'<div class="teacher-card mb-4"><div class="row g-3 align-items-end"><div class="col-md-3"><label class="form-label">Clase</label><select id="filtro-clase" class="form-select">'+clases+'</select></div><div class="col-md-3"><label class="form-label">Alumno</label><select id="filtro-alumno" class="form-select">'+alumnos+'</select></div><div class="col-md-3"><label class="form-label" for="filtro-buscar-alumno">Buscar por nombre</label><input id="filtro-buscar-alumno" class="form-control" type="search" autocomplete="off" placeholder="Nombre completo o usuario" onkeydown="if(event.key===\'Enter\'){event.preventDefault();cargarProgreso();}"></div><div class="col-md-3"><label class="form-label">Módulo</label><select id="filtro-modulo" class="form-select"><option value="todos">Todos</option><option value="documentacion">Documentación</option><option value="tutoriales">Tutoriales</option><option value="desafios">Desafíos</option><option value="aventura">Aventura</option></select></div><div class="col-12 d-flex flex-wrap justify-content-end gap-2"><button class="prof-btn prof-btn-primary" onclick="cargarProgreso()"><i class="fas fa-search"></i>Buscar y consultar</button><button class="prof-btn prof-btn-secondary" onclick="limpiarFiltrosProgreso()" title="Restablece todos los filtros"><i class="fas fa-eraser"></i>Limpiar filtros</button><button class="prof-btn prof-btn-secondary" onclick="exportarProgreso()" title="Descarga el progreso filtrado en un archivo de Excel"><i class="fas fa-file-excel"></i><span>Exportar Excel</span></button></div></div></div><div id="resultado-progreso"></div>');
        await cargarProgreso();
    }catch(e){mensaje('danger',e.message);}
}
function filtros(){
    var p=new URLSearchParams(),clase=document.getElementById('filtro-clase')?.value,alumno=document.getElementById('filtro-alumno')?.value,modulo=document.getElementById('filtro-modulo')?.value||'todos',buscar=document.getElementById('filtro-buscar-alumno')?.value.trim();
    if(clase)p.set('clase_id',clase);if(alumno)p.set('alumno_id',alumno);if(buscar)p.set('buscar',buscar);p.set('modulo',modulo);return p;
}
function limpiarFiltrosProgreso(){
    var clase=document.getElementById('filtro-clase'),alumno=document.getElementById('filtro-alumno'),buscar=document.getElementById('filtro-buscar-alumno'),modulo=document.getElementById('filtro-modulo');
    if(clase)clase.value='';if(alumno)alumno.value='';if(buscar)buscar.value='';if(modulo)modulo.value='todos';cargarProgreso();
}
async function cargarProgreso(){
    var caja=document.getElementById('resultado-progreso');caja.innerHTML='<p>Cargando progreso...</p>';
    try{
        var d=await api('/api/profesor/progreso?'+filtros().toString());
        var promedios=Object.keys(d.resumen.promedios).map(function(m){return '<div class="col-6 col-lg-3"><div class="teacher-card"><div class="text-capitalize">'+esc(m)+'</div><strong class="fs-4 text-info">'+d.resumen.promedios[m]+'%</strong></div></div>';}).join('');
        var filas=d.alumnos.map(function(a){var modulos=Object.keys(a.modulos).map(function(m){var p=a.modulos[m];return '<div class="mb-2"><div class="d-flex justify-content-between"><span class="text-capitalize">'+esc(m)+'</span><span>'+p.porcentaje+'%</span></div><div class="progress"><div class="progress-bar bg-info" style="width:'+p.porcentaje+'%">'+p.completados+'/'+p.total+'</div></div><small class="text-muted">'+esc(p.detalle)+'</small></div>';}).join('');return '<tr><td><strong>'+esc(a.nombre)+'</strong><br><small>'+esc(a.clases.join(', '))+'</small></td><td>'+a.puntos_totales+'</td><td>'+modulos+'</td></tr>';}).join('');
        caja.innerHTML='<div class="row g-3 mb-4">'+promedios+'</div>'+(filas?'<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Alumno</th><th>Puntos</th><th>Avance</th></tr></thead><tbody>'+filas+'</tbody></table></div>':'<p class="text-muted">No hay alumnos para los filtros seleccionados.</p>');
    }catch(e){caja.innerHTML='<p class="text-danger">'+esc(e.message)+'</p>';}
}
function exportarProgreso(){window.location.href='/api/profesor/progreso/exportar?'+filtros().toString();}
function abrirModuloProfesorDesdeHash(){
    var modulo=location.hash.replace('#','');
    if(modulo==='alumnos')mostrarAlumnos();
    if(modulo==='progreso')mostrarProgreso();
}
document.addEventListener('DOMContentLoaded',abrirModuloProfesorDesdeHash);
window.addEventListener('hashchange',abrirModuloProfesorDesdeHash);
</script>
@endsection
