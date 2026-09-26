@extends('layouts.security')
@section('title','Verificación de correo')
@section('content')
@if($success)
<h1>Correo verificado</h1><p>Tu correo quedó confirmado. Ya puedes continuar con la seguridad de tu cuenta.</p>
@else
<h1>Enlace no válido</h1><p>El enlace venció, ya fue utilizado o no existe. Inicia sesión y solicita uno nuevo.</p>
@endif
<a class="btn btn-primary" href="{{ route('login') }}">Ir al inicio de sesión</a>
@endsection
