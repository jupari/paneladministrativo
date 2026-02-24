{{-- Modal Detalle --}}
<div class="modal fade" id="ModalReportDetail" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="reportDetailTitle">Detalle de nómina</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="detail_pay_run_id">
                <input type="hidden" id="detail_participant_type">
                <input type="hidden" id="detail_participant_id">

                <div class="row mb-2">
                    <div class="col-md-6">
                        <div><b>Participante:</b> <span id="detail_participant_name">-</span></div>
                        <div><b>Vínculo:</b> <span id="detail_link_type">-</span></div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div><b>Devengado:</b> <span id="detail_gross">0,00</span></div>
                        <div><b>Deducciones:</b> <span id="detail_ded">0,00</span></div>
                        <div><b>Neto:</b> <span id="detail_net">0,00</span></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="report-detail-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Naturaleza</th>
                            <th class="text-right">Cantidad</th>
                            <th class="text-right">Base</th>
                            <th class="text-right">Tasa</th>
                            <th class="text-right">Valor</th>
                            <th class="text-center">Dir</th>
                            <th class="text-center">Origen</th>
                            <th>Notas</th>
                        </tr>
                        </thead>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
