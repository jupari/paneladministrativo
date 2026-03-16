@extends('adminlte::page')
@section('title', 'Orden de Producción')

@section('plugin.Datatables')
@section('plugin.Select2')
@section('plugin.Sweetalert2')

@section('content')
@php
  $breadcrumbs = [
    ['title'=>'Producción', 'icon'=>'fas fa-industry', 'url'=>route('admin.produccion.orders.index')]
  ];
  $currentTitle = 'Orden #'.$order->code;
  $currentIcon = 'fas fa-clipboard-list';
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Orden {{ $order->code }}</h4>
    <div>
    </div>
  </div>

  <div class="card-body">
    <div class="d-flex justify-content-lg-start">
        <button class="btn btn-primary btn-sm" onclick="openLogModal()">
        <i class="fas fa-plus"></i> Registrar Producción
      </button>
    </div>
    <ul class="nav nav-tabs">
      <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-info">Orden</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-ops">Operaciones (Plan vs Real)</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-logs">Registros</a></li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-damages">
          Daños @if($totalDamaged > 0)<span class="badge badge-danger">{{ $totalDamaged }}</span>@endif
        </a>
      </li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-sett">Liquidación</a></li>
    </ul>

    <div class="tab-content pt-3">
      <div class="tab-pane fade show active" id="tab-info">

        {{-- Resumen en small-boxes --}}
        @php
          $objective   = (int) $order->total_units;
          $adjusted    = max(0, $objective - $totalDamaged);
          $progressPct = $adjusted > 0 ? min(100, round(($totalProduced / $adjusted) * 100, 1)) : ($totalProduced > 0 ? 100 : 0);
          $remaining   = max(0, $adjusted - $totalProduced);

          if ($progressPct >= 100)     { $progressColor = 'bg-success'; $progressLabel = 'Completado'; }
          elseif ($progressPct >= 50)  { $progressColor = 'bg-primary'; $progressLabel = 'En progreso'; }
          elseif ($progressPct > 0)    { $progressColor = 'bg-warning'; $progressLabel = 'Iniciado'; }
          else                         { $progressColor = 'bg-secondary'; $progressLabel = 'Sin avance'; }
        @endphp

        <div class="row">
          {{-- Objetivo --}}
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{ number_format($objective) }}</h3>
                <p>Objetivo (uds)</p>
              </div>
              <div class="icon"><i class="fas fa-bullseye"></i></div>
            </div>
          </div>
          {{-- Producidas --}}
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{ number_format($totalProduced) }}</h3>
                <p>Producidas</p>
              </div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
          </div>
          {{-- Dañadas --}}
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{ number_format($totalDamaged) }}</h3>
                <p>Dañadas
                  @if($totalDamaged > 0 && $objective > 0)
                    <small>({{ number_format(($totalDamaged / $objective) * 100, 1) }}%)</small>
                  @endif
                </p>
              </div>
              <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
          </div>
          {{-- Pendientes --}}
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{ number_format($remaining) }}</h3>
                <p>Pendientes</p>
              </div>
              <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
          </div>
        </div>

        {{-- Barra de progreso --}}
        <div class="mb-4">
          <div class="d-flex justify-content-between mb-1">
            <span class="font-weight-bold">Progreso general</span>
            <span class="font-weight-bold">{{ $progressPct }}% — {{ $progressLabel }}</span>
          </div>
          <div class="progress" style="height: 22px;">
            <div class="progress-bar {{ $progressColor }} progress-bar-striped progress-bar-animated"
                 role="progressbar" style="width: {{ $progressPct }}%"
                 aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>
        </div>

        {{-- Detalle de la orden --}}
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width:140px"><i class="fas fa-box mr-1"></i> Producto</td>
                <td class="font-weight-bold">{{ $productName }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-tshirt mr-1"></i> Tipo prenda</td>
                <td>{{ $order->garment_type ?: '—' }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-barcode mr-1"></i> Referencia</td>
                <td>{{ $order->garment_reference ?: '—' }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-palette mr-1"></i> Color</td>
                <td>{{ $order->color ?: '—' }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <td class="text-muted" style="width:140px"><i class="fas fa-calendar-alt mr-1"></i> Inicio</td>
                <td>{{ optional($order->start_date)->format('d/m/Y') ?: '—' }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-calendar-check mr-1"></i> Fecha límite</td>
                <td>{{ optional($order->deadline)->format('d/m/Y') ?: '—' }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-dollar-sign mr-1"></i> Costo/unidad</td>
                <td>{{ $order->cost_per_unit ? '$'.number_format($order->cost_per_unit, 2) : '—' }}</td>
              </tr>
              <tr>
                <td class="text-muted"><i class="fas fa-info-circle mr-1"></i> Estado</td>
                <td>
                  @php
                    $statusMap = [
                      'pending'     => ['secondary','Pendiente'],
                      'in_progress' => ['primary','En Progreso'],
                      'completed'   => ['success','Completada'],
                      'cancelled'   => ['danger','Cancelada'],
                    ];
                    [$badgeColor, $badgeText] = $statusMap[strtolower($order->status)] ?? ['info', ucfirst($order->status)];
                  @endphp
                  <span class="badge badge-{{ $badgeColor }}" style="font-size:.9em">{{ $badgeText }}</span>
                </td>
              </tr>
            </table>
          </div>
        </div>

        @if($order->notes)
        <div class="mt-2">
          <label class="text-muted"><i class="fas fa-sticky-note mr-1"></i> Notas</label>
          <p class="mb-0">{{ $order->notes }}</p>
        </div>
        @endif

      </div>

      <div class="tab-pane fade" id="tab-ops">
        @include('produccion.orders.tabs.operations')
      </div>

      <div class="tab-pane fade" id="tab-logs">
        @include('produccion.orders.tabs.logs')
      </div>

      <div class="tab-pane fade" id="tab-damages">
        @include('produccion.orders.tabs.damages')
      </div>

      <div class="tab-pane fade" id="tab-sett">
        @include('produccion.orders.tabs.settlements')
      </div>
    </div>
  </div>
</div>

@include('produccion.orders.modals.log_modal')
@stop

@section('js')
<script>
window.PROD = {
  orderId: {{ $order->id }},
  routes: {
    opsTable: "{{ route('admin.produccion.orders.operations.table', $order->id) }}",
    logsTable: "{{ route('admin.produccion.orders.logs.table', $order->id) }}",
    logStore: "{{ route('admin.produccion.orders.logs.store', $order->id) }}",
    damagesTable: "{{ route('admin.produccion.orders.damages.table', $order->id) }}",
    settlementData: "{{ route('admin.produccion.orders.settlements.data', $order->id) }}",
    settlementCalculate: "{{ route('admin.produccion.orders.settlements.calculate', $order->id) }}",
    opsSelect2: "{{ route('admin.produccion.orders.operations.select2', $order->id) }}",
    empSelect2: "{{ route('admin.produccion.employees.select2') }}",
  }
};
</script>
<script
    src="{{ asset('assets/js/produccion/orders/show.js') }}?v={{ time() }}"
        type="text/javascript">
</script>
@stop
