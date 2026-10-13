@extends('layouts.security')
@section('title','Configurar segundo factor')
@section('content')
<h1>Configura el segundo factor</h1>
<p class="muted">Añade esta cuenta a una aplicación TOTP, como Google Authenticator, Microsoft Authenticator o 2FAS.</p>
<p><strong>Clave manual:</strong></p><div class="code mb-3">{{ $secret }}</div>
<details class="mb-3"><summary>URI para una aplicación compatible</summary><div class="code mt-2">{{ $provisioningUri }}</div></details>
<form method="POST" action="{{ route('security.2fa.confirm') }}">@csrf
<label class="form-label" for="codigo">Código de 6 dígitos</label>
<input class="form-control mb-3" id="codigo" name="codigo" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
<button class="btn btn-primary w-100" type="submit">Confirmar segundo factor</button>
</form>
@endsection
