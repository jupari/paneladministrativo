@extends('adminlte::page')

@section('title', 'Centros de Costo')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')
@section('plugin.Select2')

@section('content')
@php
  $breadcrumbs = [['title' => 'Organización', 'icon' => 'fas fa-sitemap', 'url' => null]];
  $currentTitle = 'Centros de Costo';
  $currentIcon = 'fas fa-network-wired';
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Centros de Costo</h4>
  </div>
  <div class="my-3 mx-3 d-flex justify-content-lg-start">
    @if(auth()->user()->can('centro_costo.create'))
        <button class="btn btn-primary" onclick="regCostCenter()"><i class="fas fa-plus"></i> Nuevo</button>
    @endif
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="cc-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Padre</th>
            <th class="text-center">Estado</th>
            <th>Creación</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@include('organizacion.cost_centers.modal')

@stop

@section('css')
<style>
/* Select2 (single) estilo Bootstrap/AdminLTE */
.select2-container--default .select2-selection--single{
  height: calc(2.25rem + 2px) !important; /* Bootstrap4 input height */
  padding: .375rem .75rem !important;
  border: 1px solid #ced4da !important;
  border-radius: .25rem !important;
  display: flex !important;
  align-items: center !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered{
  line-height: 1.5 !important;
  padding-left: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow{
  height: calc(2.25rem + 2px) !important;
  top: 0 !important;
  right: .25rem !important;
}
.select2-container{
  width: 100% !important;
}
.select2-dropdown{
  z-index: 2050; /* para que no quede detrás del modal */
}
</style>
@stop

@section('js')
<script src="{{ asset('assets/js/organizacion/cost_centers/cost_centers.js') }}"></script>
@stop
