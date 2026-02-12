<div class="modal fade" id="modalUtilidad">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-percentage"></i> Utilidad / Margen Comercial
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formUtilidad">
                    <input type="hidden" id="cotizacionId" name="cotizacion_id" value="">

                    <!-- Información -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> La utilidad se aplicará a productos que coincidan tanto con la categoría como con el item propio seleccionado.
                    </div>

                    <!-- Categorías -->
                    <div class="form-group">
                        <label for="categoria_id">
                            Categoría: <span class="text-danger">*</span>
                        </label>
                        <select id="categoria_id" name="categoria_id" class="form-control" onchange="cambiarCategoria()">
                            <option value="">Seleccione una categoría...</option>
                        </select>
                        <small class="form-text text-muted">
                            Seleccione la categoría de productos
                        </small>
                    </div>

                    <!-- Items Propios -->
                    <div class="form-group">
                        <label for="item_propio_id">
                            Item Propio/Cargo: <span class="text-danger">*</span>
                        </label>
                        <select id="item_propio_id" name="item_propio_id" class="form-control">
                            <option value="">Primero seleccione una categoría...</option>
                        </select>
                        <small class="form-text text-muted">
                            Seleccione el item propio o cargo específico dentro de la categoría
                        </small>
                    </div>
                    <!-- Tipo de utilidad -->
                    <div class="form-group">
                        <label for="utilidad_tipo">Tipo de margen:</label>
                        <select id="utilidad_tipo" name="tipo" class="form-control">
                            <option value="">Seleccione...</option>
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="valor">Valor fijo ($)</option>
                        </select>
                    </div>

                    <!-- Valor -->
                    <div class="form-group">
                        <label for="utilidad_valor">Valor del margen:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="simboloValor">$</span>
                            </div>
                            <input id="utilidad_valor"
                                   name="valor"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   placeholder="0.00">
                        </div>
                        <small class="form-text text-muted" id="ayudaValor">
                            Ingrese el valor del margen
                        </small>
                    </div>

                    <!-- Resumen -->
                    <div class="alert alert-info d-none" id="resumenUtilidad">
                        <h6><i class="fas fa-info-circle"></i> Resumen</h6>
                        <p id="textoResumen"></p>
                    </div>

                    <!-- Lista de utilidades aplicadas -->
                    <div class="mt-4">
                        <h6>Utilidades aplicadas actualmente:</h6>
                        <div id="listaUtilidades" class="border rounded p-3 bg-light">
                            <div class="text-muted text-center">
                                <i class="fas fa-percentage fa-2x opacity-50"></i>
                                <p class="mb-0 mt-2">No hay utilidades aplicadas</p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="aplicarUtilidad()" id="btnAplicarUtilidad" disabled>
                    <i class="fas fa-check"></i> Aplicar Utilidad
                </button>
            </div>

        </div>
    </div>
</div>
