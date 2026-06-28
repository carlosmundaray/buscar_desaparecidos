@extends('layouts.layout')

@section('title', 'Error del Servidor - Buscar Desaparecidos')

@section('content')
<div class="app-container">
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 70vh; text-align: center; padding: 2rem;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 4rem; color: var(--state-missing); margin-bottom: 1.5rem;"></i>
        <h1 style="font-family: var(--font-heading); font-size: 3rem; color: var(--text-primary); margin-bottom: 0.5rem;">500</h1>
        <h2 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--text-secondary); margin-bottom: 1rem;">Error Interno del Servidor</h2>
        <p style="color: var(--text-muted); max-width: 480px; line-height: 1.6; margin-bottom: 2rem;">
            Ha ocurrido un error inesperado en el servidor. Nuestro equipo ha sido notificado. Por favor, intenta de nuevo en unos momentos.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary" style="text-decoration: none;">
            <i class="fa-solid fa-house"></i> Volver al Inicio
        </a>
    </div>
</div>
@endsection
