@extends('adminlte::page')

@section('title', 'Operaciones')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
  $breadcrumbs = [
    ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
  ];
  $currentTitle = 'Operaciones';
  $currentIcon = 'fas fa-tasks';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header"><h4>Operaciones</h4></div>
  <div class="card-body">
    @if(auth()->user()->can('operaciones.create'))
        <div class="col-md-1 mb-2">
        <button type="button" onclick="regOperation()" class="btn btn-primary btn-block" title="Crear Operación">
            <i class="fas fa-plus"></i>
        </button>
        </div>
    @endif
    <div class="table-responsive">
      <table id="operations-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th class="text-center">Estado</th>
            <th>Creación</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>
</div>

@include('produccion.operations.modal')
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/operations/operations.js') }}"></script>
@stop
