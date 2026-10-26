@extends('layouts.home')

@section('content')
    <div class="text-center mb-4">
        <h2 class="text-success">Bienvenido</h2>
        <p class="lead">Sistema de gestión: pacientes, turnos, farmacia e historia clínica.</p>
    </div>

    @if ($stockBajo > 0)
        <x-alert type="warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Hay {{ $stockBajo }} producto(s) de la farmacia con stock bajo. Revisa el módulo de
            <a href="/directivaforeach" class="alert-link">Farmacia</a>.
        </x-alert>
    @else
        <x-alert type="success">El inventario de la farmacia está en niveles normales.</x-alert>
    @endif

    <div class="row g-3 mb-4">
        @foreach ($tarjetas as $tarjeta)
            <div class="col-md-6 col-lg-3">
                <x-card class="h-100 text-center">
                    <i class="fa-solid {{ $tarjeta->icono }} fa-2x text-success mb-2"></i>
                    <h5 class="card-title">{{ $tarjeta->titulo }}</h5>
                    <p class="display-6 mb-0">{{ $tarjeta->valor }}</p>
                    <p class="card-text text-muted">{{ $tarjeta->detalle }}</p>
                    <a href="{{ $tarjeta->ruta }}" class="btn btn-outline-success">Ver módulo</a>
                </x-card>
            </div>
        @endforeach
    </div>

    <h5><i class="fa-solid fa-clock-rotate-left text-success"></i> Últimas entregas de farmacia</h5>
    <ul class="list-group mb-4">
        @forelse ($ultimasEntregas as $entrega)
            <li class="list-group-item">
                {{ $entrega->hora }} - {{ $entrega->paciente }}: {{ $entrega->cantidad }} x {{ $entrega->producto }}
            </li>
        @empty
            <li class="list-group-item text-muted">Aún no se han registrado entregas.</li>
        @endforelse
    </ul>

    <form method="POST" action="/reiniciar" onsubmit="return confirm('¿Reiniciar todos los datos de la clínica?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">Reiniciar datos de ejemplo</button>
    </form>
@endsection
