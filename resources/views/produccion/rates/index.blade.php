@extends('adminlte::page')

@section('title', 'Tarifas por Producto')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
  $breadcrumbs = [
    ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
  ];
  $currentTitle = 'Tarifas por Producto';
  $currentIcon = 'fas fa-tags';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header"><h4>Tarifas (Producto + Operación)</h4></div>
  <div class="card-body">
    @if(auth()->user()->can('tarifas.create'))
    <div class="col-md-1 mb-2">
      <button type="button" onclick="regRate()" class="btn btn-primary btn-block" title="Crear Tarifa">
        <i class="fas fa-plus"></i>
      </button>
    </div>
    @endif

    <div class="table-responsive">
      <table id="rates-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Producto</th>
            <th>Operación</th>
            <th class="text-right">Tarifa</th>
            <th>Vigencia Desde</th>
            <th>Vigencia Hasta</th>
            <th class="text-center">Estado</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>
</div>

@include('produccion.rates.modal')
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/rates/rates.js') }}"></script>
@stop
