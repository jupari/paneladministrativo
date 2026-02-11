@extends('adminlte::page')

@section('title', 'Cotizaciones')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Cotizar',
                'icon' => 'fas fa-file-invoice',
                'url' => null
            ]
        ];
        $currentTitle = 'Lista de Cotizaciones';
        $currentIcon = 'fas fa-list';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Cotizaciones</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole(['Administrator']) || auth()->user()->can('cotizaciones.create'))
                <div class="col-md-1">
                <a type="button"  href="{{ route('admin.cotizaciones.create') }}" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Cotización">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="cotizaciones-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>id</th>
                           <th>Documento</th>
                           <th>Cliente</th>
                            <th>Sede</th>
                           <th>Proyecto</th>
                           <th>Fecha creación</th>
                           <th class="text-center">Estado</th>
                           <th>Total</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/cotizar/cotizacion.js') }}" type="text/javascript"></script>
@stop
