<div class="modal fade" id="ModalGenerarContratosEmpleados" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabelGE"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id" value="">
                <input type="hidden" id="user_id" value="{{ $user_id }}">
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Plantillas</legend>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="plantilla-select-ge">Seleccionar Plantilla</label>
                            <select  id="plantilla-select-ge" class="form-select">
                                <option value="">Seleccione</option>
                                @foreach($plantillas as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->plantilla }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="error_plantilla-select-ge"></span>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Digitar Campos Manuales</legend>
                    <div class="col-12" id="campos-manuales-ge">
                    </div>
                </fieldset>
                <span class="text-danger" id="error_campos_manuales"></span>
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-3">Descargar de PDF</legend>
                    <div class="col-12" id="descargar-archivos">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Link</th>
                                    </tr>
                                </thead>
                                <tbody id="body-descargas-empleados">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
