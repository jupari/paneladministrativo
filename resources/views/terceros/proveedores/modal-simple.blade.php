<!-- Modal Completo con UX/UI Mejorado para Proveedores -->
<div class="modal fade" id="ModalProveedor" tabindex="-1" role="dialog" aria-labelledby="modalProveedorLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h4 class="modal-title d-flex align-items-center" id="modalProveedorLabel">
                    <i class="fas fa-truck mr-2"></i>
                    <span id="modal-title-text">Registrar Proveedor</span>
                    <small class="ml-2 opacity-75" id="modal-subtitle"></small>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Progress Indicator -->
            <div class="progress" style="height: 4px; border-radius: 0;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" id="modal-progress"></div>
            </div>

            <div class="modal-body">
                <!-- Step Navigation -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div class="step-item active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Datos Básicos</div>
                            </div>
                            <div class="step-item" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Información</div>
                            </div>
                            <div class="step-item" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Contactos</div>
                            </div>
                            <div class="step-item" data-step="4">
                                <div class="step-number">4</div>
                                <div class="step-label">Sucursales</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ auth()->id() }}
                <!-- Form Container -->
                <form autocomplete="off" id="proveedor-form" novalidate>
                    <input type="hidden" id="tercerotipo_id" value="{{ $tercerotipo_id ?? '2' }}">
                    <input type="hidden" id="user_id" value="{{ $user_id ?? auth()->id() }}">
                    <input type="hidden" id="id">

                    <!-- DEBUG: Verificar valores desde PHP -->
                    <script>
                        // Este código NO usa jQuery, así que puede ejecutarse inmediatamente
                        console.log('📋 DEBUG BLADE - Valores desde PHP (PROVEEDOR):');
                        console.log('   - tercerotipo_id desde PHP:', '{{ $tercerotipo_id ?? "UNDEFINED" }}');
                        console.log('   - user_id desde PHP:', '{{ $user_id ?? "UNDEFINED" }}');
                        console.log('   - user_id auth():', '{{ auth()->id() ?? "NO_AUTH" }}');

                        // DEBUGGING MÁS DETALLADO
                        const debugUserValueProv = '{{ $user_id ?? auth()->id() }}';
                        console.log('   - Valor final que se asignará a user_id:', debugUserValueProv);
                        console.log('   - Tipo del valor:', typeof debugUserValueProv);
                        console.log('   - Es string vacío:', debugUserValueProv === '');

                        // ESTABLECER VALORES cuando jQuery esté disponible
                        document.addEventListener('DOMContentLoaded', function() {
                            // Esperar un poco más para asegurar que jQuery esté disponible
                            setTimeout(function() {
                                if (typeof $ !== 'undefined') {
                                    // Valores de fallback inmediatos
                                    const tercerotipo_fallback = '{{ $tercerotipo_id ?? "2" }}';
                                    const user_fallback = '{{ $user_id ?? auth()->id() }}';

                                    console.log('🔧 Estableciendo valores fallback (PROVEEDOR):');
                                    console.log('   - tercerotipo_fallback:', tercerotipo_fallback);
                                    console.log('   - user_fallback:', user_fallback);

                                    $('#tercerotipo_id').val(tercerotipo_fallback);
                                    $('#user_id').val(user_fallback);

                                    console.log('✅ Valores finales establecidos (PROVEEDOR):');
                                    console.log('   - tercerotipo_id final:', $('#tercerotipo_id').val());
                                    console.log('   - user_id final:', $('#user_id').val());
                                } else {
                                    console.error('❌ jQuery no disponible después de esperar');
                                }
                            }, 500);
                        });
                    </script>

                    <!-- Step 1: Datos Básicos -->
                    <div class="step-content" id="step-1">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-truck mr-2"></i>Información Básica</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipopersona_id" class="required">Tipo de Persona</label>
                                            <select id="tipopersona_id" class="form-control" required>
                                                <option value="" selected>Seleccione...</option>
                                                @if(isset($tiposPersona) && is_iterable($tiposPersona))
                                                    @foreach($tiposPersona as $tipo)
                                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <span class="text-danger" id="error_tipopersona_id"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipoidentificacion_id" class="required">Tipo de Identificación</label>
                                            <select id="tipoidentificacion_id" class="form-control" required>
                                                <option value="" selected>Seleccione...</option>
                                                @if(isset($tiposIdentificacion) && is_iterable($tiposIdentificacion))
                                                    @foreach($tiposIdentificacion as $tipo)
                                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <span class="text-danger" id="error_tipoidentificacion_id"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="identificacion" class="required">Identificación</label>
                                            <input type="text" id="identificacion" class="form-control" placeholder="Número de identificación" required>
                                            <span class="text-danger" id="error_identificacion"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                            <small class="form-text text-muted">Ingrese el número sin puntos ni espacios</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dv">DV</label>
                                            <input type="text" id="dv" class="form-control" placeholder="DV" maxlength="1">
                                            <span class="text-danger" id="error_dv"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombres" class="required">Nombres</label>
                                            <input type="text" id="nombres" class="form-control" placeholder="Nombres" required>
                                            <span class="text-danger" id="error_nombres"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="apellidos" class="required">Apellidos</label>
                                            <input type="text" id="apellidos" class="form-control" placeholder="Apellidos" required>
                                            <span class="text-danger" id="error_apellidos"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nombre_establecimiento">Nombre del Establecimiento</label>
                                    <input type="text" id="nombre_establecimiento" class="form-control" placeholder="Razón social o nombre comercial">
                                    <span class="text-danger" id="error_nombre_establecimiento"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Información de Contacto -->
                    <div class="step-content d-none" id="step-2">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-address-book mr-2"></i>Información de Contacto</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text" id="telefono" class="form-control" placeholder="Teléfono fijo">
                                            </div>
                                            <span class="text-danger" id="error_telefono"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="celular">Celular</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                                </div>
                                                <input type="text" id="celular" class="form-control" placeholder="Número de celular">
                                            </div>
                                            <span class="text-danger" id="error_celular"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo">Correo Electrónico</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email" id="correo" class="form-control" placeholder="correo@empresa.com">
                                            </div>
                                            <span class="text-danger" id="error_correo"></span>
                                            <div class="valid-feedback">¡Email válido!</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo_fe">Correo Facturación Electrónica</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                                </div>
                                                <input type="email" id="correo_fe" class="form-control" placeholder="facturacion@empresa.com">
                                            </div>
                                            <span class="text-danger" id="error_correo_fe"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="vendedor_id">Vendedor (Opcional)</label>
                                    <select id="vendedor_id" name="vendedor_id" class="form-control">
                                        <option value="">Seleccione un vendedor...</option>
                                        @if(isset($vendedores) && is_iterable($vendedores))
                                            @foreach($vendedores as $vendedor)
                                                <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="text-danger" id="error_vendedor_id"></span>
                                </div>

                                <!-- Dirección -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pais_id">País</label>
                                            <select id="pais_id" class="form-control">
                                                <option value="">Seleccione un país...</option>
                                                @if(isset($paises) && is_iterable($paises))
                                                    @foreach($paises as $pais)
                                                        <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="departamento_id">Departamento</label>
                                            <select id="departamento_id" class="form-control">
                                                <option value="">Seleccione un departamento...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ciudad_id" class="required">Ciudad</label>
                                            <select id="ciudad_id" class="form-control" required>
                                                <option value="" selected>Seleccione una ciudad...</option>
                                            </select>
                                            <span class="text-danger" id="error_ciudad_id"></span>
                                            <div class="valid-feedback">¡Perfecto!</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="direccion" class="required">Dirección</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <textarea id="direccion" class="form-control" rows="2" placeholder="Dirección completa" required></textarea>
                                    </div>
                                    <span class="text-danger" id="error_direccion"></span>
                                    <div class="valid-feedback">¡Perfecto!</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Contactos -->
                    <div class="step-content d-none" id="step-3">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-users mr-2"></i>Contactos</h5>
                                <button type="button" class="btn btn-success btn-sm float-right" id="addContactoBtn">
                                    <i class="fas fa-plus mr-1"></i>Agregar Contacto
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Formulario de contacto -->
                                <div id="contacto-form" class="border rounded p-3 mb-3 bg-light d-none">
                                    <h6><i class="fas fa-user-plus mr-2"></i>Nuevo Contacto</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contacto_nombres">Nombres</label>
                                                <input type="text" id="contacto_nombres" class="form-control" placeholder="Nombres">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contacto_apellidos">Apellidos</label>
                                                <input type="text" id="contacto_apellidos" class="form-control" placeholder="Apellidos">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contacto_correo">Correo</label>
                                                <input type="email" id="contacto_correo" class="form-control" placeholder="correo@empresa.com">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contacto_cargo">Cargo</label>
                                                <input type="text" id="contacto_cargo" class="form-control" placeholder="Cargo">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="contacto_celular">Celular</label>
                                                <input type="text" id="contacto_celular" class="form-control" placeholder="Celular">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="contacto_telefono">Teléfono</label>
                                                <input type="text" id="contacto_telefono" class="form-control" placeholder="Teléfono">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="contacto_ext">Ext</label>
                                                <input type="text" id="contacto_ext" class="form-control" placeholder="Extensión">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelContactoBtn">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="registerContacto()">
                                            <span class="spinner-border spinner-border-sm d-none" id="spinnerRegisterContacto"></span>
                                            <i class="fas fa-save mr-1"></i>Guardar Contacto
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla de contactos -->
                                <div class="table-responsive">
                                    <table id="contactos-table" class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 15%">Nombres</th>
                                                <th style="width: 15%">Apellidos</th>
                                                <th style="width: 15%">Cargo</th>
                                                <th style="width: 20%">Correo</th>
                                                <th style="width: 12%">Teléfono</th>
                                                <th style="width: 12%">Celular</th>
                                                <th style="width: 6%">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="no-contactos">
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-info-circle mr-2"></i>No hay contactos registrados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Sucursales -->
                    <div class="step-content d-none" id="step-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-building mr-2"></i>Sucursales</h5>
                                <button type="button" class="btn btn-success btn-sm float-right" id="addSucursalBtn">
                                    <i class="fas fa-plus mr-1"></i>Agregar Sucursal
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Formulario de sucursal -->
                                <div id="sucursal-form" class="border rounded p-3 mb-3 bg-light d-none">
                                    <h6><i class="fas fa-building mr-2"></i>Nueva Sucursal</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sucursal_nombre_sucursal">Nombre Comercial</label>
                                                <input type="text" id="sucursal_nombre_sucursal" class="form-control" placeholder="Nombre de la sucursal">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sucursal_persona_contacto">Persona de Contacto</label>
                                                <input type="text" id="sucursal_persona_contacto" class="form-control" placeholder="Nombre del contacto">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="sucursal_correo">Correo</label>
                                                <input type="email" id="sucursal_correo" class="form-control" placeholder="sucursal@empresa.com">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="sucursal_telefono">Teléfono</label>
                                                <input type="text" id="sucursal_telefono" class="form-control" placeholder="Teléfono">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="sucursal_celular">Celular</label>
                                                <input type="text" id="sucursal_celular" class="form-control" placeholder="Celular">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sucursal_pais_id">País</label>
                                                <select id="sucursal_pais_id" class="form-control">
                                                    <option value="">Seleccione...</option>
                                                    @if(isset($paises) && is_iterable($paises))
                                                        @foreach($paises as $pais)
                                                            <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sucursal_departamento_id">Departamento</label>
                                                <select id="sucursal_departamento_id" class="form-control">
                                                    <option value="">Seleccione...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sucursal_ciudad_id">Ciudad</label>
                                                <select id="sucursal_ciudad_id" class="form-control">
                                                    <option value="">Seleccione...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sucursal_direccion">Dirección</label>
                                        <textarea id="sucursal_direccion" class="form-control" rows="2" placeholder="Dirección completa"></textarea>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelSucursalBtn">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="registerSucursal()">
                                            <span class="spinner-border spinner-border-sm d-none" id="spinnerRegisterSucursal"></span>
                                            <i class="fas fa-save mr-1"></i>Guardar Sucursal
                                        </button>
                                    </div>
                                </div>

                                <!-- Tabla de sucursales -->
                                <div class="table-responsive">
                                    <table id="sucursales-table" class="table table-bordered table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%">#</th>
                                                <th style="width: 15%">Comercial</th>
                                                <th style="width: 15%">Correo</th>
                                                <th style="width: 12%">Teléfono</th>
                                                <th style="width: 12%">Celular</th>
                                                <th style="width: 15%">Ciudad</th>
                                                <th style="width: 20%">Dirección</th>
                                                <th style="width: 6%">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="no-sucursales">
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-info-circle mr-2"></i>No hay sucursales registradas
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Hidden fields for compatibility -->
                <input type="hidden" id="contacto_id">
                <input type="hidden" id="sucursal_id">
            </div>

            </div>

            <!-- Modal Footer with Step Navigation -->
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-info d-none" id="save-draft-btn">
                            <i class="fas fa-save mr-1"></i>Guardar Borrador
                        </button>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-outline-primary d-none" id="prev-btn"
                            onclick="event.preventDefault(); event.stopImmediatePropagation(); window.goToPrevStep(event); return false;"
                            onmousedown="event.preventDefault(); event.stopImmediatePropagation(); return false;"
                            onmouseup="event.preventDefault(); event.stopImmediatePropagation(); return false;">
                            <i class="fas fa-chevron-left mr-1"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-primary" id="next-btn"
                            onclick="event.preventDefault(); event.stopImmediatePropagation(); window.goToNextStep(event); return false;"
                            onmousedown="event.preventDefault(); event.stopImmediatePropagation(); return false;"
                            onmouseup="event.preventDefault(); event.stopImmediatePropagation(); return false;">
                            Siguiente<i class="fas fa-chevron-right ml-1"></i>
                        </button>
                        <button type="button" class="btn btn-success d-none" id="finish-btn"
                            onclick="event.preventDefault(); event.stopImmediatePropagation(); window.finishProveedorSetup(event); return false;"
                            onmousedown="event.preventDefault(); event.stopImmediatePropagation(); return false;"
                            onmouseup="event.preventDefault(); event.stopImmediatePropagation(); return false;">
                            <i class="fas fa-check mr-1"></i>Finalizar y Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
    <div id="toast-container"></div>
</div>

<!-- Custom CSS for Modal Steps -->
<style>
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-item:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 15px;
    right: -50%;
    width: 100%;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
}

.step-item.active:not(:last-child)::after,
.step-item.completed:not(:last-child)::after {
    background: #28a745;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.step-item.active .step-number {
    background: #007bff;
    color: white;
}

.step-item.completed .step-number {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 12px;
    text-align: center;
    color: #6c757d;
    font-weight: 500;
}

.step-item.active .step-label {
    color: #007bff;
    font-weight: 600;
}

.step-item.completed .step-label {
    color: #28a745;
    font-weight: 600;
}

.form-control.is-valid {
    border-color: #28a745;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.required::after {
    content: "*";
    color: #dc3545;
    margin-left: 4px;
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.toast {
    min-width: 300px;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}
</style>
