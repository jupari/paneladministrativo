<!-- Modal -->
<div class="modal fade" id="ModalCliente" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="exampleModalLabel">
                    <i class="fas fa-user-plus mr-2"></i>Registrar Cliente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <!-- Navegación por pasos -->
                <div class="steps-nav bg-light p-3 border-bottom">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="step active" data-step="1">
                                <div class="step-circle">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <small class="d-block mt-1">Identificación</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step" data-step="2">
                                <div class="step-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                                <small class="d-block mt-1">Información</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step" data-step="3">
                                <div class="step-circle">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <small class="d-block mt-1">Contacto</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="step" data-step="4">
                                <div class="step-circle">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <small class="d-block mt-1">Dirección</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <form autocomplete="off">
                        <input type="hidden" id="tercerotipo_id" value="{{ $tercerotipo_id }}">
                        <div class="invalid-feedback" id="error_tercerotipo_id" style="display: block;"></div>
                        
                        <input type="hidden" id="user_id" value="{{ $user_id  ?? auth()->id()}}">
                        <div class="invalid-feedback" id="error_user_id" style="display: block;"></div>

                        <!-- PASO 1: Identificación -->
                        <div class="step-content" id="step-1">

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-id-card text-primary mr-2"></i>Identificación
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Campo: Tipo de Persona -->
                                <div class="form-group">
                                    <label for="tipopersona_id">
                                        <i class="fas fa-user-tag text-muted mr-1"></i>Tipo de Persona <span class="text-danger">*</span>
                                    </label>
                                    <select id="tipopersona_id" class="form-control form-control-lg" style="width: 100%;" onchange="actualizarValidaciones()">
                                        <option value="" selected>Seleccione el tipo de persona...</option>
                                        @foreach($tiposPersona as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="error_tipopersona_id"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipoidentificacion_id">
                                                <i class="fas fa-id-card-alt text-muted mr-1"></i>Tipo de Identificación <span class="text-danger">*</span>
                                            </label>
                                            <select id="tipoidentificacion_id" class="form-control">
                                                <option value="" selected>Seleccione tipo de identificación...</option>
                                                @foreach($tiposIdentificacion as $tipo)
                                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="error_tipoidentificacion_id"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="identificacion">
                                                <i class="fas fa-hashtag text-muted mr-1"></i>Identificación <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación">
                                            <div class="invalid-feedback" id="error_identificacion"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="dv">
                                                <i class="fas fa-check text-muted mr-1"></i>DV
                                            </label>
                                            <input type="text" id="dv" class="form-control" placeholder="DV" maxlength="1">
                                            <div class="invalid-feedback" id="error_dv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- PASO 2: Información Personal -->
                        <div class="step-content d-none" id="step-2">


                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-user text-primary mr-2"></i>Información Personal
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group" id="nombres_group">
                                            <label for="nombres">
                                                <i class="fas fa-user text-muted mr-1"></i>Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" id="nombres" class="form-control" placeholder="Ingrese los nombres">
                                            <div class="invalid-feedback" id="error_nombres"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="apellidos_group">
                                            <label for="apellidos">
                                                <i class="fas fa-user text-muted mr-1"></i>Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" id="apellidos" class="form-control" placeholder="Ingrese los apellidos">
                                            <div class="invalid-feedback" id="error_apellidos"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group" id="nombre_establecimiento_group">
                                            <label for="nombre_establecimiento">
                                                <i class="fas fa-building text-muted mr-1"></i>Nombre del Establecimiento
                                            </label>
                                            <input type="text" id="nombre_establecimiento" class="form-control" placeholder="Nombre del Establecimiento">
                                            <div class="invalid-feedback" id="error_nombre_establecimiento"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- PASO 3: Información de Contacto -->
                        <div class="step-content d-none" id="step-3">

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-phone text-primary mr-2"></i>Información de Contacto
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telefono">
                                                <i class="fas fa-phone text-muted mr-1"></i>Teléfono
                                            </label>
                                            <input type="text" id="telefono" class="form-control" placeholder="Número de teléfono">
                                            <div class="invalid-feedback" id="error_telefono"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="celular">
                                                <i class="fas fa-mobile-alt text-muted mr-1"></i>Celular
                                            </label>
                                            <input type="text" id="celular" class="form-control" placeholder="Número de celular">
                                            <div class="invalid-feedback" id="error_celular"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo">
                                                <i class="fas fa-envelope text-muted mr-1"></i>Correo Electrónico
                                            </label>
                                            <input type="email" id="correo" class="form-control" placeholder="Correo Electrónico">
                                            <div class="invalid-feedback" id="error_correo"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo_fe">
                                                <i class="fas fa-file-invoice text-muted mr-1"></i>Correo Facturación Electrónica
                                            </label>
                                            <input type="email" id="correo_fe" class="form-control" placeholder="Correo FE">
                                            <div class="invalid-feedback" id="error_correo_fe"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div id="vendedor_wrapper">
                                                <label for="vendedor_id">
                                                    <i class="fas fa-user-tie text-muted mr-1"></i>Vendedor <span class="text-danger">*</span>
                                                </label>
                                                @if(isset($vendedorxrol) && auth()->user()->hasRole('Vendedor'))
                                                    <div class="alert alert-info d-flex align-items-center">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        <span>{{ $vendedorxrol->nombre_completo }} (Asignado automáticamente)</span>
                                                    </div>
                                                    <input type="hidden" id="vendedor_hidden" value="{{$vendedorxrol->id}}">
                                                @else
                                                    <select id="vendedor_id" name="vendedor_id" class="form-control">
                                                        <option value="">Seleccione un vendedor...</option>
                                                        @foreach($vendedores as $vendedor)
                                                            <option value="{{ $vendedor->id }}"
                                                                {{ isset($vendedorxrol) && $vendedor->id == $vendedorxrol->id ? 'selected' : '' }}>
                                                                {{ $vendedor->nombre_completo }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="invalid-feedback" id="error_vendedor_id"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- PASO 4: Dirección -->
                        <div class="step-content d-none" id="step-4">

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt text-primary mr-2"></i>Dirección
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="pais_id">
                                                <i class="fas fa-globe text-muted mr-1"></i>País <span class="text-danger">*</span>
                                            </label>
                                            <select id="pais_id" class="form-control">
                                                <option value="">Seleccione un país...</option>
                                                @foreach($paises as $pais)
                                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="error_pais_id"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="departamento_id">
                                                <i class="fas fa-map text-muted mr-1"></i>Departamento <span class="text-danger">*</span>
                                            </label>
                                            <select id="departamento_id" class="form-control">
                                                <option value="">Seleccione un departamento...</option>
                                            </select>
                                            <div class="invalid-feedback" id="error_departamento_id"></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="ciudad_id">
                                                <i class="fas fa-city text-muted mr-1"></i>Ciudad <span class="text-danger">*</span>
                                            </label>
                                            <select id="ciudad_id" class="form-control">
                                                <option value="" selected>Seleccione una ciudad...</option>
                                            </select>
                                            <div class="invalid-feedback" id="error_ciudad_id"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="direccion">
                                                <i class="fas fa-map-pin text-muted mr-1"></i>Dirección <span class="text-danger">*</span>
                                            </label>
                                            <textarea id="direccion" class="form-control" rows="2" placeholder="Ingrese la dirección completa"></textarea>
                                            <div class="invalid-feedback" id="error_direccion"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- Navegación entre pasos -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="prevStep" disabled>
                                <i class="fas fa-chevron-left mr-1"></i>Anterior
                            </button>
                            <button type="button" class="btn btn-primary" id="nextStep">
                                Siguiente <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                            <button type="button" class="btn btn-success d-none" id="saveClient">
                                <i class="fas fa-save mr-1"></i>Guardar Cliente
                            </button>
                        </div>
                    </form>

                    <input type="hidden" id="id">

                    <!-- Sección de Contactos y Sucursales -->
                    <hr class="my-4">

                    <!-- Accordion para Contactos -->
                    <div class="accordion mb-3" id="accordionContactos">
                        <div class="card">
                            <div class="card-header bg-info text-white" id="headingContactos">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-white text-decoration-none w-100 text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseContactos" aria-expanded="false" aria-controls="collapseContactos">
                                        <i class="fas fa-address-book mr-2"></i>Gestión de Contactos
                                        <i class="fas fa-chevron-down float-right mt-1"></i>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseContactos" class="collapse" aria-labelledby="headingContactos" data-parent="#accordionContactos">
                                <div class="card-body">
                                    <div id="contactos-container">
                                        <div class="sucursal-item border p-3 mb-3">
                                            <div class="row">
                                                <input type="hidden" id="contacto_id">
                                                <div class="col-md-4">
                                                    <label for="contacto_nombres">Nombres</label>
                                                    <input type="text" id="contacto_nombres" class="form-control" placeholder="Nombres">
                                                    <span class="text-danger" id="error_contacto_nombres"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="contacto_apellidos">Apellidos</label>
                                                    <input type="text" id="contacto_apellidos" class="form-control" placeholder="Apellidos">
                                                    <span class="text-danger" id="error_contacto_apellidos"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="contacto_correo">Correo</label>
                                                    <input type="email" id="contacto_correo" class="form-control" placeholder="Correo Electrónico">
                                                    <span class="text-danger" id="error_contacto_correo"></span>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <label for="contacto_celular">Celular</label>
                                                    <input type="text" id="contacto_celular" class="form-control" placeholder="Celular">
                                                    <span class="text-danger" id="error_contacto_celular"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="contacto_telefono">Teléfono</label>
                                                    <input type="text" id="contacto_telefono" class="form-control" placeholder="Teléfono">
                                                    <span class="text-danger" id="error_contacto_telefono"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="contacto_ext">Ext</label>
                                                    <input type="text" id="contacto_ext" class="form-control" placeholder="Extensión">
                                                    <span class="text-danger" id="error_contacto_ext"></span>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <label for="contacto_cargo">Cargo</label>
                                                    <input type="text" id="contacto_cargo" class="form-control" placeholder="Cargo">
                                                    <span class="text-danger" id="error_contacto_cargo"></span>
                                                </div>
                                            </div>
                                            <div class="text-right mt-3">
                                                <button type="button" class="btn btn-success" id="addContactoBtn">
                                                    <span class="spinner-border spinner-border-sm d-none mr-2" role="status" aria-hidden="true" id="spinnerRegisterContacto"></span>
                                                    <i class="fas fa-plus mr-1"></i>Agregar Contacto
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-3">
                                        <div class="table-responsive">
                                            <table id="contactos-table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nombre(s)</th>
                                                        <th>Apellidos(s)</th>
                                                        <th>Cargo</th>
                                                        <th>Correo electrónico</th>
                                                        <th>Número de tel.</th>
                                                        <th>Número de Celular</th>
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

                    <!-- Accordion para Sucursales -->
                    <div class="accordion mb-3" id="accordionSucursales">
                        <div class="card">
                            <div class="card-header bg-warning text-dark" id="headingSucursales">
                                <h2 class="mb-0">
                                    <button class="btn btn-link text-dark text-decoration-none w-100 text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSucursales" aria-expanded="false" aria-controls="collapseSucursales">
                                        <i class="fas fa-building mr-2"></i>Gestión de Sucursales
                                        <i class="fas fa-chevron-down float-right mt-1"></i>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseSucursales" class="collapse" aria-labelledby="headingSucursales" data-parent="#accordionSucursales">
                            <div id="collapseSucursales" class="collapse" aria-labelledby="headingSucursales" data-parent="#accordionSucursales">
                                <div class="card-body">
                                    <div id="sucursales-container">
                                        <div class="sucursal-item border p-3 mb-3">
                                            <div class="row">
                                                <input type="hidden" id="sucursal_id">
                                                <div class="col-md-8">
                                                    <label for="sucursal_nombre_sucursal">Nombre Sucursal</label>
                                                    <input type="text" id="sucursal_nombre_sucursal" class="form-control" placeholder="Nombre de la sucursal">
                                                    <span class="text-danger" id="error_nombre_sucursal"></span>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="sucursal_correo">Correo</label>
                                                    <input type="email" id="sucursal_correo" class="form-control" placeholder="Correo Electrónico">
                                                    <span class="text-danger" id="error_sucursal_correo"></span>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12 col-md-6">
                                                    <label for="sucursal_celular">Celular</label>
                                                    <input type="text" id="sucursal_celular" class="form-control" placeholder="Celular">
                                                    <span class="text-danger" id="error_sucursal_celular"></span>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label for="sucursal_telefono">Teléfono</label>
                                                    <input type="text" id="sucursal_telefono" class="form-control" placeholder="Teléfono">
                                                    <span class="text-danger" id="error_sucursal_telefono"></span>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="sucursal_pais_id">País</label>
                                                        <select id="sucursal_pais_id" class="form-control">
                                                            <option value="">Seleccione un país</option>
                                                            @foreach($paises as $pais)
                                                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="sucursal_departamento_id">Departamento</label>
                                                        <select id="sucursal_departamento_id" class="form-control">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="sucursal_ciudad_id">Ciudad</label>
                                                        <select id="sucursal_ciudad_id" class="form-control">
                                                        </select>
                                                        <span class="text-danger" id="error_sucursal_ciudad_id"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <label for="sucursal_direccion">Dirección</label>
                                                    <input type="text" id="sucursal_direccion" class="form-control" placeholder="Dirección">
                                                    <span class="text-danger" id="error_sucursal_direccion"></span>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <label for="sucursal_persona_contacto">Persona de Contacto</label>
                                                    <input type="text" id="sucursal_persona_contacto" class="form-control" placeholder="Persona contacto">
                                                    <span class="text-danger" id="error_sucursal_persona_contacto"></span>
                                                </div>
                                            </div>
                                            <div class="text-right mt-3">
                                                <button type="button" class="btn btn-success" id="addSucursalBtn">
                                                    <span class="spinner-border spinner-border-sm d-none mr-2" role="status" aria-hidden="true" id="spinnerRegisterSucursal"></span>
                                                    <i class="fas fa-plus mr-1"></i>Agregar Sucursal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-3">
                                        <div class="table-responsive">
                                            <table id="sucursales-table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Comercial</th>
                                                        <th>Nombre(s)</th>
                                                        <th>Correo electrónico</th>
                                                        <th>Número de tel.</th>
                                                        <th>Número de Celular</th>
                                                        <th>Ciudad</th>
                                                        <th>Dirección</th>
                                                        <th>Persona de contacto</th>
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
            </div>
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <div class="step-indicators">
                        <small class="text-muted">Paso <span id="current-step">1</span> de 4</small>
                    </div>
                    <div id="footer-buttons">
                        <!-- Los botones de navegación se moverán aquí dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
