<div class="modal fade" id="ModalDuplicateNovelty" aria-hidden="true" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Duplicar Novedad a varios empleados</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="dup_id">

        <div class="alert alert-info">
          Se copiará el mismo concepto/periodo/cantidad/valor/descripción/estado a los empleados seleccionados.
          La novedad original NO se modifica.
        </div>

        <div class="row">
          <div class="col-md-12">
            <label>Empleados destino</label>
            <select id="dup_employee_ids" class="form-control" multiple="multiple" style="width:100%"></select>
            <small class="text-muted">Puedes seleccionar varios empleados.</small>
            <span class="text-danger" id="error_dup_employee_ids"></span>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="dup_skip_existing" checked>
              <label class="form-check-label" for="dup_skip_existing">
                Omitir si ya existe una novedad igual (mismo empleado+concepto+periodo en PENDING)
              </label>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" onclick="confirmDuplicateNovelty()">
          <i class="fas fa-copy"></i> Duplicar
        </button>
      </div>
    </div>
  </div>
</div>
