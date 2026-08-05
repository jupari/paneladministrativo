<!-- Modal -->
<div class="modal fade" id="ModalDepartamento" aria-hidden="true" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="departamentoModalLabel">Registrar Departamento</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="departamentoForm">
                    @csrf
                    <input type="hidden" id="departamento_form_id">

                    <div class="form-group">
                        <label for="departamento_pais_id">País</label>
                        <select id="departamento_pais_id" class="form-control">
                            <option value="">Seleccione un país</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="error_departamento_pais_id"></small>
                    </div>

                    <div class="form-group">
                        <label for="departamento_nombre">Nombre del Departamento</label>
                        <input type="text" id="departamento_nombre" class="form-control" placeholder="Ingrese el nombre">
                        <small class="text-danger" id="error_departamento_nombre"></small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="departamento_active" checked>
                            <label for="departamento_active">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="modal_footer_departamento">
            </div>
        </div>
    </div>
</div>
