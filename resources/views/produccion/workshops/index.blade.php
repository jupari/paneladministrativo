@extends('adminlte::page')

@section('title', 'Talleres')
@section('plugin.Datatables', true)
@section('plugin.Toastr')
@section('plugin.Sweetalert2', true)

@section('content')
@php
  $breadcrumbs = [
    ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
  ];
  $currentTitle = 'Talleres';
  $currentIcon = 'fas fa-warehouse';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Talleres</h4>
  </div>
  <div class="card-body">
    <div class="d-flex justify-content-lg-start my-3">
        <button type="button" class="btn btn-primary" onclick="createWorkshop()">
            <i class="fas fa-plus"></i> Nuevo taller
        </button>
    </div>
    <div class="table-responsive">
      <table id="workshops-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Coordinador</th>
            <th>Teléfono</th>
            <th class="text-center">Dispositivos</th>
            <th class="text-center">Estado</th>
            <th>Última sincronización</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@include('produccion.workshops.modal')
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/workshops/workshops.js') }}"></script>
@stop
