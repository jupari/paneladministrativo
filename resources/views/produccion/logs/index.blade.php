@extends('adminlte::page')

@section('title', 'Logs de Producción')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
$breadcrumbs=[['title'=>'Producción','icon'=>'fas fa-industry','url'=>null]];
$currentTitle='Logs';
$currentIcon='fas fa-list';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header"><h4>Registro de Producción</h4></div>
  <div class="card-body">

    <div class="row">
      <div class="col-md-4">
        <label>Orden</label>
        <select id="filter_order_id" class="form-control"></select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary btn-block" onclick="applyLogFilters()"><i class="fas fa-search"></i> Filtrar</button>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-success btn-block" onclick="regLog()"><i class="fas fa-plus"></i> Nuevo</button>
      </div>
    </div>

    <div class="table-responsive mt-3">
      <table id="logs-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Fecha</th>
            <th>Turno</th>
            <th>Orden</th>
            <th>Producto</th>
            <th>Operación</th>
            <th>Empleado</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Rech.</th>
            <th class="text-right">Acept.</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>
</div>

@include('produccion.logs.modal')
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/logs/logs.js') }}"></script>
@stop
