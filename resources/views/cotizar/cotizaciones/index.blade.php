@extends('adminlte::page')

@section('title', 'Cotizaciones')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Cotizar',
                'icon' => 'fas fa-file-invoice',
                'url' => null
            ]
        ];
        $currentTitle = 'Lista de Cotizaciones';
        $currentIcon = 'fas fa-list';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    {{-- ── DASHBOARD CARDS ─────────────────────────────────────── --}}
    @if(isset($estadisticas))
    @php
        $cardConfig = [
            'Borrador'  => ['icon' => 'fas fa-pencil-alt', 'color' => '#6c757d', 'label' => 'Borrador'],
            'Enviado'   => ['icon' => 'fas fa-paper-plane','color' => '#17a2b8', 'label' => 'Enviados'],
            'Aprobado'  => ['icon' => 'fas fa-check-circle','color' => '#28a745','label' => 'Aprobados'],
            'Rechazado' => ['icon' => 'fas fa-times-circle','color' => '#dc3545','label' => 'Rechazados'],
            'Terminado' => ['icon' => 'fas fa-flag-checkered','color' => '#343a40','label' => 'Terminados'],
            'Anulado'   => ['icon' => 'fas fa-ban',         'color' => '#fd7e14','label' => 'Anulados'],
        ];
    @endphp
    <div class="row g-3 mb-3">
        {{-- Total general --}}
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100 stat-card" style="border-left:4px solid #1e3a5f;cursor:pointer;" onclick="filtrarPorEstado('')">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold fs-4">{{ $estadisticas['total_cotizaciones'] }}</div>
                            <div class="text-muted" style="font-size:11px;">${{ number_format($estadisticas['valor_total'], 0) }}</div>
                        </div>
                        <i class="fas fa-file-invoice fa-2x" style="color:#1e3a5f;opacity:.4;"></i>
                    </div>
                </div>
            </div>
        </div>
        @if($estadisticas['pendientes_respuesta'] > 0)
        {{-- Pendientes de respuesta (enviados sin respuesta) --}}
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100 stat-card" style="border-left:4px solid #fd7e14;cursor:pointer;" onclick="filtrarPorEstado('Enviado')">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Pend. Respuesta</div>
                            <div class="fw-bold fs-4" style="color:#fd7e14;">{{ $estadisticas['pendientes_respuesta'] }}</div>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x" style="color:#fd7e14;opacity:.4;"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @foreach($cardConfig as $estado => $cfg)
        @if(($estadisticas['conteos'][$estado] ?? 0) > 0)
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card shadow-sm h-100 stat-card" style="border-left:4px solid {{ $cfg['color'] }};cursor:pointer;" onclick="filtrarPorEstado('{{ $estado }}')">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">{{ $cfg['label'] }}</div>
                            <div class="fw-bold fs-4" style="color:{{ $cfg['color'] }};">{{ $estadisticas['conteos'][$estado] }}</div>
                            <div class="text-muted" style="font-size:11px;">${{ number_format($estadisticas['totales'][$estado] ?? 0, 0) }}</div>
                        </div>
                        <i class="{{ $cfg['icon'] }} fa-2x" style="color:{{ $cfg['color'] }};opacity:.4;"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif
    {{-- ── FIN CARDS ─────────────────────────────────────────────── --}}

    <div class="card">
        <div class="card-header">
            <h4>Cotizaciones</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole(['Administrator']) || auth()->user()->can('cotizaciones.create'))
                <div class="col-md-1">
                <a type="button"  href="{{ route('admin.cotizaciones.create') }}" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Cotización">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="cotizaciones-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>id</th>
                           <th>Documento</th>
                            <th>Versión</th>
                           <th>Cliente</th>
                            <th>Sede</th>
                           <th>Proyecto</th>
                           <th>Fecha creación</th>
                           <th class="text-center">Estado</th>
                           <th>Total</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/cotizar/cotizacion.js') }}" type="text/javascript"></script>
    <script>
    // Filtrar DataTable por estado al hacer clic en una card
    function filtrarPorEstado(estado) {
        const dt = $('#cotizaciones-table').DataTable();
        if (dt) {
            // Buscar en la columna Estado (índice 8)
            dt.column(8).search(estado).draw();
        }
    }
    </script>
@stop
