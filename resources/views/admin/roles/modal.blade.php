<!-- Modal -->
<div class="modal fade" id="myModal" aria-hidden="true" style="display: none;">
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
                        <input type="text" class="form-control" id="name" placeholder="Nombre rol">
                        <span class="text-danger" id="error_name"></span>
                    </div>
                    <div class="form-group">
                        <label for="rol">Guard</label>
                        <select class="form-control" id="guard_name" style="width: 100%;">
                            @foreach(config('auth.guards') as $guardName => $guard)
                                <option value="{{ $guardName}}">{{ $guardName }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger" id="error_guard_name"></span>
                    </div>
                    <div class="form-group">
                        <label for="permission">Permisos</label>

                            @if(isset($permisos))
                                @foreach($permisos->groupBy(function($permiso) {
                                    return explode('.', $permiso->name)[0]; // Agrupar por la segunda palabra del nombre
                                }) as $grupo => $permisosGrupo)
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">{{ ucfirst($grupo) }}</h3>
                                    </div>

                                    <div class="card-body px-2">
                                        <div class="px-2">
                                            @foreach($permisosGrupo as $key => $permiso)
                                                <div class="col-sm-6">
                                                    <div class="form-group clearfix">
                                                            <div class="icheck-primary d-inline">
                                                                <input type="checkbox" id="checkboxPrimary_{{ $grupo }}_{{ $key }}" class="form-check-input permissions" value="{{ $permiso->name }}" name="permissions[]">{{ $permiso->description }}
                                                                <label for="checkboxPrimary_{{ $grupo }}_{{ $key }}">
                                                                </label>
                                                            </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="card-footer"></div>
                                </div>
                                @endforeach
                            @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
