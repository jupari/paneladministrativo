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
        <div class="card-header">
            <h4>Ciudades</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <button type="button" onclick="regCiudad()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Ciudad">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="ciudades-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>id</th>
                           <th>País</th>
                           <th>Departamento</th>
                           <th>Ciudad</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop

@include('terceros.ciudades.modal')
@include('terceros.ciudades.modal_pais_dpto')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const dataPaises = @json($paises)
    </script>
    <script src="{{asset('assets/js/terceros/ciudades/ciudades.js') }}" type="text/javascript"></script>
@stop
