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
                <form autocomplete="off">

                    <div class="form-group">
                        <label for="nombre">Código</label>
                        <input type="text" class="form-control" id="nombre" placeholder="Nombre de la Cuenta ppal" autocomplete="off">
                        <span class="text-danger" id="error_nombre"></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo</label>
                        <input type="text" class="form-control" id="email" placeholder="Correo eléctronico" autocomplete="off">
                        <span class="text-danger" id="error_email"></span>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" id="password" placeholder="Contraseña" autocomplete="off">
                            <div class="input-group-append">
                              <span class="btn btn-secondary" onclick="seePasword()"><i id="eye" class="fas fa-eye"></i></span>
                            </div>
                        </div>
                        <span class="text-danger" id="error_password"></span>
                    </div>
                    <div class="form-group">
                        <label for="usuario_reg">Cuenta asociada</label>
                        <select id="cm_asociada" style="width: 100%;" placeholder="Seleccione">
                            @foreach($cuentasMadres as $cm)
                                @if($cm->cta_ppal==1)
                                    <option value="{{ $cm->email }}">
                                            {{ $cm->email }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="usuario_dist">Usuario temporal</label>
                        <select id="usuario_dist" style="width: 100%;" placeholder="Seleccione">
                            @foreach($abonados as $abonado)
                                <option value="{{ $abonado->id}}">{{ $abonado->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Id. de aplicación (cliente) English: ClientId</label>
                        <input type="text" class="form-control" id="clientId" placeholder="Client Id">
                    </div>
                    <div class="form-group">
                        <label for="password">Id. de directorio (inquilino) English: TenantId</label>
                        <input type="text" class="form-control" id="tenant_id" placeholder="Tenant Id">
                    </div>
                    <div class="form-group">
                        <label for="password">Secreto Id</label>
                        <input type="text" class="form-control" id="clientSecret" placeholder="Secret Id">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cta_ppal">
                            <label for="active">Cuenta Principal</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
