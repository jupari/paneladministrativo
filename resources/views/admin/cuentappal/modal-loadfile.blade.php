<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal-loadFile" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" style="max-width: 1070px !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabel"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex-row">
                    <p style="padding-left: 1.25rem">Filas en excel revisadas:  <span class="font-weight-bold" id="regLeidos"></span> </p>
                    <p style="padding-left: 1.25rem">Registros validos: <span  class="font-weight-bold" id="regCambiar" ></span> </p>
                    <p style="padding-left: 1.25rem">Registros No validos: <span  class="font-weight-bold" id="regNoValidos" ></span> </p>
                    <p style="padding-left: 1.25rem">Lote No. <span class="font-weight-bold" id="lote"></span>
                    <div class="card-body file_program">
                        <div class="card-body">
                            <div class="table-responsive">
                               <table id="file-loading" class="table table-bordered table-hover">
                                  <thead>
                                     <tr>
                                        <th>#</th>
                                        <th>Correo</th>
                                        <th>Password</th>
                                        <th>Código</th>
                                        <th>Cuenta asociada</th>
                                        <th>Usuario dist. Id</th>
                                        <th>ClienteId</th>
                                        <th>TenantId</th>
                                        <th>Clientsecret</th>
                                        <th>EsCuentaPpal?</th>
                                        <th>Acción</th>
                                     </tr>
                                  </thead>
                                  <tbody id="bodyRes">
                                  </tbody>
                               </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modal-footer-file">
            </div>
        </div>
    </div>
</div>
<div id="respuestaCambiarEstado"></div>
