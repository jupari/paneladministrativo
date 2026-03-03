@extends('adminlte::page')

@section('title', 'Costos Operativos')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
    $breadcrumbs = [
        ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
        ['title' => 'Reportes', 'icon' => 'fas fa-chart-bar', 'url' => null],
    ];
    $currentTitle = 'Costos Operativos (Periodo)';
    $currentIcon = 'fas fa-coins';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
    <div class="card-header">
        <h4>Reporte ejecutivo de costos operativos</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label>Desde</label>
                <input type="date" id="filter_from" class="form-control">
            </div>
            <div class="col-md-3">
                <label>Hasta</label>
                <input type="date" id="filter_to" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-block" onclick="applyCostFilters()">
                    <i class="fas fa-search"></i> Consultar
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Costo Mano de Obra</span>
                        <span class="info-box-number" id="sum_labor_cost">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-trash-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Costo de Rechazo</span>
                        <span class="info-box-number" id="sum_rejected_cost">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Costo Unitario Real</span>
                        <span class="info-box-number" id="sum_unit_cost">0,00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 id="sum_qty">0,00</h3>
                        <p>Total Producido</p>
                    </div>
                    <div class="icon"><i class="fas fa-cubes"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="sum_accepted">0,00</h3>
                        <p>Total Aceptado</p>
                    </div>
                    <div class="icon"><i class="fas fa-check"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="sum_orders">0</h3>
                        <p>Órdenes</p>
                    </div>
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 id="sum_operations">0</h3>
                        <p>Operaciones</p>
                    </div>
                    <div class="icon"><i class="fas fa-cogs"></i></div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-2">
            <table id="operating-costs-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="exclude">id orden</th>
                        <th>Orden</th>
                        <th>Producto</th>
                        <th>Operación</th>
                        <th class="text-right">Producido</th>
                        <th class="text-right">Aceptado</th>
                        <th class="text-right">Rechazado</th>
                        <th class="text-right">Tarifa Prom.</th>
                        <th class="text-right">Costo Mano Obra</th>
                        <th class="text-right">Costo Rechazo</th>
                        <th class="text-right">Costo Unitario</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/reports/operating_costs_period.js') }}"></script>
@stop

