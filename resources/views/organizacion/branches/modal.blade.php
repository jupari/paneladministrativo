<div class="modal fade" id="ModalBranch" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="branchModalTitle">Registrar Sucursal</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="id">

        <div class="row">
          <div class="col-md-3">
            <label>Código</label>
            <input type="text" id="code" class="form-control" placeholder="Ej: CALI-01">
            <span class="text-danger" id="error_code"></span>
          </div>
          <div class="col-md-9">
            <label>Nombre</label>
            <input type="text" id="name" class="form-control" placeholder="Sucursal principal">
            <span class="text-danger" id="error_name"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-6">
            <label>Dirección</label>
            <input type="text" id="address" class="form-control">
            <span class="text-danger" id="error_address"></span>
          </div>
          <div class="col-md-3">
            <label>Ciudad</label>
            <input type="text" id="city" class="form-control">
            <span class="text-danger" id="error_city"></span>
          </div>
          <div class="col-md-3">
            <label>Teléfono</label>
            <input type="text" id="phone" class="form-control">
            <span class="text-danger" id="error_phone"></span>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-3">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" id="is_active" checked>
              <label class="form-check-label" for="is_active">Activo</label>
            </div>
            <span class="text-danger" id="error_is_active"></span>
          </div>
        </div>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
