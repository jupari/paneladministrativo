<!-- Modal -->
<div class="modal fade" id="ModalPais" aria-hidden="true" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="paisModalLabel">Registrar País</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="paisForm">
                    @csrf
                    <input type="hidden" id="pais_form_id">

                    <div class="form-group">
                        <label for="pais_nombre">Nombre del País</label>
                        <input type="text" id="pais_nombre" class="form-control" placeholder="Ingrese el nombre">
                        <small class="text-danger" id="error_pais_nombre"></small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="pais_active" checked>
                            <label for="pais_active">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="modal_footer_pais">
            </div>
        </div>
    </div>
</div>
