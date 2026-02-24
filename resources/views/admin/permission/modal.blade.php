<!-- Modal -->
<div class="modal fade" id="modalpermiso" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>

                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="name" placeholder="[modulo].[acción]">
                        <span class="text-danger" id="error_name"></span>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <input type="text" class="form-control" id="description" placeholder="Descripción permiso">
                        <span class="text-danger" id="error_description"></span>
                    </div>
                    <div class="form-group">
                        <label for="rol">Guard</label>
                        <select class="form-control" id="guard_name" style="width: 100%;">
                            @foreach(config('auth.guards') as $guardName => $guard)
                                <option value="{{ $guardName}}">{{ $guardName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-danger" id="error_guard_name"></span>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
