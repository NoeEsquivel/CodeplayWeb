@extends('layouts.security')
@section('title','Segundo factor')
@section('content')
<h1>Confirma que eres tú</h1>
<p class="muted">Escribe el código de tu aplicación autenticadora. También puedes usar uno de tus códigos de recuperación.</p>
<form method="POST" action="{{ route('security.2fa.challenge.verify') }}">@csrf
<label class="form-label" for="codigo">Código</label>
<input class="form-control mb-3" id="codigo" name="codigo" maxlength="30" required autocomplete="one-time-code">
<button class="btn btn-primary w-100" type="submit">Entrar</button>
</form>
<form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button></form>
@endsection
