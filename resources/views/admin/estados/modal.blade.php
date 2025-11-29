<!-- Modal -->
<div class="modal fade" id="myModal" aria-hidden="true"  tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel"></h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>

                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <input type="text" class="form-control" id="estado" placeholder="Nombre del estado">
                        <span class="text-danger" id="error_estado"></span>
                    </div>
                    <div class="form-group">
                        <label for="estado">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" placeholder="Digite una descripción">
                    </div>

                    <div class="form-group">
                        <label for="color">Colores de fondo(Bootstrap - primary, success, info, secondary, dark, light, warning, danger)</label>
                        <input type="text" class="form-control" id="color" placeholder="Ejemplo: primary, secondary">
                        <span class="text-danger" id="error_color"></span>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
