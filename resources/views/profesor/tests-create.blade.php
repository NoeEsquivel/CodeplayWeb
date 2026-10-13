@extends('layouts.app')
@section('title', 'Nuevo banco docente - CodePlay')
@section('content')
<div class="teacher-page"><div class="container"><main class="teacher-frame mx-auto" style="max-width:920px">
    <header class="teacher-page-header">
        <div><span class="teacher-kicker"><i class="fas fa-folder-plus"></i> Contenido del Profesor</span><h1>Crear banco de preguntas</h1><p>El banco se guardará como borrador. Después podrás añadir preguntas, probarlo y publicarlo.</p></div>
        <div class="teacher-actions"><a class="teacher-btn" href="{{ route('profesor.tests.index') }}" title="Regresa sin crear el banco"><i class="fas fa-arrow-left"></i>Volver a Tests</a></div>
    </header>
    @if($errors->any())<div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i>{{ $errors->first() }}</div>@endif
    <div class="help-note"><i class="fas fa-lightbulb"></i><span>Utiliza un título fácil de reconocer y una materia que ayude a encontrar el banco más adelante.</span></div>
    <form class="teacher-card" method="POST" action="{{ route('profesor.tests.banks.store') }}">@csrf
        <div class="mb-3"><label class="form-label" for="bank-name">Título del banco</label><input class="form-control" id="bank-name" name="nombre" maxlength="160" value="{{ old('nombre') }}" placeholder="Ej. Repaso de Fundamentos HTML" required></div>
        <div class="mb-3"><label class="form-label" for="bank-subject">Materia</label><input class="form-control" id="bank-subject" name="materia" maxlength="100" value="{{ old('materia') }}" placeholder="Ej. HTML" required></div>
        <div class="mb-3"><label class="form-label" for="bank-description">Descripción</label><textarea class="form-control" id="bank-description" name="descripcion" rows="4" maxlength="2000" placeholder="Describe brevemente qué aprenderán o practicarán los alumnos">{{ old('descripcion') }}</textarea></div>
        <button type="submit" class="teacher-btn teacher-btn-primary" title="Guarda el borrador y abre el editor de preguntas"><i class="fas fa-arrow-right"></i>Crear banco y añadir preguntas</button>
    </form>
</main></div></div>
@include('profesor.partials.panel-theme')
@endsection
