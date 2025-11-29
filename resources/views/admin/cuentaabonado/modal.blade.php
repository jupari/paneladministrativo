<!-- Modal -->
<div class="modal fade" id="myModal" aria-hidden="true" style="display: none;">
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
                        <label for="user_id">Usuario</label>
                        <select id="usuario_dist" style="width: 100%;" placeholder="Seleccione" class="form-select">
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id}}">{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nombre_cuenta">Correo</label>
                        <input type="email" class="form-control" id="nombre_cuenta" placeholder="Correo eléctronico">
                        <span class="text-danger" id="error_nombre_cuenta"></span>
                    </div>
                    <div class="form-group">
                        <label for="password_cuenta">Contraseña temporal</label>
                        <input type="text" class="form-control" id="password_cuenta" placeholder="Password">
                        <span class="text-danger" id="error_password_cuenta"></span>
                    </div>
                    <div class="form-group">
                        <label for="fecha_asig">Fecha asignación</label>
                        <input type="date" class="form-control" id="fecha_asig">
                    </div>
                    <div class="form-group">
                        <label for="estado_id">Estado</label>
                        <select id="estado_id" style="width: 100%;" placeholder="Seleccione" class="form-select">
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id}}">{{ $estado->estado }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
