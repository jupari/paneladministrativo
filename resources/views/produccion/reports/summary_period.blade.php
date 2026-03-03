@extends('adminlte::page')

@section('title', 'Resumen Producción')
@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')
@php
    $breadcrumbs = [
        ['title' => 'Producción', 'icon' => 'fas fa-industry', 'url' => null],
        ['title' => 'Reportes', 'icon' => 'fas fa-chart-bar', 'url' => null],
    ];
    $currentTitle = 'Resumen Producción (Periodo)';
    $currentIcon = 'fas fa-chart-pie';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
    <div class="card-header">
        <h4>Resumen ejecutivo de producción por periodo</h4>
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
                <button class="btn btn-primary btn-block" onclick="applyReportFilters()">
                    <i class="fas fa-search"></i> Consultar
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Producido</span>
                        <span class="info-box-number" id="sum_qty">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Aceptado</span>
                        <span class="info-box-number" id="sum_accepted">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Rechazado</span>
                        <span class="info-box-number" id="sum_rejected">0,00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="sum_reject_rate">0,00%</h3>
                        <p>% Rechazo Global</p>
                    </div>
                    <div class="icon"><i class="fas fa-percentage"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 id="sum_orders">0</h3>
                        <p>Órdenes con producción</p>
                    </div>
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="sum_workers">0</h3>
                        <p>Operarios con registro</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-2">
            <table id="prod-summary-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="exclude">id orden</th>
                        <th>Orden</th>
                        <th>Producto</th>
                        <th class="text-right">Objetivo</th>
                        <th class="text-right">Producido</th>
                        <th class="text-right">Aceptado</th>
                        <th class="text-right">Rechazado</th>
                        <th class="text-right">% Rechazo</th>
                        <th class="text-right">% Cumplimiento</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/js/produccion/reports/summary_period.js') }}"></script>
@stop

