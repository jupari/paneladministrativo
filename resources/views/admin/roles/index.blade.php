@extends('adminlte::page')

@section('title', 'Roles')

@section('plugin.Datatables')

@section('plugin.Toastr')

@section('plugin.Sweetalert2')


@section('content')
    <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Roles</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de Roles</li>
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
                                Administrar Rol
                            </h3>
                        </div>

                        <div class="card-body">
                            @can('roles.create')
                                <div class="col-md-1">
                                    <button type="button" onclick="regRol()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear rol">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            @endcan

                            <div class="table-responsive my-3">
                            <table id="rol-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Rol</th>
                                        <th>Permisos</th>
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

@include('admin.roles.modal')


@push('css')
   {{-- <link rel="stylesheet" href="{{asset('assets/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}"> --}}
@endpush

@push('js')
   <script src="{{asset('assets/js/roles/roles.js') }}" type="text/javascript"></script>
@endpush
