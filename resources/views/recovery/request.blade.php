@extends('layouts.security')
@section('title','Recuperar cuenta')
@section('content')
<h1>Recuperar cuenta</h1>
<p class="muted">Disponible para Profesores, Administradores de Colegio y Master. Los alumnos recuperan su acceso con su profesor.</p>
<form method="POST" action="{{ route('recovery.request') }}">@csrf
<label class="form-label" for="email">Correo verificado</label>
<input class="form-control mb-3" id="email" name="email" type="email" maxlength="150" required>
<label class="form-label" for="tipo">¿Qué necesitas recuperar?</label>
<select class="form-control mb-3" id="tipo" name="tipo" required>
<option value="username">Nombre de usuario</option>
<option value="password">Contraseña</option>
</select>
<button class="btn btn-primary w-100" type="submit">Enviar instrucciones</button>
</form>
<a class="btn-link d-inline-block mt-3" href="{{ route('login') }}">Volver al inicio de sesión</a>
@endsection
