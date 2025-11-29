@extends('adminlte::page')

@section('title', 'Usuarios')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')
@section('plugin.Select2')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Cuentas</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de Cuentas</li>
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
                                Administrar Cuentas
                            </h3>
                        </div>

                        <div class="card-body">
                            {{-- @can('cuentappal.create')
                                <div class="col-md-1">
                                    <button type="button" onclick="regCuenta()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear rol">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            @endcan --}}
                            {{-- subir archivos de excel --}}
                            <div>
                                <p class="form-label px-3 my-0 font-weight-bold">Subir cuentas de correo por medio de archivo de Excel</p>
                                <p class="px-3 mt-1">Campos del archivo de excel:</p>
                                <p class="px-3 my-1">| usuario | email | password_cuenta | fecha asignación: (dd/mm/aaaa) la celda de excel debe ser tipo texto | estado ( Asignadas,Disponibles,Deshabilitada,Exoneradas,Recuperadas,Vencidas ) |</p>
                            </div>
                            <div class="excel-import-group d-flex px-3">
                                <div class="col-12 col-sm-3">
                                    <label for="formFileSm" class="form-label">Seleccione un archivo</label>
                                    <div class="custom-file custom-file-sm">
                                        <input type="file" class="custom-file-input custom-file-input-sm" name="files" id="fileexcel" accept="*.xlsx">
                                        <label class="custom-file-label custom-file-label-sm" for="fileexcel" data-browse="Adjuntar">
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-2 mt-auto">
                                    <button type="button" class="btn btn-success text-nowrap" id="btnImportarExcel"
                                        style="width:100%;white;" onclick="saveFileExcel()"  data-toggle="tooltip" data-placement="right" title="| DocumentoEstudiante | SNIES | estado: En Revision |">
                                        <div class="d-flex justify-content-center">
                                            <p class="my-0">Verificar archivo</p>
                                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerImportar"></span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive my-3">
                            <table id="cuenta-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>id</th>
                                        <th>Nombre de usuario</th>
                                        <th>Correo</th>
                                        <th>Password</th>
                                        <th>Fecha de Asignación</th>
                                        <th>Tiempo trasncurrido</th>
                                        <th>Estado</th>
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

@include('admin.cuentaabonado.modal')

@include('admin.cuentaabonado.modal-loadfile')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <style>

        .select2-container--open {
            z-index: 9999 !important;
        }
    </style>
@stop

@section('js')
<script src="{{asset('assets/js/cuentaabonado/cuentaabonado.js') }}" type="text/javascript"></script>
@stop
