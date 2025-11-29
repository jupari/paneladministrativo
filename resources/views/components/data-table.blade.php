<table id="{{ $id }}" class="table table-striped">
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
</table>

<script>
    $(document).ready(function () {
        if ($.fn.DataTable.isDataTable('#{{ $id }}')) {
            $('#{{ $id }}').DataTable().destroy();
        }

        let table = $('#{{ $id }}').DataTable({
            language: {
                url: "/assets/js/spanish.json"
            },
            responsive: true,
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'ltr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: @json($buttons),
            ajax: '{{ $ajaxUrl }}',
            columns: @json($columns),
            columnDefs: @json($customOptions['columnDefs'] ?? []),
            order: @json($customOptions['order'] ?? [[1, "asc"]]),
            pageLength: @json($customOptions['pageLength'] ?? 10),
            lengthMenu: @json($customOptions['lengthMenu'] ?? [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Todo(s)"]])
        });
    });
</script>
