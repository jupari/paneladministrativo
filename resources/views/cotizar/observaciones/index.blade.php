@extends('adminlte::page')

@section('title', 'Observaciones')

@section('content')
    @php
        $breadcrumbs = [
            [
                'title' => 'Cotizaciones y Ventas',
                'icon' => 'fas fa-file-invoice-dollar',
                'url' => route('admin.cotizaciones.index')
            ]
        ];
        $currentTitle = 'Observaciones';
        $currentIcon = 'fas fa-comments';
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :currentTitle="$currentTitle" :currentIcon="$currentIcon" />

    <div class="card">
        <div class="card-header">
            <h4>Observaciones</h4>
        </div>
        <div class="card-body">
            @if(auth()->user()->hasRole(['Administrator']) || auth()->user()->can('cotizaciones.observaciones.create'))
                <div class="col-md-1">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#observacionModal" onclick="createObservacion()">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-striped" id="observacionesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Texto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="observacionModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form id="observacionForm" method="POST" action="{{ route('admin.observaciones.store') }}">
              @csrf
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Observación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                  <div class="form-group">
                      <label for="texto">Texto</label>
                      <textarea name="texto" id="texto" class="form-control" rows="3" required></textarea>
                  </div>
                  <div class="form-group">
                      <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" checked>
                          <label class="custom-control-label" for="active">Activo</label>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
          </form>
        </div>
      </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#observacionesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.observaciones.index') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'texto', name: 'texto'},
                {data: 'estado', name: 'estado', orderable: false, searchable: false},
                {data: 'acciones', name: 'acciones', orderable: false, searchable: false}
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });
    });

    function createObservacion() {
        $('#observacionForm').attr('action', '{{ route('admin.observaciones.store') }}');
        $('#formMethod').val('POST');
        $('#texto').val('');
        $('#active').prop('checked', true);
        $('#modalLabel').text('Nueva Observación');
    }

    function editObservacion(obs) {
        var baseAction = '{{ route('admin.observaciones.update', 'OBS_ID') }}';
        baseAction = baseAction.replace('OBS_ID', obs.id);
        $('#observacionForm').attr('action', baseAction);
        $('#formMethod').val('PUT');
        $('#texto').val(obs.texto);
        $('#active').prop('checked', obs.active ? true : false);
        $('#modalLabel').text('Editar Observación');
        $('#observacionModal').modal('show');
    }
</script>
@stop
