<div class="modal fade" id="WorkshopModal" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="workshopModalTitle">Nuevo taller</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="workshop_id">

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="workshop_code">Código</label>
              <input type="text" id="workshop_code" class="form-control" placeholder="Ej: TCN-001">
              <span class="text-danger" id="error_code"></span>
            </div>
          </div>
          <div class="col-md-8">
            <div class="form-group">
              <label for="workshop_name">Nombre</label>
              <input type="text" id="workshop_name" class="form-control" placeholder="Nombre del taller">
              <span class="text-danger" id="error_name"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="form-group">
              <label for="workshop_address">Dirección</label>
              <input type="text" id="workshop_address" class="form-control" placeholder="Dirección">
              <span class="text-danger" id="error_address"></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="workshop_status">Estado</label>
              <select id="workshop_status" class="form-control">
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
                <option value="suspended">Suspendido</option>
              </select>
              <span class="text-danger" id="error_status"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="workshop_coordinator_name">Coordinador</label>
              <input type="text" id="workshop_coordinator_name" class="form-control" placeholder="Nombre del coordinador">
              <span class="text-danger" id="error_coordinator_name"></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="workshop_coordinator_phone">Teléfono coordinador</label>
              <input type="text" id="workshop_coordinator_phone" class="form-control" placeholder="3001234567">
              <span class="text-danger" id="error_coordinator_phone"></span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>
