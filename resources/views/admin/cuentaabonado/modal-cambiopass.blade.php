<!-- Modal -->
<div class="modal fade" id="myModalCuenta" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabelCuenta"></h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>

                    <div class="form-group">
                        <label for="nombre_cuenta">Cuenta</label>
                        <input type="email" class="form-control" id="nombre_cuenta" placeholder="Correo eléctronico" readonly>
                        <span class="text-danger" id="error_nombre_cuenta"></span>
                    </div>
                    <div class="form-group">
                        <label for="password_cuenta">Contraseña temporal</label>
                        <input type="text" class="form-control" id="password_cuenta" placeholder="Contraseña temporal">
                        <span class="text-danger" id="error_password_cuenta"></span>
                    </div>
                    <div class="form-group">
                        <label for="fecha_asig">Fecha asignación</label>
                        <input type="date" class="form-control" id="fecha_asig" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
