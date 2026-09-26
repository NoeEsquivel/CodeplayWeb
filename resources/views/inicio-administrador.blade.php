@extends('layouts.app')

@section('title', 'Panel del Profesor - CodePlay')

@section('content')
<div class="container py-4 text-white">
    <div class="game-card p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <span class="text-warning text-uppercase small">Seguimiento académico</span>
                <h1 class="mb-1"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Panel del Profesor</h1>
                <p class="mb-0">{{ session('usuario_colegio_nombre') }}</p>
            </div>
            <div class="text-end">
                <strong>{{ session('usuario_nombre') }}</strong>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf
                    <button class="btn btn-sm btn-outline-light" type="submit"><i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión</button>
                </form>
            </div>
        </div>

        <div id="mensaje-profesor"></div>
        <div id="menu-profesor" class="row g-3">
            <div class="col-md-6">
                <button class="teacher-option w-100 h-100" onclick="mostrarAlumnos()">
                    <i class="fas fa-user-graduate"></i><strong>Mis alumnos</strong>
                    <span>Crear alumnos, asignar clases y restablecer contraseñas</span>
                </button>
            </div>
            <div class="col-md-6">
                <button class="teacher-option w-100 h-100" onclick="mostrarProgreso()">
                    <i class="fas fa-chart-bar"></i><strong>Progreso de mis clases</strong>
                    <span>Filtrar y exportar avances por alumno, clase y módulo</span>
                </button>
            </div>
        </div>
        <section id="contenido-profesor" class="mt-4" style="display:none"></section>
    </div>
</div>

<style>
.teacher-option{display:flex;flex-direction:column;align-items:flex-start;gap:.6rem;padding:1.5rem;text-align:left;color:#fff;background:rgba(10,31,38,.88);border:1px solid rgba(109,231,195,.45);border-radius:14px;transition:.2s}
.teacher-option:hover{transform:translateY(-3px);border-color:#6de7c3;box-shadow:0 8px 24px rgba(0,0,0,.25)}
.teacher-option i{font-size:2rem;color:#6de7c3}.teacher-option strong{font-size:1.2rem}.teacher-option span{color:#b8c9cd}
.teacher-card{background:rgba(7,28,34,.8);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:1.2rem}
.progress{height:1.15rem;background:#152d34}.progress-bar{color:#071a20;font-weight:700}
</style>

<script>
const csrf='{{ csrf_token() }}';
let clasesProfesor=[];
let alumnosProfesor=[];

function area(){return document.getElementById('contenido-profesor');}
function esc(v){return String(v==null?'':v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
function cabecera(titulo,icono){return '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h2 class="h4 mb-0"><i class="fas '+icono+' me-2 text-warning"></i>'+titulo+'</h2><button class="btn btn-outline-light btn-sm" onclick="volverMenuProfesor()"><i class="fas fa-arrow-left me-1"></i>Menú</button></div>';}
function abrir(html){document.getElementById('menu-profesor').style.display='none';area().style.display='block';area().innerHTML=html;}
function volverMenuProfesor(){area().style.display='none';area().innerHTML='';document.getElementById('menu-profesor').style.display='';}
function mensaje(tipo,texto){var c=document.getElementById('mensaje-profesor');c.innerHTML='<div class="alert alert-'+tipo+'">'+esc(texto)+'</div>';window.scrollTo({top:0,behavior:'smooth'});setTimeout(function(){c.innerHTML='';},5000);}
async function api(url,opciones){opciones=opciones||{};opciones.headers=Object.assign({},opciones.headers||{},{'Accept':'application/json','X-CSRF-TOKEN':csrf});if(opciones.body)opciones.headers['Content-Type']='application/json';var r=await fetch(url,opciones);var d=await r.json().catch(function(){return {};});if(!r.ok)throw new Error(d.message||Object.values(d.errors||{})[0]?.[0]||'No fue posible completar la operación.');return d;}

async function cargarDatosBase(){
    var datos=await Promise.all([api('/api/profesor/clases'),api('/api/usuarios-por-rol/Alumno')]);
    clasesProfesor=datos[0];alumnosProfesor=datos[1];
}

async function mostrarAlumnos(){
    abrir(cabecera('Mis alumnos','fa-user-graduate')+'<p>Cargando alumnos...</p>');
    try{
        await cargarDatosBase();
        var clasesPorAlumno={};
        clasesProfesor.forEach(function(c){c.alumnos.forEach(function(a){if(!clasesPorAlumno[a.id])clasesPorAlumno[a.id]=[];clasesPorAlumno[a.id].push(c.nombre);});});
        var filas=alumnosProfesor.map(function(a){return '<tr><td>'+esc(a.nombre)+'</td><td>'+esc((clasesPorAlumno[a.id]||[]).join(', '))+'</td><td>'+a.puntos_totales+'</td><td>'+(a.cambio_clave_requerido?'<span class="badge bg-warning text-dark">Cambio pendiente</span>':'<span class="badge bg-success">Actualizada</span>')+'</td><td><button class="btn btn-sm btn-warning" onclick="restablecer('+a.id+')"><i class="fas fa-key me-1"></i>Restablecer</button></td></tr>';}).join('');
        abrir(cabecera('Mis alumnos','fa-user-graduate')+'<div class="d-flex justify-content-end mb-3"><button class="btn btn-success" onclick="formularioAlumno()"><i class="fas fa-plus me-1"></i>Nuevo alumno</button></div><div id="form-alumno"></div>'+(filas?'<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Alumno</th><th>Mis clases</th><th>Puntos</th><th>Contraseña</th><th>Acciones</th></tr></thead><tbody>'+filas+'</tbody></table></div>':'<p class="text-muted">No hay alumnos vinculados a tus clases.</p>'));
    }catch(e){mensaje('danger',e.message);}
}

function formularioAlumno(){
    var opciones=clasesProfesor.filter(function(c){return c.activa;}).map(function(c){return '<option value="'+c.id+'">'+esc(c.nombre)+'</option>';}).join('');
    document.getElementById('form-alumno').innerHTML='<div class="teacher-card mb-4"><h3 class="h5">Registrar alumno</h3>'+(opciones?'<form onsubmit="crearAlumno(event)"><div class="row g-3"><div class="col-md-6"><label class="form-label">Nombre de usuario</label><input class="form-control" name="nombre" maxlength="100" required></div><div class="col-md-6"><label class="form-label">Clase inicial</label><select class="form-select" name="clase_id" required><option value="">Selecciona una clase</option>'+opciones+'</select></div></div><p class="text-info small mt-3">Contraseña temporal: <strong>Ined1234</strong>.</p><button class="btn btn-success" type="submit">Crear y asignar</button></form>':'<p class="text-warning">Necesitas tener al menos una clase activa asignada para registrar alumnos.</p>')+'</div>';
}
async function crearAlumno(evento){
    evento.preventDefault();var f=new FormData(evento.target);
    try{var d=await api('/api/usuarios',{method:'POST',body:JSON.stringify({nombre:f.get('nombre'),rol:'Alumno',clase_id:f.get('clase_id'),clave:'Ined1234'})});mensaje('success',d.message);await mostrarAlumnos();}catch(e){mensaje('danger',e.message);}
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
        var alumnos='<option value="">Todos mis alumnos</option>'+alumnosProfesor.map(function(a){return '<option value="'+a.id+'">'+esc(a.nombre)+'</option>';}).join('');
        abrir(cabecera('Progreso de mis clases','fa-chart-bar')+'<div class="teacher-card mb-4"><div class="row g-3 align-items-end"><div class="col-md-3"><label class="form-label">Clase</label><select id="filtro-clase" class="form-select">'+clases+'</select></div><div class="col-md-3"><label class="form-label">Alumno</label><select id="filtro-alumno" class="form-select">'+alumnos+'</select></div><div class="col-md-3"><label class="form-label">Módulo</label><select id="filtro-modulo" class="form-select"><option value="todos">Todos</option><option value="documentacion">Documentación</option><option value="tutoriales">Tutoriales</option><option value="desafios">Desafíos</option><option value="aventura">Aventura</option></select></div><div class="col-md-3 d-flex gap-2"><button class="btn btn-info flex-fill" onclick="cargarProgreso()">Consultar</button><button class="btn btn-outline-success" onclick="exportarProgreso()"><i class="fas fa-file-csv"></i></button></div></div></div><div id="resultado-progreso"></div>');
        await cargarProgreso();
    }catch(e){mensaje('danger',e.message);}
}
function filtros(){
    var p=new URLSearchParams(),clase=document.getElementById('filtro-clase')?.value,alumno=document.getElementById('filtro-alumno')?.value,modulo=document.getElementById('filtro-modulo')?.value||'todos';
    if(clase)p.set('clase_id',clase);if(alumno)p.set('alumno_id',alumno);p.set('modulo',modulo);return p;
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
</script>
@endsection
