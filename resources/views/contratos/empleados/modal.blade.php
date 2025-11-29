<!-- Modal -->
<div class="modal fade" id="ModalEmpleado" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Empleado</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">
                    <input type="hidden" id="user_id" value="{{ $user_id }}">
                    <!-- Agrupación: Información Básica -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Básico</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_contrato">Tipos de Contrato</label>
                                    <select id="tipo_contrato" class="form-control" onchange="actualizarValidaciones()">
                                        <option value="" selected>Seleccionar</option>
                                        @foreach($tiposContratos as $tipoContrato)
                                            <option value="{{ $tipoContrato->codigo }}">{{ $tipoContrato->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_tipo_contrato_id"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombres">Nombres</label>
                                    <input type="text" id="nombres" class="form-control" placeholder="Ingrese los nombres">
                                    <span class="text-danger" id="error_nombres"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" id="apellidos" class="form-control" placeholder="Ingrese los apellidos">
                                    <span class="text-danger" id="error_apellidos"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Identificación -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Identificación</legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo_identificacion_id">Tipo de identificación</label>
                                    <select id="tipo_identificacion_id" class="form-control">
                                        <option value="" selected>Seleccione...</option>
                                        @foreach($tiposIdentificaciones as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_tipo_identificacion_id"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="identificacion">Identificación</label>
                                    <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación">
                                    <span class="text-danger" id="error_identificacion"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="expedida_en">Expedida en</label>
                                    <input type="text" id="expedida_en" class="form-control" placeholder="Lugar de expedición">
                                    <span class="text-danger" id="error_expedida_en"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Fechas -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Fechas</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                    <input type="date" id="fecha_nacimiento" class="form-control">
                                    <span class="text-danger" id="error_fecha_nacimiento"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio_labor">Fecha de Inicio Laboral</label>
                                    <input type="date" id="fecha_inicio_labor" class="form-control">
                                    <span class="text-danger" id="error_fecha_inicio_labor"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Información de Trabajo -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información de Trabajo</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" class="form-control" placeholder="Ingrese la dirección">
                                    <span class="text-danger" id="error_direccion"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ciudad_residencia">Ciudad de residencia</label>
                                    <input type="text" id="ciudad_residencia" class="form-control" placeholder="Ingrese la ciudad">
                                    <span class="text-danger" id="error_ciudad_residencia"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" id="telefono" class="form-control" placeholder="Ingrese la teléfono">
                                    <span class="text-danger" id="error_telefono"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" id="celular" class="form-control" placeholder="Ingrese la celular">
                                    <span class="text-danger" id="error_celular"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico</label>
                                    <input type="text" id="correo" class="form-control" placeholder="Ingrese la correo">
                                    <span class="text-danger" id="error_celular"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cargo_id">Cargo</label>
                                    <select id="cargo_id" class="form-control">
                                        <option value="" selected>Seleccione...</option>
                                        @foreach($cargos as $cargo)
                                            <option value="{{ $cargo->id }}">{{ $cargo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_cargo_id"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="fecha_finalizacion_contrato_group"  style="display: none">
                                    <label for="fecha_finalizacion_contrato">Fecha Finalización del contrato</label>
                                    <input type="date" id="fecha_finalizacion_contrato" class="form-control">
                                    <span class="text-danger" id="error_fecha_finalizacion_contrato"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group" id="cliente" style="display: none">
                                    <label for="cliente_id">Cliente</label>
                                    <select id="cliente_id" class="form-control">
                                        <option value="" selected>Seleccione...</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">
                                                    @if($cliente->tipopersona_id==1)
                                                        {{$cliente->nombres.' '.$cliente->apellidos}}
                                                    @else
                                                        {{$cliente->nombre_establecimiento}}
                                                    @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_cliente_id"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="sucursal_cliente_group" style="display: none">
                                    <label for="sucursal_id">Sucursal del cliente</label>
                                    <select id="sucursal_id" class="form-control">
                                        <option value="" selected>Seleccionar</option>
                                    </select>
                                    <span class="text-danger" id="error_sucursal_id"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group" id="ubicacion_group" style="display: none">
                                    <label for="ubicacion">Ubicación</label>
                                    <input type="text" id="ubicacion" class="form-control">
                                    <span class="text-danger" id="error_ubicacion"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Información Salarial -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información Salarial</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salario">Salario</label>
                                    <input type="text" id="salario" class="form-control" placeholder="Ingrese el salario">
                                    <span class="text-danger" id="error_salario"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="active">
                                        <label for="active">Activo</label>
                                    </div>
                                    <span class="text-danger" id="error_active"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
