@extends('layouts.app')

@section('title', 'Inicio Administrador - CodePlay')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="game-card p-4 text-center text-white">
                <h1 class="mb-4">👨‍💼 Panel de Administración</h1>
                <h3 class="text-warning">{{ session('usuario_nombre') }}</h3>
                <p class="lead">Gestión de usuarios alumnos del sistema</p>
                
                <div class="row mt-4">
                    <!-- Solo mostramos Gestión de Alumnos -->
                    <div class="col-md-8 offset-md-2 mb-3">
                        <div class="game-card p-4">
                            <i class="fas fa-user-graduate fa-3x text-info mb-3"></i>
                            <h4>Gestión de Alumnos</h4>
                            <p class="mb-3">Administra los usuarios alumnos del sistema</p>
                            
                            <div class="d-grid">
                                <button class="btn btn-outline-info btn-lg" 
                                        onclick="mostrarGestionAlumnos()">
                                    <i class="fas fa-users me-2"></i>
                                    Administrar Usuarios Alumnos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección para mostrar la gestión de alumnos -->
                <div id="gestion-alumnos" style="display: none;">
                    <!-- Aquí se cargará el contenido dinámicamente -->
                </div>
                
                <div class="mt-4">
                    <span class="badge bg-warning fs-6 p-2">
                        <i class="fas fa-user-shield me-1"></i> ADMINISTRADOR
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarGestionAlumnos() {
    // Ocultar botones principales
    document.querySelector('.row.mt-4').style.display = 'none';
    
    // Mostrar sección de gestión
    const gestionDiv = document.getElementById('gestion-alumnos');
    gestionDiv.style.display = 'block';
    
    // Cargar contenido de alumnos
    gestionDiv.innerHTML = `
        <div class="game-card p-4 mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-info mb-0">
                    <i class="fas fa-user-graduate me-2"></i>Gestión de Alumnos
                </h3>
                <div>
                    <button class="btn btn-success me-2" onclick="mostrarFormularioCrearAlumno()">
                        <i class="fas fa-plus me-1"></i> Nuevo Alumno
                    </button>
                    <button class="btn btn-secondary" onclick="volverAlMenuAdmin()">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </button>
                </div>
            </div>
            <div id="lista-alumnos">
                Cargando alumnos...
            </div>
            <div id="formulario-crear-alumno" style="display: none;"></div>
        </div>
    `;
    cargarAlumnos();
}

function volverAlMenuAdmin() {
    document.querySelector('.row.mt-4').style.display = 'block';
    document.getElementById('gestion-alumnos').style.display = 'none';
}

function cargarAlumnos() {
    fetch('/api/usuarios-por-rol/Alumno')
        .then(response => response.json())
        .then(alumnos => {
            const listaDiv = document.getElementById('lista-alumnos');
            
            if (alumnos.length === 0) {
                listaDiv.innerHTML = '<p class="text-center text-muted">No hay alumnos registrados.</p>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de Usuario</th>
                                <th>Puntos Totales</th>
                                <th>Fecha Creación</th>
                                <th>Estado Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            alumnos.forEach(alumno => {
                const requiereCambio = alumno.cambio_clave_requerido ? 
                    '<span class="badge bg-warning">Requiere cambio</span>' : 
                    '<span class="badge bg-success">Actualizada</span>';
                
                html += `
                    <tr>
                        <td>${alumno.id}</td>
                        <td>${alumno.nombre}</td>
                        <td>${alumno.puntos_totales}</td>
                        <td>${new Date(alumno.created_at).toLocaleDateString()}</td>
                        <td>${requiereCambio}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-1" onclick="resetearPasswordAlumno(${alumno.id})">
                                <i class="fas fa-key"></i> Resetear Clave
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarAlumno(${alumno.id})">
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
            document.getElementById('lista-alumnos').innerHTML = '<p class="text-danger">Error al cargar los alumnos.</p>';
        });
}

function mostrarFormularioCrearAlumno() {
    const formularioDiv = document.getElementById('formulario-crear-alumno');
    formularioDiv.style.display = 'block';
    formularioDiv.innerHTML = `
        <div class="game-card p-4 mt-3">
            <h4 class="text-success">
                <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Alumno
            </h4>
            <form id="formCrearAlumno" onsubmit="crearAlumno(event)">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="nombre" required 
                                   placeholder="Ingrese el nombre de usuario para el alumno">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Contraseña Temporal</label>
                            <input type="text" class="form-control" value="Ined1234" readonly 
                                   style="background-color: #e9ecef;">
                            <small class="text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                El alumno deberá cambiar esta contraseña en su primer acceso
                            </small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Información:</strong> El nuevo alumno tendrá la contraseña temporal "Ined1234" 
                        y se le solicitará cambiarla en su primer ingreso al sistema.
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Registrar Alumno
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="ocultarFormularioAlumno()">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    `;
}

function ocultarFormularioAlumno() {
    document.getElementById('formulario-crear-alumno').style.display = 'none';
}

function crearAlumno(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const datos = {
        nombre: formData.get('nombre'),
        rol: 'Alumno', // Siempre será Alumno
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
            alert('Alumno registrado exitosamente!');
            ocultarFormularioAlumno();
            cargarAlumnos(); // Recargar la lista
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al registrar el alumno');
    });
}

function resetearPasswordAlumno(alumnoId) {
    if (!confirm('¿Estás seguro de resetear la contraseña de este alumno a "Ined1234"? El alumno deberá cambiar su contraseña en el próximo acceso.')) {
        return;
    }
    
    fetch(`/api/usuarios/${alumnoId}/reset-password`, {
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
            alert('Contraseña del alumno reseteada exitosamente a "Ined1234"');
            cargarAlumnos(); // Recargar la lista para actualizar estado
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al resetear la contraseña del alumno');
    });
}

function eliminarAlumno(alumnoId) {
    if (!confirm('¿Estás seguro de eliminar este alumno? Esta acción no se puede deshacer y se perderán todos sus datos.')) {
        return;
    }
    
    fetch(`/api/usuarios/${alumnoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Alumno eliminado exitosamente');
            cargarAlumnos(); // Recargar la lista
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el alumno');
    });
}
</script>
@endsection