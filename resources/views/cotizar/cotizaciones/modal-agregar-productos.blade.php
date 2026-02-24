<!-- Modal para Agregar Productos/Salarios -->
<div class="modal fade" id="modalAgregarProductos" tabindex="-1" aria-labelledby="modalAgregarProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarProductosLabel">
                    <i class="fas fa-plus-circle"></i> Agregar Productos y Salarios
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" id="tabsAgregarProductos" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="productos-tab" data-toggle="tab" data-target="#productos" type="button" role="tab">
                            <i class="fas fa-box"></i> Productos
                        </button>
                    </li>
                    {{-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="salarios-tab" data-toggle="tab" data-target="#salarios" type="button" role="tab">
                            <i class="fas fa-users"></i> Categorias
                        </button>
                    </li> --}}
                </ul>
                <!-- Tab content -->
                <div class="tab-content" id="tabsContent">
                    <!-- Tab Productos -->
                    <div class="tab-pane fade show active" id="productos" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-list"></i> Items del Acordeón</h6>
                                <p class="text-muted">Items y subitems agregados en la sección "Ingresar consecutivo de items"</p>
                                <!-- Campo de búsqueda para filtrar items -->
                                <div class="form-group">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="buscarItemsAcordeon" placeholder="Filtrar items y subitems..." oninput="filtrarItemsAcordeon()">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="limpiarFiltroItems()" title="Limpiar filtro">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm table-hover" id="tablaItemsAcordeon">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th width="40">
                                                    <i class="fas fa-check-circle"></i>
                                                </th>
                                                <th>Item/Subitem</th>
                                                <th>Descripción</th>
                                                <th>Tipo</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyItemsAcordeon">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                    No hay items agregados en el acordeón
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Botón para usar items seleccionados -->
                                <div class="mt-2 mb-3">
                                    <button type="button" class="btn btn-success btn-sm" id="btnUsarItemsSeleccionados" onclick="usarItemSeleccionado()">
                                        <i class="fas fa-plus"></i> Agregar Item Seleccionado
                                    </button>
                                    <small class="form-text text-muted">Seleccione un item del acordeón para cargar sus productos por categoría.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success"><i class="fas fa-shopping-cart"></i> Productos Seleccionados</h6>
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Productos</th>
                                                <th width="80">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductosSeleccionados">
                                            <tr id="noProductosSeleccionados">
                                                <td colspan="2" class="text-center text-muted">
                                                    No hay productos seleccionados
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Los productos se cargan automáticamente según la categoría del producto seleccionado</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <!-- Tab Salarios -->
                    <div class="tab-pane fade" id="salarios" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-user-plus"></i> Agregar productos y personal</h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="formAgregarPersonal">
                                            <!-- Selección de Categorías -->
                                            <div class="form-group">
                                                <label>Selección de Categorías <span class="text-danger">*</span></label>
                                                <div class="border p-2" style="max-height: 150px; overflow-y: auto;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAllCategorias" onchange="toggleAllCategorias()">
                                                        <label class="form-check-label font-weight-bold" for="selectAllCategorias">
                                                            Seleccionar Todas
                                                        </label>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div id="listaCategorias">
                                                        <!-- Categorías se cargan dinámicamente -->
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Seleccione una o múltiples categorías para calcular el costo total</small>
                                            </div>

                                            <!-- Items Propios basados en categorías seleccionadas -->
                                            <div class="form-group" id="grupoItemsPropios" style="display: none;">
                                                <label>Items Propios de las Categorías Seleccionadas</label>
                                                <select class="form-control" id="itemPropio" disabled>
                                                    <option value="">Seleccione un item propio...</option>
                                                </select>
                                                <small class="form-text text-muted">Los items se cargan automáticamente al seleccionar categorías</small>
                                            </div>

                                            <!-- Tipo de Costo -->
                                            <div class="form-group">
                                                <label>Tipo de Costo <span class="text-danger">*</span></label>
                                                <select class="form-control" id="tipoCosto" onchange="cambiarTipoCosto()">
                                                    <option value="">Seleccione tipo de costo...</option>
                                                    <option value="COSTO_DIA">COSTO DIA</option>
                                                    <option value="COSTO_HORA">COSTO HORA</option>
                                                    <option value="COSTO_MES">COSTO MES</option>
                                                </select>
                                            </div>
                                            <!-- Cantidad de Personal -->
                                            <div class="form-group">
                                                <label>Cantidad de Personal <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="cantidadPersonal" min="1" value="1">
                                            </div>

                                            <!-- Campo específico para COSTO HORA -->
                                            <div id="campoHoras" class="form-group" style="display: none;">
                                                <label>Cantidad de Horas (Máximo 7) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="cantidadHoras" min="1" max="7" value="8">
                                                <small class="form-text text-muted">Máximo 7 horas permitidas para costo por hora</small>
                                            </div>

                                            <!-- Campos para COSTO DIA y COSTO MES -->
                                            <div id="camposDiurnosNocturnos">
                                                <!-- Días Diurnos -->
                                                <div class="form-group">
                                                    <label>Cantidad de Días Diurnos</label>
                                                    <input type="number" class="form-control" id="diasDiurnos" min="0" value="0" onchange="mostrarCampoRemunerados('diurnos')">
                                                </div>

                                                <div id="campoDiasRemuneradosD" class="form-group" style="display: none;">
                                                    <label>Ingrese la cantidad de días remunerados (Diurnos)</label>
                                                    <input type="number" class="form-control" id="diasRemuneradosDiurnos" min="0">
                                                </div>

                                                <!-- Días Nocturnos -->
                                                <div class="form-group">
                                                    <label>Cantidad de Días Nocturnos</label>
                                                    <input type="number" class="form-control" id="diasNocturnos" min="0" value="0" onchange="mostrarCampoRemunerados('nocturnos')">
                                                </div>

                                                <div id="campoDiasRemuneradosN" class="form-group" style="display: none;">
                                                    <label>Ingrese la cantidad de días remunerados (Nocturnos)</label>
                                                    <input type="number" class="form-control" id="diasRemuneradosNocturnos" min="0">
                                                </div>

                                                <!-- Días Dominicales -->
                                                <div class="form-group">
                                                    <label>¿Desea ingresar días dominicales?</label>
                                                    <div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="dominicales" id="dominicalesSi" value="si" onchange="mostrarCamposDominicales()">
                                                            <label class="form-check-label" for="dominicalesSi">Sí</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="dominicales" id="dominicalesNo" value="no" onchange="ocultarCamposDominicales()" checked>
                                                            <label class="form-check-label" for="dominicalesNo">No</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Campos Dominicales -->
                                                <div id="camposDominicales" style="display: none;">
                                                    <div id="dominicalDiurno" class="form-group" style="display: none;">
                                                        <label>Días Dominicales Diurnos</label>
                                                        <input type="number" class="form-control" id="dominicalesDiurnos" min="0">
                                                    </div>

                                                    <div id="dominicalNocturno" class="form-group" style="display: none;">
                                                        <label>Días Dominicales Nocturnos</label>
                                                        <input type="number" class="form-control" id="dominicalesNocturnos" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Campos específicos de la última sección de salarios -->
                                            <div class="card mt-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Ingresa el margen de cada categoría</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Margen de Utilidad (%)</label>
                                                        <input type="number" class="form-control" id="margenUtilidad" step="0.01" value="0" onchange="calcularTotalConMargen()">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Administración (%)</label>
                                                        <input type="number" class="form-control" id="porcentajeAdministracion" step="0.01" value="0" onchange="calcularTotalConMargen()">
                                                    </div>

                                                    <div class="form-group">
                                                        <label>Imprevistos (%)</label>
                                                        <input type="number" class="form-control" id="porcentajeImprevistos" step="0.01" value="0" onchange="calcularTotalConMargen()">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Total Principal -->
                                            <div class="form-group">
                                                <label class="font-weight-bold">TOTAL COSTO</label>
                                                <input type="number" class="form-control font-weight-bold text-center" id="totalCosto" readonly style="font-size: 1.2em; background-color: #f8f9fa;">
                                            </div>

                                            <!-- Botón Ver Detalles -->
                                            <div class="form-group">
                                                <button type="button" class="btn btn-info btn-sm" id="verDetalles" onclick="mostrarTablaDetalles()">
                                                    <i class="fas fa-eye"></i> Vista Detalles
                                                </button>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-user-plus"></i> Agregar Personal
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-users"></i> Personal Asignado</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-sm">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>Categoría</th>
                                                        <th>Tipo Costo</th>
                                                        <th>Cantidad</th>
                                                        <th>Total</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyPersonalAsignado">
                                                    <tr id="noPersonalAsignado">
                                                        <td colspan="5" class="text-center text-muted">
                                                            No hay personal asignado
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <strong>Total Salarios: $<span id="totalSalarios">0.00</span></strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Tabla de Detalles -->
                                <div class="modal fade" id="modalDetallesPrecios" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-table"></i> Tabla de Precios Detallada</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-sm">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>Categoría</th>
                                                                <th>Tipo Costo</th>
                                                                <th>Costo Base</th>
                                                                <th>Cantidad</th>
                                                                <th>Días/Horas</th>
                                                                <th>Subtotal</th>
                                                                <th>Margen (%)</th>
                                                                <th>Total Final</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tablaDetallesPrecios">
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted">
                                                                    No hay datos para mostrar
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
            <div class="modal-footer">
                <div class="mr-auto">
                    <strong>Total General: $ <span id="totalGeneral">0.00</span></strong>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarAgregarProductos">
                    <i class="fas fa-check"></i> Confirmar y Agregar
                </button>
            </div>
        </div>
    </div>
</div>
