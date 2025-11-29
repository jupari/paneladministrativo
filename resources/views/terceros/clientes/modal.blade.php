<!-- Modal -->
<div class="modal fade" id="ModalCliente" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Cliente</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    <!-- Agrupación: Identificación -->

                    <input type="hidden" id="tercerotipo_id" value="{{ $tercerotipo_id }}">
                    <input type="hidden" id="user_id" value="{{ $user_id }}">

                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Identificación</legend>
                        <!-- Campo: Tipo de Persona -->
                        <div class="form-group">
                            <label for="tipopersona_id">Tipo de Persona</label>
                            <select id="tipopersona_id" class="form-control" style="width: 100%;" placeholder="Seleccione el tipo de persona" onchange="actualizarValidaciones()">
                                <option value="" selected>Seleccione...</option>
                                @foreach($tiposPersona as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_tipopersona_id"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipoidentificacion_id">Tipo de Identificación</label>
                                    <select id="tipoidentificacion_id" class="form-control">
                                        <option value="" selected>Seleccione...</option>
                                        @foreach($tiposIdentificacion as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="error_tipoidentificacion_id"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="identificacion">Identificación</label>
                                    <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación">
                                </div>
                                <span class="text-danger" id="error_identificacion"></span>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="dv">DV</label>
                                    <input type="text" id="dv" class="form-control" placeholder="DV">
                                </div>
                                <span class="text-danger" id="error_dv"></span>
                            </div>
                        </div>
                    </fieldset>


                    <!-- Agrupación: Información Personal -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información Personal</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" id="nombres_group">
                                    <label for="nombres">Nombres</label>
                                    <input type="text" id="nombres" class="form-control" placeholder="Ingrese los nombres">
                                </div>
                                <span class="text-danger" id="error_nombres"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" id="apellidos_group">
                                    <label for="apellidos">Apellidos</label>
                                    <input type="text" id="apellidos" class="form-control" placeholder="Ingrese los apellidos">
                                </div>
                                <span class="text-danger" id="error_apellidos"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group" id="nombre_establecimiento_group">
                                <label for="nombre_establecimiento">Nombre del Establecimiento</label>
                                <input type="text" id="nombre_establecimiento" class="form-control" placeholder="Nombre del Establecimiento">
                            </div>
                            <span class="text-danger" id="error_nombre_establecimiento"></span>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Información de Contacto -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información de Contacto</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" id="telefono" class="form-control" placeholder="Número de teléfono">
                                </div>
                                <span class="text-danger" id="error_telefono"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="celular">Celular</label>
                                    <input type="text" id="celular" class="form-control" placeholder="Número de celular">
                                </div>
                                <span class="text-danger" id="error_celular"></span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo">Correo Electrónico</label>
                                    <input type="email" id="correo" class="form-control" placeholder="Correo Electrónico">
                                </div>
                                <span class="text-danger" id="error_correo"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="correo_fe">Correo Facturación Electrónica</label>
                                    <input type="email" id="correo_fe" class="form-control" placeholder="Correo FE">
                                </div>
                                <span class="text-danger" id="error_correo_fe"></span>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div id="vendedor_wrapper">
                                        <label for="vendedor_id">Vendedor</label>
                                        @if(isset($vendedorxrol) && auth()->user()->hasRole('Vendedor'))
                                            <p class="form-control" readonly>{{ $vendedorxrol->nombre_completo }}</p>
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
                                    <span class="text-danger" id="error_vendedor_id"></span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Agrupación: Dirección -->
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Dirección</legend>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="pais_id">País</label>
                                    <select id="pais_id" class="form-control">
                                        <option value="">Seleccione un país</option>
                                        @foreach($paises as $pais)
                                            <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Departamento -->
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="departamento_id">Departamento</label>
                                    <select id="departamento_id" class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select id="ciudad_id" class="form-control">
                                        <option value="" selected>Seleccione...</option>
                                        {{-- @foreach($ciudades as $ciudad)
                                            <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                        @endforeach --}}
                                    </select>
                                    <span class="text-danger" id="error_ciudad_id"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    <input type="text" id="direccion" class="form-control" placeholder="Dirección">
                                </div>
                                <span class="text-danger" id="error_direccion"></span>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <input type="hidden" id="id">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Contactos
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
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
                                                <input type="text" id="contacto_apellidos"  class="form-control" placeholder="Apellidos">
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
                                                <input type="text" id="contacto_celular"  class="form-control" placeholder="Celular">
                                                <span class="text-danger" id="error_contacto_celular"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="contacto_telefono">Teléfono</label>
                                                <input type="text" id="contacto_telefono" class="form-control" placeholder="Teléfono">
                                                <span class="text-danger" id="error_contacto_telefono"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="contacto_ext">Ext</label>
                                                <input type="text" id="contacto_ext"  class="form-control" placeholder="Extensión">
                                                <span class="text-danger" id="error_contacto_ext"></span>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <label for="contacto_cargo">Cargo</label>
                                                <input type="text" id="contacto_cargo"  class="form-control" placeholder="Cargo">
                                                <span class="text-danger" id="error_contacto_cargo"></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success mt-2" id="addContactoBtn"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegisterContacto"></span>Guardar Contacto</button>
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
                <div class="accordion" id="accordionExample2">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne1" aria-expanded="false" aria-controls="collapseOne1">
                                Sucursales
                            </button>
                        </h2>
                        <div id="collapseOne1" class="accordion-collapse collapse" aria-labelledby="headingOne1" data-bs-parent="#accordionExample2">
                            <div class="accordion-body">
                                <div id="sucursales-container">
                                    <div class="sucursal-item border p-3 mb-3">
                                        <div class="row">
                                            <input type="hidden" id="sucursal_id">
                                            {{--<div class="col-md-4">
                                                 <div class="form-group">
                                                    <label for="sucursal_vendedor_id">Comercial</label>
                                                    <select id="sucursal_vendedor_id" class="form-control">
                                                        <option value="" selected>Seleccione...</option>
                                                        @foreach($vendedores as $vendedor)
                                                            <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger" id="error_sucursal_vendedor_id"></span>
                                                </div>
                                            </div>--}}
                                            <div class="col-md-8">
                                                <label for="sucursal_nombre_sucursal">Nombre Sucursal</label>
                                                <input type="text" id="sucursal_nombre_sucursal" class="form-control" placeholder="Nombres">
                                                <span class="text-danger" id="error_nombre_sucursal"></span>
                                            </div>
                                            {{-- vendedor --}}
                                            <div class="col-md-4">
                                                <label for="sucursal_correo">Correo</label>
                                                <input type="email" id="sucursal_correo" class="form-control" placeholder="Correo Electrónico">
                                                <span class="text-danger" id="error_sucursal_correo"></span>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12 col-md-6">
                                                <label for="sucursal_celular">Celular</label>
                                                <input type="text" id="sucursal_celular"  class="form-control" placeholder="Celular">
                                                <span class="text-danger" id="error_sucursal_celular"></span>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label for="sucursal_telefono">Teléfono</label>
                                                <input type="text" id="sucursal_telefono" class="form-control" placeholder="Teléfono">
                                                <span class="text-danger" id="error_sucursal_telefono"></span>
                                            </div>
                                            <div class="col-md-12 d-block d-md-flex">
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
                                                <!-- Departamento -->
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

                                                            {{-- @foreach($ciudades as $ciudad)
                                                                <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                                            @endforeach --}}
                                                        </select>
                                                        <span class="text-danger" id="error_sucursal_ciudad_id"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="sucursal_direccion">Dirección</label>
                                            <input type="text" id="sucursal_direccion"  class="form-control" placeholder="Dirección">
                                            <span class="text-danger" id="error_sucursal_direccion"></span>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <label for="sucursal_persona_contacto">Persona de Contacto</label>
                                                <input type="text" id="sucursal_persona_contacto"  class="form-control" placeholder="Persona contacto">
                                                <span class="text-danger" id="error_sucursal_persona_contacto"></span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success mt-2" id="addSucursalBtn"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerRegisterSucursal"></span>Guardar Sucursal</button>
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
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
