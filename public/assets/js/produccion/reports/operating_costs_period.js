$(function () {
    toastr.options = { closeButton: true, positionClass: "toast-bottom-right", timeOut: "5000" };

    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const firstDay = `${y}-${m}-01`;
    const lastDay = new Date(y, now.getMonth() + 1, 0).toISOString().slice(0, 10);
    $('#filter_from').val(firstDay);
    $('#filter_to').val(lastDay);

    CargarCostos();
});

let operatingCostsTable = null;

function CargarCostos() {
    if ($.fn.DataTable.isDataTable('#operating-costs-table')) {
        $('#operating-costs-table').DataTable().destroy();
    }

    operatingCostsTable = $('#operating-costs-table').DataTable({
        language: { url: "/assets/js/spanish.json" },
        responsive: true,
        processing: true,
        serverSide: true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'ltr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [{
            extend: 'excel',
            className: 'btn btn-success',
            exportOptions: { columns: ':not(.exclude)' },
            text: '<i class="far fa-file-excel"></i>',
            filename: 'reporte_costos_operativos_periodo'
        }],
        ajax: {
            url: '/admin/admin.produccion.reports.operating-costs.period',
            data: function (d) {
                d.from = $('#filter_from').val();
                d.to = $('#filter_to').val();
            },
            dataSrc: function (json) {
                const s = json.summary || null;
                $('#sum_labor_cost').text(s ? s.total_labor_cost : '0,00');
                $('#sum_rejected_cost').text(s ? s.total_rejected_cost : '0,00');
                $('#sum_unit_cost').text(s ? s.real_unit_cost : '0,00');
                $('#sum_qty').text(s ? s.total_qty : '0,00');
                $('#sum_accepted').text(s ? s.total_accepted : '0,00');
                $('#sum_orders').text(s ? s.total_orders : '0');
                $('#sum_operations').text(s ? s.total_operations : '0');
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_id', className: 'exclude' },
            { data: 'order_code' },
            { data: 'producto' },
            { data: 'operacion' },
            { data: 'total_qty', className: 'text-right' },
            { data: 'total_accepted', className: 'text-right' },
            { data: 'total_rejected', className: 'text-right' },
            { data: 'avg_rate', className: 'text-right' },
            { data: 'labor_cost', className: 'text-right' },
            { data: 'rejected_cost', className: 'text-right' },
            { data: 'unit_cost', className: 'text-right' }
        ],
        order: [[1, 'desc']]
    });
}

function applyCostFilters() {
    if (!operatingCostsTable) return;

    const from = $('#filter_from').val();
    const to = $('#filter_to').val();
    if (from && to && from > to) {
        toastr.warning('La fecha Desde no puede ser mayor que Hasta.');
        return;
    }

    operatingCostsTable.ajax.reload();
}

