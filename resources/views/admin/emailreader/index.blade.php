@extends('adminlte::page')

@section('title', 'Cuentas de correo')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')
@section('plugin.Select2')

{{-- @section('content_header')
    <h1>Listado de Cuentas Principales</h1>
@stop --}}

@section('content')

<div class="content-header">

    </div>
    <section class="content">
    <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Consulta cuentas..
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="cuenatamadre-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Password</th>
                                        <th>Usuario Reg</th>
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

@include('admin.emailreader.email')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <style>
        .email {
            background: #fff;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .email h2 {
            margin: 0 0 10px;
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

@section('js')
        <script src="{{asset('assets/js/email/email.js') }}" type="text/javascript"></script>
@stop
