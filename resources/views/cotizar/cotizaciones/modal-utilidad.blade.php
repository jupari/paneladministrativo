<div class="modal fade" id="modalUtilidad">
    <div class="modal-dialog modal-xl">
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
                        <label for="items_propios_container">
                            Item Propio/Cargo: <span class="text-danger">*</span>
                        </label>

                        <!-- Filtro de búsqueda -->
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" id="filtroItems" class="form-control" placeholder="Buscar items..." onkeyup="filtrarItems()" style="display: none;">
                            <div class="input-group-append" id="contadorSeleccionados" style="display: none;">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-check"></i> <span id="numSeleccionados">0</span>
                                </span>
                            </div>
                        </div>

                        <div class="border rounded bg-light" style="max-height: 350px; overflow-y: auto; position: relative;" id="items_propios_container">
                            <div class="text-muted text-center p-4" id="placeholder_items">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <br>Primero seleccione una categoría...
                            </div>
                        </div>

                        <small class="form-text text-muted mt-2">
                            Seleccione uno o más items propios o cargos dentro de la categoría.
                            <span class="text-info" id="totalItemsInfo" style="display: none;"></span>
                        </small>

                        <!-- Botones de selección rápida mejorados -->
                        <div class="mt-3 d-flex flex-wrap justify-content-between" id="botonesControles" style="display: none !important;">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="seleccionarTodosItems()" id="btnSeleccionarTodos">
                                    <i class="fas fa-check-square"></i> Todos
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="deseleccionarTodosItems()" id="btnDeseleccionarTodos">
                                    <i class="fas fa-minus-square"></i> Ninguno
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="seleccionarSoloItemsPropios()" id="btnSoloItems">
                                    <i class="fas fa-cube"></i> Solo Items
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="seleccionarSoloCargos()" id="btnSoloCargos">
                                    <i class="fas fa-users"></i> Solo Cargos
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-dark" onclick="limpiarFiltro()" id="btnLimpiarFiltro" style="display: none;">
                                <i class="fas fa-times"></i> Limpiar filtro
                            </button>
                        </div>
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
    <!-- Estilos CSS para manejar listas grandes -->
    <style>
        /* Estilos para items individuales */
        .item-card {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 5px;
            background: white;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .item-card:hover {
            background: #f8f9fa;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .item-card.selected {
            background: #e3f2fd;
            border-color: #2196f3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }

        .item-checkbox {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .item-nombre {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .item-codigo {
            font-size: 0.85em;
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }

        .item-tipo {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: 500;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .tipo-item {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .tipo-cargo {
            background: #fff3e0;
            color: #f57c00;
            border: 1px solid #ffcc02;
        }

        /* Scroll personalizado */
        #items_propios_container::-webkit-scrollbar {
            width: 8px;
        }

        #items_propios_container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #items_propios_container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        #items_propios_container::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Sección headers para separar tipos */
        .seccion-header {
            background: #f8f9fa;
            padding: 8px 12px;
            margin: 10px -12px 10px -12px;
            border-top: 2px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.9em;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .seccion-header:first-child {
            margin-top: -12px;
            border-top: none;
        }

        /* Filtro resaltado */
        .filtro-resaltado {
            background: yellow;
            font-weight: bold;
        }

        /* Items filtrados ocultos */
        .item-oculto {
            display: none !important;
        }

        /* Mensaje cuando no hay resultados */
        .no-resultados {
            text-align: center;
            color: #6c757d;
            padding: 30px;
            font-style: italic;
        }

        /* Responsive para botones */
        @media (max-width: 768px) {
            .btn-group {
                flex-wrap: wrap;
            }

            .btn-group .btn {
                margin-bottom: 5px;
            }
        }
    </style>
</div>


