<div class="modal fade" id="ModalParametros" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Registrar Parámetro</h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off">
                    @csrf
                    <input type="hidden" id="id" value="">
                    <fieldset class="border p-3 mb-4">
                        <legend class="w-auto px-3">Información</legend>
                        <div class="row">
                            <input type="hidden" id="elemento_id">
                            <div class="col-md-2">
                                <label for="codigo">Código*</label>
                                <input type="text" id="codigo" class="form-control" placeholder="Código">
                                <span class="text-danger" id="error_codigo"></span>
                            </div>
                            <div class="col-md-4">
                                <label for="nombre">Nombre*</label>
                                <input type="text" id="nombre" class="form-control" placeholder="Párametro">
                                <span class="text-danger" id="error_nombre"></span>
                            </div>
                            <div class="col-md-4">
                                <label for="valor">Valor</label>
                                <input type="text" id="valor" class="form-control" placeholder="">
                                <span class="text-danger" id="error_valor"></span>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check my-4">
                                    <input class="form-check-input" type="checkbox" id="active">
                                    <label for="active">Activo*</label>
                                </div>
                                <span class="text-danger" id="error_active"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div>
                                <button type="button" id="btnGuardarElemento" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="my-3">
                    <div class="d-flex justify-content-between mb-2">
                        <button id="nuevoParametro" class="btn btn-primary btn-sm" onclick="nuevoParametroDet()">Nuevo Registro</button>
                        <button id="actualizarParametro" class="btn btn-secondary btn-sm" onclick="actualizarParametroDet()">Actualizar</button>
                    </div>
                    <div id="parametrosdet-table"></div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
