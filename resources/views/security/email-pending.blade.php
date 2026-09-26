@extends('layouts.security')
@section('title','Verificar correo')
@section('content')
<h1>Revisa tu correo</h1>
@if(session('local_verification_url'))
    <p>El correo quedó registrado como <strong>{{ $usuario->email }}</strong>, pero el servidor de correo local no está disponible.</p>
    <div class="alert alert-info">
        <strong>Acceso local:</strong> puedes completar la verificación desde este equipo con el enlace temporal.
    </div>
    <a class="btn btn-primary w-100 mb-3" href="{{ session('local_verification_url') }}">Verificar mi correo ahora</a>
@else
    <p>Enviamos un enlace a <strong>{{ $usuario->email }}</strong>. El enlace dura 60 minutos y solo se puede usar una vez.</p>
@endif
<form method="POST" action="{{ route('security.email.resend') }}">@csrf<button class="btn btn-primary" type="submit">Reenviar enlace</button></form>
<p class="mt-3"><a class="btn-link" href="{{ route('security.email.form') }}">Cambiar correo</a></p>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form>
@endsection
