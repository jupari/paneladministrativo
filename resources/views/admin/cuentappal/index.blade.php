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
                <h1 class="m-0">Cuentas Principales</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Lista de Cuentas Principales</li>
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
                                Administrar Cuentas Principales
                            </h3>
                        </div>

                        <div class="card-body">
                            @can('cuentappal.create')
                                <div class="col-md-1">
                                    <button type="button" onclick="regCuentaPpal()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Cuenta principal">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                </div>
                            @endcan
                            <div>
                                <p class="form-label px-3 my-0 font-weight-bold">Subir cuentas de correo por medio de archivo de Excel</p>
                                <p class="px-3">Campos del archivo: | email | password | código: puede ir en blanco | cuenta_asociada ej: cuentaasociada@gmail.com | usuario distribuidor ej: #cedula | clientId | tenant_id | clientSecret | cuenta principal (Madre)</p>
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
                            <table id="cppal-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Id</th>
                                        <th>Código</th>
                                        <th>Correo</th>
                                        <th>Password</th>
                                        <th>Cuenta Asociada</th>
                                        <th>Usuario temporal</th>
                                        <th>Es Cta ppal?</th>
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

@include('admin.cuentappal.modal')

@include('admin.cuentappal.modal-loadfile')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')

<script src="{{asset('assets/js/cuentappal/cuentappal.js') }}" type="text/javascript"></script>
@stop
