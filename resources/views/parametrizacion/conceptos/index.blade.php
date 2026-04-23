@extends('adminlte::page')

@section('title', 'Conceptos')

@section('plugin.Datatables')
@section('plugin.Sweetalert2')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Conceptos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Lista de Conceptos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Administrar Conceptos</h3>
                    </div>
                    <div class="card-body">
                        @can('conceptos.create')
                            <div class="col-md-1 mb-3">
                                <button type="button" onclick="regConcepto()" class="btn btn-primary btn-block" data-toggle="tooltip" title="Crear Concepto">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                        @endcan

                        <div class="table-responsive">
                            <table id="conceptos-table" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>% Defecto</th>
                                        <th>Estado</th>
                                        <th class="exclude">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@stop

@include('parametrizacion.conceptos.modal')

@section('js')
<script src="{{ asset('assets/js/parametrizacion/conceptos/conceptos.js') }}"></script>
@stop
