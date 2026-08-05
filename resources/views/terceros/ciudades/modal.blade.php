<!-- Modal -->
<div class="modal fade" id="ModalCiudad" aria-hidden="true"  role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ciudadModalLabel">Registrar Ciudad</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="ciudadForm">
                    @csrf
                    <input type="hidden" id="ciudad_id">

                    <!-- País -->
                    <div class="col-md-6 d-inline-block">
                        <div class="form-group">
                            <label for="pais_id">País</label>
                            <select id="pais_id" class="form-control">
                                <option value="">Seleccione un país</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-danger" id="error_pais_id"></small>
                        </div>
                    </div>

                    <!-- Departamento -->
                    <div class="col-md-6 d-inline-block">
                        <div class="form-group">
                            <label for="departamento_id">Departamento</label>
                            <select id="departamento_id" class="form-control">
                                <option value="">Seleccione un departamento</option>
                            </select>
                            <small class="text-danger" id="error_departamento_id"></small>
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-6 d-inline-block">
                        <div class="form-group">
                            <label for="ciudad">Nombre de la Ciudad</label>
                            <input type="text" id="ciudad" class="form-control" placeholder="Ingrese el nombre">
                            <small class="text-danger" id="error_nombre"></small>
                        </div>
                    </div>

                    <!-- Activo -->
                    <div class="col-md-6 d-inline-block">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ciudad_active" checked>
                                <label for="ciudad_active">Activo</label>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer" id="modal_footer_ciudad">
            </div>
        </div>
    </div>
</div>
