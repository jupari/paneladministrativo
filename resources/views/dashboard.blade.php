@extends('adminlte::page')

@section('title', 'Inicio')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="mb-1">Panel de Inicio</h1>
            <p class="text-muted mb-0">Resumen general y accesos rápidos.</p>
        </div>
        <div class="mt-2 mt-md-0">
            <span class="badge badge-light p-2">
                <i class="far fa-calendar-alt mr-1"></i> {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>
@stop

@section('content')
    @php
        $totalCuentas = collect($resultados)->sum('cantidad');
    @endphp

    <div class="container-fluid">
        <div class="welcome-banner mb-4">
            <div>
                <h4 class="mb-1">Bienvenido, {{ auth()->user()->name ?? 'Usuario' }}</h4>
                <p class="mb-0 text-muted">Consulta indicadores y entra rápido a los módulos más usados.</p>
            </div>
            <div class="welcome-total">
                <div class="text-muted">Total de cuentas</div>
                <div class="h3 mb-0">{{ $totalCuentas }}</div>
            </div>
        </div>

        @if(empty($resultados))
            <div class="alert alert-info">
                No hay indicadores disponibles por ahora.
            </div>
        @endif

        <div class="row">
            @foreach ($resultados as $item)
                <div class="col-lg-3 col-sm-6">
                    <div class="small-box bg-{{ $item->color }}">
                        <div class="inner">
                            <h3>{{ $item->cantidad }}</h3>
                            <p>{{ $item->estado }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Accesos rápidos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('cotizaciones.index')
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('admin.cotizaciones.index') }}" class="quick-link">
                                <i class="fas fa-file-invoice"></i>
                                <span>Cotizaciones</span>
                            </a>
                        </div>
                    @endcan
                    @can('nomina.index')
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('admin.nomina.payruns.index') }}" class="quick-link">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Nómina</span>
                            </a>
                        </div>
                    @endcan
                    @can('configuracion.index')
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('admin.produccion.orders.index') }}" class="quick-link">
                                <i class="fas fa-industry"></i>
                                <span>Producción</span>
                            </a>
                        </div>
                    @endcan
                    @can('users.index')
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('admin.users.index') }}" class="quick-link">
                                <i class="fas fa-users-cog"></i>
                                <span>Usuarios</span>
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estados de cuentas</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th class="text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resultados as $item)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $item->color }}">{{ $item->estado }}</span>
                                    </td>
                                    <td class="text-right">{{ $item->cantidad }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin datos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@include('admin.cuentaabonado.modal-cambiopass')
@include('admin.emailreader.email')

@section('css')
<style>
    .welcome-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border-radius: 10px;
        background: linear-gradient(135deg, #ffffff 0%, #eef4ff 100%);
        border: 1px solid #dfe7f7;
    }

    .welcome-total {
        min-width: 160px;
        text-align: right;
    }

    .quick-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 110px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #1f2937;
        text-decoration: none;
        transition: all .2s ease;
    }

    .quick-link:hover {
        transform: translateY(-2px);
        text-decoration: none;
        color: #0d6efd;
        border-color: #bfd5ff;
        box-shadow: 0 8px 18px rgba(13, 110, 253, .12);
    }

    .quick-link i {
        font-size: 1.25rem;
    }

    @media (max-width: 767px) {
        .welcome-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .welcome-total {
            text-align: left;
            min-width: auto;
        }
    }
</style>
@stop

@section('js')
    <script src="{{ asset('assets/js/dashboard/dashboard.js') }}" type="text/javascript"></script>
@stop

