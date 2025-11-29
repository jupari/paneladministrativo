@extends('adminlte::page')

@section('title', 'Clientes')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Cotizaciones</h4>
        </div>
        <div class="card-body" >
            @if(auth()->user()->hasRole('Administrator'))
                <div class="col-md-1">
                <a type="button"  href="{{ route('admin.cotizar.create') }}" class="btn btn-primary btn-block mb-1" data-toggle="tooltip" data-placement="top" title="Crear Cotización">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                </div>
            @endif
            <div class="col-md-12 my-3">
              <div class="table-responsive">
                  <table id="cargos-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                           <th>#</th>
                           <th>Número</th>
                           <th>Cliente</th>
                           <th>Fecha creación</th>
                           <th class="text-center">Estado</th>
                           <th>Acciones</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                            <td>1</td>
                            <td>0001</td>
                            <td>Cliente de prueba</td>
                            <td>09-06-2025</td>
                            <td><span class="badge bg-success">Activo</span></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>0002</td>
                            <td>Cliente de prueba2</td>
                            <td>10-06-2025</td>
                            <td><span class="badge bg-danger">Inactivo</span></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                     </tbody>
                  </table>
              </div>
            </div>
        </div>
    </div>
@stop


@include('contratos.cargos.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="{{asset('assets/js/cotizr/cotizacion.js') }}" type="text/javascript"></script>
@stop
