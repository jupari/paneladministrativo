@extends('adminlte::page')

@section('title', 'Novedades')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')


@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-start justify-content-between flex-wrap">
            <div>
                <h4 class="mb-1">Editar Novedad</h4>
                <small class="text-muted">Ajusta nombre, estado y detalles; los cambios se aplican al guardar.</small>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.novedad.index') }}" class="btn btn-outline-secondary btn-sm mr-2">Volver</a>
                <button type="button" class="btn btn-success btn-sm" onclick="updateData()">
                    <i class="fas fa-save mr-1"></i> Guardar cambios
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formNovedad">
                @csrf
                <input type="hidden" name="id" id="id" value="{{ $novedad->id }}">
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="form-group">
                            <label for="nombre" class="font-weight-semibold">Nombre de la novedad</label>
                            <input type="text" name="nombre" class="form-control" id="nombre" value="{{ $novedad->nombre }}" maxlength="150" placeholder="Ej. Cambio de horario especial">
                            <small class="text-muted">Mantén una descripción clara para identificarla rápido.</small>
                            <span class="text-danger error-nombre"></span>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" id="active" name="active" {{ $novedad->active==1?'checked': '' }}>
                                <label class="custom-control-label" for="active">Activo</label>
                            </div>
                            <small class="text-muted">Desactiva si no quieres que se muestre en listados.</small>
                            <span class="text-danger error_active"></span>
                        </div>

                        <div class="d-flex align-items-center mb-2">
                            <h5 class="mb-0 mr-2">Detalles</h5>
                            <span class="badge badge-light">Opcional</span>
                        </div>
                        <p class="text-muted">Actualiza o agrega líneas con valores administrativos y operativos para desglosar la novedad.</p>

                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" onclick="openDetalleModal()">
                                <i class="fas fa-plus mr-1"></i> Agregar detalle
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="tabla-detalles" class="table table-bordered mb-2">
                                <thead>
                                    <tr>
                                        <th style="width: 60px">#</th>
                                        <th>Nombre</th>
                                        <th style="width: 140px">Valor admon</th>
                                        <th style="width: 140px">Valor operativo</th>
                                        <th style="width: 120px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <small class="form-text text-muted">Los cambios en detalles se guardan al confirmar.</small>
                        </div>
                        <span class="text-danger error-detalles"></span>

                        <div class="row mt-3">
                            <div class="col-md-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="auto_totales" checked>
                                    <label class="custom-control-label" for="auto_totales">Calcular totales automático</label>
                                </div>
                                <small class="text-muted">Suma los valores administrativos y operativos de todos los detalles.</small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="total_valor_admon" class="mb-1">Total valor admon</label>
                                <input type="number" step="0.01" class="form-control" id="total_valor_admon" name="total_valor_admon" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="total_valor_operativo" class="mb-1">Total valor operativo</label>
                                <input type="number" step="0.01" class="form-control" id="total_valor_operativo" name="total_valor_operativo" readonly>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-secondary mr-2" onclick="window.location='{{ route('admin.novedad.index') }}'">Volver</button>
                            {{-- <button type="button" class="btn btn-success" onclick="updateData()"><i class="fas fa-save mr-1"></i> Guardar cambios</button> --}}
                        </div>
                    </div>

                    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                        <div class="border rounded p-3 bg-light h-100">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge badge-primary mr-2">Tip</span>
                                <strong class="mb-0">Edición segura</strong>
                            </div>
                            <ul class="pl-3 mb-0 text-muted">
                                <li>Confirma que el nombre refleje el cambio real.</li>
                                <li>Si la novedad ya no aplica, desactívala.</li>
                                <li>Usa detalles para registrar ajustes específicos.</li>
                                <li>Revisa antes de guardar para evitar retrabajos.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop


@include('contratos.novedades.modal')

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        const novedad =  @json($novedad);
    </script>
    <script src="{{asset('assets/js/contratos/novedades/edit.js') }}" type="text/javascript"></script>
@stop
