<div class="modal fade" id="ModalOperation" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="operationModalTitle">Registrar Operación</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id">

        <fieldset class="border p-3 mb-2">
          <legend class="w-auto px-3">Datos de la operación</legend>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="code">Código</label>
                <input type="text" id="code" class="form-control" placeholder="Ej: OP_MANGA">
                <span class="text-danger" id="error_code"></span>
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" class="form-control" placeholder="Ej: Pegar Manga">
                <span class="text-danger" id="error_name"></span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-10">
              <div class="form-group">
                <label for="description">Descripción</label>
                <input type="text" id="description" class="form-control" placeholder="Opcional">
                <span class="text-danger" id="error_description"></span>
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group mt-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="is_active" checked>
                  <label class="form-check-label" for="is_active">Activo</label>
                </div>
                <span class="text-danger" id="error_is_active"></span>
              </div>
            </div>
          </div>

        </fieldset>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
