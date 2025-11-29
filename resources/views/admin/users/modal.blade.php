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

                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-content-below-home-tab" data-toggle="pill" href="#custom-content-below-home" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Usuario</a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-profile-tab" data-toggle="pill" href="#custom-content-below-profile" role="tab" aria-controls="custom-content-below-profile" aria-selected="false">Datos usuario</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-content-below-archivos-tab" data-toggle="pill" href="#custom-content-below-archivos" role="tab" aria-controls="custom-content-below-archivos" aria-selected="false">Más detalles</a>
                        </li> --}}
                    </ul>
                    <div class="tab-content" id="custom-content-below-tabContent">
                        <div class="tab-pane fade show active" id="custom-content-below-home" role="tabpanel" aria-labelledby="custom-content-below-home-tab">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="correo">Correo electrónico</label>
                                    <input type="text" class="form-control" id="email" placeholder="Correo electrónico">
                                    <span class="text-danger" id="error_email"></span>
                                </div>
                                <div class="form-group">
                                    <label for="correo">Identificación</label>
                                    <input type="text" class="form-control" id="identificacion" placeholder="Digite una identificación">
                                    <span class="text-danger" id="error_email"></span>
                                </div>
                                <div class="form-group">
                                    <label for="nombres">Nombre(s)</label>
                                    <input type="text" class="form-control" id="name" placeholder="Nombre(s) usuario">
                                     <span class="text-danger" id="error_name"></span>
                                </div>
                                <div id="panelpassword">
                                    <div class="form-group">
                                        <label for="nombres">Contraseña(Mínimo 8 caracteres, una letra mayúscula y un número)</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" placeholder="Contraseña">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i id="toggleIcon" class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="text-danger" id="error_password"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="nombres">Repetir contraseña</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="rpassword" placeholder="Repetir contraseña">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="toggleRPassword">
                                                    <i id="toggleIconR" class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>


                                        <span class="text-danger" id="repetir-password"></span>
                                    </div>
                                </div>
                                {{-- @if(auth()->user()->hasRole('Administrator')) --}}
                                    <div class="form-group">
                                        <label for="role">Rol</label>
                                        <select class="form-control" id="role" style="width: 100%;">
                                            <option value="">Seleccione</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                {{-- @endif --}}
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="active" checked>
                                        <label for="active">Activo</label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        {{-- <div class="tab-pane fade" id="custom-content-below-profile" role="tabpanel" aria-labelledby="custom-content-below-profile-tab">
                            <div class="card-body">
                                {{-- <div class="form-group">
                                    <label for="codigo_usuario">Código</label>
                                    <input type="text" class="form-control" id="codigo_usuario" placeholder="Código" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="tipo_documento">Tipo de Documento</label>
                                    <select class="form-control"  id="tipo_documento"  style="width: 100%;">
                                        <option value="">Seleccione</option>
                                        <option value="1">Cédula de ciudadanía</option>
                                        <option value="5">Tarjeta de identidad</option>
                                        <option value="6">Registro civil</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="documento">Número de Documento</label>
                                    <input type="text" class="form-control" id="documento" placeholder="Número Documento">
                                </div> --}}
                                {{-- <div class="form-group">
                                    <label for="fecha_nac">Fecha Nacimiento</label>
                                    <div class="input-group date" id="fecha_nacimiento" data-target-input="nearest">
                                        <div class="input-group-append" data-target="#fecha_nacimiento" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                        <input type="text" class="form-control datetimepicker-input" data-target="#fecha_nacimiento" id="fecha_nac">

                                    </div>
                                </div>

                               <div class="form-group">
                                    <label for="tipo_genero_id">Género</label>
                                    <select class="form-control"  id="tipo_genero_id"  style="width: 100%;">
                                        <option value="">Seleccione</option>
                                        @foreach($generos as $genero)
                                            <option value="{{ $genero->codigo }}">{{ $genero->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="ciudad_id">Ciudad</label>
                                    <select class="form-control"  id="ciudad_id" style="width: 100%;">
                                        <option value="">Seleccione</option>
                                        @foreach($ciudades as $ciudad)
                                            <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                {{-- <div class="form-group">
                                    <label for="etnia_id">Etnia</label>
                                    <select class="form-control"  id="etnia_id" style="width: 100%;">
                                        <option value="">Seleccione</option>
                                        @foreach($etnias as $etnia)
                                            <option value="{{ $etnia->codigo_etnia }}">{{ $etnia->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-content-below-archivos" role="tabpanel" aria-labelledby="custom-content-below-archivos-tab">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="telefono">Télefono</label>
                                    <input type="text" class="form-control" id="telefono" placeholder="telefono">
                                </div>
                                <div class="form-group">
                                    <label class="file_1" for="archivo_1">Cédula</label>
                                    <div class="custom-file arch1">
                                        <input type="file" class="custom-file-input" id="archivo_1">
                                        <label class="custom-file-label lb_1" for="archivo_1">Subir cédula</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="file_2" for="archivo_2">Recibo servicios</label>
                                    <div class="custom-file arch2">
                                        <input type="file" class="custom-file-input" id="archivo_2">
                                        <label class="custom-file-label lb_2" for="archivo_2">Subir recibo servicios</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="politica">
                                        <label for="politica" class="custom-control-label">Acepto política de datos</label>
                                    </div>
                                </div>
                                @if(auth()->user()->hasRole('Admin'))
                                    <div class="form-group">
                                        <label for="estado_id">Estado Registro</label>
                                        <select class="form-control"  id="estado_id" style="width: 100%;">
                                            <option value="">Seleccione</option>
                                            @foreach($estados as $estado)
                                                <option value="{{ $estado->codigo_registro }}">{{ $estado->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif


                            </div>
                        </div> --}}

                    </div><!-- *** -->
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
