@extends('layouts.app')

@section('title', 'Administrador de Colegio - CodePlay')

@section('content')
<div class="school-admin-page">
    <div class="container-fluid school-admin-container">
        <main class="school-admin-frame">
        <header class="school-admin-header">
            <div>
                <span class="school-admin-kicker"><i class="fas fa-shield-halved"></i> Administración institucional</span>
                <h1>Panel del Colegio</h1>
                <p>Gestiona profesores, alumnos y clases de <strong>{{ session('usuario_colegio_nombre') }}</strong> desde un solo lugar.</p>
            </div>
            <div class="school-admin-seal" aria-hidden="true"><i class="fas fa-school"></i></div>
        </header>

        <div id="inicio-colegio">
            <section id="metricas-iniciales" class="row g-3 mb-4" aria-label="Estado general del colegio">
                <div class="col-12"><p class="text-muted mb-0"><i class="fas fa-spinner fa-spin me-2"></i>Cargando información del colegio...</p></div>
            </section>
            <div id="menu-colegio" class="school-admin-menu">
                <button class="panel-option" onclick="navegarSeccion('profesores')" title="Crea profesores y recupera el acceso a sus cuentas"><span class="panel-option-icon"><i class="fas fa-chalkboard-teacher"></i></span><span><small>Personal docente</small><strong>Profesores</strong><em>Crea cuentas con correo y restablece contraseñas.</em></span><i class="fas fa-chevron-right"></i></button>
                <button class="panel-option" onclick="navegarSeccion('alumnos')" title="Registra y administra las cuentas de estudiantes"><span class="panel-option-icon"><i class="fas fa-user-graduate"></i></span><span><small>Comunidad estudiantil</small><strong>Alumnos</strong><em>Registra estudiantes y administra su acceso.</em></span><i class="fas fa-chevron-right"></i></button>
                <button class="panel-option" onclick="navegarSeccion('clases')" title="Crea clases y asigna profesores y alumnos"><span class="panel-option-icon"><i class="fas fa-users-rectangle"></i></span><span><small>Organización académica</small><strong>Clases y asignaciones</strong><em>Organiza grupos y controla sus integrantes.</em></span><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <section id="contenido-colegio" class="school-admin-content" hidden aria-live="polite"></section>
        </main>
    </div>
</div>

<div id="mensaje-panel" class="admin-toast-stack" aria-live="polite" aria-atomic="false"></div>
<div id="admin-modal-root"></div>

<dialog class="admin-confirm-dialog" id="admin-confirm-dialog" aria-labelledby="admin-confirm-title">
    <div class="admin-confirm-head"><span><i class="fas fa-circle-question"></i></span><div><small>CONFIRMACIÓN</small><h2 id="admin-confirm-title">Confirma la acción</h2></div></div>
    <p id="admin-confirm-message"></p>
    <div class="admin-confirm-actions"><button type="button" class="admin-btn" id="admin-confirm-cancel"><i class="fas fa-arrow-left"></i>Cancelar</button><button type="button" class="admin-btn admin-btn-danger" id="admin-confirm-accept"><i class="fas fa-check"></i>Confirmar</button></div>
</dialog>

<style>
body{background:#06131b url("{{ asset('Imagenes/fondos/panel-profesor-codeplay.png') }}") center/cover fixed no-repeat!important;color:#edf9f6}body::before{content:"";position:fixed;inset:0;z-index:-1;background:linear-gradient(180deg,#03101898,#041016ea),radial-gradient(circle at 50% 15%,transparent,#02090ee0 88%)}.codeplay-navbar{background:#07151cf0!important;border-bottom:1px solid #79dabb32;backdrop-filter:blur(14px);box-shadow:0 10px 35px #0006}.school-admin-page{min-height:calc(100vh - 70px);padding:clamp(8px,2vw,28px) 0 58px;font-size:17px;line-height:1.6}.school-admin-container{max-width:1500px}.school-admin-frame{padding:clamp(22px,3vw,42px);border:1px solid #69c5aa59;border-radius:23px;background:linear-gradient(145deg,#071922f5,#0b2028ed);box-shadow:0 28px 75px #000a,inset 0 1px #d8fff319;backdrop-filter:blur(17px)}.school-admin-header{display:flex;justify-content:space-between;align-items:center;gap:24px;padding-bottom:26px;margin-bottom:24px;border-bottom:1px solid #405e65}.school-admin-kicker{display:inline-flex;align-items:center;gap:9px;margin-bottom:9px;color:#f2c867;font-size:14px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.school-admin-header h1{margin:0;font-family:Georgia,serif;font-size:clamp(34px,4.5vw,52px);font-weight:500}.school-admin-header p{max-width:760px;margin:8px 0 0;color:#b4c6c7}.school-admin-seal{width:78px;height:78px;display:grid;place-items:center;flex:0 0 auto;border:1px solid #e5c46580;border-radius:20px;background:#e5c46515;color:#f2ce6d;font-size:30px}.school-admin-menu{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}.panel-option{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:16px;min-height:138px;padding:21px;border:1px solid #3d6067;border-radius:16px;background:linear-gradient(145deg,#102b34,#0a2029);color:#edf9f6;text-align:left;transition:.2s}.panel-option:hover,.panel-option:focus-visible{transform:translateY(-3px);border-color:#69e4bd;box-shadow:0 15px 30px #0006;outline:0}.panel-option-icon{width:58px;height:58px;display:grid;place-items:center;border:1px solid #55c7a870;border-radius:15px;background:#071b23;color:#6de8c0;font-size:23px}.panel-option small,.panel-option strong,.panel-option em{display:block}.panel-option small{color:#f2c867;font-size:14px;font-weight:900;letter-spacing:.07em;text-transform:uppercase}.panel-option strong{margin:3px 0;font-family:Georgia,serif;font-size:25px;font-weight:500}.panel-option em{color:#adc1c2;font-size:15px;font-style:normal}.panel-option>i{color:#789497}.school-admin-content{margin-top:30px;padding-top:25px;border-top:1px solid #405e65}.admin-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px}.admin-section-head h2{margin:0;font-family:Georgia,serif;font-size:31px;font-weight:500}.admin-section-head p{margin:4px 0 0;color:#aabfc0}.admin-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:11px 15px;border:1px solid #526f75;border-radius:10px;background:#102831;color:#eafaf6;text-decoration:none;font-size:15px;font-weight:800;transition:.2s}.admin-btn:hover{transform:translateY(-2px);border-color:#75e2bf;background:#17363f;color:#fff}.admin-btn-primary{border-color:#83efca;background:linear-gradient(110deg,#00d995,#19b989);color:#032018}.admin-btn-gold{border-color:#e1c06e;background:#352f1c;color:#ffe399}.admin-btn-danger{border-color:#a7555c;background:#321d23;color:#ffc8ca}.metric-card{height:100%;padding:17px 18px;border:1px solid #36585f;border-radius:13px;background:#0c252e}.metric-card>i{color:#f2c867}.metric-value{margin-top:5px;color:#70e7c1;font:500 30px Georgia,serif}.class-card{padding:20px;border:1px solid #395c63;border-radius:14px;background:linear-gradient(145deg,#0d2730e8,#091e27e8)}.class-card h3{font-family:Georgia,serif;font-size:24px}.member-chip{display:inline-flex;align-items:center;gap:7px;margin:3px;padding:6px 9px;border-radius:20px;background:#6de7c31c;border:1px solid #6de7c359}.member-chip button{border:0;background:transparent;color:#ffaaaa;padding:0}.school-admin-page .form-label{font-size:15px;font-weight:800}.school-admin-page .form-control,.school-admin-page .form-select{min-height:46px;border:1px solid #406469;border-radius:9px;background:#071a21;color:#f2fbf9;font-size:16px}.school-admin-page .form-control::placeholder{color:#89a1a4}.school-admin-page .form-control:focus,.school-admin-page .form-select:focus{border-color:#62dfb9;box-shadow:0 0 0 3px #00efa418;background:#092129;color:#fff}.school-admin-page .form-select option{background:#071a21}.school-admin-page .table-responsive{border:1px solid #3b5a61;border-radius:13px;overflow-x:auto}.school-admin-page .table{margin:0;font-size:15px;--bs-table-bg:#0b222b;--bs-table-striped-bg:#102b34;--bs-table-color:#e9f5f2;--bs-table-border-color:#304e55}.school-admin-page .table thead{--bs-table-bg:#15333c;color:#82e7c7}.school-admin-page .table th{font-size:14px;letter-spacing:.05em;text-transform:uppercase}.school-admin-page .text-muted{color:#a9bfc0!important}.school-admin-page .text-info{color:#70dfd0!important}.admin-help{display:flex;gap:10px;margin:14px 0;padding:12px 14px;border-left:3px solid #e7c765;border-radius:0 9px 9px 0;background:#302a186b;color:#eee1bd;font-size:15px}.admin-confirm-dialog{width:min(570px,calc(100% - 28px));padding:27px;border:1px solid #69e0ba;border-radius:20px;background:linear-gradient(145deg,#0d2b34,#071a22);color:#eefaf7;box-shadow:0 34px 100px #000d}.admin-confirm-dialog::backdrop{background:#020c11db;backdrop-filter:blur(7px)}.admin-confirm-head{display:flex;align-items:center;gap:16px}.admin-confirm-head>span{width:62px;height:62px;display:grid;place-items:center;border-radius:17px;background:linear-gradient(145deg,#ffd463,#ff8a75);color:#2b1704;font-size:26px}.admin-confirm-head small{color:#f2c867;font-weight:900;letter-spacing:.12em}.admin-confirm-head h2{margin:4px 0;font:500 29px Georgia,serif}.admin-confirm-dialog>p{margin:20px 0;color:#c8d9d7;font-size:17px;line-height:1.6}.admin-confirm-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}@media(max-width:1100px){.school-admin-menu{grid-template-columns:1fr}}@media(max-width:600px){.school-admin-frame{padding:18px 13px}.school-admin-header{align-items:flex-start}.school-admin-seal{display:none}.panel-option{grid-template-columns:auto 1fr}.panel-option>i{display:none}.admin-section-head{align-items:flex-start;flex-direction:column}.admin-btn{width:100%}.admin-confirm-actions{flex-direction:column}}
</style>

<style>
.admin-toast-stack{position:fixed;top:88px;right:22px;z-index:2200;display:grid;gap:11px;width:min(430px,calc(100vw - 28px));pointer-events:none}.admin-toast{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:12px;padding:15px 16px;border:1px solid #5d777d;border-left-width:5px;border-radius:13px;background:#09212af5;color:#f1fbf8;box-shadow:0 18px 45px #000b;backdrop-filter:blur(15px);pointer-events:auto;animation:adminToastIn .28s ease-out}.admin-toast>i{font-size:21px}.admin-toast>span{font-size:16px;font-weight:700;line-height:1.4}.admin-toast>button{width:32px;height:32px;border:0;border-radius:8px;background:#ffffff12;color:#d9e8e5}.admin-toast-success{border-color:#52ddb0}.admin-toast-success>i{color:#62e6bb}.admin-toast-danger{border-color:#e86b75}.admin-toast-danger>i{color:#ff9da4}.admin-toast-warning{border-color:#e6c45f}.admin-toast-warning>i{color:#f5d779}.admin-toast-info{border-color:#62cad8}.admin-toast-info>i{color:#7cdae5}.admin-toast.is-leaving{opacity:0;transform:translateX(30px);transition:.25s}.admin-row-actions{display:flex;gap:7px;flex-wrap:wrap;min-width:310px}.admin-row-actions .admin-btn{min-height:40px;padding:8px 11px;font-size:14px}.admin-form-overlay{position:fixed;inset:0;z-index:2050;display:grid;place-items:center;padding:24px;background:#020b10c9;backdrop-filter:blur(7px);animation:adminFadeIn .2s ease-out}.admin-form-modal{width:min(980px,100%);max-height:calc(100vh - 48px);overflow-y:auto;box-shadow:0 28px 90px #000d;animation:adminModalIn .24s ease-out}#admin-modal-root .form-label{font-size:15px;font-weight:800}#admin-modal-root .form-control,#admin-modal-root .form-select{min-height:46px;border:1px solid #406469;border-radius:9px;background:#071a21;color:#f2fbf9;font-size:16px}#admin-modal-root .form-control::placeholder{color:#89a1a4}#admin-modal-root .form-control:focus,#admin-modal-root .form-select:focus{border-color:#62dfb9;box-shadow:0 0 0 3px #00efa418;background:#092129;color:#fff}#admin-modal-root .form-select option{background:#071a21}#admin-modal-root .text-muted{color:#a9bfc0!important}#admin-modal-root .text-info{color:#70dfd0!important}@keyframes adminFadeIn{from{opacity:0}to{opacity:1}}@keyframes adminModalIn{from{opacity:0;transform:translateY(14px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}@keyframes adminToastIn{from{opacity:0;transform:translateX(36px)}to{opacity:1;transform:translateX(0)}}@media(max-width:600px){.admin-toast-stack{top:76px;right:14px}.admin-row-actions{min-width:220px}.admin-row-actions .admin-btn{width:auto}.admin-form-overlay{padding:12px}.admin-form-modal{max-height:calc(100vh - 24px)}}
</style>

<style>
.student-filters{display:grid;grid-template-columns:minmax(260px,1.35fr) minmax(210px,1fr) minmax(170px,.7fr) auto;gap:12px;align-items:end;margin:18px 0 8px;padding:16px;border:1px solid #3b5b62;border-radius:14px;background:#091f27}.student-filters .input-group-text{border-color:#406469;background:#102b34;color:#6de8c0}.student-filter-count{margin:0 0 10px;color:#9fb6b7;font-size:15px}.graduation-modal{width:min(920px,100%)}.graduation-step{margin-bottom:6px;color:#f2c867;font-size:13px;font-weight:900;letter-spacing:.15em}.graduation-review{margin-top:22px;padding-top:20px;border-top:1px solid #3b5960}.graduation-review h4{font-family:Georgia,serif;font-size:25px}.graduation-summary{display:flex;justify-content:space-between;gap:12px;margin:14px 0;padding:12px 14px;border-radius:10px;background:#0a1c23;color:#87e9ca}.graduating-list{display:grid;gap:7px;max-height:300px;padding:4px;overflow-y:auto}.graduating-student{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:12px;padding:11px 13px;border:1px solid #35575e;border-radius:10px;background:#0b252d;cursor:pointer}.graduating-student:hover{border-color:#67d9b5}.graduating-student span,.graduating-student small{display:block}.graduating-student small{color:#9fb3b5}.graduating-student em{color:#f2cc6d;font-size:14px;font-style:normal;font-weight:800}.graduation-ack{display:flex;align-items:flex-start;gap:11px;margin-top:15px;padding:13px;border:1px solid #d1ae516b;border-radius:10px;background:#362e1961;color:#f1e4bd;cursor:pointer}.graduation-ack .form-check-input,.graduating-student .form-check-input{flex:0 0 auto;margin-top:4px}.admin-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}@media(max-width:1000px){.student-filters{grid-template-columns:1fr 1fr}.student-filters>.admin-btn{width:auto}}@media(max-width:650px){.student-filters{grid-template-columns:1fr}.graduation-summary{flex-direction:column}.graduating-student{grid-template-columns:auto 1fr}.graduating-student em{grid-column:2}}
</style>

<script>
const csrf = '{{ csrf_token() }}';
const apiBase = '/api/colegio';
let profesoresDisponibles = [];
let alumnosDisponibles = [];
let clasesActivas = [];
let clasesDelColegio = [];
let usuariosEnVista = [];
let graduandosEnVista = [];
let alcanceGraduandos = null;
let accionConfirmada = null;

document.addEventListener('DOMContentLoaded', function(){
    cargarMetricasIniciales();
    abrirDesdeDireccion();
    var dialogo=document.getElementById('admin-confirm-dialog');
    document.getElementById('admin-confirm-cancel').addEventListener('click',function(){dialogo.close();accionConfirmada=null;});
    document.getElementById('admin-confirm-accept').addEventListener('click',async function(){var accion=accionConfirmada;accionConfirmada=null;dialogo.close();if(accion)await accion();});
    dialogo.addEventListener('click',function(evento){if(evento.target===dialogo){dialogo.close();accionConfirmada=null;}});
});
window.addEventListener('hashchange',abrirDesdeDireccion);

function contenido(){ return document.getElementById('contenido-colegio'); }
function esc(v){ return String(v == null ? '' : v).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];}); }
function descripcionSeccion(titulo){if(titulo.indexOf('Resumen')===0)return 'Consulta las cifras principales y el progreso acumulado de tu institución.';if(titulo.indexOf('Profesor')===0)return 'Crea cuentas docentes con correo y recupera su acceso cuando sea necesario.';if(titulo.indexOf('Alumno')===0)return 'Registra estudiantes y administra sus credenciales dentro de tu colegio.';return 'Crea grupos y asigna únicamente profesores y alumnos de tu institución.';}
function encabezado(titulo,icono){ return '<div class="admin-section-head"><div><h2><i class="fas '+icono+' me-2 text-warning"></i>'+titulo+'</h2><p>'+descripcionSeccion(titulo)+'</p></div><button class="admin-btn" onclick="volverMenu()" title="Regresa a las cuatro áreas principales del panel"><i class="fas fa-arrow-left"></i>Menú principal</button></div>'; }
function abrir(html){ document.getElementById('inicio-colegio').hidden=true;contenido().hidden=false;contenido().innerHTML=html; }
function volverMenu(){ contenido().hidden=true;contenido().innerHTML='';document.getElementById('inicio-colegio').hidden=false;history.replaceState(null,'',location.pathname+location.search); }
function navegarSeccion(seccion,actualizar){if(!['profesores','alumnos','clases'].includes(seccion)){volverMenu();return;}if(actualizar!==false)history.replaceState(null,'',location.pathname+location.search+'#'+seccion);if(seccion==='profesores')mostrarUsuarios('Profesor');else if(seccion==='alumnos')mostrarUsuarios('Alumno');else mostrarClases();}
function abrirDesdeDireccion(){var seccion=location.hash.slice(1);if(['profesores','alumnos','clases'].includes(seccion))navegarSeccion(seccion,false);else{document.getElementById('inicio-colegio').hidden=false;contenido().hidden=true;contenido().innerHTML='';if(seccion)history.replaceState(null,'',location.pathname+location.search);}}
function mensaje(tipo,texto){
    var caja=document.getElementById('mensaje-panel'),aviso=document.createElement('div'),iconos={success:'fa-circle-check',danger:'fa-circle-xmark',warning:'fa-triangle-exclamation',info:'fa-circle-info'};
    aviso.className='admin-toast admin-toast-'+tipo;aviso.setAttribute('role',tipo==='danger'?'alert':'status');
    var icono=document.createElement('i');icono.className='fas '+(iconos[tipo]||iconos.info);
    var copia=document.createElement('span');copia.textContent=texto;
    var cerrar=document.createElement('button');cerrar.type='button';cerrar.setAttribute('aria-label','Cerrar aviso');cerrar.innerHTML='<i class="fas fa-times"></i>';
    cerrar.addEventListener('click',function(){aviso.remove();});aviso.append(icono,copia,cerrar);caja.appendChild(aviso);
    setTimeout(function(){aviso.classList.add('is-leaving');setTimeout(function(){aviso.remove();},250);},5000);
}
function confirmarAccion(titulo,mensajeConfirmacion,textoBoton,accion){var dialogo=document.getElementById('admin-confirm-dialog');document.getElementById('admin-confirm-title').textContent=titulo;document.getElementById('admin-confirm-message').textContent=mensajeConfirmacion;document.getElementById('admin-confirm-accept').innerHTML='<i class="fas fa-check"></i>'+textoBoton;accionConfirmada=accion;dialogo.showModal();}

async function peticion(url,opciones){
    opciones=opciones||{}; opciones.headers=Object.assign({},opciones.headers||{}, {'Accept':'application/json','X-CSRF-TOKEN':csrf});
    if(opciones.body) opciones.headers['Content-Type']='application/json';
    var respuesta=await fetch(url,opciones); var data=await respuesta.json().catch(function(){return {};});
    if(!respuesta.ok){var error=new Error(data.message || Object.values(data.errors||{})[0]?.[0] || 'No fue posible completar la operación.');error.data=data;throw error;}
    return data;
}


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

function metrica(etiqueta,valor,icono){return '<div class="col-6 col-lg-3"><div class="metric-card"><i class="fas '+icono+' text-warning"></i><div class="metric-value">'+esc(valor)+'</div><div>'+etiqueta+'</div></div></div>';}
async function cargarMetricasIniciales(){
    var caja=document.getElementById('metricas-iniciales');
    try{var d=await peticion(apiBase+'/resumen'),u=d.usuarios;caja.innerHTML=metrica('Profesores',u.profesores,'fa-chalkboard-teacher')+metrica('Alumnos',u.alumnos,'fa-user-graduate')+metrica('Clases activas',d.clases.activas,'fa-users')+metrica('Cuentas desactivadas',u.desactivados,'fa-user-slash');}
    catch(e){caja.innerHTML='<div class="col-12"><div class="alert alert-warning mb-0">'+esc(e.message)+'</div></div>';}
}

async function mostrarUsuarios(rol,actualizacionSilenciosa){
    var singular=rol==='Profesor'?'Profesor':'Alumno',plural=rol==='Profesor'?'profesores':'alumnos',titulo=rol==='Profesor'?'Profesores':'Alumnos';
    if(!actualizacionSilenciosa)abrir(encabezado(titulo,rol==='Profesor'?'fa-chalkboard-teacher':'fa-user-graduate')+'<p>Cargando...</p>');
    try{
        var solicitudes=[peticion(apiBase+'/usuarios-por-rol/'+rol),peticion(apiBase+'/clases')];if(rol==='Alumno')solicitudes.push(peticion(apiBase+'/usuarios-por-rol/Profesor'));
        var resultados=await Promise.all(solicitudes);
        usuariosEnVista=resultados[0];clasesDelColegio=resultados[1];clasesActivas=clasesDelColegio.filter(function(clase){return clase.activa;});profesoresDisponibles=(rol==='Alumno'?resultados[2]:resultados[0]).filter(function(profesor){return profesor.activo!==false;});
        var filtros=rol==='Alumno'?filtrosAlumnosHtml():'';
        var botones='<div class="d-flex justify-content-end gap-2 flex-wrap mb-3">'+(rol==='Alumno'?'<button class="admin-btn admin-btn-danger" title="Elimina por clase o ciclo a los alumnos graduandos, conservando los repitentes" onclick="abrirGraduandos()"><i class="fas fa-user-graduate"></i>Eliminar alumnos graduandos</button>':'')+'<button class="admin-btn admin-btn-primary" title="Abre el formulario para registrar una nueva cuenta" onclick="formularioUsuario(\''+rol+'\')"><i class="fas fa-plus"></i>Crear '+singular.toLowerCase()+'</button></div>';
        abrir(encabezado(titulo,rol==='Profesor'?'fa-chalkboard-teacher':'fa-user-graduate')+'<div class="admin-help"><i class="fas fa-lightbulb"></i><span>El nombre completo identifica a la persona; el nombre de usuario se utiliza únicamente para ingresar al sistema.</span></div>'+botones+filtros+'<div id="tabla-usuarios">'+tablaUsuarios(rol,usuariosEnVista,plural)+'</div>');
        if(rol==='Alumno')aplicarFiltrosAlumnos();
    }catch(e){mensaje('danger',e.message);}
}

function relacionesDeUsuario(usuario,rol){return rol==='Profesor'?(usuario.clases_como_profesor||[]):(usuario.clases_como_alumno||[]);}
function chipsClasesUsuario(usuario,rol){var clases=relacionesDeUsuario(usuario,rol);return clases.length?clases.map(function(clase){return '<span class="member-chip">'+esc(clase.nombre)+' <small>'+esc(clase.ciclo_escolar||'')+'</small></span>';}).join(''):'<span class="text-muted">Sin clase asignada</span>';}
function accionesUsuario(usuario,rol){return '<div class="admin-row-actions"><button class="admin-btn" title="Modifica los datos de esta cuenta" onclick="formularioEditarUsuario('+usuario.id+',\''+rol+'\')"><i class="fas fa-pen"></i>Editar</button><button class="admin-btn admin-btn-gold" title="Genera la contraseña temporal Ined1234 para recuperar el acceso" onclick="restablecerCuenta('+usuario.id+',\''+rol+'\')"><i class="fas fa-key"></i>Contraseña</button><button class="admin-btn admin-btn-danger" title="Elimina definitivamente esta cuenta" onclick="eliminarUsuario('+usuario.id+',\''+rol+'\')"><i class="fas fa-trash"></i>Eliminar</button></div>';}
function tablaUsuarios(rol,usuarios,plural){
    if(!usuarios.length)return '<div class="class-card text-center text-muted">No se encontraron '+plural+' con los filtros seleccionados.</div>';
    var filas=usuarios.map(function(u){return '<tr><td><strong>'+esc(u.nombre_completo||u.nombre)+'</strong><br><small>Usuario de acceso: '+esc(u.nombre)+'</small></td>'+(rol==='Profesor'?'<td>'+esc(u.email||'No registrado')+'</td>':'')+'<td>'+chipsClasesUsuario(u,rol)+'</td><td>'+(u.cambio_clave_requerido?'<span class="badge bg-warning text-dark">Debe cambiarla</span>':'<span class="badge bg-success">Configurada</span>')+'</td>'+(rol==='Alumno'?'<td>'+u.puntos_totales+'</td>':'')+'<td>'+accionesUsuario(u,rol)+'</td></tr>';}).join('');
    return '<div class="table-responsive"><table class="table table-dark table-striped align-middle"><thead><tr><th>Persona y usuario</th>'+(rol==='Profesor'?'<th>Correo</th>':'')+'<th>Clases</th><th>Contraseña</th>'+(rol==='Alumno'?'<th>Puntos CodePlay</th>':'')+'<th>Acciones</th></tr></thead><tbody>'+filas+'</tbody></table></div>';
}
function filtrosAlumnosHtml(){
    var ciclos=[...new Set(clasesDelColegio.map(function(clase){return String(clase.ciclo_escolar||'');}).filter(Boolean))].sort().reverse();
    return '<div class="student-filters"><div><label class="form-label">Buscar alumno</label><div class="input-group"><span class="input-group-text"><i class="fas fa-search"></i></span><input class="form-control" id="filtro-alumno-texto" placeholder="Nombre completo o usuario" oninput="aplicarFiltrosAlumnos()"></div></div><div><label class="form-label">Clase</label><select class="form-select" id="filtro-alumno-clase" onchange="aplicarFiltrosAlumnos()"><option value="">Todas las clases</option>'+clasesDelColegio.map(function(clase){return '<option value="'+clase.id+'">'+esc(clase.nombre)+' · '+esc(clase.ciclo_escolar)+'</option>';}).join('')+'</select></div><div><label class="form-label">Ciclo escolar</label><select class="form-select" id="filtro-alumno-ciclo" onchange="aplicarFiltrosAlumnos()"><option value="">Todos los ciclos</option>'+ciclos.map(function(ciclo){return '<option value="'+esc(ciclo)+'">'+esc(ciclo)+'</option>';}).join('')+'</select></div><button class="admin-btn" type="button" onclick="limpiarFiltrosAlumnos()"><i class="fas fa-eraser"></i>Limpiar</button></div><p class="student-filter-count" id="conteo-alumnos"></p>';
}
function aplicarFiltrosAlumnos(){
    var texto=(document.getElementById('filtro-alumno-texto')?.value||'').trim().toLocaleLowerCase(),claseId=document.getElementById('filtro-alumno-clase')?.value||'',ciclo=document.getElementById('filtro-alumno-ciclo')?.value||'';
    var filtrados=usuariosEnVista.filter(function(usuario){var nombre=((usuario.nombre_completo||'')+' '+usuario.nombre).toLocaleLowerCase(),clases=usuario.clases_como_alumno||[];return (!texto||nombre.includes(texto))&&(!claseId||clases.some(function(clase){return String(clase.id)===claseId;}))&&(!ciclo||clases.some(function(clase){return String(clase.ciclo_escolar)===ciclo;}));});
    document.getElementById('tabla-usuarios').innerHTML=tablaUsuarios('Alumno',filtrados,'alumnos');document.getElementById('conteo-alumnos').textContent=filtrados.length+' de '+usuariosEnVista.length+' alumnos';
}
function limpiarFiltrosAlumnos(){document.getElementById('filtro-alumno-texto').value='';document.getElementById('filtro-alumno-clase').value='';document.getElementById('filtro-alumno-ciclo').value='';aplicarFiltrosAlumnos();}

function opcionesClases(){return '<option value="">Sin asignar por ahora</option>'+clasesActivas.map(function(clase){return '<option value="'+clase.id+'">'+esc(clase.nombre)+' · '+esc(clase.ciclo_escolar)+'</option>';}).join('');}
function opcionesProfesores(){return '<option value="">Sin asignar por ahora</option>'+profesoresDisponibles.map(function(profesor){return '<option value="'+profesor.id+'">'+esc(profesor.nombre_completo||profesor.nombre)+'</option>';}).join('');}

function formularioUsuario(rol){
    var profesor=rol==='Profesor';
    var asignaciones=profesor?'<div class="col-md-6"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="email" maxlength="150" required placeholder="nombre@correo.com"><small class="text-info">Se usará para verificar y recuperar la cuenta.</small></div><div class="col-md-6"><label class="form-label">Clase inicial <span class="text-muted">(opcional)</span></label><select class="form-select" name="clase_id">'+opcionesClases()+'</select><small class="text-info">También podrás asignarlo después desde Clases y asignaciones.</small></div>':'<div class="col-md-6"><label class="form-label">Clase inicial <span class="text-muted">(opcional)</span></label><select class="form-select" name="clase_id" onchange="actualizarProfesorNuevoAlumno(this.value)">'+opcionesClases()+'</select><small class="text-info">Selecciona una clase activa si deseas asignarlo ahora.</small></div><div class="col-md-6"><label class="form-label">Profesor responsable <span class="text-muted">(opcional)</span></label><select class="form-select" name="profesor_id" disabled><option value="">Primero selecciona una clase</option></select><small class="text-info">El profesor quedará vinculado a la misma clase del alumno.</small></div>';
    document.getElementById('admin-modal-root').innerHTML='<div class="admin-form-overlay"><div class="class-card admin-form-modal" role="dialog" aria-modal="true" aria-label="Crear cuenta de '+rol+'"><h3>Crear cuenta de '+rol+'</h3><p class="text-muted">Completa los datos de la persona. El sistema sugerirá un usuario disponible que podrás modificar.</p><form onsubmit="crearUsuario(event,\''+rol+'\')"><div class="row g-3"><div class="col-md-6"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre_completo" maxlength="160" required placeholder="Ej. Elvin Noe Esquivel Cabrera"></div><div class="col-md-6"><label class="form-label">Nombre de usuario</label><input class="form-control" name="nombre" maxlength="100" required><small class="text-info" data-sugerencia-usuario>Escribe el nombre completo para obtener una recomendación.</small></div>'+asignaciones+'</div><div class="admin-help"><i class="fas fa-key"></i><span>La cuenta se creará con la contraseña temporal <strong>Ined1234</strong> y deberá cambiarse al iniciar sesión.</span></div><div class="d-flex gap-2 flex-wrap"><button class="admin-btn admin-btn-primary" title="Guarda la nueva cuenta dentro de este colegio" type="submit"><i class="fas fa-user-plus"></i>Crear cuenta</button><button class="admin-btn" type="button" onclick="document.getElementById(\'admin-modal-root\').innerHTML=\'\'"><i class="fas fa-times"></i>Cancelar</button></div></form></div></div>';var form=document.querySelector('#admin-modal-root form');activarSugerenciaUsuario(form,apiBase+'/usuarios/sugerir-nombre');form.querySelector('[name="nombre_completo"]').focus();
}
function actualizarProfesorNuevoAlumno(claseId){var select=document.querySelector('#admin-modal-root [name="profesor_id"]');if(!select)return;select.disabled=!claseId;select.innerHTML=claseId?opcionesProfesores():'<option value="">Primero selecciona una clase</option>';}

async function crearUsuario(evento,rol){
    evento.preventDefault();var f=new FormData(evento.target);
    try{var d=await peticion(apiBase+'/usuarios',{method:'POST',body:JSON.stringify({nombre_completo:f.get('nombre_completo'),nombre:f.get('nombre'),email:f.get('email')||null,clase_id:f.get('clase_id')||null,profesor_id:f.get('profesor_id')||null,rol:rol,clave:'Ined1234'})});document.getElementById('admin-modal-root').innerHTML='';mensaje('success',d.message);await mostrarUsuarios(rol,true);}
    catch(e){if(e.data?.code==='USERNAME_TAKEN'&&e.data.suggested_username){var input=evento.target.querySelector('[name="nombre"]');input.value=e.data.suggested_username;input.focus();input.select();}mensaje('danger',e.message);}
}
function formularioEditarUsuario(id,rol){
    var usuario=usuariosEnVista.find(function(elemento){return Number(elemento.id)===Number(id);});if(!usuario)return;var profesor=rol==='Profesor',etiqueta=profesor?'profesor':'alumno';
    document.getElementById('admin-modal-root').innerHTML='<div class="admin-form-overlay"><div class="class-card admin-form-modal" role="dialog" aria-modal="true" aria-label="Editar perfil del '+etiqueta+'"><h3>Editar perfil del '+etiqueta+'</h3><p class="text-muted">Actualiza sus datos personales y de acceso. La contraseña y los puntos CodePlay no cambiarán.</p><form onsubmit="actualizarUsuario(event,'+id+',\''+rol+'\')"><div class="row g-3"><div class="'+(profesor?'col-md-4':'col-md-6')+'"><label class="form-label">Nombre completo</label><input class="form-control" name="nombre_completo" maxlength="160" required value="'+esc(usuario.nombre_completo||usuario.nombre)+'"></div><div class="'+(profesor?'col-md-4':'col-md-6')+'"><label class="form-label">Nombre de usuario</label><input class="form-control" name="nombre" maxlength="100" required value="'+esc(usuario.nombre)+'"></div>'+(profesor?'<div class="col-md-4"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="email" maxlength="150" required value="'+esc(usuario.email||'')+'"></div>':'')+'</div><div class="d-flex gap-2 flex-wrap mt-3"><button class="admin-btn admin-btn-primary" type="submit"><i class="fas fa-floppy-disk"></i>Guardar cambios</button><button class="admin-btn" type="button" onclick="document.getElementById(\'admin-modal-root\').innerHTML=\'\'"><i class="fas fa-times"></i>Cancelar</button></div></form></div></div>';document.querySelector('#admin-modal-root [name="nombre_completo"]').focus();
}
async function actualizarUsuario(evento,id,rol){
    evento.preventDefault();var f=new FormData(evento.target);
    try{var d=await peticion(apiBase+'/usuarios/'+id,{method:'PUT',body:JSON.stringify({nombre_completo:f.get('nombre_completo'),nombre:f.get('nombre'),email:f.get('email')||null})});document.getElementById('admin-modal-root').innerHTML='';mensaje('success',d.message);await mostrarUsuarios(rol,true);}
    catch(e){if(e.data?.code==='USERNAME_TAKEN'&&e.data.suggested_username){var input=evento.target.querySelector('[name="nombre"]');input.value=e.data.suggested_username;input.focus();input.select();}mensaje('danger',e.message);}
}
function eliminarUsuario(id,rol){
    var usuario=usuariosEnVista.find(function(elemento){return Number(elemento.id)===Number(id);}),etiqueta=rol==='Profesor'?'profesor':'alumno',nombre=usuario?(usuario.nombre_completo||usuario.nombre):'este '+etiqueta;
    confirmarAccion('Eliminar '+etiqueta,'Se eliminará definitivamente la cuenta de '+nombre+'. Esta acción también retirará su acceso a las clases.','Sí, eliminar',async function(){try{var d=await peticion(apiBase+'/usuarios/'+id,{method:'DELETE'});mensaje('success',d.message);await mostrarUsuarios(rol,true);}catch(e){mensaje('danger',e.message);}});
}
function abrirGraduandos(){
    var ciclos=[...new Set(clasesDelColegio.map(function(clase){return String(clase.ciclo_escolar||'');}).filter(Boolean))].sort().reverse();
    if(!ciclos.length){mensaje('warning','Primero debes crear una clase con su ciclo escolar.');return;}
    document.getElementById('admin-modal-root').innerHTML='<div class="admin-form-overlay"><div class="class-card admin-form-modal graduation-modal" role="dialog" aria-modal="true" aria-label="Eliminar alumnos graduandos"><div class="graduation-step">PASO 1 DE 2</div><h3>Eliminar alumnos graduandos</h3><p class="text-muted">Selecciona un ciclo completo o una clase específica. El sistema consultará únicamente a los alumnos de tu colegio.</p><div class="admin-help"><i class="fas fa-shield-halved"></i><span>Antes de eliminar podrás marcar a los alumnos repitentes que deben conservarse.</span></div><div class="row g-3"><div class="col-md-5"><label class="form-label">Ciclo escolar</label><select class="form-select" id="graduandos-ciclo" onchange="actualizarClasesGraduandos()"><option value="">Selecciona un ciclo</option>'+ciclos.map(function(ciclo){return '<option value="'+esc(ciclo)+'">'+esc(ciclo)+'</option>';}).join('')+'</select></div><div class="col-md-7"><label class="form-label">Alcance de la eliminación</label><select class="form-select" id="graduandos-clase" disabled><option value="">Todo el ciclo escolar</option></select><small class="text-info">Puedes elegir una clase concreta o eliminar todo el ciclo.</small></div></div><div id="graduandos-resultados"></div><div class="d-flex gap-2 flex-wrap mt-4"><button class="admin-btn admin-btn-primary" type="button" onclick="cargarGraduandos()"><i class="fas fa-magnifying-glass"></i>Revisar alumnos</button><button class="admin-btn" type="button" onclick="document.getElementById(\'admin-modal-root\').innerHTML=\'\'"><i class="fas fa-times"></i>Cancelar</button></div></div></div>';
    document.getElementById('graduandos-ciclo').focus();
}
function actualizarClasesGraduandos(){
    var ciclo=document.getElementById('graduandos-ciclo').value,select=document.getElementById('graduandos-clase'),clases=clasesDelColegio.filter(function(clase){return String(clase.ciclo_escolar)===ciclo;});
    select.disabled=!ciclo;select.innerHTML='<option value="">Todo el ciclo escolar '+esc(ciclo)+'</option>'+clases.map(function(clase){return '<option value="'+clase.id+'">'+esc(clase.nombre)+' · '+esc(clase.ciclo_escolar)+'</option>';}).join('');
}
async function cargarGraduandos(){
    var ciclo=document.getElementById('graduandos-ciclo').value,claseId=document.getElementById('graduandos-clase').value,caja=document.getElementById('graduandos-resultados');
    if(!ciclo){mensaje('warning','Selecciona el ciclo escolar que deseas revisar.');return;}
    caja.innerHTML='<p class="text-info mt-3"><i class="fas fa-spinner fa-spin me-2"></i>Buscando alumnos del ciclo...</p>';
    try{var url=apiBase+'/alumnos-graduandos?ciclo_escolar='+encodeURIComponent(ciclo)+(claseId?'&clase_id='+encodeURIComponent(claseId):''),datos=await peticion(url);graduandosEnVista=datos.alumnos;alcanceGraduandos={ciclo_escolar:ciclo,clase_id:claseId||null};mostrarRevisionGraduandos(datos);}
    catch(e){caja.innerHTML='';mensaje('danger',e.message);}
}
function mostrarRevisionGraduandos(datos){
    var caja=document.getElementById('graduandos-resultados'),alcance=datos.clase?datos.clase.nombre+' · '+datos.ciclo_escolar:'Todo el ciclo '+datos.ciclo_escolar;
    if(!datos.total){caja.innerHTML='<div class="admin-help mt-3"><i class="fas fa-circle-info"></i><span>No se encontraron alumnos para <strong>'+esc(alcance)+'</strong>.</span></div>';return;}
    var alumnos=datos.alumnos.map(function(alumno){var nombre=alumno.nombre_completo||alumno.nombre;return '<label class="graduating-student" data-search="'+esc((nombre+' '+alumno.nombre).toLocaleLowerCase())+'"><input class="form-check-input conservar-alumno" type="checkbox" value="'+alumno.id+'"><span><strong>'+esc(nombre)+'</strong><small>Usuario: '+esc(alumno.nombre)+'</small></span><em>Conservar</em></label>';}).join('');
    caja.innerHTML='<div class="graduation-review"><div class="graduation-step">PASO 2 DE 2</div><h4>¿Existen alumnos repitentes que deban conservarse?</h4><p>Marca a cada repitente. Los alumnos marcados <strong>no serán eliminados</strong>.</p><div class="graduation-summary"><strong>'+datos.total+' alumnos encontrados</strong><span>'+esc(alcance)+'</span></div><input class="form-control mb-2" id="buscar-graduando" placeholder="Buscar dentro de esta lista" oninput="filtrarGraduandos()"><div class="graduating-list">'+alumnos+'</div><label class="graduation-ack"><input class="form-check-input" type="checkbox" id="revision-repitentes" onchange="document.getElementById(\'confirmar-graduandos\').disabled=!this.checked"><span>Ya revisé la lista y marqué a todos los alumnos repitentes que deben conservarse.</span></label><div class="d-flex gap-2 flex-wrap mt-3"><button class="admin-btn admin-btn-danger" id="confirmar-graduandos" type="button" disabled onclick="prepararEliminacionGraduandos()"><i class="fas fa-trash"></i>Eliminar graduandos</button><button class="admin-btn" type="button" onclick="abrirGraduandos()"><i class="fas fa-arrow-left"></i>Cambiar selección</button></div></div>';
}
function filtrarGraduandos(){var texto=(document.getElementById('buscar-graduando').value||'').trim().toLocaleLowerCase();document.querySelectorAll('.graduating-student').forEach(function(fila){fila.hidden=texto&&!fila.dataset.search.includes(texto);});}
function prepararEliminacionGraduandos(){
    if(!document.getElementById('revision-repitentes').checked){mensaje('warning','Confirma que revisaste a los alumnos repitentes.');return;}
    var conservar=[...document.querySelectorAll('.conservar-alumno:checked')].map(function(caja){return Number(caja.value);}),eliminar=graduandosEnVista.length-conservar.length;
    if(eliminar<1){mensaje('warning','Todos los alumnos están marcados para conservar. No hay cuentas por eliminar.');return;}
    confirmarAccion('Eliminar '+eliminar+' alumnos graduandos','Esta acción eliminará definitivamente las cuentas seleccionadas y su información relacionada. Se conservarán '+conservar.length+' alumnos repitentes marcados.','Sí, eliminar graduandos',async function(){try{var d=await peticion(apiBase+'/alumnos-graduandos',{method:'DELETE',body:JSON.stringify({ciclo_escolar:alcanceGraduandos.ciclo_escolar,clase_id:alcanceGraduandos.clase_id,conservar_alumnos:conservar})});document.getElementById('admin-modal-root').innerHTML='';mensaje('success',d.message);await Promise.all([mostrarUsuarios('Alumno',true),cargarMetricasIniciales()]);}catch(e){mensaje('danger',e.message);}});
}
function restablecerCuenta(id,rol){
    confirmarAccion('Restablecer contraseña','La contraseña actual dejará de funcionar. La cuenta utilizará temporalmente Ined1234 y deberá cambiarla en su próximo ingreso.','Sí, restablecer',async function(){try{var d=await peticion(apiBase+'/usuarios/'+id+'/reset-password',{method:'POST',body:JSON.stringify({nueva_clave:'Ined1234'})});mensaje('success',d.message);await mostrarUsuarios(rol,true);}catch(e){mensaje('danger',e.message);}});
}

async function mostrarClases(){
    abrir(encabezado('Clases y Asignaciones','fa-users')+'<p>Cargando...</p>');
    try{var datos=await Promise.all([peticion(apiBase+'/usuarios-por-rol/Profesor'),peticion(apiBase+'/usuarios-por-rol/Alumno')]);profesoresDisponibles=datos[0];alumnosDisponibles=datos[1];await cargarClases();}
    catch(e){mensaje('danger',e.message);}
}
async function cargarClases(){
    var clases=await peticion(apiBase+'/clases');
    abrir(encabezado('Clases y Asignaciones','fa-users')+'<div class="admin-help"><i class="fas fa-circle-info"></i><span>Primero crea la clase y después asigna profesores y alumnos. Una persona puede pertenecer a más de una clase.</span></div><div class="d-flex justify-content-end mb-3"><button class="admin-btn admin-btn-primary" title="Crea un nuevo grupo académico dentro de tu colegio" onclick="formularioClase()"><i class="fas fa-plus"></i>Crear clase</button></div><div id="formulario-clase"></div><div class="row g-3">'+(clases.length?clases.map(tarjetaClase).join(''):'<div class="class-card text-center text-muted">Todavía no hay clases registradas.</div>')+'</div>');
}
function formularioClase(){
    document.getElementById('formulario-clase').innerHTML='<div class="class-card mb-4"><h3>Crear una clase</h3><p class="text-muted">Usa un nombre fácil de reconocer, como “Primero Básico A” o “Programación 2026”.</p><form onsubmit="crearClase(event)"><div class="row g-3"><div class="col-md-5"><label class="form-label">Nombre de la clase</label><input class="form-control" name="nombre" maxlength="120" required placeholder="Ej. Primero Básico A"></div><div class="col-md-3"><label class="form-label">Ciclo escolar</label><input class="form-control" name="ciclo_escolar" maxlength="20" value="'+new Date().getFullYear()+'" required></div><div class="col-md-4"><label class="form-label">Descripción</label><input class="form-control" name="descripcion" maxlength="1000" placeholder="Información opcional del grupo"></div></div><button class="admin-btn admin-btn-primary mt-3" title="Guarda la clase dentro de este colegio" type="submit"><i class="fas fa-check"></i>Crear clase</button></form></div>';
}
async function crearClase(evento){
    evento.preventDefault();try{var d=await peticion(apiBase+'/clases',{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(evento.target)))});mensaje('success',d.message);await cargarClases();}catch(e){mensaje('danger',e.message);}
}
function opciones(lista){return '<option value="">Selecciona...</option>'+lista.map(function(u){return '<option value="'+u.id+'">'+esc(u.nombre_completo||u.nombre)+' ('+esc(u.nombre)+')</option>';}).join('');}
function chips(lista,tipo,claseId){return lista.length?lista.map(function(u){return '<span class="member-chip">'+esc(u.nombre_completo||u.nombre)+'<button title="Retirar a esta persona de la clase" aria-label="Retirar de la clase" onclick="quitarMiembro('+claseId+','+u.id+',\''+tipo+'\')"><i class="fas fa-times"></i></button></span>';}).join(''):'<span class="text-muted">Sin asignaciones</span>';}
function tarjetaClase(clase){
    var pids=clase.profesores.map(function(p){return p.id;}),aids=clase.alumnos.map(function(a){return a.id;});
    var profesores=profesoresDisponibles.filter(function(p){return pids.indexOf(p.id)<0;}),alumnos=alumnosDisponibles.filter(function(a){return aids.indexOf(a.id)<0;});
    return '<div class="col-12"><article class="class-card"><div class="d-flex justify-content-between gap-3 flex-wrap"><div><h3 class="mb-1">'+esc(clase.nombre)+'</h3><div class="text-info">Código '+esc(clase.codigo)+' · Ciclo '+esc(clase.ciclo_escolar)+'</div></div><button class="admin-btn '+(clase.activa?'admin-btn-gold':'admin-btn-primary')+'" title="'+(clase.activa?'Impide nuevas operaciones en esta clase hasta volver a activarla':'Vuelve a habilitar esta clase')+'" onclick="cambiarEstadoClase('+clase.id+','+(!clase.activa)+')"><i class="fas '+(clase.activa?'fa-pause':'fa-play')+'"></i>'+(clase.activa?'Desactivar clase':'Activar clase')+'</button></div><p class="mt-2 text-muted">'+esc(clase.descripcion||'Sin descripción registrada.')+'</p><div class="row g-3"><div class="col-lg-6"><strong>Profesores asignados ('+clase.profesores_count+')</strong><div class="my-2">'+chips(clase.profesores,'profesores',clase.id)+'</div><div class="input-group"><select class="form-select" id="profesor-'+clase.id+'" aria-label="Profesor para asignar">'+opciones(profesores)+'</select><button class="admin-btn" title="Añade el profesor seleccionado a esta clase" onclick="asignarMiembro('+clase.id+',\'profesores\')"><i class="fas fa-user-plus"></i>Asignar</button></div></div><div class="col-lg-6"><strong>Alumnos asignados ('+clase.alumnos_count+')</strong><div class="my-2">'+chips(clase.alumnos,'alumnos',clase.id)+'</div><div class="input-group"><select class="form-select" id="alumno-'+clase.id+'" aria-label="Alumno para asignar">'+opciones(alumnos)+'</select><button class="admin-btn" title="Añade el alumno seleccionado a esta clase" onclick="asignarMiembro('+clase.id+',\'alumnos\')"><i class="fas fa-user-plus"></i>Asignar</button></div></div></div></article></div>';
}
async function asignarMiembro(claseId,tipo){
    var prefijo=tipo==='profesores'?'profesor':'alumno',usuarioId=document.getElementById(prefijo+'-'+claseId).value;
    if(!usuarioId){mensaje('warning','Selecciona un usuario.');return;}
    try{var d=await peticion(apiBase+'/clases/'+claseId+'/'+tipo+'/'+usuarioId,{method:'POST'});mensaje('success',d.message);await mostrarClases();}catch(e){mensaje('danger',e.message);}
}
async function quitarMiembro(claseId,usuarioId,tipo){
    confirmarAccion('Retirar de la clase','La cuenta seguirá existiendo en el colegio, pero dejará de pertenecer a esta clase.','Sí, retirar',async function(){try{var d=await peticion(apiBase+'/clases/'+claseId+'/'+tipo+'/'+usuarioId,{method:'DELETE'});mensaje('success',d.message);await mostrarClases();}catch(e){mensaje('danger',e.message);}});
}
async function cambiarEstadoClase(claseId,activa){
    var ejecutar=async function(){try{var d=await peticion(apiBase+'/clases/'+claseId,{method:'PUT',body:JSON.stringify({activa:activa})});mensaje('success',d.message);await cargarClases();}catch(e){mensaje('danger',e.message);}};
    if(activa){await ejecutar();return;}confirmarAccion('Desactivar clase','La clase dejará de estar disponible para nuevas operaciones hasta que vuelvas a activarla. Sus integrantes y datos se conservarán.','Sí, desactivar',ejecutar);
}
</script>
@endsection
