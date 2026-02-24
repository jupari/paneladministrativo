<!-- Modal Simple y Directo para Proveedores (Sin Pasos) -->
<div class="modal fade" id="ModalProveedorDirect" tabindex="-1" role="dialog" aria-labelledby="modalProveedorDirectLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h4 class="modal-title d-flex align-items-center" id="modalProveedorDirectLabel">
                    <i class="fas fa-truck mr-2"></i>
                    <span id="modal-title-text">Registrar Proveedor</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Form Container -->
                <form autocomplete="off" id="proveedor-direct-form" novalidate>
                    <input type="hidden" id="direct_tercerotipo_id" value="{{ $tercerotipo_id ?? '2' }}">
                    <input type="hidden" id="direct_user_id" value="{{ $user_id ?? auth()->id() }}">
                    <input type="hidden" id="direct_id">

                    <!-- Información Básica -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-truck mr-2"></i>Información Básica</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipopersona_id" class="required">Tipo de Persona</label>
                                        <select id="direct_tipopersona_id" class="form-control" required>
                                            <option value="" selected>Seleccione...</option>
                                            @if(isset($tiposPersona) && is_iterable($tiposPersona))
                                                @foreach($tiposPersona as $tipo)
                                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <span class="text-danger" id="error_direct_tipopersona_id"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipoidentificacion_id" class="required">Tipo de Identificación</label>
                                        <select id="direct_tipoidentificacion_id" class="form-control" required>
                                            <option value="" selected>Seleccione...</option>
                                            @if(isset($tiposIdentificacion) && is_iterable($tiposIdentificacion))
                                                @foreach($tiposIdentificacion as $tipo)
                                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <span class="text-danger" id="error_direct_tipoidentificacion_id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="identificacion" class="required">Identificación</label>
                                        <input type="text" id="direct_identificacion" class="form-control" placeholder="Número de identificación" required>
                                        <span class="text-danger" id="error_direct_identificacion"></span>
                                        <small class="form-text text-muted">Ingrese el número sin puntos ni espacios</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dv">DV</label>
                                        <input type="text" id="direct_dv" class="form-control" placeholder="DV" maxlength="1">
                                        <span class="text-danger" id="error_direct_dv"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombres">Nombres</label>
                                        <input type="text" id="direct_nombres" class="form-control" placeholder="Nombres">
                                        <span class="text-danger" id="error_direct_nombres"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="apellidos">Apellidos</label>
                                        <input type="text" id="direct_apellidos" class="form-control" placeholder="Apellidos">
                                        <span class="text-danger" id="error_direct_apellidos"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nombre_establecimiento">Nombre del Establecimiento</label>
                                <input type="text" id="direct_nombre_establecimiento" class="form-control" placeholder="Razón social o nombre comercial">
                                <span class="text-danger" id="error_direct_nombre_establecimiento"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="card mb-4">
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
                                            <input type="text" id="direct_telefono" class="form-control" placeholder="Número de teléfono">
                                        </div>
                                        <span class="text-danger" id="error_direct_telefono"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="celular">Celular</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                            </div>
                                            <input type="text" id="direct_celular" class="form-control" placeholder="Número de celular">
                                        </div>
                                        <span class="text-danger" id="error_direct_celular"></span>
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
                                            <input type="email" id="direct_correo" class="form-control" placeholder="correo@empresa.com">
                                        </div>
                                        <span class="text-danger" id="error_direct_correo"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="correo_fe">Correo Facturación Electrónica</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                            </div>
                                            <input type="email" id="direct_correo_fe" class="form-control" placeholder="facturacion@empresa.com">
                                        </div>
                                        <span class="text-danger" id="error_direct_correo_fe"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="vendedor_id">Vendedor (Opcional)</label>
                                <select id="direct_vendedor_id" name="vendedor_id" class="form-control">
                                    <option value="">Seleccione un vendedor...</option>
                                    @if(isset($vendedores) && is_iterable($vendedores))
                                        @foreach($vendedores as $vendedor)
                                            <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="text-danger" id="error_direct_vendedor_id"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i>Ubicación</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pais_id">País</label>
                                        <select id="direct_pais_id" class="form-control">
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
                                        <select id="direct_departamento_id" class="form-control">
                                            <option value="">Seleccione un departamento...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ciudad_id" class="required">Ciudad</label>
                                        <select id="direct_ciudad_id" class="form-control" required>
                                            <option value="" selected>Seleccione una ciudad...</option>
                                        </select>
                                        <span class="text-danger" id="error_direct_ciudad_id"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="direccion" class="required">Dirección</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    </div>
                                    <textarea id="direct_direccion" class="form-control" rows="2" placeholder="Dirección completa" required></textarea>
                                </div>
                                <span class="text-danger" id="error_direct_direccion"></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="saveProveedorDirectBtn" onclick="saveProveedorDirect()">
                    <span class="spinner-border spinner-border-sm d-none" id="saveProveedorDirectSpinner"></span>
                    <i class="fas fa-save mr-1"></i>Guardar Proveedor
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.required {
    position: relative;
}

.required::after {
    content: " *";
    color: #e74c3c;
    font-weight: bold;
}

.card-header h5 {
    margin: 0;
}
</style>

<style>
.step-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
}

.step-card.active {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.step-header {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    padding: 1rem;
    border-radius: 0.375rem 0.375rem 0 0;
}

.step-number {
    width: 2rem;
    height: 2rem;
    background: white;
    color: #007bff;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: #495057;
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.text-danger {
    font-size: 0.875rem;
}

#saveProveedorDirectBtn {
    min-width: 140px;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    background-color: #f8f9fa;
}
</style>
