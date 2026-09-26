@extends('layouts.security')
@section('title','Códigos de recuperación')
@section('content')
<h1>Guarda tus códigos de recuperación</h1>
<p class="muted">Cada código funciona una sola vez. Guárdalos fuera de este equipo; no volverán a mostrarse.</p>
<div class="code mb-3">@foreach($codigos as $codigo)<div>{{ $codigo }}</div>@endforeach</div>
<a class="btn btn-primary w-100" href="{{ route('inicio.master') }}">He guardado los códigos</a>
@endsection
