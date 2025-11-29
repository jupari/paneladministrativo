@extends('adminlte::page')

@section('title', 'Permisos')

@section('plugin.Datatables')

@section('plugin.Toastr')

@section('plugin.Sweetalert2')

@section('content')
    <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Permisos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de Permisos</li>
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
                                Administrar Permisos
                            </h3>
                        </div>

                        <div class="card-body">
                            @can('permisos.create')
                            <div class="col-md-1">
                                <button type="button" onclick="regPerm()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear permiso">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                            @endcan
                            <div class="table-responsive my-3">
                            <table id="perm-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Permiso</th>
                                        <th>Descripción</th>
                                        <th>Guard</th>
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

@include('admin.permission.modal')


@push('css')
   {{-- <link rel="stylesheet" href="{{asset('assets/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}"> --}}
@endpush

@push('js')
   <script src="{{asset('assets/js/permisos/permisos.js') }}" type="text/javascript"></script>
@endpush
