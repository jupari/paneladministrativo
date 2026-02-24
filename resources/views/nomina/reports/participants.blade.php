@extends('adminlte::page')

@section('title', 'Reporte Nómina')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')

@php
    $breadcrumbs = [
        ['title' => 'Recursos Humanos', 'icon' => 'fas fa-users', 'url' => null],
        ['title' => 'Nómina', 'icon' => 'fas fa-file-invoice-dollar', 'url' => null],
    ];
    $currentTitle = 'Reporte de Participantes';
    $currentIcon = 'fas fa-chart-line';
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

<div class="card">
    <div class="card-header">
        <h4>Reporte de nómina (participantes)</h4>
    </div>

    <div class="card-body">

        {{-- Filtros --}}
        <div class="row">
            <div class="col-md-4">
                <label>Periodo (PayRun)</label>
                <select id="filter_pay_run_id" class="form-control">
                    <option value="">-- Seleccione --</option>
                </select>
                <small class="text-muted">Si seleccionas un PayRun, se ignoran las fechas.</small>
            </div>

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

        {{-- Resumen --}}
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-plus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Devengado</span>
                        <span class="info-box-number" id="sum_devengado">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-minus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deducciones</span>
                        <span class="info-box-number" id="sum_deducciones">0,00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-hand-holding-usd"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total a pagar (Neto)</span>
                        <span class="info-box-number" id="sum_neto">0,00</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="table-responsive mt-2">
            <table id="report-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="exclude">PayRun</th>
                        <th>Tipo</th>
                        <th>Periodo</th>
                        <th>Fecha pago</th>
                        <th>Vínculo</th>
                        <th>Participante</th>
                        <th class="text-right">Devengado</th>
                        <th class="text-right">Deducciones</th>
                        <th class="text-right">Neto</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

@stop

@include('nomina.reports.modal')

@section('js')
    <script src="{{ asset('assets/js/nomina/reports/participants_report.js') }}" type="text/javascript"></script>
@stop
