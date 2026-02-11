<!-- Modal -->
<div class="modal fade" id="ModalCiudad" aria-hidden="true"  role="dialog" tabindex="1">
    <div class="modal-dialog modal-xl"> <!-- Clase 'modal-xl' para hacer el modal más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">Registrar Ciudad</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" id="ciudadForm">
                    @csrf
                    <input type="hidden" id="id">

                    <!-- País -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pais_id">País</label>
                            <select id="pais_id" class="form-control">
                                <option value="">Seleccione un país</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" id="btnCrearPais" onclick="openModalPaisDpto(event)">Crear País</button>
                        </div>
                    </div>

                    <!-- Departamento -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="departamento_id">Departamento</label>
                            <select id="departamento_id" class="form-control">
                            </select>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" onclick="openModalPaisDpto()">Crear Departamento</button>
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre de la Ciudad</label>
                            <input type="text" id="ciudad" class="form-control" placeholder="Ingrese el nombre">
                        </div>
                    </div>

                    <!-- Activo -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active">
                                <label for="active">Activo</label>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
