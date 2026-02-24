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
        <div class="card-header">
            <h4>Novedades</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                    <a href="{{ route('admin.novedad.create') }}" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Novedad">
                        <i class="fas fa-user-plus"></i>
                    </a>
                </div>
            @endif
            <div class="col-md-12 my-3">
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
