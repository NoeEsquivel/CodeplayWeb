@extends('layouts.security')
@section('title','Registrar correo')
@section('content')
<h1>Protege tu cuenta</h1>
<p class="muted">Los profesores, administradores de colegio y Master necesitan un correo verificado para recuperar su cuenta de forma segura.</p>
<form method="POST" action="{{ route('security.email.save') }}">
    @csrf
    <label class="form-label" for="email">Correo electrónico</label>
    <input class="form-control mb-3" id="email" name="email" type="email" maxlength="150" required value="{{ old('email', $usuario->email) }}">
    <button class="btn btn-primary w-100" type="submit">Guardar y enviar verificación</button>
</form>
@endsection
