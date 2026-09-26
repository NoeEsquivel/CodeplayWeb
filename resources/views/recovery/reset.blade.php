@extends('layouts.security')
@section('title','Restablecer contraseña')
@section('content')
<h1>Nueva contraseña</h1>
<p class="muted">Al guardar la nueva contraseña se cerrarán todas tus sesiones anteriores.</p>
<form method="POST" action="{{ route('recovery.reset') }}">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label class="form-label" for="password">Nueva contraseña</label>
<input class="form-control mb-3" id="password" name="password" type="password" required>
<label class="form-label" for="password_confirmation">Confirmar contraseña</label>
<input class="form-control mb-3" id="password_confirmation" name="password_confirmation" type="password" required>
<button class="btn btn-primary w-100" type="submit">Restablecer contraseña</button>
</form>
@endsection
