@extends('adminlte::page')

@section('title', 'Dashboard')

@section('plugin.Datatables')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">

        <div class="row">
            @foreach ($resultados as $item)
            <div class="col-lg-3 col-6">
                <div class="small-box bg-{{$item->color}}">
                    <div class="inner">
                        <h3>{{$item->cantidad}}</h3>
                        <p>{{$item->estado}}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    {{-- <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a> --}}
                </div>
            </div>
            @endforeach

            {{-- <div class="col-lg-3 col-6">

                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>53<sup style="font-size: 20px">%</sup></h3>
                        <p>Bounce Rate</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>44</h3>
                        <p>User Registrations</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>65</h3>
                        <p>Unique Visitors</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div> --}}

        </div>

        <div class="col-md-12">
            <div class="card">
                {{-- <div class="card-header border-transparent">
                    <h3 class="card-title">Indicadores</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                {{-- <div class="card-body my-3">
                    <div class="table-responsive">
                        <table class="table m-0" id="table-res">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cuenta</th>
                                    <th>Usuario</th>
                                    <th>Fecha de Asignación</th>
                                    <th>Tiempo trasncurrido</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resCuentas as $item)
                                    <tr>
                                        <td>{{ ($loop->index)+1 }}</td>
                                        <td>{{ $item->nombre_cuenta }}</td>
                                        <td>{{ $item->usuario->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->fecha_asig)->format('d/m/Y') }}</td>
                                        <td>{{ 30-(intval($item->tiempo)==0?0:intval($item->tiempo)) }} de 30 día(s)</td>
                                        <td><span class="badge badge-{{ $item->estado->color }}">{{ $item->estado->estado }}</span></td>
                                        <td>
                                            @if($item->estado->estado=='Asignadas' && !$userAdmin)
                                                <button type="button" onclick="consultarCorreosCodigoAcceso('{{ $item->nombre_cuenta }}')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Ver código de acceso temporal"><i class="fas fa-envelope"></i></button>
                                                <button type="button" onclick="consultarCorreosReestablecimiento('{{ $item->nombre_cuenta }}')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Ver restablecimiento de contraseña"><i class="fas fa-key"></i></button>
                                                <button type="button" onclick="upCuenta('{{ $item->id }}')" class="btn btn-secondary btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar el password del usuario"><i class="fas fa-pencil-alt"></i></button>
                                            @endif
                                            @if($userAdmin)
                                                <button type="button" onclick="consultarCorreosCodigoAcceso('{{ $item->nombre_cuenta }}')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Ver código de acceso temporal"><i class="fas fa-envelope"></i></button>
                                                <button type="button" onclick="consultarCorreosReestablecimiento('{{ $item->nombre_cuenta }}')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Ver restablecimiento de contraseña"><i class="fas fa-key"></i></button>
                                                <button type="button" onclick="upCuenta('{{ $item->id }}')" class="btn btn-secondary btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar el password del usuario"><i class="fas fa-pencil-alt"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                              </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <a href="javascript:void(0)" class="btn btn-sm btn-info float-left">Place New Order</a>
                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right">View All Orders</a>
                </div> --}}

            </div>
        </div>

    </div>
@stop

@include('admin.cuentaabonado.modal-cambiopass')
@include('admin.emailreader.email')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <style>
        .email {
            background: #fff;
            margin: 5px 0;
            padding: 5px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .email h2 {
            margin: 0 0 5px;
        }
        .email p {
            margin: 5px 0;
        }
        .email .info {
            color: #555;
            font-size: 0.9em;
        }
        .email .attachments {
            color: #d9534f;
        }
    </style>
@stop

{{-- @section('js')
    <script>
        $(function(){
            $('#table-res').DataTable({
                "language": {
                    "url": "/assets/js/spanish.json"
                },
                paging: false,
                pageLength: 8,
                lengthMenu: [[2, 4, 6, 8, 10, -1], [2, 4, 6, 8, 10, "Todo(s)"]],
        });
        })
    </script>
@stop --}}

@section('js')
        <script src="{{asset('assets/js/dashboard/dashboard.js') }}" type="text/javascript"></script>
@stop
