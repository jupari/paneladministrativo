@extends('adminlte::page')

@section('title', 'Novedades')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')
    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Parametrización',
                'icon' => 'fas fa-cog',
                'url' => null
            ]
        ];
        $currentTitle = 'Novedades';
        $currentIcon = 'fas fa-tags';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <div class="mb-2 mb-md-0">
                <h4 class="mb-0">Novedades</h4>
                <small class="text-muted">Crea y gestiona novedades para mantener la información al día.</small>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="d-flex my-3 justify-content-lg-start">
                    <div class="col-3">
                        @if(auth()->user()->can('novedad.cotizacion.create'))
                            <a href="{{ route('admin.novedad.create') }}" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Crear novedad">
                                <i class="fas fa-plus mr-1"></i>Crear novedad
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-7 col-sm-12 mb-3 mb-lg-0">
                    <div class="alert alert-info mb-0" role="alert">
                        <div class="d-flex align-items-start">
                            <span class="badge badge-primary mr-2">Tip</span>
                            <div>
                                <strong>Crear novedades.</strong>
                                <div class="text-muted small">Usa el botón "Nueva novedad", completa el nombre, activa el estado y agrega detalles desde el modal.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-12">
                    <div class="d-flex justify-content-lg-end align-items-center">
                        <label for="estado-filter" class="mb-0 mr-2 text-muted">Filtrar estado:</label>
                        <select id="estado-filter" class="form-control w-auto">
                            <option value="">Todos</option>
                            <option value="Activo">Activos</option>
                            <option value="Inactivo">Inactivos</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="novedades-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>id</th>
                            <th>Nombre</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@include('contratos.novedades.modal')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/contratos/novedades/novedades.js') }}" type="text/javascript"></script>
@stop
