@extends('layouts.app')

@section('title', 'Administrador de Colegio - CodePlay')

@section('content')
<div class="container py-4 text-white">
    <div class="game-card p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <span class="text-warning text-uppercase small">Administración institucional</span>
                <h1 class="mb-1"><i class="fas fa-school me-2 text-warning"></i>Panel del Colegio</h1>
                <p class="mb-0">{{ session('usuario_colegio_nombre') }}</p>
            </div>
            <div class="text-end">
                <strong>{{ session('usuario_nombre') }}</strong>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión</button>
                </form>
            </div>
        </div>

        <div id="mensaje-panel"></div>
        <div id="menu-colegio" class="row g-3">
            <div class="col-md-6 col-xl-3"><button class="panel-option h-100 w-100" onclick="mostrarResumen()"><i class="fas fa-chart-line"></i><strong>Resumen</strong><span>Estadísticas generales del colegio</span></button></div>
            <div class="col-md-6 col-xl-3"><button class="panel-option h-100 w-100" onclick="mostrarUsuarios('Profesor')"><i class="fas fa-chalkboard-teacher"></i><strong>Profesores</strong><span>Crear y restablecer cuentas</span></button></div>
            <div class="col-md-6 col-xl-3"><button class="panel-option h-100 w-100" onclick="mostrarUsuarios('Alumno')"><i class="fas fa-user-graduate"></i><strong>Alumnos</strong><span>Crear y administrar estudiantes</span></button></div>
            <div class="col-md-6 col-xl-3"><button class="panel-option h-100 w-100" onclick="mostrarClases()"><i class="fas fa-users"></i><strong>Clases</strong><span>Crear clases y asignar miembros</span></button></div>
        </div>
        <section id="contenido-colegio" class="mt-4" style="display:none"></section>
    </div>
</div>

<style>
.panel-option{display:flex;flex-direction:column;align-items:flex-start;gap:.55rem;padding:1.4rem;color:#fff;text-align:left;background:rgba(10,31,38,.86);border:1px solid rgba(109,231,195,.45);border-radius:14px;transition:.2s ease}
.panel-option:hover{transform:translateY(-3px);border-color:#6de7c3;box-shadow:0 8px 24px rgba(0,0,0,.25)}
.panel-option i{font-size:1.8rem;color:#6de7c3}.panel-option strong{font-size:1.1rem}.panel-option span{color:#b8c9cd;font-size:.9rem}
.metric-card{background:rgba(7,28,34,.8);border:1px solid rgba(109,231,195,.3);border-radius:12px;padding:1rem;height:100%}
.metric-value{color:#6de7c3;font-size:1.75rem;font-weight:700}
.class-card{background:rgba(7,28,34,.78);border:1px solid rgba(255,255,255,.18);border-radius:12px;padding:1.2rem}
.member-chip{display:inline-flex;align-items:center;gap:.35rem;margin:.2rem;padding:.35rem .55rem;border-radius:20px;background:rgba(109,231,195,.14);border:1px solid rgba(109,231,195,.35)}
.member-chip button{border:0;background:transparent;color:#ff8f8f;padding:0}
</style>

<script>
const csrf = '{{ csrf_token() }}';
const apiBase = '/api/colegio';
let profesoresDisponibles = [];
let alumnosDisponibles = [];

document.addEventListener('DOMContentLoaded', mostrarResumen);

function contenido(){ return document.getElementById('contenido-colegio'); }
function esc(v){ return String(v == null ? '' : v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];}); }
function encabezado(titulo,icono){ return '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h2 class="h4 mb-0"><i class="fas '+icono+' me-2 text-warning"></i>'+titulo+'</h2><button class="btn btn-outline-light btn-sm" onclick="volverMenu()"><i class="fas fa-arrow-left me-1"></i>Menú</button></div>'; }
function abrir(html){ document.getElementById('menu-colegio').style.display='none';contenido().style.display='block';contenido().innerHTML=html; }
function volverMenu(){ contenido().style.display='none';contenido().innerHTML='';document.getElementById('menu-colegio').style.display=''; }
function mensaje(tipo,texto){var caja=document.getElementById('mensaje-panel');caja.innerHTML='<div class="alert alert-'+tipo+'">'+esc(texto)+'</div>';window.scrollTo({top:0,behavior:'smooth'});setTimeout(function(){caja.innerHTML='';},5000);}

async function peticion(url,opciones){
    opciones=opciones||{}; opciones.headers=Object.assign({},opciones.headers||{}, {'Accept':'application/json','X-CSRF-TOKEN':csrf});
    if(opciones.body) opciones.headers['Content-Type']='application/json';
    var respuesta=await fetch(url,opciones); var data=await respuesta.json().catch(function(){return {};});
    if(!respuesta.ok) throw new Error(data.message || 'No fue posible completar la operación.');
    return data;
}

function metrica(etiqueta,valor,icono){return '<div class="col-6 col-lg-3"><div class="metric-card"><i class="fas '+icono+' text-warning"></i><div class="metric-value">'+esc(valor)+'</div><div>'+etiqueta+'</div></div></div>';}

async function mostrarResumen(){
    abrir(encabezado('Resumen del Colegio','fa-chart-line')+'<p>Cargando estadísticas...</p>');
    try{
        var d=await peticion(apiBase+'/resumen'),u=d.usuarios,p=d.progreso;
        var destacados=d.alumnos_destacados.length?'<ol>'+d.alumnos_destacados.map(function(a){return '<li>'+esc(a.nombre)+' — <strong>'+a.puntos_totales+' puntos</strong></li>';}).join('')+'</ol>':'<p class="text-muted mb-0">Aún no hay alumnos registrados.</p>';
        abrir(encabezado('Resumen del Colegio','fa-chart-line')+'<h3 class="h5 mb-3">'+esc(d.colegio.nombre)+'</h3><div class="row g-3 mb-4">'+metrica('Profesores',u.profesores,'fa-chalkboard-teacher')+metrica('Alumnos',u.alumnos,'fa-user-graduate')+metrica('Clases activas',d.clases.activas,'fa-users')+metrica('Cuentas desactivadas',u.desactivados,'fa-user-slash')+'</div><h3 class="h5 mb-3">Progreso general</h3><div class="row g-3 mb-4">'+metrica('Puntos obtenidos',p.puntos_totales,'fa-star')+metrica('Tutoriales completados',p.tutoriales_completados,'fa-book-open')+metrica('Desafíos completados',p.desafios_completados,'fa-code')+metrica('Promedio de tests',Number(p.promedio_tests).toFixed(2),'fa-clipboard-check')+'</div><div class="class-card"><h3 class="h5">Alumnos con más puntos</h3>'+destacados+'</div>');
    }catch(e){abrir(encabezado('Resumen del Colegio','fa-chart-line')+'<p class="text-danger">'+esc(e.message)+'</p>');}
}

async function mostrarUsuarios(rol){
    var singular=rol==='Profesor'?'Profesor':'Alumno';
    abrir(encabezado(singular+'es',rol==='Profesor'?'fa-chalkboard-teacher':'fa-user-graduate')+'<p>Cargando...</p>');
    try{
        var usuarios=await peticion(apiBase+'/usuarios-por-rol/'+rol);
        var filas=usuarios.map(function(u){return '<tr><td>'+esc(u.nombre)+'</td><td>'+esc(u.email||'—')+'</td><td>'+(u.cambio_clave_requerido?'<span class="badge bg-warning text-dark">Cambio pendiente</span>':'<span class="badge bg-success">Actualizada</span>')+'</td><td>'+u.puntos_totales+'</td><td><button class="btn btn-sm btn-warning" onclick="restablecerCuenta('+u.id+',\''+rol+'\')"><i class="fas fa-key me-1"></i>Restablecer</button></td></tr>';}).join('');
        abrir(encabezado(singular+'es',rol==='Profesor'?'fa-chalkboard-teacher':'fa-user-graduate')+'<div class="d-flex justify-content-end mb-3"><button class="btn btn-success" onclick="formularioUsuario(\''+rol+'\')"><i class="fas fa-plus me-1"></i>Nuevo '+singular+'</button></div><div id="formulario-usuario"></div>'+(filas?'<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Nombre</th><th>Correo</th><th>Estado de clave</th><th>Puntos</th><th>Acciones</th></tr></thead><tbody>'+filas+'</tbody></table></div>':'<p class="text-muted">No hay usuarios registrados.</p>'));
    }catch(e){mensaje('danger',e.message);}
}

function formularioUsuario(rol){
    var profesor=rol==='Profesor';
    document.getElementById('formulario-usuario').innerHTML='<div class="class-card mb-4"><h3 class="h5">Crear '+rol+'</h3><form onsubmit="crearUsuario(event,\''+rol+'\')"><div class="row g-3"><div class="col-md-6"><label class="form-label">Nombre de usuario</label><input class="form-control" name="nombre" maxlength="100" required></div>'+(profesor?'<div class="col-md-6"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="email" maxlength="150" required><small class="text-info">Se usará para recuperar la cuenta.</small></div>':'')+'</div><p class="small text-info mt-3">Contraseña temporal: <strong>Ined1234</strong>.</p><button class="btn btn-success" type="submit">Crear cuenta</button> <button class="btn btn-secondary" type="button" onclick="document.getElementById(\'formulario-usuario\').innerHTML=\'\'">Cancelar</button></form></div>';
}

async function crearUsuario(evento,rol){
    evento.preventDefault();var f=new FormData(evento.target);
    try{var d=await peticion(apiBase+'/usuarios',{method:'POST',body:JSON.stringify({nombre:f.get('nombre'),email:f.get('email')||null,rol:rol,clave:'Ined1234'})});mensaje('success',d.message);mostrarUsuarios(rol);}
    catch(e){mensaje('danger',e.message);}
}
function restablecerCuenta(id,rol){
    if(!confirm('La contraseña se restablecerá a Ined1234. ¿Continuar?'))return;
    peticion(apiBase+'/usuarios/'+id+'/reset-password',{method:'POST',body:JSON.stringify({nueva_clave:'Ined1234'})}).then(function(d){mensaje('success',d.message);mostrarUsuarios(rol);}).catch(function(e){mensaje('danger',e.message);});
}

async function mostrarClases(){
    abrir(encabezado('Clases y Asignaciones','fa-users')+'<p>Cargando...</p>');
    try{var datos=await Promise.all([peticion(apiBase+'/usuarios-por-rol/Profesor'),peticion(apiBase+'/usuarios-por-rol/Alumno')]);profesoresDisponibles=datos[0];alumnosDisponibles=datos[1];await cargarClases();}
    catch(e){mensaje('danger',e.message);}
}
async function cargarClases(){
    var clases=await peticion(apiBase+'/clases');
    abrir(encabezado('Clases y Asignaciones','fa-users')+'<div class="d-flex justify-content-end mb-3"><button class="btn btn-success" onclick="formularioClase()"><i class="fas fa-plus me-1"></i>Nueva Clase</button></div><div id="formulario-clase"></div><div class="row g-3">'+(clases.length?clases.map(tarjetaClase).join(''):'<p class="text-muted">No hay clases registradas.</p>')+'</div>');
}
function formularioClase(){
    document.getElementById('formulario-clase').innerHTML='<div class="class-card mb-4"><h3 class="h5">Crear clase</h3><form onsubmit="crearClase(event)"><div class="row g-3"><div class="col-md-5"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="120" required></div><div class="col-md-3"><label class="form-label">Ciclo escolar</label><input class="form-control" name="ciclo_escolar" maxlength="20" value="'+new Date().getFullYear()+'" required></div><div class="col-md-4"><label class="form-label">Descripción</label><input class="form-control" name="descripcion" maxlength="1000"></div></div><button class="btn btn-success mt-3" type="submit">Crear clase</button></form></div>';
}
async function crearClase(evento){
    evento.preventDefault();try{var d=await peticion(apiBase+'/clases',{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(evento.target)))});mensaje('success',d.message);await cargarClases();}catch(e){mensaje('danger',e.message);}
}
function opciones(lista){return '<option value="">Selecciona...</option>'+lista.map(function(u){return '<option value="'+u.id+'">'+esc(u.nombre)+'</option>';}).join('');}
function chips(lista,tipo,claseId){return lista.length?lista.map(function(u){return '<span class="member-chip">'+esc(u.nombre)+'<button title="Quitar" onclick="quitarMiembro('+claseId+','+u.id+',\''+tipo+'\')"><i class="fas fa-times"></i></button></span>';}).join(''):'<span class="text-muted">Sin asignaciones</span>';}
function tarjetaClase(clase){
    var pids=clase.profesores.map(function(p){return p.id;}),aids=clase.alumnos.map(function(a){return a.id;});
    var profesores=profesoresDisponibles.filter(function(p){return pids.indexOf(p.id)<0;}),alumnos=alumnosDisponibles.filter(function(a){return aids.indexOf(a.id)<0;});
    return '<div class="col-12"><article class="class-card"><div class="d-flex justify-content-between"><div><h3 class="h5 mb-1">'+esc(clase.nombre)+'</h3><div class="small text-info">'+esc(clase.codigo)+' · Ciclo '+esc(clase.ciclo_escolar)+'</div></div><button class="btn btn-sm '+(clase.activa?'btn-outline-warning':'btn-outline-success')+'" onclick="cambiarEstadoClase('+clase.id+','+(!clase.activa)+')">'+(clase.activa?'Desactivar':'Activar')+'</button></div><p class="mt-2">'+esc(clase.descripcion||'Sin descripción')+'</p><div class="row g-3"><div class="col-lg-6"><strong>Profesores ('+clase.profesores_count+')</strong><div class="my-2">'+chips(clase.profesores,'profesores',clase.id)+'</div><div class="input-group"><select class="form-select" id="profesor-'+clase.id+'">'+opciones(profesores)+'</select><button class="btn btn-outline-info" onclick="asignarMiembro('+clase.id+',\'profesores\')">Asignar</button></div></div><div class="col-lg-6"><strong>Alumnos ('+clase.alumnos_count+')</strong><div class="my-2">'+chips(clase.alumnos,'alumnos',clase.id)+'</div><div class="input-group"><select class="form-select" id="alumno-'+clase.id+'">'+opciones(alumnos)+'</select><button class="btn btn-outline-info" onclick="asignarMiembro('+clase.id+',\'alumnos\')">Asignar</button></div></div></div></article></div>';
}
async function asignarMiembro(claseId,tipo){
    var prefijo=tipo==='profesores'?'profesor':'alumno',usuarioId=document.getElementById(prefijo+'-'+claseId).value;
    if(!usuarioId){mensaje('warning','Selecciona un usuario.');return;}
    try{var d=await peticion(apiBase+'/clases/'+claseId+'/'+tipo+'/'+usuarioId,{method:'POST'});mensaje('success',d.message);await mostrarClases();}catch(e){mensaje('danger',e.message);}
}
async function quitarMiembro(claseId,usuarioId,tipo){
    if(!confirm('¿Deseas retirar este usuario de la clase?'))return;
    try{var d=await peticion(apiBase+'/clases/'+claseId+'/'+tipo+'/'+usuarioId,{method:'DELETE'});mensaje('success',d.message);await mostrarClases();}catch(e){mensaje('danger',e.message);}
}
async function cambiarEstadoClase(claseId,activa){
    try{var d=await peticion(apiBase+'/clases/'+claseId,{method:'PUT',body:JSON.stringify({activa:activa})});mensaje('success',d.message);await cargarClases();}catch(e){mensaje('danger',e.message);}
}
</script>
@endsection
