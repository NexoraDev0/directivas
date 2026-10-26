# Clínica Salud Vital - Directivas Blade (Laravel)

Proyecto universitario de la **Universidad Cooperativa de Colombia** (docente: Ing. Alfredo Alvarez Rosero) para practicar las directivas básicas de Blade y el manejo de arreglos en Laravel, planteado como un pequeño sistema de gestión para una clínica.

## Integrantes

| Código | Nombres completos |
|---|---|
| CODIGO-1 | Julian David Rios Suarez |
| CODIGO-2 | NOMBRE COMPLETO 2 |
| CODIGO-3 | NOMBRE COMPLETO 3 |

## Módulos y directivas

| Módulo | Ruta | Directiva | Qué hace |
|---|---|---|---|
| Inicio | `/` | `@if`, `@foreach`, `@forelse` | Panel con resumen, alerta de stock y últimas entregas |
| Pacientes | `/directivaif` | `@if / @elseif / @else` | Registra pacientes y valida el ingreso según la edad con `<x-alert>` |
| Turnos | `/directivafor` | `@for` | Genera turnos aleatorios e identifica pares e impares |
| Farmacia | `/directivaforeach` | `@foreach` | Tabla de inventario y entregas con descuento de stock |
| Historia clínica | `/directivawhile` | `@while` | Registra diagnósticos y cuenta las vocales con un ciclo `@while` |

Los datos se guardan en la sesión (no se usa base de datos).

## Cómo ejecutarlo

1. Crea un proyecto Laravel: `composer create-project laravel/laravel directivas`
2. Crea cada archivo de este documento en la ruta indicada (los títulos indican la ruta).
3. Ejecuta `php artisan migrate` y luego `php artisan serve`.
4. Abre `http://127.0.0.1:8000`.

Requiere PHP 8.1 o superior y Laravel 10, 11 o 12. Bootstrap y Font Awesome se cargan por CDN.

---

# Código del proyecto

## `routes/web.php`

```php
<?php

use App\Http\Controllers\DirectivaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DirectivaController::class, 'inicio']);
Route::post('/reiniciar', [DirectivaController::class, 'reiniciar']);

Route::get('/directivaif', [DirectivaController::class, 'mostrarif']);
Route::post('/directivaif', [DirectivaController::class, 'guardarpaciente']);

Route::get('/directivafor', [DirectivaController::class, 'listadofor']);
Route::post('/directivafor', [DirectivaController::class, 'generarturnos']);

Route::get('/directivaforeach', [DirectivaController::class, 'listadoforeach']);
Route::post('/directivaforeach', [DirectivaController::class, 'dispensar']);

Route::get('/directivawhile', [DirectivaController::class, 'mostrarwhile']);
Route::post('/directivawhile', [DirectivaController::class, 'guardarhistoria']);

```

## `app/Http/Controllers/DirectivaController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DirectivaController extends Controller
{
    private const STOCK_BAJO = 20;

    /* ------------------------------------------------------------------
     | Datos de la clínica (se guardan en la sesión, no se usa base de datos)
     ------------------------------------------------------------------ */

    private function pacientes(): array
    {
        if (!session()->has('pacientes')) {
            session(['pacientes' => [
                ['nombres' => 'Carlos',          'apellidos' => 'Pérez Gómez',      'edad' => 25],
                ['nombres' => 'Laura',           'apellidos' => 'Ramírez Díaz',     'edad' => 15],
                ['nombres' => 'Andrés',          'apellidos' => 'Torres Rojas',     'edad' => 10],
                ['nombres' => 'Marcela',         'apellidos' => 'Castro Mejía',     'edad' => 13],
                ['nombres' => 'Sofía',           'apellidos' => 'Herrera Cano',     'edad' => 40],
                ['nombres' => 'Juan Camilo',     'apellidos' => 'Vargas Ríos',      'edad' => 7],
                ['nombres' => 'Valentina',       'apellidos' => 'Moreno Ortiz',     'edad' => 17],
                ['nombres' => 'Luis Fernando',   'apellidos' => 'Gutiérrez Pardo',  'edad' => 62],
                ['nombres' => 'Daniela',         'apellidos' => 'Suárez Peña',      'edad' => 30],
                ['nombres' => 'Santiago',        'apellidos' => 'Rojas Mora',       'edad' => 12],
                ['nombres' => 'María José',      'apellidos' => 'Cárdenas Silva',   'edad' => 52],
                ['nombres' => 'Esteban',         'apellidos' => 'Molina Duque',     'edad' => 19],
                ['nombres' => 'Isabella',        'apellidos' => 'Navarro León',     'edad' => 9],
                ['nombres' => 'Jorge Enrique',   'apellidos' => 'Salcedo Bernal',   'edad' => 71],
            ]]);
        }

        return session('pacientes');
    }

    private function productos(): array
    {
        if (!session()->has('productos')) {
            session(['productos' => [
                ['id' => 1,  'codigo' => 'MED-001', 'producto' => 'Acetaminofén 500 mg',   'cantidad' => 120, 'precio' => 8500],
                ['id' => 2,  'codigo' => 'MED-002', 'producto' => 'Ibuprofeno 400 mg',     'cantidad' => 90,  'precio' => 12000],
                ['id' => 3,  'codigo' => 'MED-003', 'producto' => 'Amoxicilina 500 mg',    'cantidad' => 60,  'precio' => 18500],
                ['id' => 4,  'codigo' => 'MED-004', 'producto' => 'Loratadina 10 mg',      'cantidad' => 75,  'precio' => 9500],
                ['id' => 5,  'codigo' => 'MED-005', 'producto' => 'Omeprazol 20 mg',       'cantidad' => 80,  'precio' => 14000],
                ['id' => 6,  'codigo' => 'INS-001', 'producto' => 'Jeringas x 10',         'cantidad' => 200, 'precio' => 6500],
                ['id' => 7,  'codigo' => 'INS-002', 'producto' => 'Guantes de látex x 100', 'cantidad' => 45, 'precio' => 32000],
                ['id' => 8,  'codigo' => 'INS-003', 'producto' => 'Tapabocas x 50',        'cantidad' => 150, 'precio' => 15000],
                ['id' => 9,  'codigo' => 'INS-004', 'producto' => 'Gasas estériles x 20',  'cantidad' => 18,  'precio' => 7500],
                ['id' => 10, 'codigo' => 'INS-005', 'producto' => 'Termómetro digital',    'cantidad' => 30,  'precio' => 28000],
            ]]);
        }

        return session('productos');
    }

    private function turnos(): array
    {
        if (!session()->has('turnos')) {
            session(['turnos' => $this->generarNumeros(10)]);
        }

        return session('turnos');
    }

    private function historias(): array
    {
        if (!session()->has('historias')) {
            session(['historias' => [
                ['id' => 1, 'paciente' => 'Carlos Pérez Gómez',    'diagnostico' => 'Hipertensión arterial leve',          'fecha' => now()->format('d/m/Y H:i')],
                ['id' => 2, 'paciente' => 'Laura Ramírez Díaz',    'diagnostico' => 'Faringoamigdalitis aguda',            'fecha' => now()->format('d/m/Y H:i')],
                ['id' => 3, 'paciente' => 'Sofía Herrera Cano',    'diagnostico' => 'Control de diabetes mellitus tipo dos', 'fecha' => now()->format('d/m/Y H:i')],
            ]]);
        }

        return session('historias');
    }

    private function generarNumeros(int $cantidad): array
    {
        $numeros = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $numeros[] = rand(1, 99);
        }

        return $numeros;
    }

    private function aObjetos(array $lista): array
    {
        return array_map(fn($item) => (object) $item, $lista);
    }

    // Pacientes con acceso a la clínica (13 años o más). Conserva el índice original.
    private function admitidos(): array
    {
        return array_filter($this->aObjetos($this->pacientes()), fn($p) => $p->edad >= 13);
    }

    private function soloLetras(string $texto): string
    {
        return preg_replace('/[^\p{L}]/u', '', $texto);
    }

    /* ------------------------------------------------------------------
     | Inicio (resumen general)
     ------------------------------------------------------------------ */

    public function inicio()
    {
        $pacientes = $this->pacientes();
        $productos = $this->productos();
        $turnos    = $this->turnos();
        $historias = $this->historias();
        $entregas  = session('entregas', []);

        $stockBajo = count(array_filter($productos, fn($p) => $p['cantidad'] <= self::STOCK_BAJO));

        $tarjetas = [
            (object)['titulo' => 'Pacientes',        'valor' => count($pacientes), 'icono' => 'fa-user-injured',  'ruta' => '/directivaif',      'detalle' => count($this->admitidos()) . ' con acceso a la clínica'],
            (object)['titulo' => 'Turnos',           'valor' => count($turnos),    'icono' => 'fa-ticket',        'ruta' => '/directivafor',     'detalle' => 'turnos generados'],
            (object)['titulo' => 'Farmacia',         'valor' => count($productos), 'icono' => 'fa-pills',         'ruta' => '/directivaforeach', 'detalle' => $stockBajo . ' con stock bajo'],
            (object)['titulo' => 'Historia clínica', 'valor' => count($historias), 'icono' => 'fa-notes-medical', 'ruta' => '/directivawhile',   'detalle' => 'notas registradas'],
        ];

        $ultimasEntregas = $this->aObjetos(array_slice(array_reverse($entregas), 0, 5));

        return view('inicio', compact('tarjetas', 'stockBajo', 'ultimasEntregas'));
    }

    public function reiniciar()
    {
        session()->forget(['pacientes', 'productos', 'turnos', 'entregas', 'historias']);

        return redirect('/')->with('ok', 'Los datos de la clínica volvieron a su estado inicial.');
    }

    /* ------------------------------------------------------------------
     | Ejercicio 1 - @if: admisión de pacientes según la edad
     ------------------------------------------------------------------ */

    public function mostrarif(Request $request)
    {
        $todos  = $this->aObjetos($this->pacientes());
        $buscar = trim((string) $request->query('buscar', ''));

        $usuarios = array_filter($todos, function ($p) use ($buscar) {
            return $buscar === '' || mb_stripos($p->nombres . ' ' . $p->apellidos, $buscar) !== false;
        });

        $resumen = (object)[
            'total'        => count($todos),
            'conAcceso'    => count(array_filter($todos, fn($p) => $p->edad >= 18)),
            'autorizacion' => count(array_filter($todos, fn($p) => $p->edad >= 13 && $p->edad < 18)),
            'sinAcceso'    => count(array_filter($todos, fn($p) => $p->edad < 13)),
        ];

        return view('directivaif', compact('usuarios', 'resumen', 'buscar'));
    }

    public function guardarpaciente(Request $request)
    {
        $datos = $request->validate([
            'nombres'   => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'edad'      => 'required|integer|min:0|max:120',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'min'      => 'El campo :attribute no es válido.',
            'max'      => 'El campo :attribute es demasiado largo o grande.',
        ]);

        $pacientes = $this->pacientes();
        $pacientes[] = [
            'nombres'   => trim($datos['nombres']),
            'apellidos' => trim($datos['apellidos']),
            'edad'      => (int) $datos['edad'],
        ];
        session(['pacientes' => $pacientes]);

        return redirect('/directivaif')->with('ok', 'Paciente registrado: ' . trim($datos['nombres']) . ' ' . trim($datos['apellidos']) . '.');
    }

    /* ------------------------------------------------------------------
     | Ejercicio 2 - @for: turnos de atención (par/impar)
     ------------------------------------------------------------------ */

    public function listadofor()
    {
        $numeros   = $this->turnos();
        $admitidos = array_values($this->admitidos());
        $pares     = count(array_filter($numeros, fn($n) => $n % 2 == 0));
        $impares   = count($numeros) - $pares;

        return view('directivafor', compact('numeros', 'admitidos', 'pares', 'impares'));
    }

    public function generarturnos(Request $request)
    {
        $datos = $request->validate([
            'cantidad' => 'required|integer|min:10|max:30',
        ], [
            'required' => 'Indica cuántos turnos generar.',
            'integer'  => 'La cantidad debe ser un número entero.',
            'min'      => 'Debes generar mínimo 10 turnos.',
            'max'      => 'Puedes generar máximo 30 turnos.',
        ]);

        session(['turnos' => $this->generarNumeros((int) $datos['cantidad'])]);

        return redirect('/directivafor')->with('ok', 'Se generaron ' . $datos['cantidad'] . ' turnos nuevos.');
    }

    /* ------------------------------------------------------------------
     | Ejercicio 3 - @foreach: inventario y entrega de farmacia
     ------------------------------------------------------------------ */

    public function listadoforeach()
    {
        $productos = $this->aObjetos($this->productos());
        $admitidos = $this->admitidos();
        $entregas  = $this->aObjetos(array_reverse(session('entregas', [])));
        $stockBajo = self::STOCK_BAJO;

        return view('directivaforeach', compact('productos', 'admitidos', 'entregas', 'stockBajo'));
    }

    public function dispensar(Request $request)
    {
        $datos = $request->validate([
            'paciente' => 'required|integer',
            'producto' => 'required|integer',
            'cantidad' => 'required|integer|min:1',
        ], [
            'required' => 'Completa el campo :attribute.',
            'integer'  => 'El campo :attribute no es válido.',
            'min'      => 'La cantidad debe ser al menos 1.',
        ]);

        $pacientes = $this->pacientes();
        $productos = $this->productos();

        $paciente = $pacientes[$datos['paciente']] ?? null;
        if ($paciente === null || $paciente['edad'] < 13) {
            return back()->withErrors(['paciente' => 'El paciente seleccionado no tiene acceso a la clínica.'])->withInput();
        }

        $posicion = null;
        foreach ($productos as $indice => $producto) {
            if ($producto['id'] == $datos['producto']) {
                $posicion = $indice;
            }
        }
        if ($posicion === null) {
            return back()->withErrors(['producto' => 'El producto seleccionado no existe.'])->withInput();
        }

        $cantidad = (int) $datos['cantidad'];
        if ($cantidad > $productos[$posicion]['cantidad']) {
            return back()->withErrors(['cantidad' => 'Stock insuficiente: solo quedan ' . $productos[$posicion]['cantidad'] . ' unidades.'])->withInput();
        }

        $productos[$posicion]['cantidad'] -= $cantidad;
        session(['productos' => $productos]);

        $entregas = session('entregas', []);
        $entregas[] = [
            'paciente' => $paciente['nombres'] . ' ' . $paciente['apellidos'],
            'producto' => $productos[$posicion]['producto'],
            'cantidad' => $cantidad,
            'total'    => $cantidad * $productos[$posicion]['precio'],
            'hora'     => now()->format('d/m/Y H:i'),
        ];
        session(['entregas' => $entregas]);

        return redirect('/directivaforeach')->with('ok', 'Entrega registrada para ' . $paciente['nombres'] . ' ' . $paciente['apellidos'] . '.');
    }

    /* ------------------------------------------------------------------
     | Ejercicio 4 - @while: historia clínica (análisis de vocales del diagnóstico)
     ------------------------------------------------------------------ */

    public function mostrarwhile(Request $request)
    {
        $admitidos = $this->admitidos();
        $historias = $this->aObjetos(array_reverse($this->historias()));

        // Nota seleccionada: la indicada en la URL o, por defecto, la más reciente
        $seleccionada = $historias[0] ?? null;
        foreach ($historias as $historia) {
            if ($historia->id == $request->query('nota')) {
                $seleccionada = $historia;
            }
        }

        // Arreglo indexado con las letras del diagnóstico (mínimo 10 posiciones)
        $letras = $seleccionada ? mb_str_split($this->soloLetras($seleccionada->diagnostico)) : [];

        return view('directivawhile', compact('admitidos', 'historias', 'seleccionada', 'letras'));
    }

    public function guardarhistoria(Request $request)
    {
        $datos = $request->validate([
            'paciente'    => 'required|integer',
            'diagnostico' => 'required|string|max:200',
        ], [
            'required' => 'Completa el campo :attribute.',
            'integer'  => 'El campo :attribute no es válido.',
            'max'      => 'El diagnóstico es demasiado largo (máximo 200 caracteres).',
        ]);

        if (mb_strlen($this->soloLetras($datos['diagnostico'])) < 10) {
            return back()->withErrors(['diagnostico' => 'El diagnóstico debe tener mínimo 10 letras.'])->withInput();
        }

        $pacientes = $this->pacientes();
        $paciente  = $pacientes[$datos['paciente']] ?? null;
        if ($paciente === null || $paciente['edad'] < 13) {
            return back()->withErrors(['paciente' => 'El paciente seleccionado no tiene acceso a la clínica.'])->withInput();
        }

        $historias = $this->historias();
        $nuevoId   = count($historias) > 0 ? max(array_column($historias, 'id')) + 1 : 1;

        $historias[] = [
            'id'          => $nuevoId,
            'paciente'    => $paciente['nombres'] . ' ' . $paciente['apellidos'],
            'diagnostico' => trim($datos['diagnostico']),
            'fecha'       => now()->format('d/m/Y H:i'),
        ];
        session(['historias' => $historias]);

        return redirect('/directivawhile?nota=' . $nuevoId)->with('ok', 'Nota clínica registrada para ' . $paciente['nombres'] . ' ' . $paciente['apellidos'] . '.');
    }
}

```

## `resources/views/layouts/home.blade.php`

```blade
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Salud Vital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <x-header></x-header>
    </header>

    <main>
        <section class="container mt-3">
            <x-mensajes></x-mensajes>
        </section>

        <section class="container mt-3">
            @yield('content')
        </section>
    </main>

    <footer>
        <x-footer></x-footer>
    </footer>
</body>

</html>

```

## `resources/views/components/header.blade.php`

```blade
<div class="bg-success text-white py-3">
    <div class="container d-flex align-items-center gap-3">
        <i class="fa-solid fa-hospital fs-2"></i>
        <div>
            <h1 class="h4 mb-0">Clínica Salud Vital</h1>
            <small>Sistema de gestión clínica</small>
        </div>
    </div>
</div>

<x-nav></x-nav>

```

## `resources/views/components/nav.blade.php`

```blade
<nav class="bg-body-tertiary border-bottom">
    <div class="container">
        <ul class="nav nav-underline">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/"><i class="fa-solid fa-house"></i> Inicio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('directivaif') ? 'active' : '' }}" href="/directivaif"><i class="fa-solid fa-user-injured"></i> Pacientes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('directivafor') ? 'active' : '' }}" href="/directivafor"><i class="fa-solid fa-ticket"></i> Turnos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('directivaforeach') ? 'active' : '' }}" href="/directivaforeach"><i class="fa-solid fa-pills"></i> Farmacia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('directivawhile') ? 'active' : '' }}" href="/directivawhile"><i class="fa-solid fa-notes-medical"></i> Historia clínica</a>
            </li>
        </ul>
    </div>
</nav>

```

## `resources/views/components/alert.blade.php`

```blade
@props(['type' => 'info'])

<div {{ $attributes->merge(['class' => 'alert alert-' . $type]) }} role="alert">
    {{ $slot }}
</div>

```

## `resources/views/components/mensajes.blade.php`

```blade
{{-- Mensajes de la aplicación: éxito, advertencia y errores de validación --}}
@if (session('ok'))
    <x-alert type="success">{{ session('ok') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="warning">{{ session('error') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert type="danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

```

## `resources/views/components/titulo.blade.php`

```blade
@props(['icono' => 'fa-circle', 'titulo', 'directiva', 'descripcion' => null])

<div class="mb-3">
    <h2>
        <i class="fa-solid {{ $icono }} text-success"></i>
        {{ $titulo }} - <span style="color: brown">&#64{{ $directiva }}</span>
    </h2>
    @if ($descripcion)
        <p class="text-muted">{{ $descripcion }}</p>
    @endif
</div>

```

## `resources/views/components/card.blade.php`

```blade
@props(['titulo' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-body">
        @if ($titulo)
            <h5 class="card-title">{{ $titulo }}</h5>
        @endif

        {{ $slot }}
    </div>
</div>

```

## `resources/views/components/stat.blade.php`

```blade
@props(['valor', 'etiqueta', 'color' => 'dark'])

<div class="border border-{{ $color }} rounded py-2 text-center">
    <div class="fs-4 text-{{ $color }}">{{ $valor }}</div>
    <small class="text-muted">{{ $etiqueta }}</small>
</div>

```

## `resources/views/components/footer.blade.php`

```blade
@php
    // Completar con los datos reales de cada integrante del grupo
    $integrantes = [
        (object)['codigo' => 'CODIGO-1', 'nombre' => 'Julian David Rios Suarez'],
        (object)['codigo' => 'CODIGO-2', 'nombre' => 'NOMBRE COMPLETO 2'],
        (object)['codigo' => 'CODIGO-3', 'nombre' => 'NOMBRE COMPLETO 3'],
    ];
@endphp

<div class="bg-body-tertiary border-top mt-5 py-4">
    <div class="container">
        <h6 class="text-success"><i class="fa-solid fa-heart-pulse"></i> Clínica Salud Vital - Integrantes del grupo</h6>
        <table class="table table-sm table-bordered w-auto bg-white">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombres completos</th>
                    <th>Fecha de realización</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($integrantes as $integrante)
                    <tr>
                        <td>{{ $integrante->codigo }}</td>
                        <td>{{ $integrante->nombre }}</td>
                        <td>{{ now()->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

```

## `resources/views/inicio.blade.php`

```blade
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

```

## `resources/views/directivaif.blade.php`

```blade
@extends('layouts.home')

@section('content')
    <x-titulo
        icono="fa-user-injured"
        titulo="Admisión de pacientes"
        directiva="if"
        descripcion="Registra un paciente y el sistema verifica si puede ingresar según su edad." />

    <x-card titulo="Registrar paciente" class="mb-4">
        <form method="POST" action="/directivaif" class="row g-2">
            @csrf
            <div class="col-md-4">
                <input type="text" name="nombres" class="form-control" placeholder="Nombres" value="{{ old('nombres') }}" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="apellidos" class="form-control" placeholder="Apellidos" value="{{ old('apellidos') }}" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="edad" class="form-control" placeholder="Edad" min="0" max="120" value="{{ old('edad') }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Registrar</button>
            </div>
        </form>
    </x-card>

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3"><x-stat :valor="$resumen->total" etiqueta="Pacientes registrados" color="dark" /></div>
        <div class="col-6 col-md-3"><x-stat :valor="$resumen->conAcceso" etiqueta="Pueden ingresar" color="success" /></div>
        <div class="col-6 col-md-3"><x-stat :valor="$resumen->autorizacion" etiqueta="Necesitan autorización" color="warning" /></div>
        <div class="col-6 col-md-3"><x-stat :valor="$resumen->sinAcceso" etiqueta="Sin acceso" color="danger" /></div>
    </div>

    <form method="GET" action="/directivaif" class="row g-2 mb-3">
        <div class="col-md-8">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar paciente por nombre o apellido..." value="{{ $buscar }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-outline-success">Buscar</button>
            @if ($buscar !== '')
                <a href="/directivaif" class="btn btn-outline-secondary">Limpiar</a>
            @endif
        </div>
    </form>

    <h5>Listado de pacientes ({{ count($usuarios) }} de {{ $resumen->total }})</h5>

    @forelse ($usuarios as $usuario)
        @if ($usuario->edad >= 18)
            <x-alert type="success">
                El usuario {{ $usuario->nombres }} {{ $usuario->apellidos }} puede ingresar, su edad es: {{ $usuario->edad }}
            </x-alert>
        @elseif ($usuario->edad >= 13)
            <x-alert type="warning">
                El usuario {{ $usuario->nombres }} {{ $usuario->apellidos }} necesita autorización para ingresar, su edad es: {{ $usuario->edad }}
            </x-alert>
        @else
            <x-alert type="danger">
                El usuario {{ $usuario->nombres }} {{ $usuario->apellidos }} no tiene acceso, su edad es: {{ $usuario->edad }}
            </x-alert>
        @endif
    @empty
        <x-alert type="secondary">No se encontraron pacientes.</x-alert>
    @endforelse
@endsection

```

## `resources/views/directivafor.blade.php`

```blade
@extends('layouts.home')

@section('content')
    <x-titulo
        icono="fa-ticket"
        titulo="Turnos de atención"
        directiva="for"
        descripcion="Los turnos pares se atienden en el Consultorio A y los impares en el Consultorio B. Los turnos se asignan en orden a los pacientes que tienen acceso a la clínica." />

    <x-card class="mb-4">
        <form method="POST" action="/directivafor" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="cantidad" class="form-label">Cantidad de turnos (10 a 30)</label>
                <input type="number" id="cantidad" name="cantidad" class="form-control" min="10" max="30" value="{{ old('cantidad', count($numeros)) }}" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success">Generar turnos</button>
            </div>
        </form>
    </x-card>

    <div class="row g-2 mb-3">
        <div class="col-4"><x-stat :valor="$pares" etiqueta="Turnos pares" color="primary" /></div>
        <div class="col-4"><x-stat :valor="$impares" etiqueta="Turnos impares" color="secondary" /></div>
        <div class="col-4"><x-stat :valor="count($admitidos)" etiqueta="Pacientes con acceso" color="success" /></div>
    </div>

    <ul class="list-group">
        @for ($i = 0; $i < count($numeros); $i++)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    {{ $i + 1 }}. Turno <strong>{{ $numeros[$i] }}</strong> -
                    @if ($i < count($admitidos))
                        Paciente: {{ $admitidos[$i]->nombres }} {{ $admitidos[$i]->apellidos }}
                    @else
                        <span class="text-muted">sin paciente asignado</span>
                    @endif
                </span>
                @if ($numeros[$i] % 2 == 0)
                    <span class="badge text-bg-primary">Par - Consultorio A</span>
                @else
                    <span class="badge text-bg-secondary">Impar - Consultorio B</span>
                @endif
            </li>
        @endfor
    </ul>
@endsection

```

## `resources/views/directivaforeach.blade.php`

```blade
@extends('layouts.home')

@section('content')
    <x-titulo
        icono="fa-pills"
        titulo="Farmacia"
        directiva="foreach"
        descripcion="Inventario de medicamentos e insumos. Cada entrega descuenta unidades del stock." />

    <x-card titulo="Registrar entrega" class="mb-4">
        @if (count($admitidos) > 0)
            <form method="POST" action="/directivaforeach" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <select name="paciente" class="form-select" required>
                        <option value="">Paciente...</option>
                        @foreach ($admitidos as $indice => $paciente)
                            <option value="{{ $indice }}" @selected((string) old('paciente') === (string) $indice)>
                                {{ $paciente->nombres }} {{ $paciente->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="producto" class="form-select" required>
                        <option value="">Producto...</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(old('producto') == $producto->id) @disabled($producto->cantidad == 0)>
                                {{ $producto->producto }} ({{ $producto->cantidad }} disp.)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="cantidad" class="form-control" placeholder="Cantidad" min="1" value="{{ old('cantidad') }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Entregar</button>
                </div>
            </form>
        @else
            <x-alert type="warning">No hay pacientes con acceso. Registra uno en el módulo de Pacientes.</x-alert>
        @endif
    </x-card>

    <table class="table table-striped table-bordered">
        <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->codigo }}</td>
                    <td>{{ $producto->producto }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td>${{ number_format($producto->precio, 0, ',', '.') }}</td>
                    <td>
                        @if ($producto->cantidad == 0)
                            <span class="badge text-bg-danger">Agotado</span>
                        @elseif ($producto->cantidad <= $stockBajo)
                            <span class="badge text-bg-warning">Stock bajo</span>
                        @else
                            <span class="badge text-bg-success">Disponible</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h5 class="mt-4">Historial de entregas</h5>
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entregas as $entrega)
                <tr>
                    <td>{{ $entrega->hora }}</td>
                    <td>{{ $entrega->paciente }}</td>
                    <td>{{ $entrega->producto }}</td>
                    <td>{{ $entrega->cantidad }}</td>
                    <td>${{ number_format($entrega->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Aún no hay entregas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

```

## `resources/views/directivawhile.blade.php`

```blade
@extends('layouts.home')

@section('content')
    <x-titulo
        icono="fa-notes-medical"
        titulo="Historia clínica"
        directiva="while"
        descripcion="Registra el diagnóstico de un paciente (mínimo 10 letras). El sistema descompone el diagnóstico en un arreglo de letras y cuenta sus vocales como control de la nota." />

    <x-card titulo="Nueva nota clínica" class="mb-4">
        @if (count($admitidos) > 0)
            <form method="POST" action="/directivawhile" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <select name="paciente" class="form-select" required>
                        <option value="">Paciente...</option>
                        @foreach ($admitidos as $indice => $paciente)
                            <option value="{{ $indice }}" @selected((string) old('paciente') === (string) $indice)>
                                {{ $paciente->nombres }} {{ $paciente->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="diagnostico" class="form-control" placeholder="Diagnóstico (mínimo 10 letras)" maxlength="200" value="{{ old('diagnostico') }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Guardar</button>
                </div>
            </form>
        @else
            <x-alert type="warning">No hay pacientes con acceso. Registra uno en el módulo de Pacientes.</x-alert>
        @endif
    </x-card>

    @if ($seleccionada)
        @php
            // Conteo de vocales con @while sobre el arreglo de letras
            $i = 0;
            $totalVocales = 0;
            $conteo = ['a' => 0, 'e' => 0, 'i' => 0, 'o' => 0, 'u' => 0];
            $acentos = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u'];
        @endphp

        <h5>Análisis de la nota #{{ $seleccionada->id }}</h5>
        <p class="mb-2">
            <strong>Paciente:</strong> {{ $seleccionada->paciente }}<br>
            <strong>Diagnóstico:</strong> {{ $seleccionada->diagnostico }}<br>
            <strong>Fecha:</strong> {{ $seleccionada->fecha }}
        </p>

        <div class="d-flex flex-wrap gap-1 mb-3">
            @while ($i < count($letras))
                @php $base = strtr(mb_strtolower($letras[$i]), $acentos); @endphp
                @if (array_key_exists($base, $conteo))
                    <span class="badge text-bg-success fs-6" title="Posición {{ $i + 1 }}">{{ $letras[$i] }}</span>
                    @php
                        $conteo[$base]++;
                        $totalVocales++;
                    @endphp
                @else
                    <span class="badge text-bg-light border fs-6" title="Posición {{ $i + 1 }}">{{ $letras[$i] }}</span>
                @endif
                @php $i++; @endphp
            @endwhile
        </div>

        <x-alert type="info">
            El diagnóstico tiene <strong>{{ count($letras) }}</strong> letras:
            <strong>{{ $totalVocales }}</strong> vocales y <strong>{{ count($letras) - $totalVocales }}</strong> consonantes.
            <div class="mt-2">
                @foreach ($conteo as $vocal => $cantidad)
                    <span class="badge text-bg-secondary">{{ strtoupper($vocal) }}: {{ $cantidad }}</span>
                @endforeach
            </div>
        </x-alert>
    @endif

    <h5 class="mt-4">Historial de notas clínicas</h5>
    <table class="table table-sm table-bordered align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Diagnóstico</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historias as $historia)
                <tr @class(['table-success' => $seleccionada && $seleccionada->id == $historia->id])>
                    <td>{{ $historia->id }}</td>
                    <td>{{ $historia->fecha }}</td>
                    <td>{{ $historia->paciente }}</td>
                    <td>{{ $historia->diagnostico }}</td>
                    <td><a href="/directivawhile?nota={{ $historia->id }}" class="btn btn-sm btn-outline-success">Analizar</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Aún no hay notas clínicas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

```
