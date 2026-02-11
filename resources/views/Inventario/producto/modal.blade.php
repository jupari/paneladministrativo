<!-- Modal -->
<div class="modal fade" id="ModalProducto" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form id="form-producto">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="producto_id">
                    <div class="row">
                        <div class="form-group col-12 col-md-2">
                                <label class="form-label">Código*</label>
                                <input type="text" class="form-control" name="codigo" id="codigo">
                                <span class="text-danger" id="error_codigo"></span>
                        </div>
                        <div class="form-group col-12 col-md-6">
                                <label class="form-label">Nombre*</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" >
                                <span class="text-danger" id="error_nombre"></span>
                        </div>
                        <div class="form-group col-12 col-md-4">
                                <label for="tipo_producto">Tipo Producto*</label>
                                <select id="tipo_producto" name="tipo_producto" class="form-control" >
                                        <option value="">Cargando...</option>
                                </select>
                                <span class="text-danger" id="error_tipo_producto"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="descripcion"></textarea>
                        <span class="text-danger" id="error_descripcion"></span>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-2">
                                <label for="unidad_medida">Unidad de Medida*</label>
                                <select id="unidad_medida" name="unidad_medida" class="form-control">
                                        <option value="">Cargando...</option>
                                </select>
                                <span class="text-danger" id="error_unidad_medida"></span>
                        </div>
                    <div class="form-group col-12 col-md-2">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="stock">
                            <span class="text-danger" id="error_stock"></span>
                        </div>
                        <div class="form-group col-12 col-md-8">
                            <label class="form-label">Precio</label>
                            <input type="number" class="form-control" step="0.01" name="precio" id="precio">
                            <span class="text-danger" id="error_precio"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-4">
                            <label for="marca">Marca</label>
                            <select id="marca" name="marca" class="form-control" >
                                    <option value="">Cargando...</option>
                            </select>
                            <span class="text-danger" id="error_marca"></span>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="categoria">Categoría</label>
                            <select id="categoria" name="categoria" class="form-control" >
                                    <option value="">Cargando...</option>
                            </select>
                            <span class="text-danger" id="error_categoria"></span>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="subcategoria">Subcategoría</label>
                            <select id="subcategoria" name="subcategoria" class="form-control" >
                                    <option value="">Cargando...</option>
                            </select>
                            <span class="text-danger" id="error_subcategoria"></span>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="active" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                </div>
                <div class="row my-1 col-12 px-3">
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success" id="guardar-modal">Guardar</button>
                    </div>
                </div>
                <fieldset class="border col-12 mb-4">
                    <div class="col-md-3 my-2">
                        <button type="button" class="btn btn-primary mb-3" id="btn-nuevo-propiedad">Nuevo Registro</button>
                    </div>
                    <div id="productosdet-table"></div>
                </fieldset>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

