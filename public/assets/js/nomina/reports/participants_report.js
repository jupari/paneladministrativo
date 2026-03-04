$(function () {

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    loadPayRunsFilter();
    Cargar();
});

let reportTable = null;

function Cargar() {

    if ($.fn.DataTable.isDataTable('#report-table')) {
        $('#report-table').DataTable().destroy();
    }

    reportTable = $('#report-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        processing: true,
        serverSide: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'ltr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-success',
                exportOptions: { columns: ':not(.exclude)' },
                text: '<i class="far fa-file-excel"></i>',
                titleAttr: 'Exportar a Excel',
                filename: 'reporte_nomina_participantes'
            }
        ],
        ajax: {
            url: '/admin/admin.nomina.reports.participants.index',
            data: function (d) {
                d.pay_run_id = $('#filter_pay_run_id').val();
                d.from = $('#filter_from').val();
                d.to = $('#filter_to').val();
            },
            dataSrc: function (json) {
                // ✅ pintar resumen
                const sum = (json && json.summary) ? json.summary : null;
                $('#sum_devengado').text(sum ? sum.total_devengado : '0,00');
                $('#sum_deducciones').text(sum ? sum.total_deducciones : '0,00');
                $('#sum_neto').text(sum ? sum.total_a_pagar : '0,00');
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'exclude', orderable: false, searchable: false },
            { data: 'pay_run_id', name: 'pay_run_id', className: 'exclude' },
            { data: 'run_type', name: 'run_type' },
            { data: 'period', name: 'period', orderable: false, searchable: false },
            { data: 'pay_date', name: 'pay_date' },
            { data: 'link_type', name: 'link_type' },
            { data: 'participante', name: 'participante' },
            { data: 'gross_total', name: 'gross_total', className: 'text-right' },
            { data: 'deductions_total', name: 'deductions_total', className: 'text-right' },
            { data: 'net_total', name: 'net_total', className: 'text-right' },
            { data: 'status', name: 'status', className: 'text-center', orderable: false, searchable: false }
        ],
        order: [[1, "desc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
    });
}

function applyReportFilters() {
    if (!reportTable) return;
    reportTable.ajax.reload();
}

function loadPayRunsFilter() {
    $.get('/admin/admin.nomina.reports.payruns.list', (resp) => {
        const items = resp.data || [];
        const $sel = $('#filter_pay_run_id');
        $sel.empty();
        $sel.append('<option value="">-- Seleccione --</option>');
        items.forEach(i => $sel.append(`<option value="${i.id}">${i.text}</option>`));
    }).catch(() => {
        toastr.warning('No fue posible cargar la lista de periodos.');
    });
}

// Si selecciona payrun, opcionalmente limpia fechas (para evitar confusiones)
$('#filter_pay_run_id').on('change', function () {
    if ($(this).val()) {
        $('#filter_from').val('');
        $('#filter_to').val('');
    }
});

//Modal datatable

let detailTable = null;

// ✅ Click en fila (abre modal detalle)
// Recomendación: clic en la columna "Participante"
$(document).on('click', '#report-table tbody tr', function () {

    // tomar data de la fila
    const row = reportTable.row(this).data();
    if (!row) return;

    openDetailModal(row);
});

function openDetailModal(row) {

    // row trae: pay_run_id, participant_type, participant_id, participante, link_type, gross_total, deductions_total, net_total
    $('#detail_pay_run_id').val(row.pay_run_id);
    $('#detail_participant_type').val(row.participant_type);
    $('#detail_participant_id').val(row.participant_id);

    $('#detail_participant_name').text(row.participante || '-');
    $('#detail_link_type').text(row.link_type || '-');

    $('#detail_gross').text(row.gross_total || '0,00');
    $('#detail_ded').text(row.deductions_total || '0,00');
    $('#detail_net').text(row.net_total || '0,00');

    $('#reportDetailTitle').text(`Detalle de nómina | PayRun #${row.pay_run_id}`);

    $('#ModalReportDetail').modal('show');

    loadDetailTable();
}

function loadDetailTable() {

    const payRunId = $('#detail_pay_run_id').val();
    const pType = $('#detail_participant_type').val();
    const pId = $('#detail_participant_id').val();

    if ($.fn.DataTable.isDataTable('#report-detail-table')) {
        $('#report-detail-table').DataTable().destroy();
    }

    detailTable = $('#report-detail-table').DataTable({
        language: { "url": "/assets/js/spanish.json" },
        responsive: true,
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        pageLength: 50,
        ajax: {
            url: '/admin/admin.nomina.reports.lines',
            data: function (d) {
                d.pay_run_id = payRunId;
                d.participant_type = pType;
                d.participant_id = pId;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            {
                data: null,
                name: 'concept_name',
                render: function (data, type, row) {
                    return `<b>${row.code}</b> - ${row.concept_name}`;
                }
            },
            { data: 'kind', name: 'kind' },
            { data: 'tax_nature', name: 'tax_nature' },
            { data: 'quantity', name: 'quantity', className: 'text-right' },
            { data: 'base_amount', name: 'base_amount', className: 'text-right' },
            { data: 'rate', name: 'rate', className: 'text-right' },
            { data: 'amount', name: 'amount', className: 'text-right' },
            { data: 'direction', name: 'direction', className: 'text-center', orderable: false, searchable: false },
            { data: 'source', name: 'source', className: 'text-center', orderable: false, searchable: false },
            { data: 'notes', name: 'notes' }
        ],
        order: [[1, "asc"]],
    });
}
