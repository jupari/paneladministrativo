@extends('adminlte::page')

@section('title', 'Sucursales')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')
@section('plugin.Select2')

@section('content')
@php
  $breadcrumbs = [['title' => 'Organización', 'icon' => 'fas fa-sitemap', 'url' => null]];
  $currentTitle = 'Sucursales';
  $currentIcon = 'fas fa-store-alt';
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Sucursales</h4>

  </div>
  <div class="my-3 mx-3 d-flex justify-content-lg-start">
     @if(auth()->user()->can('branches.create'))
        <button class="btn btn-primary" onclick="regBranch()"><i class="fas fa-plus"></i> Nueva</button>
    @endif
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="branches-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Ciudad</th>
            <th>Teléfono</th>
            <th class="text-center">Estado</th>
            <th>Creación</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@include('organizacion.branches.modal')

@stop

@section('js')
<script src="{{ asset('assets/js/organizacion/branches/branches.js') }}"></script>
@stop
