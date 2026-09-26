@extends('layouts.security')
@section('title','Enlace no válido')
@section('content')
<h1>Enlace no válido</h1>
<p>El enlace venció, ya fue utilizado o no existe.</p>
<a class="btn btn-primary" href="{{ route('recovery.form') }}">Solicitar otro enlace</a>
@endsection
