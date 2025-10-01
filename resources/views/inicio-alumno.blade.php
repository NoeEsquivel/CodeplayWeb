@extends('layouts.app')

@section('title', 'Inicio Alumno - CodePlay')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="game-card p-4 text-center text-white">
                <h1 class="mb-4">🎮 ¡Bienvenido Alumno!</h1>
                <h3 class="text-warning">{{ session('usuario_nombre') }}</h3>
                <p class="lead">Estás listo para aprender programación jugando</p>
                
                <div class="row mt-5">
                    <div class="col-md-6 mb-3">
                        <div class="game-card p-3">
                            <i class="fas fa-book fa-3x text-info mb-3"></i>
                            <h5>Tutoriales</h5>
                            <p>Aprende con guías interactivas</p>
                            <button class="btn btn-outline-info">Comenzar</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="game-card p-3">
                            <i class="fas fa-flag fa-3x text-success mb-3"></i>
                            <h5>Desafíos</h5>
                            <p>Pon a prueba tus habilidades</p>
                            <button class="btn btn-outline-success">Jugar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection