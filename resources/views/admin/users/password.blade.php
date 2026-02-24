<!-- Modal -->
<div class="modal fade" id="myModalPss" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelPss" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabelPss"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    {{-- <div class="form-group">
                        <label for="current_password">Contraseña actual</label>
                        <input type="password" class="form-control" id="current_password"  placeholder="Contraseña actual">
                    </div> --}}
                    <div class="form-group">
                        <label for="password">Nueva Contraseña(Mínimo 8 caracteres, una letra mayúscula y un número)</label>
                        <input type="password" class="form-control" id="reset-password" placeholder="Nueva Contraseña.">
                        <span class="text-danger" id="error_reset_password"></span>
                    </div>
                    <div class="form-group">
                        <label for="password-confirm">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password-confirm" placeholder="Confirmar Contraseña.">
                        <span class="text-danger" id="error_cpassword"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer pass">
            </div>
        </div>
    </div>
</div>
