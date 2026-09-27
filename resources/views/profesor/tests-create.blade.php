@extends('layouts.app')
@section('title','Nuevo banco docente - CodePlay')
@section('content')
<div class="container py-4 text-white"><div class="game-card p-4 mx-auto" style="max-width:850px">
<a class="btn btn-outline-light btn-sm mb-3" href="{{ route('profesor.tests.index') }}">← Volver</a>
<h1 class="h3">Crear banco de preguntas</h1><p class="text-secondary">Se guardará como borrador para añadir preguntas antes de publicarlo.</p>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('profesor.tests.banks.store') }}">@csrf
<div class="mb-3"><label class="form-label">Título</label><input class="form-control" name="nombre" maxlength="160" value="{{ old('nombre') }}" required></div>
<div class="mb-3"><label class="form-label">Materia</label><input class="form-control" name="materia" maxlength="100" value="{{ old('materia') }}" required></div>
<div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="4" maxlength="2000">{{ old('descripcion') }}</textarea></div>
<button class="btn btn-success">Crear banco y añadir preguntas</button></form>
</div></div>
@endsection
