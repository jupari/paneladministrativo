<div class="modal fade" id="ModalBodega" tabindex="-1">
  <div class="modal-dialog">
    <form id="form-bodega">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Bodega</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="bodega_id">
          <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control" name="codigo" id="codigo">
            <span class="text-danger" id="error_codigo"></span>
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="nombre" >
            <span class="text-danger" id="error_nombre"></span>
          </div>
          <div class="mb-3">
            <label class="form-label">Ubicación</label>
            <input type="text" class="form-control" name="ubicacion" id="ubicacion">
            <span class="text-danger" id="error_ubicacion"></span>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="activo" checked>
            <label class="form-check-label">Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>
