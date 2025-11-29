@extends('adminlte::page')

@section('title', 'Usuarios')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')
@section('plugin.Select2')

{{-- @section('content_header')
    <h1>Listado de Cuentas Principales</h1>
@stop --}}

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Estados</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de estados</li>
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
                            <h3 class="card-title">
                                Administrar estados
                            </h3>
                        </div>

                        <div class="card-body">
                            @can('estados.create')
                                <div class="col-md-1">
                                    <button type="button" onclick="regEstado()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Estado">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            @endcan

                            <div class="table-responsive my-3">
                            <table id="estado-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Estado</th>
                                        <th>Descripción</th>
                                        <th>Color fondo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>
@stop

@include('admin.estados.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
<script src="{{asset('assets/js/estados/estados.js') }}" type="text/javascript"></script>
@stop
