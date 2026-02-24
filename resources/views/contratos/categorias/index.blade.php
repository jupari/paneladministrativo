@extends('adminlte::page')

@section('title', 'Categorias')

@section('plugins.jQueryLib', true)
@section('plugins.BootstrapBundle', true)

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
        $currentTitle = 'Categorías';
        $currentIcon = 'fas fa-tags';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Categorias</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <button type="button" onclick="regCargo()" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Categorias">
                    <i class="fas fa-user-plus"></i>
                </button>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="categorias-table" class="table table-bordered table-striped">
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

{{-- Incluir el modal de categorías --}}
@include('contratos.categorias.modal')


@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    {{-- JavaScript específico de la página aquí --}}
    <script src="{{asset('assets/js/contratos/categorias/categorias.js') }}" type="text/javascript"></script>

@stop
