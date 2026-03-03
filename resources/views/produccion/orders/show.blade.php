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
      <button class="btn btn-primary btn-sm" onclick="openLogModal()">
        <i class="fas fa-plus"></i> Registrar Producción
      </button>
    </div>
  </div>

  <div class="card-body">
    <ul class="nav nav-tabs">
      <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-info">Orden</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-ops">Operaciones (Plan vs Real)</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-logs">Registros</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-sett">Liquidación</a></li>
    </ul>

    <div class="tab-content pt-3">
      <div class="tab-pane fade show active" id="tab-info">
        <div class="row">
          <div class="col-md-3"><b>Producto:</b> {{ $order->product_id }}</div>
          <div class="col-md-3"><b>Objetivo:</b> {{ $order->objective_qty }}</div>
          <div class="col-md-3"><b>Estado:</b> {{ $order->status }}</div>
        </div>
      </div>

      <div class="tab-pane fade" id="tab-ops">
        @include('produccion.orders.tabs.operations')
      </div>

      <div class="tab-pane fade" id="tab-logs">
        @include('produccion.orders.tabs.logs')
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
    logStore: "{{ route('admin.produccion.orders.logs.store', $order->id) }}"
  }
};
</script>
<script src="{{ asset('assets/js/produccion/orders/show.js') }}"></script>
@stop
