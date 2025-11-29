<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="myModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel"></h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12"><h5 id="email-subject">Seleccione un correo para ver el contenido </h5></div>
                    <div class="col-md-12 d-flex justify-content-center">
                        <div class="spinner-border m-5" role="status" id="spinner-email">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <ul class="list-group" id="email-list">

                        </ul>
                    </div>
                    <div class="col-md-12">
                        <div id="email-body" class="email">
                            <p id="email-from" class="info"></p>
                            <p id="email-received" class="info"></p>
                            <div id="email-content"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
