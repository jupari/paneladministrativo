<!-- Modal -->
<div class="modal fade" id="ModalPaisDpto" aria-hidden="true" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabelPaisDpto">Registrar País</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="ciudadForm">
                    @csrf
                    <input type="hidden" id="id">
                    <!-- País -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pais_id">País</label>
                            <select id="input_pais_id" class="form-control">
                            </select>
                        </div>
                    </div>
                    <!-- Departamento -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre Departamento</label>
                            <input type="text" id="input_departamento" class="form-control" placeholder="Ingrese el nombre">
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre país</label>
                            <input type="text" id="input_pais" class="form-control" placeholder="Ingrese el nombre">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="modal_footer">
            </div>
        </div>
    </div>
</div>
