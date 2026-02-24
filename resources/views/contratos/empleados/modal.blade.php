<!-- Modal -->
<div class="modal fade" id="ModalEmpleado" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title d-flex align-items-center" id="exampleModalLabel">
                    <i class="fas fa-user-plus mr-2"></i>
                    <span id="modal-title-text">Registrar Empleado</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <input type="hidden" id="id" value="">
                    <input type="hidden" id="user_id" value="{{ $user_id }}">
                    <!-- Agrupación: Información Básica -->
                    <fieldset class="border border-primary rounded p-3 mb-4">
                        <legend class="w-auto px-3 text-primary font-weight-bold">
                            <i class="fas fa-info-circle mr-1"></i>Información Básica
                        </legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo_contrato">Tipos de Contrato <span class="text-danger">*</span></label>
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
                                    <label for="nombres">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" id="nombres" class="form-control" placeholder="Ingrese los nombres">
                                    <span class="text-danger" id="error_nombres"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" id="apellidos" class="form-control" placeholder="Ingrese los apellidos">
                                    <span class="text-danger" id="error_apellidos"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Identificación -->
                    <fieldset class="border border-info rounded p-3 mb-4">
                        <legend class="w-auto px-3 text-info font-weight-bold">
                            <i class="fas fa-id-card mr-1"></i>Identificación
                        </legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo_identificacion_id">Tipo de identificación <span class="text-danger">*</span></label>
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
                                    <label for="identificacion">Identificación <span class="text-danger">*</span></label>
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
                    <fieldset class="border border-success rounded p-3 mb-4">
                        <legend class="w-auto px-3 text-success font-weight-bold">
                            <i class="fas fa-calendar-alt mr-1"></i>Fechas Importantes
                        </legend>
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
                    <fieldset class="border border-warning rounded p-3 mb-4">
                        <legend class="w-auto px-3 text-warning font-weight-bold">
                            <i class="fas fa-briefcase mr-1"></i>Información de Trabajo
                        </legend>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" class="form-control" placeholder="Ingrese la dirección">
                                    <span class="text-danger" id="error_direccion"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="departamento_id">Departamento</label>
                                    <select id="departamento_id" class="form-control" onchange="getCiudades(this.value)"></select>
                                    <span class="text-danger" id="error_departamento_id"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select id="ciudad_id" class="form-control" onchange="actualizarCiudadResidencia(this.value)">
                                        <option value="" selected>Seleccione...</option>
                                        @foreach($ciudades as $ciudad)
                                            <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_ciudad_id"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciudad_residencia">Ciudad de residencia</label>
                                    <input type="text" id="ciudad_residencia" class="form-control" placeholder="Ingrese la ciudad">
                                    <span class="text-danger" id="error_ciudad_residencia"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" id="telefono" class="form-control" placeholder="Ingrese el teléfono">
                                    <span class="text-danger" id="error_telefono"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" id="celular" class="form-control" placeholder="Ingrese el celular">
                                    <span class="text-danger" id="error_celular"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="text" id="correo" class="form-control" placeholder="Ingrese el correo">
                                    <span class="text-danger" id="error_correo"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cargo_id">Cargo <span class="text-danger">*</span></label>
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
                    <fieldset class="border border-secondary rounded p-3 mb-4">
                        <legend class="w-auto px-3 text-secondary font-weight-bold">
                            <i class="fas fa-dollar-sign mr-1"></i>Información Salarial
                        </legend>
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
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mejoras visuales para el modal */
    #ModalEmpleado .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    #ModalEmpleado .modal-header {
        border-radius: 15px 15px 0 0;
        background: linear-gradient(45deg, #007bff, #0056b3);
    }

    #ModalEmpleado fieldset {
        transition: all 0.3s ease;
    }

    #ModalEmpleado fieldset:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    #ModalEmpleado .text-danger {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }

    /* Asteriscos de campos requeridos deben ser inline */
    #ModalEmpleado label .text-danger {
        display: inline;
        margin-top: 0;
        margin-left: 2px;
        font-weight: 700;
        font-size: 1rem;
    }

    /* Mensajes de error específicos que van después de los inputs */
    #ModalEmpleado span[id^="error_"].text-danger {
        display: block;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        font-weight: 400;
    }

    #ModalEmpleado .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    #ModalEmpleado .btn {
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #ModalEmpleado .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    #btn-guardar-empleado:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* Loading spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Indicador de campo requerido - solo para asteriscos en labels */
    #ModalEmpleado label .text-danger {
        font-weight: 700;
    }

    /* Smooth transitions para fieldsets */
    .form-group {
        margin-bottom: 1.2rem;
    }

    /* Mejora para labels */
    label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
</style>
