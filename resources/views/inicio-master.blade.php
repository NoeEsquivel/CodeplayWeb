@extends('layouts.app')

@section('title', 'Inicio Master - CodePlay')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="game-card p-4 text-center text-white">
                <h1 class="mb-4">👑 Panel Master</h1>
                <h3 class="text-warning">{{ session('usuario_nombre') }}</h3>
                <p class="lead">Control total del sistema CodePlay</p>
                
                <div class="row mt-4">
                    <!-- Solo mostramos Gestión de Usuarios -->
                    <div class="col-md-6 offset-md-3 mb-3">
                        <div class="game-card p-4">
                            <i class="fas fa-users-cog fa-3x text-danger mb-3"></i>
                            <h4>Gestión de Usuarios</h4>
                            <p class="mb-3">Administra todos los usuarios del sistema</p>
                            
                            <div class="d-grid gap-3">
                                <button class="btn btn-outline-warning btn-lg" 
                                        onclick="mostrarGestion('administradores')">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Administrar Usuarios Administradores
                                </button>
                                
                                <button class="btn btn-outline-info btn-lg" 
                                        onclick="mostrarGestion('alumnos')">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Administrar Usuarios Alumnos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección para mostrar la gestión específica -->
                <div id="gestion-usuarios" style="display: none;">
                    <!-- Aquí se cargará el contenido dinámicamente -->
                </div>
                
                <div class="mt-4">
                    <span class="badge bg-danger fs-6 p-2">
                        <i class="fas fa-crown me-1"></i> VISTA MASTER
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cambiar el fondo específicamente para la vista Master
document.addEventListener('DOMContentLoaded', function() {
    document.body.style.background = 'url("{{ asset("imagenes/Fondo_Master.jpg") }}") no-repeat center center fixed';
    document.body.style.backgroundSize = 'cover';
    
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    overlay.style.zIndex = '-1';
    document.body.appendChild(overlay);
    
    document.querySelector('main').style.position = 'relative';
    document.querySelector('main').style.zIndex = '1';
});

function mostrarGestion(tipo) {
    // Ocultar botones principales
    document.querySelector('.row.mt-4').style.display = 'none';
    
    // Mostrar sección de gestión
    const gestionDiv = document.getElementById('gestion-usuarios');
    gestionDiv.style.display = 'block';
    
    // Cargar contenido según el tipo
    if (tipo === 'administradores') {
        gestionDiv.innerHTML = `
            <div class="game-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-warning mb-0">
                        <i class="fas fa-user-shield me-2"></i>Usuarios Administradores
                    </h3>
                    <div>
                        <button class="btn btn-success me-2" onclick="mostrarFormularioCrear('Administrador')">
                            <i class="fas fa-plus me-1"></i> Nuevo Administrador
                        </button>
                        <button class="btn btn-secondary" onclick="volverAlMenu()">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                    </div>
                </div>
                <div id="lista-administradores">
                    Cargando administradores...
                </div>
                <div id="formulario-crear" style="display: none;"></div>
            </div>
        `;
        cargarUsuarios('Administrador');
    } else {
        gestionDiv.innerHTML = `
            <div class="game-card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-info mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Usuarios Alumnos
                    </h3>
                    <div>
                        <button class="btn btn-success me-2" onclick="mostrarFormularioCrear('Alumno')">
                            <i class="fas fa-plus me-1"></i> Nuevo Alumno
                        </button>
                        <button class="btn btn-secondary" onclick="volverAlMenu()">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                    </div>
                </div>
                <div id="lista-alumnos">
                    Cargando alumnos...
                </div>
                <div id="formulario-crear" style="display: none;"></div>
            </div>
        `;
        cargarUsuarios('Alumno');
    }
}

function volverAlMenu() {
    document.querySelector('.row.mt-4').style.display = 'block';
    document.getElementById('gestion-usuarios').style.display = 'none';
}

function cargarUsuarios(rol) {
    const listaId = rol === 'Administrador' ? 'lista-administradores' : 'lista-alumnos';
    
    fetch(`/api/usuarios-por-rol/${rol}`)
        .then(response => response.json())
        .then(usuarios => {
            const listaDiv = document.getElementById(listaId);
            
            if (usuarios.length === 0) {
                listaDiv.innerHTML = '<p class="text-center text-muted">No hay usuarios de este tipo.</p>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Puntos Totales</th>
                                <th>Fecha Creación</th>
                                <th>Estado Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            usuarios.forEach(usuario => {
                // Añadir el estado de la contraseña
                const requiereCambio = usuario.cambio_clave_requerido ? 
                    '<span class="badge bg-warning">Requiere cambio</span>' : 
                    '<span class="badge bg-success">Actualizada</span>';
                
                html += `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.nombre}</td>
                        <td>${usuario.puntos_totales}</td>
                        <td>${new Date(usuario.created_at).toLocaleDateString()}</td>
                        <td>${requiereCambio}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" onclick="resetearPassword(${usuario.id})">
                                <i class="fas fa-key"></i> Resetear Clave
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${usuario.id})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            listaDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById(listaId).innerHTML = '<p class="text-danger">Error al cargar los usuarios.</p>';
        });
}

function mostrarFormularioCrear(rol) {
    const formularioDiv = document.getElementById('formulario-crear');
    formularioDiv.style.display = 'block';
    formularioDiv.innerHTML = `
        <div class="game-card p-4 mt-3">
            <h4 class="text-success">
                <i class="fas fa-plus-circle me-2"></i>Crear Nuevo ${rol}
            </h4>
            <form id="formCrearUsuario" onsubmit="crearUsuario(event, '${rol}')">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nombre" required 
                                   placeholder="Ingrese el nombre de usuario">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Contraseña Temporal</label>
                            <input type="text" class="form-control" value="Ined1234" readonly 
                                   style="background-color: #e9ecef;">
                            <small class="text-warning">Esta será la contraseña temporal del usuario</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Crear Usuario
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="ocultarFormulario()">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    `;
}

function ocultarFormulario() {
    document.getElementById('formulario-crear').style.display = 'none';
}

function crearUsuario(event, rol) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const datos = {
        nombre: formData.get('nombre'),
        rol: rol,
        clave: 'Ined1234' // Contraseña temporal fija
    };
    
    fetch('/api/usuarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usuario creado exitosamente!');
            ocultarFormulario();
            cargarUsuarios(rol); // Recargar la lista
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el usuario');
    });
}

function resetearPassword(usuarioId) {
    if (!confirm('¿Estás seguro de resetear la contraseña de este usuario a "Ined1234"?')) {
        return;
    }
    
    fetch(`/api/usuarios/${usuarioId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ nueva_clave: 'Ined1234' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contraseña reseteada exitosamente a "Ined1234"');
            // Recargar la lista actual para actualizar el estado
            const titulo = document.querySelector('#gestion-usuarios h3');
            if (titulo.textContent.includes('Administradores')) {
                cargarUsuarios('Administrador');
            } else {
                cargarUsuarios('Alumno');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al resetear la contraseña');
    });
}

function eliminarUsuario(usuarioId) {
    if (!confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) {
        return;
    }
    
    fetch(`/api/usuarios/${usuarioId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usuario eliminado exitosamente');
            // Recargar la lista actual
            const titulo = document.querySelector('#gestion-usuarios h3');
            if (titulo.textContent.includes('Administradores')) {
                cargarUsuarios('Administrador');
            } else {
                cargarUsuarios('Alumno');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
    });
}
</script>
@endsection