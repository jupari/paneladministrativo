@extends('adminlte::page')

@section('title', 'Cotizaciones')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    {{-- Breadcrumbs --}}

        {{-- Breadcrumbs mejorados usando componente --}}
    @php
        $breadcrumbs = [
            [
                'title' => 'Cotizar',
                'icon' => 'fas fa-file-invoice',
                'url' => null
            ]
        ];
        $currentTitle = 'Solicitud de aprobación';
        $currentIcon = 'fas fa-shield-alt';
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Solicitud de aprobación</h4>
        </div>
        <div class="card-body" >
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="cotizaciones-aprobacion-table" class="table table-bordered table-striped">
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
                           <th class="text-center">Autorización</th>
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
    <style>
        .badge.d-flex {
            display: inline-flex !important;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-success {
            background-color: #28a745 !important;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107 !important;
            color: #212529;
        }

        .badge i {
            font-size: 10px;
        }

        .badge-success i {
            color: #d4edda;
        }

        .badge-warning i {
            color: #856404;
        }
    </style>
@stop

@section('js')
    <script src="{{asset('assets/js/cotizar/cotizacion.js') }}" type="text/javascript"></script>
@stop
