@extends('adminlte::page')

@section('title', 'Liquidación Destajo')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
$breadcrumbs=[['title'=>'Producción','icon'=>'fas fa-industry','url'=>null]];
$currentTitle='Liquidación';
$currentIcon='fas fa-money-check-alt';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
  <div class="card-header"><h4>Liquidación de Destajo</h4></div>
  <div class="card-body">

    <div class="row">
      <div class="col-md-6">
        <label>Orden</label>
        <select id="filter_order_id" class="form-control"></select>
      </div>
      <div class="col-md-3">
        <label>Periodo Nómina Desde</label>
        <input type="date" id="period_start" class="form-control">
      </div>
      <div class="col-md-3">
        <label>Periodo Nómina Hasta</label>
        <input type="date" id="period_end" class="form-control">
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-md-2">
        <button class="btn btn-primary btn-block" onclick="applySettlementFilters()">
          <i class="fas fa-search"></i> Consultar
        </button>
      </div>
      <div class="col-md-3">
        <button class="btn btn-warning btn-block" onclick="calculateSettlement()">
          <i class="fas fa-calculator"></i> Calcular
        </button>
      </div>
      <div class="col-md-3">
        <button class="btn btn-success btn-block" onclick="sendToNomina()">
          <i class="fas fa-paper-plane"></i> Enviar a Nómina
        </button>
      </div>

      <div class="col-md-4 text-right d-flex align-items-center justify-content-end">
        <div class="mr-3"><b>Total Qty:</b> <span id="sum_qty">0,00</span></div>
        <div><b>Total a pagar:</b> <span id="sum_total">0,00</span></div>
      </div>
    </div>

    <div class="table-responsive mt-3">
      <table id="settlements-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Orden</th>
            <th>Producto</th>
            <th>Operación</th>
            <th>Empleado</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Tarifa</th>
            <th class="text-right">Pago</th>
            <th class="text-center">Estado</th>
            <th>Actualizado</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/settlements/settlements.js') }}"></script>
@stop
