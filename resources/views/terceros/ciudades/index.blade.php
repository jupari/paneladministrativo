@extends('adminlte::page')

@section('title', 'Ciudades')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Configuración',
                'icon' => 'fas fa-cog',
                'url' => null
            ]
        ];
        $currentTitle = 'Ciudades';
        $currentIcon = 'fas fa-city';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="ubicacionesTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-ciudades-link" data-toggle="pill" href="#tab-ciudades" role="tab">
                        <i class="fas fa-city"></i> Ciudades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-departamentos-link" data-toggle="pill" href="#tab-departamentos" role="tab">
                        <i class="fas fa-map"></i> Departamentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-paises-link" data-toggle="pill" href="#tab-paises" role="tab">
                        <i class="fas fa-globe-americas"></i> Países
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">

                {{-- Ciudades --}}
                <div class="tab-pane fade show active" id="tab-ciudades" role="tabpanel">
                    @can('ciudades.create')
                        <div class="col-md-2 px-0 mb-2">
                            <button type="button" onclick="regCiudad()" class="btn btn-primary btn-block" data-toggle="tooltip" title="Crear Ciudad">
                                <i class="fas fa-plus"></i> Nueva Ciudad
                            </button>
                        </div>
                    @endcan
                    <div class="table-responsive">
                        <table id="ciudades-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>id</th>
                                    <th>País</th>
                                    <th>Departamento</th>
                                    <th>Ciudad</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                {{-- Departamentos --}}
                <div class="tab-pane fade" id="tab-departamentos" role="tabpanel">
                    @can('ciudades.create')
                        <div class="col-md-2 px-0 mb-2">
                            <button type="button" onclick="regDepartamento()" class="btn btn-primary btn-block" data-toggle="tooltip" title="Crear Departamento">
                                <i class="fas fa-plus"></i> Nuevo Departamento
                            </button>
                        </div>
                    @endcan
                    <div class="table-responsive">
                        <table id="departamentos-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>id</th>
                                    <th>País</th>
                                    <th>Departamento</th>
                                    <th>Ciudades</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                {{-- Países --}}
                <div class="tab-pane fade" id="tab-paises" role="tabpanel">
                    @can('ciudades.create')
                        <div class="col-md-2 px-0 mb-2">
                            <button type="button" onclick="regPais()" class="btn btn-primary btn-block" data-toggle="tooltip" title="Crear País">
                                <i class="fas fa-plus"></i> Nuevo País
                            </button>
                        </div>
                    @endcan
                    <div class="table-responsive">
                        <table id="paises-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>id</th>
                                    <th>País</th>
                                    <th>Departamentos</th>
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
@stop

@include('terceros.ciudades.modal')
@include('terceros.ciudades.modal_departamento')
@include('terceros.ciudades.modal_pais')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        let dataPaises = @json($paises)
    </script>
    <script src="{{asset('assets/js/Terceros/ciudades/ciudades.js') }}" type="text/javascript"></script>
@stop
