@extends('adminlte::page')

@section('title', 'Novedades de Nómina')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')
@section('plugin.Select2')

@section('content')
@php
  $breadcrumbs = [
    ['title' => 'Gestión de Personal', 'icon' => 'fas fa-user-friends', 'url' => null],
    ['title' => 'Nómina', 'icon' => 'fas fa-money-check-alt', 'url' => null],
  ];
  $currentTitle = 'Novedades de Nómina';
  $currentIcon = 'fas fa-exclamation-triangle';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Novedades</h4>

    <div>
      <button class="btn btn-outline-primary" onclick="openRecalcDestajoModal()">
        <i class="fas fa-sync-alt"></i> Recalcular Destajo
      </button>

      <button class="btn btn-primary" onclick="regNovelty()">
        <i class="fas fa-plus"></i> Nueva novedad
      </button>
    </div>
  </div>

  <div class="card-body">
    {{-- Filtros --}}
    <div class="row">
      <div class="col-md-3">
        <label>Periodo desde</label>
        <input type="date" id="filter_start" class="form-control">
      </div>
      <div class="col-md-3">
        <label>Periodo hasta</label>
        <input type="date" id="filter_end" class="form-control">
      </div>
      <div class="col-md-3">
        <label>Estado</label>
        <select id="filter_status" class="form-control">
          <option value="">Todos</option>
          <option value="PENDING">PENDING</option>
          <option value="APPLIED">APPLIED</option>
          <option value="CANCELLED">CANCELLED</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-success btn-block" onclick="reloadNovelties()">
          <i class="fas fa-filter"></i> Filtrar
        </button>
      </div>
    </div>

    <hr>

    <div class="table-responsive">
      <table id="novelties-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th class="exclude">id</th>
            <th>Participante</th>
            <th>Tipo</th>
            <th>Concepto</th>
            <th class="text-right">Cantidad</th>
            <th class="text-right">Valor</th>
            <th>Periodo</th>
            <th class="text-center">Estado</th>
            <th>Creación</th>
            <th class="exclude text-center">Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@include('nomina.novedades.modal')
@include('nomina.novedades.modal_recalc_destajo')
@include('nomina.novedades.modal_duplicate')

@stop

@section('js')
<script src="{{ asset('assets/js/nomina/novedades/novedades.js') }}"></script>
@stop
