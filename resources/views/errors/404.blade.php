@extends('layouts.layout')

@section('title', 'Página No Encontrada - Buscar Desaparecidos')

@section('content')
<div class="app-container">
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 70vh; text-align: center; padding: 2rem;">
        <i class="fa-regular fa-face-frown" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
        <h1 style="font-family: var(--font-heading); font-size: 3rem; color: var(--text-primary); margin-bottom: 0.5rem;">404</h1>
        <h2 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--text-secondary); margin-bottom: 1rem;">Página No Encontrada</h2>
        <p style="color: var(--text-muted); max-width: 480px; line-height: 1.6; margin-bottom: 2rem;">
            La página que buscas no existe, fue movida o el enlace está roto. Puedes regresar a la página principal para seguir buscando.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary" style="text-decoration: none;">
            <i class="fa-solid fa-house"></i> Volver al Inicio
        </a>
    </div>
</div>
@endsection
